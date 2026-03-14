@extends('layouts.app')
@section('title', 'Report - Monthly Performance Analytics')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/report_monthly_performance_analytics.css') }}">
@endpush
@section('content')
<div class="reports-page">
<div class="reports-page-layout">
<header class="dashboard-header">
    <div>
        <div id="reportsTitleHover" class="reports-title-hover">
            <div class="reports-title-dropdown">
                <button id="dropdownHoverButton" data-dropdown-toggle="dropdownHover" data-dropdown-trigger="hover" class="reports-dropdown-btn" type="button">
                    Report - Monthly Performance Analytics
                    <svg class="reports-dropdown-caret" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7"/></svg>
                </button>

                <!-- Dropdown menu -->
                <div id="dropdownHover" class="reports-dropdown-menu" role="menu" aria-labelledby="dropdownHoverButton">
                    <ul class="reports-dropdown-list">
                        <li><a href="{{ route('admin.reports') }}" class="reports-dropdown-item">Report - Monthly Performance Analytics</a></li>
                        <li><a href="{{ route('admin.reports.v2') }}" class="reports-dropdown-item">Report - Dealer Sales Overtime</a></li>
                        <li><a href="{{ route('admin.reports.revenue') }}" class="reports-dropdown-item">Report - Dealer Revenue Production</a></li>
                    </ul>
                </div>
            </div>
            <p class="dashboard-subtitle">Overview of system activities and performance</p>
        </div>
    </div>
</header>

@php
    $totalLeads = array_sum($leadStatus);
    $totalActivities = array_sum($activityStatus);
    $totalPayouts = array_sum($payoutStatus);
    $totalStatus = max($totalActivities, 1);
    $statusColors = [
        'Created' => '#94a3b8',
        'Pending' => '#a855f7',
        'FollowUp' => '#22c55e',
        'Demo' => '#0ea5e9',
        'Confirmed' => '#6366f1',
        'Completed' => '#14b8a6',
        'Failed' => '#dc2626',
        'reward' => '#f97316',
    ];
@endphp

@php
    $metricCards = [
        ['key' => 'unassigned', 'label' => 'UNASSIGNED', 'value' => $unassignedLeads, 'btn' => 'Assign'],
        ['key' => 'Pending', 'label' => 'PENDING', 'value' => $activityStatus['Pending'] ?? 0, 'dealer' => true],
        ['key' => 'FollowUp', 'label' => 'FOLLOW-UP', 'value' => $activityStatus['FollowUp'] ?? 0, 'dealer' => true],
        ['key' => 'Demo', 'label' => 'DEMO', 'value' => $activityStatus['Demo'] ?? 0, 'dealer' => true],
        ['key' => 'Confirmed', 'label' => 'CONFIRMED', 'value' => $activityStatus['Confirmed'] ?? 0, 'dealer' => true],
        ['key' => 'Completed', 'label' => 'COMPLETED', 'value' => $activityStatus['Completed'] ?? 0, 'dealer' => true],
        ['key' => 'Pending Reward', 'label' => 'PENDING REWARD', 'value' => $payoutStatus['Pending'] ?? 0, 'btn' => 'Assign'],
        ['key' => 'Rewarded', 'label' => 'REWARDED', 'value' => $payoutStatus['Paid'] ?? 0, 'btn' => 'View'],
    ];
@endphp
<section class="dashboard-metrics-grid">
    @foreach ($metricCards as $card)
    @php
        $pct = $metricPercent[$card['key']] ?? 0;
        $trend = $pct > 0 ? 'up' : ($pct < 0 ? 'down' : 'same');
    @endphp
    <div class="dashboard-metric-card">
        <div class="dashboard-metric-card-top">
            <div class="dashboard-metric-label">{{ $card['label'] }}</div>
            @if (!empty($card['dealer']))
            <span class="dashboard-metric-dealer-tag">DEALER MANAGED</span>
            @else
            <span class="dashboard-metric-dealer-spacer"></span>
            @endif
        </div>
        <div class="dashboard-metric-value-row">
            <div class="dashboard-metric-value">{{ $card['value'] }}</div>
        </div>
        <div class="dashboard-metric-trend dashboard-metric-trend-{{ $trend }}">
            @if ($trend === 'up')
            <svg class="dashboard-metric-trend-icon" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v13m0-13 4 4m-4-4-4 4"/></svg>
            <span>+{{ $pct }}% vs last month</span>
            @elseif ($trend === 'down')
            <svg class="dashboard-metric-trend-icon" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19V5m0 0-4 4m4-4 4 4"/></svg>
            <span>{{ $pct }}% vs last month</span>
            @else
            <svg class="dashboard-metric-trend-icon dashboard-metric-trend-icon-same" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-4 4 4-4m0 0 4-4"/></svg>
            <span>→ No change</span>
            @endif
        </div>
        <div class="dashboard-metric-btn-wrap">
            @if (!empty($card['btn']))
            <button type="button" class="dashboard-metric-btn">{{ $card['btn'] }}</button>
            @endif
        </div>
    </div>
    @endforeach
