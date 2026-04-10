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
    <script>
        (function () {
            try {
                if (localStorage.getItem('sqlsms-theme') === 'dark') {
                    document.documentElement.classList.add('theme-dark');
                }
            } catch (e) {}
        })();
    </script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v=20260410-29">
    <script src="{{ asset('js/passkey-registration.js') }}"></script>
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
  

    <main class="dashboard-main">
        <header class="dashboard-topbar" data-scroll-anchor>
            <button type="button" class="dashboard-topbar-toggle" id="topbarSidebarToggle" aria-label="Toggle navigation">
                <span class="dashboard-topbar-toggle-inner"></span>
            </button>
            <div class="dashboard-topbar-actions">
                <a href="#" class="dashboard-icon-btn top-right-btn dashboard-bookmark-btn" type="button" title="Bookmark" aria-label="Bookmark">
                    <img src="{{ asset('Guide.ico') }}" alt="Bookmark" class="dashboard-bookmark-img-light" aria-hidden="true">
                    <i class="bi bi-bookmark-star-fill dashboard-bookmark-icon-dark" aria-hidden="true"></i>
                </a>
                <button type="button" class="dashboard-icon-btn top-right-btn dashboard-theme-toggle" data-theme-toggle aria-label="Enable dark mode" title="Enable dark mode">
                    <i class="bi bi-moon-fill" data-theme-icon aria-hidden="true"></i>
                </button>
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
                                <div class="dashboard-notifications-empty">Loading...</div>
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
                            <div class="dashboard-profile-actions">
                                <button type="button" class="dashboard-profile-passkey-link" id="profileRegisterPasskeyBtn">
                                    <span>Register passkey</span>
                                </button>
                                <form action="{{ route('logout') }}" method="POST" class="dashboard-profile-signout-form">
                                    @csrf
                                    <button type="submit" class="dashboard-profile-signout-btn">
                                        <span>Sign out</span>
                                    </button>
                                </form>
                            </div>
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

        <div class="dashboard-passkey-quick-modal" id="profilePasskeyQuickModal" hidden>
            <div class="dashboard-passkey-quick-card" role="dialog" aria-modal="true" aria-labelledby="profilePasskeyQuickTitle">
                <button type="button" class="dashboard-passkey-quick-close" id="profilePasskeyQuickClose" aria-label="Close">
                    <i class="bi bi-x-lg" aria-hidden="true"></i>
                </button>
                <h3 class="dashboard-passkey-quick-title" id="profilePasskeyQuickTitle">Register passkey</h3>
                <p class="dashboard-passkey-quick-subtitle">Choose where to save your new passkey.</p>
                <div class="dashboard-passkey-quick-actions">
                    <button type="button" class="login-primary-btn" id="profilePasskeyPhoneBtn" style="margin-top: 0;">
                        <i class="bi bi-phone" aria-hidden="true"></i>
                        <span>Use Phone / Scan QR</span>
                    </button>
                    <button type="button" class="login-passkey-btn" id="profilePasskeyDeviceBtn">
                        <i class="bi bi-laptop" aria-hidden="true"></i>
                        <span>Register On This Device</span>
                    </button>
                </div>
                <div class="dashboard-passkey-quick-status" id="profilePasskeyQuickStatus" hidden></div>
            </div>
        </div>

        <div class="dashboard-main-body">
            @yield('content')
        </div>

        {{-- FIXED: Footer properly moved here inside the main tag --}}
        <footer class="dashboard-bottombar" style="display: flex; justify-content: space-between; align-items: center; font-size: 13px; color: #6b7280;">
            <div class="dashboard-bottombar-left">
                <span class="dashboard-footer-text-main">&copy; {{ date('Y') }} E Stream Software. All rights reserved.</span>
            </div>
            <div class="dashboard-bottombar-right">
                <span>Designed &amp; Developed by <strong>Damien, WeiJian &amp; WenJun with &#x1F49C;</strong></span>
            </div>
        </footer>
    </main>
