@extends('layouts.app')
@section('title', 'Dashboard – SQL LMS Dealer Console')

{{-- Link to your external CSS file --}}
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        /* Force progress cell to stay on one line and align all progress bars */
        .dealer-progress-cell {
            display: flex !important;
            flex-direction: row !important;
            flex-wrap: nowrap !important;
            align-items: center !important;
            gap: 10px !important;
            width: 100%;
            min-width: 0 !important; /* Prevents column blowout */
        }
        
        .dealer-progress-text {
            flex: 0 0 85px !important; /* Fixed width stops the progress bars from shifting left/right */
            width: 85px !important;
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            text-align: left !important;
            line-height: 1 !important;
            margin: 0 !important;
        }

        .dealer-status-bar {
            display: flex !important;
            flex-direction: row !important;
            flex-wrap: nowrap !important;
            align-items: center !important;
            gap: 4px !important;
            flex: 1 1 auto !important;
            min-width: 0 !important;
        }

        .dealer-status-segment {
            flex: 1 1 0 !important;
            min-width: 8px !important;
        }
    </style>
@endpush

@section('content')
<div class="dashboard-content dealer-dashboard-content">
    {{-- Summary Cards --}}
    <section class="dashboard-metrics dealer-metrics">
        <div class="dashboard-metric-card">
            <div class="dashboard-metric-icon dashboard-metric-icon-inquiries">
                <i class="bi bi-people"></i>
            </div>
            <div class="dashboard-metric-label">Active Inquiries</div>
            <div class="dashboard-metric-value-row">
                <div class="dashboard-metric-value">{{ number_format($metrics['activeInquiries'] ?? 0) }}</div>
                <span class="dashboard-metric-pill dashboard-metric-pill-up">↑12%</span>
            </div>
        </div>
        <div class="dashboard-metric-card">
            <div class="dashboard-metric-icon dashboard-metric-icon-inquiries">
                <i class="bi bi-clock"></i>
            </div>
            <div class="dashboard-metric-label">Pending Follow-ups</div>
            <div class="dashboard-metric-value-row">
                <div class="dashboard-metric-value">{{ number_format($metrics['pendingFollowups'] ?? 0) }}</div>
                <span class="dashboard-metric-pill dashboard-metric-pill-critical">CRITICAL</span>
            </div>
        </div>
        <div class="dashboard-metric-card">
            <div class="dashboard-metric-icon dashboard-metric-icon-closed">
                <i class="bi bi-calendar-check"></i>
            </div>
            <div class="dashboard-metric-label">Total Closed</div>
            <div class="dashboard-metric-value-row">
                <div class="dashboard-metric-value">{{ number_format($metrics['closedCaseCount'] ?? 0) }}</div>
                <span class="dashboard-metric-pill dashboard-metric-pill-up">↑5</span>
            </div>
        </div>
        <div class="dashboard-metric-card">
            <div class="dashboard-metric-icon dashboard-metric-icon-conversion">
                <i class="bi bi-graph-up"></i>
            </div>
            <div class="dashboard-metric-label">Conversion Rate</div>
            <div class="dashboard-metric-value-row">
                <div class="dashboard-metric-value">{{ $metrics['conversionRate'] ?? 0 }}%</div>
                <span class="dashboard-metric-pill dashboard-metric-pill-down">-2%</span>
            </div>
        </div>
    </section>

    {{-- Main Row: Active Inquiries + Upcoming Demos --}}
    <div class="dealer-dashboard-main" id="dealerMainDashboard">
        
        <div class="dealer-dashboard-main-left">
            <div class="dealer-panel dealer-inquiries-panel">
                <div class="dealer-panel-header">
                    <div class="dealer-panel-title-row">
                        <div class="dealer-panel-icon dealer-panel-icon-demos">
                            <i class="bi bi-folder2"></i>
                        </div>
                        <h2 class="dealer-panel-title">Active Inquiries</h2>
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
                    @forelse(($leads ?? []) as $i => $r)
                        @php
                            $statusMap = [
                                'PENDING' => 'PENDING', 'FOLLOW UP' => 'FOLLOW UP', 'FOLLOWUP' => 'FOLLOW UP',
                                'DEMO' => 'DEMO', 'CONFIRMED' => 'CONFIRMED', 'CASE CONFIRMED' => 'CONFIRMED',
                                'COMPLETED' => 'COMPLETED', 'CASE COMPLETED' => 'COMPLETED',
                                'REWARD' => 'REWARDED', 'REWARDED' => 'REWARDED', 'REWARD DISTRIBUTED' => 'REWARDED'
                            ];
                            $rawStatus = strtoupper(trim($r->ACT_STATUS ?? $r->CURRENTSTATUS ?? 'PENDING'));
                            $status = $statusMap[$rawStatus] ?? 'PENDING';
                            $stages = ['PENDING', 'FOLLOW UP', 'DEMO', 'CONFIRMED', 'COMPLETED', 'REWARDED'];
                            $idx = array_search($status, $stages);
                            $idx = $idx !== false ? $idx : 0;
                            $filledCount = $idx + 1;
                            $displayStatus = $status;
                            $rowPage = (int) floor($i / ($inquiriesPerPage ?? 6)) + 1;
                            
                            // Customer name truncation limit to 24 characters
                            $customerFull = trim(($r->COMPANYNAME ?? '') . ' ' . ($r->CONTACTNAME ?? '')) ?: '—';
                            $customerShort = \Illuminate\Support\Str::limit($customerFull, 24, '...');
                        @endphp
                        <div class="dealer-table-row dealer-inquiry-row" data-page="{{ $rowPage }}">
                            <span class="dealer-inquiry-id">#SQL-{{ $r->LEADID }}</span>
                            <span title="{{ $customerFull !== '—' ? $customerFull : '' }}">{{ $customerShort }}</span>
                            <span>{{ ($r->ACT_LAST_UPDATE ?? $r->LASTMODIFIED) ? date('M j, Y', strtotime($r->ACT_LAST_UPDATE ?? $r->LASTMODIFIED)) : '—' }}</span>
                            <div class="dealer-progress-cell">
                                <span class="dealer-progress-text">{{ $displayStatus }}</span>
                                <div class="dealer-status-bar">
                                    @for($j = 0; $j < 6; $j++)
                                        @php
                                            $isFilled = $j < $filledCount;
                                        @endphp
                                        <div class="dealer-status-segment dealer-status-segment--{{ $j }} {{ $isFilled ? 'dealer-status-segment--filled' : '' }}"></div>
                                    @endfor
                                </div>
                            </div>
                            <span>{{ $idx < 4 ? ($r->LASTMODIFIED ? date('M j, Y', strtotime($r->LASTMODIFIED . ' +3 days')) : 'N/A') : 'N/A' }}</span>
                        </div>
                    @empty
                        <div class="dealer-table-empty">No inquiries assigned yet.</div>
                    @endforelse
                </div>
                <div class="dealer-table-footer">
                    @php
                        $leadsTotal = $leadsTotal ?? 0;
                        $inquiriesPerPage = 8;
                        $inquiriesTotalPages = max(1, (int) ceil($leadsTotal / $inquiriesPerPage));
                    @endphp
                    <span class="dealer-table-count" id="inquiriesCountText">Showing 0 of {{ $leadsTotal }} inquiries</span>
                    <div class="dealer-pagination" id="inquiriesPagination"
                         data-total="{{ $leadsTotal }}"
                         data-per-page="{{ $inquiriesPerPage }}"
                         data-total-pages="{{ $inquiriesTotalPages }}">
                        <button type="button" class="dealer-pagination-btn" id="inquiriesPrevBtn" title="Previous page"><i class="bi bi-chevron-left"></i></button>
                        <div class="dealer-pagination-pages" id="inquiriesPageNumbers"></div>
                        <button type="button" class="dealer-pagination-btn" id="inquiriesNextBtn" title="Next page"><i class="bi bi-chevron-right"></i></button>
                    </div>
                </div>
            </div>
        </div>

        <div class="dealer-dashboard-sidebar-divider">
            <button type="button" class="dealer-right-panel-toggle" id="toggleRightPanelBtn" title="Toggle Sidebar">
                <i class="bi bi-chevron-right" style="transition: transform 0.3s;"></i>
            </button>
        </div>

        <div class="dealer-dashboard-main-right" id="dealerRightPanel">
            <div class="dealer-panel dealer-closed-case-panel dashboard-chart-panel">
                <div class="dealer-panel-header dealer-panel-header--no-action">
                    <div class="dealer-panel-title-row">
                        <div class="dealer-panel-icon dealer-panel-icon-demos">
                            <i class="bi bi-bar-chart-fill"></i>
                        </div>
                        <h2 class="dealer-panel-title">Total Closed</h2>
                    </div>
                    <div class="dashboard-chart-tabs" id="dealerClosedCaseRangeTabs">
                        <button type="button" class="dashboard-chart-tab active" data-range="week">Week</button>
                        <button type="button" class="dashboard-chart-tab" data-range="month">Month</button>
                        <button type="button" class="dashboard-chart-tab" data-range="year">Year</button>
                    </div>
                </div>
                <div class="dashboard-panel-body">
                    <div class="dashboard-chart-container">
                        <canvas id="dealerClosedCaseChart" height="200"></canvas>
                    </div>
                </div>
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
                                <button type="button"
                                        class="dealer-primary-pill dealer-followup-email-btn"
                                        data-lead-id="{{ $h->leadId }}"
                                        data-email="{{ $h->email ?? '' }}"
                                        data-subject="Follow-up: {{ $h->inquiryId }}"
                                        data-body="Hi,\n\nFollowing up on inquiry {{ $h->inquiryId }}.\n\nThanks">
                                    Email Now
                                </button>
                                <button type="button"
                                        class="dealer-secondary-pill dealer-followup-skip-btn"
                                        data-lead-id="{{ $h->leadId }}">
                                    Skip
                                </button>
                            </div>
                        </div>
                    @endforeach
                    @if(empty($highPriorityFollowups))
                        <div class="dealer-alert-empty">No follow-ups due at the moment.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
