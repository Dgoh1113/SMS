@extends('layouts.app')
@section('title', 'Report - Dealer Sales Overtime')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/shared/reports-tabs.css') }}?v=20260424-1">
    <link rel="stylesheet" href="{{ asset('css/report_dealer_sales_overtime.css') }}?v=20260423-4">
    <link rel="stylesheet" href="{{ asset('css/pages/admin-reports-v2.css') }}?v=20260324-9">
@endpush
@section('content')
<div class="rv2-page">
    <header class="rv2-header">
        @php
            $reportTabQuery = [];
            $currentReportScope = trim((string) ($selectedReportScope ?? request('report_scope', 'all')));
            if ($currentReportScope !== '') {
                $reportTabQuery['report_scope'] = $currentReportScope;
            }
        @endphp
        <div class="reports-tabs-row">
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
    </header>

    <div class="rv2-filtered-layer">
        <div class="rv2-filtered-layer-head reports-filter-bar">
            <form method="GET" action="{{ route('admin.reports.v2') }}" class="reports-period-form reports-period-form-compact rv2-filters-form" data-auto-submit-report-filters style="display: flex; flex-direction: row; align-items: flex-end; gap: 12px; flex-wrap: wrap;">
                @foreach(request()->query() as $key => $val)
                    @if($key !== 'days' && $key !== 'primary_from' && $key !== 'primary_to' && $key !== 'compare_days' && $key !== 'compare_from' && $key !== 'compare_to' && $key !== 'report_area' && $key !== 'report_scope')
                        <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                    @endif
                @endforeach

                <div class="reports-filter-container rv2-filter" style="width: 280px; min-height: 90px; display: flex; flex-direction: column;">
                    <div class="reports-range-label" style="height: 1.6em; display: flex; align-items: center;">PERIOD</div>
                    <div style="flex: 1; display: flex; align-items: flex-end;">
                        <select name="days" class="rv2-filter-select" id="rv2PrimarySelect" style="display: {{ request('primary_from') || request('primary_to') ? 'none' : 'block' }}; width: 100%;">
                            @php $primaryDays = (int) request('days', $chartDays ?? 90); @endphp
                            <option value="30" {{ $primaryDays === 30 ? 'selected' : '' }}>Last 30 days</option>
                            <option value="60" {{ $primaryDays === 60 ? 'selected' : '' }}>Last 60 days</option>
                            <option value="90" {{ $primaryDays === 90 ? 'selected' : '' }}>Last 90 days</option>
                            <option value="custom" {{ request('primary_from') || request('primary_to') ? 'selected' : '' }}>Custom range…</option>
                        </select>
                        <div id="rv2PrimaryRangeInline" class="reports-range-grid" style="display: {{ request('primary_from') || request('primary_to') ? 'grid' : 'none' }}; width: 100%; min-width: 0; gap: 10px;">
                            <div class="reports-range-col">
                                <label class="reports-range-label" style="font-size: 9px; opacity: 0.8;">Starting</label>
                                <input type="date" name="primary_from" id="rv2PrimaryFrom" value="{{ request('primary_from', now()->subMonth()->format('Y-m-d')) }}" class="reports-range-input" style="width: 100%;">
                            </div>
                            <div class="reports-range-col">
                                <label class="reports-range-label" style="font-size: 9px; opacity: 0.8;">Ending</label>
                                <input type="date" name="primary_to" id="rv2PrimaryTo" value="{{ request('primary_to', now()->format('Y-m-d')) }}" class="reports-range-input" style="width: 100%;">
                            </div>
                            <button type="button" class="reports-range-back-btn rv2-range-reset" data-target="rv2PrimarySelect" style="right: 4px; top: 4px;">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="reports-filter-container rv2-filter" style="width: 280px; min-height: 90px; display: flex; flex-direction: column;">
                    <div class="reports-range-label" style="height: 1.6em; display: flex; align-items: center;">COMPARE AGAINST</div>
                    <div style="flex: 1; display: flex; align-items: flex-end;">
                        @php $compareDays = (int) request('compare_days', 30); @endphp
                        <select name="compare_days" class="rv2-filter-select" id="rv2CompareSelect" style="display: {{ request('compare_from') || request('compare_to') ? 'none' : 'block' }}; width: 100%;">
                            <option value="30" {{ $compareDays === 30 ? 'selected' : '' }}>Last 30 days</option>
                            <option value="60" {{ $compareDays === 60 ? 'selected' : '' }}>Last 60 days</option>
                            <option value="90" {{ $compareDays === 90 ? 'selected' : '' }}>Last 90 days</option>
                            <option value="custom" {{ request('compare_from') || request('compare_to') ? 'selected' : '' }}>Custom range…</option>
                        </select>
                        <div id="rv2CompareRangeInline" class="reports-range-grid" style="display: {{ request('compare_from') || request('compare_to') ? 'grid' : 'none' }}; width: 100%; min-width: 0; gap: 10px;">
                            <div class="reports-range-col">
                                <label class="reports-range-label" style="font-size: 9px; opacity: 0.8;">Starting</label>
                                <input type="date" name="compare_from" id="rv2CompareFrom" value="{{ request('compare_from', now()->subMonths(2)->format('Y-m-d')) }}" class="reports-range-input" style="width: 100%;">
                            </div>
                            <div class="reports-range-col">
                                <label class="reports-range-label" style="font-size: 9px; opacity: 0.8;">Ending</label>
                                <input type="date" name="compare_to" id="rv2CompareTo" value="{{ request('compare_to', now()->subMonth()->format('Y-m-d')) }}" class="reports-range-input" style="width: 100%;">
                            </div>
                            <button type="button" class="reports-range-back-btn rv2-range-reset" data-target="rv2CompareSelect" style="right: 4px; top: 4px;">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="reports-filter-container rv2-filter" style="width: 170px; min-height: 60px; display: flex; flex-direction: column;">
                    <div class="reports-range-label" style="height: 1.6em; display: flex; align-items: center; white-space: nowrap;">DEALER SCOPE</div>
                    <div style="flex: 1; display: flex; align-items: flex-end;">
                        <div style="width: 100%; max-width: 100%; --report-scope-picker-width: 100%;">
                            @include('admin.partials.report_scope_picker', [
                                'options' => $reportScopeOptions ?? [],
                                'selected' => $selectedReportScope ?? 'all',
                            ])
                        </div>
                    </div>
                </div>

                <div class="reports-filter-container rv2-filter" style="width: 170px; min-height: 60px; display: flex; flex-direction: column;">
                    <div class="reports-range-label" style="height: 1.6em; display: flex; align-items: center;">AREA</div>
                    <div class="report-scope-field" style="flex: 1; display: flex; align-items: flex-end; width: 100%;">
                        <select name="report_area" id="adminSalesAreaSelect" class="report-scope-select" aria-label="Select area" style="width: 100%; font-size: 13px;">
                            <option value="all">All</option>
                            @foreach($areaOptions ?? [] as $area)
                                <option value="{{ $area }}" {{ ($selectedArea ?? '') === $area ? 'selected' : '' }}>{{ $area }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="reports-period-actions report-filter-actions" style="margin-left: auto; align-self: flex-end; padding-bottom: 8px;">
                    @include('admin.partials.report_filter_actions', [
                        'wrapperClass' => 'reports-period-actions-inner',
                        'applyClass' => 'report-filter-apply',
                        'exportClass' => 'report-filter-export',
                        'clearClass' => 'report-filter-clear',
                        'showApply' => false,
                        'showExport' => true,
                        'showClear' => false,
                        'exportTitle' => 'Dealer Sales Overtime Report',
                        'exportTarget' => '.rv2-page',
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
            // Title dropdown
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

            // Log Intervention popout
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

            const autoSubmitReportForms = document.querySelectorAll('[data-auto-submit-report-filters]');
            autoSubmitReportForms.forEach(function (form) {
                if (!form || form.dataset.autoSubmitReady === '1') {
                    return;
                }

                form.dataset.autoSubmitReady = '1';
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

                form.addEventListener('submit', markReportFiltersSubmitting);

                const getRangeWrapper = function (select) {
                    const filter = select.closest('.rv2-filter');
                    return filter ? filter.querySelector('.reports-range-grid') : null;
                };

                const syncFormCustomState = function () {
                    const anyCustom = Array.prototype.some.call(
                        form.querySelectorAll('select[name="days"], select[name="compare_days"]'),
                        function (s) { return s.value === 'custom'; }
                    );
                    form.classList.toggle('rv2-has-custom-range', anyCustom);
                };

                const syncRange = function (select) {
                    const wrapper = getRangeWrapper(select);
                    if (!wrapper) return;

                    const isCustom = select.value === 'custom';
                    wrapper.style.display = isCustom ? 'grid' : 'none';
                    
                    if (isCustom) {
                        select.style.display = 'none';
                        if (select.tomselect) select.tomselect.wrapper.style.display = 'none';
                    } else {
                        if (select.tomselect) select.tomselect.wrapper.style.display = 'block';
                        else select.style.display = 'block';
                    }
                    
                    const inputs = wrapper.querySelectorAll('input[type="date"]');
                    if (inputs.length === 2) {
                        inputs[0].disabled = inputs[1].disabled = !isCustom;
                        inputs[0].required = inputs[1].required = isCustom;
                        if (!isCustom) {
                            inputs[1].min = '';
                        } else {
                            if (!inputs[0].value) inputs[0].value = "{{ now()->subMonth()->format('Y-m-d') }}";
                            if (!inputs[1].value) inputs[1].value = "{{ now()->format('Y-m-d') }}";
                            inputs[1].min = inputs[0].value || '';
                        }
                    }
                    syncFormCustomState();
                };

                const isFormReadyToSubmit = function () {
                    return Array.prototype.every.call(form.querySelectorAll('select[name="days"], select[name="compare_days"]'), function (select) {
                        const wrapper = getRangeWrapper(select);
                        if (!wrapper || select.value !== 'custom') return true;
                        const inputs = wrapper.querySelectorAll('input[type="date"]');
                        const from = inputs[0] ? inputs[0].value : '';
                        const to = inputs[1] ? inputs[1].value : '';
                        return from !== '' && to !== '' && from <= to;
                    });
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

                form.querySelectorAll('select[name="days"], select[name="compare_days"], select[name="report_scope"], select[name="report_area"]').forEach(function (select) {
                    if (select.name === 'days' || select.name === 'compare_days') {
                        syncRange(select);
                    }
                    select.addEventListener('change', function () {
                        if (select.name === 'days' || select.name === 'compare_days') {
                            syncRange(select);
                        }
                        submitReportFilters();
                    });

                    const bindTomSelectChange = function () {
                        if (!select.tomselect || select.dataset.autoSubmitTomSelectReady === '1') return;
                        select.dataset.autoSubmitTomSelectReady = '1';
                        select.tomselect.on('change', submitReportFilters);
                    };
                    bindTomSelectChange();
                    window.setTimeout(bindTomSelectChange, 120);
                    window.setTimeout(bindTomSelectChange, 360);
                });


                // Area searchable dropdown
                const areaSelectEl = document.getElementById('adminSalesAreaSelect');
                if (areaSelectEl && typeof TomSelect === 'function') {
                    new TomSelect(areaSelectEl, {
                        maxItems: 1,
                        hideSelected: false,
                        plugins: ['dropdown_input'],
                        maxOptions: 100,
                        searchField: 'text',
                        placeholder: 'Search city...',
                        allowEmptyOption: false,
                        copyClassesToDropdown: false,
                        onDropdownOpen: function() { this.clearCache(); },
                        onChange: function() { submitReportFilters(); },
                        onInitialize: function () {
                            if (this.wrapper) {
                                this.wrapper.classList.add('report-scope-ts-wrapper');
                                this.wrapper.id = 'adminSalesAreaSelect-ts-wrapper';
                            }
                            if (this.control) this.control.classList.add('report-scope-ts-control');
                            if (this.dropdown) this.dropdown.classList.add('report-scope-ts-dropdown');
                        }
                    });
                }

                form.querySelectorAll('.rv2-range-reset').forEach(function(btn) {
                    btn.addEventListener('click', function() {
                        const targetId = btn.getAttribute('data-target');
                        const select = document.getElementById(targetId);
                        if (select) {
                            select.value = '30';
                            syncRange(select);
                            submitReportFilters();
                        }
                    });
                });

                form.querySelectorAll('.reports-range-grid').forEach(function(grid) {
                    const inputs = grid.querySelectorAll('input[type="date"]');
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
                });
            });

            // ——— Charts ———
            const top10Failed = @json($top10Failed ?? []);
            const top10Closed = @json($top10Closed ?? []);
            const BAR_RED = 'rgba(239, 68, 68, 0.96)';
            const BAR_CLOSED = 'rgba(34, 197, 94, 0.94)';
            const isDarkTheme = document.documentElement.classList.contains('theme-dark');
            const isMobile = window.matchMedia('(max-width: 768px)').matches;
            const chartLabelFontSize = isMobile ? 8 : 10;
            const chartBarThickness = isMobile ? 16 : 24;

            if (window.Chart && typeof window.ChartDataLabels !== 'undefined') {
                window.Chart.register(window.ChartDataLabels);
            }

            const rowCount = Math.max(top10Failed.length, top10Closed.length, 0);
            const failedName = Array.from({ length: rowCount }, (_, i) => (top10Failed[i]?.name ?? '—'));
            const closedName = Array.from({ length: rowCount }, (_, i) => (top10Closed[i]?.name ?? '—'));
            const failedPct = Array.from({ length: rowCount }, (_, i) => top10Failed[i]?.percentage ?? 0);
            const closedPct = Array.from({ length: rowCount }, (_, i) => top10Closed[i]?.percentage ?? 0);

            const labels = Array.from({ length: rowCount }, (_, i) => `#${i + 1}`);
            const data = {
                labels: labels,
                datasets: [
                    {
                        label: 'Failed',
                        data: Array.from({ length: rowCount }, (_, i) => -Math.min(100, Math.abs(failedPct[i] ?? 0))),
                        backgroundColor: BAR_RED,
                        borderRadius: 10,
                        barThickness: chartBarThickness,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Closed',
                        data: Array.from({ length: rowCount }, (_, i) => Math.min(100, Math.abs(closedPct[i] ?? 0))),
                        backgroundColor: BAR_CLOSED,
                        borderRadius: 10,
                        barThickness: chartBarThickness,
                        yAxisID: 'yRight'
                    }
                ]
            };

            const config = {
                type: 'bar',
                data: data,
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
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
                            color: '#fff',
                            font: { size: chartLabelFontSize, weight: '800' },
                            formatter: function(_v, ctx) {
                                const i = ctx.dataIndex;
                                return ctx.dataset.label === 'Failed' ? `${failedPct[i]}%` : `${closedPct[i]}%`;
                            }
                        }
                    },
                    scales: {
                        x: {
                            min: -100,
                            max: 100,
                            ticks: { callback: function(v) { return Math.abs(v); } }
                        },
                        y: {
                            position: 'left',
                            ticks: {
                                callback: function(value, index) {
                                    return failedName[index] ?? '—';
                                }
                            }
                        },
                        yRight: {
                            position: 'right',
                            grid: { display: false },
                            ticks: {
                                callback: function(value, index) {
                                    return closedName[index] ?? '—';
                                }
                            }
                        }
                    }
                }
            };

            const el = document.getElementById('top10FailedClosedChart');
            if (el && window.Chart) {
                new Chart(el.getContext('2d'), config);
            }
        });
    </script>
@endpush
