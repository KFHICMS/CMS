<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') | KFHI CMS</title>
    <style>
        :root { font-family: Arial, sans-serif; color: #294432; background: #f3f8f4; }
        * { box-sizing: border-box; }
        body { margin: 0; }
        a { color: #28733a; }
        .layout { display: flex; min-height: 100vh; }
        aside { width: 245px; padding: 26px 18px; background: #fff; border-right: 1px solid #dfe9e1; }
        .brand { font-size: 20px; font-weight: 700; color: #236b3b; margin: 0 8px 8px; }
        .role { color: #718075; font-size: 13px; margin: 0 8px 28px; }
        .officer-profile { display: flex; align-items: center; gap: 10px; margin: -12px 8px 24px; }
        .officer-profile img, .officer-profile .avatar { width: 40px; height: 40px; border-radius: 50%; }
        .officer-profile img { object-fit: cover; }
        .officer-profile .avatar { display: grid; place-items: center; background: #e8f4ea; color: #236b3b; font-weight: 700; }
        .officer-profile strong { display: block; color: #294432; font-size: 14px; }
        nav a { display: block; padding: 11px 12px; margin: 4px 0; color: #53665a; text-decoration: none; border-radius: 9px; }
        nav a:hover, nav a.active { background: #e8f4ea; color: #236b3b; }
        .logout { margin-top: 26px; }
        .logout button { width: 100%; padding: 10px; border: 0; border-radius: 8px; background: #fbeaea; color: #a94343; cursor: pointer; }
        main { flex: 1; max-width: 1440px; padding: 28px 34px; margin: 0 auto; }
        header { padding-bottom: 20px; border-bottom: 1px solid #dfe9e1; }
        h1 { margin: 0; font-size: 25px; }
        .welcome { color: #65756a; margin: 8px 0 0; }
        .section { margin-top: 24px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 14px; }
        .card { background: #fff; border: 1px solid #e0e9e2; border-radius: 12px; padding: 18px; }
        .card h3 { margin: 0 0 8px; font-size: 16px; }
        .card p { color: #65756a; line-height: 1.5; margin: 6px 0; }
        .muted { color: #718075; font-size: 13px; }
        .tag { display: inline-block; padding: 4px 8px; border-radius: 20px; background: #edf5ee; color: #28733a; font-size: 12px; }
        .action { display: inline-block; margin-top: 12px; padding: 9px 12px; border-radius: 8px; color: #fff; background: #28733a; text-decoration: none; font-size: 14px; }
        .empty { padding: 22px; background: #fff; border: 1px dashed #bfcec2; border-radius: 12px; color: #65756a; }
        .list { display: grid; gap: 10px; }
        .tabs a { display: inline-block; margin: 0 8px 8px 0; padding: 7px 12px; border-radius: 20px; background: #e8f4ea; color: #236b3b; text-decoration: none; font-size: 14px; }
        .tabs a.current { background: #28733a; color: #fff; }
        @media (max-width: 760px) {
            .layout { display: block; }
            aside { width: auto; padding: 14px; border-right: 0; border-bottom: 1px solid #dfe9e1; }
            .officer-profile { margin: 0 8px 12px; }
            nav { display: flex; overflow-x: auto; gap: 4px; }
            nav a { white-space: nowrap; }
            .logout { margin-top: 6px; }
            main { padding: 20px 14px; }
        }
    </style>
</head>
<body>
<div class="layout">
    @include('field_officer.partials.sidebar', ['active' => $active ?? 'dashboard'])
    <main>
        <header>
            <h1>@yield('heading')</h1>
            @hasSection('subheading')
                <p class="welcome">@yield('subheading')</p>
            @endif
        </header>
        @if (session('success'))
            <p class="card" role="status">{{ session('success') }}</p>
        @endif
        @yield('content')
    </main>
</div>
</body>
</html>