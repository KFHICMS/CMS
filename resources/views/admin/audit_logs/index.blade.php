@extends('layouts.app', [
    'active' => 'audit_logs',
    'crumbs' => [
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Audit Logs'],
    ],
])

@section('title', 'Audit Logs')
@section('heading', 'Audit Logs')
@section('subheading', 'A read-only record of who did what in the system. Entries cannot be edited or deleted from here.')

@section('actions')
<a class="k-btn k-btn-secondary" href="{{ route('admin.audit_logs.export', array_filter($filters)) }}">Download CSV</a>
@endsection

@section('content')
<div class="k-stack">

    <form class="k-card filters" method="GET" action="{{ route('admin.audit_logs.index') }}" aria-label="Audit log filters">
        <div>
            <label for="q">Search</label>
            <input type="search" id="q" name="q" class="k-input" maxlength="100" value="{{ $filters['q'] ?? '' }}" placeholder="Description, action or module">
        </div>
        <div>
            <label for="from">From</label>
            <input type="date" id="from" name="from" class="k-input" value="{{ $filters['from'] ?? '' }}">
        </div>
        <div>
            <label for="to">To</label>
            <input type="date" id="to" name="to" class="k-input" value="{{ $filters['to'] ?? '' }}">
        </div>
        <div>
            <label for="user_id">User</label>
            <select id="user_id" name="user_id" class="k-select">
                <option value="">All users</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}" @selected(($filters['user_id'] ?? null) == $user->id)>{{ $user->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="module">Module</label>
            <select id="module" name="module" class="k-select">
                <option value="">All modules</option>
                @foreach ($modules as $module)
                    <option value="{{ $module }}" @selected(($filters['module'] ?? null) === $module)>{{ str_replace('_', ' ', $module) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="action">Action</label>
            <select id="action" name="action" class="k-select">
                <option value="">All actions</option>
                @foreach ($actions as $action)
                    <option value="{{ $action }}" @selected(($filters['action'] ?? null) === $action)>{{ str_replace('_', ' ', $action) }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="k-btn k-btn-primary">Apply filters</button>
        <a class="k-btn k-btn-secondary" href="{{ route('admin.audit_logs.index') }}">Reset</a>
    </form>

    @if ($errors->any())
        <div class="k-alert k-alert-error" role="alert">
            @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif

    <p style="margin: 0"><strong>{{ number_format($logs->total()) }}</strong> {{ \Illuminate\Support\Str::plural('entry', $logs->total()) }} found</p>

    <section class="k-card k-table-wrap">
        <table class="k-table">
            <thead>
                <tr><th>Date and time</th><th>User</th><th>Action</th><th>Module</th><th>Record</th><th>Description</th><th>IP address</th></tr>
            </thead>
            <tbody>
            @forelse ($logs as $log)
                @php($attention = str_contains($log->action, 'failed') || str_contains($log->action, 'duplicate'))
                <tr>
                    <td style="white-space: nowrap">{{ $log->created_at?->format('d M Y, H:i:s') }}</td>
                    <td>
                        @if ($log->user)
                            {{ $log->user->name }}<br><span class="muted">{{ str_replace('_', ' ', $log->user->role) }}</span>
                        @else
                            <span class="muted">System / removed user</span>
                        @endif
                    </td>
                    <td><span class="k-badge {{ $attention ? 'k-badge-warning' : '' }}" style="white-space: nowrap">{{ str_replace('_', ' ', $log->action) }}@if ($attention) (needs attention)@endif</span></td>
                    <td><span class="k-badge k-badge-muted" style="white-space: nowrap">{{ str_replace('_', ' ', $log->module) }}</span></td>
                    <td>{{ $log->record_id ?? '—' }}</td>
                    <td>
                        {{ $log->description ?: '—' }}
                        @if ($log->user_agent)
                            <details><summary class="muted" style="cursor: pointer">Device details</summary><span class="muted">{{ \Illuminate\Support\Str::limit($log->user_agent, 200) }}</span></details>
                        @endif
                    </td>
                    <td>{{ $log->ip_address ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="7">No audit entries match these filters.</td></tr>
            @endforelse
            </tbody>
        </table>
        {{ $logs->links() }}
    </section>
</div>
@endsection