// Toggle Right Panel Logic
(function() {
    var toggleBtn = document.getElementById('toggleRightPanelBtn');
    var mainContainer = document.getElementById('dealerMainDashboard');
    
    if (!toggleBtn || !mainContainer) return;

    // Apply saved state on load
    if (localStorage.getItem('dealer.rightPanel.hidden') === 'true') {
        mainContainer.classList.add('is-right-panel-hidden');
    }

    toggleBtn.addEventListener('click', function() {
        mainContainer.classList.toggle('is-right-panel-hidden');
        var isHidden = mainContainer.classList.contains('is-right-panel-hidden');
        localStorage.setItem('dealer.rightPanel.hidden', isHidden);
        
        // Trigger chart resize after CSS transition finishes
        setTimeout(function() {
            if (typeof closedChart !== 'undefined' && closedChart !== null) {
                closedChart.resize();
            }
        }, 300);
    });
})();

// Chart Logic
(function() {
    @php
        $closedLabels = $closedCaseChartData['chartLabels'] ?? ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
        $closedData = $closedCaseChartData['chartData'] ?? [0,0,0,0,0,0,0];
        $closedMonthLabels = $closedCaseChartData['chartMonthLabels'] ?? range(1, 30);
        $closedMonthData = $closedCaseChartData['chartMonthData'] ?? array_fill(0, 30, 0);
        $closedYearLabels = $closedCaseChartData['chartYearLabels'] ?? ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        $closedYearData = $closedCaseChartData['chartYearData'] ?? array_fill(0, 12, 0);
    @endphp
    var ctx = document.getElementById('dealerClosedCaseChart')?.getContext('2d');
    var weekLabels = @json($closedLabels);
    var weekData = @json($closedData);
    var monthLabels = @json($closedMonthLabels);
    var monthData = @json($closedMonthData);
    var yearLabels = @json($closedYearLabels);
    var yearData = @json($closedYearData);

    var ranges = {
        week: { labels: weekLabels, data: weekData },
        month: { labels: monthLabels, data: monthData },
        year: { labels: yearLabels, data: yearData }
    };

    window.closedChart = null; // Made global so toggle function can resize it
    if (ctx && weekData) {
        window.closedChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: weekLabels,
                datasets: [{
                    label: 'Closed',
                    data: weekData,
                    backgroundColor: 'rgba(127, 90, 240, 0.6)',
                    borderColor: 'rgba(127, 90, 240, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    }

    document.querySelectorAll('#dealerClosedCaseRangeTabs .dashboard-chart-tab[data-range]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var range = btn.getAttribute('data-range');
            if (!range || !ranges[range]) return;

            document.querySelectorAll('#dealerClosedCaseRangeTabs .dashboard-chart-tab[data-range]').forEach(function(b) { b.classList.remove('active'); });
            btn.classList.add('active');

            if (window.closedChart) {
                window.closedChart.data.labels = ranges[range].labels;
                window.closedChart.data.datasets[0].data = ranges[range].data;
                window.closedChart.update();
            }
        });
    });
})();