</div>

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
})();
</script>

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
    var passkeyTrigger = document.getElementById('profileRegisterPasskeyBtn');
    var passkeyModal = document.getElementById('profilePasskeyQuickModal');
    var passkeyCloseBtn = document.getElementById('profilePasskeyQuickClose');
    var passkeyDeviceBtn = document.getElementById('profilePasskeyDeviceBtn');
    var passkeyPhoneBtn = document.getElementById('profilePasskeyPhoneBtn');
    var passkeyStatus = document.getElementById('profilePasskeyQuickStatus');
    var passkeyUtils = window.SQLSMSPasskey;

    function hideProfileMenu() {
        if (!menu || !trigger) return;
        menu.hidden = true;
        trigger.setAttribute('aria-expanded', 'false');
    }

    function showFlashMessage(type, message) {
        var mainBody = document.querySelector('.dashboard-main-body');
        var host = document.querySelector('.dashboard-main');
        if (!mainBody || !host) {
            alert(message);
            return;
        }

        var flash = document.createElement('div');
        flash.className = 'login-message ' + (type === 'error' ? 'login-error' : 'login-success');
        flash.style.margin = '16px';
        flash.textContent = message;
        host.insertBefore(flash, mainBody);

        setTimeout(function() {
            flash.style.transition = 'opacity 200ms ease';
            flash.style.opacity = '0';
            setTimeout(function() {
                if (flash.parentNode) {
                    flash.parentNode.removeChild(flash);
                }
            }, 250);
        }, 3000);
    }

    function enforceFourDigitYearOnDateInput(field) {
        if (!field || field.type !== 'date') {
            return;
        }

        if (!field.hasAttribute('min')) {
            field.setAttribute('min', '1000-01-01');
        }

        if (!field.hasAttribute('max')) {
            field.setAttribute('max', '9999-12-31');
        }

        if (!field.value) {
            return;
        }

        var segments = String(field.value).split('-');
        if (segments.length !== 3) {
            return;
        }

        var year = String(segments[0] || '');
        if (year.length <= 4) {
            return;
        }

        field.value = year.slice(0, 4) + '-' + String(segments[1] || '') + '-' + String(segments[2] || '');
    }

    function initSharedDateInputGuards(root) {
        var scope = root && root.querySelectorAll ? root : document;
        scope.querySelectorAll('input[type="date"]').forEach(function(field) {
            enforceFourDigitYearOnDateInput(field);
        });
    }

    initSharedDateInputGuards(document);

    ['focusin', 'input', 'change', 'blur'].forEach(function(eventName) {
        document.addEventListener(eventName, function(e) {
            var target = e.target;
            if (target && target.matches && target.matches('input[type="date"]')) {
                enforceFourDigitYearOnDateInput(target);
            }
        }, true);
    });

    function setPasskeyModalOpen(open) {
        if (!passkeyModal) return;
        passkeyModal.hidden = !open;
        document.body.classList.toggle('dashboard-passkey-modal-open', !!open);
        if (!open) {
            setPasskeyStatus('', '');
            setPasskeyButtonsDisabled(false);
        }
    }

    function setPasskeyButtonsDisabled(disabled) {
        [passkeyDeviceBtn, passkeyPhoneBtn].forEach(function(button) {
            if (button) button.disabled = !!disabled;
        });
    }

    function setPasskeyStatus(type, message) {
        if (!passkeyStatus) return;
        passkeyStatus.hidden = !message;
        passkeyStatus.className = 'dashboard-passkey-quick-status';
        if (!message) {
            passkeyStatus.textContent = '';
            return;
        }
        passkeyStatus.classList.add('login-message', type === 'error' ? 'login-error' : 'login-success');
        passkeyStatus.textContent = message;
    }

    function getCsrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function passkeyErrorMessage(err) {
        if (err && err.cancelled) {
            return '';
        }
        if (err && err.name === 'NotAllowedError') {
            return 'Passkey registration was cancelled.';
        }
        if (err && err.message) {
            return err.message;
        }
        return 'Passkey registration failed.';
    }

    function startProfilePasskeyRegistration(preference) {
        if (!window.PublicKeyCredential || !passkeyUtils) {
            setPasskeyStatus('error', 'Passkeys are not supported in this browser.');
            return;
        }

        setPasskeyStatus('', '');
        setPasskeyButtonsDisabled(true);

        var nickname = preference === 'phone' ? 'Phone passkey' : 'This device';

        passkeyUtils.register({
            preference: preference,
            optionsUrl: '{{ route("passkey.register.options") }}',
            verifyUrl: '{{ route("passkey.register.verify") }}',
            csrfToken: getCsrfToken(),
            getNickname: function () {
                return nickname;
            }
        })
        .then(function(result) {
            setPasskeyModalOpen(false);

            if (result && result.redirect) {
                window.location.href = result.redirect;
                return;
            }

            showFlashMessage('success', 'Passkey registered successfully.');
        })
        .catch(function(err) {
            setPasskeyButtonsDisabled(false);
            var message = passkeyErrorMessage(err);
            if (message) {
                setPasskeyStatus('error', message);
            }
        });
    }

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
            hideProfileMenu();
        });
        menu.addEventListener('click', function(e) { e.stopPropagation(); });
    }

    if (passkeyTrigger) {
        passkeyTrigger.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            hideProfileMenu();
            setPasskeyModalOpen(true);
        });
    }

    if (passkeyCloseBtn) {
        passkeyCloseBtn.addEventListener('click', function() {
            setPasskeyModalOpen(false);
        });
    }

    if (passkeyModal) {
        passkeyModal.addEventListener('click', function(e) {
            if (e.target === passkeyModal) {
                setPasskeyModalOpen(false);
            }
        });
    }

    if (passkeyDeviceBtn) {
        passkeyDeviceBtn.addEventListener('click', function() {
            startProfilePasskeyRegistration('device');
        });
    }

    if (passkeyPhoneBtn) {
        passkeyPhoneBtn.addEventListener('click', function() {
            startProfilePasskeyRegistration('phone');
        });
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && passkeyModal && !passkeyModal.hidden) {
            setPasskeyModalOpen(false);
        }
    });

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
            var targetUrl = (it.target_url || ('/dealer/inquiries?lead=' + encodeURIComponent(leadId) + '&fromNotif=' + encodeURIComponent(id))).toString();
            
            // Check if this ID exists in our local browser storage
            var isSeen = !!seen[id];
            
            if (!isSeen) {
                hasUnseen = true;
                currentNotifIds.push(id); // Save ID so 'Mark all' knows what to update
            }

            var a = document.createElement('a');
            a.href = targetUrl;
            a.className = 'dashboard-notification-item' + (isSeen ? '' : ' dashboard-notification-item--new');
            a.setAttribute('data-notif-id', id);
            a.innerHTML =
                '<div class="dashboard-notification-title">' + title.replace(/</g,'&lt;').replace(/>/g,'&gt;') + '</div>' +
                (desc ? '<div class="dashboard-notification-desc">' + desc.replace(/</g,'&lt;').replace(/>/g,'&gt;') + '</div>' : '') +
                (time ? '<div class="dashboard-notification-time">' + time.replace(/</g,'&lt;').replace(/>/g,'&gt;') + '</div>' : '');
            
            // Mark individual item as read when clicked
            a.addEventListener('click', function(e) {
                e.preventDefault();
                var s = getSeen();
                s[id] = Date.now();
                setSeen(s);
                window.location.href = targetUrl;
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

    // Shared "Columns" dropdown search for admin/dealer tables
    (function() {
        function normalizeText(value) {
            return String(value || '').replace(/\s+/g, ' ').trim().toLowerCase();
        }

        function ensureColumnsMenuReady(menu) {
            if (!menu || menu.dataset.columnsSearchReady === '1') return;
            menu.dataset.columnsSearchReady = '1';

            var title = menu.querySelector('.inquiries-columns-menu-title');
            if (!title) return;

            var searchWrap = document.createElement('div');
            searchWrap.className = 'inquiries-columns-search';

            var searchIcon = document.createElement('i');
            searchIcon.className = 'bi bi-search inquiries-columns-search-icon';
            searchIcon.setAttribute('aria-hidden', 'true');

            var searchInput = document.createElement('input');
            searchInput.type = 'search';
            searchInput.className = 'inquiries-columns-search-input';
            searchInput.placeholder = 'Type to search';
            searchInput.autocomplete = 'off';
            searchInput.setAttribute('aria-label', 'Search columns');

            searchWrap.appendChild(searchIcon);
            searchWrap.appendChild(searchInput);

            var emptyState = document.createElement('div');
            emptyState.className = 'inquiries-columns-empty';
            emptyState.textContent = 'No columns found';
            title.insertAdjacentElement('afterend', searchWrap);
            searchWrap.insertAdjacentElement('afterend', emptyState);

            menu._columnsSearchWrap = searchWrap;
            menu._columnsSearchInput = searchInput;
            menu._columnsEmptyState = emptyState;
            menu._columnsCheckItems = Array.prototype.slice.call(menu.querySelectorAll('.inquiries-columns-check'));

            searchInput.addEventListener('input', function() {
                applyColumnsTypeahead(menu, searchInput.value);
            });
        }

        function isMenuOpen(menu) {
            if (!menu) return false;
            if (menu.hidden) return false;
            return window.getComputedStyle(menu).display !== 'none';
        }

        function matchesColumnsTypeahead(text, keyword) {
            if (!keyword) return true;
            return text.indexOf(keyword) !== -1;
        }

        function applyColumnsTypeahead(menu, keyword) {
            ensureColumnsMenuReady(menu);
            if (!menu) return;

            var shown = 0;
            var normalizedKeyword = normalizeText(keyword);
            var checkItems = menu._columnsCheckItems || [];
            var emptyState = menu._columnsEmptyState;
            var firstMatch = null;

            menu.dataset.columnsTypeahead = normalizedKeyword;

            checkItems.forEach(function(item) {
                var matched = matchesColumnsTypeahead(normalizeText(item.textContent), normalizedKeyword);
                item.hidden = !matched;
                if (matched) {
                    shown += 1;
                    if (!firstMatch) firstMatch = item;
                }
            });

            if (emptyState) {
                emptyState.classList.toggle('is-visible', shown === 0);
            }

            if (normalizedKeyword && firstMatch) {
                firstMatch.scrollIntoView({ block: 'nearest' });
            }
        }

        function resetColumnsSearch(menu) {
            if (!menu) return;
            ensureColumnsMenuReady(menu);
            if (menu._columnsSearchInput) {
                menu._columnsSearchInput.value = '';
            }
            applyColumnsTypeahead(menu, '');
        }

        function closeColumnsMenu(menu) {
            if (!menu) return;
            menu.hidden = true;

            var dropdown = menu.closest('.inquiries-columns-dropdown');
            var button = dropdown ? dropdown.querySelector('button[aria-haspopup="true"]') : null;
            if (button) {
                button.setAttribute('aria-expanded', 'false');
                if (typeof button.focus === 'function') {
                    button.focus();
                }
            }
        }

        function getOpenColumnsMenus() {
            return Array.prototype.filter.call(
                document.querySelectorAll('.inquiries-columns-menu'),
                function(menu) { return isMenuOpen(menu); }
            );
        }

        function getActiveColumnsMenu() {
            var openMenus = getOpenColumnsMenus();
            return openMenus.length ? openMenus[openMenus.length - 1] : null;
        }

        function isTypingTarget(target) {
            if (!target) return false;
            var tagName = (target.tagName || '').toUpperCase();
            if (tagName === 'TEXTAREA' || tagName === 'SELECT' || !!target.isContentEditable) return true;
            if (tagName !== 'INPUT') return false;

            var inputType = String(target.type || '').toLowerCase();
            return ['text', 'search', 'email', 'number', 'tel', 'url'].indexOf(inputType) !== -1;
        }

        Array.prototype.forEach.call(document.querySelectorAll('.inquiries-columns-menu'), function(menu) {
            ensureColumnsMenuReady(menu);

            menu.addEventListener('change', function(e) {
                if (!e.target || e.target.tagName !== 'INPUT' || e.target.type !== 'checkbox') return;
            });

            menu.addEventListener('click', function(e) {
                var resetButton = e.target.closest('.inquiries-columns-reset');
                if (!resetButton) return;

                window.setTimeout(function() {
                    resetColumnsSearch(menu);
                    closeColumnsMenu(menu);
                }, 0);
            });
        });

        Array.prototype.forEach.call(document.querySelectorAll('.inquiries-columns-dropdown > button'), function(button) {
            button.addEventListener('click', function() {
                var menu = button.parentElement ? button.parentElement.querySelector('.inquiries-columns-menu') : null;
                if (!menu) return;

                window.setTimeout(function() {
                    resetColumnsSearch(menu);
                    if (menu._columnsSearchInput && typeof menu._columnsSearchInput.focus === 'function' && isMenuOpen(menu)) {
                        menu._columnsSearchInput.focus();
                    }
                }, 0);
            });
        });

        document.addEventListener('keydown', function(e) {
            var menu = getActiveColumnsMenu();
            if (!menu) return;

            if (e.key === 'Escape') {
                resetColumnsSearch(menu);
                return;
            }

            if (isTypingTarget(e.target)) return;

            if (e.key === 'Backspace') {
                e.preventDefault();
                ensureColumnsMenuReady(menu);
                if (menu._columnsSearchInput) {
                    menu._columnsSearchInput.focus();
                    menu._columnsSearchInput.value = (menu._columnsSearchInput.value || '').slice(0, -1);
                    applyColumnsTypeahead(menu, menu._columnsSearchInput.value);
                    return;
                }
                applyColumnsTypeahead(menu, (menu.dataset.columnsTypeahead || '').slice(0, -1));
                return;
            }

            if (e.ctrlKey || e.metaKey || e.altKey || e.key.length !== 1) return;
            if (!/[a-z0-9 _-]/i.test(e.key)) return;

            e.preventDefault();
            ensureColumnsMenuReady(menu);
            if (menu._columnsSearchInput) {
                menu._columnsSearchInput.focus();
                menu._columnsSearchInput.value = (menu._columnsSearchInput.value || '') + e.key;
                applyColumnsTypeahead(menu, menu._columnsSearchInput.value);
                return;
            }
            applyColumnsTypeahead(menu, (menu.dataset.columnsTypeahead || '') + e.key);
        });

        document.addEventListener('click', function(e) {
            if (e.target.closest('.inquiries-columns-dropdown')) return;
            window.setTimeout(function() {
                Array.prototype.forEach.call(document.querySelectorAll('.inquiries-columns-menu'), function(menu) {
                    if (!isMenuOpen(menu)) {
                        resetColumnsSearch(menu);
                    }
                });
            }, 0);
        });
    })();

    // Shared idle handling:
    // enforce client-side logout after the configured session lifetime
    var isLoggedIn = {{ session()->has('user_role') ? 'true' : 'false' }};
    if (isLoggedIn) {
        var lifetimeMinutes = {{ (int) config('session.lifetime', 120) }};
        var maxIdleMs = Math.max(1, lifetimeMinutes) * 60 * 1000;
        var storagePrefix = 'dashboard-idle.{{ session()->getId() }}.';
        var activityKey = storagePrefix + 'last-activity';

        function readStoredTime(key) {
            try {
                var raw = sessionStorage.getItem(key);
                var value = parseInt(raw, 10);
                return Number.isFinite(value) && value > 0 ? value : 0;
            } catch (e) {
                return 0;
            }
        }

        function writeStoredTime(key, value) {
            try {
                sessionStorage.setItem(key, String(value));
            } catch (e) {}
        }

        function removeStoredTime(key) {
            try {
                sessionStorage.removeItem(key);
            } catch (e) {}
        }

        var lastActivity = readStoredTime(activityKey) || Date.now();
        writeStoredTime(activityKey, lastActivity);

        function bump() {
            lastActivity = Date.now();
            writeStoredTime(activityKey, lastActivity);
        }

        ['mousemove','mousedown','keydown','scroll','touchstart','click'].forEach(function(evt) {
            document.addEventListener(evt, bump, { passive: true });
        });

        setInterval(function() {
            var storedActivity = readStoredTime(activityKey);
            if (storedActivity > 0) {
                lastActivity = storedActivity;
            }

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
