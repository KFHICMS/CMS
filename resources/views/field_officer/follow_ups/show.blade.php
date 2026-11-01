@extends('layouts.app', [
    'active' => 'follow-ups',
    'crumbs' => [
        ['label' => 'Dashboard', 'url' => route('field_officer.dashboard')],
        ['label' => 'Follow-ups', 'url' => route('field_officer.follow_ups.index')],
        ['label' => $followUp->category ?: 'Follow-up'],
    ],
])

@section('title', $followUp->category ?: 'Follow-up')
@section('heading', $followUp->category ?: 'Follow-up')
@section('subheading', 'Assigned child follow-up')

@section('content')
    <section class="card">
        
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
@endsection
