@extends('enumerator.layouts.app', ['active' => 'dashboard'])

@section('title', 'Dashboard')
@section('heading', 'Welcome, '.auth()->user()->name)
@section('subheading', today()->format('l, F j, Y'))

@php
    $states = ['upcoming' => 'Upcoming', 'open' => 'Open for collection', 'completed' => 'Completed', 'cancelled' => 'Cancelled'];
@endphp

@section('content')
    <section class="section">
        <div class="grid">
            <div class="card stat"><strong>{{ $stats['assigned'] }}</strong><span>Assigned events</span></div>
            <div class="card stat"><strong>{{ $stats['upcoming'] }}</strong><span>Upcoming events</span></div>
            <div class="card stat"><strong>{{ $stats['attendance'] }}</strong><span>Attendance records submitted</span></div>
            <div class="card stat"><strong>{{ $stats['questionnaires'] }}</strong><span>Questionnaire submissions</span></div>
        </div>
    </section>

    <section class="section">
        <h2>Today's Events</h2>
        @if ($todayEvents->isEmpty())
            <div class="empty">You have no events scheduled for today.</div>
        @else
            <div class="grid">
                @foreach ($todayEvents as $event)
                    @include('enumerator.partials.event-card', ['event' => $event, 'states' => $states])
                @endforeach
            </div>
        @endif
    </section>

    <section class="section">
        <h2>My Assigned Events</h2>
        @if ($events->isEmpty())
            <div class="empty">You do not currently have any assigned events.</div>
        @else
            <div class="grid">
                @foreach ($events as $event)
                    @include('enumerator.partials.event-card', ['event' => $event, 'states' => $states])
                @endforeach
            </div>
        @endif
    </section>
@endsection