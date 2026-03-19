<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SQL Sales Management System</title>
    <link rel="icon" type="image/png" href="{{ asset('sql-logo.png') }}?v=20260318">
    <link rel="shortcut icon" href="{{ asset('sql-logo.png') }}?v=20260318">
    <link rel="apple-touch-icon" href="{{ asset('sql-logo.png') }}?v=20260318">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script>
        (function () {
            try {
                if (window.sessionStorage && window.sessionStorage.getItem('loginSplashShown') === '1') {
                    document.documentElement.classList.add('login-splash-skip');
                }
            } catch (e) {}
        })();
    </script>
</head>
<body>
<div class="login-root">
    <div class="login-splash" id="loginSplash" aria-hidden="false">
        <div class="login-splash-panel" id="loginSplashPanel">
            <div class="login-splash-sky" aria-hidden="true">
                <span class="login-splash-star login-splash-star-a"></span>
                <span class="login-splash-star login-splash-star-b"></span>
                <span class="login-splash-star login-splash-star-c"></span>
                <span class="login-splash-star login-splash-star-d"></span>
                <span class="login-splash-star login-splash-star-e"></span>
                <span class="login-splash-star login-splash-star-f"></span>
                <span class="login-splash-star login-splash-star-g"></span>
                <span class="login-splash-star login-splash-star-h"></span>
                <span class="login-splash-star login-splash-star-i"></span>
                <span class="login-splash-moon">
                    <span class="login-splash-moon-cut"></span>
                    <span class="login-splash-moon-star"></span>
                </span>
            </div>

            <div class="login-splash-hero">
                <img src="{{ asset('sql-logo.png') }}" alt="SQL logo" class="login-splash-logo">
                <span class="login-splash-wordmark" aria-label="SMS">
                    <span>S</span>
                    <span>M</span>
                    <span>S</span>
                </span>
            </div>

            <div class="login-splash-typing" aria-label="SALES MANAGEMENT SYSTEM">
                <span class="login-splash-typing-text">SALES MANAGEMENT SYSTEM</span>
            </div>

            <div class="login-splash-illustration" aria-hidden="true">
                <img src="{{ asset('sql-cover-mascot.png') }}" alt="" class="login-splash-illustration-img">
            </div>

            <p class="login-splash-credits">Wei Jian &amp; Wen Jun &amp; Damien</p>
        </div>
    </div>

    <header class="login-header">
        <div class="login-header-left">
            <img src="{{ asset('sql-logo.png') }}" alt="SQL company logo" class="login-header-logo-img">
            <div class="login-logo-text">SQL Sales Management System</div>
        </div>
        <div class="login-header-right">
            <button class="login-help-link" type="button">Help</button>
            <button class="login-bell" type="button" aria-label="Notifications"><i class="bi bi-bell-fill" aria-hidden="true"></i></button>
        </div>
    </header>

    <main class="login-main">
        <div class="login-main-layout">
            <div class="login-card">
                <div class="login-logo">
                    <img src="{{ asset('sql-logo.png') }}" alt="SQL logo" class="login-logo-img">
                    <span class="login-logo-lms">SMS</span>
                </div>
                <p class="login-subtitle">Sales Management System</p>

                @if (!empty($show_register_passkey))
                    {{-- After sign-in: stay on login page and show only register passkey; then redirect to dashboard --}}
                    <div class="login-form">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        @if (session('success'))
                            <div class="login-message login-success">{{ session('success') }}</div>
                        @endif
                        <p class="login-passkey-note" style="margin-bottom: 1rem;">You're signed in. Register a passkey to use it next time, or skip to go to the dashboard.</p>
                        <button type="button" class="login-primary-btn" id="register-passkey-btn">
                            <i class="bi bi-shield-lock" aria-hidden="true"></i>
                            <span>Register passkey</span>
                        </button>
                        <p class="login-passkey-countdown" id="registerPasskeyCountdown" data-seconds="6" data-dashboard-url="{{ $dashboard_url ?? '/admin/dashboard' }}">
                            Redirect to dashboard in <span>6</span> seconds.
                        </p>
                        <p style="margin-top: 1rem; text-align: center;">
                            <a href="{{ $dashboard_url ?? '/admin/dashboard' }}" class="login-link-btn" id="skipToDashboardLink" style="font-size: 14px;">Skip, go to dashboard</a>
                        </p>
                    </div>
                @else
                <form method="POST" action="{{ route('login') }}" class="login-form" novalidate>
                    @csrf
                    <label class="login-label">
                        Email
                        <div class="login-input-wrapper">
                            <span class="login-input-icon"><i class="bi bi-person-fill" aria-hidden="true"></i></span>
                            <input type="email" id="loginEmail" name="email" placeholder="Enter your email" value="{{ old('email') }}" autocomplete="email" required autofocus>
                        </div>
                    </label>
                    <label class="login-label">
                        Password
                        <div class="login-input-wrapper">
                            <span class="login-input-icon"><i class="bi bi-lock-fill" aria-hidden="true"></i></span>
                            <input type="password" id="loginPassword" name="password" placeholder="********" required>
                            <button type="button" class="login-password-toggle" data-password-toggle data-target="loginPassword" aria-label="Show password" aria-pressed="false">
                                <i class="bi bi-eye" aria-hidden="true"></i>
                            </button>
                        </div>
                    </label>

                    @if ($errors->any())
                        <div class="login-message login-error">{{ $errors->first('email') ?: $errors->first('password') ?: $errors->first() }}</div>
                    @elseif (session('error'))
                        <div class="login-message login-error">{{ session('error') }}</div>
                    @elseif (session('success'))
                        <div class="login-message login-success">{{ session('success') }}</div>
                    @endif

                    @if (session('show_reset_password') && old('email') && !session('success'))
                        <button
                            type="button"
                            id="resetPasswordRequestButton"
                            class="login-link-btn"
                            style="text-align: left; padding-left: 0;"
                        >
                            Reset password
                        </button>
                    @endif

                    <button type="submit" class="login-primary-btn">Sign In</button>

                    <div class="login-divider"><span>OR</span></div>

                    <button type="button" class="login-passkey-btn" id="login-passkey-btn">
                        <i class="bi bi-shield-lock" aria-hidden="true"></i>
                        <span>Login with passkey</span>
                    </button>

                    <p class="login-passkey-note">
                        <span class="login-passkey-note-line">Sign in with email and password.</span><br>
                        <span class="login-passkey-note-line">You can add a passkey after logging in.</span>
                    </p>
                </form>
                @if (session('show_reset_password') && old('email') && !session('success'))
                <form method="POST" action="{{ route('password.forgot') }}" id="loginResetPasswordForm" style="display: none;">
                    @csrf
                    <input type="hidden" name="email" id="loginResetPasswordEmail" value="{{ old('email') }}">
                </form>
                @endif
                @endif
            </div>
        </div>
    </main>
