<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Response Submitted - KFHI
    </title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: #f1f8f3;
            font-family: Arial, sans-serif;
            color: #294a32;
        }

        .card {
            width: min(520px, 100%);
            background: #ffffff;
            border-radius: 20px;
            padding: 40px 30px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(35, 90, 55, 0.10);
            border-top: 5px solid #4caf50;
        }

        .icon {
            width: 70px;
            height: 70px;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: #e5f5e8;
            color: #2e7d32;
            font-size: 35px;
        }

        .brand {
            color: #4a8d58;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 10px;
        }

        h1 {
            color: #245f34;
            font-size: 26px;
            margin-bottom: 12px;
        }

        .message {
            color: #68756c;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .details {
            background: #f5faf6;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 22px;
            text-align: left;
        }

        .detail {
            display: flex;
            justify-content: space-between;
            gap: 15px;
            padding: 7px 0;
            font-size: 14px;
        }

        .label {
            color: #78907d;
        }

        .value {
            color: #285d37;
            font-weight: 700;
            text-align: right;
        }

        .back-btn {
            display: inline-block;
            padding: 12px 20px;
            border-radius: 10px;
            background: #3f914c;
            color: #ffffff;
            text-decoration: none;
            font-weight: 700;
            font-size: 14px;
        }

        .back-btn:hover {
            background: #327b3e;
        }

        @media (max-width: 500px) {
            .card {
                padding: 30px 20px;
            }

            h1 {
                font-size: 22px;
            }

            .detail {
                flex-direction: column;
                gap: 3px;
            }

            .value {
                text-align: left;
            }
        }
    </style>
</head>

<body>

<div class="card">

    <div class="icon">
        ✓
    </div>

    <div class="brand">
        KFHI Child Management System
    </div>

    <h1>
        Response Submitted Successfully
    </h1>

    <p class="message">
        Thank you. The questionnaire response has been
        successfully recorded for this child.
    </p>

    <div class="details">

        <div class="detail">
            <span class="label">
                Child
            </span>

            <span class="value">
                {{ $child->full_name ?? $child->name ?? 'N/A' }}
            </span>
        </div>

        <div class="detail">
            <span class="label">
                Child Code
            </span>

            <span class="value">
                {{ $child->child_code ?? $child->id }}
            </span>
        </div>

        <div class="detail">
            <span class="label">
                Questionnaire
            </span>

            <span class="value">
                {{ $questionnaire->title }}
            </span>
        </div>

    </div>

    <a
        href="{{ auth()->user()->role === 'enumerator' ? route('enumerator.dashboard') : url('/') }}"
        class="back-btn"
    >
        {{ auth()->user()->role === 'enumerator' ? 'Back to My Events' : 'Return to Home' }}
    </a>

</div>

</body>
</html>