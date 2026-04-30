@extends('layouts.app')
@section('title', 'Report - Dealer Revenue Production')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/shared/reports-tabs.css') }}?v=20260424-1">
    <link rel="stylesheet" href="{{ asset('css/report_dealer_revenue_production.css') }}?v=20260423-3">
@endpush
@section('content')
<div class="rrp-page">
    @php
        $reportTabQuery = [];
        $currentReportScope = trim((string) ($selectedReportScope ?? request('report_scope', 'all')));
        if ($currentReportScope !== '') {
            $reportTabQuery['report_scope'] = $currentReportScope;
        }
    @endphp
    <div class="reports-tabs-row rrp-tabs-row">
        <nav class="reports-tabs-nav" aria-label="Report views">
            <a href="{{ route('admin.reports', $reportTabQuery) }}"
               class="reports-tab-link {{ request()->routeIs('admin.reports') ? 'is-active' : '' }}">
                <i class="bi bi-bar-chart-line reports-tab-icon" aria-hidden="true"></i>
                <span>Monthly Performance</span>
            </a>
            <a href="{{ route('admin.reports.v2', $reportTabQuery) }}"
               class="reports-tab-link {{ request()->routeIs('admin.reports.v2') ? 'is-active' : '' }}">
                <i class="bi bi-clock-history reports-tab-icon" aria-hidden="true"></i>
                <span>Dealer Sales Overtime</span>
            </a>
            <a href="{{ route('admin.reports.revenue', $reportTabQuery) }}"
               class="reports-tab-link {{ request()->routeIs('admin.reports.revenue') ? 'is-active' : '' }}">
                <i class="bi bi-coin reports-tab-icon" aria-hidden="true"></i>
                <span>Dealer Revenue Production</span>
            </a>
        </nav>
    </div>

    <div class="reports-filter-bar">
        <form method="GET" action="{{ route('admin.reports.revenue') }}" class="reports-period-form reports-period-form-compact rrp-filter-form" data-auto-submit-report-filters style="display: flex; flex-direction: row; align-items: flex-end; gap: 8px; flex-wrap: nowrap; justify-content: flex-end;">
            @foreach(request()->query() as $key => $val)
                @if($key !== 'days' && $key !== 'from' && $key !== 'to' && $key !== 'report_area' && $key !== 'report_scope')
                    <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                @endif
            @endforeach

            <div class="reports-filter-container rv2-filter" style="width: 190px; min-height: 90px; display: flex; flex-direction: column; align-self: auto;">
                <div class="reports-range-label" style="display: flex; align-items: center; font-size: 9px; font-weight: 800; height: 1.6em;">PERIOD</div>
                <div style="flex: 1; display: flex; align-items: flex-end;">
                    <select name="days" class="rrp-filter-select" aria-label="Select period" id="reportsPeriodSelect" style="display: {{ request('from') || request('to') ? 'none' : 'block' }}; width: 100%;">
                        @php $daysParam = request('days', '60'); @endphp
                        <option value="30" {{ $daysParam == '30' ? 'selected' : '' }}>Last 30 Days</option>
                        <option value="60" {{ $daysParam == '60' ? 'selected' : '' }}>Last 60 Days</option>
                        <option value="90" {{ $daysParam == '90' ? 'selected' : '' }}>Last 90 Days</option>
                        <option value="custom" {{ request('from') || request('to') ? 'selected' : '' }}>Custom range…</option>
                    </select>
                    <div id="reportsRangeInline" class="reports-range-grid" style="display: {{ request('from') || request('to') ? 'grid' : 'none' }}; width: 100%; min-width: 0; gap: 4px;">
                        <div class="reports-range-col">
                            <label class="reports-range-label" style="font-size: 9px; opacity: 0.8;">Starting</label>
                            <input type="date" name="from" id="reportsRangeFrom" value="{{ request('from', now()->subMonth()->format('Y-m-d')) }}" class="reports-range-input" aria-label="From date" style="width: 100%;">
                        </div>
                        <div class="reports-range-col">
                            <label class="reports-range-label" style="font-size: 9px; opacity: 0.8;">Ending</label>
                            <input type="date" name="to" id="reportsRangeTo" value="{{ request('to', now()->format('Y-m-d')) }}" class="reports-range-input" aria-label="To date" style="width: 100%;">
                        </div>
                        <button type="button" class="reports-range-back-btn" id="reportsRangeReset" style="right: 4px; top: 4px;">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="reports-filter-container rv2-filter" style="width: 170px; min-height: 90px; display: flex; flex-direction: column; align-self: auto;">
                <div class="reports-range-label" style="display: flex; align-items: center; font-size: 9px; font-weight: 800; height: 1.6em;">DEALER SCOPE</div>
                <div style="flex: 1; display: flex; align-items: flex-end;">
                    <div style="width: 100%; max-width: 100%; --report-scope-picker-width: 100%;">
                        @include('admin.partials.report_scope_picker', [
                            'options' => $reportScopeOptions ?? [],
                            'selected' => $selectedReportScope ?? 'all',
                        ])
                    </div>
                </div>
            </div>

            <div class="reports-period-actions report-filter-actions" style="margin-left: auto; align-self: flex-end; padding-bottom: 8px;">
                @include('admin.partials.report_filter_actions', [
                    'wrapperClass' => 'rrp-filter-actions-inner report-filter-actions-inner',
                    'applyClass' => 'report-filter-apply',
                    'exportClass' => 'report-filter-export',
                    'clearClass' => 'report-filter-clear',
                    'showApply' => false,
                    'showExport' => true,
                    'showClear' => false,
                    'exportTitle' => 'Dealer Revenue Production Report',
                    'exportTarget' => '.rrp-page',
                ])
            </div>
        </form>
    </div>

    <section class="rrp-top-grid">
        <div class="rrp-metric-card">
            <div class="rrp-metric-label">Total Inquiries</div>
            <div class="rrp-metric-value">{{ number_format($totalVolume) }}</div>
            <div class="rrp-metric-sub">Total leads assigned to dealers in {{ $periodLabel ?? 'selected period' }}</div>
        </div>
        <div class="rrp-metric-card">
            <div class="rrp-metric-label">Average Fail Rate</div>
            <div class="rrp-metric-value">{{ number_format($avgRejectionRate, 1) }}%</div>
            <div class="rrp-metric-sub">Across active dealers in {{ $periodLabel ?? 'selected period' }}</div>
        </div>
        <div class="rrp-metric-card">
            <div class="rrp-metric-label">Top Dealer by Product Conversion</div>
            <div class="rrp-metric-value">{{ $topProductDealer['name'] ?? '-' }}</div>
            <div class="rrp-metric-sub">
                {{ isset($topProductDealer['converted_products']) ? number_format((int) $topProductDealer['converted_products']) : 0 }} converted products in {{ $periodLabel ?? 'selected period' }}
            </div>
        </div>
    </section>

    <section class="rrp-panel">
        <div class="rrp-panel-header">
            <div>
                <div class="rrp-panel-title">Dealer Volume vs Outcomes</div>
                <div class="rrp-panel-subtitle">Top dealers for {{ $periodLabel ?? 'selected period' }}</div>
            </div>
            <div class="rrp-legend">
                <span class="rrp-legend-item"><span class="rrp-dot rrp-dot-purple"></span> Total Leads</span>
                <span class="rrp-legend-item"><span class="rrp-dot rrp-dot-gold"></span> Closed Leads</span>
                <span class="rrp-legend-item"><span class="rrp-dot rrp-dot-green"></span> Rewarded Leads</span>
            </div>
        </div>
        <div class="rrp-panel-body">
            @if (empty($chartLabels))
                <p class="rrp-empty">No dealer performance data for selected period.</p>
            @else
                <div class="rrp-chart-wrap">
                    <canvas id="rrpVolumeChart"></canvas>
                </div>
            @endif
        </div>
    </section>

    <section class="rrp-panel">
        <div class="rrp-panel-header">
            <div>
                <div class="rrp-panel-title">Dealer Product Conversion Ranking</div>
                <div class="rrp-panel-subtitle">Sorted by closed products in {{ $periodLabel ?? 'selected period' }}</div>
            </div>
            <div class="rrp-pill rrp-pill-purple">{{ $periodLabel ?? 'Period' }}</div>
        </div>
        <div class="rrp-panel-body">
            <div class="table-responsive">
                <table class="dashboard-table rrp-table">
                    <thead>
                        <tr>
                            <th>Dealer Name</th>
                            <th>Total Inquiries</th>
                            <th>Closed Inquiries</th>
                            <th>Fail Rate</th>
                            <th>Closed Products</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rankings as $row)
                            <tr>
                                <td>
                                    <div class="rrp-dealer-name">{{ $row['name'] ?? $row['email'] }}</div>
                                </td>
                                <td>{{ number_format($row['total']) }}</td>
                                <td>{{ number_format($row['closed']) }}</td>
                                <td>{{ number_format($row['rejection_rate'], 1) }}%</td>
                                <td>{{ number_format((int) ($row['converted_products'] ?? 0)) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="inquiries-empty">No dealer data for selected quarter and year.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.querySelector('.rrp-filter-form');
            if (!form) return;

            let autoSubmitTimer = null;

            const markReportFiltersSubmitting = function () {
                form.classList.add('is-report-filter-submitting');
                form.querySelectorAll('select[name="report_scope"]').forEach(function (select) {
                    if (select.tomselect && typeof select.tomselect.blur === 'function') {
                        select.tomselect.blur();
                    }
                });
                if (document.activeElement && form.contains(document.activeElement) && typeof document.activeElement.blur === 'function') {
                    document.activeElement.blur();
                }
            };

            const getRangeWrapper = function (select) {
                if (select.name !== 'days') return null;
                return document.getElementById('reportsRangeInline');
            };

            const syncRange = function (select) {
                const wrapper = getRangeWrapper(select);
                if (!wrapper) return;
                const isCustom = select.value === 'custom';
                wrapper.style.display = isCustom ? 'grid' : 'none';
                select.style.display = isCustom ? 'none' : 'block';
                
                const inputs = wrapper.querySelectorAll('input[type="date"]');
                const fromInput = inputs[0];
                const toInput = inputs[1];

                if (fromInput && toInput) {
                    fromInput.disabled = !isCustom;
                    toInput.disabled = !isCustom;
                    fromInput.required = isCustom;
                    toInput.required = isCustom;

                    if (!isCustom) {
                        toInput.min = '';
                    } else {
                        if (!fromInput.value) fromInput.value = "{{ now()->subMonth()->format('Y-m-d') }}";
                        if (!toInput.value) toInput.value = "{{ now()->format('Y-m-d') }}";
                        toInput.min = fromInput.value || '';
                    }
                }
            };

            const isFormReadyToSubmit = function () {
                const daysSelect = form.querySelector('select[name="days"]');
                if (daysSelect && daysSelect.value === 'custom') {
                    const wrapper = getRangeWrapper(daysSelect);
                    if (wrapper) {
                        const inputs = wrapper.querySelectorAll('input[type="date"]');
                        const from = inputs[0] ? inputs[0].value : '';
                        const to = inputs[1] ? inputs[1].value : '';
                        if (from === '' || to === '' || from > to) {
                            return false;
                        }
                    }
                }
                return true;
            };

            const submitReportFilters = function () {
                window.clearTimeout(autoSubmitTimer);
                if (!isFormReadyToSubmit()) return;
                autoSubmitTimer = window.setTimeout(function () {
                    markReportFiltersSubmitting();
                    if (typeof form.requestSubmit === 'function') {
                        form.requestSubmit();
                        return;
                    }
                    form.submit();
                }, 80);
            };

            form.querySelectorAll('select[name="days"], select[name="report_scope"], select[name="report_area"]').forEach(function (select) {
                if (select.name === 'days') {
                    select.addEventListener('change', function() {
                        syncRange(select);
                        submitReportFilters();
                    });
                } else {
                    select.addEventListener('change', submitReportFilters);
                }
                const bindTomSelectChange = function () {
                    if (!select.tomselect || select.dataset.autoSubmitTomSelectReady === '1') return;
                    select.dataset.autoSubmitTomSelectReady = '1';
                    select.tomselect.on('change', submitReportFilters);
                };
                bindTomSelectChange();
                window.setTimeout(bindTomSelectChange, 120);
                window.setTimeout(bindTomSelectChange, 360);
            });

            const rangeWrapper = document.getElementById('reportsRangeInline');
            if (rangeWrapper) {
                const inputs = rangeWrapper.querySelectorAll('input[type="date"]');
                if (inputs.length === 2) {
                    const fromInput = inputs[0];
                    const toInput = inputs[1];
                    fromInput.addEventListener('change', submitReportFilters);
                    toInput.addEventListener('change', submitReportFilters);
                    
                    fromInput.addEventListener('input', function() {
                        toInput.min = fromInput.value || '';
                        if (toInput.value && fromInput.value && toInput.value < fromInput.value) {
                            toInput.value = fromInput.value;
                        }
                    });
                }
                const rangeReset = document.getElementById('reportsRangeReset');
                if (rangeReset) {
                    rangeReset.addEventListener('click', function() {
                        const daysSelect = form.querySelector('select[name="days"]');
                        if (daysSelect) {
                            daysSelect.value = '60';
                            syncRange(daysSelect);
                            submitReportFilters();
                        }
                    });
                }
            }

            // Area searchable dropdown
            const areaSelectEl = document.getElementById('adminRevenueAreaSelect');
            if (areaSelectEl && typeof TomSelect === 'function') {
                new TomSelect(areaSelectEl, {
                    plugins: ['dropdown_input'],
                    maxOptions: 100,
                    searchField: 'text',
                    placeholder: 'Search city...',
                    allowEmptyOption: false,
                    onDropdownOpen: function() {
                        this.clearCache();
                    },
                    onChange: function() {
                        submitReportFilters();
                    }
                });
            }

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

            const labels = @json($chartLabels);
            const volume = @json($chartVolume);
            const closed = @json($chartClosed);
            const rewarded = @json($chartRewarded);
            const isDarkTheme = document.documentElement.classList.contains('theme-dark');
            const isMobile = window.matchMedia('(max-width: 768px)').matches;
            const chartFontSize = isMobile ? 10 : 12;
            const chartBarThickness = isMobile ? 16 : undefined;
            const legendColor = isDarkTheme ? '#a5b1cf' : '#475569';
            const tickColor = isDarkTheme ? '#99a5c5' : '#475569';
            const axisColor = isDarkTheme ? '#7f8caf' : '#64748b';
            const gridColor = isDarkTheme ? 'rgba(148, 163, 184, 0.12)' : 'rgba(148, 163, 184, 0.25)';

            // Keep grouped bars consistent and readable.
            const data = {
                labels: labels,
                datasets: [
                    {
                        label: 'Total Leads',
                        data: volume,
                        backgroundColor: 'rgba(79, 70, 229, 0.88)',
                        borderColor: 'rgba(67, 56, 202, 1)',
                        borderWidth: 1.2,
                        borderRadius: 8,
                        maxBarThickness: chartBarThickness,
                        barPercentage: 0.85,
                        categoryPercentage: 0.9,
                    },
                    {
                        label: 'Closed Leads',
                        data: closed,
                        backgroundColor: 'rgba(234, 179, 8, 0.88)',
                        borderColor: 'rgba(202, 138, 4, 1)',
                        borderWidth: 1.2,
                        borderRadius: 8,
                        maxBarThickness: chartBarThickness,
                        barPercentage: 0.85,
                        categoryPercentage: 0.9,
                    },
                    {
                        label: 'Rewarded Leads',
                        data: rewarded,
                        backgroundColor: 'rgba(22, 163, 74, 0.88)',
                        borderColor: 'rgba(21, 128, 61, 1)',
                        borderWidth: 1.2,
                        borderRadius: 8,
                        maxBarThickness: chartBarThickness,
                        barPercentage: 0.85,
                        categoryPercentage: 0.9,
                    }
                ]
            };

            const config = {
                type: 'bar',
                data: data,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                color: legendColor,
                                usePointStyle: true,
                                boxWidth: 8,
                                font: { size: chartFontSize }
                            }
                        },
                        title: {
                            display: false,
                        },
                        tooltip: {
                            backgroundColor: isDarkTheme ? 'rgba(15, 23, 42, 0.96)' : 'rgba(15, 23, 42, 0.92)',
                            titleColor: '#ffffff',
                            bodyColor: '#e5edf9',
                            borderColor: isDarkTheme ? 'rgba(99, 113, 146, 0.45)' : 'rgba(148, 163, 184, 0.24)',
                            borderWidth: 1,
                            callbacks: {
                                label: function(ctx) {
                                    const value = Number(ctx.parsed?.y ?? 0);
                                    return `${ctx.dataset.label}: ${value.toLocaleString()}`;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: {
                                color: tickColor,
                                font: { size: chartFontSize },
                                maxRotation: isMobile ? 40 : 0,
                                minRotation: isMobile ? 40 : 0,
                            },
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: gridColor },
                            ticks: {
                                precision: 0,
                                color: axisColor,
                                font: { size: chartFontSize },
                            },
                            title: {
                                display: true,
                                text: 'Number of inquiries',
                                color: axisColor,
                                font: { size: isMobile ? 10 : 11, weight: '700' }
                            }
                        },
                    },
                },
            };

            const el = document.getElementById('rrpVolumeChart');
            if (el && window.Chart) {
                new Chart(el.getContext('2d'), config);
            }
        });
    </script>
@endpush
