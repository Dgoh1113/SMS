@extends('layouts.app')
@section('title', 'Reports – SQL LMS Dealer Console')
@section('content')
<div class="dashboard-content reports-page">
    <header class="reports-header">
        <div class="reports-header-actions">
            <form method="get" action="{{ route('dealer.reports') }}" class="reports-period-form" id="reportsPeriodForm">
                <input type="hidden" name="period" id="reportsPeriodInput" value="{{ $period ?? 'month' }}">
                <input type="hidden" name="from" id="reportsFromInput" value="{{ $from ?? '' }}">
                <input type="hidden" name="to" id="reportsToInput" value="{{ $to ?? '' }}">
                <div class="reports-period-dropdown">
                    <button type="button" class="reports-period-btn" id="reportsPeriodBtn" aria-expanded="false" aria-haspopup="listbox">
                        <i class="bi bi-calendar3"></i>
                        <span class="reports-period-label">{{ $periodLabel ?? 'Current Month' }}</span>
                        <i class="bi bi-chevron-down"></i>
                    </button>
                    <div class="reports-period-menu" id="reportsPeriodMenu" role="listbox" hidden>
                        <button type="button" class="reports-period-option" data-value="week">Current Week</button>
                        <button type="button" class="reports-period-option" data-value="month">Current Month</button>
                        <button type="button" class="reports-period-option" data-value="year">Current Year</button>
                        <button type="button" class="reports-period-option reports-period-option-range" data-value="range">Range</button>
                        <div class="reports-range-panel" id="reportsRangePanel" hidden>
                            <div class="reports-range-inputs">
                                <label>
                                    <span>From</span>
                                    <input type="date" id="reportsRangeFrom" class="reports-range-input" value="{{ $from ?? '' }}">
                                </label>
                                <label>
                                    <span>To</span>
                                    <input type="date" id="reportsRangeTo" class="reports-range-input" value="{{ $to ?? '' }}">
                                </label>
                            </div>
                            <div class="reports-range-actions">
                                <button type="button" class="reports-range-back" id="reportsRangeBack">Back</button>
                                <button type="button" class="reports-range-apply" id="reportsRangeApply">Apply</button>
                            </div>
                        </div>
                    </div>
                </div>
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

    {{-- Charts Row --}}
    <section class="reports-charts-row">
        <div class="reports-panel reports-chart-panel">
            <div class="reports-chart-header">
                <h2 class="reports-chart-title">Inquiry Trends</h2>
                <p class="reports-chart-subtitle">Total inquiries: {{ $totalInquiry ?? 0 }}</p>
            </div>
            @php
                $trend = $inquiryTrendData ?? [0, 0, 0, 0];
                $labels = $trendLabels ?? ['Week 1', 'Week 2', 'Week 3', 'Week 4'];
                $trendMax = max(1, max($trend));
                $heights = array_map(fn($v) => $trendMax > 0 ? round(($v / $trendMax) * 100, 1) : 0, $trend);
                $yTicks = $trendMax <= 5
                    ? range(0, $trendMax)
                    : [0, (int) round($trendMax * 0.25), (int) round($trendMax * 0.5), (int) round($trendMax * 0.75), $trendMax];
            @endphp
            <div class="reports-line-chart-placeholder">
                <div class="reports-line-chart-main">
                    <div class="reports-line-chart-yaxis">
                        @foreach (array_reverse($yTicks) as $tick)
                        <span>{{ $tick }}</span>
                        @endforeach
                    </div>
                    <div class="reports-line-chart-area reports-trend-bars">
                        @foreach ($trend as $i => $v)
                        <div class="reports-trend-bar" style="height:{{ $heights[$i] ?? 0 }}%"></div>
                        @endforeach
                    </div>
                </div>
                <div class="reports-line-chart-xaxis reports-line-chart-xaxis--{{ count($labels) }}">
                    @foreach ($labels as $lbl)
                    <span>{{ $lbl }}</span>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="reports-panel reports-chart-panel">
            <div class="reports-chart-header">
                <h2 class="reports-chart-title">Status Report</h2>
            </div>
            @php
                $donutOrder = ['PENDING', 'FOLLOW UP', 'DEMO', 'CONFIRMED', 'COMPLETED', 'REWARDED'];
                $donutColors = ['#fecaca', '#dc2626', '#fef08a', '#eab308', '#bbf7d0', '#22c55e'];
                $donutTotal = array_sum(array_map(fn($s) => $statusCounts[$s] ?? 0, $donutOrder));
                $donutSegments = [];
                $deg = 0;
                foreach ($donutOrder as $i => $s) {
                    $cnt = $statusCounts[$s] ?? 0;
                    $pct = $donutTotal > 0 ? ($cnt / $donutTotal) : 0;
                    $endDeg = $deg + ($pct * 360);
                    $donutSegments[] = $donutColors[$i] . ' ' . round($deg, 1) . 'deg ' . round($endDeg, 1) . 'deg';
                    $deg = $endDeg;
                }
                $donutGradient = $donutTotal > 0 ? 'conic-gradient(' . implode(', ', $donutSegments) . ')' : 'conic-gradient(#e5e7eb 0deg 360deg)';
            @endphp
            <div class="reports-donut-placeholder">
                <div class="reports-donut-ring" style="background: {{ $donutGradient }};"></div>
                <div class="reports-donut-legend">
                    <span class="reports-legend-item"><i class="bi bi-circle-fill" style="color:#fecaca"></i> Pending</span>
                    <span class="reports-legend-item"><i class="bi bi-circle-fill" style="color:#dc2626"></i> Follow Up</span>
                    <span class="reports-legend-item"><i class="bi bi-circle-fill" style="color:#fef08a"></i> Demo</span>
                    <span class="reports-legend-item"><i class="bi bi-circle-fill" style="color:#eab308"></i> Confirmed</span>
                    <span class="reports-legend-item"><i class="bi bi-circle-fill" style="color:#bbf7d0"></i> Completed</span>
                    <span class="reports-legend-item"><i class="bi bi-circle-fill" style="color:#22c55e"></i> Rewarded</span>
                </div>
            </div>
        </div>
    </section>

    {{-- Product Conversion Chart --}}
    @php
        $productNames = ['SQL Account', 'SQL Payroll', 'SQL Production', 'Mobile Sales', 'SQL Ecommerce', 'SQL EBI Wellness POS', 'SQL X Suduai', 'SQL X-Store', 'SQL Vision', 'SQL HRMS', 'Others'];
        $productCounts = $productCounts ?? array_fill(0, 11, 0);
        $productMax = max(1, max($productCounts));
        $productTicks = $productMax <= 5 ? range(0, $productMax) : [0, (int) round($productMax * 0.33), (int) round($productMax * 0.66), $productMax];
    @endphp
    <section class="reports-panel reports-product-panel">
        <h2 class="reports-chart-title">Product Conversion</h2>
        <div class="reports-bar-chart-placeholder">
            <div class="reports-bar-chart-content">
                <div class="reports-bar-chart-yaxis">
                    @foreach ($productNames as $name)
                    <span>{{ $name }}</span>
                    @endforeach
                </div>
                <div class="reports-bar-chart-bars">
                    @foreach ($productCounts as $i => $cnt)
                    @php $pid = $i + 1; @endphp
                    <div class="reports-bar-row">
                        <div class="reports-bar-fill reports-product-fill reports-product-fill-p{{ $pid }}"
                             style="width:{{ $productMax > 0 ? round(($cnt / $productMax) * 100, 1) : 0 }}%"></div>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="reports-bar-chart-xaxis">
                @foreach ($productTicks as $tick)
                <span>{{ $tick }}</span>
                @endforeach
            </div>
        </div>
    </section>

