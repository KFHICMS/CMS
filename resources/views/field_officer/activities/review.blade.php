<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Activity Record Review | KFHI CMS</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; padding: 24px; background: #f3f8f4; color: #294432; font-family: Arial, sans-serif; }
        main { max-width: 900px; margin: 0 auto; }
        .card { margin-top: 18px; padding: 22px; background: white; border: 1px solid #dfe9e1; border-radius: 12px; }
        .muted { color: #65756a; }
        .tag { display: inline-block; padding: 5px 9px; border-radius: 20px; background: #e8f4ea; color: #28733a; }
        dl { display: grid; grid-template-columns: 180px 1fr; gap: 10px; }
        dt { color: #718075; } dd { margin: 0; }
        li { margin: 7px 0; }
        a { color: #28733a; }
        @media (max-width: 580px) { body { padding: 14px; } dl { grid-template-columns: 1fr; gap: 4px; } dd { margin-bottom: 9px; } }
    </style>
</head>
<body>
<main>
        @include('field_officer.partials.breadcrumbs', ['crumbs' => [
        ['label' => 'Dashboard', 'url' => route('field_officer.dashboard')],
        ['label' => $event->program->name, 'url' => route('field_officer.programs.show', $event->program)],
        ['label' => $event->title, 'url' => route('field_officer.activities.show', $event)],
        ['label' => 'Review', 'url' => null],
    ]])
    @if (session('success')) <p class="card" role="status">{{ session('success') }}</p> @endif
    <section class="card">
        <p class="muted">{{ $event->program->name }} · {{ $event->event_code }}</p>
        <h1>{{ $event->title }} — submitted record review</h1>
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
</main>
</body>
</html>
