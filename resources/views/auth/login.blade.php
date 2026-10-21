
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>KFHI-CMS | Login</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, Helvetica, sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #eef7f0;
        }

        .login-container {
            width: 100%;
            max-width: 420px;
            padding: 25px;
        }

        .login-card {
            background: #f4faf5;
            padding: 40px;
            border-radius: 25px;
            box-shadow:
                10px 10px 25px rgba(0, 70, 30, 0.12),
                -10px -10px 25px rgba(255, 255, 255, 0.9);
        }

        .logo {
            text-align: center;
            margin-bottom: 20px;
        }

        .logo img {
            width: 100px;
            height: auto;
            object-fit: contain;
        }

        .title {
            text-align: center;
            color: #236b3b;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .subtitle {
            text-align: center;
            color: #6b7d70;
            font-size: 14px;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #315c3e;
            font-size: 14px;
            font-weight: 600;
        }

        .form-group input {
            width: 100%;
            padding: 14px 16px;
            border: none;
            outline: none;
            border-radius: 12px;
            background: #edf5ef;
            color: #263c2c;
            font-size: 15px;

            box-shadow:
                inset 3px 3px 8px rgba(0, 70, 30, 0.08),
                inset -3px -3px 8px rgba(255, 255, 255, 0.8);
        }

        .form-group input:focus {
            box-shadow:
                inset 2px 2px 6px rgba(0, 70, 30, 0.08),
                0 0 0 2px rgba(46, 125, 50, 0.15);
        }

        .remember {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 22px;
            color: #64756a;
            font-size: 14px;
        }

        .remember input {
            width: 16px;
            height: 16px;
            accent-color: #2e7d32;
        }

        .login-button {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 12px;
            background: #2e7d32;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;

            box-shadow:
                5px 5px 12px rgba(0, 70, 30, 0.18),
                -5px -5px 12px rgba(255, 255, 255, 0.8);

            transition: 0.2s ease;
        }

        .login-button:hover {
            background: #256b2b;
            transform: translateY(-1px);
        }

        .error-message {
            background: #ffeaea;
            color: #b42318;
            padding: 12px 14px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .back-home {
            display: block;
            text-align: center;
            margin-top: 22px;
            color: #357447;
            text-decoration: none;
            font-size: 14px;
        }

        .back-home:hover {
            text-decoration: underline;
        }

        .footer-text {
            text-align: center;
            margin-top: 25px;
            color: #8a978d;
            font-size: 12px;
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 18px;
            }

            .login-card {
                padding: 30px 22px;
            }

            .title {
                font-size: 24px;
            }
        }
    </style>
</head>

<body>

<div class="login-container">

    <div class="login-card">

        <div class="logo">
            <img src="{{ asset('images/logo.png') }}" alt="KFHI Logo">
        </div>

        <h1 class="title">Welcome Back</h1>

        <p class="subtitle">
            Sign in to KFHI Child Management System
        </p>

        @if ($errors->any())
            <div class="error-message">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.process') }}">
            @csrf

            <div class="form-group">
                <label for="username">Username</label>

                <input
                    type="text"
                    id="username"
                    name="username"
                    value="{{ old('username') }}"
                    placeholder="Enter your username"
                    autocomplete="username"
                    required
                    autofocus
                >
            </div>

            <div class="form-group">
                <label for="password">Password</label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Enter your password"
                    autocomplete="current-password"
                    required
                >
            </div>

            <label class="remember">
                <input
                    type="checkbox"
                    name="remember"
                    value="1"
                    {{ old('remember') ? 'checked' : '' }}
                >

                Remember me
            </label>

            <button type="submit" class="login-button">
                Login
            </button>

        </form>

        <a href="{{ route('home') }}" class="back-home">
            ← Back to Home
        </a>

        <p class="footer-text">
            KFHI Child Management System
        </p>

    </div>

</div>

</body>
</html>
