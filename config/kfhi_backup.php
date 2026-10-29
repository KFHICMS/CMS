<?php

/*
|--------------------------------------------------------------------------
| KFHI database backup and recovery
|--------------------------------------------------------------------------
*/

return [
    // Private disk defined in config/filesystems.php (storage/app/backups, never public).
    'disk' => 'backups',

    // Roles allowed to create, download and restore backups.
    'allowed_roles' => ['admin'],

    // Command-line tools (must be installed on the server).
    'mysqldump_path' => env('KFHI_MYSQLDUMP_PATH', 'mysqldump'),
    'mysql_path' => env('KFHI_MYSQL_PATH', 'mysql'),

    // Set to false if your MySQL/MariaDB version does not know --no-tablespaces.
    'no_tablespaces' => env('KFHI_BACKUP_NO_TABLESPACES', true),

    // 'queue'          = background job processed by "php artisan queue:work" (recommended)
    // 'after_response' = runs in the web process right after the page has been sent (no worker needed)
    'run_mode' => env('KFHI_BACKUP_RUN_MODE', 'queue'),

    // Seconds a dump or restore may run.
    'timeout' => (int) env('KFHI_BACKUP_TIMEOUT', 900),

    // Restoring replaces live data, so it is OFF until an administrator of the server turns it on in .env.
    'restore_enabled' => (bool) env('KFHI_BACKUP_RESTORE_ENABLED', false),

    // Weekly reminder. Times use the application timezone (APP_TIMEZONE).
    'reminder' => [
        'day' => env('KFHI_BACKUP_REMINDER_DAY', 'sunday'),          // end-of-week reminder day
        'lead_days' => (int) env('KFHI_BACKUP_REMINDER_LEAD_DAYS', 0), // also remind this many days earlier
        'week_starts_on' => env('KFHI_BACKUP_WEEK_STARTS', 'monday'),
        'overdue_days' => (int) env('KFHI_BACKUP_OVERDUE_DAYS', 7),    // warn when the last backup is older than this
    ],
];