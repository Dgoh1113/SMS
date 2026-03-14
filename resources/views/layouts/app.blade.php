<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SQL Sales Management System')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script>
        // Apply sidebar state ASAP (prevents flicker on page navigation)
        (function () {
            try {
                var collapsed = localStorage.getItem('dashboard-sidebar-collapsed') === '1';
                if (collapsed) {
                    document.documentElement.classList.add('dashboard-root-sidebar-collapsed-preload');
                }
            } catch (e) {}
        })();
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    @stack('styles')
</head>
<body>
<div class="dashboard-root" id="dashboardRoot">
    @if (in_array(session('user_role'), ['admin', 'manager'], true))
        @include('partials.sidebar-admin')
    @elseif (session('user_role') === 'dealer')
        @include('partials.sidebar-dealer')
    @endif
    <div class="dashboard-sidebar-backdrop" id="sidebarBackdrop"></div>

    <main class="dashboard-main">
        <header class="dashboard-topbar" data-scroll-anchor>
            <button type="button" class="dashboard-topbar-toggle" id="topbarSidebarToggle" aria-label="Toggle navigation">
                <span class="dashboard-topbar-toggle-inner"></span>
            </button>
            <div class="dashboard-topbar-actions">
                <a href="#" class="dashboard-icon-btn top-right-btn" type="button" title="Bookmark"><img src="{{ asset('Guide.ico') }}" alt="Bookmark" class="dashboard-icon-img"></a>
                <a href="#" class="dashboard-icon-btn top-right-btn" type="button" title="Notifications"><img src="{{ asset('Notification.ico') }}" alt="Notifications" class="dashboard-icon-img"></a>
                <div class="dashboard-profile-dropdown">
                    <button type="button" class="dashboard-profile-btn" id="profileDropdownTrigger" aria-expanded="false" aria-haspopup="true" title="{{ session('user_email', '') }}">
                        <div class="dashboard-user-avatar">{{ strtoupper(substr(session('user_email', 'U'), 0, 1)) }}</div>
                    </button>
                    <div class="dashboard-profile-menu" id="profileDropdownMenu" hidden>
                        <div class="dashboard-profile-card">
                            <div class="dashboard-profile-avatar-lg">{{ strtoupper(substr(session('user_email', 'U'), 0, 1)) }}</div>
                            <div class="dashboard-profile-email">{{ session('user_email', '') }}</div>
                            @if(session('user_alias'))
                                <div class="dashboard-profile-alias">{{ strtoupper(session('user_alias')) }}</div>
                            @endif
                            <form action="{{ route('logout') }}" method="POST" class="dashboard-profile-signout-form">
                                @csrf
                                <button type="submit" class="dashboard-profile-signout-btn">Sign out</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        @if (session('error'))
            <div class="login-message login-error" style="margin:16px;" data-flash-message="1">{{ session('error') }}</div>
        @endif
        @if (session('success'))
            <div class="login-message login-success" style="margin:16px;" data-flash-message="1">{{ session('success') }}</div>
        @endif

        @if (session('show_passkey_prompt'))
        <div class="passkey-prompt-overlay" id="passkeyPromptOverlay" role="dialog" aria-modal="true" aria-labelledby="passkeyPromptTitle">
            <div class="passkey-prompt-window">
                <h2 class="passkey-prompt-title" id="passkeyPromptTitle">Register a passkey?</h2>
                <p class="passkey-prompt-text">You can register a passkey to sign in quickly next time without entering your password.</p>
                <div class="passkey-prompt-actions">
                    <a href="{{ route('passkey.register.form') }}" class="login-primary-btn passkey-prompt-btn">Register passkey</a>
                    <button type="button" class="passkey-prompt-skip" id="passkeyPromptSkip">Not now</button>
                </div>
            </div>
        </div>
        @endif

        <div class="dashboard-main-body">
            @yield('content')
        </div>

        <footer class="dashboard-bottombar"></footer>
    </main>
