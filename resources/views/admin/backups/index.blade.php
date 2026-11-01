@extends('layouts.app', [
    'active' => 'backups',
    'crumbs' => [
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Backup & Recovery'],
    ],
])

@section('title', 'Backup & Recovery')
@section('heading', 'Backup & Recovery')
@section('subheading', 'Create database backups and review previous ones.')

@section('actions')
<form method="POST" action="{{ route('admin.backups.store') }}">
    @csrf
    <button type="submit" class="k-btn k-btn-primary" @disabled($busy)>Back up now</button>
</form>
@endsection

@section('content')
@php
    $fmtSize = function ($bytes) {
        if ($bytes === null) { return '—'; }
        if ($bytes >= 1048576) { return number_format($bytes / 1048576, 2).' MB'; }
        return number_format(max($bytes, 1) / 1024, 1).' KB';
    };
    $badge = ['completed' => '', 'failed' => 'k-badge-error', 'running' => 'k-badge-info', 'pending' => 'k-badge-warning'];
@endphp
<div class="k-stack">

    @php($r = $reminder)
    @if ($r['state'] === 'overdue')
        <div class="k-alert k-alert-error" role="alert">
            {{ $r['last'] ? 'The last successful backup is more than '.$r['overdue_days'].' days old.' : 'No successful backup has been taken yet.' }}
            Please back up now.
        </div>
    @elseif ($r['state'] === 'due')
        <div class="k-alert k-alert-warning" role="alert">It is time for the weekly backup (reminder day: {{ $r['reminder_day'] }}). No backup has been completed this week.</div>
    @else
        <div class="k-alert k-alert-success" role="status">
            @if ($r['satisfied']) This week’s backup is done. @else No backup is due yet. @endif
            Next reminder: {{ $r['next_reminder']->format('l, d M Y') }} ({{ $r['timezone'] }}).
        </div>
    @endif

    @if ($running && $runMode === 'queue')
        <div class="k-alert k-alert-info" role="status">
            A backup is waiting or running. If it stays “Pending” for more than a minute, the queue worker is not running:
            start it with <code>php artisan queue:work</code> on the server. This page refreshes automatically.
        </div>
    @endif

    <section class="k-card">
        <h2>How backups are stored</h2>
        <p class="muted">Backups are saved in a private folder on the server, outside the public web directory, with owner-only file permissions.
            Each file name contains the date and time. Downloads and restores are limited to authorised administrators and are recorded in the audit log.
            Backups contain confidential child information: keep downloaded copies in a secure place.</p>
    </section>

    <section class="k-card k-table-wrap">
        <div class="k-card-head"><h2>Backup history</h2></div>
        <table class="k-table">
            <thead>
                <tr><th>ID</th><th>File name</th><th>Created</th><th>Status</th><th>Size</th><th>Started by</th><th></th></tr>
            </thead>
            <tbody>
            @forelse ($backups as $b)
                <tr>
                    <td>{{ $b->id }}</td>
                    <td>
                        {{ $b->filename }}
                        @if ($b->type === 'pre_restore')<br><span class="muted">Safety copy taken before a restore</span>@endif
                        @if ($b->status === 'failed' && $b->error)
                            <details><summary class="muted" style="cursor: pointer">Error details</summary><span class="muted">{{ $b->error }}</span></details>
                        @endif
                    </td>
                    <td style="white-space: nowrap">{{ ($b->completed_at ?? $b->created_at)->format('d M Y, H:i') }}</td>
                    <td><span class="k-badge {{ $badge[$b->status] ?? 'k-badge-muted' }}">{{ ucfirst($b->status) }}</span></td>
                    <td>{{ $fmtSize($b->size_bytes) }}</td>
                    <td>{{ $b->initiator?->name ?? 'System' }}</td>
                    <td>
                        @if ($b->status === 'completed')
                            <div style="display: flex; gap: 8px; flex-wrap: wrap">
                                <a class="k-btn k-btn-secondary" href="{{ route('admin.backups.download', $b) }}">Download</a>
                                @if ($restoreEnabled)
                                    <a class="k-btn k-btn-secondary" href="{{ route('admin.backups.restore.form', $b) }}">Restore…</a>
                                @endif
                            </div>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7">No backups have been created yet.</td></tr>
            @endforelse
            </tbody>
        </table>
        {{ $backups->links() }}
    </section>

    @unless ($restoreEnabled)
        <p class="muted">Restore is switched off for safety. To use it, a server administrator must set <code>KFHI_BACKUP_RESTORE_ENABLED=true</code> in the <code>.env</code> file and requires MySQL or MariaDB.</p>
    @endunless
</div>

@if ($running)
    <script>setTimeout(function () { window.location.reload(); }, 5000);</script>
@endif
@endsection