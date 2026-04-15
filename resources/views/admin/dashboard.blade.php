@extends('layouts.app')
@section('title', 'Dashboard - Admin')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/admin-dashboard.css') }}?v=20260408-09">
@endpush
@section('content')
<div class="admin-dashboard-page">
<section class="dashboard-metrics">
    <div class="dashboard-metric-card">
        <div class="dashboard-metric-icon dashboard-metric-icon-leads"><i class="bi bi-graph-up-arrow"></i></div>
        <div class="dashboard-metric-label">Total Leads</div>
        <div class="dashboard-metric-value-row">
            <div class="dashboard-metric-value">{{ number_format($totalLeads) }}</div>
            @php
                $p = (float)($pctLeads ?? 0);
                $trendClass = $p > 0 ? 'dashboard-metric-pill-up' : ($p < 0 ? 'dashboard-metric-pill-down' : 'dashboard-metric-pill-neutral');
                $trendSign = $p > 0 ? '+' : ($p < 0 ? '-' : '');
                $trendText = $p == 0.0 ? 'No change vs last week' : ($trendSign . abs($p) . '% vs last week');
            @endphp
            <span class="dashboard-metric-pill {{ $trendClass }}">{{ $trendText }}</span>
        </div>
    </div>
    <div class="dashboard-metric-card">
        <div class="dashboard-metric-icon dashboard-metric-icon-closed"><i class="bi bi-cash-stack"></i></div>
        <div class="dashboard-metric-label">Total Closed</div>
        <div class="dashboard-metric-value-row">
            <div class="dashboard-metric-value">{{ number_format($totalClosed) }}</div>
            @php
                $p = (float)($pctClosed ?? 0);
                $trendClass = $p > 0 ? 'dashboard-metric-pill-up' : ($p < 0 ? 'dashboard-metric-pill-down' : 'dashboard-metric-pill-neutral');
                $trendSign = $p > 0 ? '+' : ($p < 0 ? '-' : '');
                $trendText = $p == 0.0 ? 'No change vs last week' : ($trendSign . abs($p) . '% vs last week');
            @endphp
            <span class="dashboard-metric-pill {{ $trendClass }}">{{ $trendText }}</span>
        </div>
    </div>
    <div class="dashboard-metric-card">
        <div class="dashboard-metric-icon dashboard-metric-icon-inquiries"><i class="bi bi-inbox"></i></div>
        <div class="dashboard-metric-label">Active Inquiries</div>
        <div class="dashboard-metric-value-row">
            <div class="dashboard-metric-value">{{ number_format($activeInquiries) }}</div>
            @php
                $p = (float)($pctActive ?? 0);
                $trendClass = $p > 0 ? 'dashboard-metric-pill-up' : ($p < 0 ? 'dashboard-metric-pill-down' : 'dashboard-metric-pill-neutral');
                $trendSign = $p > 0 ? '+' : ($p < 0 ? '-' : '');
                $trendText = $p == 0.0 ? 'No change vs last week' : ($trendSign . abs($p) . '% vs last week');
            @endphp
            <span class="dashboard-metric-pill {{ $trendClass }}">{{ $trendText }}</span>
        </div>
    </div>
    <div class="dashboard-metric-card">
        <div class="dashboard-metric-icon dashboard-metric-icon-conversion"><i class="bi bi-percent"></i></div>
        <div class="dashboard-metric-label">Conversion Rate</div>
        <div class="dashboard-metric-value-row">
            <div class="dashboard-metric-value">{{ $conversionRate }}%</div>
            @php
                $p = (float)($conversionRateChange ?? 0);
                $trendClass = $p > 0 ? 'dashboard-metric-pill-up' : ($p < 0 ? 'dashboard-metric-pill-down' : 'dashboard-metric-pill-neutral');
                $trendSign = $p > 0 ? '+' : ($p < 0 ? '-' : '');
                $trendText = $p == 0.0 ? 'No change vs last week' : ($trendSign . abs($p) . '% vs last week');
            @endphp
            <span class="dashboard-metric-pill {{ $trendClass }}">{{ $trendText }}</span>
        </div>
    </div>
    <div class="dashboard-metric-card">
        <div class="dashboard-metric-icon dashboard-metric-icon-closing"><i class="bi bi-stopwatch"></i></div>
        <div class="dashboard-metric-label">Avg. Closing Time</div>
        <div class="dashboard-metric-value-row">
            <div class="dashboard-metric-value">{{ $avgClosingTime }}</div>
        </div>
    </div>
