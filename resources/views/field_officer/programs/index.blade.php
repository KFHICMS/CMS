@extends('field_officer.layouts.app', ['active' => 'programs'])

@section('title', 'My Programs')
@section('heading', 'My Programs')
@section('subheading', 'Programs assigned to your account.')

@section('content')
    <section class="section">
        @if ($programs->isEmpty())
            <div class="empty">No programs are currently assigned to your account.</div>
        @else
            <div class="grid">
                @foreach ($programs as $program)
                    <article class="card">
                        <h3>{{ $program->name }}</h3>
                        <p>{{ $program->description ?: 'No program description has been provided.' }}</p>
                        <p><span class="tag">{{ ucfirst($program->status) }}</span></p>
                        <p class="muted">
                            {{ $program->start_date?->format('M j, Y') ?? 'Start date not set' }}
                            @if ($program->end_date)
                                – {{ $program->end_date->format('M j, Y') }}
                            @endif
                        </p>
                        <p>{{ $program->location ?: 'Location not specified' }} · {{ $program->events_count }} activities</p>
                        @foreach ($program->events as $event)
                            <p class="muted">
                                <a href="{{ route('field_officer.activities.show', $event) }}">{{ $event->title }}</a>
                                · {{ $event->event_date->format('M j, Y') }}
                            </p>
                        @endforeach
                        <a class="action" href="{{ route('field_officer.programs.show', $program) }}">View Program</a>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
@endsection