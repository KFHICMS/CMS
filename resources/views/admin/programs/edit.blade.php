<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Program | KFHI CMS</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; padding: 28px; background: #eef7f0; color: #294432; font-family: Arial, sans-serif; }
        main { max-width: 760px; margin: auto; }
        form { margin-top: 18px; padding: 22px; background: white; border: 1px solid #dce9df; border-radius: 10px; }
        label { display: block; margin: 14px 0 6px; font-weight: 600; }
        input, textarea, select { width: 100%; padding: 10px; border: 1px solid #bdcbbf; border-radius: 7px; font: inherit; }
        select[multiple] { min-height: 130px; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        button { margin-top: 18px; padding: 11px 16px; border: 0; border-radius: 7px; color: white; background: #28733a; cursor: pointer; }
        .errors { color: #a94343; }
        .activities-card { margin-top: 18px; padding: 22px; background: white; border: 1px solid #dce9df; border-radius: 10px; }
        .activities-head { display: flex; justify-content: space-between; align-items: center; gap: 12px; }
        .activities-head h2 { margin: 0; font-size: 18px; }
        .add-activity { padding: 9px 14px; border-radius: 7px; color: white; background: #28733a; text-decoration: none; }
        .activity-row { display: flex; justify-content: space-between; gap: 12px; padding: 10px 0; border-top: 1px solid #e3ede6; }
        .activity-row small { display: block; color: #5f7a68; }
        @media (max-width: 560px) { body { padding: 16px; } .grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
<main>
    <a href="{{ route('programs.index') }}">← Back to programs</a>
    <h1>Edit program</h1>
    @if ($errors->any()) <div class="errors"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div> @endif
    <form method="POST" action="{{ route('programs.update', $program) }}">
        @csrf @method('PUT')
        <label for="name">Program name</label>
        <input id="name" name="name" required maxlength="255" value="{{ old('name', $program->name) }}">
        <label for="description">Description</label>
        <textarea id="description" name="description" rows="4">{{ old('description', $program->description) }}</textarea>
        <div class="grid">
            <div><label for="status">Status</label><select id="status" name="status" required>
                @foreach (['planned', 'active', 'completed', 'cancelled'] as $status)
                    <option value="{{ $status }}" @selected(old('status', $program->status) === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select></div>
            <div><label for="location">Location</label><input id="location" name="location" value="{{ old('location', $program->location) }}"></div>
            <div><label for="start_date">Start date</label><input id="start_date" type="date" name="start_date" value="{{ old('start_date', $program->start_date?->format('Y-m-d')) }}"></div>
            <div><label for="end_date">End date</label><input id="end_date" type="date" name="end_date" value="{{ old('end_date', $program->end_date?->format('Y-m-d')) }}"></div>
        </div>
        <label for="officers">Assign active Field Officers</label>
        <select id="officers" name="officers[]" multiple>
            @php($selectedOfficers = is_array(old('officers')) ? old('officers') : $assignedOfficerIds)
            @foreach ($officers as $officer)
                <option value="{{ $officer->id }}" @selected(in_array($officer->id, $selectedOfficers))>
                    {{ $officer->name }}{{ $officer->username ? ' ('.$officer->username.')' : '' }}
                </option>
            @endforeach
        </select>
        <button type="submit">Update program</button>
    </form>
        <section class="activities-card">
        <div class="activities-head">
            <h2>Activities in this program</h2>
            <a class="add-activity" href="{{ route('events.create', ['program_id' => $program->id]) }}">+ Add activity</a>
        </div>
        @php($activities = $program->events()->orderBy('event_date')->get())
        @forelse ($activities as $activity)
            <div class="activity-row">
                <div>
                    <strong>{{ $activity->title }}</strong>
                    <small>{{ \Illuminate\Support\Carbon::parse($activity->event_date)->format('d M Y') }} · {{ ucfirst($activity->status) }}</small>
                </div>
                <a href="{{ route('events.edit', $activity) }}">Edit</a>
            </div>
        @empty
            <p>No activities yet. Add one so assigned officers can see it.</p>
        @endforelse
    </section>
</main>
</body>
</html>
