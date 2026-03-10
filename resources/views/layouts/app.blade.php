<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SQL Sales Management System')</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
<div class="dashboard-root {{ isset($sidebarCollapsed) && $sidebarCollapsed ? 'dashboard-root-sidebar-collapsed' : '' }}">
    @if (in_array(session('user_role'), ['admin', 'manager'], true))
        @include('partials.sidebar-admin')
    @elseif (session('user_role') === 'dealer')
        @include('partials.sidebar-dealer')
    @endif

    <main class="dashboard-main">
        <header class="dashboard-topbar">
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
            <div class="login-message login-error" style="margin:16px;">{{ session('error') }}</div>
        @endif
        @if (session('success'))
            <div class="login-message login-success" style="margin:16px;">{{ session('success') }}</div>
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
    var trigger = document.getElementById('profileDropdownTrigger');
    var menu = document.getElementById('profileDropdownMenu');
    if (!trigger || !menu) return;
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
})();
</script>
@endpush
@stack('scripts')
</body>
</html>
