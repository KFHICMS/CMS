@extends('layouts.app', [
    'active' => 'activities',
    'crumbs' => [
        ['label' => 'Dashboard', 'url' => route('field_officer.dashboard')],
        ['label' => 'My Programs', 'url' => route('field_officer.programs.index')],
        ['label' => $event->program->name, 'url' => route('field_officer.programs.show', $event->program)],
        ['label' => $event->title, 'url' => route('field_officer.activities.show', $event)],
        ['label' => 'Review'],
    ],
])

@section('title', 'Submitted record review')
@section('heading', $event->title.' — submitted record review')
@section('subheading', $event->program->name.' · '.$event->event_code)

@section('content')
    <section class="card">
       
        <p><span class="tag">{{ ucfirst($submission->status) }}</span></p>
        @if ($submission->corrections->isNotEmpty())
            @php($latestCorrection = $submission->corrections->first())
            <p class="muted" role="note">
                Corrected by an administrator{{ $submission->corrections->count() > 1 ? ' ('.$submission->corrections->count().' times)' : '' }},
                last on {{ $latestCorrection->created_at->format('M j, Y g:i A') }}.
                Reason: {{ $latestCorrection->reason }}
            </p>
        @endif
        <dl>
            <dt>Child Code</dt><dd>{{ $submission->child->child_code }}</dd>
            <dt>Child Name</dt>
            <dd>{{ trim(implode(' ', array_filter([
                $submission->child->first_name,
                $submission->child->middle_name,
                $submission->child->last_name,
            ]))) }}</dd>
            <dt>Attendance</dt><dd>{{ ucfirst($attendance?->status ?? 'Not recorded') }}
                @if ($attendance?->attendance_time)
                    · {{ $attendance->attendance_time->format('M j, Y g:i A') }}
                @endif
            </dd>
            <dt>Submitted at</dt><dd>{{ $submission->submitted_at?->format('M j, Y g:i A') ?? 'Not submitted' }}</dd>
        </dl>
    </section>
    <section class="card">
        <h2>Benefits / items</h2>
        @forelse ($submission->benefits as $benefit)
            <article>
                <strong>{{ $benefit->item_name }}</strong>
                <p>{{ $benefit->category ?: 'Uncategorized' }} · Quantity {{ $benefit->quantity }}
                    @if ($benefit->received_at) · {{ $benefit->received_at->format('M j, Y') }} @endif
                </p>
                @if ($benefit->notes) <p>{{ $benefit->notes }}</p> @endif
            </article>
        @empty
            <p class="muted">No benefits recorded.</p>
        @endforelse
    </section>
    <section class="card">
        <h2>Observations / field notes</h2>
        @forelse ($submission->observations as $observation)
            <article>
                <p class="muted">{{ $observation->category ?: 'General observation' }} · {{ $observation->observed_at->format('M j, Y g:i A') }}</p>
                <p style="white-space:pre-wrap">{{ $observation->note }}</p>
            </article>
        @empty
            <p class="muted">No observations recorded.</p>
        @endforelse
    </section>
    @if ($submission->status === 'draft')
        <section class="card">
            <a href="{{ route('field_officer.activities.show', ['event' => $event, 'submission' => $submission->id]) }}">Continue editing this draft</a>
        </section>
    @endif
@endsection