// Pagination Logic
(function() {
    var pagination = document.getElementById('inquiriesPagination');
    var rows = document.querySelectorAll('.dealer-inquiry-row');
    var countText = document.getElementById('inquiriesCountText');
    var prevBtn = document.getElementById('inquiriesPrevBtn');
    var nextBtn = document.getElementById('inquiriesNextBtn');
    var pageNumbersEl = document.getElementById('inquiriesPageNumbers');
    if (!pagination || !countText) return;

    var total = parseInt(pagination.getAttribute('data-total') || '0', 10);
    var perPage = parseInt(pagination.getAttribute('data-per-page') || '8', 10);
    var totalPages = Math.max(1, Math.ceil(total / perPage));
    var currentPage = 1;

    function goToPage(page) {
        currentPage = Math.max(1, Math.min(page, totalPages));
        rows.forEach(function(row) {
            row.style.display = parseInt(row.getAttribute('data-page'), 10) === currentPage ? '' : 'none';
        });
        var from = total > 0 ? ((currentPage - 1) * perPage) + 1 : 0;
        var to = Math.min(currentPage * perPage, total);
        countText.textContent = 'Showing ' + (total > 0 ? from + '-' + to + ' of ' : '0 of ') + total + ' inquiries';
        prevBtn.disabled = currentPage <= 1;
        nextBtn.disabled = currentPage >= totalPages;
        var btns = pageNumbersEl.querySelectorAll('button');
        btns.forEach(function(b) {
            b.classList.toggle('dealer-pagination-btn--active', parseInt(b.getAttribute('data-page'), 10) === currentPage);
        });
    }

    function buildPageNumbers() {
        pageNumbersEl.innerHTML = '';
        for (var p = 1; p <= Math.min(totalPages, 5); p++) {
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'dealer-pagination-btn' + (p === 1 ? ' dealer-pagination-btn--active' : '');
            btn.setAttribute('data-page', p);
            btn.textContent = p;
            btn.addEventListener('click', function() { goToPage(parseInt(this.getAttribute('data-page'), 10)); });
            pageNumbersEl.appendChild(btn);
        }
    }

    prevBtn.addEventListener('click', function() { if (!this.disabled) goToPage(currentPage - 1); });
    nextBtn.addEventListener('click', function() { if (!this.disabled) goToPage(currentPage + 1); });
    buildPageNumbers();
    goToPage(1);
})();

