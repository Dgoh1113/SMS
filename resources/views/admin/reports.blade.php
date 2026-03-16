@extends('layouts.app')
@section('title', 'Report - Monthly Performance Analytics')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/report_monthly_performance_analytics.css') }}">
@endpush
@section('content')
<div class="reports-page">
<div class="reports-page-layout">
<header class="dashboard-header">
    <div class="reports-tabs-row">
        <nav class="reports-tabs-nav" aria-label="Report views">
            <a href="{{ route('admin.reports') }}"
               class="reports-tab-link {{ request()->routeIs('admin.reports') ? 'is-active' : '' }}">
                Monthly Performance
            </a>
            <a href="{{ route('admin.reports.v2') }}"
               class="reports-tab-link {{ request()->routeIs('admin.reports.v2') ? 'is-active' : '' }}">
                Dealer Sales Overtime
            </a>
            <a href="{{ route('admin.reports.revenue') }}"
               class="reports-tab-link {{ request()->routeIs('admin.reports.revenue') ? 'is-active' : '' }}">
                Dealer Revenue Production
            </a>
        </nav>
    </div>
</header>
<div class="reports-period-row">
    <form method="get" class="reports-period-form reports-period-form-compact">
        @php
            $months = [
                1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
            ];
        @endphp
        <select name="month" class="reports-period-select">
            @foreach ($months as $m => $label)
                <option value="{{ $m }}" {{ (int) ($selectedMonth ?? now()->format('n')) === (int) $m ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        <select name="year" class="reports-period-select">
            @foreach (($yearOptions ?? []) as $y)
                <option value="{{ $y }}" {{ (int) ($selectedYear ?? now()->format('Y')) === (int) $y ? 'selected' : '' }}>
                    {{ $y }}
                </option>
            @endforeach
        </select>
        <button type="submit" class="reports-period-apply">Apply</button>
    </form>
</div>

@php
    $totalLeads = array_sum($leadStatus);
    $totalActivities = array_sum($activityStatus);
    $totalPayouts = array_sum($payoutStatus);
    $totalStatus = max($totalActivities, 1);
    $statusColors = [
        'Created' => '#ff4d4f',
        'Pending' => '#fecaca',
        'FollowUp' => '#dc2626',
        'Demo' => '#fef08a',
        'Confirmed' => '#eab308',
        'Completed' => '#bbf7d0',
        'Failed' => '#434343',
        'reward' => '#22c55e',
    ];
@endphp

@php
    $metricCards = [
        [
            'key' => 'unassigned',
            'label' => 'UNASSIGNED',
            'value' => $unassignedLeads,
            'link' => route('admin.inquiries'),
            'link_aria' => 'View unassigned inquiries'
        ],
        ['key' => 'Pending', 'label' => 'PENDING', 'value' => $activityStatus['Pending'] ?? 0, 'dealer' => true],
        ['key' => 'FollowUp', 'label' => 'FOLLOW-UP', 'value' => $activityStatus['FollowUp'] ?? 0, 'dealer' => true],
        ['key' => 'Demo', 'label' => 'DEMO', 'value' => $activityStatus['Demo'] ?? 0, 'dealer' => true],
        ['key' => 'Confirmed', 'label' => 'CONFIRMED', 'value' => $activityStatus['Confirmed'] ?? 0, 'dealer' => true],
        [
            'key' => 'CompletedPendingReward',
            'label' => 'COMPLETED AND PENDING REWARD',
            'value' => ($activityStatus['Completed'] ?? 0) + ($payoutStatus['Pending'] ?? 0),
            'link' => route('admin.rewards', ['tab' => 'completed']),
            'link_aria' => 'View payouts pending reward'
        ],
        [
            'key' => 'Rewarded',
            'label' => 'REWARDED',
            'value' => $activityStatus['reward'] ?? 0,
            'link' => route('admin.rewards', ['tab' => 'rewarded']),
            'link_aria' => 'View payouts rewarded'
        ],
    ];
@endphp
<section class="dashboard-metrics-grid">
    @foreach ($metricCards as $card)
    @php
        $pct = $metricPercent[$card['key']] ?? 0;
        $trend = $pct > 0 ? 'up' : ($pct < 0 ? 'down' : 'same');
    @endphp
    @if (!empty($card['link']))
    <a href="{{ $card['link'] }}" class="dashboard-metric-card report-metric-link" aria-label="{{ $card['link_aria'] ?? $card['label'] }}">
        <span class="report-metric-chevron" aria-hidden="true">&gt;</span>
    @else
    <div class="dashboard-metric-card">
    @endif
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
        <div class="dashboard-metric-btn-wrap"></div>
    @if (!empty($card['link']))
        </a>
    @else
        </div>
    @endif
    @endforeach
</section>

<section class="dashboard-panels-two-column">
    <div class="dashboard-panel inquiry-chart-panel">
        <div class="dashboard-panel-header">
            <div class="dashboard-panel-title" id="inquiryRangeText">Inquiries this month</div>
            <div id="inquiryPercentBadge" class="flex items-center inquiry-trend-badge font-medium text-center inquiry-trend-same">
                <svg class="inquiry-percent-arrow inquiry-percent-arrow-up" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v13m0-13 4 4m-4-4-4 4"/></svg>
                <svg class="inquiry-percent-arrow inquiry-percent-arrow-down" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19V5m0 0-4 4m4-4 4 4"/></svg>
                <span id="inquiryPercent" class="inquiry-percent">0%</span>
                <span class="inquiry-percent-suffix">vs last month</span>
            </div>
        </div>
        <div class="dashboard-panel-body">
            @if (count($inquiryTrend) === 0)
                <p class="text-muted">No leads created this month yet.</p>
            @else
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
            <div class="dashboard-panel-title">Product Conversion Rate</div>
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
            const inquiryTrendPercentChange = @json($inquiryTrendPercentChange ?? 0);
            const monthName = @json($selectedMonthName ?? now()->format('F'));
            const selectedYear = @json($selectedYear ?? now()->format('Y'));
            const daysInMonth = @json($selectedDaysInMonth ?? now()->daysInMonth);
            const currentDay = @json($selectedDaysInMonth ?? (int) now()->daysInMonth);

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
            const brandColor = '#7f5af0';
            const toRgba = function(hex, alpha) {
                if (!hex) return null;
                const h = hex.replace('#', '').trim();
                if (!/^[0-9a-fA-F]{3,6}$/.test(h)) return null;
                const full = h.length === 3 ? h.split('').map((c) => c + c).join('') : h;
                if (full.length !== 6) return null;
                const r = parseInt(full.substring(0, 2), 16);
                const g = parseInt(full.substring(2, 4), 16);
                const b = parseInt(full.substring(4, 6), 16);
                return `rgba(${r}, ${g}, ${b}, ${alpha})`;
            };
            const columnColor = toRgba(brandColor, 0.32) || brandColor;

            function buildChartOptions(cats, data) {
                return {
                    chart: {
                        height: "100%",
                        maxWidth: "100%",
                        type: "line",
                        fontFamily: "Inter, sans-serif",
                        dropShadow: { enabled: false },
                        toolbar: { show: false },
                        zoom: { enabled: false },
                    },
                    tooltip: {
                        enabled: true,
                        shared: true,
                        intersect: false,
                        y: {
                            formatter: function (val) { return (val || 0) + ' inquiries'; }
                        }
                    },
                    plotOptions: {
                        bar: {
                            columnWidth: "55%",
                            borderRadius: 6,
                        }
                    },
                    fill: { opacity: [0.5, 1] },
                    dataLabels: { enabled: false },
                    stroke: { width: [0, 2], curve: 'smooth' },
                    markers: { size: [0, 3], hover: { size: 4 } },
                    grid: {
                        show: true,
                        borderColor: 'rgba(148, 163, 184, 0.25)',
                        strokeDashArray: 4,
                        padding: { left: 6, right: 6, top: 0, bottom: 0 }
                    },
                    series: [
                        { name: "Inquiries", type: "column", data: data },
                        { name: "Trend", type: "line", data: data },
                    ],
                    colors: [columnColor, brandColor],
                    xaxis: {
                        categories: cats,
                        tickAmount: Math.min(8, cats.length),
                        labels: {
                            show: true,
                            rotate: -35,
                            style: { colors: '#94a3b8', fontSize: '10px' },
                        },
                        axisBorder: { show: true, color: 'rgba(148, 163, 184, 0.35)' },
                        axisTicks: { show: true, color: 'rgba(148, 163, 184, 0.35)' },
                    },
                    yaxis: {
                        show: true,
                        labels: {
                            style: { colors: '#94a3b8', fontSize: '11px' },
                            formatter: function (val) { return Math.round(val); }
                        }
                    },
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
                const elRange = document.getElementById('inquiryRangeText');
                const elPercent = document.getElementById('inquiryPercent');
                const elBadge = document.getElementById('inquiryPercentBadge');
                if (!elRange) return;

                const filtered = getFilteredData(days);
                const total = filtered.data.reduce((acc, v) => acc + (v || 0), 0);
                elRange.textContent = days > 0 ? `Inquiries last ${days} days` : `Inquiries in ${monthName} ${selectedYear}`;

                if (elPercent && elBadge) {
                    const diffPct = Number(inquiryTrendPercentChange) || 0;
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
                    elBadge.title = 'Vs last month';
                }
            }
            if (document.getElementById("area-chart") && typeof ApexCharts !== 'undefined') {
                const initial = getFilteredData(0);
                areaChart = new ApexCharts(document.getElementById("area-chart"), buildChartOptions(initial.cats, initial.data));
                areaChart.render();
                updateInquiryHeader(0);
            }

            // Product Conversion bar chart
            const el = document.getElementById('productConversionChart');
            if (!el) return;

            const labels = @json(array_column($productConversion, 'label'));
            const dataValues = @json(array_column($productConversion, 'count'));

            const productColorMap = {
                'SQL ACCOUNT': '#662d91',
                'SQL PAYROLL': '#cd2027',
                'SQL PRODUCTION': '#662d91',
                'MOBILE SALES': '#662d91',
                'SQL ECOMMERCE': 'orange',
                'SQL EBI WELLNESS POS': '#8cc63f',
                'SQL X SUDUAI': '#111827',
                'SQL X-STORE': 'orange',
                'SQL VISION': '#c4b5fd',
                'SQL HRMS': '#cd2027',
                'OTHERS': '#64748b',
            };
            const getProductColor = (label) => {
                const key = String(label || '').trim().toUpperCase();
                return productColorMap[key] || '#64748b';
            };
            const barColors = labels.map(l => getProductColor(l));

            const yLabelBackgroundPlugin = {
                id: 'yLabelBackground',
                beforeDraw(chart, args, opts) {
                    const yScale = chart.scales?.y;
                    const labels = chart.data?.labels || [];
                    if (!yScale || labels.length === 0) return;
                    const ctx = chart.ctx;
                    const tickPadding = yScale.options?.ticks?.padding ?? 0;
                    const toFont = (Chart.helpers && Chart.helpers.toFont)
                        ? Chart.helpers.toFont
                        : (fontSpec) => {
                            const size = fontSpec?.size || 12;
                            const family = fontSpec?.family || 'sans-serif';
                            return { string: `${size}px ${family}`, lineHeight: fontSpec?.lineHeight || size };
                        };
                    const font = toFont(yScale.options?.ticks?.font);

                    ctx.save();
                    ctx.font = font.string;
                    ctx.textBaseline = 'middle';
                    const padX = opts?.paddingX ?? 6;
                    const padY = opts?.paddingY ?? 3;
                    const alpha = opts?.alpha ?? 0.2;
                    const xRight = yScale.right - tickPadding;

                    labels.forEach((label, i) => {
                        const y = yScale.getPixelForTick(i);
                        if (!Number.isFinite(y)) return;
                        const text = String(label);
                        const textWidth = ctx.measureText(text).width;
                        const textHeight = font.lineHeight || 12;
                        const w = textWidth + padX * 2;
                        const h = textHeight + padY * 2;
                        const x = xRight - w;
                        const yTop = y - h / 2;
                        const r = Math.min(6, h / 2);
                        const color = barColors[i] || '#e5e7eb';

                        ctx.globalAlpha = alpha;
                        ctx.fillStyle = color;
                        ctx.beginPath();
                        ctx.moveTo(x + r, yTop);
                        ctx.lineTo(x + w - r, yTop);
                        ctx.quadraticCurveTo(x + w, yTop, x + w, yTop + r);
                        ctx.lineTo(x + w, yTop + h - r);
                        ctx.quadraticCurveTo(x + w, yTop + h, x + w - r, yTop + h);
                        ctx.lineTo(x + r, yTop + h);
                        ctx.quadraticCurveTo(x, yTop + h, x, yTop + h - r);
                        ctx.lineTo(x, yTop + r);
                        ctx.quadraticCurveTo(x, yTop, x + r, yTop);
                        ctx.closePath();
                        ctx.fill();
                    });

                    ctx.restore();
                }
            };

            const data = {
                labels: labels,
                datasets: [{
                    axis: 'y',
                    label: 'Product conversions',
                    data: dataValues,
                    backgroundColor: (ctx) => barColors[ctx.dataIndex] || '#64748b',
                    borderColor: (ctx) => barColors[ctx.dataIndex] || '#64748b',
                    borderWidth: 1,
                    borderRadius: 999,
                    barThickness: 30,
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
                        yLabelBackground: {
                            colors: barColors,
                            alpha: 0.22,
                            paddingX: 6,
                            paddingY: 3,
                        },
                        legend: {
                            display: false,
                        },
                        tooltip: {
                            displayColors: false,
                            backgroundColor: 'rgba(15, 23, 42, 0.92)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            padding: 10,
                            cornerRadius: 10,
                            callbacks: {
                                label: function(ctx) {
                                    const val = ctx.parsed && typeof ctx.parsed.x === 'number' ? Math.round(ctx.parsed.x) : 0;
                                    return 'Count: ' + val;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(148, 163, 184, 0.20)',
                                drawBorder: false,
                            },
                            ticks: {
                                color: '#64748b',
                                stepSize: 1,
                                callback: function(v) { return Math.round(v); }
                            }
                        },
                        y: {
                            grid: {
                                display: false,
                            },
                            ticks: {
                                padding: 6,
                                color: '#0f172a',
                            },
                        },
                    },
                },
            };

            new Chart(el.getContext('2d'), config);
        });
    </script>
@endpush
