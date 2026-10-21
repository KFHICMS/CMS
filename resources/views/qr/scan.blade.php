<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
<<<<<<< HEAD
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Child Identification | KFHI-CMS</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background: #eef7f1;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px 15px;
            color: #26352c;
        }

        .card {
            width: 100%;
            max-width: 650px;
            background: #ffffff;
            border-radius: 24px;
            padding: 35px;
            box-shadow: 0 15px 40px rgba(46, 125, 79, 0.12);
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo {
            width: 110px;
            max-height: 70px;
            object-fit: contain;
            margin-bottom: 15px;
        }

        .header h1 {
            color: #2e7d4f;
            font-size: 26px;
            margin-bottom: 8px;
        }

        .header p {
            color: #718078;
            font-size: 14px;
        }

        .verified {
            background: #e8f6ed;
            color: #2e7d4f;
            border: 1px solid #c9e5d3;
            padding: 12px 16px;
            border-radius: 12px;
            text-align: center;
            margin-bottom: 25px;
            font-weight: 600;
        }

        .child-section {
            display: flex;
            gap: 25px;
            align-items: center;
            padding: 20px;
            background: #f7fbf8;
            border-radius: 18px;
            margin-bottom: 25px;
        }

        .photo {
            width: 120px;
            height: 120px;
            border-radius: 18px;
            object-fit: cover;
            border: 3px solid #d6eadc;
            flex-shrink: 0;
        }

        .initial {
            width: 120px;
            height: 120px;
            border-radius: 18px;
            background: #dff0e5;
            color: #2e7d4f;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 42px;
            font-weight: bold;
            flex-shrink: 0;
        }

        .child-name {
            font-size: 24px;
            font-weight: 700;
            color: #234b32;
            margin-bottom: 8px;
        }

        .child-code {
            color: #718078;
            font-size: 14px;
        }

        .details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .detail {
            background: #f7fbf8;
            border-radius: 14px;
            padding: 15px;
        }

        .label {
            display: block;
            color: #789084;
            font-size: 12px;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .value {
            color: #26352c;
            font-weight: 600;
            font-size: 15px;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e3eee6;
            color: #829088;
            font-size: 12px;
        }

        @media (max-width: 600px) {
            .card {
                padding: 22px;
                border-radius: 18px;
            }

            .child-section {
                flex-direction: column;
                text-align: center;
            }

            .details {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

<div class="card">

    <div class="header">

        <img
            src="{{ asset('images/logo.png') }}"
            alt="KFHI Logo"
            class="logo"
        >

        <h1>Child Identification</h1>

        <p>KFHI Child Management System</p>

    </div>

    <div class="verified">
        ✓ QR Code Verified Successfully
    </div>

    <div class="child-section">

        @if($child->profile_photo)

            <img
                src="{{ asset('storage/' . $child->profile_photo) }}"
                alt="{{ $child->first_name }}"
                class="photo"
            >

        @else

            <div class="initial">
                {{ strtoupper(substr($child->first_name, 0, 1)) }}
            </div>

        @endif

        <div>

            <div class="child-name">
                {{ $child->first_name }}
                @if($child->middle_name)
                    {{ $child->middle_name }}
                @endif
                {{ $child->last_name }}
            </div>

            <div class="child-code">
                Child Code: {{ $child->child_code }}
            </div>

        </div>

    </div>

    <div class="details">

        <div class="detail">
            <span class="label">Date of Birth</span>
            <span class="value">
                {{ $child->date_of_birth?->format('d M Y') ?? 'Not Available' }}
            </span>
        </div>

        <div class="detail">
            <span class="label">Gender</span>
            <span class="value">
                {{ ucfirst($child->gender) }}
            </span>
        </div>

        <div class="detail">
            <span class="label">School</span>
            <span class="value">
                {{ $child->school_name ?: 'Not Available' }}
            </span>
        </div>

        <div class="detail">
            <span class="label">Grade</span>
            <span class="value">
                {{ $child->grade ?: 'Not Available' }}
            </span>
        </div>

        <div class="detail">
            <span class="label">District</span>
            <span class="value">
                {{ $child->district ?: 'Not Available' }}
            </span>
        </div>

        <div class="detail">
            <span class="label">Status</span>
            <span class="value">
                {{ ucfirst($child->status) }}
            </span>
        </div>

    </div>

    <div class="footer">
        KFHI Child Management System<br>
        Secure QR-Based Child Identification
    </div>

</div>

</body>
</html>
=======
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Secure Child Identification | KFHI CMS</title>
    <style>
        * { box-sizing: border-box; }
        body { min-height: 100vh; margin: 0; padding: 20px; display: grid; place-items: center; background: #eef7f1; color: #294432; font-family: Arial, sans-serif; }
        main { width: min(100%, 520px); padding: 28px; background: #fff; border: 1px solid #dce9df; border-radius: 14px; text-align: center; }
        h1 { color: #236b3b; }
        p { color: #65756a; line-height: 1.6; }
        a { display: inline-block; margin-top: 14px; padding: 10px 14px; border-radius: 8px; background: #28733a; color: #fff; text-decoration: none; }
    </style>
</head>
<body>
<main>
    <h1>Secure child identification</h1>
    <p>This QR code does not display child information directly. Sign in as an assigned Field Officer, open the authorized activity, and scan the code there.</p>
    <a href="{{ route('login') }}">Sign in to KFHI CMS</a>
</main>
</body>
</html>
>>>>>>> 08dd1234501cea8666179ed49a8a99ee95531b73
