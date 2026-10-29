<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\DatabaseBackup;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Process\Process;
use Throwable;

/**
 * Creates and restores database backups.
 * Secrets: the database password is handed to mysqldump/mysql through a temporary
 * 0600 options file, never on the command line, and is scrubbed from any error text.
 */
class DatabaseBackupService
{
    public function root(): string
    {
        return rtrim((string) config('filesystems.disks.'.config('kfhi_backup.disk').'.root'), '/');
    }

    public function path(string $filename): string
    {
        return $this->root().'/'.basename($filename);
    }

    public function driver(): string
    {
        return DB::connection()->getDriverName();
    }

    public function supportsRestore(): bool
    {
        return in_array($this->driver(), ['mysql', 'mariadb'], true);
    }

    /** True while another backup/restore is genuinely in progress (stale entries older than 30 min are ignored). */
    public function isBusy(): bool
    {
        return DatabaseBackup::query()
            ->whereIn('status', ['pending', 'running'])
            ->where('updated_at', '>=', now()->subMinutes(30))
            ->exists();
    }

    /** Creates the metadata row with a unique, descriptive file name. Nothing is dumped yet. */
    public function createRecord(?User $user, string $type = 'manual'): DatabaseBackup
    {
        $prefix = $type === 'pre_restore' ? 'kfhi_prerestore_' : 'kfhi_backup_';
        $ext = $this->driver() === 'sqlite' ? 'sqlite' : 'sql';
        $base = $prefix.now()->format('Y-m-d_His');

        $name = "{$base}.{$ext}";
        $n = 2;
        while (DatabaseBackup::where('filename', $name)->exists() || file_exists($this->path($name))) {
            $name = "{$base}_{$n}.{$ext}";
            $n++;
        }

        return DatabaseBackup::create([
            'filename' => $name,
            'type' => $type,
            'status' => 'pending',
            'initiated_by' => $user?->id,
        ]);
    }

    /** Performs the dump for an existing record and stores the result. Never throws: failures are recorded. */
    public function run(DatabaseBackup $backup): void
    {
        if (function_exists('set_time_limit')) {
            @set_time_limit(0);
        }

        $backup->update(['status' => 'running', 'started_at' => now(), 'error' => null]);
        $this->audit($backup->initiated_by, 'backup_started', $backup->id, "Backup {$backup->filename} started.");

        $path = $this->path($backup->filename);

        try {
            $this->ensureDirectory();

            if ($this->driver() === 'sqlite') {
                $this->dumpSqlite($path);
            } else {
                $this->dumpMysql($path);
            }

            $this->verifyDump($path);
            @chmod($path, 0600);

            $backup->update([
                'status' => 'completed',
                'size_bytes' => filesize($path),
                'checksum' => hash_file('sha256', $path),
                'completed_at' => now(),
            ]);

            $this->audit($backup->initiated_by, 'backup_completed', $backup->id,
                "Backup {$backup->filename} completed (".$backup->size_bytes.' bytes).');
        } catch (Throwable $e) {
            @unlink($path);
            $message = $this->sanitize($e->getMessage());

            $backup->update(['status' => 'failed', 'completed_at' => now(), 'error' => $message]);

            Log::error('Database backup failed', ['backup_id' => $backup->id, 'error' => $message]);
            $this->audit($backup->initiated_by, 'backup_failed', $backup->id,
                "Backup {$backup->filename} failed: ".Str::limit($message, 300));
        }
    }

    /**
     * Checks that a backup may be restored. Throws RuntimeException with a clear message otherwise.
     *
     * @return array{path: string, size: int}
     */
    public function validateForRestore(DatabaseBackup $backup): array
    {
        if (! $this->supportsRestore()) {
            throw new RuntimeException('Restore is supported only for MySQL or MariaDB databases.');
        }
        if ($backup->status !== 'completed') {
            throw new RuntimeException('Only completed backups can be restored.');
        }

        $path = $this->path($backup->filename);
        if (! is_file($path) || ! is_readable($path)) {
            throw new RuntimeException('The backup file is missing from the backup folder.');
        }

        $size = (int) filesize($path);
        if ($size <= 0 || ($backup->size_bytes && $size !== (int) $backup->size_bytes)) {
            throw new RuntimeException('The backup file size does not match its record. The file may be damaged.');
        }
        if ($backup->checksum && ! hash_equals($backup->checksum, hash_file('sha256', $path))) {
            throw new RuntimeException('The backup file failed its integrity check (checksum mismatch). It may be damaged or altered.');
        }

        $head = (string) file_get_contents($path, false, null, 0, 2048);
        if (! preg_match('/^-- (MySQL|MariaDB) dump/m', $head)) {
            throw new RuntimeException('The file is not a valid MySQL/MariaDB dump.');
        }

        return ['path' => $path, 'size' => $size];
    }

