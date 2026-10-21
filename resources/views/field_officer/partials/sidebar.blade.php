@php($active = $active ?? 'dashboard')
<aside>
    <div class="brand">KFHI CMS</div>
    <p class="role">Field Officer workspace</p>
    @php($officer = auth()->user())
    <div class="officer-profile">
        @if ($officer?->profile_photo)
            <img src="{{ asset('storage/'.$officer->profile_photo) }}" alt="Your profile photo">
        @else
            <div class="avatar" aria-hidden="true">{{ mb_substr($officer->name, 0, 1) }}</div>
        @endif
        <strong>{{ $officer->name }}</strong>
    </div>
    <nav aria-label="Field officer navigation">
        <a @class(['active' => $active === 'dashboard']) href="{{ route('field_officer.dashboard') }}">Dashboard</a>
        <a @class(['active' => $active === 'programs']) href="{{ route('field_officer.programs.index') }}">My Programs</a>
        <a @class(['active' => $active === 'activities']) href="{{ route('field_officer.activities.index') }}">Today's Activities</a>
        <a @class(['active' => $active === 'follow-ups']) href="{{ route('field_officer.follow_ups.index') }}">Follow-ups</a>
        <a @class(['active' => $active === 'reports']) href="{{ route('field_officer.reports.index') }}">Reports</a>
    </nav>
    <form class="logout" method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit">Log out</button>
    </form>
</aside>