<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Field Reports | KFHI CMS</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; padding: 24px; background: #f3f8f4; color: #294432; font-family: Arial, sans-serif; }
        main { max-width: 1100px; margin: auto; }
        .card { margin-top: 18px; padding: 20px; background: white; border: 1px solid #dfe9e1; border-radius: 12px; }
        form { display: grid; grid-template-columns: repeat(auto-fit, minmax(165px, 1fr)); align-items: end; gap: 12px; }
        label { display: block; margin-bottom: 5px; font-weight: 600; }
        input, select, button { width: 100%; padding: 10px; border: 1px solid #bdcbbf; border-radius: 7px; font: inherit; }
        button { color: white; background: #28733a; cursor: pointer; border: 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 11px 8px; border-bottom: 1px solid #e5ece6; text-align: left; vertical-align: top; }
        a { color: #28733a; }
        .scroll { overflow-x: auto; }
        .muted { color: #65756a; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 12px; margin-top: 18px; }
        .stat { padding: 14px; background: white; border: 1px solid #dfe9e1; border-radius: 12px; }
        .stat strong { display: block; font-size: 24px; }
        .stat span { color: #65756a; font-size: 13px; }
        @media (max-width: 700px) { body { padding: 14px; } }
    </style>
</head>
<body>
<main>
        @include('field_officer.partials.breadcrumbs', ['crumbs' => [
        ['label' => 'Dashboard', 'url' => route('field_officer.dashboard')],
        ['label' => 'Reports', 'url' => null],
    ]])
    <h1>My Field Reports</h1>
    <p class="muted">Reports include only activity records you created for programs currently assigned to you.</p>
    <section class="card">
        <form method="GET" action="{{ route('field_officer.reports.index') }}">
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
</main>
</body>
</html>
