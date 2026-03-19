<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Password - SQL Sales Management System</title>
    <link rel="icon" type="image/png" href="{{ asset('sql-logo.png') }}?v=20260318">
    <link rel="shortcut icon" href="{{ asset('sql-logo.png') }}?v=20260318">
    <link rel="apple-touch-icon" href="{{ asset('sql-logo.png') }}?v=20260318">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>
    <div class="login-root">
        <main class="login-main">
            <div class="login-card">
                <div class="login-logo">
                    <img src="{{ asset('sql-logo.png') }}" alt="SQL logo" class="login-logo-img">
                    <span class="login-logo-lms">SMS</span>
                </div>
                <p class="login-subtitle">Create Password</p>

                <form method="POST" action="{{ $formAction }}" class="login-form">
                    @csrf

                    <div class="login-passkey-note">
                        You signed in with a temporary password for <strong>{{ $email }}</strong>.<br>
                        Create your own password to continue.
                    </div>

                    <label class="login-label">
                        New password
                        <div class="login-input-wrapper">
                            <input type="password" id="forcePasswordInput" name="password" placeholder="Enter new password" required>
                            <button type="button" class="login-password-toggle" data-password-toggle data-target="forcePasswordInput" aria-label="Show password" aria-pressed="false" tabindex="-1">
                                <i class="bi bi-eye" aria-hidden="true"></i>
                            </button>
                        </div>
                    </label>

                    <label class="login-label">
                        Confirm password
                        <div class="login-input-wrapper">
                            <input type="password" id="forcePasswordConfirmInput" name="password_confirmation" placeholder="Confirm new password" required>
                            <button type="button" class="login-password-toggle" data-password-toggle data-target="forcePasswordConfirmInput" aria-label="Show password" aria-pressed="false" tabindex="-1">
                                <i class="bi bi-eye" aria-hidden="true"></i>
                            </button>
                        </div>
                    </label>

                    @if ($errors->any())
                        <div class="login-message login-error">{{ $errors->first() }}</div>
                    @elseif (session('error'))
                        <div class="login-message login-error">{{ session('error') }}</div>
                    @endif

                    <button type="submit" class="login-primary-btn">Save password</button>
                </form>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-password-toggle]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var targetId = btn.getAttribute('data-target');
                    var input = document.getElementById(targetId);
                    if (!input) return;

                    var icon = btn.querySelector('i');
                    var show = input.type === 'password';
                    input.type = show ? 'text' : 'password';
                    btn.setAttribute('aria-pressed', show ? 'true' : 'false');
                    btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');

                    if (icon) {
                        icon.className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
                    }
                });
            });
        });
    </script>
</body>
</html>
