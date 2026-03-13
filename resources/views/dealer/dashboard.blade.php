@extends('layouts.app')
@section('title', 'Dashboard – SQL LMS Dealer Console')
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
    <div class="dealer-dashboard-main">
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
                        @endphp
                        <div class="dealer-table-row dealer-inquiry-row" data-page="{{ $rowPage }}">
                            <span class="dealer-inquiry-id">#SQL-{{ $r->LEADID }}</span>
                            <span>{{ trim(($r->COMPANYNAME ?? '') . ' ' . ($r->CONTACTNAME ?? '')) ?: '—' }}</span>
                            <span>{{ ($r->ACT_LAST_UPDATE ?? $r->LASTMODIFIED) ? date('M j, Y', strtotime($r->ACT_LAST_UPDATE ?? $r->LASTMODIFIED)) : '—' }}</span>
                            <div class="dealer-progress-cell">
                                <span class="dealer-progress-text">{{ $displayStatus }}</span>
                                <div class="dealer-status-bar">
                                    @for($i = 0; $i < 6; $i++)
                                        @php
                                            $isFilled = $i < $filledCount;
                                        @endphp
                                        <div class="dealer-status-segment dealer-status-segment--{{ $i }} {{ $isFilled ? 'dealer-status-segment--filled' : '' }}"></div>
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
                        $inquiriesTotalPages = max(1, $inquiriesTotalPages ?? 1);
                        $inquiriesPerPage = $inquiriesPerPage ?? 6;
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
        <div class="dealer-dashboard-main-right">
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
                                <button type="button" class="dealer-primary-pill">Email Now</button>
                                <button type="button" class="dealer-secondary-pill">Skip</button>
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

    var closedChart = null;
    if (ctx && weekData) {
        closedChart = new Chart(ctx, {
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

            if (closedChart) {
                closedChart.data.labels = ranges[range].labels;
                closedChart.data.datasets[0].data = ranges[range].data;
                closedChart.update();
            }
        });
    });
})();

(function() {
    var pagination = document.getElementById('inquiriesPagination');
    var rows = document.querySelectorAll('.dealer-inquiry-row');
    var countText = document.getElementById('inquiriesCountText');
    var prevBtn = document.getElementById('inquiriesPrevBtn');
    var nextBtn = document.getElementById('inquiriesNextBtn');
    var pageNumbersEl = document.getElementById('inquiriesPageNumbers');
    if (!pagination || !countText) return;

    var total = parseInt(pagination.getAttribute('data-total') || '0', 10);
    var perPage = parseInt(pagination.getAttribute('data-per-page') || '6', 10);
    var totalPages = parseInt(pagination.getAttribute('data-total-pages') || '1', 10);
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
</script>
@endpush
@endsection
