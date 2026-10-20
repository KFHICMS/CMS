<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>KFHI-CMS | Child Management System</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, Helvetica, sans-serif;
        }

        body {
            min-height: 100vh;
            background: #eef7f0;
            color: #254331;
        }

        /* Navigation */

        .navbar {
            width: 100%;
            padding: 18px 7%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: rgba(255, 255, 255, 0.75);
            box-shadow: 0 4px 20px rgba(0, 80, 40, 0.08);
            position: relative;
            z-index: 10;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .brand img {
            width: 55px;
            height: 55px;
            object-fit: contain;
        }

        .brand-text h2 {
            color: #236b3b;
            font-size: 20px;
        }

        .brand-text span {
            font-size: 11px;
            color: #728078;
        }

        .login-btn {
            text-decoration: none;
            background: #2e7d32;
            color: white;
            padding: 11px 24px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            box-shadow: 4px 4px 10px rgba(0, 70, 30, 0.15);
            transition: 0.2s ease;
        }

        .login-btn:hover {
            background: #256b2b;
            transform: translateY(-2px);
        }

        /* Hero */

        .hero {
            min-height: calc(100vh - 91px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 60px 7%;
        }

        .hero-content {
            max-width: 900px;
            text-align: center;
        }

        .hero-logo {
            width: 130px;
            height: 130px;
            object-fit: contain;
            margin-bottom: 25px;
        }

        .hero h1 {
            font-size: 48px;
            color: #216b38;
            margin-bottom: 15px;
            line-height: 1.15;
        }

        .hero h1 span {
            color: #3f8f4d;
        }

        .hero p {
            max-width: 700px;
            margin: 0 auto 30px;
            color: #65766b;
            font-size: 17px;
            line-height: 1.7;
        }

        .hero-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .primary-btn,
        .secondary-btn {
            padding: 14px 28px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            font-size: 15px;
            transition: 0.2s ease;
        }

        .primary-btn {
            background: #2e7d32;
            color: white;
            box-shadow: 5px 5px 12px rgba(0, 70, 30, 0.15);
        }

        .primary-btn:hover {
            background: #256b2b;
            transform: translateY(-2px);
        }

        .secondary-btn {
            background: #f4faf5;
            color: #2e7d32;
            box-shadow:
                5px 5px 12px rgba(0, 70, 30, 0.08),
                -5px -5px 12px rgba(255, 255, 255, 0.9);
        }

        .secondary-btn:hover {
            transform: translateY(-2px);
        }

        /* Information Cards */

        .features {
            padding: 20px 7% 70px;
        }

        .feature-grid {
            max-width: 1100px;
            margin: auto;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
        }

        .feature-card {
            background: #f4faf5;
            padding: 30px 25px;
            text-align: center;
            border-radius: 20px;
            box-shadow:
                8px 8px 20px rgba(0, 70, 30, 0.08),
                -8px -8px 20px rgba(255, 255, 255, 0.9);
        }

        .feature-icon {
            width: 55px;
            height: 55px;
            margin: 0 auto 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 15px;
            background: #e0f0e3;
            color: #2e7d32;
            font-size: 24px;
        }

        .feature-card h3 {
            color: #2b5f39;
            margin-bottom: 10px;
            font-size: 18px;
        }

        .feature-card p {
            color: #718077;
            font-size: 13px;
            line-height: 1.6;
        }

        /* Footer */

        footer {
            text-align: center;
            padding: 25px;
            color: #7c8b81;
            font-size: 12px;
        }

        /* Responsive */

        @media (max-width: 800px) {
            .hero h1 {
                font-size: 38px;
            }

            .feature-grid {
                grid-template-columns: 1fr;
                max-width: 500px;
            }
        }

        @media (max-width: 500px) {
            .navbar {
                padding: 15px 5%;
            }

            .brand-text span {
                display: none;
            }

            .hero {
                padding: 45px 5%;
            }

            .hero h1 {
                font-size: 32px;
            }

            .hero p {
                font-size: 15px;
            }

            .hero-logo {
                width: 100px;
                height: 100px;
            }
        }
    </style>
</head>

<body>

    <!-- Navigation -->

    <nav class="navbar">

        <div class="brand">

            <img
                src="{{ asset('images/logo.png') }}"
                alt="KFHI Logo"
            >

            <div class="brand-text">
                <h2>KFHI-CMS</h2>
                <span>Child Management System</span>
            </div>

        </div>

        <a href="{{ route('login') }}" class="login-btn">
            Login
        </a>

    </nav>


    <!-- Hero Section -->

    <section class="hero">

        <div class="hero-content">

            <img
                src="{{ asset('images/logo.png') }}"
                alt="KFHI Logo"
                class="hero-logo"
            >

            <h1>
                KFHI <span>Child Management System</span>
            </h1>

            <p>
                A secure digital platform designed to manage children's
                information, identification, attendance, events and
                follow-up activities efficiently and securely.
            </p>

            <div class="hero-buttons">

                <a
                    href="{{ route('login') }}"
                    class="primary-btn"
                >
                    Login to System
                </a>

                <a
                    href="#features"
                    class="secondary-btn"
                >
                    Learn More
                </a>

            </div>

        </div>

    </section>


    <!-- Features -->

    <section class="features" id="features">

        <div class="feature-grid">

            <div class="feature-card">

                <div class="feature-icon">
                    QR
                </div>

                <h3>
                    Secure QR Identification
                </h3>

                <p>
                    Quickly identify registered children using
                    secure QR technology without exposing sensitive
                    personal information.
                </p>

            </div>


            <div class="feature-card">

                <div class="feature-icon">
                    ✓
                </div>

                <h3>
                    Attendance Management
                </h3>

                <p>
                    Record and manage children's attendance for
                    KFHI events and activities efficiently.
                </p>

            </div>


            <div class="feature-card">

                <div class="feature-icon">
                    +
                </div>

                <h3>
                    Child Information
                </h3>

                <p>
                    Maintain organized child, guardian, event and
                    follow-up information in one secure system.
                </p>

            </div>

        </div>

    </section>


    <!-- Footer -->

    <footer>
        © {{ date('Y') }} KFHI Child Management System.
        All rights reserved.
    </footer>

</body>
</html>