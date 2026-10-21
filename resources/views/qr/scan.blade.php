<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
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
