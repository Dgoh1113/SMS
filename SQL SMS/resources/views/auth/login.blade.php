<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SQL Sales Management System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
<div class="login-root">
    <header class="login-header">
        <div class="login-header-left">
            <img src="{{ asset('sql-logo.png') }}" alt="SQL company logo" class="login-header-logo-img">
            <div class="login-logo-text">SQL Sales Management System</div>
        </div>
        <div class="login-header-right">
            <button class="login-help-link" type="button">Help</button>
            <button class="login-bell" type="button">🔔</button>
        </div>
    </header>

    <main class="login-main">
        <div class="login-card">
            <div class="login-logo">
                <img src="{{ asset('sql-logo.png') }}" alt="SQL logo" class="login-logo-img">
                <span class="login-logo-lms">SMS</span>
            </div>
            <p class="login-subtitle">Sales Management System</p>

            <form method="POST" action="{{ route('login') }}" class="login-form">
                @csrf
                <label class="login-label">
                    Email
                    <div class="login-input-wrapper">
                        <span class="login-input-icon">👤</span>
                        <input type="text" name="email" placeholder="Enter your email" value="{{ old('email') }}" required autofocus>
                    </div>
                </label>
                <label class="login-label">
                    Password
                    <div class="login-input-wrapper">
                        <span class="login-input-icon">🔒</span>
                        <input type="password" name="password" placeholder="••••••••" required>
                    </div>
                </label>

                @if (session('error'))
                    <div class="login-message login-error">{{ session('error') }}</div>
                @endif

                <button type="submit" class="login-primary-btn">Sign In →</button>

                <div class="login-divider"><span>OR</span></div>

                <button type="button" class="login-passkey-btn" id="login-passkey-btn">
                    <i class="bi bi-shield-lock" aria-hidden="true"></i>
                    <span>Login with passkey</span>
                </button>

                <p class="login-passkey-register">
                    <button type="button" class="login-link-btn" id="register-passkey-btn">Register a passkey</button>
                </p>
                <p class="login-passkey-hint">Use <strong>http://localhost:8080</strong> (not 127.0.0.1) for passkey to work.</p>
            </form>
        </div>
    </main>
</div>

<script>
(function () {
    if (!window.PublicKeyCredential) {
        document.getElementById('login-passkey-btn').disabled = true;
        document.getElementById('login-passkey-btn').title = 'Passkeys not supported in this browser';
        var regBtn = document.getElementById('register-passkey-btn');
        if (regBtn) regBtn.style.display = 'none';
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

    document.getElementById('login-passkey-btn').addEventListener('click', function () {
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

    var registerPasskeyBtn = document.getElementById('register-passkey-btn');
    if (registerPasskeyBtn) {
        registerPasskeyBtn.addEventListener('click', function () {
            var email = document.querySelector('input[name="email"]').value.trim();
            if (!email) {
                alert('Enter your email first (must be an existing account).');
                return;
            }
            var nickname = prompt('Name this passkey (e.g. "My laptop"):', 'Device');
            if (nickname === null) return;
            nickname = nickname.trim() || 'Device';

            fetch('{{ route("passkey.register.options") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value },
                credentials: 'same-origin',
                body: JSON.stringify({ email: email })
            })
            .then(function (r) {
                return r.text().then(function (text) {
                    var data = null;
                    try { data = text ? JSON.parse(text) : {}; } catch (e) { data = { error: 'Server returned an invalid response.' }; }
                    return { ok: r.ok, status: r.status, data: data };
                });
            })
            .then(function (result) {
                if (!result.ok) {
                    var msg = (result.data && result.data.error) ? result.data.error : ('Could not start registration (HTTP ' + result.status + ').');
                    alert(msg);
                    return;
                }
                return navigator.credentials.create({ publicKey: transformCreateOptions(result.data).publicKey });
            })
            .then(function (cred) {
                if (!cred) return Promise.reject(new Error('No credential created'));
                return fetch('{{ route("passkey.register.verify") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value },
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
                if (result.ok) {
                    alert('Passkey registered. You can now sign in with it.');
                } else {
                    alert(result.data.error || 'Registration failed.');
                }
            })
            .catch(function (err) {
                alert(err.message || 'Registration failed.');
            });
        });
    }
})();
</script>
</body>
</html>
