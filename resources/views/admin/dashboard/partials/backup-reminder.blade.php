{{-- Weekly backup reminder. Data comes from App\Services\BackupReminder via a view composer. --}}
@php($r = $backupReminder)
<section class="k-card" aria-labelledby="bk-h">
    <div class="k-card-head">
        <h2 id="bk-h">Database backup</h2>
        <span>
            @if ($r['last'])
                Last backup: {{ $r['last']->completed_at->format('d M Y, H:i') }} ({{ $r['last']->completed_at->diffForHumans() }})
            @else
                No backup yet
            @endif
        </span>
    </div>

    @if ($r['state'] === 'overdue')
        <div class="k-alert k-alert-error" role="alert" style="margin-bottom: 12px">
            {{ $r['last'] ? 'The last successful backup is more than '.$r['overdue_days'].' days old.' : 'No successful backup has been taken yet.' }}
            Please back up now.
        </div>
    @elseif ($r['state'] === 'due')
        <div class="k-alert k-alert-warning" role="alert" style="margin-bottom: 12px">
            It is {{ $r['reminder_day'] }}, the weekly backup day, and no backup has been completed this week.
        </div>
    @else
        <div class="k-alert k-alert-success" role="status" style="margin-bottom: 12px">
            @if ($r['satisfied']) A recent backup is available. This week’s backup is done. @else No backup is due yet. @endif
            Next reminder: {{ $r['next_reminder']->format('l, d M') }}.
        </div>
    @endif

    <a class="k-btn {{ $r['state'] === 'ok' ? 'k-btn-secondary' : 'k-btn-primary' }}" href="{{ route('admin.backups.index') }}">
        {{ $r['state'] === 'ok' ? 'Open backups' : 'Back up now' }}
    </a>
</section>