</section>

<section class="dashboard-panels-two-column">
    <div class="inquiry-chart-box max-w-sm w-full bg-neutral-primary-soft border border-default rounded-base shadow-xs p-4 md:p-6">
        <div class="flex justify-between items-start">
            <div>
                @php
                    $inquiryTotal = array_sum(array_column($inquiryTrend, 'count'));
                    $displayTotal = $inquiryTotal >= 1000 ? number_format($inquiryTotal / 1000, 1) . 'k' : (string) $inquiryTotal;
                @endphp
                <p id="inquiryRangeText" class="text-body">Inquiries this month</p>
                <h5 id="inquiryTotal" class="text-2xl font-semibold text-heading">{{ $displayTotal }}</h5>
            </div>
            <div id="inquiryPercentBadge" class="flex items-center inquiry-trend-badge font-medium text-center inquiry-trend-same">
                <svg class="inquiry-percent-arrow inquiry-percent-arrow-up" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v13m0-13 4 4m-4-4-4 4"/></svg>
                <svg class="inquiry-percent-arrow inquiry-percent-arrow-down" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19V5m0 0-4 4m4-4 4 4"/></svg>
                <span id="inquiryPercent" class="inquiry-percent">0%</span>
            </div>
        </div>
        <div class="dashboard-panel-body mt-4">
            @if (count($inquiryTrend) === 0)
                <p class="text-muted">No leads created this month yet.</p>
            @else
                <div class="inquiry-chart-filters">
                    <button type="button" class="inquiry-filter-btn active" data-days="7">7 days</button>
                    <button type="button" class="inquiry-filter-btn" data-days="14">14 days</button>
                    <button type="button" class="inquiry-filter-btn" data-days="0">Full month</button>
                </div>
                <div id="area-chart"></div>
            @endif
        </div>
    </div>

    <div class="dashboard-panel">
        <div class="dashboard-panel-header">
            <div class="dashboard-panel-title">Status Report</div>
        </div>
        <div class="dashboard-panel-body report-status-body">
            @php
                $segments = [];
                $offset = 0;
                foreach ($activityStatus as $name => $value) {
                    if ($value <= 0) {
                        continue;
                    }
                    $percent = $value / $totalStatus * 100;
                    $segments[] = [
                        'from' => $offset,
                        'to' => $offset + $percent,
                        'color' => $statusColors[$name] ?? '#e5e7eb',
                    ];
                    $offset += $percent;
                }
                $gradientParts = collect($segments)->map(function ($s) {
                    return $s['color'] . ' ' . $s['from'] . '% ' . $s['to'] . '%';
                })->implode(', ');
            @endphp
            <div class="report-donut-wrapper">
                <div class="report-donut"
                     style="background: conic-gradient({{ $gradientParts ?: '#e5e7eb 0 100%' }});">
                    <div class="report-donut-center">
                        <div class="report-donut-total">{{ $totalActivities }}</div>
                        <div class="report-donut-label">Activities</div>
                    </div>
                </div>
            </div>
            <ul class="report-legend">
                @foreach ($activityStatus as $name => $value)
                    <li>
                        <span class="report-legend-color"
                              style="background-color: {{ $statusColors[$name] ?? '#e5e7eb' }}"></span>
                        <span class="report-legend-label">{{ $name === 'reward' ? 'Rewarded' : $name }}</span>
                        <span class="report-legend-value">{{ $value }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</section>

<section class="dashboard-panel dashboard-table-panel reports-product-section">
    <div class="dashboard-panel-header">
        <div>
            <div class="dashboard-panel-title">Monthly Performance Conversion</div>
            <div class="dashboard-panel-subtitle">Top 10 closed case dealers this month</div>
        </div>
    </div>
    <div class="dashboard-panel-body">
        @if (count($productConversion) === 0)
            <p class="text-muted">No closed cases this month yet.</p>
        @else
            @php
                $dealerCount = count($productConversion);
                $barHeightPx = 32;
                $gapPx = 10;
                $paddingPx = 60;
                $chartHeightPx = max(140, $dealerCount * ($barHeightPx + $gapPx) + $paddingPx);
            @endphp
            <div class="reports-product-chart-wrapper" style="height: {{ $chartHeightPx }}px;">
                <canvas id="productConversionChart"></canvas>
            </div>
        @endif
    </div>
</section>
</div>
</div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Title dropdown (light purple)
            const dropdownBtn = document.getElementById('dropdownHoverButton');
            const dropdown = document.getElementById('dropdownHover');
            const titleHover = document.getElementById('reportsTitleHover');
            if (dropdownBtn && dropdown) {
                const closeDropdown = () => {
                    dropdown.classList.remove('is-open');
                    dropdownBtn.classList.remove('is-open');
                    if (titleHover) titleHover.classList.remove('is-open');
                };
                const toggleDropdown = (e) => {
                    if (e) e.preventDefault();
                    const open = dropdown.classList.toggle('is-open');
                    dropdownBtn.classList.toggle('is-open', open);
                    if (titleHover) titleHover.classList.toggle('is-open', open);
                };
                dropdownBtn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    toggleDropdown(e);
                });
                if (titleHover) {
                    titleHover.addEventListener('click', function (e) {
                        // Don't re-toggle when clicking inside the menu
                        if (dropdown.contains(e.target)) return;
                        // Button (including arrow) already handled above
                        if (dropdownBtn.contains(e.target)) return;
                        toggleDropdown(e);
                    });
                }
                document.addEventListener('click', function (e) {
                    if (!dropdown.contains(e.target) && !dropdownBtn.contains(e.target) && !(titleHover && titleHover.contains(e.target))) {
                        closeDropdown();
                    }
                });
                window.addEventListener('keydown', function (e) {
                    if (e.key === 'Escape') closeDropdown();
                });
            }

            // Inquiry Trends area chart
            const inquiryTrendData = @json($inquiryTrend);
            const monthName = @json(now()->format('F'));
            const daysInMonth = @json(now()->daysInMonth);
            const currentDay = @json((int) now()->format('d'));

            const trendByDay = {};
            inquiryTrendData.forEach(function(p) { trendByDay[p.day] = p.count; });

            const categories = [];
            const seriesData = [];
            for (let d = 1; d <= daysInMonth; d++) {
                categories.push((d < 10 ? '0' : '') + d + ' ' + monthName);
                seriesData.push(trendByDay[d] || 0);
            }
            const monthTotal = seriesData.reduce((acc, v) => acc + (v || 0), 0);

            const getBrandColor = function() {
                const computedStyle = getComputedStyle(document.documentElement);
                return computedStyle.getPropertyValue('--color-fg-brand').trim() || "#1447E6";
            };
            const brandColor = getBrandColor();

            function buildChartOptions(cats, data) {
                return {
                    chart: {
                        height: "100%",
                        maxWidth: "100%",
                        type: "area",
                        fontFamily: "Inter, sans-serif",
                        dropShadow: { enabled: false },
                        toolbar: { show: false },
                        zoom: { enabled: false },
                    },
                    tooltip: { enabled: true, x: { show: false } },
                    fill: {
                        type: "gradient",
                        gradient: {
                            opacityFrom: 0.55,
                            opacityTo: 0,
                            shade: brandColor,
                            gradientToColors: [brandColor],
                        },
                    },
                    dataLabels: { enabled: false },
                    stroke: { width: 2 },
                    grid: { show: false, strokeDashArray: 4, padding: { left: 2, right: 2, top: 0 } },
                    series: [{ name: "Inquiries", data: data, color: brandColor }],
                    xaxis: {
                        categories: cats,
                        labels: { show: false },
                        axisBorder: { show: false },
                        axisTicks: { show: false },
                    },
                    yaxis: { show: false },
                };
            }

            let areaChart = null;
            // days > 0 => rolling window ending today; days === 0 => full month (all daysInMonth)
            function getFilteredData(days) {
                let startDay = 1;
                let endDay = days > 0 ? currentDay : daysInMonth;
                if (days > 0) {
                    startDay = Math.max(1, currentDay - days + 1);
                }
                const startIdx = startDay - 1;
                const endIdx = endDay;
                return {
                    cats: categories.slice(startIdx, endIdx),
                    data: seriesData.slice(startIdx, endIdx)
                };
            }
            function formatTotal(n) {
                if (n >= 1000) return (n / 1000).toFixed(1).replace(/\.0$/, '') + 'k';
                return String(n);
            }
            function updateInquiryHeader(days) {
                const elTotal = document.getElementById('inquiryTotal');
                const elRange = document.getElementById('inquiryRangeText');
                const elPercent = document.getElementById('inquiryPercent');
                const elBadge = document.getElementById('inquiryPercentBadge');
                if (!elTotal || !elRange) return;

                const filtered = getFilteredData(days);
                const total = filtered.data.reduce((acc, v) => acc + (v || 0), 0);
                elTotal.textContent = formatTotal(total);
                elRange.textContent = days > 0 ? `Inquiries last ${days} days` : 'Inquiries this month';

                if (elPercent && elBadge) {
                    const daysInRange = days > 0 ? Math.min(days, currentDay) : daysInMonth;
                    const expectedPct = daysInMonth > 0 ? (daysInRange / daysInMonth) * 100 : 100;
                    const actualPct = monthTotal > 0 ? (total / monthTotal) * 100 : 0;
                    const diffPct = actualPct - expectedPct;

                    elBadge.classList.remove('inquiry-trend-up', 'inquiry-trend-down', 'inquiry-trend-same');
                    if (diffPct > 0) {
                        elBadge.classList.add('inquiry-trend-up');
                        elPercent.textContent = '+' + Math.round(diffPct) + '%';
                    } else if (diffPct < 0) {
                        elBadge.classList.add('inquiry-trend-down');
                        elPercent.textContent = Math.round(diffPct) + '%';
                    } else {
                        elBadge.classList.add('inquiry-trend-same');
                        elPercent.textContent = '0%';
                    }
                }
            }
            if (document.getElementById("area-chart") && typeof ApexCharts !== 'undefined') {
                const initial = getFilteredData(7);
                areaChart = new ApexCharts(document.getElementById("area-chart"), buildChartOptions(initial.cats, initial.data));
                areaChart.render();
                updateInquiryHeader(7);

                document.querySelectorAll('.inquiry-filter-btn').forEach(function(btn) {
                    btn.addEventListener('click', function() {
                        document.querySelectorAll('.inquiry-filter-btn').forEach(function(b) { b.classList.remove('active'); });
                        this.classList.add('active');
                        const days = parseInt(this.dataset.days, 10);
                        const filtered = getFilteredData(days);
                        areaChart.updateOptions(buildChartOptions(filtered.cats, filtered.data));
                        updateInquiryHeader(days);
                    });
                });
            }

            // Product Conversion bar chart
            const el = document.getElementById('productConversionChart');
            if (!el) return;

            const labels = @json(array_column($productConversion, 'label'));
            const dataValues = @json(array_column($productConversion, 'count'));

            const data = {
                labels: labels,
                datasets: [{
                    axis: 'y',
                    label: 'Closed cases',
                    data: dataValues,
                    backgroundColor: 'rgba(127, 90, 240, 0.9)',
                    borderColor: 'rgb(109, 61, 245)',
                    borderWidth: 1,
                    borderRadius: 999,
                    barThickness: 32,
                }]
            };

            const config = {
                type: 'bar',
                data: data,
                options: {
                    indexAxis: 'y',
                    maintainAspectRatio: false,
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false,
                        },
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            grid: {
                                display: false,
                            },
                        },
                        y: {
                            grid: {
                                display: false,
                            },
                            ticks: {
                                padding: 4,
                            },
                        },
                    },
                },
            };

            new Chart(el.getContext('2d'), config);
        });
    </script>
@endpush
