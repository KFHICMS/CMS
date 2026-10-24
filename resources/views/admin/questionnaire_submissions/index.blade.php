<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Questionnaire Submissions | KFHI CMS</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; padding: 30px; background: #eef7f0; color: #294432; font-family: Arial, sans-serif; }
        main { max-width: 1200px; margin: auto; }
        .card { margin-top: 18px; padding: 18px; background: white; border: 1px solid #dce9df; border-radius: 10px; }
        a { color: #28733a; }
        .filters { display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 12px; align-items: end; }
        .filters label { display: block; font-size: 13px; color: #65756a; margin-bottom: 4px; }
        .filters select, .filters input { width: 100%; padding: 9px; border: 1px solid #bdcbbf; border-radius: 8px; font: inherit; }
        .btn { display: inline-block; padding: 10px 14px; color: white; background: #28733a; border: 0; border-radius: 8px; font: inherit; cursor: pointer; text-decoration: none; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px 8px; text-align: left; border-bottom: 1px solid #e6eee7; vertical-align: top; }
        .tag { display: inline-block; padding: 3px 9px; border-radius: 20px; font-size: 12px; background: #edf5ee; color: #28733a; }
        .tag.pending { background: #fff3d6; color: #8a6200; }
        .tag.rejected { background: #f8dcdc; color: #9a2a2a; }
        .muted { color: #65756a; }
        .scroll { overflow-x: auto; }
        @media (max-width: 700px) { body { padding: 16px; } }
    </style>
</head>
<body>
<main>
    <a href="{{ route('admin.dashboard') }}">← Back to dashboard</a>
    <h1>Questionnaire Submissions</h1>
    <p class="muted">Responses received through enumerator invitation links and from logged-in users. Pending review: <strong>{{ $pending }}</strong></p>

    @if ($errors->any()) <p role="alert">{{ $errors->first() }}</p> @endif

    <form method="GET" action="{{ route('admin_submissions.index') }}" class="card filters">
        <div>
            <label for="event_id">Event</label>
            <select id="event_id" name="event_id">
                <option value="">All events</option>
                @foreach ($events as $event)
                    <option value="{{ $event->id }}" @selected((int) request('event_id') === $event->id)>{{ $event->title }} · {{ $event->event_date->format('d M Y') }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="questionnaire_id">Questionnaire</label>
            <select id="questionnaire_id" name="questionnaire_id">
                <option value="">All questionnaires</option>
                @foreach ($questionnaires as $questionnaire)
                    <option value="{{ $questionnaire->id }}" @selected((int) request('questionnaire_id') === $questionnaire->id)>{{ $questionnaire->title }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="source">Source</label>
            <select id="source" name="source">
                <option value="">All sources</option>
                <option value="invitation" @selected(request('source') === 'invitation')>Invitation link</option>
                <option value="logged_in" @selected(request('source') === 'logged_in')>Logged-in user</option>
            </select>
        </div>
        <div>
            <label for="review_status">Review status</label>
            <select id="review_status" name="review_status">
                <option value="">All</option>
                @foreach (['pending', 'approved', 'rejected'] as $status)
                    <option value="{{ $status }}" @selected(request('review_status') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
        </div>
        <div><label for="from">From</label><input type="date" id="from" name="from" value="{{ request('from') }}"></div>
        <div><label for="to">To</label><input type="date" id="to" name="to" value="{{ request('to') }}"></div>
        <div>
            <button type="submit" class="btn">Apply filters</button>
            <a href="{{ route('admin_submissions.index') }}">Reset</a>
        </div>
    </form>

    <section class="card scroll">
        <table>
            <thead>
            <tr><th>Photo</th><th>Event</th><th>Questionnaire</th><th>Child</th><th>Submitted by</th><th>Source</th><th>Submitted</th><th>Review</th><th></th></tr>
            </thead>
            <tbody>
            @forelse ($submissions as $submission)
                <tr>
                    <td>
                        @if ($submission->photo_path)
                            <a href="{{ route('admin_submissions.show', $submission) }}">
                                <img src="{{ route('admin_submissions.photo', $submission) }}" alt="Photo" loading="lazy"
                                     width="48" height="48" style="object-fit:cover;border-radius:6px">
                            </a>
                        @else
                            —
                        @endif
                    </td>
                    <td>{{ $submission->questionnaire?->event?->title }}</td>
                    <td>{{ $submission->questionnaire?->title }}</td>
                    <td>{{ $submission->child?->child_code ?? 'New child' }}</td>
                    <td>{{ $submission->respondent_name ?: ($submission->submitter?->name ?? '—') }}</td>
                    <td>
                        @if ($submission->invitation_id)
                            Invitation link
                        @else
                            {{ $submission->submitter ? ucfirst(str_replace('_', ' ', $submission->submitter->role)) : '—' }}
                        @endif
                    </td>
                    <td>{{ \Illuminate\Support\Carbon::parse($submission->submitted_at)->format('d M Y H:i') }}</td>
                    <td><span class="tag {{ $submission->review_status }}">{{ ucfirst($submission->review_status) }}</span></td>
                    <td><a href="{{ route('admin_submissions.show', $submission) }}">Open</a></td>
                </tr>
            @empty
                <tr><td colspan="8">No questionnaire submissions match these filters.</td></tr>
            @endforelse
            </tbody>
        </table>
        {{ $submissions->links() }}
    </section>
</main>
</body>
</html>