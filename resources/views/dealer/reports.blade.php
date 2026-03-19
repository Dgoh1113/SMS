@extends('layouts.app')
@section('title', 'Reports – SQL LMS Dealer Console')
@section('content')
<div class="dashboard-content reports-page">
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
                $donutColors = ['#FF4500', '#FF8C00', '#FFD700', '#9ACD32', '#32CD32', '#008000'];
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
                    <span class="reports-legend-item"><i class="bi bi-circle-fill" style="color:#FF4500"></i> Pending</span>
                    <span class="reports-legend-item"><i class="bi bi-circle-fill" style="color:#FF8C00"></i> Follow Up</span>
                    <span class="reports-legend-item"><i class="bi bi-circle-fill" style="color:#FFD700"></i> Demo</span>
                    <span class="reports-legend-item"><i class="bi bi-circle-fill" style="color:#9ACD32"></i> Confirmed</span>
                    <span class="reports-legend-item"><i class="bi bi-circle-fill" style="color:#32CD32"></i> Completed</span>
                    <span class="reports-legend-item"><i class="bi bi-circle-fill" style="color:#008000"></i> Rewarded</span>
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
    var periodSelect = document.getElementById('reportsPeriodSelect');
    var rangeWrap = document.getElementById('reportsRangeInline');
    var rangeFrom = document.getElementById('reportsRangeFrom');
    var rangeTo = document.getElementById('reportsRangeTo');

    if (!form || !periodSelect || !rangeWrap || !rangeFrom || !rangeTo) return;

    function syncRangeInputs() {
        var isRange = periodSelect.value === 'range';
        rangeWrap.classList.toggle('is-hidden', !isRange);
        rangeFrom.disabled = !isRange;
        rangeTo.disabled = !isRange;
        rangeFrom.required = isRange;
        rangeTo.required = isRange;
        if (!isRange) {
            rangeTo.min = '';
        } else {
            rangeTo.min = rangeFrom.value || '';
        }
    }

    periodSelect.addEventListener('change', syncRangeInputs);
    rangeFrom.addEventListener('change', function() {
        rangeTo.min = rangeFrom.value || '';
    });

    form.addEventListener('submit', function(e) {
        if (periodSelect.value === 'range') {
            var from = rangeFrom.value;
            var to = rangeTo.value;
            if (!from || !to || from > to) {
                e.preventDefault();
                rangeFrom.focus();
            }
        }
    });

    syncRangeInputs();
})();
</script>
@endpush
@endsection
