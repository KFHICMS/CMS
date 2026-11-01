@extends('layouts.app', [
    'active' => 'programs',
    'crumbs' => [
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Programs', 'url' => route('programs.index')],
        ['label' => 'Create program'],
    ],
])

@section('title', 'Create program')
@section('heading', 'Create program')

@section('content')
    @if ($errors->any())
        <div class="k-alert k-alert-error" role="alert">
            @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif

    <form method="POST" class="k-card" style="max-width: 760px" action="{{ route('programs.store') }}">
        @csrf

        <div class="k-field">
            <label for="name">Program name</label>
            <input id="name" name="name" class="k-input" required maxlength="255" value="{{ old('name') }}">
        </div>

        <div class="k-field">
            <label for="description">Description</label>
            <textarea id="description" name="description" class="k-textarea" rows="4">{{ old('description') }}</textarea>
        </div>

        <div class="k-form-row">
            <div class="k-field">
                <label for="status">Status</label>
                <select id="status" name="status" class="k-select" required>
                    @foreach (['planned', 'active', 'completed', 'cancelled'] as $status)
                        <option value="{{ $status }}" @selected(old('status', 'planned') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="k-field">
                <label for="location">Location</label>
                <input id="location" name="location" class="k-input" value="{{ old('location') }}">
            </div>
        </div>

        <div class="k-form-row">
            <div class="k-field">
                <label for="start_date">Start date</label>
                <input id="start_date" type="date" name="start_date" class="k-input" value="{{ old('start_date') }}">
            </div>
            <div class="k-field">
                <label for="end_date">End date</label>
                <input id="end_date" type="date" name="end_date" class="k-input" value="{{ old('end_date') }}">
            </div>
        </div>

        <div class="k-field">
            <label for="officers">Assign active Field Officers</label>
            <select id="officers" name="officers[]" class="k-select" style="min-height: 130px" multiple>
                @php($selectedOfficerIds = is_array(old('officers')) ? old('officers') : [])
                @foreach ($officers as $officer)
                    <option value="{{ $officer->id }}" @selected(in_array($officer->id, $selectedOfficerIds))>
                        {{ $officer->name }}{{ $officer->username ? ' ('.$officer->username.')' : '' }}
                    </option>
                @endforeach
            </select>
            <p class="k-help">Hold Ctrl (or Cmd on Mac) to select more than one officer.</p>
        </div>

        <button type="submit" class="k-btn k-btn-primary">Save program</button>
        <a class="k-btn k-btn-secondary" href="{{ route('programs.index') }}">Cancel</a>
    </form>
@endsection