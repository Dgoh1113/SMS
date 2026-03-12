@extends('layouts.app')
@section('title', 'Add passkey')
@section('content')
<header class="dashboard-header">
    <div>
        <h1 class="dashboard-title">Add passkey</h1>
        <p class="dashboard-subtitle">Register a passkey for this account so you can sign in with it next time.</p>
    </div>
</header>

<section class="dashboard-panel" style="max-width: 420px;">
    <div class="dashboard-panel-body">
        @if (!session('user_email'))
            <p>You must be signed in to add a passkey.</p>
        @else
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <p>You are signed in as <strong>{{ session('user_email') }}</strong>. Register a passkey to sign in with it next time.</p>
            <button type="button" class="login-primary-btn" id="register-passkey-btn" style="margin-top: 1rem;">
                <i class="bi bi-shield-lock" aria-hidden="true"></i>
                <span>Register passkey</span>
            </button>
            <p style="margin-top: 1rem;">
                <a href="{{ session('user_role') === 'admin' || session('user_role') === 'manager' ? route('admin.dashboard') : route('dealer.dashboard') }}" class="dashboard-profile-passkey-link">Skip, go to dashboard</a>
            </p>
        @endif
    </div>
</section>

@push('scripts')
@if (session('user_email') && session()->has('user_id'))
<script>
(function () {
    if (!window.PublicKeyCredential) {
        document.getElementById('register-passkey-btn').disabled = true;
        document.getElementById('register-passkey-btn').title = 'Passkeys not supported in this browser';
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

    function bufferToBase64url(buffer) {
        var bytes = new Uint8Array(buffer);
        var bin = '';
        for (var i = 0; i < bytes.length; i++) bin += String.fromCharCode(bytes[i]);
        return btoa(bin).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');
    }

    function getCsrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        if (meta) return meta.getAttribute('content');
        var input = document.querySelector('input[name="_token"]');
        return input ? input.value : '';
    }

    document.getElementById('register-passkey-btn').addEventListener('click', function () {
        var btn = this;
        var nickname = prompt('Name this passkey (e.g. "My laptop"):', 'Device');
        if (nickname === null) return;
        nickname = nickname.trim() || 'Device';
        btn.disabled = true;

        fetch('{{ route("passkey.register.options") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': getCsrfToken() },
            credentials: 'same-origin',
            body: JSON.stringify({})
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
                btn.disabled = false;
                return;
            }
            return navigator.credentials.create({ publicKey: transformCreateOptions(result.data).publicKey });
        })
        .then(function (cred) {
            if (!cred) return Promise.reject(new Error('No credential created'));
            return fetch('{{ route("passkey.register.verify") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': getCsrfToken() },
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
                alert('Passkey registered. You can now sign in with it next time.');
                window.location.href = '{{ session("user_role") === "admin" || session("user_role") === "manager" ? route("admin.dashboard") : route("dealer.dashboard") }}';
            } else {
                alert(result.data.error || 'Registration failed.');
                btn.disabled = false;
            }
        })
        .catch(function (err) {
            alert(err.message || 'Registration failed.');
            btn.disabled = false;
        });
    });
})();
</script>
@endif
@endpush
@endsection
