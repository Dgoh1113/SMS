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

                    <!-- Dropdown menu -->
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

            <button type="button" class="rv2-export-btn">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 3v12m0 0 4-4m-4 4-4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M4 17v3h16v-3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                Export CSV
            </button>
        </div>
    </header>

    <section class="rv2-panel">
        <div class="rv2-panel-top">
            <div class="rv2-filters">
                <div class="rv2-filter">
                    <div class="rv2-filter-label">PRIMARY PERIOD</div>
                    <button class="rv2-filter-btn" type="button">Last 90 Days
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
                            <path d="m19 9-7 7-7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
                <div class="rv2-filter">
                    <div class="rv2-filter-label">COMPARE AGAINST</div>
                    <button class="rv2-filter-btn" type="button">Same Period Last Year
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
                            <path d="m19 9-7 7-7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
                <div class="rv2-filter">
                    <div class="rv2-filter-label">ANALYSIS RANGE</div>
                    <button class="rv2-filter-btn" type="button">Nov 29 - Feb 27 vs Same Dates 2024
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
                            <path d="m19 9-7 7-7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <div class="rv2-panel-body">
            <div class="rv2-variance-header">
                <div>
                    <div class="rv2-section-title">Top 10 Performance Variance</div>
                    <div class="rv2-section-subtitle">Sales volume change vs Same Period Last Year (Seasonality Adjusted)</div>
                </div>
                <div class="rv2-legend">
                    <span class="rv2-legend-item"><span class="rv2-dot rv2-dot-red"></span> Drop-off</span>
                    <span class="rv2-legend-item"><span class="rv2-dot rv2-dot-green"></span> Growth</span>
                </div>
            </div>

            <div class="rv2-variance-grid">
                <div class="rv2-side-card">
                    <div class="rv2-side-title">Highest Closed Rate</div>
                    <div class="rv2-side-name">{{ $highestClosed['email'] ?? '—' }}</div>
                    <div class="rv2-side-metric">{{ isset($highestClosed['closed_rate']) ? number_format($highestClosed['closed_rate'], 1) : '0.0' }}%</div>
                </div>

                <div class="rv2-chart-wrap">
                    <canvas id="varianceChart"></canvas>
                </div>

                <div class="rv2-side-card">
                    <div class="rv2-side-title">Highest Rejection Rate</div>
                    <div class="rv2-side-name">{{ $highestRejected['email'] ?? '—' }}</div>
                    <div class="rv2-side-metric">{{ isset($highestRejected['rejection_rate']) ? number_format($highestRejected['rejection_rate'], 1) : '0.0' }}%</div>
                </div>
            </div>
        </div>
    </section>

    <section class="rv2-panel">
        <div class="rv2-panel-head">
            <div>
                <div class="rv2-section-title">Action List: At-Risk Dealers</div>
                <div class="rv2-section-subtitle">Context: Core Accounting | Comparison: SAME PERIOD LAST YEAR</div>
            </div>
            <div class="rv2-badge-danger">8 CRITICAL DROPS</div>
        </div>

        <div class="rv2-table-wrap">
            <table class="rv2-table">
                <thead>
                    <tr>
                        <th>DEALER NAME</th>
                        <th>COMP. PERIOD</th>
                        <th>PRIMARY PERIOD</th>
                        <th>SEASONAL VARIANCE</th>
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
                            <td><span class="rv2-muted">{{ $r['comp'] ? number_format($r['comp']) : '—' }}</span> <span class="rv2-muted-xs">Core Units</span></td>
                            <td><span class="rv2-muted">{{ $r['primary'] ? number_format($r['primary']) : '—' }}</span> <span class="rv2-muted-xs">Core Units</span></td>
                            <td>
                                <div class="rv2-variance-val">{{ $r['variance'] }}%</div>
                                <div class="rv2-variance-sub">Drop-off vs last year</div>
                            </td>
                            <td><span class="rv2-pill-warn">{{ $r['last_activity'] }}</span></td>
                            <td><button class="rv2-action-btn" type="button">Log Intervention</button></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="rv2-table-footer">
                <div class="rv2-muted-xs">Showing 1 to {{ count($atRisk) }} of 28 dealers at risk</div>
                <div class="rv2-pagination">
                    <button class="rv2-page-btn" type="button">Previous</button>
                    <button class="rv2-page-btn rv2-page-active" type="button">1</button>
                    <button class="rv2-page-btn" type="button">2</button>
                    <button class="rv2-page-btn" type="button">3</button>
                    <button class="rv2-page-btn" type="button">Next</button>
                </div>
            </div>
        </div>
    </section>

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
            // Dynamic rows (server data)
            const rows = @json($topVariance);

            // Minimal Utils (so your snippet works as-is)
            const Utils = {
                rand(min, max) { return Math.floor(Math.random() * (max - min + 1)) + min; },
                numbers(cfg) {
                    const out = [];
                    for (let i = 0; i < (cfg.count || 0); i++) out.push(this.rand(cfg.min ?? 0, cfg.max ?? 100));
                    return out;
                },
                months({count}) {
                    const out = [];
                    for (let i = 0; i < count; i++) out.push(`Item ${i + 1}`);
                    return out;
                },
                namedColor(i) {
                    const colors = ['#ef4444', '#3b82f6', '#22c55e', '#a855f7', '#f59e0b', '#14b8a6', '#6366f1'];
                    return colors[i % colors.length];
                },
                transparentize(hex, opacity) {
                    const h = hex.replace('#', '');
                    const r = parseInt(h.substring(0, 2), 16);
                    const g = parseInt(h.substring(2, 4), 16);
                    const b = parseInt(h.substring(4, 6), 16);
                    return `rgba(${r}, ${g}, ${b}, ${opacity})`;
                },
                CHART_COLORS: {
                    red: '#ef4444',
                    blue: '#3b82f6',
                }
            };

            const actions = [
                {
                    name: 'Randomize',
                    handler(chart) {
                        chart.data.datasets.forEach(dataset => {
                            dataset.data = Utils.numbers({count: chart.data.labels.length, min: -100, max: 100});
                        });
                        chart.update();
                    }
                },
                {
                    name: 'Add Dataset',
                    handler(chart) {
                        const data = chart.data;
                        const dsColor = Utils.namedColor(chart.data.datasets.length);
                        const newDataset = {
                            label: 'Dataset ' + (data.datasets.length + 1),
                            backgroundColor: Utils.transparentize(dsColor, 0.5),
                            borderColor: dsColor,
                            borderWidth: 1,
                            data: Utils.numbers({count: data.labels.length, min: -100, max: 100}),
                        };
                        chart.data.datasets.push(newDataset);
                        chart.update();
                    }
                },
                {
                    name: 'Add Data',
                    handler(chart) {
                        const data = chart.data;
                        if (data.datasets.length > 0) {
                            data.labels = Utils.months({count: data.labels.length + 1});

                            for (let index = 0; index < data.datasets.length; ++index) {
                                data.datasets[index].data.push(Utils.rand(-100, 100));
                            }

                            chart.update();
                        }
                    }
                },
                {
                    name: 'Remove Dataset',
                    handler(chart) {
                        chart.data.datasets.pop();
                        chart.update();
                    }
                },
                {
                    name: 'Remove Data',
                    handler(chart) {
                        chart.data.labels.splice(-1, 1); // remove the label first

                        chart.data.datasets.forEach(dataset => {
                            dataset.data.pop();
                        });

                        chart.update();
                    }
                }
            ];

            // Use your variables, but feed dynamic data
            const DATA_COUNT = rows.length || 7;
            const NUMBER_CFG = {count: DATA_COUNT, min: -100, max: 100};

            const labels = rows.length ? rows.map(r => r.name) : Utils.months({count: 7});
            const dataset1 = rows.length ? rows.map(r => (r.delta < 0 ? r.delta : 0)) : Utils.numbers(NUMBER_CFG);
            const dataset2 = rows.length ? rows.map(r => (r.delta > 0 ? r.delta : 0)) : Utils.numbers(NUMBER_CFG);

            const data = {
                labels: labels,
                datasets: [
                    {
                        label: 'Dataset 1',
                        data: dataset1,
                        borderColor: Utils.CHART_COLORS.red,
                        backgroundColor: Utils.transparentize(Utils.CHART_COLORS.red, 0.5),
                    },
                    {
                        label: 'Dataset 2',
                        data: dataset2,
                        borderColor: Utils.CHART_COLORS.blue,
                        backgroundColor: Utils.transparentize(Utils.CHART_COLORS.blue, 0.5),
                    }
                ]
            };

            const config = {
                type: 'bar',
                data: data,
                options: {
                    indexAxis: 'y',
                    // Elements options apply to all of the options unless overridden in a dataset
                    // In this case, we are setting the border of each horizontal bar to be 2px wide
                    elements: {
                        bar: {
                            borderWidth: 2,
                        }
                    },
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'right',
                        },
                        title: {
                            display: true,
                            text: 'Chart.js Horizontal Bar Chart'
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { callback: (v) => `${v}%` }
                        },
                        y: { grid: { display: false } }
                    }
                },
            };

            const el = document.getElementById('varianceChart');
            if (el && window.Chart) {
                const chart = new Chart(el.getContext('2d'), config);
                // actions[] is available if you want to wire buttons later
                void(actions);
                void(chart);
            }
        });
    </script>
@endpush

