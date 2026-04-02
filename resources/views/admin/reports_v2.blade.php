@extends('layouts.app')
@section('title', 'Report - Dealer Sales Overtime')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/shared/reports-tabs.css') }}?v=20260402-8">
    <link rel="stylesheet" href="{{ asset('css/report_dealer_sales_overtime.css') }}?v=20260402-2">
    <link rel="stylesheet" href="{{ asset('css/pages/admin-reports-v2.css') }}?v=20260324-9">
@endpush
@section('content')
<div class="rv2-page">
    <header class="rv2-header">
        @php
            $reportTabQuery = [];
            $currentReportScope = trim((string) ($selectedReportScope ?? request('report_scope', '')));
            if ($currentReportScope !== '') {
                $reportTabQuery['report_scope'] = $currentReportScope;
            }
        @endphp
        <div class="reports-tabs-row">
            <nav class="reports-tabs-nav" aria-label="Report views">
                <a href="{{ route('admin.reports', $reportTabQuery) }}"
                   class="reports-tab-link {{ request()->routeIs('admin.reports') ? 'is-active' : '' }}">
                    Monthly Performance
                </a>
                <a href="{{ route('admin.reports.v2', $reportTabQuery) }}"
                   class="reports-tab-link {{ request()->routeIs('admin.reports.v2') ? 'is-active' : '' }}">
                    Dealer Sales Overtime
                </a>
                <a href="{{ route('admin.reports.revenue', $reportTabQuery) }}"
                   class="reports-tab-link {{ request()->routeIs('admin.reports.revenue') ? 'is-active' : '' }}">
                    Dealer Revenue Production
                </a>
            </nav>
        </div>
    </header>

    <div class="rv2-filtered-layer">
        <div class="rv2-filtered-layer-head">
            @php
                $clearSalesOvertimeFiltersUrl = route('admin.reports.v2', [
                    'days' => 90,
                    'compare_days' => 30,
                    'report_scope' => 'all',
                ]);
            @endphp
            <form method="GET" class="rv2-filters rv2-filters-form">
                @foreach(request()->query() as $key => $val)
                    @if($key !== 'days' && $key !== 'compare_days' && $key !== 'page' && $key !== 'primary_from' && $key !== 'primary_to' && $key !== 'compare_from' && $key !== 'compare_to' && $key !== 'include_dealer' && $key !== 'include_estream' && $key !== 'report_scope')
                        <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                    @endif
                @endforeach
                <div class="rv2-filter">
                    <div class="rv2-filter-label">PRIMARY PERIOD</div>
                    <select name="days" class="rv2-filter-select">
                        @php $primaryDays = (int) request('days', $chartDays ?? 90); @endphp
                        <option value="30" {{ $primaryDays === 30 ? 'selected' : '' }}>Last 30 days</option>
                        <option value="60" {{ $primaryDays === 60 ? 'selected' : '' }}>Last 60 days</option>
                        <option value="90" {{ $primaryDays === 90 ? 'selected' : '' }}>Last 90 days</option>
                        <option value="custom" {{ request('primary_from') || request('primary_to') ? 'selected' : '' }}>Custom range…</option>
                    </select>
                    <div class="rv2-date-range">
                        <input type="date" name="primary_from" value="{{ request('primary_from') }}">
                        <span>to</span>
                        <input type="date" name="primary_to" value="{{ request('primary_to') }}">
                    </div>
                </div>
                <div class="rv2-filter">
                    <div class="rv2-filter-label">COMPARE AGAINST</div>
                    @php $compareDays = (int) request('compare_days', 30); @endphp
                    <select name="compare_days" class="rv2-filter-select">
                        <option value="30" {{ $compareDays === 30 ? 'selected' : '' }}>Last 30 days</option>
                        <option value="60" {{ $compareDays === 60 ? 'selected' : '' }}>Last 60 days</option>
                        <option value="90" {{ $compareDays === 90 ? 'selected' : '' }}>Last 90 days</option>
                        <option value="custom" {{ request('compare_from') || request('compare_to') ? 'selected' : '' }}>Custom range…</option>
                    </select>
                    <div class="rv2-date-range">
                        <input type="date" name="compare_from" value="{{ request('compare_from') }}">
                        <span>to</span>
                        <input type="date" name="compare_to" value="{{ request('compare_to') }}">
                    </div>
                </div>
                <div class="rv2-filter">
                    <div class="rv2-filter-label">DEALER SCOPE</div>
                    @include('admin.partials.report_scope_picker', [
                        'options' => $reportScopeOptions ?? [],
                        'selected' => $selectedReportScope ?? 'all',
                    ])
                </div>
                <div class="rv2-filter rv2-filter-apply">
                    @include('admin.partials.report_filter_actions', [
                        'clearUrl' => $clearSalesOvertimeFiltersUrl,
                        'wrapperClass' => 'rv2-filter-actions report-filter-actions',
                        'applyClass' => 'report-filter-apply',
                        'clearClass' => 'report-filter-clear',
                    ])
                </div>
            </form>
        </div>

        <section class="rv2-panel rv2-panel-in-layer">
            <div class="rv2-panel-head">
                <div>
                    <div class="rv2-section-title rv2-section-title-prominent">Top 5 Dealer &mdash; Failed vs Closed</div>
