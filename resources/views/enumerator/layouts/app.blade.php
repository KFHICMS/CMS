<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') | KFHI CMS</title>
    <style>
        :root { font-family: Arial, sans-serif; color: #294432; background: #f3f8f4; }
        * { box-sizing: border-box; }
        body { margin: 0; }
        a { color: #28733a; }
        .layout { display: flex; min-height: 100vh; }
        aside { width: 235px; padding: 26px 18px; background: #fff; border-right: 1px solid #dfe9e1; }
        .brand { font-size: 20px; font-weight: 700; color: #236b3b; margin: 0 8px 8px; }
        .role { color: #718075; font-size: 13px; margin: 0 8px 28px; }
        nav a { display: block; padding: 11px 12px; margin: 4px 0; color: #53665a; text-decoration: none; border-radius: 9px; }
        nav a.active, nav a:hover { background: #e8f4ea; color: #236b3b; }
        .logout { margin-top: 26px; }
        .logout button { width: 100%; padding: 10px; border: 0; border-radius: 8px; background: #fbeaea; color: #a94343; cursor: pointer; }
        main { flex: 1; max-width: 1300px; padding: 28px 34px; margin: 0 auto; }
        header { display: flex; justify-content: space-between; align-items: center; gap: 16px; padding-bottom: 20px; border-bottom: 1px solid #dfe9e1; }
        h1 { margin: 0; font-size: 25px; }
        .sub { color: #65756a; margin: 8px 0 0; }
        .profile { display: flex; align-items: center; gap: 10px; text-align: right; }
        .profile img, .avatar { width: 42px; height: 42px; border-radius: 50%; object-fit: cover; background: #e1efe3; }
        .avatar { display: grid; place-items: center; font-weight: 700; color: #28733a; }
        .profile small { display: block; color: #718075; margin-top: 3px; }
        .section { margin-top: 26px; }
        .section h2 { font-size: 19px; margin: 0 0 12px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 14px; }
        .card { background: #fff; border: 1px solid #e0e9e2; border-radius: 12px; padding: 18px; }
        .card h3 { margin: 0 0 8px; font-size: 16px; }
        .card p { color: #65756a; line-height: 1.5; margin: 6px 0; }
        .stat strong { display: block; font-size: 28px; }
        .stat span { color: #65756a; font-size: 13px; }
        .folder { display: block; text-decoration: none; color: inherit; }
        .folder:hover { border-color: #28733a; }
        .tag { display: inline-block; padding: 4px 8px; border-radius: 20px; background: #edf5ee; color: #28733a; font-size: 12px; margin: 2px 4px 2px 0; }
        .tag.off { background: #f1f1f1; color: #777; }
        .notice { padding: 14px 16px; border-radius: 10px; background: #fff7e0; border: 1px solid #f0dfa6; }
        .empty { padding: 22px; background: #fff; border: 1px dashed #bfcec2; border-radius: 12px; color: #65756a; }
        .muted { color: #718075; font-size: 13px; }
        .btn { display: inline-block; padding: 11px 16px; border: 0; border-radius: 8px; background: #28733a; color: #fff; font: inherit; cursor: pointer; text-decoration: none; }
        .btn.secondary { background: #e8f4ea; color: #236b3b; }
        .btn[disabled] { opacity: .6; cursor: not-allowed; }
        input[type=text] { width: 100%; padding: 11px; border: 1px solid #bdcbbf; border-radius: 8px; font: inherit; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px 8px; text-align: left; border-bottom: 1px solid #e6eee7; }
        @media (max-width: 760px) {
            .layout { display: block; }
            aside { width: auto; padding: 14px; border-right: 0; border-bottom: 1px solid #dfe9e1; }
            main { padding: 20px 14px; }
        }
    </style>
</head>
<body>
<div class="layout">
    <aside>
        <div class="brand">KFHI CMS</div>
        <p class="role">Enumerator workspace</p>
        <nav aria-label="Enumerator navigation">
            <a @class(['active' => ($active ?? 'dashboard') === 'dashboard']) href="{{ route('enumerator.dashboard') }}">Dashboard</a>
        </nav>
        <form class="logout" method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit">Log out</button>
        </form>
    </aside>
    <main>
        <header>
            <div>
                <h1>@yield('heading')</h1>
                @hasSection('subheading')<p class="sub">@yield('subheading')</p>@endif
            </div>
            @php($me = auth()->user())
            <div class="profile">
                @if ($me->profile_photo)
                    <img src="{{ asset('storage/'.$me->profile_photo) }}" alt="Your profile photo">
                @else
                    <div class="avatar" aria-hidden="true">{{ mb_substr($me->name, 0, 1) }}</div>
                @endif
                <div>
                    <strong>{{ $me->name }}</strong>
                    <small>{{ str_replace('_', ' ', ucfirst($me->role)) }}</small>
                </div>
            </div>
        </header>
        @if (session('success')) <p class="card" role="status">{{ session('success') }}</p> @endif
        @yield('content')
    </main>
</div>
@stack('scripts')
</body>
</html>