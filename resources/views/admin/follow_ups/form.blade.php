@php($editing = $followUp->exists)
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $editing ? 'Edit' : 'New' }} follow-up | KFHI CMS</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; padding: 30px; background: #eef7f0; color: #294432; font-family: Arial, sans-serif; }
        main { max-width: 720px; margin: auto; }
        form { margin-top: 18px; padding: 22px; background: white; border: 1px solid #dce9df; border-radius: 10px; }
        a { color: #28733a; }
        label { display: block; margin: 14px 0 4px; font-weight: 600; }
        input, select, textarea { width: 100%; padding: 10px; border: 1px solid #bdcbbf; border-radius: 7px; font: inherit; }
        .row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        button { margin-top: 20px; padding: 11px 16px; color: white; background: #28733a; border: 0; border-radius: 8px; font: inherit; cursor: pointer; }
        .error { color: #a12626; }
        .muted { color: #65756a; }
        @media (max-width: 560px) { body { padding: 16px; } .row { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
<main>
    <a href="{{ route('admin_follow_ups.index') }}">← Back to follow-ups</a>
    <h1>{{ $editing ? 'Edit follow-up' : 'New follow-up' }}</h1>

    @if ($errors->any())
        <div class="error" role="alert">@foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
    @endif

    <form method="POST" action="{{ $editing ? route('admin_follow_ups.update', $followUp) : route('admin_follow_ups.store') }}">
        @csrf
        @if ($editing) @method('PUT') @endif

        @if ($editing)
            <p><strong>Child code:</strong> {{ $followUp->child?->child_code }}</p>
        @else
            <label for="child_code">Child code</label>
            <input id="child_code" name="child_code" maxlength="30" value="{{ old('child_code') }}" required>
        @endif

        <label for="assigned_to">Assigned field officer</label>
        <select id="assigned_to" name="assigned_to" required>
            <option value="">Select an officer</option>
            @foreach ($officers as $officer)
                <option value="{{ $officer->id }}" @selected((int) old('assigned_to', $followUp->assigned_to) === $officer->id)>{{ $officer->name }} ({{ $officer->username }})</option>
            @endforeach
        </select>

        <label for="event_id">Related activity (optional)</label>
        <select id="event_id" name="event_id">
            <option value="">No specific activity</option>
            @foreach ($events as $event)
                <option value="{{ $event->id }}" @selected((int) old('event_id', $followUp->event_id) === $event->id)>{{ $event->title }} · {{ $event->event_date->format('d M Y') }}</option>
            @endforeach
        </select>
        <small class="muted">The officer must be assigned to that activity's program.</small>

        <label for="category">Category</label>
        <input id="category" name="category" maxlength="100" value="{{ old('category', $followUp->category) }}">

        <label for="description">Instructions for the officer</label>
        <textarea id="description" name="description" rows="4" maxlength="5000">{{ old('description', $followUp->description) }}</textarea>

        <div class="row">
            <div>
                <label for="priority">Priority</label>
                <select id="priority" name="priority" required>
                    @foreach (['low', 'medium', 'high'] as $priority)
                        <option value="{{ $priority }}" @selected(old('priority', $followUp->priority) === $priority)>{{ ucfirst($priority) }}</option>
                    @endforeach
                </select>
            </div>
            @if ($editing)
                <div>
                    <label for="status">Status</label>
                    <select id="status" name="status" required>
                        @foreach (['pending', 'completed', 'cancelled'] as $status)
                            <option value="{{ $status }}" @selected(old('status', $followUp->status) === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
        </div>

        <div class="row">
            <div>
                <label for="follow_up_date">Follow-up date</label>
                <input id="follow_up_date" type="date" name="follow_up_date" value="{{ old('follow_up_date', $followUp->follow_up_date?->format('Y-m-d')) }}">
            </div>
            <div>
                <label for="next_follow_up_date">Due / next follow-up date</label>
                <input id="next_follow_up_date" type="date" name="next_follow_up_date" value="{{ old('next_follow_up_date', $followUp->next_follow_up_date?->format('Y-m-d')) }}">
            </div>
        </div>

        <button type="submit">{{ $editing ? 'Update follow-up' : 'Create follow-up' }}</button>
    </form>
</main>
</body>
</html>