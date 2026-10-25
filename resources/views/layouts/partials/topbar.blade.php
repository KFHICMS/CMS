<header class="k-topbar">
    <div class="k-topbar-left">
        <button type="button" class="k-menu-btn" id="k-menu" aria-label="Open navigation menu" aria-expanded="false" aria-controls="k-sidebar">
            <svg class="k-icon" aria-hidden="true"><use href="#i-menu"></use></svg>
        </button>
        <a class="k-brand" href="{{ route($navItems->first()['route'] ?? 'login') }}">
            <img src="{{ asset('images/logo.png') }}" alt="" width="36" height="36">
            <span class="k-brand-text">
                <strong>KFHI</strong>
                <span>Child Management System</span>
            </span>
        </a>
    </div>

    <div class="k-topbar-right">
        @if (config('kfhi_navigation.notifications'))
            <a class="k-icon-btn" href="#" aria-label="Notifications">
                <svg class="k-icon" aria-hidden="true"><use href="#i-bell"></use></svg>
                @if (($unreadNotifications ?? 0) > 0)
                    <span class="k-dot" aria-hidden="true"></span>
                    <span class="sr-only">{{ $unreadNotifications }} unread</span>
                @endif
            </a>
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