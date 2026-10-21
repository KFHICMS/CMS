<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Panel') | KFHI-CMS</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
        }

        body {
            min-height: 100vh;
            background: #eef7f0;
            color: #294432;
        }

        .layout {
            display: flex;
            min-height: 100vh;
        }

        /* Unified Admin Sidebar */
        .sidebar {
            width: 250px;
            background: #f4faf5;
            padding: 25px 18px;
            box-shadow: 5px 0 20px rgba(0, 70, 30, 0.08);
            position: fixed;
            inset: 0 auto 0 0;
            z-index: 100;
        }

        .brand {
            text-align: center;
            padding-bottom: 25px;
            border-bottom: 1px solid #dce9df;
        }

        .brand img {
            width: 70px;
            height: 70px;
            object-fit: contain;
            margin-bottom: 10px;
        }

        .brand h2 {
            color: #236b3b;
            font-size: 19px;
            letter-spacing: 0.5px;
        }

        .brand p {
            color: #7b897f;
            font-size: 11px;
            margin-top: 4px;
        }

        .nav {
            margin-top: 25px;
        }

        .nav-title {
            font-size: 11px;
            color: #8a978d;
            text-transform: uppercase;
            margin: 0 10px 10px;
            letter-spacing: 1px;
            font-weight: 700;
        }

        .nav a {
            display: block;
            text-decoration: none;
            color: #53665a;
            padding: 12px 14px;
            margin-bottom: 6px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .nav a:hover, .nav a.active {
            background: #e0f0e3;
            color: #28733a;
            font-weight: bold;
        }

        .logout-form {
            position: absolute;
            left: 18px;
            right: 18px;
            bottom: 25px;
        }

        .logout-button {
            width: 100%;
            border: none;
            background: #e8efe9;
            color: #385d43;
            padding: 12px;
            border-radius: 10px;
            cursor: pointer;
            font-size: 13px;
            font-weight: bold;
            transition: background 0.2s ease;
        }

        .logout-button:hover {
            background: #dce7dd;
            color: #1f4f2c;
        }

        /* Main Content Container */
        .main {
            margin-left: 250px;
            width: calc(100% - 250px);
            padding: 30px;
            min-height: 100vh;
        }

        /* Standard Top Bar */
        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            gap: 20px;
        }

        .topbar h1 {
            color: #215e34;
            font-size: 26px;
        }

        .topbar p {
            color: #798b7e;
            font-size: 13px;
            margin-top: 4px;
        }

        .user-info {
            text-align: right;
            background: #f4faf5;
            padding: 8px 16px;
            border-radius: 10px;
            border: 1px solid #dce9df;
        }

        .user-info strong {
            display: block;
            color: #205c33;
            font-size: 14px;
        }

        .user-info span {
            color: #849488;
            font-size: 12px;
            font-weight: bold;
        }

        /* Unified Alerts */
        .alert {
            padding: 14px 18px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: 600;
        }

        .alert-success, .success-alert {
            background: #e4f4e8;
            color: #28613c;
            border: 1px solid #cce8d3;
        }

        .alert-error, .error-alert, .alert-danger {
            background: #fbeaea;
            color: #b33a3a;
            border: 1px solid #f1cccc;
        }

        /* Standard Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 18px;
            border-radius: 10px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-primary, .add-btn {
            background: #25783f;
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(37, 120, 63, 0.2);
        }

        .btn-primary:hover, .add-btn:hover {
            background: #1e6334;
        }

        .btn-secondary {
            background: #e2ede5;
            color: #315a3d;
        }

        .btn-secondary:hover {
            background: #d4e3d8;
        }

        /* Responsive Breakpoints */
        @media (max-width: 900px) {
            .sidebar {
                width: 210px;
            }
            .main {
                margin-left: 210px;
                width: calc(100% - 210px);
                padding: 20px;
            }
        }

        @media (max-width: 650px) {
            .sidebar {
                display: none;
            }
            .main {
                margin-left: 0;
                width: 100%;
                padding: 16px;
            }
            .topbar {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
    @yield('styles')
</head>
<body>

<div class="layout">
    @include('admin.partials.sidebar')

    <main class="main">
        <header class="topbar">
            <div>
                <h1>@yield('page_title')</h1>
                <p>@yield('page_description')</p>
            </div>
            <div class="user-info">
                <strong>{{ auth()->user()->name }}</strong>
                <span>{{ ucfirst(str_replace('_', ' ', auth()->user()->role)) }}</span>
            </div>
        </header>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-error">
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </main>
</div>

@yield('scripts')
</body>
</html>