</div>

@push('scripts')
<script>
(function() {
    var form = document.getElementById('reportsPeriodForm');
    var periodInput = document.getElementById('reportsPeriodInput');
    var fromInput = document.getElementById('reportsFromInput');
    var toInput = document.getElementById('reportsToInput');
    var btn = document.getElementById('reportsPeriodBtn');
    var menu = document.getElementById('reportsPeriodMenu');
    var label = document.querySelector('.reports-period-label');
    var options = document.querySelectorAll('.reports-period-option:not(.reports-period-option-range)');
    var rangeOption = document.querySelector('.reports-period-option-range');
    var rangePanel = document.getElementById('reportsRangePanel');
    var rangeFrom = document.getElementById('reportsRangeFrom');
    var rangeTo = document.getElementById('reportsRangeTo');
    var rangeApply = document.getElementById('reportsRangeApply');
    var rangeBack = document.getElementById('reportsRangeBack');

    var labels = { week: 'Current Week', month: 'Current Month', year: 'Current Year' };

    function closeMenu() {
        menu.hidden = true;
        btn.setAttribute('aria-expanded', 'false');
        rangePanel.hidden = true;
        options.forEach(function(o) { o.hidden = false; });
        if (rangeOption) rangeOption.hidden = false;
    }

    function showRangeOnly() {
        options.forEach(function(o) { o.hidden = true; });
        if (rangeOption) rangeOption.hidden = true;
        rangePanel.hidden = false;
    }

    function showOptionsOnly() {
        options.forEach(function(o) { o.hidden = false; });
        if (rangeOption) rangeOption.hidden = false;
        rangePanel.hidden = true;
    }

    btn.addEventListener('click', function(e) {
        e.preventDefault();
        var open = !menu.hidden;
        if (open) {
            closeMenu();
        } else {
            menu.hidden = false;
            btn.setAttribute('aria-expanded', 'true');
            showOptionsOnly();
        }
    });

    options.forEach(function(opt) {
        opt.addEventListener('click', function(e) {
            e.preventDefault();
            periodInput.value = opt.dataset.value;
            fromInput.value = '';
            toInput.value = '';
            form.submit();
        });
    });

    if (rangeOption) {
        rangeOption.addEventListener('click', function(e) {
            e.stopPropagation();
            e.preventDefault();
            showRangeOnly();
        });
    }

    if (rangeFrom.value) rangeTo.min = rangeFrom.value;
    rangeFrom.addEventListener('change', function() {
        rangeTo.min = rangeFrom.value || '';
    });

    rangeApply.addEventListener('click', function(e) {
        e.preventDefault();
        var from = rangeFrom.value;
        var to = rangeTo.value;
        if (from && to && from <= to) {
            periodInput.value = 'range';
            fromInput.value = from;
            toInput.value = to;
            form.submit();
        }
    });

    rangeBack.addEventListener('click', function(e) {
        e.preventDefault();
        showOptionsOnly();
    });

    document.addEventListener('click', function(e) {
        if (!btn.contains(e.target) && !menu.contains(e.target)) closeMenu();
    });
})();
</script>
@endpush
@endsection
