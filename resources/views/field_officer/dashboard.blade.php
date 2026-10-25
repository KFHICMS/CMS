@extends('layouts.app', ['active' => 'dashboard'])

@section('title', 'Field Officer Dashboard')
@section('heading', 'Field Officer Dashboard')
@section('subheading', 'Welcome back, '.$officer->name.'. Here is your assigned fieldwork.')

@section('content')
      

        <section class="section" id="programs">
            <h2>My Programs</h2>
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

        <section class="section" id="activities">
            <h2>Today's Activities <span class="muted">· {{ today()->format('l, F j') }}</span></h2>
            @if ($todayActivities->isEmpty())
                <div class="empty">You have no activities scheduled for today.</div>
            @else
                <div class="grid">
                    @foreach ($todayActivities as $activity)
                        <article class="card">
                            <h3>{{ $activity->title }}</h3>
                            <p>{{ $activity->program?->name }}</p>
                            <p>{{ $activity->start_time ? \Illuminate\Support\Carbon::parse($activity->start_time)->format('g:i A') : 'Time not set' }}
                                @if ($activity->end_time)
                                    – {{ \Illuminate\Support\Carbon::parse($activity->end_time)->format('g:i A') }}
                                @endif
                            </p>
                            <p>{{ $activity->location ?: 'Location not specified' }}</p>
                            <span class="tag">{{ ucfirst($activity->status) }}</span>
                            <a class="action" href="{{ route('field_officer.activities.show', $activity) }}">Select Activity</a>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="section" id="follow-ups">
            <h2>Assigned Follow-ups</h2>
            @if ($followUps->isEmpty())
                <div class="empty">You have no open follow-ups assigned to you.</div>
            @else
                <div class="list">
                    @foreach ($followUps as $followUp)
                        <article class="card">
                            <h3>{{ $followUp->category ?: 'Child follow-up' }}</h3>
                            <p>{{ $followUp->child?->child_code }} ·
                                {{ trim(implode(' ', array_filter([
                                    $followUp->child?->first_name,
                                    $followUp->child?->middle_name,
                                    $followUp->child?->last_name,
                                ]))) }}
                            </p>
                            <p>{{ $followUp->description ?: 'No instructions provided.' }}</p>

                            @if ($followUp->event)
                                <p class="muted">Activity: {{ $followUp->event->title }}</p>
                            @endif
                            <p class="muted">
                                Due {{ $followUp->next_follow_up_date?->format('M j, Y')
                                    ?? $followUp->follow_up_date?->format('M j, Y')
                                    ?? 'date not set' }} ·
                                {{ ucfirst($followUp->priority) }} priority ·
                                {{ ucfirst(str_replace('_', ' ', $followUp->status)) }}
                            </p>
                            <a class="action" href="{{ route('field_officer.follow_ups.show', $followUp) }}">Open follow-up</a>
                        </article>
                    @endforeach
                </div>
            @endif
            {{ $followUps->links() }}
        </section>

        <section class="section" id="pending-submissions">
            <h2>Pending Submissions</h2>
            @if ($pendingSubmissions->isEmpty())
                <div class="empty">You have no saved drafts awaiting submission.</div>
            @else
                <div class="grid">
                    @foreach ($pendingSubmissions as $draft)
                        <article class="card">
                            <h3>{{ $draft->event->title }}</h3>
                            <p>{{ $draft->event->program->name }} · {{ $draft->child->child_code }}</p>
                            <p class="muted">Last saved {{ $draft->updated_at->format('M j, Y g:i A') }}</p>
                            <a class="action" href="{{ route('field_officer.activities.show', ['event' => $draft->event, 'submission' => $draft->id]) }}">Continue draft</a>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
 @endsection
