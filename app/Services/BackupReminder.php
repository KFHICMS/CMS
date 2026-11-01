<?php

namespace App\Services;

use App\Models\DatabaseBackup;
use Illuminate\Support\Carbon;

/**
 * Decides whether the weekly backup reminder should be shown. All dates use the
 * application timezone (config app.timezone).
 *
 * Week:      starts on config kfhi_backup.reminder.week_starts_on (default Monday).
 * Satisfied: a completed manual backup exists since the start of the current week.
 * Window:    from (reminder day - lead_days) to the end of that week; default Sunday only.
 * State:     'overdue' (nothing newer than overdue_days) > 'due' (in window, not satisfied) > 'ok'.
 */
class BackupReminder
{
    private const DAYS = ['sunday' => 0, 'monday' => 1, 'tuesday' => 2, 'wednesday' => 3, 'thursday' => 4, 'friday' => 5, 'saturday' => 6];

    public function status(): array
    {
        $cfg = config('kfhi_backup.reminder');
        $now = now();

        $weekStartDow = self::DAYS[strtolower($cfg['week_starts_on'])] ?? 1;
        $reminderDow = self::DAYS[strtolower($cfg['day'])] ?? 0;

        $weekStart = $now->copy()->startOfWeek($weekStartDow);
        $reminderDay = $weekStart->copy()->addDays((($reminderDow - $weekStartDow) + 7) % 7)->startOfDay();
        $windowStart = $reminderDay->copy()->subDays(max(0, (int) $cfg['lead_days']))->max($weekStart);

        $last = DatabaseBackup::query()
            ->where('status', 'completed')
            ->where('type', 'manual')
            ->latest('completed_at')
            ->first();

        $satisfied = $last && $last->completed_at && $last->completed_at->greaterThanOrEqualTo($weekStart);
        $inWindow = $now->greaterThanOrEqualTo($windowStart);
        $overdue = ! $last || ! $last->completed_at
            || $last->completed_at->lessThan($now->copy()->subDays((int) $cfg['overdue_days']));

        $state = $overdue ? 'overdue' : (($inWindow && ! $satisfied) ? 'due' : 'ok');

        return [
            'state' => $state,
            'last' => $last,
            'satisfied' => (bool) $satisfied,
            'in_window' => $inWindow,
            'reminder_day' => ucfirst(strtolower($cfg['day'])),
            'next_reminder' => $reminderDay->greaterThanOrEqualTo($now->copy()->startOfDay())
                ? $reminderDay : $reminderDay->copy()->addWeek(),
            'overdue_days' => (int) $cfg['overdue_days'],
            'timezone' => config('app.timezone'),
        ];
    }
}