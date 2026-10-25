@extends('layouts.app', ['active' => 'dashboard'])

@section('title', 'Admin Dashboard')
@section('heading', 'Dashboard')
@section('subheading', "Welcome back, ".auth()->user()->name.". Here's an overview of KFHI-CMS.")

@section('content')
    @php
        $cards = [
            ['label' => 'Total Children', 'value' => $stats['children'], 'note' => $stats['active_children'].' active', 'icon' => 'heart', 'route' => 'children.index'],
            ['label' => 'Active Users', 'value' => $stats['users'], 'note' => 'System users', 'icon' => 'users', 'route' => 'users.index'],
            ['label' => 'Total Events', 'value' => $stats['events'], 'note' => $stats['upcoming_events'].' upcoming', 'icon' => 'calendar', 'route' => 'events.index'],
            ['label' => "Today's Attendance", 'value' => $stats['attendance'], 'note' => 'Attendance records today', 'icon' => 'check', 'route' => null],
            ['label' => 'Pending Reviews', 'value' => $stats['pending_reviews'], 'note' => 'Questionnaire submissions awaiting review', 'icon' => 'clipboard', 'route' => 'admin_submissions.index', 'params' => ['review_status' => 'pending']],
            ['label' => 'Open Follow-ups', 'value' => $stats['follow_ups'], 'note' => 'Not yet completed', 'icon' => 'file', 'route' => 'admin_follow_ups.index', 'params' => ['status' => 'pending']],
        ];
    @endphp

    <section class="k-stat-grid" aria-label="Summary">
        @foreach ($cards as $card)
            @php($url = ($card['route'] ?? null) && \Illuminate\Support\Facades\Route::has($card['route']) ? route($card['route'], $card['params'] ?? []) : null)
            @if ($url)
                <a class="k-stat-card" href="{{ $url }}">
            @else
                <div class="k-stat-card">
            @endif
                <span class="k-stat-icon" aria-hidden="true"><svg class="k-icon"><use href="#i-{{ $card['icon'] }}"></use></svg></span>
                <span>
                    <span class="k-stat-label">{{ $card['label'] }}</span>
                    <strong>{{ number_format($card['value']) }}</strong>
                    <small>{{ $card['note'] }}</small>
                </span>
            @if ($url)
                </a>
            @else
                </div>
            @endif
        @endforeach
    </section>

    <section class="k-two-col" style="margin-top: 16px">
        <div class="k-card">
            <div class="k-card-head">
                <h2>Recent Activity</h2>
                <span>Latest system activities</span>
            </div>
            @forelse ($activity as $entry)
                <div class="k-activity-item">
                    <strong>{{ ucfirst(str_replace('_', ' ', $entry->action)) }}</strong>
                    @if ($entry->description)
                        <p>{{ $entry->description }}</p>
                    @endif
                    <small>
                        {{ $names[$entry->user_id] ?? 'System' }}
                        · {{ $entry->created_at?->diffForHumans() }}
                    </small>
                </div>
            @empty
                <div class="k-empty">No activity has been recorded yet.</div>
            @endforelse
        </div>

        <div class="k-card">
            <div class="k-card-head">
                <h2>Quick Actions</h2>
            </div>
            <div class="k-quick">
                @foreach ($actions as $action)
                    <a href="{{ $action['url'] }}">
                        <svg class="k-icon" aria-hidden="true"><use href="#i-{{ $action['icon'] }}"></use></svg>
                        {{ $action['label'] }}
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endsection