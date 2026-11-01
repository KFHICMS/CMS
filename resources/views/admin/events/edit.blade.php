@extends('layouts.app', [
    'active' => 'events',
    'crumbs' => [
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Events', 'url' => route('events.index')],
        ['label' => $event->title, 'url' => route('events.show', $event)],
        ['label' => 'Edit Event'],
    ],
])

@section('title', 'Edit Event')
@section('heading', 'Edit Event')
@section('subheading', $event->title)

@section('actions')
    <a class="k-btn k-btn-secondary" href="{{ route('events.show', $event) }}">Back to Event</a>
@endsection

@push('head')
    <style>
        * { box-sizing: border-box; }
        main { max-width: none; margin: 0; }
        main > form { margin-top: 0; padding: 22px; background: white; border: 1px solid #dce9df; border-radius: 10px; }
        label { display: block; margin: 14px 0 6px; font-weight: 600; }
        input, textarea, select { width: 100%; padding: 10px; border: 1px solid #bdcbbf; border-radius: 7px; font: inherit; }
        .grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; }
        button { margin-top: 18px; padding: 11px 16px; border: 0; border-radius: 7px; color: white; background: #28733a; cursor: pointer; }
        .errors { color: #a94343; }
    </style>
@endpush

@section('content')
<main>
    @if ($errors->any()) <div class="errors"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div> @endif
    <form method="POST" action="{{ route('events.update', $event) }}">
        @csrf
        @method('PUT')
        <div class="grid">
            <div>
                <label for="event_code">Activity ID</label>
                <input id="event_code" name="event_code" required maxlength="50" value="{{ old('event_code', $event->event_code) }}">
            </div>
            <div>
                <label for="title">Activity name</label>
                <input id="title" name="title" required maxlength="255" value="{{ old('title', $event->title) }}">
            </div>
            <div>
                <label for="program_id">Program</label>
                <select id="program_id" name="program_id">
                    <option value="">Unassigned</option>
                    @foreach ($programs as $program)
                        <option value="{{ $program->id }}" @selected(old('program_id', $event->program_id) == $program->id)>
                            {{ $program->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="event_date">Date</label>
                <input id="event_date" type="date" name="event_date" required value="{{ old('event_date', $event->event_date?->format('Y-m-d')) }}">
            </div>
            <div>
                <label for="start_time">Start time</label>
                <input id="start_time" type="time" name="start_time" value="{{ old('start_time', $event->start_time) }}">
            </div>
            <div>
                <label for="end_time">End time</label>
                <input id="end_time" type="time" name="end_time" value="{{ old('end_time', $event->end_time) }}">
            </div>
            <div>
                <label for="location">Location</label>
                <input id="location" name="location" maxlength="255" value="{{ old('location', $event->location) }}">
            </div>
            <div>
                <label for="status">Status</label>
                <select id="status" name="status" required>
                    @foreach (['planned', 'ongoing', 'completed', 'cancelled'] as $status)
                        <option value="{{ $status }}" @selected(old('status', $event->status) === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <label for="description">Description / instructions</label>
        <textarea id="description" name="description" rows="5">{{ old('description', $event->description) }}</textarea>
        <input type="hidden" name="features_submitted" value="1">
        <label>
            <input type="checkbox" name="enable_attendance" value="1" style="width:auto;margin-right:8px"
                @checked(old('features_submitted') ? old('enable_attendance') : $event->enable_attendance)>
            Enable QR code attendance
        </label>
        <label>
            <input type="checkbox" name="enable_questionnaires" value="1" style="width:auto;margin-right:8px"
                @checked(old('features_submitted') ? old('enable_questionnaires') : $event->enable_questionnaires)>
            Enable questionnaires
        </label>
        <button type="submit">Save activity</button>
    </form>
</main>
@endsection
