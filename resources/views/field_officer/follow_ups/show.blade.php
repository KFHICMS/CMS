<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Assigned Follow-up | KFHI CMS</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; padding: 24px; background: #f3f8f4; color: #294432; font-family: Arial, sans-serif; }
        main { max-width: 760px; margin: auto; }
        .card { margin-top: 18px; padding: 22px; background: white; border: 1px solid #dfe9e1; border-radius: 12px; }
        label { display: block; margin: 14px 0 6px; font-weight: 600; }
        input, select, textarea { width: 100%; padding: 10px; border: 1px solid #bdcbbf; border-radius: 7px; font: inherit; }
        textarea { min-height: 140px; }
        button { margin-top: 16px; padding: 10px 14px; border: 0; border-radius: 8px; color: white; background: #28733a; cursor: pointer; }
        .muted { color: #65756a; }
        .error { color: #a94343; }
        a { color: #28733a; }
        @media (max-width: 560px) { body { padding: 14px; } }
    </style>
</head>
<body>
<main>
        @include('field_officer.partials.breadcrumbs', ['crumbs' => [
        ['label' => 'Dashboard', 'url' => route('field_officer.dashboard')],
        ['label' => 'Follow-ups', 'url' => route('field_officer.follow_ups.index')],
        ['label' => $followUp->category ?: 'Follow-up', 'url' => null],
    ]])
    @if (session('success')) <p class="card" role="status">{{ session('success') }}</p> @endif
    <section class="card">
        <p class="muted">Assigned child follow-up</p>
        <h1>{{ $followUp->category ?: 'Follow-up' }}</h1>
        <p><strong>Child Code:</strong> {{ $followUp->child->child_code }}</p>
        <p><strong>Child:</strong> {{ trim(implode(' ', array_filter([
            $followUp->child->first_name,
            $followUp->child->middle_name,
            $followUp->child->last_name,
        ]))) }}</p>
        <p><strong>Priority:</strong> {{ ucfirst($followUp->priority) }}</p>
        @if ($followUp->event)
            <p><strong>Related activity:</strong>
                @if ($canOpenActivity)
                    <a href="{{ route('field_officer.activities.show', $followUp->event) }}">{{ $followUp->event->title }}</a>
                @else
                    {{ $followUp->event->title }}
                @endif
                ({{ $followUp->event->event_date->format('M j, Y') }})
            </p>
        @endif
        <p><strong>Instructions:</strong> {{ $followUp->description ?: 'No instructions provided.' }}</p>
        <p><strong>Due date:</strong> {{ $followUp->next_follow_up_date?->format('M j, Y')
            ?? $followUp->follow_up_date?->format('M j, Y')
            ?? 'Not set' }}</p>
    </section>
    <section class="card">
        <h2>Update follow-up</h2>
        @if ($errors->any()) <ul class="error">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul> @endif
        <form method="POST" action="{{ route('field_officer.follow_ups.update', $followUp) }}">
            @csrf @method('PATCH')
            <label for="action_taken">Action taken</label>
            <textarea id="action_taken" name="action_taken" maxlength="10000">{{ old('action_taken', $followUp->action_taken) }}</textarea>
            <label for="next_follow_up_date">Next follow-up date</label>
            <input id="next_follow_up_date" type="date" name="next_follow_up_date" value="{{ old('next_follow_up_date', $followUp->next_follow_up_date?->format('Y-m-d')) }}">
            <label for="status">Status</label>
            <select id="status" name="status" required>
                @foreach (['pending', 'completed'] as $status)
                    <option value="{{ $status }}" @selected(old('status', $followUp->status) === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
            <button type="submit">Save follow-up</button>
        </form>
    </section>
</main>
</body>
</html>
