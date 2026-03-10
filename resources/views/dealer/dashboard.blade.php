@extends('layouts.app')
@section('title', 'Dashboard – SQL LMS Dealer Console')
@section('content')
<div class="dashboard-content dealer-dashboard-content">
    <header class="dashboard-header">
        <div>
            <h1 class="dashboard-title">Dashboard</h1>
            <p class="dashboard-subtitle">Overview of inquiries and performance metrics</p>
        </div>
    </header>

    {{-- Summary Cards --}}
    <div class="dealer-metrics">
        <div class="dealer-metric-card">
            <div class="dealer-metric-header">
                <div class="dealer-metric-icon dealer-metric-icon-inquiries">
                    <i class="bi bi-people"></i>
                </div>
                <span class="dealer-metric-trend dealer-metric-trend-up">+12% <i class="bi bi-arrow-up"></i></span>
            </div>
            <div class="dealer-metric-label">My Active Inquiries</div>
            <div class="dealer-metric-value">{{ $metrics['activeInquiries'] ?? 42 }}</div>
        </div>
        <div class="dealer-metric-card">
            <div class="dealer-metric-header">
                <div class="dealer-metric-icon dealer-metric-icon-conversion">
                    <i class="bi bi-graph-up"></i>
                </div>
                <span class="dealer-metric-trend dealer-metric-trend-down">-2% <i class="bi bi-arrow-down"></i></span>
            </div>
            <div class="dealer-metric-label">My Conversion Rate</div>
            <div class="dealer-metric-value">{{ $metrics['conversionRate'] ?? '18.5' }}%</div>
        </div>
        <div class="dealer-metric-card">
            <div class="dealer-metric-header">
                <div class="dealer-metric-icon dealer-metric-icon-demos">
                    <i class="bi bi-calendar-check"></i>
                </div>
                <span class="dealer-metric-trend dealer-metric-trend-up">+5 <i class="bi bi-arrow-up"></i></span>
            </div>
            <div class="dealer-metric-label">Demos This Week</div>
            <div class="dealer-metric-value">{{ $metrics['demosThisWeek'] ?? 12 }}</div>
        </div>
        <div class="dealer-metric-card">
            <div class="dealer-metric-header">
                <div class="dealer-metric-icon dealer-metric-icon-pending">
                    <i class="bi bi-clock"></i>
                </div>
                <span class="dealer-metric-trend dealer-metric-trend-critical">CRITICAL</span>
            </div>
            <div class="dealer-metric-label">Pending Follow-ups</div>
            <div class="dealer-metric-value">{{ $metrics['pendingFollowups'] ?? 8 }}</div>
        </div>
    </div>

    {{-- Main Row: My Inquiries + Upcoming Demos --}}
    <div class="dealer-dashboard-main">
        <div class="dealer-dashboard-main-left">
            <div class="dealer-panel dealer-inquiries-panel">
                <div class="dealer-panel-header">
                    <div class="dealer-panel-title-row">
                        <div class="dealer-panel-icon dealer-panel-icon-demos">
                            <i class="bi bi-folder2"></i>
                        </div>
                        <h2 class="dealer-panel-title">My Inquiries</h2>
                    </div>
                    <a href="{{ route('dealer.inquiries') }}" class="dealer-link-btn">View All Inquiries</a>
                </div>
                <div class="dealer-table-wrapper">
                    <div class="dealer-table-header">
                        <span>INQUIRY ID</span>
                        <span>CUSTOMER</span>
                        <span>LAST STATUS UPDATE</span>
                        <span>PROGRESS STAGE</span>
                        <span>NEXT FOLLOW-UP</span>
                    </div>
                    @forelse(array_slice($leads ?? [], 0, 6) as $r)
                        @php
                            $status = strtoupper($r->CURRENTSTATUS ?? 'PENDING');
                            $stages = ['PENDING','FOLLOW UP','DEMO','CASE CONFIRMED','CASE COMPLETED','REWARD DISTRIBUTED'];
                            $idx = array_search($status, $stages);
                            $idx = $idx !== false ? $idx : 0;
                            $filledCount = $idx + 1;
                            $displayStatus = in_array($status, $stages) ? $status : 'PENDING';
                        @endphp
                        <div class="dealer-table-row">
                            <span class="dealer-inquiry-id">#LX-{{ $r->LEADID }}</span>
                            <span>{{ $r->CONTACTNAME ? 'Mr/Ms ' . $r->CONTACTNAME : ($r->COMPANYNAME ?? '—') }}</span>
                            <span>{{ $r->LASTMODIFIED ? date('M j, Y', strtotime($r->LASTMODIFIED)) : '—' }}</span>
                            <div class="dealer-progress-cell">
                                <span class="dealer-progress-text">{{ $displayStatus }}</span>
                                <div class="dealer-status-bar">
                                    @for($i = 0; $i < 6; $i++)
                                        @php
                                            $isFilled = $i < $filledCount;
                                            $segmentColor = $i < 2 ? 'red' : ($i < 4 ? 'yellow' : 'green');
                                        @endphp
                                        <div class="dealer-status-segment dealer-status-segment--{{ $segmentColor }} {{ $isFilled ? 'dealer-status-segment--filled' : '' }}"></div>
                                    @endfor
                                </div>
                            </div>
                            <span>{{ $idx < 4 ? ($r->LASTMODIFIED ? date('M j, Y', strtotime($r->LASTMODIFIED . ' +7 days')) : 'N/A') : 'N/A' }}</span>
                        </div>
                    @empty
                        <div class="dealer-table-row dealer-table-empty">
                            <span colspan="5">No inquiries assigned yet.</span>
                        </div>
                    @endforelse
                </div>
                <div class="dealer-table-footer">
                    <span class="dealer-table-count">Showing {{ min(6, count($leads ?? [])) }} of {{ count($leads ?? []) }} inquiries</span>
                    <div class="dealer-pagination">
                        <button type="button" class="dealer-pagination-btn" disabled><i class="bi bi-chevron-left"></i></button>
                        <button type="button" class="dealer-pagination-btn dealer-pagination-btn--active">1</button>
                        <button type="button" class="dealer-pagination-btn">2</button>
                        <button type="button" class="dealer-pagination-btn"><i class="bi bi-chevron-right"></i></button>
                    </div>
                </div>
            </div>
        </div>
        <div class="dealer-dashboard-main-right">
            <div class="dealer-panel dealer-demos-panel">
                <div class="dealer-panel-header dealer-panel-header--no-action">
                    <div class="dealer-panel-title-row">
                        <div class="dealer-panel-icon dealer-panel-icon-demos">
                            <i class="bi bi-calendar-event"></i>
                        </div>
                        <h2 class="dealer-panel-title">Upcoming Demos</h2>
                    </div>
                </div>
                <div class="dealer-upcoming-list">
                    @foreach($upcomingDemos ?? [] as $d)
                        <div class="dealer-upcoming-item">
                            <div class="dealer-upcoming-date-block">
                                <span class="dealer-upcoming-day">{{ $d->day }}</span>
                                <span class="dealer-upcoming-date-num">{{ $d->dateNum }}</span>
                            </div>
                            <div class="dealer-upcoming-info">
                                <span class="dealer-upcoming-title">{{ $d->title }}</span>
                                <span class="dealer-upcoming-meta">{{ $d->time }} • {{ $d->contact }}</span>
                            </div>
                        </div>
                    @endforeach
                    @if(empty($upcomingDemos))
                        <div class="dealer-upcoming-item">
                            <div class="dealer-upcoming-date-block">
                                <span class="dealer-upcoming-day">OCT</span>
                                <span class="dealer-upcoming-date-num">24</span>
                            </div>
                            <div class="dealer-upcoming-info">
                                <span class="dealer-upcoming-title">SQL Payroll Demo</span>
                                <span class="dealer-upcoming-meta">10:30 AM • Ms Ng</span>
                            </div>
                        </div>
                        <div class="dealer-upcoming-item">
                            <div class="dealer-upcoming-date-block">
                                <span class="dealer-upcoming-day">OCT</span>
                                <span class="dealer-upcoming-date-num">24</span>
                            </div>
                            <div class="dealer-upcoming-info">
                                <span class="dealer-upcoming-title">SQL Account Demo</span>
                                <span class="dealer-upcoming-meta">2:00 PM • Mr Lim</span>
                            </div>
                        </div>
                    @endif
                </div>
                <a href="{{ route('dealer.demo') }}" class="dealer-upcoming-schedule-link">Open Full Schedule</a>
            </div>
            <div class="dealer-panel dealer-alert-panel">
                <div class="dealer-panel-header dealer-panel-header--simple">
                    <div class="dealer-panel-title-row">
                        <span class="dealer-alert-panel-icon"><i class="bi bi-exclamation-circle-fill"></i></span>
                        <h2 class="dealer-panel-title">High Priority Follow-ups</h2>
                    </div>
                </div>
                <div class="dealer-alert-list">
                    @foreach($highPriorityFollowups ?? [] as $h)
                        <div class="dealer-alert-item dealer-alert-item--{{ strtolower(str_replace(' ', '-', $h->status)) }}">
                            <div class="dealer-alert-top">
                                <span class="dealer-alert-badge dealer-alert-badge--{{ $h->status === 'OVERDUE' ? 'overdue' : 'due' }}">{{ $h->status }}</span>
                                <span class="dealer-alert-time">{{ $h->time }}</span>
                            </div>
                            <div class="dealer-alert-main">
                                <span class="dealer-alert-title">{{ $h->inquiryId }} {{ $h->contact }}</span>
                                <span class="dealer-alert-subtitle">{{ $h->product }}</span>
                            </div>
                            <div class="dealer-alert-actions">
                                <button type="button" class="dealer-primary-pill">Email Now</button>
                                <button type="button" class="dealer-secondary-pill">Skip</button>
                            </div>
                        </div>
                    @endforeach
                    @if(empty($highPriorityFollowups))
                        <div class="dealer-alert-item dealer-alert-item--overdue">
                            <div class="dealer-alert-top">
                                <span class="dealer-alert-badge dealer-alert-badge--overdue">OVERDUE</span>
                                <span class="dealer-alert-time">2h late</span>
                            </div>
                            <div class="dealer-alert-main">
                                <span class="dealer-alert-title">LX-1234 Ms Wong</span>
                                <span class="dealer-alert-subtitle">SQL Account + Stock</span>
                            </div>
                            <div class="dealer-alert-actions">
                                <button type="button" class="dealer-primary-pill">Email Now</button>
                                <button type="button" class="dealer-secondary-pill">Skip</button>
                            </div>
                        </div>
                        <div class="dealer-alert-item dealer-alert-item--due-soon">
                            <div class="dealer-alert-top">
                                <span class="dealer-alert-badge dealer-alert-badge--due">DUE SOON</span>
                                <span class="dealer-alert-time">In 45m</span>
                            </div>
                            <div class="dealer-alert-main">
                                <span class="dealer-alert-title">LX-6789 Ms Sarah</span>
                                <span class="dealer-alert-subtitle">SQL Payroll (50 staffs)</span>
                            </div>
                            <div class="dealer-alert-actions">
                                <button type="button" class="dealer-primary-pill">Email Now</button>
                                <button type="button" class="dealer-secondary-pill">Skip</button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Footer --}}
    <footer class="dashboard-bottombar">
        <div class="dashboard-bottombar-left">
            <button type="button" class="dashboard-sync-btn" title="Refresh"><i class="bi bi-arrow-clockwise dashboard-sync-icon"></i></button>
            <div class="dashboard-sync-text">
                <span class="dashboard-sync-title">SYSTEM SYNC STATUS</span>
                <span class="dashboard-sync-time">Last synced: {{ date('M j, Y, g:i A') }}</span>
            </div>
        </div>
        <div class="dashboard-bottombar-right">
            © Copyright {{ date('Y') }} SQL Lead Management System. All rights reserved.
        </div>
    </footer>
</div>
@endsection