</div>

<script>
(function () {
    function initPasswordToggles() {
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
    }

    initPasswordToggles();

    var registerPasskeyCountdown = document.getElementById('registerPasskeyCountdown');
    var skipToDashboardLink = document.getElementById('skipToDashboardLink');
    var registerPasskeyCountdownTimer = null;

    function stopRegisterPasskeyCountdown() {
        if (registerPasskeyCountdownTimer) {
            window.clearInterval(registerPasskeyCountdownTimer);
            registerPasskeyCountdownTimer = null;
        }
    }

    if (registerPasskeyCountdown) {
        var countdownValue = parseInt(registerPasskeyCountdown.getAttribute('data-seconds') || '6', 10);
        var countdownTarget = registerPasskeyCountdown.getAttribute('data-dashboard-url') || '';
        var countdownText = registerPasskeyCountdown.querySelector('span');

        registerPasskeyCountdownTimer = window.setInterval(function () {
            countdownValue -= 1;
            if (countdownText) {
                countdownText.textContent = String(Math.max(countdownValue, 0));
            }
            if (countdownValue <= 0) {
                stopRegisterPasskeyCountdown();
                if (countdownTarget !== '') {
                    window.location.href = countdownTarget;
                }
            }
        }, 1000);
    }

    if (skipToDashboardLink) {
        skipToDashboardLink.addEventListener('click', stopRegisterPasskeyCountdown);
    }

    var resetPasswordRequestButton = document.getElementById('resetPasswordRequestButton');
    var loginResetPasswordForm = document.getElementById('loginResetPasswordForm');
    var loginResetPasswordEmail = document.getElementById('loginResetPasswordEmail');
    var loginEmail = document.getElementById('loginEmail');

    if (resetPasswordRequestButton && loginResetPasswordForm && loginResetPasswordEmail) {
        resetPasswordRequestButton.addEventListener('click', function () {
            loginResetPasswordEmail.value = loginEmail ? loginEmail.value : '';
            loginResetPasswordForm.submit();
        });
    }

    var loginSplash = document.getElementById('loginSplash');
    var loginSplashPanel = document.getElementById('loginSplashPanel');
    var loginCard = document.querySelector('.login-main-layout .login-card');

    function syncLoginSplashSize() {
        if (!loginSplashPanel || !loginCard) {
            return;
        }

        var rect = loginCard.getBoundingClientRect();
        if (!rect.width || !rect.height) {
            return;
        }

        loginSplashPanel.style.width = Math.round(rect.width) + 'px';
        loginSplashPanel.style.maxWidth = Math.round(rect.width) + 'px';
        loginSplashPanel.style.height = Math.round(rect.height) + 'px';
        loginSplashPanel.style.minHeight = Math.round(rect.height) + 'px';
    }

    syncLoginSplashSize();
    window.requestAnimationFrame(syncLoginSplashSize);
    window.addEventListener('resize', syncLoginSplashSize);
    window.addEventListener('load', syncLoginSplashSize);

    if (loginSplash) {
        var skipSplash = false;

        try {
            skipSplash = window.sessionStorage && window.sessionStorage.getItem('loginSplashShown') === '1';
        } catch (e) {}

        if (skipSplash) {
            loginSplash.classList.add('is-hidden');
            loginSplash.setAttribute('aria-hidden', 'true');
        } else {
            window.setTimeout(function () {
                loginSplash.classList.add('is-iris-out');
                window.setTimeout(function () {
                    loginSplash.classList.add('is-hidden');
                    loginSplash.setAttribute('aria-hidden', 'true');
                    try {
                        if (window.sessionStorage) {
                            window.sessionStorage.setItem('loginSplashShown', '1');
                        }
                    } catch (e) {}
                }, 500);
            }, 2000);
        }
    }

    var loginError = document.querySelector('.login-message.login-error');
    if (loginError) {
        var errorText = (loginError.textContent || '').toLowerCase();
        if (
            errorText.indexOf('invalid email or password') !== -1 ||
            errorText.indexOf('invalid email address format') !== -1 ||
            errorText.indexOf('password is required') !== -1
        ) {
            setTimeout(function () {
                loginError.style.transition = 'opacity 180ms ease';
                loginError.style.opacity = '0';
                setTimeout(function () {
                    loginError.style.display = 'none';
                }, 180);
            }, 3000);
        }
    }

    if (!window.PublicKeyCredential) {
        var lp = document.getElementById('login-passkey-btn');
        if (lp) { lp.disabled = true; lp.title = 'Passkeys not supported in this browser'; }
        var rp = document.getElementById('register-passkey-btn');
        if (rp) { rp.disabled = true; rp.title = 'Passkeys not supported in this browser'; }
        return;
    }

    function base64urlToBuffer(base64url) {
        var base64 = base64url.replace(/-/g, '+').replace(/_/g, '/');
        var pad = base64.length % 4;
        if (pad) base64 += new Array(5 - pad).join('=');
        var bin = atob(base64);
        var buf = new Uint8Array(bin.length);
        for (var i = 0; i < bin.length; i++) buf[i] = bin.charCodeAt(i);
        return buf.buffer;
    }

    function bufferToBase64url(buffer) {
        var bytes = new Uint8Array(buffer);
        var bin = '';
        for (var i = 0; i < bytes.length; i++) bin += String.fromCharCode(bytes[i]);
        var base64 = btoa(bin);
        return base64.replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');
    }

    function transformGetOptions(options) {
        var pk = options.publicKey;
        pk.challenge = base64urlToBuffer(pk.challenge);
        if (pk.allowCredentials && pk.allowCredentials.length) {
            pk.allowCredentials = pk.allowCredentials.map(function (c) {
                return { type: 'public-key', id: base64urlToBuffer(c.id), transports: c.transports || [] };
            });
        }
        return options;
    }

    var loginPasskeyBtn = document.getElementById('login-passkey-btn');
    if (loginPasskeyBtn) {
    loginPasskeyBtn.addEventListener('click', function () {
        var btn = this;
        btn.disabled = true;
        fetch('{{ route("passkey.auth.options") }}', { method: 'GET', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (options) {
                return navigator.credentials.get({ publicKey: transformGetOptions(options).publicKey });
            })
            .then(function (cred) {
                if (!cred) return Promise.reject(new Error('No credential returned'));
                return fetch('{{ route("passkey.auth.verify") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        id: bufferToBase64url(cred.rawId),
                        clientDataJSON: bufferToBase64url(cred.response.clientDataJSON),
                        authenticatorData: bufferToBase64url(cred.response.authenticatorData),
                        signature: bufferToBase64url(cred.response.signature),
                        userHandle: cred.response.userHandle ? bufferToBase64url(cred.response.userHandle) : null
                    })
                });
            })
            .then(function (r) { return r.json().then(function (data) { return { ok: r.ok, data: data }; }); })
            .then(function (result) {
                if (result.ok && result.data.redirect) {
                    window.location.href = result.data.redirect;
                } else {
                    alert(result.data.error || 'Passkey sign-in failed.');
                    btn.disabled = false;
                }
            })
            .catch(function (err) {
                alert(err.message || 'Passkey sign-in failed.');
                btn.disabled = false;
            });
    });
    }

    // Register passkey (after sign-in, still on login page)
    var registerPasskeyBtn = document.getElementById('register-passkey-btn');
    if (registerPasskeyBtn) {
        var dashboardUrl = '{{ $dashboard_url ?? "" }}';
        function base64urlToBuffer(base64url) {
            var base64 = base64url.replace(/-/g, '+').replace(/_/g, '/');
            var pad = base64.length % 4;
            if (pad) base64 += new Array(5 - pad).join('=');
            var bin = atob(base64);
            var buf = new Uint8Array(bin.length);
            for (var i = 0; i < bin.length; i++) buf[i] = bin.charCodeAt(i);
            return buf.buffer;
        }
        function bufferToBase64url(buffer) {
            var bytes = new Uint8Array(buffer);
            var bin = '';
            for (var i = 0; i < bytes.length; i++) bin += String.fromCharCode(bytes[i]);
            return btoa(bin).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');
        }
        function transformCreateOptions(options) {
            var pk = options.publicKey;
            pk.challenge = base64urlToBuffer(pk.challenge);
            pk.user.id = base64urlToBuffer(pk.user.id);
            if (pk.excludeCredentials && pk.excludeCredentials.length) {
                pk.excludeCredentials = pk.excludeCredentials.map(function (c) {
                    return { type: 'public-key', id: base64urlToBuffer(c.id), transports: c.transports || [] };
                });
            }
            return options;
        }
        registerPasskeyBtn.addEventListener('click', function () {
            stopRegisterPasskeyCountdown();
            var btn = this;
            var nickname = prompt('Name this passkey (e.g. "My laptop"):', 'Device');
            if (nickname === null) return;
            nickname = nickname.trim() || 'Device';
            btn.disabled = true;
            var token = document.querySelector('input[name="_token"]').value;
            fetch('{{ route("passkey.register.options") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': token },
                credentials: 'same-origin',
                body: JSON.stringify({})
            })
            .then(function (r) {
                return r.text().then(function (text) {
                    var data = null;
                    try { data = text ? JSON.parse(text) : {}; } catch (e) { data = {}; }
                    return { ok: r.ok, status: r.status, data: data };
                });
            })
            .then(function (result) {
                if (!result.ok) {
                    alert((result.data && result.data.error) ? result.data.error : 'Could not start registration.');
                    btn.disabled = false;
                    return;
                }
                return navigator.credentials.create({ publicKey: transformCreateOptions(result.data).publicKey });
            })
            .then(function (cred) {
                if (!cred) return Promise.reject(new Error('No credential created'));
                return fetch('{{ route("passkey.register.verify") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': token },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        nickname: nickname,
                        clientDataJSON: bufferToBase64url(cred.response.clientDataJSON),
                        attestationObject: bufferToBase64url(cred.response.attestationObject)
                    })
                });
            })
            .then(function (r) { return r.json().then(function (data) { return { ok: r.ok, data: data }; }); })
            .then(function (result) {
                if (result.ok && result.data.success) {
                    window.location.href = dashboardUrl || '/admin/dashboard';
                } else {
                    alert(result.data && result.data.error ? result.data.error : 'Registration failed.');
                    btn.disabled = false;
                }
            })
            .catch(function (err) {
                alert(err.message || 'Registration failed.');
                btn.disabled = false;
            });
        });
    }
})();
</script>
</body>
</html>
