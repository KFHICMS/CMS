@extends('enumerator.layouts.app', ['active' => 'submissions'])

@section('title', 'Review submission')
@section('heading', $submission->questionnaire?->title ?? 'Questionnaire submission')
@section('subheading', 'Submitted '.$submission->submitted_at->format('M j, Y g:i A'))

@section('content')
    <p style="margin-top:18px">
        <a href="{{ route('enumerator.dashboard') }}">Dashboard</a> ›
        <a href="{{ route('enumerator.submissions.index') }}">My Submissions</a> › Review
    </p>

    <section class="section">
        <div class="card">
            <p><strong>Event:</strong> {{ $submission->questionnaire?->event?->title ?? '—' }}</p>
            <p><strong>Child:</strong> {{ $submission->child?->child_code }}
                @if ($submission->child)
                    · {{ trim(implode(' ', array_filter([$submission->child->first_name, $submission->child->middle_name, $submission->child->last_name]))) }}
                @endif
            </p>
            <p><strong>Status:</strong> <span class="tag">Submitted</span></p>
            <p class="muted">Record ID: {{ $submission->response_uuid }}</p>
            <p class="muted">Submitted responses cannot be edited. Contact your administrator if a correction is needed.</p>
        </div>
    </section>

    <section class="section">
        <h2>Answers</h2>
        <div class="grid" style="grid-template-columns: 1fr">
            @forelse ($answers as $answer)
                <article class="card">
                    <strong>{{ $answer->question?->question_text ?? 'Question no longer available' }}</strong>
                    @if (is_array($answer->answer_json))
                        <p>{{ implode(', ', $answer->answer_json) }}</p>
                    @elseif (filled($answer->answer_text))
                        <p style="white-space: pre-line">{{ $answer->answer_text }}</p>
                    @else
                        <p class="muted">Not answered</p>
                    @endif
                </article>
            @empty
                <div class="empty">No answers were recorded for this submission.</div>
            @endforelse
        </div>
    </section>
@endsection