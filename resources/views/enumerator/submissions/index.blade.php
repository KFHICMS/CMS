@extends('enumerator.layouts.app', ['active' => 'submissions'])

@section('title', 'My Submissions')
@section('heading', 'My Submissions')
@section('subheading', 'Records you have submitted.')

@section('content')
    <style>
        .filters { display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 12px; align-items: end; }
        .filters label { display: block; font-size: 13px; color: #65756a; margin-bottom: 4px; }
        .filters select, .filters input[type=date] { width: 100%; padding: 10px; border: 1px solid #bdcbbf; border-radius: 8px; font: inherit; background: #fff; }
        .tabs a { display: inline-block; margin: 0 8px 8px 0; padding: 7px 12px; border-radius: 20px; background: #e8f4ea; color: #236b3b; text-decoration: none; font-size: 14px; }
        .tabs a.current { background: #28733a; color: #fff; }
        .scroll { overflow-x: auto; }
    </style>

    <section class="section">
        <div class="grid">
            <div class="card stat"><strong>{{ $stats['questionnaires'] }}</strong><span>Questionnaires submitted</span></div>
            <div class="card stat"><strong>{{ $stats['attendance'] }}</strong><span>Attendance records</span></div>
            <div class="card stat"><strong>{{ $stats['present'] }} / {{ $stats['absent'] }}</strong><span>Present / Absent</span></div>
            <div class="card stat"><strong>{{ $stats['events'] }}</strong><span>Events worked on</span></div>
        </div>
    </section>

    <section class="section">
        <p class="tabs">
            <a @class(['current' => $tab === 'questionnaires']) href="{{ route('enumerator.submissions.index', ['tab' => 'questionnaires']) }}">Questionnaires ({{ $stats['questionnaires'] }})</a>
            <a @class(['current' => $tab === 'attendance']) href="{{ route('enumerator.submissions.index', ['tab' => 'attendance']) }}">Attendance ({{ $stats['attendance'] }})</a>
        </p>

        @if ($errors->any())
            <p class="notice" role="alert">{{ $errors->first() }}</p>
        @endif

        <form method="GET" action="{{ route('enumerator.submissions.index') }}" class="card filters">
            <input type="hidden" name="tab" value="{{ $tab }}">

            <div>
                <label for="event_id">Event</label>
                <select id="event_id" name="event_id">
                    <option value="">All events</option>
                    @foreach ($events as $event)
                        <option value="{{ $event->id }}" @selected((int) request('event_id') === $event->id)>{{ $event->title }} · {{ $event->event_date->format('M j, Y') }}</option>
                    @endforeach
                </select>
            </div>

            @if ($tab === 'questionnaires')
                <div>
                    <label for="questionnaire_id">Questionnaire</label>
                    <select id="questionnaire_id" name="questionnaire_id">
                        <option value="">All questionnaires</option>
                        @foreach ($questionnaires as $questionnaire)
                            <option value="{{ $questionnaire->id }}" @selected((int) request('questionnaire_id') === $questionnaire->id)>{{ $questionnaire->title }}</option>
                        @endforeach
                    </select>
                </div>
            @else
                <div>
                    <label for="status">Attendance status</label>
                    <select id="status" name="status">
                        <option value="">Present and absent</option>
                        <option value="present" @selected(request('status') === 'present')>Present</option>
                        <option value="absent" @selected(request('status') === 'absent')>Absent</option>
                    </select>
                </div>
            @endif

            <div>
                <label for="from">From</label>
                <input type="date" id="from" name="from" value="{{ request('from') }}">
            </div>
            <div>
                <label for="to">To</label>
                <input type="date" id="to" name="to" value="{{ request('to') }}">
            </div>
            <div>
                <button type="submit" class="btn">Apply filters</button>
                <a href="{{ route('enumerator.submissions.index', ['tab' => $tab]) }}">Reset</a>
            </div>
        </form>
    </section>

    <section class="section">
        <div class="card scroll">
            @if ($tab === 'attendance')
                @if ($records->isEmpty())
                    <p class="muted">No attendance records match these filters.</p>
                @else
                    <table>
                        <thead><tr><th>Event</th><th>Child code</th><th>Status</th><th>Recorded</th></tr></thead>
                        <tbody>
                        @foreach ($records as $record)
                            <tr>
                                <td>{{ $record->event?->title }}</td>
                                <td>{{ $record->child?->child_code }}</td>
                                <td>{{ ucfirst($record->status) }}</td>
                                <td>{{ $record->attendance_time->format('M j, Y g:i A') }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @endif
            @else
                @if ($records->isEmpty())
                    <p class="muted">No questionnaire submissions match these filters.</p>
                @else
                    <table>
                        <thead><tr><th>Questionnaire</th><th>Event</th><th>Child code</th><th>Submitted</th><th>Status</th><th></th></tr></thead>
                        <tbody>
                        @foreach ($records as $record)
                            <tr>
                                <td>{{ $record->questionnaire?->title }}</td>
                                <td>{{ $record->questionnaire?->event?->title }}</td>
                                <td>{{ $record->child?->child_code }}</td>
                                <td>{{ $record->submitted_at->format('M j, Y g:i A') }}</td>
                                <td><span class="tag">Submitted</span></td>
                                <td><a href="{{ route('enumerator.submissions.show', $record) }}">Review</a></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @endif
            @endif
            {{ $records->links() }}
        </div>
    </section>
@endsection