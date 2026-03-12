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
            <div class="dashboard-brand-subtitle">Dealer Console</div>
        </div>
    </div>
    <nav class="dashboard-nav">
        <div class="dashboard-nav-group">
            <a href="{{ route('dealer.dashboard') }}" class="dashboard-nav-item {{ $current === 'dashboard' ? 'dashboard-nav-item-active' : '' }}">
                <span class="dashboard-nav-icon"><i class="bi {{ $current === 'dashboard' ? 'bi-grid-fill' : 'bi-grid' }}"></i></span>
                <span>Dashboard</span>
            </a>
            <a href="{{ route('dealer.inquiries') }}" class="dashboard-nav-item {{ $current === 'inquiries' ? 'dashboard-nav-item-active' : '' }}">
                <span class="dashboard-nav-icon"><i class="bi {{ $current === 'inquiries' ? 'bi-envelope-open' : 'bi-envelope' }}"></i></span>
                <span>Inquiries</span>
            </a>
            <a href="{{ route('dealer.rewards') }}" class="dashboard-nav-item {{ $current === 'rewards' ? 'dashboard-nav-item-active' : '' }}">
                <span class="dashboard-nav-icon"><i class="bi {{ $current === 'rewards' ? 'bi-gift-fill' : 'bi-gift' }}"></i></span>
                <span>Rewards</span>
            </a>
            <a href="{{ route('dealer.reports') }}" class="dashboard-nav-item {{ $current === 'reports' ? 'dashboard-nav-item-active' : '' }}">
                <span class="dashboard-nav-icon"><i class="bi {{ $current === 'reports' ? 'bi-file-earmark-bar-graph-fill' : 'bi-file-earmark-bar-graph' }}"></i></span>
                <span>Reports</span>
            </a>
        </div>
    </nav>
</aside>
