@extends('layouts.app', [
    'active' => 'submissions',
    'crumbs' => [
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Questionnaire Submissions', 'url' => route('admin_submissions.index')],
        ['label' => 'Submission'],
    ],
])

@section('title', 'Submission')
@section('heading', $submission->questionnaire?->title ?? 'Questionnaire submission')
@section('subheading', $submission->questionnaire?->event?->title)

@section('content')
    @if ($errors->any())
        <div class="k-alert k-alert-error" role="alert">
            @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif

    @php($badge = ['pending' => 'k-badge-warning', 'rejected' => 'k-badge-error'][$submission->review_status] ?? '')

    <div class="k-stack">
        <section class="k-card">
            <p><strong>Event:</strong> {{ $submission->questionnaire?->event?->title }}
                @if ($submission->questionnaire?->event?->event_date) · {{ $submission->questionnaire->event->event_date->format('d M Y') }} @endif
            </p>
            <p><strong>Child:</strong>
                @if ($submission->child)
                    {{ $submission->child->child_code }} ·
                    {{ trim(implode(' ', array_filter([$submission->child->first_name, $submission->child->middle_name, $submission->child->last_name]))) }}
                @else
                    New child (not linked to an existing record)
                @endif
            </p>
            <p><strong>Submitted by:</strong> {{ $submission->respondent_name ?: ($submission->submitter?->name ?? '—') }}
                ({{ $submission->invitation_id ? 'Invitation link' : ($submission->submitter ? ucfirst(str_replace('_', ' ', $submission->submitter->role)) : 'Unknown source') }})
            </p>
            <p><strong>Submitted:</strong> {{ \Illuminate\Support\Carbon::parse($submission->submitted_at)->format('d M Y H:i') }}</p>
            <p><strong>Review status:</strong> <span class="k-badge {{ $badge }}">{{ ucfirst($submission->review_status) }}</span></p>
            @if ($submission->reviewed_at)
                <p class="muted">Reviewed {{ \Illuminate\Support\Carbon::parse($submission->reviewed_at)->format('d M Y H:i') }}
                    @if ($reviewerName) by {{ $reviewerName }} @endif
                </p>
            @endif
            <p class="muted">Record ID: {{ $submission->response_uuid }}</p>
            <p>
                @if ($submission->review_status === 'approved')
                    <span class="muted">Answers are locked while the submission is approved. Set the review status to Pending below to edit them.</span>
                @else
                    <a class="k-btn k-btn-primary" href="{{ route('admin_submissions.edit', $submission) }}">Edit answers</a>
                @endif
            </p>
        </section>

        @if ($submission->photo_path)
            <section class="k-card">
                <h2>Photograph</h2>
                <img src="{{ route('admin_submissions.photo', $submission) }}"
                     alt="Photograph submitted for this record"
                     style="max-width:100%;max-height:420px;border-radius:8px">
            </section>
        @endif

        <section class="k-card">
            <h2>Answers</h2>
            @forelse ($answers as $answer)
                <div class="k-activity-item">
                    <strong>{{ $answer->question?->question_text ?? 'Question no longer available' }}</strong>
                    @if (is_array($answer->answer_json))
                        <p>{{ implode(', ', $answer->answer_json) }}</p>
                    @elseif (filled($answer->answer_text))
                        <p style="white-space: pre-line">{{ $answer->answer_text }}</p>
                    @else
                        <p>Not answered</p>
                    @endif
                </div>
            @empty
                <p class="muted">No answers were recorded.</p>
            @endforelse
        </section>

        <section class="k-card">
            <h2>Review</h2>
            <form method="POST" action="{{ route('admin_submissions.review', $submission) }}">
                @csrf
                @method('PATCH')
                <div class="k-field">
                    <label for="review_status">Status</label>
                    <select id="review_status" name="review_status" class="k-select" required>
                        @foreach (['pending', 'approved', 'rejected'] as $status)
                            <option value="{{ $status }}" @selected(old('review_status', $submission->review_status) === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="k-field">
                    <label for="review_note">Note (optional)</label>
                    <textarea id="review_note" name="review_note" class="k-textarea" rows="3" maxlength="1000">{{ old('review_note', $submission->review_note) }}</textarea>
                </div>
                <button type="submit" class="k-btn k-btn-primary">Save review</button>
            </form>
        </section>

        <section class="k-card">
            <h2>Edit history</h2>
            @php($shorten = fn ($value) => \Illuminate\Support\Str::limit(
                is_array($value) ? implode(', ', $value) : (filled($value) ? $value : '—'), 300
            ))
            @forelse ($submission->edits as $edit)
                <div class="k-activity-item">
                    <strong>{{ $edit->created_at->format('d M Y H:i') }}</strong> by {{ $edit->editor?->name ?? 'Unknown user' }}
                    <p>Reason: {{ $edit->reason }}</p>
                    <ul>
                        @foreach ($edit->changes as $change)
                            <li>
                                <strong>{{ $change['question'] }}</strong>:
                                {{ $shorten($change['old']) }} → {{ $shorten($change['new']) }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            @empty
                <p class="muted">No answers have been edited.</p>
            @endforelse
        </section>
    </div>
@endsection