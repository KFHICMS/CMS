<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Field Officer Dashboard | KFHI CMS</title>
    <style>
        :root { font-family: Arial, sans-serif; color: #294432; background: #f3f8f4; }
        * { box-sizing: border-box; }
        body { margin: 0; }
        a { color: #28733a; }
        .layout { display: flex; min-height: 100vh; }
        aside { width: 245px; padding: 26px 18px; background: #fff; border-right: 1px solid #dfe9e1; }
        .brand { font-size: 20px; font-weight: 700; color: #236b3b; margin: 0 8px 8px; }
        .role { color: #718075; font-size: 13px; margin: 0 8px 28px; }
        nav a { display: block; padding: 11px 12px; margin: 4px 0; color: #53665a; text-decoration: none; border-radius: 9px; }
        nav a:hover, nav a.active { background: #e8f4ea; color: #236b3b; }
        .logout { margin-top: 26px; }
        .logout button { width: 100%; padding: 10px; border: 0; border-radius: 8px; background: #fbeaea; color: #a94343; cursor: pointer; }
        main { flex: 1; max-width: 1440px; padding: 28px 34px; margin: 0 auto; }
        header { display: flex; justify-content: space-between; align-items: center; gap: 16px; padding-bottom: 20px; border-bottom: 1px solid #dfe9e1; }
        h1 { margin: 0; font-size: 25px; }
        h2 { margin: 0 0 16px; font-size: 19px; }
        .welcome { color: #65756a; margin: 8px 0 24px; }
        .profile { display: flex; align-items: center; gap: 10px; text-align: right; }
        .profile img, .avatar { width: 42px; height: 42px; border-radius: 50%; object-fit: cover; background: #e1efe3; }
        .avatar { display: grid; place-items: center; font-weight: 700; color: #28733a; }
        .profile small { display: block; color: #718075; margin-top: 3px; }
        .section { margin-top: 28px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 14px; }
        .card { background: #fff; border: 1px solid #e0e9e2; border-radius: 12px; padding: 18px; }
        .card h3 { margin: 0 0 8px; font-size: 16px; }
        .card p { color: #65756a; line-height: 1.5; margin: 6px 0; }
        .muted { color: #718075; font-size: 13px; }
        .tag { display: inline-block; padding: 4px 8px; border-radius: 20px; background: #edf5ee; color: #28733a; font-size: 12px; }
        .action { display: inline-block; margin-top: 12px; padding: 9px 12px; border-radius: 8px; color: #fff; background: #28733a; text-decoration: none; font-size: 14px; }
        .empty { padding: 22px; background: #fff; border: 1px dashed #bfcec2; border-radius: 12px; color: #65756a; }
        .list { display: grid; gap: 10px; }
        @media (max-width: 760px) {
            .layout { display: block; }
            aside { width: auto; padding: 14px; border-right: 0; border-bottom: 1px solid #dfe9e1; }
            nav { display: flex; overflow-x: auto; gap: 4px; }
            nav a { white-space: nowrap; }
            .logout { margin-top: 6px; }
            main { padding: 20px 14px; }
            header { align-items: flex-start; }
        }
    </style>
</head>
<body>
<div class="layout">
    @include('field_officer.partials.sidebar', ['active' => 'dashboard'])
    <main>
        <header>
            <div>
                <h1>Field Officer Dashboard</h1>
                <p class="welcome">Welcome back, {{ $officer->name }}. Here is your assigned fieldwork.</p>
            </div>
            <div class="profile">
                @if ($officer->profile_photo)
                    <img src="{{ asset('storage/'.$officer->profile_photo) }}" alt="Your profile photo">
                @else
                    <div class="avatar" aria-hidden="true">{{ mb_substr($officer->name, 0, 1) }}</div>
                @endif
                <div>
                    <strong>{{ $officer->name }}</strong>
                    <small>{{ str_replace('_', ' ', ucfirst($officer->role)) }}</small>
                    @if ($officer->username)
                        <small>{{ '@'.$officer->username }}</small>
                    @endif
                </div>
            </div>
        </header>

        @if (session('success'))
            <p class="card" role="status">{{ session('success') }}</p>
        @endif

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
    </main>
</div>
</body>
</html>
