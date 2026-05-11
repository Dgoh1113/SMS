<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SQL Sales Management System</title>
    <link rel="icon" type="image/png" href="{{ asset('sql-logo.png') }}?v=20260318">
    <link rel="shortcut icon" href="{{ asset('sql-logo.png') }}?v=20260318">
    <link rel="apple-touch-icon" href="{{ asset('sql-logo.png') }}?v=20260318">
    <script>
        (function () {
            try {
                if (localStorage.getItem('sqlsms-theme') === 'dark') {
                    document.documentElement.classList.add('theme-dark');
                }
            } catch (e) {}
        })();
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v=20260417-01">
<script src="{{ asset('js/passkey-registration.js') }}?v=20260427-02"></script>
<style>
.login-help-modal {
    position: fixed;
    inset: 0;
    z-index: 2000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
}

.login-help-modal.is-active {
    opacity: 1;
    visibility: visible;
}

.login-help-modal-overlay {
    position: absolute;
    inset: 0;
    background: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(8px);
}

.login-help-modal-container {
    position: relative;
    background: var(--card-bg, #fff);
    width: 100%;
    max-width: 500px;
    border-radius: 32px;
    padding: 40px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    transform: translateY(20px);
    transition: transform 0.3s ease;
}

.login-help-modal.is-active .login-help-modal-container {
    transform: translateY(0);
}

.login-help-modal-close {
    position: absolute;
    top: 24px;
    right: 24px;
    border: none;
    background: transparent;
    font-size: 28px;
    color: var(--text-muted);
    cursor: pointer;
    line-height: 1;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: background 0.2s;
}

.login-help-modal-close:hover {
    background: rgba(0,0,0,0.05);
}

.login-help-modal-header {
    text-align: center;
    margin-bottom: 32px;
}

.login-help-modal-header i {
    font-size: 48px;
    color: var(--primary);
    display: block;
    margin-bottom: 16px;
}

.login-help-modal-header h2 {
    font-size: 24px;
    font-weight: 700;
    margin: 0;
    color: var(--text-main);
}

.login-help-steps {
    display: flex;
    flex-direction: column;
    gap: 24px;
    margin-bottom: 32px;
}

.login-help-step {
    display: flex;
    gap: 20px;
    text-align: left;
}

.login-help-step-number {
    flex-shrink: 0;
    width: 36px;
    height: 36px;
    background: var(--primary);
    color: #fff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 18px;
}

.login-help-step-text h3 {
    font-size: 16px;
    font-weight: 600;
    margin: 0 0 4px;
    color: var(--text-main);
}

.login-help-step-text p {
    font-size: 14px;
    margin: 0;
    color: var(--text-muted);
    line-height: 1.5;
}

#closeHelpModalBtn {
    margin-top: 0;
}
</style>
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
        </div>
    </div>

    <header class="login-header">
        <div class="login-header-left">
            <img src="{{ asset('sql-logo.png') }}" alt="SQL company logo" class="login-header-logo-img">
            <div class="login-logo-text">SQL Sales Management System</div>
        </div>
        <div class="login-header-right">
            <button type="button" class="login-theme-toggle" data-theme-toggle aria-label="Enable dark mode" title="Enable dark mode">
                <i class="bi bi-moon-fill" data-theme-icon aria-hidden="true"></i>
            </button>
            <button class="login-bell" type="button" aria-label="Notifications"><i class="bi bi-bell-fill" aria-hidden="true"></i></button>
            <button class="login-help-link" type="button" id="loginSearchBtn"><i class="bi bi-search" style="margin-right: 6px;"></i>Check Status</button>
            <button class="login-help-link" type="button" id="loginHelpBtn">Help</button>
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

                        @if (!empty($show_test_login_shortcuts))
                            <div class="login-test-shortcuts">
                                <div class="login-test-shortcuts-title">Testing Mode</div>
                                <div class="login-test-shortcuts-buttons">
                                    <a href="{{ route('login.testing', ['role' => 'admin']) }}" class="login-test-shortcut-btn">Login to admin</a>
                                    <a href="{{ route('login.testing', ['role' => 'dealer']) }}" class="login-test-shortcut-btn login-test-shortcut-btn-secondary">Login to dealer</a>
                                </div>
                            </div>
                        @endif

                    </div>
                @endif
            </div>
        </div>
    </main>
