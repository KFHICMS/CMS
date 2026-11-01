<?php

namespace App\Jobs;

use App\Models\DatabaseBackup;
use App\Services\DatabaseBackupService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateDatabaseBackup implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 900;

    public function __construct(public int $backupId)
    {
    }

    public function handle(DatabaseBackupService $service): void
    {
        $backup = DatabaseBackup::find($this->backupId);

        // Only a pending record may run, so a stale job can never run twice.
        if (! $backup || $backup->status !== 'pending') {
            return;
        }

        $service->run($backup);
    }
}