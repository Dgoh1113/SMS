@php
    $current = $currentPage ?? 'dashboard';
@endphp
<aside class="dashboard-sidebar">
    <div class="dashboard-sidebar-top">
        <div class="dashboard-brand">
            <div class="dashboard-brand-main">
                <div class="dealer-logo-sq">
                    <span class="dealer-logo-s">S</span>
                </div>
                <span class="dashboard-brand-sms">SQL LMS</span>
            </div>
            <div class="dashboard-brand-subtitle">Dealer Console</div>
        </div>
    </div>
    <nav class="dashboard-nav">
        <a href="{{ route('dealer.dashboard') }}" class="dashboard-nav-item {{ $current === 'dashboard' ? 'dashboard-nav-item-active' : '' }}">
            <span class="dashboard-nav-icon"><i class="bi {{ $current === 'dashboard' ? 'bi-grid-fill' : 'bi-grid' }}"></i></span>
            <span>Dashboard</span>
        </a>
        <a href="{{ route('dealer.inquiries') }}" class="dashboard-nav-item {{ $current === 'inquiries' ? 'dashboard-nav-item-active' : '' }}">
            <span class="dashboard-nav-icon"><i class="bi {{ $current === 'inquiries' ? 'bi-list-ul' : 'bi-list-ul' }}"></i></span>
            <span>My Inquiries</span>
        </a>
        <a href="{{ route('dealer.demo') }}" class="dashboard-nav-item {{ $current === 'demo' ? 'dashboard-nav-item-active' : '' }}">
            <span class="dashboard-nav-icon"><i class="bi bi-calendar-event"></i></span>
            <span>Demo Schedule</span>
        </a>
        <a href="{{ route('dealer.rewards') }}" class="dashboard-nav-item {{ $current === 'rewards' ? 'dashboard-nav-item-active' : '' }}">
            <span class="dashboard-nav-icon"><i class="bi {{ $current === 'rewards' ? 'bi-trophy-fill' : 'bi-trophy' }}"></i></span>
            <span>Rewards</span>
        </a>
        <a href="{{ route('dealer.reports') }}" class="dashboard-nav-item {{ $current === 'reports' ? 'dashboard-nav-item-active' : '' }}">
            <span class="dashboard-nav-icon"><i class="bi {{ $current === 'reports' ? 'bi-bar-chart-fill' : 'bi-bar-chart' }}"></i></span>
            <span>Reports</span>
        </a>
        <a href="{{ route('dealer.history') }}" class="dashboard-nav-item {{ $current === 'history' ? 'dashboard-nav-item-active' : '' }}">
            <span class="dashboard-nav-icon"><i class="bi {{ $current === 'history' ? 'bi-clock-fill' : 'bi-clock-history' }}"></i></span>
            <span>History</span>
        </a>
    </nav>
    <div class="dashboard-sidebar-footer dealer-sidebar-profile">
        <div class="dashboard-user">
            <div class="dashboard-user-avatar">{{ strtoupper(substr(session('user_email', 'A'), 0, 1)) }}</div>
            <div class="dashboard-user-meta">
                <span class="dashboard-user-name">{{ ucfirst(session('user_email') ? explode('@', session('user_email'))[0] : 'Alex') }}</span>
                <span class="dashboard-user-role">Dealer</span>
            </div>
            <a href="#" class="dealer-settings-icon" aria-label="Settings"><i class="bi bi-gear"></i></a>
        </div>
    </div>
</aside>
