@php($editing = $followUp->exists)
@extends('layouts.app', [
    'active' => 'follow_ups',
    'crumbs' => [
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Follow-ups', 'url' => route('admin_follow_ups.index')],
        ['label' => $editing ? 'Edit follow-up' : 'New follow-up'],
    ],
])

@section('title', $editing ? 'Edit follow-up' : 'New follow-up')
@section('heading', $editing ? 'Edit follow-up' : 'New follow-up')

@section('content')
    @if ($errors->any())
        <div class="k-alert k-alert-error" role="alert">
            @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif

    <form method="POST" class="k-card" style="max-width: 720px"
          action="{{ $editing ? route('admin_follow_ups.update', $followUp) : route('admin_follow_ups.store') }}">
        @csrf
        @if ($editing) @method('PUT') @endif

        @if ($editing)
            <p><strong>Child code:</strong> {{ $followUp->child?->child_code }}</p>
        @else
            <div class="k-field">
                <label for="child_code">Child code</label>
                <input id="child_code" name="child_code" class="k-input" maxlength="30" value="{{ old('child_code') }}" required>
            </div>
        @endif

        <div class="k-field">
            <label for="assigned_to">Assigned field officer</label>
            <select id="assigned_to" name="assigned_to" class="k-select" required>
                <option value="">Select an officer</option>
                @foreach ($officers as $officer)
                    <option value="{{ $officer->id }}" @selected((int) old('assigned_to', $followUp->assigned_to) === $officer->id)>{{ $officer->name }} ({{ $officer->username }})</option>
                @endforeach
            </select>
        </div>

        <div class="k-field">
            <label for="event_id">Related activity (optional)</label>
            <select id="event_id" name="event_id" class="k-select">
                <option value="">No specific activity</option>
                @foreach ($events as $event)
                    <option value="{{ $event->id }}" @selected((int) old('event_id', $followUp->event_id) === $event->id)>{{ $event->title }} · {{ $event->event_date->format('d M Y') }}</option>
                @endforeach
            </select>
            <p class="k-help">The officer must be assigned to that activity's program.</p>
        </div>

        <div class="k-field">
            <label for="category">Category</label>
            <input id="category" name="category" class="k-input" maxlength="100" value="{{ old('category', $followUp->category) }}">
        </div>

        <div class="k-field">
            <label for="description">Instructions for the officer</label>
            <textarea id="description" name="description" class="k-textarea" rows="4" maxlength="5000">{{ old('description', $followUp->description) }}</textarea>
        </div>

        <div class="k-form-row">
            <div class="k-field">
                <label for="priority">Priority</label>
                <select id="priority" name="priority" class="k-select" required>
                    @foreach (['low', 'medium', 'high'] as $priority)
                        <option value="{{ $priority }}" @selected(old('priority', $followUp->priority) === $priority)>{{ ucfirst($priority) }}</option>
                    @endforeach
                </select>
            </div>
            @if ($editing)
                <div class="k-field">
                    <label for="status">Status</label>
                    <select id="status" name="status" class="k-select" required>
                        @foreach (['pending', 'completed', 'cancelled'] as $status)
                            <option value="{{ $status }}" @selected(old('status', $followUp->status) === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
        </div>

        <div class="k-form-row">
            <div class="k-field">
                <label for="follow_up_date">Follow-up date</label>
                <input id="follow_up_date" type="date" name="follow_up_date" class="k-input" value="{{ old('follow_up_date', $followUp->follow_up_date?->format('Y-m-d')) }}">
            </div>
            <div class="k-field">
                <label for="next_follow_up_date">Due / next follow-up date</label>
                <input id="next_follow_up_date" type="date" name="next_follow_up_date" class="k-input" value="{{ old('next_follow_up_date', $followUp->next_follow_up_date?->format('Y-m-d')) }}">
            </div>
        </div>

        <button type="submit" class="k-btn k-btn-primary">{{ $editing ? 'Update follow-up' : 'Create follow-up' }}</button>
        <a class="k-btn k-btn-secondary" href="{{ route('admin_follow_ups.index') }}">Cancel</a>
    </form>
@endsection