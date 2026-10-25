@php
    $user = auth()->user();
    $navItems = collect(config('kfhi_navigation.roles.'.$user->role, []))
        ->filter(fn ($item) => \Illuminate\Support\Facades\Route::has($item['route']))
        ->values();
    $active = $active ?? null;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') | KFHI CMS</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="stylesheet" href="{{ asset('css/kfhi-ui.css') }}">
    @stack('head')
</head>
<body class="k-body">
<a class="k-skip" href="#main-content">Skip to main content</a>

@include('layouts.partials.icons')

<div class="k-app" id="k-app">
    @include('layouts.partials.topbar')

    <div class="k-shell">
        @include('layouts.partials.sidebar')
        <div class="k-overlay" id="k-overlay" hidden></div>

        <div class="k-main">
            <main class="k-content" id="main-content" tabindex="-1">
                @isset($crumbs)
                    <x-breadcrumbs :items="$crumbs" />
                @endisset

                <div class="k-page-header">
                    <div>
                        <h1 class="k-page-title">@yield('heading')</h1>
                        @hasSection('subheading')
                            <p class="k-page-sub">@yield('subheading')</p>
                        @endif
                    </div>
                    @hasSection('actions')
                        <div class="k-page-actions">@yield('actions')</div>
                    @endif
                </div>

                @if (session('success'))
                    <div class="k-alert k-alert-success" role="status">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="k-alert k-alert-error" role="alert">{{ session('error') }}</div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</div>

<script>
(() => {
    const app = document.getElementById('k-app');
    const collapse = document.getElementById('k-collapse');
    const menu = document.getElementById('k-menu');
    const overlay = document.getElementById('k-overlay');
    const KEY = 'kfhi.sidebar.collapsed';
    const isMobile = () => window.matchMedia('(max-width: 900px)').matches;

    function setCollapsed(on) {
        app.classList.toggle('is-collapsed', on);
        collapse.setAttribute('aria-expanded', String(!on));
        collapse.setAttribute('aria-label', on ? 'Expand sidebar' : 'Collapse sidebar');
        try { localStorage.setItem(KEY, on ? '1' : '0'); } catch (e) { /* storage unavailable */ }
    }

    try {
        if (localStorage.getItem(KEY) === '1' && !isMobile()) setCollapsed(true);
    } catch (e) { /* storage unavailable */ }

    collapse.addEventListener('click', () => setCollapsed(!app.classList.contains('is-collapsed')));

    function setDrawer(open) {
        app.classList.toggle('drawer-open', open);
        overlay.hidden = !open;
        menu.setAttribute('aria-expanded', String(open));
    }

    menu.addEventListener('click', () => setDrawer(!app.classList.contains('drawer-open')));
    overlay.addEventListener('click', () => setDrawer(false));
    document.addEventListener('keydown', (event) => { if (event.key === 'Escape') setDrawer(false); });
    window.addEventListener('resize', () => { if (!isMobile()) setDrawer(false); });
})();
</script>
@stack('scripts')
</body>
</html>