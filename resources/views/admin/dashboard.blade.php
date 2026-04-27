@extends('layouts.app')
@section('title', 'Dashboard - Admin')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/admin-dashboard.css') }}?v=20260421-04">
@endpush
@section('content')
<div class="admin-dashboard-page">
<section class="dashboard-metrics">
    @php
        $dashboardMetricDefaultRange = '30';
        $dashboardMetricDefaultChanges = $dashboardMetricRangeChanges[$dashboardMetricDefaultRange] ?? [];
    @endphp
    <div class="dashboard-metric-card">
        <div class="dashboard-metric-icon dashboard-metric-icon-leads"><i class="bi bi-graph-up-arrow"></i></div>
        <div class="dashboard-metric-label">Total Leads</div>
        <div class="dashboard-metric-value-row">
            <div class="dashboard-metric-value">{{ number_format($totalLeads) }}</div>
            @php
                $p = (float)($dashboardMetricDefaultChanges['leads'] ?? $pctLeads ?? 0);
                $trendClass = $p > 0 ? 'dashboard-metric-pill-up' : ($p < 0 ? 'dashboard-metric-pill-down' : 'dashboard-metric-pill-neutral');
                $trendSign = $p > 0 ? '+' : ($p < 0 ? '-' : '');
                $trendText = $p == 0.0 ? 'No change vs previous 30 days' : ($trendSign . abs($p) . '% vs previous 30 days');
            @endphp
            <span class="dashboard-metric-pill {{ $trendClass }}" data-dashboard-metric-trend="leads">{{ $trendText }}</span>
        </div>
    </div>
    <div class="dashboard-metric-card">
        <div class="dashboard-metric-icon dashboard-metric-icon-closed"><i class="bi bi-cash-stack"></i></div>
        <div class="dashboard-metric-label">Total Closed</div>
        <div class="dashboard-metric-value-row">
            <div class="dashboard-metric-value">{{ number_format($totalClosed) }}</div>
            @php
                $p = (float)($dashboardMetricDefaultChanges['closed'] ?? $pctClosed ?? 0);
                $trendClass = $p > 0 ? 'dashboard-metric-pill-up' : ($p < 0 ? 'dashboard-metric-pill-down' : 'dashboard-metric-pill-neutral');
                $trendSign = $p > 0 ? '+' : ($p < 0 ? '-' : '');
                $trendText = $p == 0.0 ? 'No change vs previous 30 days' : ($trendSign . abs($p) . '% vs previous 30 days');
            @endphp
            <span class="dashboard-metric-pill {{ $trendClass }}" data-dashboard-metric-trend="closed">{{ $trendText }}</span>
        </div>
    </div>
    <div class="dashboard-metric-card">
        <div class="dashboard-metric-icon dashboard-metric-icon-inquiries"><i class="bi bi-inbox"></i></div>
        <div class="dashboard-metric-label">Active Inquiries</div>
        <div class="dashboard-metric-value-row">
            <div class="dashboard-metric-value">{{ number_format($activeInquiries) }}</div>
            @php
                $p = (float)($dashboardMetricDefaultChanges['active'] ?? $pctActive ?? 0);
                $trendClass = $p > 0 ? 'dashboard-metric-pill-up' : ($p < 0 ? 'dashboard-metric-pill-down' : 'dashboard-metric-pill-neutral');
                $trendSign = $p > 0 ? '+' : ($p < 0 ? '-' : '');
                $trendText = $p == 0.0 ? 'No change vs previous 30 days' : ($trendSign . abs($p) . '% vs previous 30 days');
            @endphp
            <span class="dashboard-metric-pill {{ $trendClass }}" data-dashboard-metric-trend="active">{{ $trendText }}</span>
        </div>
    </div>
    <div class="dashboard-metric-card">
        <div class="dashboard-metric-icon dashboard-metric-icon-conversion"><i class="bi bi-percent"></i></div>
        <div class="dashboard-metric-label">Conversion Rate</div>
        <div class="dashboard-metric-value-row">
            <div class="dashboard-metric-value">{{ $conversionRate }}%</div>
            @php
                $p = (float)($dashboardMetricDefaultChanges['conversion'] ?? $conversionRateChange ?? 0);
                $trendClass = $p > 0 ? 'dashboard-metric-pill-up' : ($p < 0 ? 'dashboard-metric-pill-down' : 'dashboard-metric-pill-neutral');
                $trendSign = $p > 0 ? '+' : ($p < 0 ? '-' : '');
                $trendText = $p == 0.0 ? 'No change vs previous 30 days' : ($trendSign . abs($p) . '% vs previous 30 days');
            @endphp
            <span class="dashboard-metric-pill {{ $trendClass }}" data-dashboard-metric-trend="conversion">{{ $trendText }}</span>
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
            <button type="button" class="dashboard-chart-tab active" data-range="30">30 Days</button>
            <button type="button" class="dashboard-chart-tab" data-range="60">60 Days</button>
            <button type="button" class="dashboard-chart-tab" data-range="90">90 Days</button>
        </div>
    </div>
    <div class="dashboard-row">
        <div class="dashboard-panel dashboard-chart-panel">
            <div class="dashboard-panel-header">
                <div class="dashboard-panel-title dashboard-chart-title">
                    <span class="dashboard-chart-title-text">Recent Referral Activity</span>
                    <i class="bi bi-info-circle dashboard-info-icon"
                       title="Count of leads turning into Follow-Up status within the selected rolling day range."></i>
                </div>
                @php
                    $p = (float)($dashboardReferralRangeChanges['30'] ?? 0);
                    $trendClass = $p > 0 ? 'dashboard-metric-pill-up' : ($p < 0 ? 'dashboard-metric-pill-down' : 'dashboard-metric-pill-neutral');
                    $trendSign = $p > 0 ? '+' : ($p < 0 ? '-' : '');
                    $trendText = $p == 0.0 ? 'No change vs previous 30 days' : ($trendSign . abs($p) . '% vs previous 30 days');
                @endphp
                <span class="dashboard-metric-pill {{ $trendClass }}" id="referralRangeTrendPill">{{ $trendText }}</span>
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
    const closedRanges = @json($dashboardClosedCaseRanges);
    const referralRanges = @json($dashboardReferralRanges);
    const referralRangeChanges = @json($dashboardReferralRangeChanges);
    const metricRangeChanges = @json($dashboardMetricRangeChanges ?? []);
    const referralTrendPill = document.getElementById('referralRangeTrendPill');
    const metricTrendPills = Array.prototype.slice.call(document.querySelectorAll('[data-dashboard-metric-trend]'));

    function buildWeeklyRange(range) {
        if (!range || !Array.isArray(range.data) || !Array.isArray(range.tooltipTitles)) {
            return { labels: [], data: [], tooltipTitles: [] };
        }

        const labels = [];
        const data = [];
        const tooltipTitles = [];
        const titles = range.tooltipTitles;
        const values = range.data;

        for (let startIndex = 0; startIndex < values.length; startIndex += 7) {
            const endIndex = Math.min(startIndex + 6, values.length - 1);
            let bucketTotal = 0;

            for (let index = startIndex; index <= endIndex; index++) {
                bucketTotal += Number(values[index] || 0);
            }

            labels.push('Week ' + (labels.length + 1));
            tooltipTitles.push((titles[startIndex] || '') + ' - ' + (titles[endIndex] || ''));
            data.push(bucketTotal);
        }

        return {
            labels: labels,
            data: data,
            tooltipTitles: tooltipTitles
        };
    }

    function buildDisplayRanges(sourceRanges) {
        const displayRanges = {};

        Object.keys(sourceRanges || {}).forEach(function(rangeKey) {
            const rawRange = sourceRanges[rangeKey];
            displayRanges[rangeKey] = (rangeKey === '60' || rangeKey === '90')
                ? buildWeeklyRange(rawRange)
                : rawRange;
        });

        return displayRanges;
    }

    const closedDisplayRanges = buildDisplayRanges(closedRanges);
    const referralDisplayRanges = buildDisplayRanges(referralRanges);

    function buildRangeTrendMeta(change, days) {
        const numericChange = Number(change || 0);
        const trendClass = numericChange > 0
            ? 'dashboard-metric-pill-up'
            : (numericChange < 0 ? 'dashboard-metric-pill-down' : 'dashboard-metric-pill-neutral');
        const trendSign = numericChange > 0 ? '+' : (numericChange < 0 ? '-' : '');
        const trendText = numericChange === 0
            ? 'No change vs previous ' + days + ' days'
            : (trendSign + Math.abs(numericChange) + '% vs previous ' + days + ' days');

        return {
            className: trendClass,
            text: trendText
        };
    }

    function updateReferralTrendPill(range) {
        if (!referralTrendPill) {
            return;
        }

        const meta = buildRangeTrendMeta(referralRangeChanges[range] || 0, range);
        referralTrendPill.className = 'dashboard-metric-pill ' + meta.className;
        referralTrendPill.textContent = meta.text;
    }

    function updateDashboardMetricTrendPills(range) {
        metricTrendPills.forEach(function(pill) {
            const metricKey = pill.getAttribute('data-dashboard-metric-trend') || '';
            const rangeValues = metricRangeChanges[range] || {};
            const meta = buildRangeTrendMeta(rangeValues[metricKey] || 0, range);
            pill.className = 'dashboard-metric-pill ' + meta.className;
            pill.textContent = meta.text;
        });
    }

    function formatDashboardTooltipTitle(label, range, dataIndex) {
        const titles = (closedDisplayRanges[range] && closedDisplayRanges[range].tooltipTitles)
            ? closedDisplayRanges[range].tooltipTitles
            : [];

        if (Number.isInteger(dataIndex) && titles[dataIndex]) {
            return titles[dataIndex];
        }

        return String(label);
    }

    let activeRange = '30';
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
                    font: { size: 11 },
                    autoSkip: true,
                    maxTicksLimit: 10,
                    maxRotation: 0,
                    minRotation: 0
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

    if (ctx1 && closedDisplayRanges[activeRange]) {
        const palette = getDashboardChartPalette();
        closedChart = new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: closedDisplayRanges[activeRange].labels,
                datasets: [{
                    label: 'Closed',
                    data: closedDisplayRanges[activeRange].data,
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

    if (ctx2 && referralDisplayRanges[activeRange]) {
        const palette = getDashboardChartPalette();
        referralChart = new Chart(ctx2, {
            type: 'line',
            data: {
                labels: referralDisplayRanges[activeRange].labels,
                datasets: [{
                    label: 'Activity',
                    data: referralDisplayRanges[activeRange].data,
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
            if (!range || !closedDisplayRanges[range] || !referralDisplayRanges[range]) return;

            activeRange = range;
            document.querySelectorAll('#closedCaseRangeTabs .dashboard-chart-tab[data-range]').forEach((b) => b.classList.remove('active'));
            btn.classList.add('active');

            if (closedChart) {
                closedChart.data.labels = closedDisplayRanges[range].labels;
                closedChart.data.datasets[0].data = closedDisplayRanges[range].data;
                closedChart.update();
            }
            if (referralChart) {
                referralChart.data.labels = referralDisplayRanges[range].labels;
                referralChart.data.datasets[0].data = referralDisplayRanges[range].data;
                referralChart.update();
            }

            updateDashboardMetricTrendPills(range);
            updateReferralTrendPill(range);
        });
    });

    updateDashboardMetricTrendPills(activeRange);
    updateReferralTrendPill(activeRange);
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
