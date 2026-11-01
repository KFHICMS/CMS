@extends('enumerator.layouts.app', ['active' => 'dashboard'])

@section('title', $event->title)
@section('heading', '📁 '.$event->title)
@section('subheading', ($event->program?->name ?? 'No program').' · '.$event->event_date->format('l, F j, Y'))

@section('content')
    <p style="margin-top:18px"><a href="{{ route('enumerator.dashboard') }}">← Back to My Events</a></p>

    @if ($state === 'cancelled')
        <p class="notice" role="alert">This event has been cancelled. Data collection is not available.</p>
    @elseif ($state === 'completed')
        <p class="notice" role="alert">This event is completed. Data collection is closed.</p>
    @elseif ($state === 'upcoming')
        <p class="notice" role="alert">This event has not started yet. Collection opens on {{ $event->event_date->format('M j, Y') }}.</p>
    @endif

    <section class="section">
        <div class="grid">
            <article class="card">
                <h3>📄 Event Information</h3>
                <p>
                    {{ $event->start_time ? \Illuminate\Support\Carbon::parse($event->start_time)->format('g:i A') : 'Time not set' }}
                    @if ($event->end_time) – {{ \Illuminate\Support\Carbon::parse($event->end_time)->format('g:i A') }} @endif
                </p>
                <p>{{ $event->location ?: 'Location not specified' }}</p>
                <p>{{ $event->description ?: 'No instructions have been provided for this event.' }}</p>
            </article>

            <article class="card">
                <h3>📁 Attendance</h3>
                @if (! $event->enable_attendance)
                    <p>Attendance has not been enabled for this event. Please contact your administrator.</p>
                @elseif (! $collecting)
                    <p>Attendance is not available while this event is {{ $state }}.</p>
                @else
                    <p>Attendance collection is enabled for this event.</p>
                    <a class="btn" href="{{ route('enumerator.events.attendance.index', $event) }}">Open Attendance</a>
                @endif
            </article>

            <article class="card">
                <h3>📁 Questionnaires</h3>
                @if (! $event->enable_questionnaires)
                    <p>Questionnaires have not been enabled for this event. Please contact your administrator.</p>
                @elseif (! $collecting)
                    <p>Questionnaires are not available while this event is {{ $state }}.</p>
                @elseif ($questionnaires->isEmpty())
                    <p>No published questionnaires are currently available for this event.</p>
                @else
                    @foreach ($questionnaires as $questionnaire)
                        <p><strong>{{ $questionnaire->title }}</strong>
                            @if ($questionnaire->description)<br><span class="muted">{{ $questionnaire->description }}</span>@endif
                        </p>
                        <p><a class="btn" href="{{ route('enumerator.events.questionnaires.show', [$event, $questionnaire]) }}">Start</a></p>
                    @endforeach
                    {{-- The "Start" buttons are added in a later step. --}}
                @endif
            </article>

            <article class="card">
                <h3>📋 My Submissions</h3>
                <p>Attendance recorded by you: {{ $mine['attendance'] }}</p>
                <p>Questionnaires submitted by you: {{ $mine['questionnaires'] }}</p>
                <p><a href="{{ route('enumerator.submissions.index', ['event_id' => $event->id]) }}">View my submissions for this event</a></p>
            </article>
        </div>
    </section>
@endsection