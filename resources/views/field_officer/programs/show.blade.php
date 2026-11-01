@extends('layouts.app', [
    'active' => 'programs',
    'crumbs' => [
        ['label' => 'Dashboard', 'url' => route('field_officer.dashboard')],
        ['label' => 'My Programs', 'url' => route('field_officer.programs.index')],
        ['label' => $program->name],
    ],
])

@section('title', $program->name)
@section('heading', $program->name)
@section('subheading', 'Program details and activities')

@section('content')
    <section class="card">
        <p class="muted">My Program</p>
        <p>{{ $program->description ?: 'No program description has been provided.' }}</p>
        <p><span class="tag">{{ ucfirst($program->status) }}</span></p>
        <p>{{ $program->location ?: 'Location not specified' }}</p>
        <p class="muted">
            {{ $program->start_date?->format('M j, Y') ?? 'Start date not set' }}
            @if ($program->end_date) – {{ $program->end_date->format('M j, Y') }} @endif
        </p>
    </section>
    <section class="card">
        <h2>Activities</h2>
        @if ($program->events->isEmpty())
            <p class="muted">No activities are linked to this program yet.</p>
        @else
            <div class="grid">
                @foreach ($program->events as $event)
                    <article class="card">
                        <h3>{{ $event->title }}</h3>
                        <p class="muted">{{ $event->event_code }} · {{ $event->event_date->format('M j, Y') }}</p>
                        <p>{{ $event->start_time ?: 'Time not set' }} · {{ $event->location ?: 'Location not specified' }}</p>
                        <p><span class="tag">{{ ucfirst($event->status) }}</span></p>
                        <a class="action" href="{{ route('field_officer.activities.show', $event) }}">Select Activity</a>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
@endsection