    /**
     * Restores the database from a backup. Steps: validate, take a safety backup of the CURRENT data,
     * enter maintenance mode, import, leave maintenance mode, repair the backup list.
     * Throws RuntimeException on failure.
     */
    public function restore(DatabaseBackup $backup, ?int $userId, string $userName): void
    {
        $check = $this->validateForRestore($backup);

        $meta = [
            'id' => $backup->id,
            'filename' => $backup->filename,
            'type' => $backup->type,
            'size' => $backup->size_bytes,
            'checksum' => $backup->checksum,
            'completed' => $backup->completed_at,
            'initiated_by' => $backup->initiated_by,
        ];

        Log::warning('Database restore started', ['backup' => $meta['filename'], 'by' => $userName]);
        $this->audit($userId, 'database_restore_started', $meta['id'], "Restore of {$meta['filename']} started by {$userName}.");

        // 1. Safety copy of what is in the database right now.
        $safety = $this->createRecord($userId ? User::find($userId) : null, 'pre_restore');
        $this->run($safety);
        $safety->refresh();

        if ($safety->status !== 'completed') {
            $this->audit($userId, 'database_restore_failed', $meta['id'], 'Restore cancelled: the safety backup could not be created.');
            throw new RuntimeException('Restore cancelled. The safety backup of the current data failed: '.$safety->error);
        }

        $safetyMeta = [
            'filename' => $safety->filename, 'type' => 'pre_restore', 'size' => $safety->size_bytes,
            'checksum' => $safety->checksum, 'completed' => $safety->completed_at, 'initiated_by' => $userId,
        ];

        // 2. Import with the site in maintenance mode so nobody writes while tables are replaced.
        $error = null;
        Artisan::call('down', ['--retry' => 60]);
        try {
            $this->importDump($check['path']);
        } catch (Throwable $e) {
            $error = $this->sanitize($e->getMessage());
        } finally {
            Artisan::call('up');
        }

        if ($error !== null) {
            Log::critical('Database restore FAILED', ['backup' => $meta['filename'], 'safety_backup' => $safetyMeta['filename'], 'error' => $error]);
            try {
                $this->audit($userId, 'database_restore_failed', $meta['id'], 'Restore failed: '.Str::limit($error, 300));
            } catch (Throwable) {
                // The database may be mid-restore; the log file above still has the record.
            }

            throw new RuntimeException("Restore failed: {$error} The database may be partly restored. "
                ."The data from before the restore is saved as {$safetyMeta['filename']}.");
        }

        // 3. The restored tables replaced the backup list and audit log with their old contents. Repair the list.
        $this->resyncAfterRestore([$meta, $safetyMeta]);

        $this->audit($userId, 'database_restored', $meta['id'],
            "Database restored from {$meta['filename']} by {$userName}. Safety backup: {$safetyMeta['filename']}.");
        Log::warning('Database restore completed', ['backup' => $meta['filename'], 'by' => $userName]);
    }

    private function resyncAfterRestore(array $metas): void
    {
        foreach ($metas as $m) {
            $initiator = $m['initiated_by'] && User::whereKey($m['initiated_by'])->exists() ? $m['initiated_by'] : null;

            DatabaseBackup::updateOrCreate(
                ['filename' => $m['filename']],
                [
                    'type' => $m['type'],
                    'status' => 'completed',
                    'size_bytes' => $m['size'],
                    'checksum' => $m['checksum'],
                    'completed_at' => $m['completed'] ?? now(),
                    'initiated_by' => $initiator,
                    'error' => null,
                ]
            );
        }

        DatabaseBackup::whereIn('status', ['pending', 'running'])->update([
            'status' => 'failed',
            'error' => 'Interrupted: the database was restored from an earlier backup.',
            'completed_at' => now(),
        ]);
    }

    // ----- low-level helpers ------------------------------------------------------------------

    private function connectionConfig(): array
    {
        return config('database.connections.'.config('database.default'));
    }

