<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SQL Sales Management System')</title>
    <link rel="icon" type="image/png" href="{{ asset('sql-logo.png') }}?v=20260318">
    <link rel="shortcut icon" href="{{ asset('sql-logo.png') }}?v=20260318">
    <link rel="apple-touch-icon" href="{{ asset('sql-logo.png') }}?v=20260318">
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
    
    <style>
        /* Unread notification visual styling */
        .dashboard-notification-item--new {
            background-color: #f4f8ff; /* Soft blue background for unread */
            position: relative;
        }
        .dashboard-notification-item--new::after {
            content: '';
            position: absolute;
            top: 15px;
            right: 15px;
            width: 8px;
            height: 8px;
            background-color: #0d6efd; /* Blue dot indicator */
            border-radius: 50%;
        }
        
        /* Header layout for the Mark as Read button */
        .dashboard-notifications-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 16px;
            border-bottom: 1px solid #e5e7eb;
            font-weight: 600;
        }
        .dashboard-notifications-mark-read {
            font-size: 0.8rem;
            color: #0d6efd;
            cursor: pointer;
            background: none;
            border: none;
            padding: 0;
            font-weight: 400;
        }
        .dashboard-notifications-mark-read:hover {
            text-decoration: underline;
        }
    </style>
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
                @if (session('user_role') === 'dealer')
                    <div class="dashboard-notifications">
                        <button type="button" class="dashboard-icon-btn top-right-btn" id="dealerNotificationsTrigger" aria-expanded="false" aria-haspopup="true" title="Notifications">
                            <img src="{{ asset('Notification.ico') }}" alt="Notifications" class="dashboard-icon-img">
                            <span class="dashboard-notifications-dot" id="dealerNotificationsDot" hidden></span>
                        </button>
                        <div class="dashboard-notifications-menu" id="dealerNotificationsMenu" hidden>
                            <div class="dashboard-notifications-header">
                                <span>Notifications</span>
                                <button type="button" class="dashboard-notifications-mark-read" id="dealerMarkAllReadBtn">Mark all as read</button>
                            </div>
                            <div class="dashboard-notifications-list" id="dealerNotificationsList">
                                <div class="dashboard-notifications-empty">Loading…</div>
                            </div>
                        </div>
                    </div>
                @else
                    <a href="#" class="dashboard-icon-btn top-right-btn" type="button" title="Notifications"><img src="{{ asset('Notification.ico') }}" alt="Notifications" class="dashboard-icon-img"></a>
                @endif
                @php
                    $avatarInitial = strtoupper(substr(session('user_email', 'U'), 0, 1));
                    $avatarLetter = (ctype_alpha($avatarInitial) ? $avatarInitial : 'U');
                @endphp
                <div class="dashboard-profile-dropdown">
                    <button type="button" class="dashboard-profile-btn" id="profileDropdownTrigger" aria-expanded="false" aria-haspopup="true" title="{{ session('user_email', '') }}">
                        <div class="dashboard-user-avatar dashboard-avatar-{{ $avatarLetter }}">{{ $avatarInitial }}</div>
                    </button>
                    <div class="dashboard-profile-menu" id="profileDropdownMenu" hidden>
                        <div class="dashboard-profile-card">
                            <div class="dashboard-profile-avatar-lg dashboard-avatar-{{ $avatarLetter }}">{{ $avatarInitial }}</div>
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

        {{-- FIXED: Footer properly moved here inside the main tag --}}
        <footer class="dashboard-bottombar" style="display: flex; justify-content: space-between; align-items: center; font-size: 13px; color: #6b7280;">
            <div class="dashboard-bottombar-left">
                <span class="dashboard-footer-text-main">&copy; {{ date('Y') }} E Stream Software. All rights reserved.</span>
            </div>
            <div class="dashboard-bottombar-right">
                <span>Designed & Developed by <strong>Damien, Weijian & WenJun with 💜</strong></span>
            </div>
        </footer>
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

    // Dealer notifications dropdown (bell) - LocalStorage Version
    var nTrigger = document.getElementById('dealerNotificationsTrigger');
    var nMenu = document.getElementById('dealerNotificationsMenu');
    var nList = document.getElementById('dealerNotificationsList');
    var nDot = document.getElementById('dealerNotificationsDot');
    var markAllBtn = document.getElementById('dealerMarkAllReadBtn');
    var currentNotifIds = []; // Stores IDs of currently loaded notifications
    var nLoaded = false;
    
    // Key used to save data in the browser
    var SEEN_KEY = 'dealer.notifications.seen.v1';
    
    function getSeen() {
        try { return JSON.parse(localStorage.getItem(SEEN_KEY) || '{}') || {}; } catch (e) { return {}; }
    }
    function setSeen(obj) {
        try { localStorage.setItem(SEEN_KEY, JSON.stringify(obj || {})); } catch (e) {}
    }
    
    function renderNotifications(items) {
        if (!nList) return;
        currentNotifIds = []; // Clear the array on new render

        if (!items || !items.length) {
            nList.innerHTML = '<div class="dashboard-notifications-empty">No notifications</div>';
            if (nDot) nDot.hidden = true;
            return;
        }
        
        var seen = getSeen();
        var hasUnseen = false;
        nList.innerHTML = '';
        
        // Limit to exactly 20 items
        var displayItems = items.slice(0, 20);
        
        displayItems.forEach(function(it) {
            // It is critical that your server provides a unique 'id' for each notification
            var id = String(it.id || it.lead_id || ''); 
            var leadId = String(it.lead_id || '');
            var title = (it.title || ('Inquiry #SQL-' + leadId)).toString();
            var desc = (it.description || '').toString();
            var time = (it.time || '').toString();
            
            // Check if this ID exists in our local browser storage
            var isSeen = !!seen[id];
            
            if (!isSeen) {
                hasUnseen = true;
                currentNotifIds.push(id); // Save ID so 'Mark all' knows what to update
            }

            var a = document.createElement('a');
            a.href = '/dealer/inquiries?lead=' + encodeURIComponent(leadId) + '&fromNotif=' + encodeURIComponent(id);
            a.className = 'dashboard-notification-item' + (isSeen ? '' : ' dashboard-notification-item--new');
            a.setAttribute('data-notif-id', id);
            a.innerHTML =
                '<div class="dashboard-notification-title">' + title.replace(/</g,'&lt;').replace(/>/g,'&gt;') + '</div>' +
                (desc ? '<div class="dashboard-notification-desc">' + desc.replace(/</g,'&lt;').replace(/>/g,'&gt;') + '</div>' : '') +
                (time ? '<div class="dashboard-notification-time">' + time.replace(/</g,'&lt;').replace(/>/g,'&gt;') + '</div>' : '');
            
            // Mark individual item as read when clicked
            a.addEventListener('click', function() {
                var s = getSeen();
                s[id] = Date.now();
                setSeen(s);
                // The browser will follow the href and navigate away
            });
            nList.appendChild(a);
        });
        
        if (nDot) nDot.hidden = !hasUnseen;
    }

    function loadNotifications() {
        if (!nList) return;
        fetch('/dealer/notifications', { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function(r) { return r.json(); })
            .then(function(data) { renderNotifications((data && data.items) ? data.items : []); })
            .catch(function() { 
                // Only show error text if the menu is actively open and it's the first manual load
                if (!nMenu.hidden && !nLoaded) {
                    nList.innerHTML = '<div class="dashboard-notifications-empty">Failed to load</div>'; 
                }
            });
    }

    // Mark all as read functionality
    if (markAllBtn) {
        markAllBtn.addEventListener('click', function(e) {
            e.stopPropagation(); // Prevent dropdown from closing
            var s = getSeen();
            
            // Add all unread IDs to the local storage 'seen' object
            currentNotifIds.forEach(function(id) {
                s[id] = Date.now();
            });
            setSeen(s);
            currentNotifIds = []; // Clear array
            
            // Update UI instantly to remove the blue background and dot
            document.querySelectorAll('.dashboard-notification-item--new').forEach(function(el) {
                el.classList.remove('dashboard-notification-item--new');
            });
            if (nDot) nDot.hidden = true;
        });
    }

    if (nTrigger && nMenu) {
        nTrigger.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var open = !nMenu.hidden;
            nMenu.hidden = open;
            nTrigger.setAttribute('aria-expanded', String(!open));
            if (!open && !nLoaded) {
                nLoaded = true;
                loadNotifications();
            } else if (!open) {
                loadNotifications();
            }
        });
        document.addEventListener('click', function() {
            if (!nMenu.hidden) { nMenu.hidden = true; nTrigger.setAttribute('aria-expanded', 'false'); }
        });
        nMenu.addEventListener('click', function(e) { e.stopPropagation(); });
        
        // Initial load to check for red dot immediately on page load
        loadNotifications();
        nLoaded = true;

        // REAL-TIME AUTO REFRESH: Fetch notifications every 5 seconds
        setInterval(function() {
            // We fetch silently in the background. We check if the dropdown is hidden 
            // so we don't redraw the list while the user is trying to read/click it!
            if (nMenu && nMenu.hidden) {
                loadNotifications();
            }
        }, 5000);
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