// Follow-up Skip & Email Logic
(function() {
    var SKIP_KEY = 'dealer.followups.skip.v1';
    function loadSkipped() {
        try { return JSON.parse(localStorage.getItem(SKIP_KEY) || '{}') || {}; } catch (e) { return {}; }
    }
    function saveSkipped(obj) {
        try { localStorage.setItem(SKIP_KEY, JSON.stringify(obj || {})); } catch (e) {}
    }

    var skipped = loadSkipped();
    document.querySelectorAll('.dealer-followup-skip-btn[data-lead-id]').forEach(function(btn) {
        var id = btn.getAttribute('data-lead-id');
        if (id && skipped[id]) {
            var item = btn.closest('.dealer-alert-item');
            if (item) item.remove();
        }
    });

    document.addEventListener('click', function(e) {
        var emailBtn = e.target.closest('.dealer-followup-email-btn');
        if (emailBtn) {
            e.preventDefault();
            var to = (emailBtn.getAttribute('data-email') || '').trim();
            if (!to) {
                alert('No email address found for this inquiry.');
                return;
            }
            var subject = emailBtn.getAttribute('data-subject') || '';
            var body = emailBtn.getAttribute('data-body') || '';
            var href = 'mailto:' + encodeURIComponent(to)
                + '?subject=' + encodeURIComponent(subject)
                + '&body=' + encodeURIComponent(body);
            window.location.href = href;
            return;
        }

        var skipBtn = e.target.closest('.dealer-followup-skip-btn');
        if (skipBtn) {
            e.preventDefault();
            var leadId = skipBtn.getAttribute('data-lead-id');
            if (leadId) {
                var s = loadSkipped();
                s[leadId] = Date.now();
                saveSkipped(s);
            }
            var item = skipBtn.closest('.dealer-alert-item');
            if (item) item.remove();
        }
    });
})();
</script>
@endpush
@endsection