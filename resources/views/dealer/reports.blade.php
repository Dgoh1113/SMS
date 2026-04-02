@extends('layouts.app')
@section('title', 'Reports – SQL LMS Dealer Console')
@push('styles')
<style>
    .dealer-reports-page .dashboard-panels-two-column { display:grid; grid-template-columns:minmax(0,1.35fr) minmax(0,1fr); gap:20px; min-width:0; }
    .dealer-reports-page .dashboard-panels-two-column > *, .dealer-reports-page .reports-product-section { min-width:0; }
    .dealer-reports-page .reports-inquiry-section, .dealer-reports-page .dealer-reports-status-section, .dealer-reports-page .reports-product-section { border:1px solid #e8ecf5; border-radius:20px; box-shadow:0 14px 28px rgba(15,23,42,.05); overflow:hidden; background:#fff; }
    .dealer-reports-page .reports-inquiry-section .dashboard-panel-header, .dealer-reports-page .dealer-reports-status-section .dashboard-panel-header, .dealer-reports-page .reports-product-section .dashboard-panel-header { display:flex; align-items:flex-start; justify-content:space-between; gap:16px; padding:20px 22px 12px; border-bottom:none; }
    .dealer-reports-page .reports-inquiry-section .dashboard-panel-body, .dealer-reports-page .dealer-reports-status-section .dashboard-panel-body, .dealer-reports-page .reports-product-section .dashboard-panel-body { padding:0 22px 22px; }
    .dealer-reports-page .reports-inquiry-heading, .dealer-reports-page .reports-product-heading, .dealer-reports-page .dealer-reports-status-heading { display:flex; flex-direction:column; gap:4px; }
    .dealer-reports-page .reports-inquiry-heading .dashboard-panel-title, .dealer-reports-page .reports-product-heading .dashboard-panel-title, .dealer-reports-page .dealer-reports-status-heading .dashboard-panel-title { margin:0; font-size:18px; font-weight:700; line-height:1.2; color:#0f172a; letter-spacing:-.01em; }
    .dealer-reports-page .reports-inquiry-subtitle, .dealer-reports-page .reports-product-subtitle, .dealer-reports-page .dealer-reports-status-subtitle { font-size:12px; font-weight:500; color:#64748b; line-height:1.4; }
    .dealer-reports-page .reports-inquiry-meta { display:inline-flex; align-items:center; justify-content:flex-end; flex-wrap:wrap; gap:10px; }
    .dealer-reports-page .reports-inquiry-chip, .dealer-reports-page .reports-product-scale-chip { display:inline-flex; align-items:center; gap:8px; padding:7px 12px; border-radius:999px; border:1px solid #e2e8f0; background:#fff; color:#475569; box-shadow:0 1px 2px rgba(15,23,42,.04); }
    .dealer-reports-page .reports-inquiry-chip-label { font-size:10px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:#94a3b8; }
    .dealer-reports-page .reports-inquiry-chip-value { font-size:14px; font-weight:700; color:#0f172a; }
    .dealer-reports-page .dealer-reports-card, .dealer-reports-page .reports-product-card, .dealer-reports-page .dealer-reports-status-card { border:1px solid #eef2f8; border-radius:18px; background:linear-gradient(180deg,#fff 0%,#fbfcff 100%); padding:14px 16px 12px; }
    .dealer-reports-page .reports-inquiry-section .dealer-reports-card { padding:12px 14px 6px; }
    .dealer-reports-page .dealer-reports-chart-wrapper, .dealer-reports-page .reports-product-chart-wrapper { position:relative; width:100%; background:linear-gradient(180deg,#fff 0%,#f8fafc 100%); border-radius:10px; border:1px solid #e5e7eb; padding:10px; box-sizing:border-box; }
    .dealer-reports-page .reports-inquiry-section .dealer-reports-chart-wrapper { background:#fff; border:0; border-radius:0; padding:0 2px; }
    .dealer-reports-page .dealer-reports-chart-wrapper canvas, .dealer-reports-page .reports-product-chart-wrapper canvas { display:block; width:100% !important; height:100% !important; }
    .dealer-reports-page .dealer-reports-chart-fallback, .dealer-reports-page .reports-product-chart-fallback { display:none; margin:0; font-size:13px; font-weight:500; color:#64748b; text-align:center; }
    .dealer-reports-page .dealer-reports-chart-wrapper.is-error, .dealer-reports-page .reports-product-chart-wrapper.is-error { height:auto !important; min-height:0; padding:0; border:0; background:transparent; }
    .dealer-reports-page .dealer-reports-chart-wrapper.is-error canvas, .dealer-reports-page .reports-product-chart-wrapper.is-error canvas { display:none; }
    .dealer-reports-page .dealer-reports-chart-wrapper.is-error .dealer-reports-chart-fallback, .dealer-reports-page .reports-product-chart-wrapper.is-error .reports-product-chart-fallback { display:block; padding:8px 0 4px; }
    .dealer-reports-page .dealer-reports-empty, .dealer-reports-page .reports-product-empty { margin:0; padding:20px 8px; font-size:14px; color:#64748b; text-align:center; }
    .dealer-reports-page .reports-product-scale { display:inline-flex; align-items:center; flex-wrap:wrap; gap:8px; }
    .dealer-reports-page .reports-product-scale-chip { gap:6px; padding:5px 10px; font-size:11px; font-weight:600; }
    .dealer-reports-page .reports-product-scale-dot { width:8px; height:8px; border-radius:999px; flex-shrink:0; }
    .dealer-reports-page .reports-product-scale-dot--high { background:#22c55e; } .dealer-reports-page .reports-product-scale-dot--medium { background:#f59e0b; } .dealer-reports-page .reports-product-scale-dot--low { background:#ef4444; }
    .dealer-reports-page .report-status-body { display:flex; flex-direction:column; align-items:center; gap:16px; }
    .dealer-reports-page .report-donut-wrapper { display:flex; justify-content:center; width:100%; }
    .dealer-reports-page .report-donut { width:196px; height:196px; border-radius:50%; position:relative; border:1px solid #e5e7eb; box-shadow:0 14px 30px rgba(15,23,42,.08); }
    .dealer-reports-page .report-donut-center { position:absolute; inset:24px; border-radius:50%; background:#fff; display:flex; flex-direction:column; align-items:center; justify-content:center; box-shadow:0 10px 30px rgba(15,23,42,.08); }
    .dealer-reports-page .report-donut-total { font-size:22px; font-weight:700; color:#0f172a; } .dealer-reports-page .report-donut-label { font-size:12px; color:#64748b; }
    .dealer-reports-page .report-legend { list-style:none; margin:0; padding:0 8px 4px; display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:6px 16px; font-size:12px; width:100%; }
    .dealer-reports-page .report-legend li { display:flex; align-items:center; justify-content:space-between; gap:8px; min-width:0; }
    .dealer-reports-page .report-legend-color { width:10px; height:10px; border-radius:999px; flex-shrink:0; }
    .dealer-reports-page .report-legend-label { flex:1; color:#475569; } .dealer-reports-page .report-legend-value { font-weight:600; color:#0f172a; }
    @media (max-width:1200px) { .dealer-reports-page .dashboard-panels-two-column { grid-template-columns:minmax(0,1fr); } }
    @media (max-width:768px) {
        .dealer-reports-page .reports-inquiry-section .dashboard-panel-header, .dealer-reports-page .dealer-reports-status-section .dashboard-panel-header, .dealer-reports-page .reports-product-section .dashboard-panel-header { padding:16px 16px 10px; flex-direction:column; align-items:flex-start; }
        .dealer-reports-page .reports-inquiry-section .dashboard-panel-body, .dealer-reports-page .dealer-reports-status-section .dashboard-panel-body, .dealer-reports-page .reports-product-section .dashboard-panel-body { padding:0 16px 16px; }
        .dealer-reports-page .dealer-reports-card, .dealer-reports-page .reports-product-card, .dealer-reports-page .dealer-reports-status-card { padding:12px; border-radius:14px; }
        .dealer-reports-page .reports-inquiry-heading .dashboard-panel-title, .dealer-reports-page .reports-product-heading .dashboard-panel-title, .dealer-reports-page .dealer-reports-status-heading .dashboard-panel-title { font-size:16px; }
        .dealer-reports-page .reports-inquiry-meta { justify-content:flex-start; }
        .dealer-reports-page .report-legend { grid-template-columns:1fr; }
    }
</style>
@endpush
@section('content')
<div class="dashboard-content reports-page dealer-reports-page">
    <header class="reports-header reports-header--dealer">
        <div class="reports-header-actions">
            <form method="get" action="{{ route('dealer.reports') }}" class="reports-period-form reports-period-form-compact reports-period-form--dealer" id="reportsPeriodForm">
                <select name="period" id="reportsPeriodSelect" class="reports-period-select reports-period-select--dealer" aria-label="Report period">
                    <option value="week" {{ ($period ?? 'month') === 'week' ? 'selected' : '' }}>Current Week</option>
                    <option value="month" {{ ($period ?? 'month') === 'month' ? 'selected' : '' }}>Current Month</option>
                    <option value="year" {{ ($period ?? 'month') === 'year' ? 'selected' : '' }}>Current Year</option>
                    <option value="range" {{ ($period ?? 'month') === 'range' ? 'selected' : '' }}>Range</option>
                </select>
                <div class="reports-period-range-inline{{ ($period ?? 'month') === 'range' ? '' : ' is-hidden' }}" id="reportsRangeInline">
                    <input type="date" name="from" id="reportsRangeFrom" class="reports-period-select reports-period-select--dealer reports-period-date" value="{{ $from ?? '' }}" aria-label="From date">
                    <input type="date" name="to" id="reportsRangeTo" class="reports-period-select reports-period-select--dealer reports-period-date" value="{{ $to ?? '' }}" aria-label="To date">
                </div>
                <button type="submit" class="reports-period-apply reports-period-apply--dealer">Apply</button>
            </form>
        </div>
    </header>

    {{-- Metric Cards --}}
    <section class="reports-metrics">
        <div class="reports-metric-card">
            <div class="reports-metric-icon reports-metric-icon-pending"><i class="bi bi-file-earmark"></i></div>
            <div class="reports-metric-value">{{ $statusCounts['PENDING'] ?? 0 }}</div>
            <div class="reports-metric-label">PENDING</div>
        </div>
        <div class="reports-metric-card">
            <div class="reports-metric-icon reports-metric-icon-followup"><i class="bi bi-calendar-event"></i></div>
            <div class="reports-metric-value">{{ $statusCounts['FOLLOW UP'] ?? 0 }}</div>
            <div class="reports-metric-label">FOLLOW UP</div>
        </div>
        <div class="reports-metric-card">
            <div class="reports-metric-icon reports-metric-icon-demo"><i class="bi bi-person-video2"></i></div>
            <div class="reports-metric-value">{{ $statusCounts['DEMO'] ?? 0 }}</div>
            <div class="reports-metric-label">DEMO</div>
        </div>
        <div class="reports-metric-card">
            <div class="reports-metric-icon reports-metric-icon-confirmed"><i class="bi bi-check-circle"></i></div>
            <div class="reports-metric-value">{{ $statusCounts['CONFIRMED'] ?? 0 }}</div>
            <div class="reports-metric-label">CONFIRMED</div>
        </div>
        <div class="reports-metric-card">
            <div class="reports-metric-icon reports-metric-icon-completed"><i class="bi bi-box-seam"></i></div>
            <div class="reports-metric-value">{{ $statusCounts['COMPLETED'] ?? 0 }}</div>
            <div class="reports-metric-label">COMPLETED</div>
        </div>
        <div class="reports-metric-card">
            <div class="reports-metric-icon reports-metric-icon-reward"><i class="bi bi-gift"></i></div>
            <div class="reports-metric-value">{{ $statusCounts['REWARDED'] ?? 0 }}</div>
            <div class="reports-metric-label">REWARDED</div>
        </div>
    </section>

    @php
        $statusReportData = [
            ['label' => 'Pending', 'value' => (int) ($statusCounts['PENDING'] ?? 0), 'color' => '#f97316'],
            ['label' => 'Follow Up', 'value' => (int) ($statusCounts['FOLLOW UP'] ?? 0), 'color' => '#f59e0b'],
            ['label' => 'Demo', 'value' => (int) ($statusCounts['DEMO'] ?? 0), 'color' => '#eab308'],
            ['label' => 'Confirmed', 'value' => (int) ($statusCounts['CONFIRMED'] ?? 0), 'color' => '#84cc16'],
            ['label' => 'Completed', 'value' => (int) ($statusCounts['COMPLETED'] ?? 0), 'color' => '#22c55e'],
            ['label' => 'Rewarded', 'value' => (int) ($statusCounts['REWARDED'] ?? 0), 'color' => '#15803d'],
        ];
        $totalStatus = max(array_sum(array_column($statusReportData, 'value')), 1);
        $statusSegments = [];
        $statusOffset = 0;
        foreach ($statusReportData as $item) {
            $value = (int) ($item['value'] ?? 0);
            if ($value <= 0) {
                continue;
            }
            $percent = $value / $totalStatus * 100;
            $statusSegments[] = [
                'from' => $statusOffset,
                'to' => $statusOffset + $percent,
                'color' => $item['color'] ?? '#e5e7eb',
            ];
            $statusOffset += $percent;
        }
        $statusGradient = collect($statusSegments)->map(function ($segment) {
            return $segment['color'] . ' ' . $segment['from'] . '% ' . $segment['to'] . '%';
        })->implode(', ');
        $productNames = [
            1 => 'SQL Account',
            2 => 'SQL Payroll',
            3 => 'SQL Production',
            4 => 'Mobile Sales',
            5 => 'SQL Ecommerce',
            6 => 'SQL EBI Wellness POS',
            7 => 'SQL X Suduai',
            8 => 'SQL X-Store',
            9 => 'SQL Vision',
            10 => 'SQL HRMS',
            11 => 'Others',
        ];
        $productCounts = $productCounts ?? array_fill(0, 11, 0);
        $productConversionDisplay = collect(range(1, 11))
            ->map(function ($productId) use ($productNames, $productCounts) {
                return [
                    'label' => (string) ($productNames[$productId] ?? 'Product'),
                    'count' => (int) ($productCounts[$productId - 1] ?? 0),
                ];
            })
            ->sort(function ($a, $b) {
                $aIsOthers = strtoupper(trim((string) ($a['label'] ?? ''))) === 'OTHERS';
                $bIsOthers = strtoupper(trim((string) ($b['label'] ?? ''))) === 'OTHERS';
                if ($aIsOthers && !$bIsOthers) return 1;
                if (!$aIsOthers && $bIsOthers) return -1;
                $countCompare = ($b['count'] ?? 0) <=> ($a['count'] ?? 0);
                if ($countCompare !== 0) return $countCompare;
                return strcasecmp((string) ($a['label'] ?? ''), (string) ($b['label'] ?? ''));
            })
            ->values();
        $hasInquiryTrendData = (int) ($totalInquiry ?? 0) > 0 && collect($inquiryTrendData ?? [])->sum() > 0;
        $productChartHeightPx = max(168, $productConversionDisplay->count() * 30 + 44);
    @endphp

    <section class="dashboard-panels-two-column">
        <section class="dashboard-panel reports-inquiry-section">
            <div class="dashboard-panel-header">
                <div class="reports-inquiry-heading">
                    <div class="dashboard-panel-title">Inquiry Trends</div>
                    <div class="reports-inquiry-subtitle">Inquiries for {{ $periodLabel ?? 'Current Month' }}</div>
                </div>
                <div class="reports-inquiry-meta">
                    <span class="reports-inquiry-chip">
                        <span class="reports-inquiry-chip-label">Total</span>
                        <span class="reports-inquiry-chip-value">{{ number_format($totalInquiry ?? 0) }}</span>
                    </span>
                </div>
            </div>
            <div class="dashboard-panel-body">
                <div class="dealer-reports-card">
                    @if (!$hasInquiryTrendData)
                        <p class="dealer-reports-empty">No leads created in this period yet.</p>
                    @else
                        <div class="dealer-reports-chart-wrapper" style="height: 336px;">
                            <p class="dealer-reports-chart-fallback">Unable to load inquiry trend chart.</p>
                            <canvas id="dealerInquiryTrendChart"></canvas>
                        </div>
                    @endif
                </div>
            </div>
        </section>

        <section class="dashboard-panel dealer-reports-status-section">
            <div class="dashboard-panel-header">
                <div class="dealer-reports-status-heading">
                    <div class="dashboard-panel-title">Status Report</div>
                    <div class="dealer-reports-status-subtitle">Current status distribution for {{ $periodLabel ?? 'Current Month' }}</div>
                </div>
            </div>
            <div class="dashboard-panel-body report-status-body">
                <div class="dealer-reports-status-card">
                    <div class="report-donut-wrapper">
                        <div class="report-donut" style="background: conic-gradient({{ $statusGradient ?: '#e5e7eb 0 100%' }});">
                            <div class="report-donut-center">
                                <div class="report-donut-total">{{ array_sum(array_column($statusReportData, 'value')) }}</div>
                                <div class="report-donut-label">Activities</div>
                            </div>
                        </div>
                    </div>
                </div>
                <ul class="report-legend">
                    @foreach ($statusReportData as $item)
                        <li>
                            <span class="report-legend-color" style="background-color: {{ $item['color'] ?? '#e5e7eb' }}"></span>
                            <span class="report-legend-label">{{ $item['label'] }}</span>
                            <span class="report-legend-value">{{ $item['value'] }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    </section>

    <section class="dashboard-panel dashboard-table-panel reports-product-section">
        <div class="dashboard-panel-header">
            <div class="reports-product-heading">
                <div class="dashboard-panel-title">Product Conversion Rate</div>
                <div class="reports-product-subtitle">Closed-case conversions by product for {{ $periodLabel ?? 'Current Month' }}</div>
            </div>
            <div class="reports-product-scale" aria-hidden="true">
                <span class="reports-product-scale-chip"><span class="reports-product-scale-dot reports-product-scale-dot--high"></span>High</span>
                <span class="reports-product-scale-chip"><span class="reports-product-scale-dot reports-product-scale-dot--medium"></span>Medium</span>
                <span class="reports-product-scale-chip"><span class="reports-product-scale-dot reports-product-scale-dot--low"></span>Low</span>
            </div>
        </div>
        <div class="dashboard-panel-body">
            <div class="reports-product-card">
                <div class="reports-product-chart-wrapper" style="height: {{ $productChartHeightPx }}px;">
                    <p class="reports-product-chart-fallback">Unable to load product conversion chart.</p>
                    <canvas id="dealerProductConversionChart"></canvas>
                </div>
            </div>
        </div>
    </section>

</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function initDealerReportsPage() {
    var periodSelect = document.getElementById('reportsPeriodSelect');
    var rangeWrap = document.getElementById('reportsRangeInline');
    var rangeFrom = document.getElementById('reportsRangeFrom');
    var rangeTo = document.getElementById('reportsRangeTo');
    var form = document.getElementById('reportsPeriodForm');

    function syncRangeInputs() {
        if (!periodSelect || !rangeWrap || !rangeFrom || !rangeTo) return;
        var isRange = periodSelect.value === 'range';
        rangeWrap.classList.toggle('is-hidden', !isRange);
        rangeFrom.disabled = !isRange;
        rangeTo.disabled = !isRange;
        rangeFrom.required = isRange;
        rangeTo.required = isRange;
        rangeTo.min = isRange ? (rangeFrom.value || '') : '';
    }

    if (periodSelect && rangeWrap && rangeFrom && rangeTo) {
        periodSelect.addEventListener('change', syncRangeInputs);
        rangeFrom.addEventListener('change', function() {
            rangeTo.min = rangeFrom.value || '';
        });
        syncRangeInputs();
    }

    if (form && periodSelect && rangeFrom && rangeTo) {
        form.addEventListener('submit', function(e) {
            if (periodSelect.value !== 'range') return;
            var from = rangeFrom.value;
            var to = rangeTo.value;
            if (!from || !to || from > to) {
                e.preventDefault();
                rangeFrom.focus();
            }
        });
    }

    function showChartFallback(wrapper, fallback, message) {
        if (wrapper) wrapper.classList.add('is-error');
        if (fallback) fallback.textContent = message;
    }

    if (typeof Chart === 'undefined') {
        var missingInquiryWrapper = document.querySelector('.dealer-reports-chart-wrapper');
        var missingInquiryFallback = missingInquiryWrapper ? missingInquiryWrapper.querySelector('.dealer-reports-chart-fallback') : null;
        showChartFallback(missingInquiryWrapper, missingInquiryFallback, 'Inquiry trend chart could not be loaded right now.');

        var missingProductWrapper = document.querySelector('.reports-product-chart-wrapper');
        var missingProductFallback = missingProductWrapper ? missingProductWrapper.querySelector('.reports-product-chart-fallback') : null;
        showChartFallback(missingProductWrapper, missingProductFallback, 'Product conversion chart could not be loaded right now.');
        return;
    }

    var inquiryCanvas = document.getElementById('dealerInquiryTrendChart');
    if (inquiryCanvas) {
        var inquiryWrapper = inquiryCanvas.closest('.dealer-reports-chart-wrapper');
        var inquiryFallback = inquiryWrapper ? inquiryWrapper.querySelector('.dealer-reports-chart-fallback') : null;

        try {
            var rawInquiryLabels = @json(array_values($trendLabels ?? []));
            var inquiryValues = @json(array_values($inquiryTrendData ?? []));
            var reportPeriod = @json($period ?? 'month');
            var currentMonthName = @json(now()->format('F'));
            var brandColor = '#7f5af0';
            var columnColor = 'rgba(127, 90, 240, 0.32)';
            var gridColor = 'rgba(148, 163, 184, 0.25)';
            var axisColor = 'rgba(148, 163, 184, 0.28)';
            var inquiryLabels = rawInquiryLabels.map(function(label) {
                return String(label || '').trim();
            });
            var tooltipLabels = rawInquiryLabels.map(function(label) {
                var normalized = String(label || '').trim();
                if (reportPeriod === 'month' && normalized && /^\d+$/.test(normalized)) {
                    return normalized + ' ' + currentMonthName;
                }
                return normalized;
            });
            var showAllDayTicks = reportPeriod === 'month';
            var maxTickCount = inquiryLabels.length > 24 ? 11 : (inquiryLabels.length > 14 ? 9 : (inquiryLabels.length > 7 ? 7 : inquiryLabels.length));
            var tickStep = inquiryLabels.length > 24 ? 3 : (inquiryLabels.length > 14 ? 2 : 1);
            var maxInquiryValue = inquiryValues.length ? Math.max.apply(null, inquiryValues) : 0;
            function clearInquiryHover(chart) {
                chart.setActiveElements([]);
                if (chart.tooltip) {
                    chart.tooltip.setActiveElements([], { x: 0, y: 0 });
                }
            }

            function getNearestDateIndex(chart, x) {
                var scale = chart.scales.x;
                var labels = chart.data.labels || [];
                if (!scale || !labels.length) {
                    return null;
                }

                var nearestIndex = 0;
                var smallestDistance = Infinity;
                for (var i = 0; i < labels.length; i++) {
                    var tickPixel = scale.getPixelForTick(i);
                    var distance = Math.abs(x - tickPixel);
                    if (distance < smallestDistance) {
                        smallestDistance = distance;
                        nearestIndex = i;
                    }
                }

                return nearestIndex;
            }

            var exactDateHover = {
                id: 'dealerInquiryExactDateHover',
                afterEvent: function(chart, args) {
                    var event = args.event;
                    var chartArea = chart.chartArea;
                    if (!event || !chartArea) {
                        return;
                    }

                    if (event.type === 'mouseout') {
                        clearInquiryHover(chart);
                        args.changed = true;
                        return;
                    }

                    if (event.type !== 'mousemove' && event.type !== 'click' && event.type !== 'touchmove' && event.type !== 'touchstart') {
                        return;
                    }

                    if (event.x < chartArea.left || event.x > chartArea.right || event.y < chartArea.top || event.y > chartArea.bottom) {
                        clearInquiryHover(chart);
                        args.changed = true;
                        return;
                    }

                    var nearestIndex = getNearestDateIndex(chart, event.x);
                    if (nearestIndex === null) {
                        clearInquiryHover(chart);
                        args.changed = true;
                        return;
                    }

                    var activeElements = chart.data.datasets.map(function(dataset, datasetIndex) {
                        return { datasetIndex: datasetIndex, index: nearestIndex };
                    });
                    var anchorX = chart.scales.x.getPixelForTick(nearestIndex);
                    var anchorY = chart.scales.y.getPixelForValue(Number(inquiryValues[nearestIndex] || 0));

                    chart.setActiveElements(activeElements);
                    if (chart.tooltip) {
                        chart.tooltip.setActiveElements(activeElements, { x: anchorX, y: anchorY });
                    }
                    args.changed = true;
                }
            };
            var activeDateGuide = {
                id: 'dealerInquiryHoverGuide',
                afterDatasetsDraw: function(chart) {
                    var tooltip = chart.tooltip;
                    if (!tooltip || tooltip.opacity === 0 || !tooltip.dataPoints || !tooltip.dataPoints.length) {
                        return;
                    }

                    var activePoint = tooltip.dataPoints[0] && tooltip.dataPoints[0].element ? tooltip.dataPoints[0].element : null;
                    if (!activePoint) {
                        return;
                    }

                    var ctx = chart.ctx;
                    var chartArea = chart.chartArea;
                    ctx.save();
                    ctx.beginPath();
                    ctx.setLineDash([4, 4]);
                    ctx.lineWidth = 1;
                    ctx.strokeStyle = 'rgba(148, 163, 184, 0.5)';
                    ctx.moveTo(activePoint.x, chartArea.top);
                    ctx.lineTo(activePoint.x, chartArea.bottom);
                    ctx.stroke();
                    ctx.restore();
                }
            };

            new Chart(inquiryCanvas.getContext('2d'), {
                plugins: [exactDateHover, activeDateGuide],
                data: {
                    labels: inquiryLabels,
                    datasets: [
                        {
                            type: 'bar',
                            label: 'Inquiries',
                            data: inquiryValues,
                            backgroundColor: columnColor,
                            borderColor: 'rgba(127, 90, 240, 0.18)',
                            borderWidth: 0,
                            borderRadius: 4,
                            borderSkipped: false,
                            barPercentage: 0.34,
                            categoryPercentage: 0.78,
                            maxBarThickness: 14,
                            pointStyle: 'circle'
                        },
                        {
                            type: 'line',
                            label: 'Trend',
                            data: inquiryValues,
                            borderColor: brandColor,
                            backgroundColor: brandColor,
                            borderWidth: 2.25,
                            pointBackgroundColor: brandColor,
                            pointBorderColor: brandColor,
                            pointBorderWidth: 0,
                            pointRadius: 0,
                            pointHoverRadius: 4,
                            pointHitRadius: 0,
                            pointStyle: 'circle',
                            cubicInterpolationMode: 'monotone',
                            tension: 0.42,
                            fill: false
                        }
                    ]
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    interaction: {
                        mode: 'index',
                        axis: 'x',
                        intersect: false
                    },
                    animation: {
                        duration: 260,
                        easing: 'easeOutCubic'
                    },
                    layout: {
                        padding: {
                            top: 4,
                            bottom: 4
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom',
                            align: 'center',
                            labels: {
                                usePointStyle: true,
                                pointStyle: 'circle',
                                boxWidth: 9,
                                boxHeight: 9,
                                padding: 18,
                                color: '#334155',
                                font: {
                                    size: 12,
                                    weight: '500'
                                }
                            }
                        },
                        tooltip: {
                            displayColors: true,
                            usePointStyle: true,
                            backgroundColor: 'rgba(15, 23, 42, 0.94)',
                            titleColor: '#ffffff',
                            bodyColor: '#ffffff',
                            padding: 12,
                            cornerRadius: 10,
                            callbacks: {
                                title: function(items) {
                                    if (!items || !items.length) {
                                        return '';
                                    }
                                    var item = items[0];
                                    return tooltipLabels[item.dataIndex] || item.label || '';
                                },
                                label: function(context) {
                                    var value = typeof context.parsed.y === 'number' ? context.parsed.y : 0;
                                    return context.dataset.label + ': ' + Math.round(value);
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false,
                                drawTicks: true,
                                tickLength: 4,
                                color: axisColor
                            },
                            border: {
                                display: true,
                                color: axisColor
                            },
                            ticks: {
                                color: '#8b95b5',
                                padding: 4,
                                font: {
                                    size: showAllDayTicks ? 8 : (inquiryLabels.length > 10 ? 10 : 11),
                                    weight: '500'
                                },
                                autoSkip: !showAllDayTicks,
                                maxTicksLimit: showAllDayTicks ? inquiryLabels.length : maxTickCount,
                                callback: function(value, index) {
                                    var label = this.getLabelForValue(value);
                                    if (showAllDayTicks) {
                                        return label;
                                    }
                                    if (inquiryLabels.length <= maxTickCount) {
                                        return label;
                                    }
                                    if (index === 0 || index === inquiryLabels.length - 1 || index % tickStep === 0) {
                                        return label;
                                    }
                                    return '';
                                },
                                maxRotation: showAllDayTicks ? 50 : (inquiryLabels.length > 7 ? 35 : 0),
                                minRotation: showAllDayTicks ? 50 : (inquiryLabels.length > 7 ? 35 : 0)
                            }
                        },
                        y: {
                            beginAtZero: true,
                            suggestedMax: maxInquiryValue > 0 ? Math.max(maxInquiryValue + 1, Math.ceil(maxInquiryValue * 1.3)) : 1,
                            grid: {
                                color: gridColor,
                                borderDash: [4, 4],
                                drawBorder: false,
                                drawTicks: false
                            },
                            border: {
                                display: false
                            },
                            ticks: {
                                color: '#8b95b5',
                                padding: 6,
                                font: {
                                    size: 10,
                                    weight: '500'
                                },
                                stepSize: maxInquiryValue <= 6 ? 1 : undefined,
                                callback: function(value) {
                                    return Math.round(value);
                                }
                            }
                        }
                    }
                }
            });

            if (inquiryWrapper) inquiryWrapper.classList.remove('is-error');
        } catch (error) {
            console.error('Dealer inquiry trend chart failed to render.', error);
            showChartFallback(inquiryWrapper, inquiryFallback, 'Unable to render inquiry trend chart.');
        }
    }

    var productCanvas = document.getElementById('dealerProductConversionChart');
    if (productCanvas) {
        var productWrapper = productCanvas.closest('.reports-product-chart-wrapper');
        var productFallback = productWrapper ? productWrapper.querySelector('.reports-product-chart-fallback') : null;

        try {
            var rawProducts = @json($productConversionDisplay->values());
            var products = rawProducts.map(function(item) {
                return {
                    label: String(item.label || ''),
                    count: Number(item.count || 0)
                };
            });

            var labels = products.map(function(item) { return item.label; });
            var dataValues = products.map(function(item) { return item.count; });
            var totalValue = dataValues.reduce(function(sum, value) { return sum + value; }, 0);
            var maxValue = dataValues.length ? Math.max.apply(null, dataValues) : 0;

            function getPerformanceTone(value) {
                var ratio = maxValue > 0 ? value / maxValue : 0;
                if (ratio >= 0.67) return { level: 'High', background: '#22c55e', border: '#16a34a' };
                if (ratio >= 0.34) return { level: 'Medium', background: '#f59e0b', border: '#d97706' };
                return { level: 'Low', background: '#ef4444', border: '#dc2626' };
            }

            var toneMap = dataValues.map(function(value) { return getPerformanceTone(value); });
            var barColors = toneMap.map(function(tone) { return tone.background; });
            var borderColors = toneMap.map(function(tone) { return tone.border; });
            var axisMax = maxValue > 0 ? Math.max(maxValue + 1, Math.ceil(maxValue * 1.35)) : 1;

            var endValueLabels = {
                id: 'dealerEndValueLabels',
                afterDatasetsDraw: function(chart) {
                    var meta = chart.getDatasetMeta(0);
                    if (!meta || !meta.data || !meta.data.length) return;

                    var ctx = chart.ctx;
                    var chartArea = chart.chartArea;
                    ctx.save();
                    ctx.font = '600 12px "Public Sans", sans-serif';
                    ctx.fillStyle = '#475569';
                    ctx.textBaseline = 'middle';

                    meta.data.forEach(function(bar, index) {
                        var value = dataValues[index] || 0;
                        var pct = totalValue > 0 ? Math.round((value / totalValue) * 100) : 0;
                        var text = value + ' (' + pct + '%)';
                        var textWidth = ctx.measureText(text).width;
                        var x = bar.x + 10;
                        if (x + textWidth > chartArea.right - 4) {
                            x = chartArea.right - textWidth - 4;
                        }
                        ctx.fillText(text, x, bar.y);
                    });

                    ctx.restore();
                }
            };

            new Chart(productCanvas.getContext('2d'), {
                type: 'bar',
                plugins: [endValueLabels],
                data: {
                    labels: labels,
                    datasets: [{
                        axis: 'y',
                        label: 'Product conversions',
                        data: dataValues,
                        backgroundColor: function(context) {
                            return barColors[context.dataIndex] || '#94a3b8';
                        },
                        borderColor: function(context) {
                            return borderColors[context.dataIndex] || '#64748b';
                        },
                        borderWidth: 1,
                        borderSkipped: false,
                        borderRadius: 8,
                        barThickness: 18,
                        maxBarThickness: 20,
                        categoryPercentage: 0.78,
                        barPercentage: 0.82,
                        hoverBackgroundColor: function(context) {
                            return borderColors[context.dataIndex] || '#475569';
                        }
                    }]
                },
                options: {
                    indexAxis: 'y',
                    maintainAspectRatio: false,
                    responsive: true,
                    animation: {
                        duration: 260,
                        easing: 'easeOutCubic'
                    },
                    layout: {
                        padding: {
                            top: 4,
                            right: 72,
                            bottom: 0,
                            left: 6
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            displayColors: false,
                            backgroundColor: 'rgba(15, 23, 42, 0.94)',
                            titleColor: '#ffffff',
                            bodyColor: '#ffffff',
                            padding: 12,
                            cornerRadius: 10,
                            callbacks: {
                                title: function(items) {
                                    return items[0] && items[0].label ? items[0].label : 'Product';
                                },
                                label: function(context) {
                                    var value = context.parsed && typeof context.parsed.x === 'number' ? Math.round(context.parsed.x) : 0;
                                    var pct = totalValue > 0 ? ((value / totalValue) * 100).toFixed(1) : '0.0';
                                    return 'Conversions: ' + value + ' (' + pct + '%)';
                                },
                                afterLabel: function(context) {
                                    var tone = toneMap[context.dataIndex];
                                    return tone ? 'Performance: ' + tone.level : '';
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            max: axisMax,
                            grid: {
                                color: 'rgba(148, 163, 184, 0.10)',
                                drawBorder: false,
                                drawTicks: false
                            },
                            border: {
                                display: false
                            },
                            ticks: {
                                color: '#94a3b8',
                                font: {
                                    size: 11,
                                    weight: '600'
                                },
                                padding: 8,
                                stepSize: maxValue <= 10 ? 1 : undefined,
                                callback: function(value) {
                                    return Math.round(value);
                                }
                            }
                        },
                        y: {
                            grid: {
                                display: false
                            },
                            border: {
                                display: false
                            },
                            ticks: {
                                padding: 12,
                                color: '#0f172a',
                                font: {
                                    size: 12,
                                    weight: '600'
                                }
                            }
                        }
                    }
                }
            });

            if (productWrapper) productWrapper.classList.remove('is-error');
        } catch (error) {
            console.error('Dealer product conversion chart failed to render.', error);
            showChartFallback(productWrapper, productFallback, 'Unable to render product conversion chart.');
        }
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initDealerReportsPage, { once: true });
} else {
    initDealerReportsPage();
}
</script>
@endpush
@endsection
