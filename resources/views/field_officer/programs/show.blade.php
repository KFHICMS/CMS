<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $program->name }} | KFHI CMS</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; padding: 24px; background: #f3f8f4; color: #294432; font-family: Arial, sans-serif; }
        main { max-width: 1000px; margin: auto; }
        .card { margin-top: 18px; padding: 20px; background: #fff; border: 1px solid #dfe9e1; border-radius: 12px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 12px; }
        .muted { color: #65756a; }
        .tag { display: inline-block; padding: 4px 8px; border-radius: 20px; background: #e8f4ea; color: #28733a; font-size: 13px; }
        .action { display: inline-block; margin-top: 10px; padding: 9px 12px; border-radius: 8px; background: #28733a; color: #fff; text-decoration: none; }
        a { color: #28733a; }
        @media (max-width: 600px) { body { padding: 14px; } }
    </style>
</head>
<body>
<main>
        @include('field_officer.partials.breadcrumbs', ['crumbs' => [
        ['label' => 'Dashboard', 'url' => route('field_officer.dashboard')],
        ['label' => 'My Programs', 'url' => route('field_officer.programs.index')],
        ['label' => $program->name, 'url' => null],
    ]])
    <section class="card">
        <p class="muted">My Program</p>
        <h1>{{ $program->name }}</h1>
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
</main>
</body>
</html>