    private function ensureDirectory(): void
    {
        $root = $this->root();
        if (! is_dir($root) && ! mkdir($root, 0700, true) && ! is_dir($root)) {
            throw new RuntimeException('The backup folder could not be created.');
        }
        @chmod($root, 0700);
        if (! is_writable($root)) {
            throw new RuntimeException('The backup folder is not writable by the web server user.');
        }
    }

    private function dumpMysql(string $path): void
    {
        $c = $this->connectionConfig();
        $defaults = $this->writeDefaultsFile($c);

        try {
            $args = [config('kfhi_backup.mysqldump_path'), '--defaults-extra-file='.$defaults,
                '--single-transaction', '--quick', '--routines', '--triggers', '--default-character-set=utf8mb4'];
            if (config('kfhi_backup.no_tablespaces')) {
                $args[] = '--no-tablespaces';
            }
            $args[] = '--result-file='.$path;
            $args[] = $c['database'];

            $process = new Process($args);
            $process->setTimeout((float) config('kfhi_backup.timeout'));
            $process->run();

            if (! $process->isSuccessful()) {
                throw new RuntimeException('mysqldump failed: '.(trim($process->getErrorOutput()) ?: 'exit code '.$process->getExitCode()));
            }
        } finally {
            @unlink($defaults);
        }
    }

    private function importDump(string $path): void
    {
        $c = $this->connectionConfig();
        $defaults = $this->writeDefaultsFile($c);
        $input = fopen($path, 'r');

        try {
            $process = new Process([config('kfhi_backup.mysql_path'), '--defaults-extra-file='.$defaults,
                '--default-character-set=utf8mb4', $c['database']]);
            $process->setTimeout((float) config('kfhi_backup.timeout'));
            $process->setInput($input);
            $process->run();

            if (! $process->isSuccessful()) {
                throw new RuntimeException(trim($process->getErrorOutput()) ?: 'mysql exited with code '.$process->getExitCode());
            }
        } finally {
            if (is_resource($input)) {
                fclose($input);
            }
            @unlink($defaults);
        }
    }

    private function dumpSqlite(string $path): void
    {
        $quoted = str_replace("'", "''", $path);
        DB::statement("VACUUM INTO '{$quoted}'");
    }

    private function verifyDump(string $path): void
    {
        if (! is_file($path) || filesize($path) === 0) {
            throw new RuntimeException('The backup file was not created or is empty.');
        }

        if ($this->driver() !== 'sqlite') {
            $size = filesize($path);
            $tail = (string) file_get_contents($path, false, null, max(0, $size - 4096));
            if (! str_contains($tail, 'Dump completed')) {
                throw new RuntimeException('The backup file looks incomplete (no completion marker).');
            }
        }
    }

    /** Temporary 0600 options file so the password never appears in the process list or logs. */
    private function writeDefaultsFile(array $c): string
    {
        $esc = fn ($v) => str_replace(['\\', '"'], ['\\\\', '\\"'], (string) $v);

        $lines = ['[client]', 'user="'.$esc($c['username'] ?? '').'"', 'password="'.$esc($c['password'] ?? '').'"'];
        if (! empty($c['unix_socket'])) {
            $lines[] = 'socket="'.$esc($c['unix_socket']).'"';
        } else {
            $lines[] = 'host="'.$esc($c['host'] ?? '127.0.0.1').'"';
            $lines[] = 'port='.(int) ($c['port'] ?? 3306);
        }

        $file = tempnam(sys_get_temp_dir(), 'kfhi');
        chmod($file, 0600);
        file_put_contents($file, implode("\n", $lines)."\n");

        return $file;
    }

    private function sanitize(string $message): string
    {
        $secret = (string) ($this->connectionConfig()['password'] ?? '');
        if ($secret !== '') {
            $message = str_replace($secret, '[hidden]', $message);
        }
        $message = preg_replace('#'.preg_quote(sys_get_temp_dir(), '#').'/kfhi\w+#', '[temp file]', $message);

        return Str::limit(trim($message), 1000);
    }

    public function audit(?int $userId, string $action, ?int $recordId, string $description): void
    {
        try {
            AuditLog::create([
                'user_id' => $userId,
                'action' => $action,
                'module' => 'backup',
                'record_id' => $recordId,
                'description' => $description,
            ]);
        } catch (Throwable $e) {
            Log::warning('Could not write backup audit entry', ['action' => $action]);
        }
    }
}