<header class="k-topbar">
    <div class="k-topbar-left">
        <button type="button" class="k-menu-btn" id="k-menu" aria-label="Collapse sidebar" aria-expanded="true" aria-controls="k-sidebar">
            <svg class="k-icon" aria-hidden="true"><use href="#i-menu"></use></svg>
        </button>
        <a class="k-brand" href="{{ route($navItems->first()['route'] ?? 'login') }}">
            <img src="{{ asset('images/logo.png') }}" alt="KFHI Child Management System" width="124" height="48">
        </a>
    </div>

<div class="k-topbar-right">
        @if (config('kfhi_navigation.notifications', false))
        @include('layouts.partials.notification-bell')
        @endif

<details class="k-profile">
<summary aria-label="Open profile menu">
                @if ($user->profile_photo)
<img class="k-avatar" src="{{ asset('storage/'.$user->profile_photo) }}" alt="">
                @else
<span class="k-avatar k-avatar-initial" aria-hidden="true">{{ mb_substr($user->name, 0, 1) }}</span>
                @endif
<span class="k-profile-meta">
<strong>{{ $user->name }}</strong>
<span>{{ ucwords(str_replace('_', ' ', $user->role)) }}</span>
</span>
<svg class="k-icon" aria-hidden="true"><use href="#i-chevron-down"></use></svg>
</summary>
<div class="k-profile-menu">
<div class="k-profile-head">
<strong>{{ $user->name }}</strong>
<span>{{ ucwords(str_replace('_', ' ', $user->role)) }}</span>
</div>
<form method="POST" action="{{ route('logout') }}">
                    @csrf
<button type="submit">
<svg class="k-icon" aria-hidden="true"><use href="#i-log-out"></use></svg>
                        Log out
</button>
</form>
</div>
</details>
</div>
</header>