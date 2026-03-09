@php
    $current = $currentPage ?? 'dashboard';
@endphp
<aside class="dashboard-sidebar">
    <div class="dashboard-sidebar-top">
        <div class="dashboard-brand">
            <div class="dashboard-brand-main">
                <img src="{{ asset('sql-logo.png') }}" alt="SQL logo" class="dashboard-brand-logo-img">
                <span class="dashboard-brand-sms">SMS</span>
            </div>
            <div class="dashboard-brand-subtitle">Admin Console</div>
        </div>
    </div>
    <nav class="dashboard-nav">
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
            <span>Rewards</span>
        </a>
        <a href="{{ route('admin.reports') }}" class="dashboard-nav-item {{ $current === 'reports' ? 'dashboard-nav-item-active' : '' }}">
            <span class="dashboard-nav-icon"><i class="bi {{ $current === 'reports' ? 'bi-file-earmark-bar-graph-fill' : 'bi-file-earmark-bar-graph' }}"></i></span>
            <span>Reports</span>
        </a>
        <a href="{{ route('admin.history') }}" class="dashboard-nav-item {{ $current === 'history' ? 'dashboard-nav-item-active' : '' }}">
            <span class="dashboard-nav-icon"><i class="bi {{ $current === 'history' ? 'bi-clock-fill' : 'bi-clock-history' }}"></i></span>
            <span>History</span>
        </a>
        <a href="{{ route('admin.fulldatabase') }}" class="dashboard-nav-item {{ $current === 'fulldatabase' ? 'dashboard-nav-item-active' : '' }}">
            <span class="dashboard-nav-icon"><i class="bi {{ $current === 'fulldatabase' ? 'bi-database-fill' : 'bi-database' }}"></i></span>
            <span>Full Database</span>
        </a>
    </nav>
</aside>
