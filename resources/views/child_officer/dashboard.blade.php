@extends('layouts.app', ['active' => 'dashboard'])

@section('title', 'Child Officer Dashboard')
@section('heading', 'Child Officer Dashboard')
@section('subheading', 'Manage and monitor registered children.')

@section('content')
    @php
        $actions = [
            ['label' => 'Register Child', 'url' => route('child_officer.children.create'), 'icon' => 'heart'],
            ['label' => 'Scan QR', 'url' => route('child_officer.qr.scan'), 'icon' => 'scan'],
            ['label' => 'Search Child', 'url' => route('child_officer.children.index'), 'icon' => 'search'],
        ];
    @endphp

    <section class="k-stat-grid" aria-label="Summary">
        <div class="k-stat-card">
            <span class="k-stat-icon" aria-hidden="true"><svg class="k-icon"><use href="#i-heart"></use></svg></span>
            <span>
                <span class="k-stat-label">Total Children</span>
                <strong>{{ $totalChildren }}</strong>
                <small>{{ $activeChildren }} active records</small>
            </span>
        </div>

        <div class="k-stat-card">
            <span class="k-stat-icon" aria-hidden="true"><svg class="k-icon"><use href="#i-check"></use></svg></span>
            <span>
                <span class="k-stat-label">Active Children</span>
                <strong>{{ $activeChildren }}</strong>
                <small>Currently active</small>
            </span>
        </div>

        <div class="k-stat-card">
            <span class="k-stat-icon" aria-hidden="true"><svg class="k-icon"><use href="#i-user"></use></svg></span>
            <span>
                <span class="k-stat-label">Inactive Children</span>
                <strong>{{ $inactiveChildren }}</strong>
                <small>Not currently active</small>
            </span>
        </div>

        <div class="k-stat-card">
            <span class="k-stat-icon" aria-hidden="true"><svg class="k-icon"><use href="#i-file"></use></svg></span>
            <span>
                <span class="k-stat-label">Follow-ups</span>
                <strong>{{ $followUps }}</strong>
                <small>Open follow-up items</small>
            </span>
        </div>
    </section>

    <section class="k-two-col" style="margin-top: 16px;">
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

        <div class="k-card">
            <div class="k-card-head">
                <h2>Recent Children</h2>
                <span>Latest updates</span>
            </div>

            @if ($recentChildren->isEmpty())
                <div class="k-empty">No children registered yet.</div>
            @else
                <div class="k-stack">
                    @foreach ($recentChildren as $child)
                        <div class="k-activity-item">
                            <strong>{{ $child->first_name }} {{ $child->middle_name }} {{ $child->last_name }}</strong>
                            <p>{{ $child->child_code }} · {{ $child->district ?? 'N/A' }}</p>
                            <small>{{ ucfirst($child->status) }} · <a href="{{ route('child_officer.children.show', $child) }}">View</a></small>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endsection