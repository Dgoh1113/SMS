<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pageTitle ?? 'Passkey Message - SQL Sales Management System' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('sql-logo.png') }}?v=20260318">
    <link rel="shortcut icon" href="{{ asset('sql-logo.png') }}?v=20260318">
    <link rel="apple-touch-icon" href="{{ asset('sql-logo.png') }}?v=20260318">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v=20260416-03">
</head>
<body>
<div class="login-root">
    <main class="login-main">
        <div class="login-card">
            <div class="login-logo">
                <img src="{{ asset('sql-logo.png') }}" alt="SQL logo" class="login-logo-img">
                <span class="login-logo-lms">SMS</span>
            </div>
            <p class="login-subtitle">{{ $subtitle ?? 'Passkey Sign-In' }}</p>
            <div class="login-message login-error">{{ $message }}</div>
            <p class="login-passkey-note">{{ $helperText ?? 'Please request a new passkey setup link.' }}</p>
            <a href="{{ route('login') }}" class="login-link-btn">Back to login</a>
        </div>
    </main>
</div>
</body>
</html>
