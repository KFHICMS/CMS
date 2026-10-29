@extends('layouts.app', [
    'active' => 'programs',
    'crumbs' => [
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Programs', 'url' => route('programs.index')],
        ['label' => 'Edit program'],
    ],
])

@section('title', 'Edit program')
@section('heading', 'Edit program')
@section('subheading', $program->name)

@section('content')
    @if ($errors->any())
        <div class="k-alert k-alert-error" role="alert">
            @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif

    <div class="k-stack" style="max-width: 760px">
        <form method="POST" class="k-card" action="{{ route('programs.update', $program) }}">
            @csrf @method('PUT')

            <div class="k-field">
                <label for="name">Program name</label>
                <input id="name" name="name" class="k-input" required maxlength="255" value="{{ old('name', $program->name) }}">
            </div>

            <div class="k-field">
                <label for="description">Description</label>
                <textarea id="description" name="description" class="k-textarea" rows="4">{{ old('description', $program->description) }}</textarea>
            </div>

            <div class="k-form-row">
                <div class="k-field">
                    <label for="status">Status</label>
                    <select id="status" name="status" class="k-select" required>
                        @foreach (['planned', 'active', 'completed', 'cancelled'] as $status)
                            <option value="{{ $status }}" @selected(old('status', $program->status) === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="k-field">
                    <label for="location">Location</label>
                    <input id="location" name="location" class="k-input" value="{{ old('location', $program->location) }}">
                </div>
            </div>

            <div class="k-form-row">
                <div class="k-field">
                    <label for="start_date">Start date</label>
                    <input id="start_date" type="date" name="start_date" class="k-input" value="{{ old('start_date', $program->start_date?->format('Y-m-d')) }}">
                </div>
                <div class="k-field">
                    <label for="end_date">End date</label>
                    <input id="end_date" type="date" name="end_date" class="k-input" value="{{ old('end_date', $program->end_date?->format('Y-m-d')) }}">
                </div>
            </div>

            <div class="k-field">
                <label for="officers">Assign active Field Officers</label>
                <select id="officers" name="officers[]" class="k-select" style="min-height: 130px" multiple>
                    @php($selectedOfficers = is_array(old('officers')) ? old('officers') : $assignedOfficerIds)
                    @foreach ($officers as $officer)
                        <option value="{{ $officer->id }}" @selected(in_array($officer->id, $selectedOfficers))>
                            {{ $officer->name }}{{ $officer->username ? ' ('.$officer->username.')' : '' }}
                        </option>
                    @endforeach
                </select>
                <p class="k-help">Hold Ctrl (or Cmd on Mac) to select more than one officer.</p>
            </div>

            <button type="submit" class="k-btn k-btn-primary">Update program</button>
            <a class="k-btn k-btn-secondary" href="{{ route('programs.index') }}">Cancel</a>
        </form>

        <section class="k-card">
            <div class="k-card-head">
                <h2>Activities in this program</h2>
                <a class="k-btn k-btn-primary" href="{{ route('events.create', ['program_id' => $program->id]) }}">+ Add activity</a>
            </div>
            @php($activities = $program->events()->orderBy('event_date')->get())
            @forelse ($activities as $activity)
                <div class="k-activity-item" style="display: flex; justify-content: space-between; align-items: center; gap: 12px">
                    <div>
                        <strong>{{ $activity->title }}</strong>
                        <small>{{ \Illuminate\Support\Carbon::parse($activity->event_date)->format('d M Y') }} · {{ ucfirst($activity->status) }}</small>
                    </div>
                    <a class="k-btn k-btn-secondary" href="{{ route('events.edit', $activity) }}">Edit</a>
                </div>
            @empty
                <p class="muted">No activities yet. Add one so assigned officers can see it.</p>
            @endforelse
        </section>
    </div>
@endsection