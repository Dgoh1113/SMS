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
    @if (session('user_role') === 'admin')
        @include('partials.sidebar-admin')
    @elseif (session('user_role') === 'dealer')
        @include('partials.sidebar-dealer')
    @endif

    <main class="dashboard-main">
        <header class="dashboard-topbar">
            <div class="dashboard-topbar-actions">
                <a href="#" class="dashboard-icon-btn top-right-btn" type="button"><img src="{{ asset('Guide.ico') }}" alt="Guide" class="dashboard-icon-img"></a>
                <a href="#" class="dashboard-icon-btn top-right-btn" type="button"><img src="{{ asset('Notification.ico') }}" alt="Notifications" class="dashboard-icon-img"></a>
                <div class="dashboard-profile-btn" style="cursor:default;">
                    <div class="dashboard-user-avatar">{{ strtoupper(substr(session('user_email', 'U'), 0, 1)) }}</div>
                </div>
                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="dashboard-profile-signout-btn" style="margin-left:8px;">Sign out</button>
                </form>
            </div>
        </header>

        @if (session('error'))
            <div class="login-message login-error" style="margin:16px;">{{ session('error') }}</div>
        @endif
        @if (session('success'))
            <div class="login-message login-success" style="margin:16px;">{{ session('success') }}</div>
        @endif

        @yield('content')
    </main>
</div>
@stack('scripts')
</body>
</html>
