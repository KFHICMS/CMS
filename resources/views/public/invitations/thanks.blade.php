<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="referrer" content="no-referrer">
    <title>Thank you | KFHI</title>
    <style>
        body { margin: 0; background: #f3f8f4; color: #243b2c; font-family: Arial, sans-serif; line-height: 1.5; }
        main { max-width: 560px; margin: 0 auto; padding: 40px 16px; text-align: center; }
        .box { padding: 28px 20px; background: #fff; border: 1px solid #dfe9e1; border-radius: 14px; }
        a.btn { display: inline-block; margin-top: 16px; padding: 14px 20px; background: #28733a; color: #fff; border-radius: 10px; text-decoration: none; font-weight: 700; }
        .muted { color: #65756a; }
    </style>
</head>
<body>
<main>
    <div class="box">
        <h1>Thank you</h1>
        <p>Your response has been recorded.</p>
        <p class="muted">{{ $questionnaire->title }} · {{ $event->title }}</p>
        @if ($canSubmitAgain)
            <a class="btn" href="{{ route('public.invitations.show', $token) }}">Submit another response</a>
        @endif
    </div>
</main>
</body>
</html>