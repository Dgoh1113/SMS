<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - SQL Sales Management System</title>
    <link rel="icon" type="image/png" href="{{ asset('sql-logo.png') }}?v=20260318">
    <link rel="shortcut icon" href="{{ asset('sql-logo.png') }}?v=20260318">
    <link rel="apple-touch-icon" href="{{ asset('sql-logo.png') }}?v=20260318">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
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

            <form method="POST" action="{{ $formAction }}" class="login-form">
                @csrf
                <div class="login-passkey-note">
                    Set a new password for <strong>{{ $email }}</strong>.
                </div>

                <label class="login-label">
                    New password
                    <div class="login-input-wrapper">
                        <input type="password" id="resetPasswordInput" name="password" placeholder="Enter new password" required>
                        <button type="button" class="login-password-toggle" data-password-toggle data-target="resetPasswordInput" aria-label="Show password" aria-pressed="false">
                            <i class="bi bi-eye" aria-hidden="true"></i>
                        </button>
                    </div>
                </label>

                <label class="login-label">
                    Confirm password
                    <div class="login-input-wrapper">
                        <input type="password" id="resetPasswordConfirmInput" name="password_confirmation" placeholder="Confirm new password" required>
                        <button type="button" class="login-password-toggle" data-password-toggle data-target="resetPasswordConfirmInput" aria-label="Show password" aria-pressed="false">
                            <i class="bi bi-eye" aria-hidden="true"></i>
                        </button>
                    </div>
                </label>

                @if ($errors->any())
                    <div class="login-message login-error">{{ $errors->first() }}</div>
                @elseif (session('error'))
                    <div class="login-message login-error">{{ session('error') }}</div>
                @endif

                <button type="submit" class="login-primary-btn">Update password</button>
                <a href="{{ route('login') }}" class="login-link-btn" style="text-align: center;">Back to login</a>
            </form>
        </div>
    </main>
</div>
<script>
(function () {
    document.querySelectorAll('[data-password-toggle]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var targetId = btn.getAttribute('data-target');
            if (!targetId) return;
            var input = document.getElementById(targetId);
            if (!input) return;
            var icon = btn.querySelector('i');
            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.setAttribute('aria-pressed', show ? 'true' : 'false');
            btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
            if (icon) {
                icon.classList.toggle('bi-eye', !show);
                icon.classList.toggle('bi-eye-slash', show);
            }
        });
    });
})();
</script>
</body>
</html>
