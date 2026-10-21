@extends('field_officer.layouts.app', ['active' => 'follow-ups'])

@section('title', 'Follow-ups')
@section('heading', 'Assigned Follow-ups')
@section('subheading', 'Follow-ups assigned to you.')

@section('content')
    <section class="section">
        <div class="tabs">
            <a @class(['current' => $filter === 'open']) href="{{ route('field_officer.follow_ups.index') }}">Open</a>
            <a @class(['current' => $filter === 'completed']) href="{{ route('field_officer.follow_ups.index', ['status' => 'completed']) }}">Completed</a>
            <a @class(['current' => $filter === 'all']) href="{{ route('field_officer.follow_ups.index', ['status' => 'all']) }}">All</a>
        </div>

        @if ($followUps->isEmpty())
            <div class="empty">No follow-ups to show here.</div>
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
@endsection