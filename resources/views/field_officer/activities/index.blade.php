@extends('field_officer.layouts.app', ['active' => 'activities'])

@section('title', "Today's Activities")
@section('heading', "Today's Activities")
@section('subheading', today()->format('l, F j, Y'))

@section('content')
    <section class="section">
        @if ($todayActivities->isEmpty())
            <div class="empty">
                You have no activities scheduled for today.
                <a href="{{ route('field_officer.programs.index') }}">Browse your programs</a> to open other activities.
            </div>
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
@endsection