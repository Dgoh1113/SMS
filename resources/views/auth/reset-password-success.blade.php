<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset Successful - SQL Sales Management System</title>
    <link rel="icon" type="image/png" href="{{ asset('sql-logo.png') }}?v=20260318">
    <link rel="shortcut icon" href="{{ asset('sql-logo.png') }}?v=20260318">
    <link rel="apple-touch-icon" href="{{ asset('sql-logo.png') }}?v=20260318">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
<div class="login-root">
    <main class="login-main">
        <div class="login-card">
            <div class="login-logo">
                <img src="{{ asset('sql-logo.png') }}" alt="SQL logo" class="login-logo-img">
                <span class="login-logo-lms">SMS</span>
            </div>
            <p class="login-subtitle">Reset Password</p>
            <div class="login-message login-success">{{ $message }}</div>
            <p class="login-passkey-note" style="margin-top: 10px;">
                This window will close in <span id="closeCountdown">5</span> seconds.
            </p>
        </div>
    </main>
</div>
<script>
(function () {
    var countdownEl = document.getElementById('closeCountdown');
    var seconds = 5;
    var timer = setInterval(function () {
        seconds -= 1;
        if (countdownEl) countdownEl.textContent = String(seconds);
        if (seconds <= 0) {
            clearInterval(timer);
            window.close();
        }
    }, 1000);
})();
</script>
</body>
</html>
