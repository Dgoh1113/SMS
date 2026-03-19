@php
    $current = $currentPage ?? 'dashboard';
    $role = session('user_role');
@endphp
<aside class="dashboard-sidebar" id="dashboardSidebar">
    <div class="dashboard-sidebar-top">
        <div class="dashboard-brand">
            <div class="dashboard-brand-main">
                <img src="{{ asset('sql-logo.png') }}" alt="SQL logo" class="dashboard-brand-logo-img">
                <span class="dashboard-brand-sms">SMS</span>
            </div>
            <div class="dashboard-brand-subtitle">
                {{ $role === 'manager' ? 'Manager Console' : 'Admin Console' }}
            </div>
        </div>
        <button type="button" class="dashboard-sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">
            <span class="dashboard-sidebar-toggle-inner"></span>
        </button>
    </div>
    <nav class="dashboard-nav">
        <div class="dashboard-nav-group">
        <a href="{{ route('admin.dashboard') }}" class="dashboard-nav-item {{ $current === 'dashboard' ? 'dashboard-nav-item-active' : '' }}">
            <span class="dashboard-nav-icon"><i class="bi {{ $current === 'dashboard' ? 'bi-grid-fill' : 'bi-grid' }}"></i></span>
            <span>Dashboard</span>
        </a>
        <a href="{{ route('admin.inquiries') }}" class="dashboard-nav-item {{ $current === 'inquiries' ? 'dashboard-nav-item-active' : '' }}">
            <span class="dashboard-nav-icon"><i class="bi {{ $current === 'inquiries' ? 'bi-envelope-open' : 'bi-envelope' }}"></i></span>
            <span>Inquiries</span>
        </a>
        <a href="{{ route('admin.dealers') }}" class="dashboard-nav-item {{ $current === 'dealers' ? 'dashboard-nav-item-active' : '' }}">
            <span class="dashboard-nav-icon"><i class="bi {{ $current === 'dealers' ? 'bi-people-fill' : 'bi-people' }}"></i></span>
            <span>Dealers</span>
        </a>
        <a href="{{ route('admin.rewards') }}" class="dashboard-nav-item {{ $current === 'rewards' ? 'dashboard-nav-item-active' : '' }}">
            <span class="dashboard-nav-icon"><i class="bi {{ $current === 'rewards' ? 'bi-gift-fill' : 'bi-gift' }}"></i></span>
            <span>Payouts</span>
        </a>
        <a href="{{ route('admin.reports') }}" class="dashboard-nav-item {{ $current === 'reports' ? 'dashboard-nav-item-active' : '' }}">
            <span class="dashboard-nav-icon"><i class="bi {{ $current === 'reports' ? 'bi-file-earmark-bar-graph-fill' : 'bi-file-earmark-bar-graph' }}"></i></span>
            <span>Reports</span>
        </a>
        <a href="{{ route('admin.history') }}" class="dashboard-nav-item {{ $current === 'history' ? 'dashboard-nav-item-active' : '' }}">
            <span class="dashboard-nav-icon"><i class="bi {{ $current === 'history' ? 'bi-clock-fill' : 'bi-clock-history' }}"></i></span>
            <span>Lead Activities</span>
        </a>
        </div>

        @if ($role === 'admin')
        <div class="dashboard-nav-group dashboard-nav-group-maintain">
            <div class="sidebar-section">Maintenance</div>
            <a href="{{ route('admin.maintain-users') }}" class="dashboard-nav-item {{ $current === 'maintain-users' ? 'dashboard-nav-item-active' : '' }}">
                <span class="dashboard-nav-icon"><i class="bi bi-person-gear"></i></span>
                <span>Maintain Users</span>
            </a>
        </div>
        @endif
    </nav>
</aside>
