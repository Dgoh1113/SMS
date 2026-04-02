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
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v=20260402-03">
    <script src="{{ asset('js/passkey-registration.js') }}"></script>
</head>
<body>
<div class="login-root{{ !empty($show_register_passkey) ? ' login-root-passkey-setup' : '' }}">
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
            <div class="login-card{{ !empty($show_register_passkey) ? ' login-card-passkey-setup' : ' login-card-passkey-login' }}">
                <div class="login-logo">
                    <img src="{{ asset('sql-logo.png') }}" alt="SQL logo" class="login-logo-img">
                    <span class="login-logo-lms">SMS</span>
                </div>
                <p class="login-subtitle">Sales Management System</p>

                @if (!empty($show_register_passkey))
                    {{-- After sign-in: show the login-style passkey setup window instead of the console page. --}}
                    <div class="login-form login-form-passkey-login">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        @if (session('success'))
                            <div class="login-message login-success">{{ session('success') }}</div>
                        @endif
                        <p class="login-passkey-note" style="margin-bottom: 1rem;">
                            @if (!empty($passkey_setup_required))
                                You're signed in as <strong>{{ session('user_email') }}</strong>. Register your passkey here before entering the console.
                            @else
                                You're signed in as <strong>{{ session('user_email') }}</strong>. Register a passkey to use it next time, or skip to go to the dashboard.
                            @endif
                        </p>
                        <div style="display: grid; gap: 12px;">
                            <button type="button" class="login-primary-btn" id="register-passkey-phone-btn">
                                <i class="bi bi-phone" aria-hidden="true"></i>
                                <span>Use Phone / Scan QR</span>
                            </button>
                            <button type="button" class="login-passkey-btn" id="register-passkey-btn">
                                <i class="bi bi-laptop" aria-hidden="true"></i>
                                <span>{{ !empty($passkey_setup_required) ? 'Set Up On This Device' : 'Register On This Device' }}</span>
                            </button>
                        </div>
                        <p class="login-passkey-note" style="margin-top: 12px;">
                            On Windows or desktop, the phone option opens the browser passkey window so you can choose iPhone or Android and scan the QR code there.
                        </p>
                        @if (empty($passkey_setup_required))
                            <p class="login-passkey-countdown" id="registerPasskeyCountdown" data-seconds="6" data-dashboard-url="{{ $dashboard_url ?? '/admin/dashboard' }}">
                                Redirect to dashboard in <span>6</span> seconds.
                            </p>
                            <p style="margin-top: 1rem; text-align: center;">
                                <a href="{{ $dashboard_url ?? '/admin/dashboard' }}" class="login-link-btn" id="skipToDashboardLink" style="font-size: 14px;">Skip, go to dashboard</a>
                            </p>
                        @endif
                    </div>
                @else
                    <div class="login-form login-form-passkey-login">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">

                        @if ($errors->any())
                            <div class="login-message login-error">{{ $errors->first() }}</div>
                        @elseif (session('error'))
                            <div class="login-message login-error">{{ session('error') }}</div>
                        @elseif (session('success'))
                            <div class="login-message login-success">{{ session('success') }}</div>
                        @endif

                        <button type="button" class="login-primary-btn" id="login-passkey-btn">
                            <i class="bi bi-shield-lock" aria-hidden="true"></i>
                            <span>Login with passkey</span>
                        </button>

                        <p class="login-passkey-note" style="margin-top: 1rem;">
                            Sign in with your passkey. For first-time setup, use the passkey setup link sent by your administrator.
                        </p>

                    </div>
                @endif
            </div>
        </div>
    </main>
</div>

<script>
(function () {
    var loginSplash = document.getElementById('loginSplash');
    if (loginSplash) {
        window.setTimeout(function () {
            loginSplash.hidden = true;
            loginSplash.setAttribute('aria-hidden', 'true');
        }, 2000);
    }

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

    var passkeyUtils = window.SQLSMSPasskey;

    if (!window.PublicKeyCredential || !passkeyUtils) {
        var lp = document.getElementById('login-passkey-btn');
        if (lp) { lp.disabled = true; lp.title = 'Passkeys not supported in this browser'; }
        var rp = document.getElementById('register-passkey-btn');
        if (rp) { rp.disabled = true; rp.title = 'Passkeys not supported in this browser'; }
        return;
    }

    var loginPasskeyBtn = document.getElementById('login-passkey-btn');
    if (loginPasskeyBtn) {
    loginPasskeyBtn.addEventListener('click', function () {
        var btn = this;
        btn.disabled = true;
        fetch('{{ route("passkey.auth.options") }}', { method: 'GET', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (options) {
                return navigator.credentials.get({ publicKey: passkeyUtils.transformGetOptions(options).publicKey });
            })
            .then(function (cred) {
                if (!cred) return Promise.reject(new Error('No credential returned'));
                return fetch('{{ route("passkey.auth.verify") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        id: passkeyUtils.bufferToBase64url(cred.rawId),
                        clientDataJSON: passkeyUtils.bufferToBase64url(cred.response.clientDataJSON),
                        authenticatorData: passkeyUtils.bufferToBase64url(cred.response.authenticatorData),
                        signature: passkeyUtils.bufferToBase64url(cred.response.signature),
                        userHandle: cred.response.userHandle ? passkeyUtils.bufferToBase64url(cred.response.userHandle) : null
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
    var registerPasskeyPhoneBtn = document.getElementById('register-passkey-phone-btn');
    if (registerPasskeyBtn || registerPasskeyPhoneBtn) {
        var dashboardUrl = '{{ $dashboard_url ?? "" }}';
        function setRegisterButtonsDisabled(disabled) {
            [registerPasskeyBtn, registerPasskeyPhoneBtn].forEach(function (button) {
                if (button) button.disabled = !!disabled;
            });
        }
        registerPasskeyBtn.addEventListener('click', function () {
            startPasskeyRegistration('device');
        });
        if (registerPasskeyPhoneBtn) {
            registerPasskeyPhoneBtn.addEventListener('click', function () {
                startPasskeyRegistration('phone');
            });
        }
        function startPasskeyRegistration(preference) {
            stopRegisterPasskeyCountdown();
            var nicknamePromptDefault = preference === 'phone' ? 'My phone' : 'This device';
            var nickname = prompt(
                preference === 'phone'
                    ? 'Name this phone passkey (e.g. "My iPhone" or "My Android"):'
                    : 'Name this passkey (e.g. "My laptop"):',
                nicknamePromptDefault
            );
            if (nickname === null) return;
            setRegisterButtonsDisabled(true);
            passkeyUtils.register({
                preference: preference,
                optionsUrl: '{{ route("passkey.register.options") }}',
                verifyUrl: '{{ route("passkey.register.verify") }}',
                csrfToken: document.querySelector('input[name="_token"]').value,
                getNickname: function () {
                    return nickname;
                }
            })
            .then(function (result) {
                if (result && result.success) {
                    window.location.href = dashboardUrl || '/admin/dashboard';
                } else {
                    alert(result && result.error ? result.error : 'Registration failed.');
                    setRegisterButtonsDisabled(false);
                }
            })
            .catch(function (err) {
                if (err && err.cancelled) {
                    setRegisterButtonsDisabled(false);
                    return;
                }
                alert(err.message || 'Registration failed.');
                setRegisterButtonsDisabled(false);
            });
        }
    }
})();
</script>
</body>
</html>