</section>

<section class="dashboard-charts-container">
    <div class="dashboard-charts-container-header">
        <div class="dashboard-chart-tabs" id="closedCaseRangeTabs">
            <button type="button" class="dashboard-chart-tab active" data-range="week">Week</button>
            <button type="button" class="dashboard-chart-tab" data-range="month">Month</button>
            <button type="button" class="dashboard-chart-tab" data-range="year">Year</button>
        </div>
    </div>
    <div class="dashboard-row">
        <div class="dashboard-panel dashboard-chart-panel">
            <div class="dashboard-panel-header">
                <div class="dashboard-panel-title dashboard-chart-title">
                    <span class="dashboard-chart-title-text">Recent Referral Activity</span>
                    <i class="bi bi-info-circle dashboard-info-icon"
                       title="Count of leads turning into Follow-Up status by dealer (weekly/monthly/yearly)."></i>
                </div>
                @php
                    $p = (float)($pctReferral ?? 0);
                    $trendClass = $p > 0 ? 'dashboard-metric-pill-up' : ($p < 0 ? 'dashboard-metric-pill-down' : 'dashboard-metric-pill-neutral');
                    $trendSign = $p > 0 ? '+' : ($p < 0 ? '-' : '');
                    $trendText = $p == 0.0 ? 'No change vs last week' : ($trendSign . abs($p) . '% vs last week');
                @endphp
                <span class="dashboard-metric-pill {{ $trendClass }}">{{ $trendText }}</span>
            </div>
            <div class="dashboard-panel-body">
                <div class="dashboard-chart-container">
                    <canvas id="referralChart" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="dashboard-panel dashboard-chart-panel dashboard-chart-panel-closed">
            <div class="dashboard-panel-header">
                <div class="dashboard-panel-title dashboard-chart-title">
                    <span class="dashboard-chart-title-text">Closed Case</span>
                    <i class="bi bi-info-circle dashboard-info-icon"
                       title="Count of leads turned into close cases."></i>
                </div>
            </div>
            <div class="dashboard-panel-body">
                <div class="dashboard-chart-container">
                    <canvas id="closedCaseChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="dashboard-panel dashboard-table-panel">
    <div class="dashboard-panel-header">
        <div class="dashboard-panel-title">Top Active Dealers</div>
        <a href="{{ route('admin.dealers') }}" class="dashboard-panel-link">View All Dealers</a>
    </div>
    <div class="dashboard-panel-body">
        <div class="table-responsive">
            <table class="dashboard-table">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Dealer Name</th>
                        <th>Location</th>
                        <th>Leads</th>
                        <th>Ongoing</th>
                        <th>Closed</th>
                        <th>Failed</th>
                        <th>Conversion</th>
                        <th>Avg. Closing Time</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topDealers as $d)
                        @php
                            $i = $loop->iteration;
                            if ($i > 5) { break; }
                            $rankEmoji = match($i) {
                                1 => html_entity_decode('&#x1F947;', ENT_QUOTES, 'UTF-8'),
                                2 => html_entity_decode('&#x1F948;', ENT_QUOTES, 'UTF-8'),
                                3 => html_entity_decode('&#x1F949;', ENT_QUOTES, 'UTF-8'),
                                4 => html_entity_decode('&#x1F396;&#xFE0F;', ENT_QUOTES, 'UTF-8'),
                                5 => html_entity_decode('&#x1F396;&#xFE0F;', ENT_QUOTES, 'UTF-8'),
                                default => '',
                            };
                        @endphp
                        <tr>
                            <td><span class="dashboard-rank-emoji" title="Rank {{ $i }}">{{ $rankEmoji }}</span></td>
                            <td>{{ $d['dealer_name'] }}</td>
                            <td>{{ $d['location'] }}</td>
                            <td>{{ number_format($d['total_leads']) }}</td>
                            <td>{{ number_format($d['ongoing_count'] ?? 0) }}</td>
                            <td>{{ number_format($d['closed_count']) }}</td>
                            <td>{{ number_format($d['failed_count'] ?? 0) }}</td>
                            @php
                                $cr = (float) ($d['conversion_rate'] ?? 0);
                                $crLabel = $cr >= 60 ? 'High' : ($cr >= 40 ? 'Medium' : 'Low');
                                $crClass = $cr >= 60 ? 'dashboard-badge-green' : ($cr >= 40 ? 'dashboard-badge-orange' : 'dashboard-badge-red');
                            @endphp
                            <td>
                                <span class="dashboard-badge {{ $crClass }}">
                                    <span class="dashboard-badge-dot"></span>
                                    <span class="dashboard-badge-text">{{ rtrim(rtrim(number_format($cr, 1), '0'), '.') }}% {{ $crLabel }}</span>
                                </span>
                            </td>
                            <td>{{ $d['avg_closing_time'] }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="9">No dealer data yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    window.setTimeout(function () {
        window.location.reload();
    }, 30 * 60 * 1000);

    const ctx1 = document.getElementById('closedCaseChart')?.getContext('2d');
    const ctx2 = document.getElementById('referralChart')?.getContext('2d');
    const weekLabels = @json($chartLabels);
    const weekData = @json($chartData);
    const monthLabels = @json($chartMonthLabels);
    const monthData = @json($chartMonthData);
    const yearLabels = @json($chartYearLabels);
    const yearData = @json($chartYearData);
    const referralWeekData = @json($referralWeekData);
    const referralMonthData = @json($referralMonthData);
    const referralYearData = @json($referralYearData);
    const dashboardChartNow = {
        year: {{ now()->year }},
        month: {{ now()->month }},
        day: {{ now()->day }}
    };

    function buildDashboardMonthRange(labels, data) {
        const bucketLabels = [];
        const bucketData = [];
        const bucketTooltips = [];
        const monthStart = new Date(Date.UTC(dashboardChartNow.year, dashboardChartNow.month - 1, 1));
        const firstWeekday = monthStart.getUTCDay();
        const mondayOffset = firstWeekday === 0 ? 6 : firstWeekday - 1;
        const daysInMonth = new Date(Date.UTC(dashboardChartNow.year, dashboardChartNow.month, 0)).getUTCDate();
        const totalWeeks = Math.floor((mondayOffset + daysInMonth - 1) / 7) + 1;
        const weekTotals = Array(totalWeeks).fill(0);

        for (let index = 0; index < labels.length; index++) {
            const dayNumber = parseInt(String(labels[index] ?? ''), 10);
            if (!Number.isFinite(dayNumber)) {
                continue;
            }
            const weekIndex = Math.floor((mondayOffset + dayNumber - 1) / 7);
            if (weekIndex >= 0 && weekIndex < totalWeeks) {
                weekTotals[weekIndex] += Number(data[index] || 0);
            }
        }

        for (let weekIndex = 0; weekIndex < totalWeeks; weekIndex++) {
            const calendarStartDay = 1 + (weekIndex * 7) - mondayOffset;
            const calendarEndDay = calendarStartDay + 6;
            const displayStartDay = Math.max(1, calendarStartDay);
            const displayEndDay = Math.min(daysInMonth, calendarEndDay);
            const startDate = new Date(Date.UTC(dashboardChartNow.year, dashboardChartNow.month - 1, displayStartDay));
            const endDate = new Date(Date.UTC(dashboardChartNow.year, dashboardChartNow.month - 1, displayEndDay));

            bucketLabels.push('Week ' + (weekIndex + 1));
            bucketData.push(weekTotals[weekIndex]);
            bucketTooltips.push(
                startDate.toLocaleDateString('en-US', {
                    day: 'numeric',
                    month: 'short',
                    year: 'numeric',
                    timeZone: 'UTC'
                }) + ' - ' + endDate.toLocaleDateString('en-US', {
                    day: 'numeric',
                    month: 'short',
                    year: 'numeric',
                    timeZone: 'UTC'
                })
            );
        }

        return {
            labels: bucketLabels,
            data: bucketData,
            tooltipTitles: bucketTooltips
        };
    }

    const closedMonthRange = buildDashboardMonthRange(monthLabels, monthData);
    const referralMonthRange = buildDashboardMonthRange(monthLabels, referralMonthData);

    const ranges = {
        week: { labels: weekLabels, data: weekData },
        month: closedMonthRange,
        year: { labels: yearLabels, data: yearData },
    };

    const referralRanges = {
        week: { labels: weekLabels, data: referralWeekData },
        month: referralMonthRange,
        year: { labels: yearLabels, data: referralYearData },
    };

    let activeRange = 'week';
    let closedChart = null;
    let referralChart = null;

    function isDarkTheme() {
        return document.documentElement.classList.contains('theme-dark');
    }

    function getDashboardChartPalette() {
        if (isDarkTheme()) {
            return {
                tick: '#8ea2c8',
                grid: 'rgba(90, 109, 149, 0.16)',
                closedFill: 'rgba(124, 92, 255, 0.72)',
                closedBorder: 'rgba(173, 157, 255, 0.96)',
                referralFill: 'rgba(124, 92, 255, 0.18)',
                referralBorder: 'rgba(179, 163, 255, 1)',
                referralPoint: '#d7cbff',
                tooltipBg: 'rgba(8, 17, 31, 0.94)',
                tooltipBorder: 'rgba(155, 135, 255, 0.36)',
                tooltipText: '#eef4ff',
            };
        }

        return {
            tick: '#6b7280',
            grid: 'rgba(148, 163, 184, 0.18)',
            closedFill: 'rgba(127, 90, 240, 0.6)',
            closedBorder: 'rgba(127, 90, 240, 1)',
            referralFill: 'rgba(127, 90, 240, 0.1)',
            referralBorder: 'rgba(127, 90, 240, 1)',
            referralPoint: 'rgba(127, 90, 240, 1)',
            tooltipBg: 'rgba(17, 24, 39, 0.92)',
            tooltipBorder: 'rgba(127, 90, 240, 0.18)',
            tooltipText: '#ffffff',
        };
    }

    function getDashboardWeekTooltipDate(index) {
        const today = new Date(Date.UTC(
            dashboardChartNow.year,
            dashboardChartNow.month - 1,
            dashboardChartNow.day
        ));
        const weekday = today.getUTCDay();
        const mondayOffset = weekday === 0 ? -6 : 1 - weekday;
        const monday = new Date(today);
        monday.setUTCDate(today.getUTCDate() + mondayOffset);
        monday.setUTCDate(monday.getUTCDate() + index);
        return monday;
    }

    function formatDashboardTooltipTitle(label, range, dataIndex) {
        if (range === 'week' && Number.isInteger(dataIndex)) {
            const fullDate = getDashboardWeekTooltipDate(dataIndex);
            return fullDate.toLocaleDateString('en-US', {
                weekday: 'short',
                day: 'numeric',
                month: 'short',
                year: 'numeric',
                timeZone: 'UTC'
            });
        }

        if (range === 'month' && Number.isInteger(dataIndex)) {
            return closedMonthRange.tooltipTitles[dataIndex] || String(label);
        }

        if (range === 'year') {
            const monthIndex = yearLabels.indexOf(String(label));
            if (monthIndex >= 0) {
                const fullDate = new Date(Date.UTC(dashboardChartNow.year, monthIndex, 1));
                return fullDate.toLocaleDateString('en-US', {
                    month: 'long',
                    year: 'numeric',
                    timeZone: 'UTC'
                });
            }
        }

        return String(label);
    }

    function buildTooltipOptions(palette) {
        return {
            backgroundColor: palette.tooltipBg,
            titleColor: palette.tooltipText,
            bodyColor: palette.tooltipText,
            borderColor: palette.tooltipBorder,
            borderWidth: 1,
            cornerRadius: 10,
            padding: 10,
            displayColors: false,
            mode: 'index',
            intersect: false,
            callbacks: {
                title: function(items) {
                    const firstItem = Array.isArray(items) && items.length ? items[0] : null;
                    const label = firstItem ? firstItem.label : '';
                    const dataIndex = firstItem && Number.isInteger(firstItem.dataIndex) ? firstItem.dataIndex : null;
                    return formatDashboardTooltipTitle(label, activeRange, dataIndex);
                }
            }
        };
    }

    function buildScaleOptions(palette) {
        return {
            x: {
                ticks: {
                    color: palette.tick,
                    font: { size: 11 }
                },
                grid: {
                    color: palette.grid,
                    drawBorder: false
                },
                border: { display: false }
            },
            y: {
                beginAtZero: true,
                ticks: {
                    color: palette.tick,
                    precision: 0,
                    font: { size: 11 }
                },
                grid: {
                    color: palette.grid,
                    drawBorder: false
                },
                border: { display: false }
            }
        };
    }

    function buildLineInteractionOptions() {
        return {
            mode: 'index',
            intersect: false
        };
    }

    function applyDashboardChartTheme() {
        const palette = getDashboardChartPalette();

        if (closedChart) {
            const closedDataset = closedChart.data.datasets[0];
            closedDataset.backgroundColor = palette.closedFill;
            closedDataset.borderColor = palette.closedBorder;
            closedDataset.borderRadius = 10;
            closedDataset.maxBarThickness = 42;
            closedDataset.hoverBackgroundColor = palette.closedBorder;
            closedChart.options.plugins.tooltip = buildTooltipOptions(palette);
            closedChart.options.scales = buildScaleOptions(palette);
            closedChart.update('none');
        }

        if (referralChart) {
            const referralDataset = referralChart.data.datasets[0];
            referralDataset.borderColor = palette.referralBorder;
            referralDataset.backgroundColor = palette.referralFill;
            referralDataset.pointBackgroundColor = palette.referralPoint;
            referralDataset.pointBorderColor = palette.referralPoint;
            referralDataset.pointHoverBackgroundColor = palette.tooltipText;
            referralDataset.pointHoverBorderColor = palette.referralBorder;
            referralDataset.pointRadius = 3;
            referralDataset.pointHoverRadius = 5;
            referralChart.options.plugins.tooltip = buildTooltipOptions(palette);
            referralChart.options.scales = buildScaleOptions(palette);
            referralChart.update('none');
        }
    }

    if (ctx1 && weekData) {
        const palette = getDashboardChartPalette();
        closedChart = new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: weekLabels,
                datasets: [{
                    label: 'Closed',
                    data: weekData,
                    backgroundColor: palette.closedFill,
                    borderColor: palette.closedBorder,
                    borderWidth: 1,
                    borderRadius: 10,
                    maxBarThickness: 42
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: buildLineInteractionOptions(),
                plugins: {
                    legend: { display: false },
                    tooltip: buildTooltipOptions(palette)
                },
                scales: buildScaleOptions(palette)
            }
        });
    }

    if (ctx2 && referralWeekData) {
        const palette = getDashboardChartPalette();
        referralChart = new Chart(ctx2, {
            type: 'line',
            data: {
                labels: weekLabels,
                datasets: [{
                    label: 'Activity',
                    data: referralWeekData,
                    borderColor: palette.referralBorder,
                    backgroundColor: palette.referralFill,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: palette.referralPoint,
                    pointBorderColor: palette.referralPoint,
                    pointRadius: 3,
                    pointHoverRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: buildLineInteractionOptions(),
                hover: buildLineInteractionOptions(),
                plugins: {
                    legend: { display: false },
                    tooltip: buildTooltipOptions(palette)
                },
                scales: buildScaleOptions(palette),
                elements: {
                    point: {
                        hitRadius: 18,
                        hoverRadius: 5
                    }
                }
            }
        });
    }

    document.querySelectorAll('#closedCaseRangeTabs .dashboard-chart-tab[data-range]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const range = btn.getAttribute('data-range');
            if (!range || !ranges[range]) return;

            activeRange = range;
            document.querySelectorAll('#closedCaseRangeTabs .dashboard-chart-tab[data-range]').forEach((b) => b.classList.remove('active'));
            btn.classList.add('active');

            if (closedChart) {
                closedChart.data.labels = ranges[range].labels;
                closedChart.data.datasets[0].data = ranges[range].data;
                closedChart.update();
            }
            if (referralChart) {
                referralChart.data.labels = referralRanges[range].labels;
                referralChart.data.datasets[0].data = referralRanges[range].data;
                referralChart.update();
            }
        });
    });

    applyDashboardChartTheme();

    if (window.MutationObserver) {
        const themeObserver = new MutationObserver(function(mutations) {
            for (const mutation of mutations) {
                if (mutation.type === 'attributes') {
                    applyDashboardChartTheme();
                    break;
                }
            }
        });

        themeObserver.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['class', 'data-theme']
        });
    }
});
</script>
@endpush
@endsection
