<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Assign Enumerators | KFHI CMS</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; padding: 30px; background: #eef7f0; color: #294432; font-family: Arial, sans-serif; }
        main { max-width: 720px; margin: auto; }
        form { margin-top: 18px; padding: 22px; background: white; border: 1px solid #dce9df; border-radius: 10px; }
        a { color: #28733a; }
        label.item { display: flex; align-items: center; gap: 10px; padding: 10px 0; border-bottom: 1px solid #e6eee7; }
        button { margin-top: 18px; padding: 11px 16px; color: white; background: #28733a; border: 0; border-radius: 8px; font: inherit; cursor: pointer; }
        .error { color: #a12626; }
        .muted { color: #65756a; }
        @media (max-width: 560px) { body { padding: 16px; } }
    </style>
</head>
<body>
<main>
    <a href="{{ route('events.show', $event) }}">← Back to event</a>
    <h1>Assign Enumerators</h1>
    <p class="muted">{{ $event->title }} · {{ $event->event_date->format('d M Y') }}</p>

    @if ($errors->any())
        <div class="error" role="alert">@foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
    @endif

    <form method="POST" action="{{ route('events.enumerators.update', $event) }}">
        @csrf
        @method('PUT')

        @forelse ($enumerators as $enumerator)
            <label class="item">
                <input type="checkbox" name="enumerators[]" value="{{ $enumerator->id }}"
                    @checked(in_array($enumerator->id, old('enumerators', $assignedIds)))>
                <span>{{ $enumerator->name }} ({{ $enumerator->username }})
                    @if ($enumerator->status !== 'active') <em class="muted">· inactive</em> @endif
                </span>
            </label>
        @empty
            <p class="muted">No active enumerator accounts exist yet. Create one in Users first.</p>
        @endforelse

        <button type="submit">Save assignments</button>
    </form>
</main>
</body>
</html>