</div>

<!-- Help Modal -->
<div id="loginHelpModal" class="login-help-modal" aria-hidden="true" style="display: none;">
    <div class="login-help-modal-overlay"></div>
    <div class="login-help-modal-container">
        <button type="button" class="login-help-modal-close" id="closeHelpModal" aria-label="Close help">
            <i class="bi bi-x"></i>
        </button>
        <div class="login-help-modal-content">
            <div class="login-help-modal-header">
                <i class="bi bi-question-circle-fill"></i>
                <h2>How to Access Your Account</h2>
            </div>
            <div class="login-help-steps">
                <div class="login-help-step">
                    <div class="login-help-step-number">1</div>
                    <div class="login-help-step-text">
                        <h3>Check Your Email</h3>
                        <p>If this is your first time, use the passkey registration link sent to your email by your administrator.</p>
                    </div>
                </div>
                <div class="login-help-step">
                    <div class="login-help-step-number">2</div>
                    <div class="login-help-step-text">
                        <h3>Register Your Passkey</h3>
                        <p>Follow the link to register a passkey on your device. This will be your primary way to sign in safely.</p>
                    </div>
                </div>
                <div class="login-help-step">
                    <div class="login-help-step-number">3</div>
                    <div class="login-help-step-text">
                        <h3>Login & Manage</h3>
                        <p>Once registered, return here to log in. You can manage or add more passkeys from your dashboard settings.</p>
                    </div>
                </div>
            </div>
            <button type="button" class="login-primary-btn" id="closeHelpModalBtn">Got it, thanks!</button>
        </div>
    </div>
</div>

<!-- Customer Search Modal -->
<div id="loginSearchModal" class="login-help-modal" aria-hidden="true" style="display: none;">
    <div class="login-help-modal-overlay"></div>
    <div class="login-help-modal-container" style="max-width: 600px; border-radius: 24px; padding: 32px;">
        <button type="button" class="login-help-modal-close" id="closeSearchModal" aria-label="Close">
            <i class="bi bi-x"></i>
        </button>
        <div class="login-help-modal-content">
            <div class="login-help-modal-header" style="margin-bottom: 24px;">
                <i class="bi bi-person-badge-fill" style="color: #6366f1;"></i>
                <h2 style="font-size: 22px;">SQL Account Status Check</h2>
                <p style="color: var(--text-muted); font-size: 14px; margin-top: 8px;">Check your company support status and details.</p>
            </div>
            
            <div class="login-search-box" style="display: flex; gap: 8px; margin-bottom: 24px;">
                <div style="position: relative; flex: 1;">
                    <input type="text" id="customerSearchInput" placeholder="Enter Company Name or Customer Code..." 
                        style="width: 100%; height: 48px; padding: 0 16px 0 44px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 15px; outline: none; transition: border-color 0.2s;">
                    <i class="bi bi-search" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 18px;"></i>
                </div>
                <button type="button" id="execCustomerSearchBtn" class="login-primary-btn" style="height: 48px; min-width: 100px; padding: 0 20px; font-size: 14px; border-radius: 12px; position: static;">Search</button>
            </div>

            <div id="customerSearchResults" style="max-height: 350px; overflow-y: auto; display: none; padding-right: 4px;">
                <!-- Results populated here -->
            </div>

            <div id="customerSearchEmpty" style="display: none; text-align: center; padding: 40px 20px;">
                <i class="bi bi-search" style="font-size: 40px; color: #e2e8f0; display: block; margin-bottom: 12px;"></i>
                <p style="color: #64748b; font-size: 15px;">No results found. Try a different keyword.</p>
            </div>

            <div id="customerSearchLoading" style="display: none; text-align: center; padding: 40px 20px;">
                <div class="login-spinner" style="width: 32px; height: 32px; border: 3px solid #f3f3f3; border-top: 3px solid #6366f1; border-radius: 50%; display: inline-block; animation: spin 1s linear infinite;"></div>
                <p style="color: #64748b; font-size: 14px; margin-top: 12px;">Searching SQL database...</p>
            </div>
        </div>
    </div>
