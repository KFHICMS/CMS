<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Submission | KFHI CMS</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; padding: 30px; background: #eef7f0; color: #294432; font-family: Arial, sans-serif; }
        main { max-width: 900px; margin: auto; }
        .card { margin-top: 18px; padding: 20px; background: white; border: 1px solid #dce9df; border-radius: 10px; }
        a { color: #28733a; }
        label { display: block; margin: 12px 0 4px; font-weight: 600; }
        select, textarea { width: 100%; padding: 10px; border: 1px solid #bdcbbf; border-radius: 7px; font: inherit; }
        button { margin-top: 14px; padding: 11px 16px; color: white; background: #28733a; border: 0; border-radius: 8px; font: inherit; cursor: pointer; }
        .tag { display: inline-block; padding: 3px 9px; border-radius: 20px; font-size: 12px; background: #edf5ee; color: #28733a; }
        .tag.pending { background: #fff3d6; color: #8a6200; }
        .tag.rejected { background: #f8dcdc; color: #9a2a2a; }
        .muted { color: #65756a; }
        .error { color: #a12626; }
        .answer { padding: 10px 0; border-top: 1px solid #e3ede6; }
        @media (max-width: 700px) { body { padding: 16px; } }
    </style>
</head>
<body>
<main>
    <a href="{{ route('admin_submissions.index') }}">← Back to submissions</a>
    <h1>{{ $submission->questionnaire?->title }}</h1>

    @if (session('success')) <p role="status">{{ session('success') }}</p> @endif
    @if ($errors->any())
        <div class="error" role="alert">@foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
    @endif

    <section class="card">
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
        <p><strong>Review status:</strong> <span class="tag {{ $submission->review_status }}">{{ ucfirst($submission->review_status) }}</span></p>
        @if ($submission->reviewed_at)
            <p class="muted">Reviewed {{ \Illuminate\Support\Carbon::parse($submission->reviewed_at)->format('d M Y H:i') }}
                @if ($reviewerName) by {{ $reviewerName }} @endif
            </p>
        @endif
        <p class="muted">Record ID: {{ $submission->response_uuid }}</p>
    </section>

    @if ($submission->photo_path)
        <section class="card">
            <h2>Photograph</h2>
            <img src="{{ route('admin_submissions.photo', $submission) }}"
                 alt="Photograph submitted for this record"
                 style="max-width:100%;max-height:420px;border-radius:8px">
        </section>
    @endif

    <section class="card">
        <h2>Answers</h2>
        @forelse ($answers as $answer)
            <div class="answer">
                <strong>{{ $answer->question?->question_text ?? 'Question no longer available' }}</strong>
                @if (is_array($answer->answer_json))
                    <p>{{ implode(', ', $answer->answer_json) }}</p>
                @elseif (filled($answer->answer_text))
                    <p style="white-space: pre-line">{{ $answer->answer_text }}</p>
                @else
                    <p class="muted">Not answered</p>
                @endif
            </div>
        @empty
            <p class="muted">No answers were recorded.</p>
        @endforelse
    </section>

    <section class="card">
        <h2>Review</h2>
        <form method="POST" action="{{ route('admin_submissions.review', $submission) }}">
            @csrf
            @method('PATCH')
            <label for="review_status">Status</label>
            <select id="review_status" name="review_status" required>
                @foreach (['pending', 'approved', 'rejected'] as $status)
                    <option value="{{ $status }}" @selected(old('review_status', $submission->review_status) === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
            <label for="review_note">Note (optional)</label>
            <textarea id="review_note" name="review_note" rows="3" maxlength="1000">{{ old('review_note', $submission->review_note) }}</textarea>
            <button type="submit">Save review</button>
        </form>
    </section>
</main>
</body>
</html>