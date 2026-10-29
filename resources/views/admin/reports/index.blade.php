@extends('layouts.app', [
    'active' => 'reports',
    'crumbs' => [
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Reports'],
    ],
])

@section('title', 'Reports')
@section('heading', 'Reports')
@section('subheading', 'Organisation-wide figures drawn live from the database.')

@section('content')
@php($query = array_filter($filters))
<div class="k-stack">

    <form class="k-card filters" method="GET" action="{{ route('admin.reports.index') }}" aria-label="Report filters">
        <div>
            <label for="from">From</label>
            <input type="date" id="from" name="from" class="k-input" value="{{ $filters['from'] ?? '' }}">
        </div>
        <div>
            <label for="to">To</label>
            <input type="date" id="to" name="to" class="k-input" value="{{ $filters['to'] ?? '' }}">
        </div>
        <div>
            <label for="program_id">Program</label>
            <select id="program_id" name="program_id" class="k-select">
                <option value="">All programs</option>
                @foreach ($programs as $program)
                    <option value="{{ $program->id }}" @selected(($filters['program_id'] ?? null) == $program->id)>{{ $program->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="k-btn k-btn-primary">Apply filters</button>
        <a class="k-btn k-btn-secondary" href="{{ route('admin.reports.index') }}">Reset</a>
    </form>

    @if ($errors->any())
        <div class="k-alert k-alert-error" role="alert">
            @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif

    <p class="muted" style="margin: 0">
        Counting rules: events, attendance, questionnaire responses and field submissions follow the <strong>event date</strong> range and program filter.
        “Children registered” follows the registration date only, because children are not tied to a program. Deleted records are excluded.
    </p>

    <section aria-labelledby="summary-h">
        <h2 id="summary-h" style="margin: 0 0 12px; font-size: 20px">Summary</h2>
        <div class="k-stat-grid">
            <div class="k-stat-card"><div><strong>{{ number_format($totals['children_registered']) }}</strong><span class="k-stat-label">Children registered in period</span></div></div>
            <div class="k-stat-card"><div><strong>{{ number_format($totals['children_active']) }}</strong><span class="k-stat-label">Active children (all time)</span></div></div>
            <div class="k-stat-card"><div><strong>{{ number_format($totals['events']) }}</strong><span class="k-stat-label">Events</span></div></div>
            <div class="k-stat-card"><div><strong>{{ number_format($totals['present']) }}</strong><span class="k-stat-label">Attendance: present</span></div></div>
            <div class="k-stat-card"><div><strong>{{ number_format($totals['absent']) }}</strong><span class="k-stat-label">Attendance: absent</span></div></div>
            <div class="k-stat-card"><div><strong>{{ number_format($totals['responses']) }}</strong><span class="k-stat-label">Questionnaire responses</span></div></div>
            <div class="k-stat-card"><div><strong>{{ number_format($totals['field_submitted']) }}</strong><span class="k-stat-label">Field records submitted</span></div></div>
        </div>
    </section>

    <section class="k-card k-table-wrap" aria-labelledby="part-h">
        <div class="k-card-head">
            <h2 id="part-h">Program participation</h2>
            <a class="k-btn k-btn-secondary" href="{{ route('admin.reports.export', $query + ['report' => 'participation']) }}">Download CSV</a>
        </div>
        <table class="k-table">
            <thead><tr><th>Program</th><th>Status</th><th>Events</th><th>Present</th><th>Absent</th><th>Attendance rate</th><th>Field records submitted</th></tr></thead>
            <tbody>
            @forelse ($participation as $row)
                <tr>
                    <td>{{ $row['name'] }}</td>
                    <td><span class="k-badge">{{ ucfirst($row['status']) }}</span></td>
                    <td>{{ $row['events'] }}</td>
                    <td>{{ $row['present'] }}</td>
                    <td>{{ $row['absent'] }}</td>
                    <td>{{ is_null($row['rate']) ? 'No data' : $row['rate'].'%' }}</td>
                    <td>{{ $row['submitted'] }}</td>
                </tr>
            @empty
                <tr><td colspan="7">No programs match these filters.</td></tr>
            @endforelse
            </tbody>
        </table>
    </section>

    <section class="k-card k-table-wrap" aria-labelledby="events-h">
        <div class="k-card-head">
            <h2 id="events-h">Events and attendance</h2>
            <a class="k-btn k-btn-secondary" href="{{ route('admin.reports.export', $query + ['report' => 'events']) }}">Download CSV</a>
        </div>
        <table class="k-table">
            <thead><tr><th>Event</th><th>Date</th><th>Program</th><th>Status</th><th>Present</th><th>Absent</th><th>Field records submitted</th></tr></thead>
            <tbody>
            @forelse ($eventRows as $e)
                <tr>
                    <td>{{ $e->title }} <span class="muted">({{ $e->event_code }})</span></td>
                    <td>{{ $e->event_date?->format('d M Y') }}</td>
                    <td>{{ $e->program?->name ?? 'Not assigned' }}</td>
                    <td><span class="k-badge">{{ ucfirst($e->status) }}</span></td>
                    <td>{{ $e->present_count }}</td>
                    <td>{{ $e->absent_count }}</td>
                    <td>{{ $e->submitted_count }}</td>
                </tr>
            @empty
                <tr><td colspan="7">No events found for these filters.</td></tr>
            @endforelse
            </tbody>
        </table>
        {{ $eventRows->links() }}
    </section>

    <section class="k-card k-table-wrap" style="max-width: 520px" aria-labelledby="reg-h">
        <div class="k-card-head">
            <h2 id="reg-h">Child registrations by month</h2>
            <a class="k-btn k-btn-secondary" href="{{ route('admin.reports.export', \Illuminate\Support\Arr::only($query, ['from', 'to']) + ['report' => 'registrations']) }}">Download CSV</a>
        </div>
        <table class="k-table">
            <thead><tr><th>Month</th><th>Children registered</th></tr></thead>
            <tbody>
            @forelse ($registrations as $r)
                <tr><td>{{ \Illuminate\Support\Carbon::createFromFormat('Y-m', $r->month)->format('F Y') }}</td><td>{{ $r->total }}</td></tr>
            @empty
                <tr><td colspan="2">No children were registered in this period.</td></tr>
            @endforelse
            </tbody>
        </table>
        <p class="muted" style="margin-bottom: 0">Showing the most recent 24 months; the CSV contains all months.</p>
    </section>
</div>
@endsection
