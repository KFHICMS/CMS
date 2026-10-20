<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Field Submission | KFHI CMS</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; padding: 30px; background: #eef7f0; color: #294432; font-family: Arial, sans-serif; }
        main { max-width: 900px; margin: auto; }
        .card { margin-top: 18px; padding: 20px; background: white; border: 1px solid #dce9df; border-radius: 10px; }
        a { color: #28733a; }
        label { display: block; margin: 10px 0 4px; font-weight: 600; }
        input, select, textarea { width: 100%; padding: 9px; border: 1px solid #bdcbbf; border-radius: 7px; font: inherit; }
        .row { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 10px; }
        .item { margin-top: 12px; padding: 12px; border: 1px solid #e3ede6; border-radius: 8px; }
        button { margin-top: 16px; padding: 11px 16px; color: white; background: #28733a; border: 0; border-radius: 8px; font: inherit; cursor: pointer; }
        .notice { color: #28733a; }
        .error { color: #a12626; }
        .muted { color: #65756a; }
        .history { padding: 10px 0; border-top: 1px solid #e3ede6; }
        @media (max-width: 700px) { body { padding: 16px; } }
    </style>
</head>
<body>
<main>
    <a href="{{ route('field_submissions.index') }}">← Back to field submissions</a>
    <h1>{{ $submission->event->title }}</h1>
    <p class="muted">
        Program: {{ $submission->event->program?->name ?? '—' }} ·
        Child: {{ $submission->child->child_code }} ({{ trim(implode(' ', array_filter([$submission->child->first_name, $submission->child->middle_name, $submission->child->last_name]))) }}) ·
        Officer: {{ $submission->user->name }} ·
        Submitted: {{ $submission->submitted_at?->format('d M Y H:i') }}
    </p>

    @if (session('success')) <p class="notice" role="status">{{ session('success') }}</p> @endif
    @if ($errors->any())
        <div class="error" role="alert">
            @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif

    <section class="card">
        <h2>Record a correction</h2>
        <p class="muted">Changes are saved with the old values and your reason. Nothing is deleted.</p>
        <form method="POST" action="{{ route('field_submissions.correct', $submission) }}">
            @csrf
            @method('PATCH')

            @if ($submission->attendance_status)
                <label for="attendance_status">Attendance</label>
                <select id="attendance_status" name="attendance_status">
                    @foreach (['present', 'absent'] as $status)
                        <option value="{{ $status }}" @selected(old('attendance_status', $submission->attendance_status) === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            @endif

            @foreach ($submission->benefits as $benefit)
                <div class="item">
                    <strong>Benefit #{{ $benefit->id }}</strong>
                    <div class="row">
                        <div><label>Item</label><input name="benefits[{{ $benefit->id }}][item_name]" value="{{ old("benefits.{$benefit->id}.item_name", $benefit->item_name) }}" required></div>
                        <div><label>Category</label><input name="benefits[{{ $benefit->id }}][category]" value="{{ old("benefits.{$benefit->id}.category", $benefit->category) }}"></div>
                        <div><label>Quantity</label><input type="number" min="1" name="benefits[{{ $benefit->id }}][quantity]" value="{{ old("benefits.{$benefit->id}.quantity", $benefit->quantity) }}" required></div>
                    </div>
                    <label>Notes</label>
                    <textarea name="benefits[{{ $benefit->id }}][notes]" rows="2">{{ old("benefits.{$benefit->id}.notes", $benefit->notes) }}</textarea>
                </div>
            @endforeach

            @foreach ($submission->observations as $observation)
                <div class="item">
                    <strong>Field note #{{ $observation->id }}</strong>
                    <label>Category</label>
                    <input name="observations[{{ $observation->id }}][category]" value="{{ old("observations.{$observation->id}.category", $observation->category) }}">
                    <label>Note</label>
                    <textarea name="observations[{{ $observation->id }}][note]" rows="4" required>{{ old("observations.{$observation->id}.note", $observation->note) }}</textarea>
                </div>
            @endforeach

            <label for="reason">Reason for correction (required)</label>
            <textarea id="reason" name="reason" rows="3" required>{{ old('reason') }}</textarea>

            <button type="submit">Save correction</button>
        </form>
    </section>

    <section class="card">
        <h2>Correction history</h2>
        @forelse ($submission->corrections as $correction)
            <div class="history">
                <strong>{{ $correction->created_at->format('d M Y H:i') }}</strong>
                by {{ $correction->corrector?->name ?? 'Unknown user' }}<br>
                <span class="muted">Reason: {{ $correction->reason }}</span>
                <ul>
                    @foreach ($correction->changes as $change)
                        <li>
                            {{ ucfirst($change['target']) }}{{ $change['id'] ? ' #'.$change['id'] : '' }}
                            {{ $change['field'] }}:
                            {{ \Illuminate\Support\Str::limit((string) $change['old'], 200) }}
                            →
                            {{ \Illuminate\Support\Str::limit((string) $change['new'], 200) }}
                        </li>
                    @endforeach
                </ul>
            </div>
        @empty
            <p class="muted">This record has not been corrected.</p>
        @endforelse
    </section>
</main>
</body>
</html>