<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\CreateDatabaseBackup;
use App\Jobs\RestoreDatabaseBackup;
use App\Models\DatabaseBackup;
use App\Services\AuditLogService;
use App\Services\BackupReminder;
use App\Services\DatabaseBackupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BackupController extends Controller
{
    public function __construct(
        private readonly DatabaseBackupService $backups,
        private readonly BackupReminder $reminder,
        private readonly AuditLogService $audit,
    ) {
    }

    public function index(Request $request): View
    {
        $this->authorizeBackup($request);

        return view('admin.backups.index', [
            'backups' => DatabaseBackup::query()->with('initiator:id,name')->latest('id')->paginate(15),
            'reminder' => $this->reminder->status(),
            'busy' => $this->backups->isBusy(),
            'restoreEnabled' => (bool) config('kfhi_backup.restore_enabled') && $this->backups->supportsRestore(),
            'runMode' => config('kfhi_backup.run_mode'),
            'running' => DatabaseBackup::whereIn('status', ['pending', 'running'])
                ->where('updated_at', '>=', now()->subMinutes(30))->exists(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeBackup($request);

        if ($this->backups->isBusy()) {
            return back()->with('error', 'A backup is already in progress. Please wait for it to finish.');
        }

        $backup = $this->backups->createRecord($request->user());

        $this->audit->record($request, 'backup_requested', 'backup', $backup->id,
            "Backup {$backup->filename} requested.");

        $this->dispatchJob(new CreateDatabaseBackup($backup->id));

        return redirect()->route('admin.backups.index')
            ->with('success', 'Backup started. It runs in the background, so you can keep working. Refresh this page to see the result.');
    }

    public function download(Request $request, DatabaseBackup $backup): BinaryFileResponse
    {
        $this->authorizeBackup($request);

        abort_unless($backup->status === 'completed', 404);
        $path = $this->backups->path($backup->filename);
        abort_unless(is_file($path), 404, 'The backup file is missing.');

        $this->audit->record($request, 'backup_downloaded', 'backup', $backup->id,
            "Backup {$backup->filename} downloaded.");

        return response()->download($path, $backup->filename, [
            'Content-Type' => 'application/octet-stream',
            'Cache-Control' => 'no-store',
        ]);
    }

    public function restoreForm(Request $request, DatabaseBackup $backup): View|RedirectResponse
    {
        $this->authorizeBackup($request);

        if ($refusal = $this->restoreRefusal($backup)) {
            return redirect()->route('admin.backups.index')->with('error', $refusal);
        }

        return view('admin.backups.restore', ['backup' => $backup]);
    }

    public function restore(Request $request, DatabaseBackup $backup): RedirectResponse
    {
        $this->authorizeBackup($request);

        $data = $request->validate([
            'confirm_phrase' => ['required', 'in:RESTORE'],
            'understand' => ['accepted'],
            'password' => ['required', 'string'],
        ], [
            'confirm_phrase.in' => 'Type the word RESTORE in capital letters to confirm.',
            'understand.accepted' => 'You must tick the box to confirm you understand.',
        ]);

        if (! Hash::check($data['password'], $request->user()->password)) {
            $this->audit->record($request, 'database_restore_denied', 'backup', $backup->id,
                'Restore refused: wrong password.');

            return back()->withErrors(['password' => 'The password is incorrect.']);
        }

        if ($refusal = $this->restoreRefusal($backup)) {
            return redirect()->route('admin.backups.index')->with('error', $refusal);
        }

        $this->audit->record($request, 'database_restore_requested', 'backup', $backup->id,
            "Restore of {$backup->filename} confirmed by {$request->user()->name}.");

        $this->dispatchJob(new RestoreDatabaseBackup($backup->id, $request->user()->id, $request->user()->name));

        return redirect()->route('admin.backups.index')
            ->with('success', 'Restore started. The system will briefly go into maintenance mode, and you may need to log in again.');
    }

    /** Returns a reason text when this backup must not be restored right now, or null when it is allowed. */
    private function restoreRefusal(DatabaseBackup $backup): ?string
    {
        if (! config('kfhi_backup.restore_enabled')) {
            return 'Restore is switched off. A server administrator must set KFHI_BACKUP_RESTORE_ENABLED=true in the .env file first.';
        }
        if ($this->backups->isBusy()) {
            return 'A backup or restore is in progress. Please wait for it to finish.';
        }

        try {
            $this->backups->validateForRestore($backup);
        } catch (RuntimeException $e) {
            return $e->getMessage();
        }

        return null;
    }

    private function authorizeBackup(Request $request): void
    {
        abort_unless(in_array($request->user()->role, config('kfhi_backup.allowed_roles', ['admin']), true), 403);
    }

    private function dispatchJob(object $job): void
    {
        if (config('kfhi_backup.run_mode') === 'after_response') {
            dispatch($job)->afterResponse();
        } else {
            dispatch($job);
        }
    }
}