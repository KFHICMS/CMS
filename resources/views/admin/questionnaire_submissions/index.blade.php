@extends('layouts.app', [
    'active' => 'submissions',
    'crumbs' => [
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Questionnaire Submissions'],
    ],
])

@section('title', 'Questionnaire Submissions')
@section('heading', 'Questionnaire Submissions')
@section('subheading', 'Responses received through enumerator invitation links and from logged-in users.')

@section('content')
    @if ($errors->any())
        <div class="k-alert k-alert-error" role="alert">{{ $errors->first() }}</div>
    @endif

    <div class="k-stack">
        <div class="k-card">
            Pending review: <strong>{{ $pending }}</strong>
        </div>

        <form method="GET" action="{{ route('admin_submissions.index') }}" class="k-card filters">
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
            <div>
                <label for="from">From</label>
                <input type="date" id="from" name="from" value="{{ request('from') }}">
            </div>
            <div>
                <label for="to">To</label>
                <input type="date" id="to" name="to" value="{{ request('to') }}">
            </div>
            <div>
                <button type="submit" class="k-btn k-btn-primary">Apply filters</button>
                <a href="{{ route('admin_submissions.index') }}">Reset</a>
            </div>
        </form>

        <section class="k-card k-table-wrap">
            <table class="k-table">
                <thead>
                <tr><th>Photo</th><th>Event</th><th>Questionnaire</th><th>Child</th><th>Submitted by</th><th>Source</th><th>Submitted</th><th>Review</th><th></th></tr>
                </thead>
                <tbody>
                @forelse ($submissions as $submission)
                    @php($badge = ['pending' => 'k-badge-warning', 'rejected' => 'k-badge-error'][$submission->review_status] ?? '')
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
                        <td><span class="k-badge {{ $badge }}">{{ ucfirst($submission->review_status) }}</span></td>
                        <td><a href="{{ route('admin_submissions.show', $submission) }}">Open</a></td>
                    </tr>
                @empty
                    <tr><td colspan="9">No questionnaire submissions match these filters.</td></tr>
                @endforelse
                </tbody>
            </table>
            {{ $submissions->links() }}
        </section>
    </div>
@endsection