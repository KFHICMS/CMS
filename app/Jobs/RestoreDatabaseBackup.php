<?php

namespace App\Jobs;

use App\Models\DatabaseBackup;
use App\Services\DatabaseBackupService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class RestoreDatabaseBackup implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 1800;

    public function __construct(public int $backupId, public ?int $userId, public string $userName)
    {
    }

    public function handle(DatabaseBackupService $service): void
    {
        $backup = DatabaseBackup::find($this->backupId);
        if (! $backup) {
            return;
        }

        try {
            $service->restore($backup, $this->userId, $this->userName);
        } catch (Throwable $e) {
            // Details are already in the audit log / laravel.log; never retry a restore automatically.
            Log::error('Database restore did not complete', ['message' => $e->getMessage()]);
        }
    }
}