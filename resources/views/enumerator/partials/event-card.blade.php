<a class="card folder" href="{{ route('enumerator.events.show', $event) }}">
    <h3>📁 {{ $event->title }}</h3>
    <p>{{ $event->program?->name ?? 'No program' }}</p>
    <p>{{ $event->event_date->format('M j, Y') }}
        @if ($event->start_time)
            · {{ \Illuminate\Support\Carbon::parse($event->start_time)->format('g:i A') }}
        @endif
    </p>
    <p>{{ $event->location ?: 'Location not specified' }}</p>
    <span class="tag">{{ $states[$event->collectionState()] }}</span>
    <span class="tag {{ $event->enable_attendance ? '' : 'off' }}">Attendance {{ $event->enable_attendance ? 'on' : 'off' }}</span>
    <span class="tag {{ $event->enable_questionnaires ? '' : 'off' }}">Questionnaires {{ $event->enable_questionnaires ? 'on' : 'off' }}</span>
</a>