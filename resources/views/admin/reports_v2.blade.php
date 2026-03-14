@extends('layouts.app')
@section('title', 'Report - Dealer Sales Overtime')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/report_dealer_sales_overtime.css') }}">
@endpush
@section('content')
<div class="rv2-page">
    <header class="rv2-header">
        <div class="rv2-header-left">
            <div id="reportsTitleHover" class="reports-title-hover">
                <div class="reports-title-dropdown">
                    <button id="dropdownHoverButton" data-dropdown-toggle="dropdownHover" data-dropdown-trigger="hover" class="reports-dropdown-btn" type="button">
                        Report - Dealer Sales Overtime
                        <svg class="reports-dropdown-caret" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7"/></svg>
                    </button>

                    <div id="dropdownHover" class="reports-dropdown-menu" role="menu" aria-labelledby="dropdownHoverButton">
                        <ul class="reports-dropdown-list">
                            <li><a href="{{ route('admin.reports') }}" class="reports-dropdown-item">Report - Monthly Performance Analytics</a></li>
                            <li><a href="{{ route('admin.reports.v2') }}" class="reports-dropdown-item">Report - Dealer Sales Overtime</a></li>
                            <li><a href="{{ route('admin.reports.revenue') }}" class="reports-dropdown-item">Report - Dealer Revenue Production</a></li>
                        </ul>
                    </div>
                </div>
                <div class="rv2-subtitle">Identifying at-risk partners with seasonality and trends</div>
            </div>
        </div>

        <div class="rv2-header-right">
            <div class="rv2-search">
                <svg class="rv2-search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
                    <path d="M21 21l-4.3-4.3m1.8-5.2a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                <input class="rv2-search-input" type="text" placeholder="Search Dealer Name or ID..." />
            </div>

            <div class="rv2-pill">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
                    <path d="M4 7h16M4 12h10M4 17h16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                <span>Core Accounting</span>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
                    <path d="m19 9-7 7-7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>

        </div>
    </header>

    <div class="rv2-filtered-layer">
        <div class="rv2-filtered-layer-head">
            <span class="rv2-filtered-layer-label">Filter applies to chart and table below</span>
            <form method="GET" class="rv2-filters rv2-filters-form">
                @foreach(request()->query() as $key => $val)
                    @if($key !== 'days' && $key !== 'compare_days' && $key !== 'page' && $key !== 'primary_from' && $key !== 'primary_to' && $key !== 'compare_from' && $key !== 'compare_to')
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
                <div class="rv2-filter rv2-filter-apply">
                    <button type="submit" class="rv2-filter-btn">Apply</button>
                </div>
            </form>
        </div>

        <section class="rv2-panel rv2-panel-in-layer">
            <div class="rv2-panel-head">
                <div>
                    <div class="rv2-section-title">Top 5 Dealers — Failed vs Closed</div>
                    <div class="rv2-section-subtitle">Primary vs comparison period. Left: % failed (red). Right: % closed (green).</div>
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
                    <div class="rv2-section-title">Action List: At-Risk Dealers</div>
                    <div class="rv2-section-subtitle">Dealers with 30%+ increase in fail rate vs same period last year</div>
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
                            <th>ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($atRisk as $r)
                            <tr>
                                <td>
                                    <div class="rv2-dealer-name">{{ $r['name'] }}</div>
                                    <div class="rv2-dealer-id">{{ $r['id'] }}</div>
                                </td>
                                <td>
                                    <div class="rv2-variance-val">{{ number_format($r['increase_fail_rate'] ?? 0, 1) }}%</div>
                                    <div class="rv2-variance-sub">vs same period last year</div>
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
                                <td><button class="rv2-action-btn" type="button">Log Intervention</button></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @php
                    $atRiskTotal = $atRiskTotal ?? 0;
                    $atRiskPage = $atRiskPage ?? 1;
                    $atRiskPerPage = $atRiskPerPage ?? 10;
                    $atRiskTotalPages = $atRiskTotalPages ?? 1;
                    $from = $atRiskTotal === 0 ? 0 : ($atRiskPage - 1) * $atRiskPerPage + 1;
                    $to = min($atRiskPage * $atRiskPerPage, $atRiskTotal);
                    $query = request()->query();
                @endphp
                <div class="rv2-table-footer">
                    <div class="rv2-muted-xs">Showing {{ $from }} to {{ $to }} of {{ $atRiskTotal }} dealers at risk (30%+ increase)</div>
                    <div class="rv2-pagination">
                        @if($atRiskPage > 1)
                            <a href="{{ request()->url() . '?' . http_build_query(array_merge($query, ['page' => $atRiskPage - 1])) }}" class="rv2-page-btn">Previous</a>
                        @else
                            <span class="rv2-page-btn rv2-page-btn-disabled">Previous</span>
                        @endif
                        @for($p = 1; $p <= $atRiskTotalPages; $p++)
                            @if($p == $atRiskPage)
                                <span class="rv2-page-btn rv2-page-active">{{ $p }}</span>
                            @else
                                <a href="{{ request()->url() . '?' . http_build_query(array_merge($query, ['page' => $p])) }}" class="rv2-page-btn">{{ $p }}</a>
                            @endif
                        @endfor
                        @if($atRiskPage < $atRiskTotalPages)
                            <a href="{{ request()->url() . '?' . http_build_query(array_merge($query, ['page' => $atRiskPage + 1])) }}" class="rv2-page-btn">Next</a>
                        @else
                            <span class="rv2-page-btn rv2-page-btn-disabled">Next</span>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    </div>

    <section class="rv2-cards">
        <div class="rv2-mini-card">
            <div class="rv2-mini-top">
                <div class="rv2-mini-icon rv2-i-red">↘</div>
                <div class="rv2-mini-title">Seasonal Market Variance</div>
            </div>
            <div class="rv2-mini-value">-1,240 <span class="rv2-mini-unit">Core Units</span></div>
            <div class="rv2-mini-sub">12.4% below same period last year</div>
        </div>

        <div class="rv2-mini-card">
            <div class="rv2-mini-top">
                <div class="rv2-mini-icon rv2-i-purple">▣</div>
                <div class="rv2-mini-title">At-Risk Dealers</div>
            </div>
            <div class="rv2-mini-value">28 <span class="rv2-mini-unit">Partners</span></div>
            <div class="rv2-mini-sub">8 requiring urgent intervention</div>
        </div>

        <div class="rv2-mini-card">
            <div class="rv2-mini-top">
                <div class="rv2-mini-icon rv2-i-green">✓</div>
                <div class="rv2-mini-title">Recovery Score</div>
            </div>
            <div class="rv2-mini-value">72% <span class="rv2-mini-unit">Success Rate</span></div>
            <div class="rv2-mini-sub">Weighted by seasonal baseline</div>
        </div>
    </section>
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
            // Bar colors: Failed red, Closed green (dark border, light inner fill)
            const BAR_RED = 'rgba(220, 38, 38, 0.6)';
            const BAR_RED_BORDER = 'rgba(220, 38, 38, 1)';
            const BAR_CLOSED = 'rgba(34, 197, 94, 0.4)';
            const BAR_CLOSED_BORDER = 'rgba(22, 163, 74, 1)';

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
                        borderColor: Array.from({ length: rowCount }, () => BAR_RED_BORDER),
                        borderWidth: 1,
                        barThickness: 40,
                        maxBarThickness: 40
                    },
                    {
                        label: 'Closed',
                        yAxisID: 'y',
                        data: Array.from({ length: rowCount }, (_, i) => Math.min(100, Math.abs(closedPct[i] ?? 0))),
                        backgroundColor: Array.from({ length: rowCount }, () => BAR_CLOSED),
                        borderColor: Array.from({ length: rowCount }, () => BAR_CLOSED_BORDER),
                        borderWidth: 1,
                        barThickness: 40,
                        maxBarThickness:40
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
                                    font: { size: 10, weight: '700' },
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
                            grid: { color: 'rgba(148, 163, 184, 0.25)' },
                            ticks: {
                                stepSize: 20,
                                callback: function(v) { return Math.abs(v); }
                            },
                            title: { display: true, text: 'Percentage of cases', font: { size: 10 } }
                        },
                        // Left Sidebar Alignment (Failed Names)
                        y: {
                            position: 'left',
                            grid: { display: false, drawBorder: false },
                            ticks: {
                                autoSkip: false,
                                color: '#334155',
                                font: { size: 10, weight: '700' },
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
                                color: '#334155',
                                font: { size: 10, weight: '700' },
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
                // Approximate one "row" as barThickness (18) + gap (~8px).
                const perRow = 92;         // row height + gap (px)
                const minRows = 4;         // keep at least a reasonable height when few dealers
                const rowsForHeight = Math.max(rowCount, minRows);
                divergeEl.height = perRow * rowsForHeight;
                new Chart(divergeEl.getContext('2d'), config);
            }
        });
    </script>
@endpush