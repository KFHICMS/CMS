@extends('layouts.app', [
    'active' => 'reports',
    'crumbs' => [
        ['label' => 'Dashboard', 'url' => route('field_officer.dashboard')],
        ['label' => 'Reports'],
    ],
])

@section('title', 'My Field Reports')
@section('heading', 'My Field Reports')
@section('subheading', 'Reports include only activity records you created for programs currently assigned to you.')

@section('content')
 
    <section class="card">
        <form method="GET" action="{{ route('field_officer.reports.index') }}" class="filters">
            <div><label for="from">Activity date from</label><input id="from" type="date" name="from" value="{{ $validated['from'] ?? '' }}"></div>
            <div><label for="to">Activity date to</label><input id="to" type="date" name="to" value="{{ $validated['to'] ?? '' }}"></div>
            <div><label for="program_id">Program</label><select id="program_id" name="program_id">
                <option value="">All assigned programs</option>
                @foreach ($programs as $program)
                    <option value="{{ $program->id }}" @selected(($validated['program_id'] ?? '') == $program->id)>{{ $program->name }}</option>
                @endforeach
            </select></div>
            <div><label for="event_id">Activity</label><select id="event_id" name="event_id">
                <option value="">All assigned activities</option>
                @foreach ($events as $event)
                    <option value="{{ $event->id }}" @selected(($validated['event_id'] ?? '') == $event->id)>{{ $event->title }}</option>
                @endforeach
            </select></div>
            <div><label for="status">Submission status</label><select id="status" name="status">
                <option value="">All statuses</option>
                @foreach (['draft', 'submitted'] as $status)
                    <option value="{{ $status }}" @selected(($validated['status'] ?? '') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select></div>
            <div><button type="submit">Filter reports</button></div>
        </form>
    </section>

    <section class="stats">
        <div class="stat"><strong>{{ $totals['records'] }}</strong><span>Records</span></div>
        <div class="stat"><strong>{{ $totals['submitted'] }}</strong><span>Submitted</span></div>
        <div class="stat"><strong>{{ $totals['drafts'] }}</strong><span>Pending submission</span></div>
        <div class="stat"><strong>{{ $totals['present'] }}</strong><span>Present</span></div>
        <div class="stat"><strong>{{ $totals['absent'] }}</strong><span>Absent</span></div>
        <div class="stat"><strong>{{ $totals['benefit_items'] }}</strong><span>Benefit items given</span></div>
        <div class="stat"><strong>{{ $totals['observations'] }}</strong><span>Field notes</span></div>
    </section>
    <p><a href="{{ route('field_officer.reports.export', request()->query()) }}">Download CSV (uses the filters above)</a></p>
    
    <section class="card scroll">
        <table>
            <thead><tr><th>Activity</th><th>Program</th><th>Child Code</th><th>Attendance</th><th>Benefits</th><th>Notes</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse ($submissions as $submission)
                <tr>
                    <td>{{ $submission->event->title }}<br><small>{{ $submission->event->event_date->format('M j, Y') }}</small></td>
                    <td>{{ $submission->event->program->name }}</td>
                    <td>{{ $submission->child->child_code }}</td>
                    <td>{{ ucfirst($submission->attendance_status ?? '—') }}</td>
                    <td>{{ $submission->benefits->count() }}</td>
                    <td>{{ $submission->observations->count() }}</td>
                    <td>{{ ucfirst($submission->status) }}</td>
                    <td><a href="{{ route('field_officer.submissions.review', $submission) }}">Review</a></td>
                </tr>
            @empty
                <tr><td colspan="8">No field records match these filters.</td></tr>
            @endforelse
            </tbody>
        </table>
        {{ $submissions->links() }}
    </section>
@endsection