</div>
            </div>
            <div class="rv2-panel-body">
                <div class="rv2-bar-chart-title-row">
                    <div class="rv2-bar-chart-title rv2-bar-chart-title-failed">Top 5 Failed (left)</div>
                    <div class="rv2-bar-chart-title rv2-bar-chart-title-closed">Top 5 Closed (right)</div>
                </div>
                <div class="rv2-chart-wrap rv2-bar-chart-wrap rv2-bar-chart-wrap-full">
                    <canvas id="top10FailedClosedChart"></canvas>
                </div>
            </div>
        </section>

        <section class="rv2-panel rv2-panel-in-layer">
            <div class="rv2-panel-head">
                <div>
                    <div class="rv2-section-title">
                        At-Risk Dealer
                        <i class="bi bi-info-circle dashboard-info-icon"
                           title="Fail-rate increase threshold: 30%+ compared with selected compare period."
                           aria-label="At-risk fail-rate threshold info"></i>
                    </div>
                </div>
                <div class="rv2-badge-danger">{{ $criticalDropsCount ?? 0 }} CRITICAL DROPS</div>
            </div>

            <div class="rv2-table-wrap">
                <table class="rv2-table">
                    <thead>
                        <tr>
                            <th>DEALER NAME</th>
                            <th>INCREASED FAIL RATE</th>
                            <th>FAIL RATE</th>
                            <th>FAIL COUNT</th>
                            <th>LAST ACTIVITY</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($atRisk as $r)
                            <tr>
                                <td>
                                    <div class="rv2-dealer-name">{{ $r['name'] }}</div>
                                    <div class="rv2-dealer-id">{{ $r['id'] }}</div>
                                </td>
                                <td>
                                    <div class="rv2-variance-val">{{ number_format($r['increase_fail_rate'] ?? 0, 1) }}%</div>
                                    <div class="rv2-variance-sub">vs selected compare period</div>
                                </td>
                                <td>
                                    <span class="rv2-muted">
                                        {{ isset($r['fail_rate']) ? number_format($r['fail_rate'], 1) . '%' : '—' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="rv2-muted">{{ isset($r['fail_count']) ? number_format($r['fail_count']) : '0' }}</span>
                                </td>
                                <td>
                                    @if(isset($r['last_activity_days']) && $r['last_activity_days'] !== null)
                                        <span class="rv2-pill-warn">
                                            {{ $r['last_activity_days'] === 0 ? 'Today' : $r['last_activity_days'] . ' days ago' }}
                                        </span>
                                    @else
                                        <span class="rv2-pill-warn">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="inquiries-empty">No At-Risk Dealer displayed</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                @php
                    $atRiskTotal = $atRiskTotal ?? 0;
                    $from = $atRiskTotal === 0 ? 0 : 1;
                    $to = $atRiskTotal;
                @endphp
                @if($atRiskTotal > 0)
                    <div class="rv2-table-footer">
                        <span class="rv2-muted-xs">Showing {{ $from }} to {{ $to }} of {{ $atRiskTotal }}</span>
                    </div>
                @endif
            </div>
        </section>
    </div>
</div>

{{-- View-only intervention / activity popout (like dealer status process) --}}
<div class="inquiries-assign-modal rv2-intervention-modal" id="rv2InterventionModal" hidden>
    <div class="inquiries-assign-backdrop" data-rv2-intervention-close="1"></div>
    <div class="inquiries-assign-window" role="dialog" aria-modal="true" aria-labelledby="rv2InterventionModalTitle">
        <div class="inquiries-assign-header">
            <div class="inquiries-assign-title" id="rv2InterventionModalTitle">Activity — Dealer <span id="rv2InterventionDealerName"></span> (ID: <span id="rv2InterventionDealerId"></span>)</div>
            <button type="button" class="inquiries-assign-close" aria-label="Close" data-rv2-intervention-close="1">&times;</button>
        </div>
        <div class="inquiries-assign-body">
            <p class="rv2-intervention-view-only">View only. Status process for this dealer.</p>
            <div class="inquiries-status-table-wrap">
                <table class="inquiries-table">
                    <thead><tr><th>Date</th><th>Lead</th><th>Subject</th><th>Status</th><th>Description</th><th>User</th></tr></thead>
                    <tbody id="rv2InterventionModalBody"></tbody>
                </table>
            </div>
            <p id="rv2InterventionModalEmpty" class="inquiries-empty" style="display:none;">No activity for this dealer.</p>
        </div>
    </div>
</div>

@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Title dropdown (shared across reports)
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
                        if (dropdown.contains(e.target)) return;
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

            // Log Intervention popout — view-only dealer activity (like status process in inquiries)
            (function initInterventionModal() {
                var modal = document.getElementById('rv2InterventionModal');
                var titleName = document.getElementById('rv2InterventionDealerName');
                var titleId = document.getElementById('rv2InterventionDealerId');
                var body = document.getElementById('rv2InterventionModalBody');
                var emptyEl = document.getElementById('rv2InterventionModalEmpty');
                if (!modal || !body) return;
                function closeModal() { modal.hidden = true; }
                function openModal(dealerId, dealerName, items) {
                    if (titleId) titleId.textContent = dealerId || '—';
                    if (titleName) titleName.textContent = dealerName || '—';
                    body.innerHTML = '';
                    if (!items || items.length === 0) {
                        if (emptyEl) emptyEl.style.display = 'block';
                    } else {
                        if (emptyEl) emptyEl.style.display = 'none';
                        items.forEach(function(it) {
                            var tr = document.createElement('tr');
                            var date = it.CREATIONDATE ? String(it.CREATIONDATE).substring(0, 19) : '—';
                            var leadId = it.LEADID != null ? '#SQL-' + it.LEADID : '—';
                            tr.innerHTML = '<td>' + date + '</td><td>' + leadId + '</td><td>' + (it.SUBJECT || '—') + '</td><td>' + (it.STATUS || '—') + '</td><td>' + (it.DESCRIPTION || '—') + '</td><td>' + (it.USERID || '—') + '</td>';
                            body.appendChild(tr);
                        });
                    }
                    modal.hidden = false;
                }
                document.addEventListener('click', function(e) {
                    var btn = e.target && e.target.closest ? e.target.closest('.rv2-intervention-btn') : null;
                    if (btn) {
                        var dealerId = btn.getAttribute('data-dealer-id');
                        var dealerName = btn.getAttribute('data-dealer-name') || '';
                        if (dealerId) {
                            fetch('{{ url("/admin/reports/dealer-activity") }}/' + encodeURIComponent(dealerId), { headers: { 'Accept': 'application/json' } })
                                .then(function(r) { return r.json(); })
                                .then(function(data) { openModal(dealerId, dealerName, data.items || []); })
                                .catch(function() { openModal(dealerId, dealerName, []); });
                        }
                        return;
                    }
                    if (e.target && (e.target.getAttribute('data-rv2-intervention-close') === '1')) closeModal();
                });
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape' && modal && !modal.hidden) closeModal();
                });
            })();

            // Toggle custom date ranges when "Custom range..." is selected
            document.querySelectorAll('.rv2-filter-select').forEach(function (sel) {
                function syncRange() {
                    var wrapper = sel.closest('.rv2-filter')?.querySelector('.rv2-date-range');
                    if (!wrapper) return;
                    wrapper.style.display = sel.value === 'custom' ? 'flex' : 'none';
                }
                sel.addEventListener('change', syncRange);
                syncRange();
            });

            // ——— Top 10 Failed / Top 10 Closed bar charts (side by side) ———
            const top10Failed = @json($top10Failed ?? []);
            const top10Closed = @json($top10Closed ?? []);
            // Keep a single, consistent color per side.
            const BAR_RED = 'rgba(220, 38, 38, 0.86)';
            const BAR_RED_HOVER = 'rgba(220, 38, 38, 1)';
            const BAR_CLOSED = 'rgba(22, 163, 74, 0.82)';
            const BAR_CLOSED_HOVER = 'rgba(22, 163, 74, 1)';
            const BAR_RED_BORDER = 'rgba(127, 29, 29, 1)';
            const BAR_CLOSED_BORDER = 'rgba(20, 83, 45, 1)';
            const isMobile = window.matchMedia('(max-width: 768px)').matches;
            const chartLabelFontSize = isMobile ? 8 : 10;
            const chartBarThickness = isMobile ? 22 : 40;

            if (window.Chart && typeof window.ChartDataLabels !== 'undefined') {
                window.Chart.register(window.ChartDataLabels);
            }

            const rowCount = Math.max(top10Failed.length, top10Closed.length, 0);
            const failedName = Array.from({ length: rowCount }, (_, i) => (top10Failed[i]?.name ?? top10Failed[i]?.dealer_id ?? '—'));
            const closedName = Array.from({ length: rowCount }, (_, i) => (top10Closed[i]?.name ?? top10Closed[i]?.dealer_id ?? '—'));
            const failedPct = Array.from({ length: rowCount }, (_, i) => top10Failed[i]?.percentage ?? 0);
            const closedPct = Array.from({ length: rowCount }, (_, i) => top10Closed[i]?.percentage ?? 0);

            const labels = Array.from({ length: rowCount }, (_, i) => `#${i + 1}`);
            const data = {
                labels: labels,
                datasets: [
                    {
                        label: 'Failed',
                        yAxisID: 'y',
                        data: Array.from({ length: rowCount }, (_, i) => -Math.min(100, Math.abs(failedPct[i] ?? 0))),
                        backgroundColor: Array.from({ length: rowCount }, () => BAR_RED),
                        hoverBackgroundColor: Array.from({ length: rowCount }, () => BAR_RED_HOVER),
                        borderColor: Array.from({ length: rowCount }, () => BAR_RED_BORDER),
                        borderWidth: 1.2,
                        borderRadius: 8,
                        barThickness: chartBarThickness,
                        maxBarThickness: chartBarThickness
                    },
                    {
                        label: 'Closed',
                        yAxisID: 'y',
                        data: Array.from({ length: rowCount }, (_, i) => Math.min(100, Math.abs(closedPct[i] ?? 0))),
                        backgroundColor: Array.from({ length: rowCount }, () => BAR_CLOSED),
                        hoverBackgroundColor: Array.from({ length: rowCount }, () => BAR_CLOSED_HOVER),
                        borderColor: Array.from({ length: rowCount }, () => BAR_CLOSED_BORDER),
                        borderWidth: 1.2,
                        borderRadius: 8,
                        barThickness: chartBarThickness,
                        maxBarThickness: chartBarThickness
                    }
                ]
            };

            const failedClosedBackdropPlugin = {
                id: 'failedClosedBackdropPlugin',
                beforeDatasetsDraw: function(chart) {
                    const chartArea = chart.chartArea;
                    const xScale = chart.scales?.x;
                    if (!chartArea || !xScale) return;

                    const left = chartArea.left;
                    const right = chartArea.right;
                    const top = chartArea.top;
                    const height = chartArea.bottom - chartArea.top;
                    const zeroX = xScale.getPixelForValue(0);
                    const ctx = chart.ctx;

                    ctx.save();
                    ctx.fillStyle = 'rgba(248, 113, 113, 0.06)';
                    ctx.fillRect(left, top, Math.max(0, zeroX - left), height);
                    ctx.fillStyle = 'rgba(74, 222, 128, 0.06)';
                    ctx.fillRect(zeroX, top, Math.max(0, right - zeroX), height);
                    ctx.restore();
                }
            };

            const config = {
                type: 'bar',
                data: data,
                plugins: [failedClosedBackdropPlugin],
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    layout: { padding: { left: 0, right: 0, top: 4, bottom: 4 } },
                    datasets: {
                        bar: {
                            // Shrink each category band and bar so Failed / Closed rows
                            // have more vertical space between them.
                            categoryPercentage: 0.6,
                            barPercentage: 0.6
                        }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(ctx) {
                                    const pct = Math.abs(Number(ctx.raw) || 0);
                                    const idx = ctx.dataIndex;
                                    const name = ctx.dataset.label === 'Failed' ? failedName[idx] : closedName[idx];
                                    return `${ctx.dataset.label}: ${pct}% — ${name}`;
                                }
                            }
                        },
                        datalabels: {
                            clip: false,
                            labels: {
                                // Only show percentages inside the bars
                                pct: {
                                    color: '#ffffff',
                                    font: { size: chartLabelFontSize, weight: '700' },
                                    formatter: function(_value, ctx) {
                                        const i = ctx.dataIndex;
                                        return ctx.dataset.label === 'Failed' ? `${failedPct[i]}%` : `${closedPct[i]}%`;
                                    },
                                    anchor: 'center',
                                    align: 'center'
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            min: -100,
                            max: 100,
                            grid: {
                                color: function(ctx) {
                                    return Number(ctx.tick?.value) === 0 ? 'rgba(51, 65, 85, 0.4)' : 'rgba(148, 163, 184, 0.22)';
                                },
                                lineWidth: function(ctx) {
                                    return Number(ctx.tick?.value) === 0 ? 1.2 : 1;
                                },
                                drawTicks: false
                            },
                            ticks: {
                                stepSize: 20,
                                color: '#475569',
                                font: { size: chartLabelFontSize },
                                callback: function(v) { return Math.abs(v); }
                            },
                            title: {
                                display: true,
                                text: 'Percentage of cases',
                                color: '#64748b',
                                font: { size: chartLabelFontSize, weight: '700' }
                            }
                        },
                        // Left Sidebar Alignment (Failed Names)
                        y: {
                            position: 'left',
                            grid: { display: false, drawBorder: false },
                            ticks: {
                                autoSkip: false,
                                color: '#7f1d1d',
                                font: { size: chartLabelFontSize, weight: '700' },
                                callback: function(value, index) {
                                    const name = failedName[index] ?? '—';
                                    return name.length > 15 ? name.substring(0, 15) + '…' : name;
                                }
                            }
                        },
                        // Right Sidebar Alignment (Closed Names)
                        yRight: {
                            position: 'right',
                            grid: { display: false, drawBorder: false },
                            ticks: {
                                autoSkip: false,
                                color: '#166534',
                                font: { size: chartLabelFontSize, weight: '700' },
                                callback: function(value, index) {
                                    const name = closedName[index] ?? '—';
                                    return name.length > 15 ? name.substring(0, 15) + '…' : name;
                                }
                            }
                        }
                    }
                }
            };

            const divergeEl = document.getElementById('top10FailedClosedChart');
            if (divergeEl && window.Chart && rowCount > 0) {
                // Make chart height dynamic based on number of dealers and bar size.
                const perRow = isMobile ? 52 : 92;
                const minRows = isMobile ? 1 : 4;
                const rowsForHeight = Math.max(rowCount, minRows);
                divergeEl.height = perRow * rowsForHeight;
                new Chart(divergeEl.getContext('2d'), config);
            }
        });
    </script>
@endpush
