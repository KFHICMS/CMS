<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create Program | KFHI CMS</title>
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
        @media (max-width: 560px) { body { padding: 16px; } .grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
<main>
    <a href="{{ route('programs.index') }}">← Back to programs</a>
    <h1>Create program</h1>
    @if ($errors->any()) <div class="errors"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div> @endif
    <form method="POST" action="{{ route('programs.store') }}">
        @csrf
        <label for="name">Program name</label>
        <input id="name" name="name" required maxlength="255" value="{{ old('name') }}">
        <label for="description">Description</label>
        <textarea id="description" name="description" rows="4">{{ old('description') }}</textarea>
        <div class="grid">
            <div><label for="status">Status</label><select id="status" name="status" required>
                @foreach (['planned', 'active', 'completed', 'cancelled'] as $status)
                    <option value="{{ $status }}" @selected(old('status', 'planned') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select></div>
            <div><label for="location">Location</label><input id="location" name="location" value="{{ old('location') }}"></div>
            <div><label for="start_date">Start date</label><input id="start_date" type="date" name="start_date" value="{{ old('start_date') }}"></div>
            <div><label for="end_date">End date</label><input id="end_date" type="date" name="end_date" value="{{ old('end_date') }}"></div>
        </div>
        <label for="officers">Assign active Field Officers</label>
        <select id="officers" name="officers[]" multiple>
            @php($selectedOfficerIds = is_array(old('officers')) ? old('officers') : [])
            @foreach ($officers as $officer)
                <option value="{{ $officer->id }}" @selected(in_array($officer->id, $selectedOfficerIds))>
                    {{ $officer->name }}{{ $officer->username ? ' ('.$officer->username.')' : '' }}
                </option>
            @endforeach
        </select>
        <button type="submit">Save program</button>
    </form>
</main>
</body>
</html>
