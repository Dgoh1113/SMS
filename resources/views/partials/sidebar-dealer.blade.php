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
        <a href="{{ route('dealer.dashboard') }}" class="dashboard-nav-item {{ $current === 'dashboard' ? 'dashboard-nav-item-active' : '' }}">
            <span class="dashboard-nav-icon"><i class="bi {{ $current === 'dashboard' ? 'bi-grid-fill' : 'bi-grid' }}"></i></span>
            <span>Dashboard</span>
        </a>
        <a href="{{ route('dealer.demo') }}" class="dashboard-nav-item {{ $current === 'demo' ? 'dashboard-nav-item-active' : '' }}">
            <span class="dashboard-nav-icon"><i class="bi bi-calendar-event"></i></span>
            <span>Demo Schedule</span>
        </a>
    </nav>
</aside>
