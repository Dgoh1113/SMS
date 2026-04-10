@extends('layouts.app')
@section('title', 'Dashboard – SQL LMS Dealer Console')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/dealer-dashboard.css') }}?v=20260410-04">
@endpush

@section('content')
<div class="dashboard-content dealer-dashboard-content dealer-dashboard-page">
    {{-- Summary Cards --}}
    <section class="dashboard-metrics dealer-metrics">
        <div class="dashboard-metric-card">
            <div class="dashboard-metric-icon dashboard-metric-icon-inquiries">
                <i class="bi bi-inbox"></i>
            </div>
            <div class="dashboard-metric-label">Active Inquiries</div>
            <div class="dashboard-metric-value-row">
                <div class="dashboard-metric-value">{{ number_format($metrics['activeInquiries'] ?? 0) }}</div>
                @php
                    $p = (float) ($metrics['pctActive'] ?? 0);
                    $trendClass = $p > 0 ? 'dashboard-metric-pill-up' : ($p < 0 ? 'dashboard-metric-pill-down' : 'dashboard-metric-pill-neutral');
                    $trendSign = $p > 0 ? '+' : ($p < 0 ? '-' : '');
                    $trendText = $p == 0.0 ? 'No change vs last week' : ($trendSign . abs($p) . '% vs last week');
                @endphp
                <span class="dashboard-metric-pill {{ $trendClass }}">{{ $trendText }}</span>
            </div>
        </div>
        <div class="dashboard-metric-card">
            <div class="dashboard-metric-icon dashboard-metric-icon-critical">
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
                <i class="bi bi-cash-stack"></i>
            </div>
            <div class="dashboard-metric-label">Total Closed</div>
            <div class="dashboard-metric-value-row">
                <div class="dashboard-metric-value">{{ number_format($metrics['closedCaseCount'] ?? 0) }}</div>
                @php
                    $p = (float) ($metrics['pctClosed'] ?? 0);
                    $trendClass = $p > 0 ? 'dashboard-metric-pill-up' : ($p < 0 ? 'dashboard-metric-pill-down' : 'dashboard-metric-pill-neutral');
                    $trendSign = $p > 0 ? '+' : ($p < 0 ? '-' : '');
                    $trendText = $p == 0.0 ? 'No change vs last week' : ($trendSign . abs($p) . '% vs last week');
                @endphp
                <span class="dashboard-metric-pill {{ $trendClass }}">{{ $trendText }}</span>
            </div>
        </div>
        <div class="dashboard-metric-card">
            <div class="dashboard-metric-icon dashboard-metric-icon-conversion">
                <i class="bi bi-percent"></i>
            </div>
            <div class="dashboard-metric-label">Conversion Rate</div>
            <div class="dashboard-metric-value-row">
                <div class="dashboard-metric-value">{{ $metrics['conversionRate'] ?? 0 }}%</div>
                @php
                    $p = (float)($metrics['conversionTrend'] ?? 0);
                    $trendClass = $p > 0 ? 'dashboard-metric-pill-up' : ($p < 0 ? 'dashboard-metric-pill-down' : 'dashboard-metric-pill-neutral');
                    $trendSign = $p > 0 ? '+' : ($p < 0 ? '-' : '');
                    $trendText = $p == 0.0 ? 'No change vs last week' : ($trendSign . abs($p) . '% vs last week');
                @endphp
                <span class="dashboard-metric-pill {{ $trendClass }}">{{ $trendText }}</span>
            </div>
        </div>
    </section>

    {{-- Main Row: Active Inquiries + Upcoming Demos --}}
    <div class="dealer-dashboard-main" id="dealerMainDashboard">
        
        <div class="dealer-dashboard-main-left">
            <div class="dealer-panel dealer-inquiries-panel">
                <div class="dealer-panel-header">
                    <div class="dealer-panel-title-row">
                        <h2 class="dealer-panel-title dashboard-panel-title">Active Inquiries</h2>
                    </div>
                        <a href="{{ route('dealer.inquiries') }}" class="dealer-link-btn">View My Inquiries</a>
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
                            $rowPage = (int) floor($i / ($inquiriesPerPage ?? 8)) + 1;
                            
                            // Customer label: Company - Contact, with clean fallback if one side is missing.
                            $customerCompany = trim((string) ($r->COMPANYNAME ?? ''));
                            $customerContact = trim((string) ($r->CONTACTNAME ?? ''));
                            if ($customerCompany !== '' && $customerContact !== '') {
                                $customerFull = $customerCompany . ' - ' . $customerContact;
                            } elseif ($customerCompany !== '') {
                                $customerFull = $customerCompany;
                            } elseif ($customerContact !== '') {
                                $customerFull = $customerContact;
                            } else {
                                $customerFull = '—';
                            }
                        @endphp
                        <a href="{{ route('dealer.inquiries', ['lead' => $r->LEADID, 'action' => 'update']) }}"
                           class="dealer-table-row dealer-inquiry-row dealer-inquiry-row-link"
                           data-page="{{ $rowPage }}"
                           aria-label="Open inquiry #SQL-{{ $r->LEADID }} and update status">
                            <span class="dealer-inquiry-id">#SQL-{{ $r->LEADID }}</span>
                            <span title="{{ $customerFull !== '—' ? $customerFull : '' }}">{{ $customerFull }}</span>
                            <span>{{ $r->LASTMODIFIED ? date('M j, Y', strtotime($r->LASTMODIFIED)) : '—' }}</span>
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
                        </a>
                    @empty
                        <div class="dealer-table-row dealer-table-row--empty">
                            <div class="dealer-table-empty">No inquiries assigned yet.</div>
                        </div>
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

        @php
            $dealerClosedWeekData = $closedCaseChartData['chartData'] ?? [0, 0, 0, 0, 0, 0, 0];
            $dealerClosedWeekHasData = collect($dealerClosedWeekData)->contains(function ($value) {
                return (int) $value > 0;
            });
        @endphp

        <div class="dealer-dashboard-main-right" id="dealerRightPanel">
            <div class="dealer-panel dealer-closed-case-panel dashboard-chart-panel">
                <div class="dashboard-panel-header">
                    <div class="dashboard-panel-title">
                        Closed Case
                        <i class="bi bi-info-circle dashboard-info-icon"
                           title="Count of leads turned into close cases."></i>
                    </div>
                    <div class="dashboard-chart-tabs" id="dealerClosedCaseRangeTabs">
                        <button type="button" class="dashboard-chart-tab active" data-range="week" onclick="window.setDealerClosedCaseRange && window.setDealerClosedCaseRange('week')">Week</button>
                        <button type="button" class="dashboard-chart-tab" data-range="month" onclick="window.setDealerClosedCaseRange && window.setDealerClosedCaseRange('month')">Month</button>
                        <button type="button" class="dashboard-chart-tab" data-range="year" onclick="window.setDealerClosedCaseRange && window.setDealerClosedCaseRange('year')">Year</button>
                    </div>
                </div>
                <div class="dashboard-panel-body">
                    <div class="dashboard-chart-container dealer-chart-shell{{ $dealerClosedWeekHasData ? '' : ' is-empty' }}" id="dealerClosedCaseChartShell">
                        <canvas id="dealerClosedCaseChart" height="200"></canvas>
                        <div class="dealer-chart-empty-state" id="dealerClosedCaseEmpty"{{ $dealerClosedWeekHasData ? ' hidden' : '' }}>
                            <span class="dealer-chart-empty-icon" aria-hidden="true"><i class="bi bi-bar-chart-line"></i></span>
                            <span class="dealer-chart-empty-range" id="dealerClosedCaseEmptyRange">Week</span>
                            <strong class="dealer-chart-empty-title" id="dealerClosedCaseEmptyTitle">No closed cases this week</strong>
                            <span class="dealer-chart-empty-text" id="dealerClosedCaseEmptyText">Closed inquiries completed this week will appear here once there is activity.</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="dealer-panel dealer-alert-panel">
                <div class="dealer-panel-header dealer-panel-header--simple">
                    <div class="dealer-panel-title-row">
                        <h2 class="dealer-panel-title dashboard-panel-title">
                            High Priority Follow-ups
                            <i class="bi bi-info-circle dashboard-info-icon"
                               title="Upcoming and overdue follow-ups (overdue by more than 3 days)."></i>
                        </h2>
                    </div>
                </div>
                <div class="dealer-alert-list">
                    @foreach($highPriorityFollowups ?? [] as $h)
                        <div class="dealer-alert-item dealer-alert-item--{{ strtolower(str_replace(' ', '-', $h->status)) }}">
                            <div class="dealer-alert-top">
                                <span class="dealer-alert-badge dealer-alert-badge--{{ $h->status === 'OVERDUE' ? 'overdue' : 'due' }}">{{ $h->status }} <span class="dealer-alert-badge-time">{{ $h->time }}</span></span>
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
                        <div class="dealer-alert-empty">
                            <div class="dealer-alert-empty-card">
                                <span class="dealer-alert-empty-icon" aria-hidden="true"><i class="bi bi-check2-circle"></i></span>
                                <strong class="dealer-alert-empty-title">No urgent follow-ups</strong>
                                <span class="dealer-alert-empty-text">You're all caught up. Upcoming or overdue follow-ups will appear here automatically.</span>
                            </div>
                        </div>
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
    var STORAGE_KEY = 'dealer.rightPanel.hidden';
    var MOBILE_MEDIA_QUERY = '(max-width: 768px)';
    var toggleBtn = document.getElementById('toggleRightPanelBtn');
    var mainContainer = document.getElementById('dealerMainDashboard');
    
    if (!toggleBtn || !mainContainer) return;

    function isMobileView() {
        return window.matchMedia(MOBILE_MEDIA_QUERY).matches;
    }

    function resizeClosedChartAfterToggle() {
        setTimeout(function() {
            if (typeof closedChart !== 'undefined' && closedChart !== null) {
                closedChart.resize();
            }
        }, 300);
    }

    function setRightPanelHidden(isHidden) {
        mainContainer.classList.toggle('is-right-panel-hidden', !!isHidden);
    }

    var savedDesktopState = null;
    try {
        savedDesktopState = localStorage.getItem(STORAGE_KEY);
    } catch (error) {
        savedDesktopState = null;
    }

    function applyInitialPanelState() {
        if (isMobileView()) {
            setRightPanelHidden(true);
            return;
        }

        setRightPanelHidden(savedDesktopState === 'true');
    }

    applyInitialPanelState();

    toggleBtn.addEventListener('click', function() {
        var isHidden = !mainContainer.classList.contains('is-right-panel-hidden');
        setRightPanelHidden(isHidden);

        if (!isMobileView()) {
            savedDesktopState = isHidden ? 'true' : 'false';
            try {
                localStorage.setItem(STORAGE_KEY, savedDesktopState);
            } catch (error) {}
        }

        resizeClosedChartAfterToggle();
    });

    var mobileMedia = window.matchMedia(MOBILE_MEDIA_QUERY);
    if (typeof mobileMedia.addEventListener === 'function') {
        mobileMedia.addEventListener('change', function() {
            applyInitialPanelState();
            resizeClosedChartAfterToggle();
        });
    } else if (typeof mobileMedia.addListener === 'function') {
        mobileMedia.addListener(function() {
            applyInitialPanelState();
            resizeClosedChartAfterToggle();
        });
    }
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
    var monthWeekRange = buildClosedCaseMonthRange(monthLabels, monthData);

    var ranges = {
        week: { labels: weekLabels, data: weekData },
        month: monthWeekRange,
        year: { labels: yearLabels, data: yearData }
    };
    var activeClosedCaseRange = 'week';
    var closedCaseRangeTabs = document.getElementById('dealerClosedCaseRangeTabs');
    var closedCaseRangeButtons = closedCaseRangeTabs
        ? Array.prototype.slice.call(closedCaseRangeTabs.querySelectorAll('.dashboard-chart-tab[data-range]'))
        : [];
    var closedChartShell = document.getElementById('dealerClosedCaseChartShell');
    var closedChartEmpty = document.getElementById('dealerClosedCaseEmpty');
    var closedChartEmptyRange = document.getElementById('dealerClosedCaseEmptyRange');
    var closedChartEmptyTitle = document.getElementById('dealerClosedCaseEmptyTitle');
    var closedChartEmptyText = document.getElementById('dealerClosedCaseEmptyText');
    var emptyStateMeta = {
        week: {
            label: 'Week',
            title: 'No closed cases this week',
            text: 'Closed inquiries completed this week will appear here once there is activity.'
        },
        month: {
            label: 'Month',
            title: 'No closed cases this month',
            text: 'Closed inquiries completed this month will appear here once there is activity.'
        },
        year: {
            label: 'Year',
            title: 'No closed cases this year',
            text: 'Closed inquiries completed this year will appear here once there is activity.'
        }
    };

    function hasClosedCaseData(values) {
        if (!values) {
            return false;
        }

        var normalizedValues = Array.isArray(values)
            ? values
            : Object.keys(values).map(function(key) { return values[key]; });

        return normalizedValues.some(function(value) {
            return Number(value || 0) > 0;
        });
    }

    function buildClosedCaseMonthRange(labels, data) {
        var bucketLabels = [];
        var bucketData = [];
        var bucketTooltips = [];
        var startIndex = 0;

        while (startIndex < labels.length) {
            var endIndex = Math.min(startIndex + 6, labels.length - 1);
            var weekNumber = bucketLabels.length + 1;
            var rangeTotal = 0;

            for (var i = startIndex; i <= endIndex; i++) {
                rangeTotal += Number(data[i] || 0);
            }

            bucketLabels.push('Week ' + weekNumber);
            bucketData.push(rangeTotal);
            bucketTooltips.push('Days ' + labels[startIndex] + ' - ' + labels[endIndex]);

            startIndex = endIndex + 1;
        }

        return {
            labels: bucketLabels,
            data: bucketData,
            tooltipLabels: bucketTooltips
        };
    }

    function getClosedCaseTickConfig(range, theme) {
        var fontSize = 11;
        var padding = 10;
        var callback = null;
        var fontWeight = '600';

        if (range === 'year') {
            fontSize = 11;
            padding = 10;
            callback = function(value) {
                var label = this.getLabelForValue(value);
                return typeof label === 'string' && label.length ? label.charAt(0) : label;
            };
        }

        var config = {
            color: theme.tick,
            autoSkip: false,
            maxRotation: 0,
            minRotation: 0,
            padding: padding,
            crossAlign: 'far',
            font: {
                size: fontSize,
                weight: fontWeight
            }
        };

        if (callback) {
            config.callback = callback;
        }

        return config;
    }

    function buildClosedCaseChartOptions(range) {
        var theme = getClosedCaseTheme();
        var scaleRange = range || activeClosedCaseRange || 'week';

        return {
            responsive: true,
            maintainAspectRatio: false,
            layout: {
                padding: {
                    top: 4,
                    bottom: scaleRange === 'month' ? 12 : 10
                }
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: theme.tooltipBg,
                    titleColor: theme.tooltipTitle,
                    bodyColor: theme.tooltipBody,
                    cornerRadius: 10,
                    padding: 10,
                    callbacks: {
                        title: function(items) {
                            if (!items || !items.length) {
                                return '';
                            }

                            var item = items[0];
                            if (scaleRange === 'month') {
                                return ranges.month.tooltipLabels[item.dataIndex] || ranges.month.labels[item.dataIndex];
                            }

                            return ranges[scaleRange].labels[item.dataIndex] || '';
                        }
                    }
                }
            },
            scales: {
                x: {
                    offset: true,
                    border: {
                        display: true,
                        color: theme.grid
                    },
                    grid: {
                        display: false,
                        drawBorder: false
                    },
                    ticks: getClosedCaseTickConfig(scaleRange, theme)
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: theme.grid,
                        drawBorder: false,
                        drawTicks: false
                    },
                    border: {
                        display: false
                    },
                    ticks: {
                        color: theme.tick,
                        font: {
                            size: 11,
                            weight: '500'
                        },
                        precision: 0
                    }
                }
            }
        };
    }

    function renderClosedCaseChart(range) {
        if (!ctx) return;
        var activeRange = range && ranges[range] ? range : 'week';
        var theme = getClosedCaseTheme();

        if (window.closedChart) {
            window.closedChart.destroy();
            window.closedChart = null;
        }

        window.closedChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ranges[activeRange].labels,
                datasets: [{
                    label: 'Closed',
                    data: ranges[activeRange].data,
                    backgroundColor: theme.lineFill,
                    borderColor: theme.lineBorder,
                    borderWidth: 3,
                    pointBackgroundColor: theme.pointFill,
                    pointBorderColor: theme.pointBorder,
                    pointBorderWidth: 2,
                    pointRadius: activeRange === 'year' ? 3 : 4,
                    pointHoverRadius: activeRange === 'year' ? 5 : 6,
                    pointHitRadius: 16,
                    fill: true,
                    tension: 0.35,
                    cubicInterpolationMode: 'monotone',
                    spanGaps: true
                }]
            },
            options: buildClosedCaseChartOptions(activeRange)
        });
    }

    function syncClosedCaseEmptyState(range) {
        var activeRange = range && ranges[range] ? range : 'week';
        var isEmpty = !hasClosedCaseData(ranges[activeRange].data);
        var meta = emptyStateMeta[activeRange] || emptyStateMeta.week;
        if (closedChartShell) {
            closedChartShell.classList.toggle('is-empty', isEmpty);
        }
        if (closedChartEmpty) {
            closedChartEmpty.hidden = !isEmpty;
        }
        if (closedChartEmptyRange) {
            closedChartEmptyRange.textContent = meta.label;
        }
        if (closedChartEmptyTitle) {
            closedChartEmptyTitle.textContent = meta.title;
        }
        if (closedChartEmptyText) {
            closedChartEmptyText.textContent = meta.text;
        }
    }

    function setClosedCaseRange(range) {
        if (!range || !ranges[range]) return;
        activeClosedCaseRange = range;

        if (closedCaseRangeButtons.length) {
            closedCaseRangeButtons.forEach(function(b) {
                var isActive = b.getAttribute('data-range') === range;
                b.classList.toggle('active', isActive);
                b.setAttribute('aria-pressed', isActive ? 'true' : 'false');
            });
        }

        renderClosedCaseChart(range);

        syncClosedCaseEmptyState(range);
    }

    window.setDealerClosedCaseRange = setClosedCaseRange;

    function getClosedCaseTheme() {
        var dark = document.documentElement.classList.contains('theme-dark');
        return dark ? {
            lineFill: 'rgba(139, 92, 246, 0.14)',
            lineBorder: 'rgba(191, 171, 255, 0.98)',
            pointFill: 'rgba(191, 171, 255, 1)',
            pointBorder: 'rgba(139, 92, 246, 0.95)',
            tick: '#9fb4d8',
            grid: 'rgba(148, 163, 184, 0.12)',
            tooltipBg: 'rgba(10, 18, 32, 0.96)',
            tooltipTitle: '#f8fafc',
            tooltipBody: '#dbe6ff'
        } : {
            lineFill: 'rgba(127, 90, 240, 0.12)',
            lineBorder: 'rgba(127, 90, 240, 0.96)',
            pointFill: 'rgba(127, 90, 240, 1)',
            pointBorder: 'rgba(238, 231, 255, 1)',
            tick: '#64748b',
            grid: 'rgba(148, 163, 184, 0.18)',
            tooltipBg: 'rgba(15, 23, 42, 0.94)',
            tooltipTitle: '#ffffff',
            tooltipBody: '#ffffff'
        };
    }

    function applyClosedChartTheme(range) {
        renderClosedCaseChart(range || activeClosedCaseRange || 'week');
    }

    window.closedChart = null; // Made global so toggle function can resize it
    if (ctx && weekData) {
        renderClosedCaseChart('week');
    }

    setClosedCaseRange('week');

    if (closedCaseRangeTabs) {
        closedCaseRangeTabs.addEventListener('click', function(event) {
            var btn = event.target.closest('.dashboard-chart-tab[data-range]');
            if (!btn) return;
            var range = btn.getAttribute('data-range');
            if (!range || !ranges[range]) return;
            setClosedCaseRange(range);
        });
    }

    if (closedCaseRangeButtons.length) {
        closedCaseRangeButtons.forEach(function(btn) {
            btn.addEventListener('keydown', function(event) {
                if (event.key !== 'Enter' && event.key !== ' ') return;
                event.preventDefault();
                var range = btn.getAttribute('data-range');
                if (!range || !ranges[range]) return;
                setClosedCaseRange(range);
            });
        });
    }

    if (window.MutationObserver) {
        var themeObserver = new MutationObserver(function(mutations) {
            for (var i = 0; i < mutations.length; i++) {
                if (mutations[i].type === 'attributes') {
                    applyClosedChartTheme(activeClosedCaseRange);
                    syncClosedCaseEmptyState(activeClosedCaseRange);
                    break;
                }
            }
        });
        themeObserver.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['class', 'data-theme']
        });
    }
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