</div>

<style>
@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
.customer-result-item {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 12px;
    transition: all 0.2s;
}
.customer-result-item:hover {
    border-color: #6366f1;
    background: #f1f5f9;
}
.customer-result-badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    margin-bottom: 8px;
}
.badge-active { background: #dcfce7; color: #15803d; }
.badge-inactive { background: #fee2e2; color: #b91c1c; }
.customer-result-title { font-weight: 700; font-size: 15px; color: #1e293b; margin-bottom: 4px; display: block; }
.customer-result-code { font-family: monospace; color: #64748b; font-size: 13px; }
.customer-result-details { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 12px; padding-top: 12px; border-top: 1px dashed #cbd5e1; }
.customer-detail-label { font-size: 11px; color: #94a3b8; text-transform: uppercase; display: block; margin-bottom: 2px; }
.customer-detail-value { font-size: 13px; font-weight: 600; color: #475569; }
</style>

<script>
(function () {
    var THEME_KEY = 'sqlsms-theme';

    function getStoredTheme() {
        try {
            return localStorage.getItem(THEME_KEY) === 'dark' ? 'dark' : 'light';
        } catch (e) {
            return 'light';
        }
    }

    function isDarkTheme() {
        return document.documentElement.classList.contains('theme-dark');
    }

    function updateThemeToggle(button) {
        if (!button) return;

        var dark = isDarkTheme();
        var icon = button.querySelector('[data-theme-icon]');
        button.setAttribute('aria-label', dark ? 'Switch to light mode' : 'Switch to dark mode');
        button.setAttribute('title', dark ? 'Switch to light mode' : 'Switch to dark mode');
        button.setAttribute('data-theme-state', dark ? 'dark' : 'light');

        if (icon) {
            icon.classList.remove('bi-moon-fill', 'bi-brightness-high-fill');
            icon.classList.add(dark ? 'bi-brightness-high-fill' : 'bi-moon-fill');
        }
    }

    function syncThemeToggles() {
        document.querySelectorAll('[data-theme-toggle]').forEach(updateThemeToggle);
    }

    var themeAnimationTimer = null;
    var themeToggleSpinTimer = null;

    function shouldAnimateTheme() {
        return !(window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches);
    }

    function runThemeAnimation() {
        if (!shouldAnimateTheme()) {
            return;
        }

        window.clearTimeout(themeAnimationTimer);
        document.documentElement.classList.add('theme-animating');
        themeAnimationTimer = window.setTimeout(function () {
            document.documentElement.classList.remove('theme-animating');
        }, 460);
    }

    function runThemeToggleSpin(sourceButton) {
        if (!shouldAnimateTheme() || !sourceButton) {
            return;
        }

        window.clearTimeout(themeToggleSpinTimer);
        document.querySelectorAll('[data-theme-toggle].is-spinning').forEach(function (button) {
            button.classList.remove('is-spinning');
        });
        sourceButton.classList.add('is-spinning');
        themeToggleSpinTimer = window.setTimeout(function () {
            sourceButton.classList.remove('is-spinning');
        }, 640);
    }

    function primeThemeOrigin(sourceButton) {
        if (!sourceButton || !sourceButton.getBoundingClientRect) {
            return;
        }

        var rect = sourceButton.getBoundingClientRect();
        document.documentElement.style.setProperty('--theme-origin-x', Math.round(rect.left + (rect.width / 2)) + 'px');
        document.documentElement.style.setProperty('--theme-origin-y', Math.round(rect.top + (rect.height / 2)) + 'px');
    }

    function commitTheme(theme) {
        var dark = theme === 'dark';
        document.documentElement.classList.toggle('theme-dark', dark);
        document.documentElement.setAttribute('data-theme', dark ? 'dark' : 'light');
        syncThemeToggles();
    }

    function applyTheme(theme, options) {
        options = options || {};
        

        if (options.animate && shouldAnimateTheme() && typeof document.startViewTransition === 'function') {
            document.startViewTransition(function () {
                commitTheme(theme);
            });
            return;
        }

        if (options.animate) {
            runThemeAnimation();
        }

        commitTheme(theme);
    }

    function toggleTheme(event) {
        var nextTheme = isDarkTheme() ? 'light' : 'dark';
        var sourceButton = event && event.currentTarget ? event.currentTarget : null;
        primeThemeOrigin(sourceButton);
        runThemeToggleSpin(sourceButton);
        try {
            localStorage.setItem(THEME_KEY, nextTheme);
        } catch (e) {}
        applyTheme(nextTheme, { animate: true });
    }

    document.querySelectorAll('[data-theme-toggle]').forEach(function (button) {
        if (button.dataset.themeBound === '1') {
            updateThemeToggle(button);
            return;
        }

        button.dataset.themeBound = '1';
        updateThemeToggle(button);
        button.addEventListener('click', toggleTheme);
    });

    window.addEventListener('storage', function (event) {
        if (event.key === THEME_KEY) {
            applyTheme(getStoredTheme(), { animate: true });
        }
    });

    applyTheme(getStoredTheme());

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
    var loginPasskeyBtn = document.getElementById('login-passkey-btn');
    var registerPasskeyBtn = document.getElementById('register-passkey-btn');
    var registerPasskeyPhoneBtn = document.getElementById('register-passkey-phone-btn');
    var loginHelpBtn = document.getElementById('loginHelpBtn');

    if (loginHelpBtn) {
        var helpModal = document.getElementById('loginHelpModal');
        
        loginHelpBtn.addEventListener('click', function() {
            if (helpModal) {
                helpModal.style.display = 'flex';
                setTimeout(function() {
                    helpModal.classList.add('is-active');
                    helpModal.setAttribute('aria-hidden', 'false');
                }, 10);
            }
        });

        // Close logic
        var handleClose = function() {
            if (helpModal) {
                helpModal.classList.remove('is-active');
                helpModal.setAttribute('aria-hidden', 'true');
                setTimeout(function() {
                    helpModal.style.display = 'none';
                }, 300);
            }
        };

        var closeOverlay = helpModal ? helpModal.querySelector('.login-help-modal-overlay') : null;
        var closeX = document.getElementById('closeHelpModal');
        var closeBtn = document.getElementById('closeHelpModalBtn');

        if (closeOverlay) closeOverlay.addEventListener('click', handleClose);
        if (closeX) closeX.addEventListener('click', handleClose);
        if (closeBtn) closeBtn.addEventListener('click', handleClose);
    }

    // Customer Search logic
    var loginSearchBtn = document.getElementById('loginSearchBtn');
    if (loginSearchBtn) {
        var searchModal = document.getElementById('loginSearchModal');
        var closeX = document.getElementById('closeSearchModal');
        var input = document.getElementById('customerSearchInput');
        var execBtn = document.getElementById('execCustomerSearchBtn');
        var resultsArea = document.getElementById('customerSearchResults');
        var loadingArea = document.getElementById('customerSearchLoading');
        var emptyArea = document.getElementById('customerSearchEmpty');

        loginSearchBtn.addEventListener('click', function() {
            if (searchModal) {
                searchModal.style.display = 'flex';
                setTimeout(function() {
                    searchModal.classList.add('is-active');
                    searchModal.setAttribute('aria-hidden', 'false');
                    input.focus();
                }, 10);
            }
        });

        function closeSearch() {
            if (searchModal) {
                searchModal.classList.remove('is-active');
                searchModal.setAttribute('aria-hidden', 'true');
                setTimeout(function() { searchModal.style.display = 'none'; }, 300);
            }
        }

        if (closeX) closeX.addEventListener('click', closeSearch);
        var overlay = searchModal ? searchModal.querySelector('.login-help-modal-overlay') : null;
        if (overlay) overlay.addEventListener('click', closeSearch);

        function performSearch() {
            var val = input.value.trim();
            if (val.length < 3) return;

            resultsArea.style.display = 'none';
            emptyArea.style.display = 'none';
            loadingArea.style.display = 'block';

            fetch('{{ route("customer.search") }}?q=' + encodeURIComponent(val), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                loadingArea.style.display = 'none';
                if (data.customers && data.customers.length > 0) {
                    resultsArea.innerHTML = '';
                    data.customers.forEach(function(c) {
                        var div = document.createElement('div');
                        div.className = 'customer-result-item';
                        var status = (c.status || '').toUpperCase() === 'A' ? 'Active' : 'Inactive';
                        var statusClass = status === 'Active' ? 'badge-active' : 'badge-inactive';
                        var expiry = c.udf_expirydate || 'N/A';
                        var agent = c.agent || 'N/A';
                        var biz = c.biznature || 'N/A';
                        var brn = c.brn2 || c.brn || 'N/A';
                        
                        div.innerHTML = 
                            '<span class="customer-result-badge ' + statusClass + '">' + status + '</span>' +
                            '<span class="customer-result-title">' + (c.companyname || 'Unknown Company') + '</span>' +
                            '<span class="customer-result-code">Code: ' + (c.code || '-') + '</span>' +
                            '<div class="customer-result-details">' +
                                '<div><span class="customer-detail-label">Support Expiry</span><span class="customer-detail-value">' + expiry + '</span></div>' +
                                '<div><span class="customer-detail-label">Agent</span><span class="customer-detail-value">' + agent + '</span></div>' +
                                '<div><span class="customer-detail-label">BRN</span><span class="customer-detail-value">' + brn + '</span></div>' +
                                '<div><span class="customer-detail-label">Business Nature</span><span class="customer-detail-value">' + biz + '</span></div>' +
                            '</div>';
                        resultsArea.appendChild(div);
                    });
                    resultsArea.style.display = 'block';
                } else {
                    emptyArea.style.display = 'block';
                }
            })
            .catch(function(err) {
                loadingArea.style.display = 'none';
                alert('Search failed. Please try again later.');
            });
        }

        if (execBtn) execBtn.addEventListener('click', performSearch);
        if (input) {
            input.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') performSearch();
            });
        }
    }

    function resetPasskeyScreenState() {
        [loginPasskeyBtn, registerPasskeyBtn, registerPasskeyPhoneBtn].forEach(function (button) {
            if (button) {
                button.disabled = false;
            }
        });
    }

    if (!window.PublicKeyCredential || !passkeyUtils) {
        if (loginPasskeyBtn) { loginPasskeyBtn.disabled = true; loginPasskeyBtn.title = 'Passkeys not supported in this browser'; }
        if (registerPasskeyBtn) { registerPasskeyBtn.disabled = true; registerPasskeyBtn.title = 'Passkeys not supported in this browser'; }
        return;
    }

    window.addEventListener('pageshow', resetPasskeyScreenState);
    window.addEventListener('pagehide', resetPasskeyScreenState);

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
                    ? 'How should we name this phone passkey? Example: "My iPhone" or "My Android".'
                    : 'How should we name this passkey? Example: "My laptop" or "Office desktop".',
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