</div>
@push('scripts')
<script>
(function() {
    // Auto-hide flash messages (success/error) after 3s
    document.querySelectorAll('[data-flash-message="1"]').forEach(function(el) {
        setTimeout(function() {
            if (!el) return;
            el.style.transition = 'opacity 200ms ease';
            el.style.opacity = '0';
            setTimeout(function() { if (el && el.parentNode) el.parentNode.removeChild(el); }, 250);
        }, 3000);
    });

    var trigger = document.getElementById('profileDropdownTrigger');
    var menu = document.getElementById('profileDropdownMenu');
    if (trigger && menu) {
        function toggle() {
            var open = !menu.hidden;
            menu.hidden = open;
            trigger.setAttribute('aria-expanded', !open);
        }
        trigger.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            toggle();
        });
        document.addEventListener('click', function() {
            if (!menu.hidden) { menu.hidden = true; trigger.setAttribute('aria-expanded', 'false'); }
        });
        menu.addEventListener('click', function(e) { e.stopPropagation(); });
    }
    var overlay = document.getElementById('passkeyPromptOverlay');
    var skipBtn = document.getElementById('passkeyPromptSkip');
    if (overlay && skipBtn) {
        skipBtn.addEventListener('click', function() { overlay.remove(); });
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) overlay.remove();
        });
    }

    // Sidebar: apply preload state immediately (avoids flicker) on desktop only
    if (!window.matchMedia('(max-width: 768px)').matches &&
        document.documentElement.classList.contains('dashboard-root-sidebar-collapsed-preload')) {
        var r = document.getElementById('dashboardRoot');
        if (r) r.classList.add('dashboard-root-sidebar-collapsed');
        document.documentElement.classList.remove('dashboard-root-sidebar-collapsed-preload');
    }

    // Sidebar hamburger toggle: state persists (localStorage) on desktop,
    // acts as overlay drawer on mobile.
    (function() {
        var root = document.querySelector('.dashboard-root');
        var sidebarToggle = document.getElementById('sidebarToggle');           // inside sidebar
        var topbarToggle = document.getElementById('topbarSidebarToggle');      // in top bar (mobile)
        var backdrop = document.getElementById('sidebarBackdrop');
        var storageKey = 'dashboard-sidebar-collapsed';
        if (!root) return;

        function isMobile() {
            return window.matchMedia('(max-width: 768px)').matches;
        }

        // Desktop: persistent collapsed state (do NOT apply on mobile so overlay shows full sidebar)
        var collapsed = false;
        if (!isMobile()) {
            collapsed = localStorage.getItem(storageKey) === '1';
            if (collapsed) root.classList.add('dashboard-root-sidebar-collapsed');
        }

        function handleToggleClick() {
            if (isMobile()) {
                // Ensure full sidebar when opening overlay
                root.classList.remove('dashboard-root-sidebar-collapsed');
                root.classList.toggle('dashboard-root-sidebar-open');
            } else {
                collapsed = root.classList.toggle('dashboard-root-sidebar-collapsed');
                localStorage.setItem(storageKey, collapsed ? '1' : '0');
            }
        }

        if (sidebarToggle) sidebarToggle.addEventListener('click', handleToggleClick);
        if (topbarToggle) topbarToggle.addEventListener('click', handleToggleClick);

        if (backdrop) {
            backdrop.addEventListener('click', function() {
                root.classList.remove('dashboard-root-sidebar-open');
            });
        }
    })();

    // Mobile header: hide on scroll down, show on scroll up (disabled on desktop)
    (function() {
        var element = document.querySelector('.dashboard-topbar');
        if (!element) return;

        var className = 'dashboard-topbar-hidden';
        var offsetSelector = '[data-scroll-anchor]';
        var mediaQueryMatch = '(min-width: 48rem)';

        var offsetEl = element.querySelector(offsetSelector) || element;
        var mediaQuery = window.matchMedia(mediaQueryMatch);
        var isDisabled = mediaQuery.matches;
        var lastY = null;

        function getOffset() {
            return offsetEl.getBoundingClientRect().height || 0;
        }

        function onMediaChange(ev) {
            isDisabled = ev.matches;
            if (ev.matches) element.classList.remove(className);
        }

        function onScroll() {
            if (isDisabled) return;
            var scrollY = window.scrollY;
            var offset = getOffset();
            var isPastOffset = scrollY > offset;
            var isAtBottom = scrollY + window.innerHeight >= document.documentElement.scrollHeight;
            var direction = '';
            if (!isAtBottom && lastY !== null) {
                direction = scrollY > lastY ? 'down' : 'up';
            }
            element.classList.toggle(className, isPastOffset && direction === 'down');
            lastY = scrollY;
        }

        mediaQuery.addEventListener('change', onMediaChange);
        window.addEventListener('scroll', onScroll, { passive: true });
    })();

    // Strict client-side idle timeout: redirect to login after SESSION_LIFETIME minutes
    // This handles the "no requests made" case where server can't enforce timeout.
    var isLoggedIn = {{ session()->has('user_role') ? 'true' : 'false' }};
    if (isLoggedIn) {
        var lifetimeMinutes = {{ (int) config('session.lifetime', 120) }};
        var maxIdleMs = Math.max(1, lifetimeMinutes) * 60 * 1000;
        var lastActivity = Date.now();
        function bump() { lastActivity = Date.now(); }
        ['mousemove','mousedown','keydown','scroll','touchstart','click'].forEach(function(evt) {
            document.addEventListener(evt, bump, { passive: true });
        });
        setInterval(function() {
            if ((Date.now() - lastActivity) > maxIdleMs) {
                // Best-effort logout, then go to login.
                var token = (document.querySelector('meta[name="csrf-token"]') || {}).content;
                if (token) {
                    fetch('{{ route('logout') }}', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest' },
                        credentials: 'same-origin'
                    }).finally(function() {
                        window.location.href = '{{ route('login') }}';
                    });
                } else {
                    window.location.href = '{{ route('login') }}';
                }
            }
        }, 5000);
    }
})();
</script>
@endpush
@stack('scripts')
</body>
</html>
