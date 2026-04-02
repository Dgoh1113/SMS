@extends('layouts.app')
@section('title', 'Report - Dealer Revenue Production')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/shared/reports-tabs.css') }}?v=20260402-8">
    <link rel="stylesheet" href="{{ asset('css/report_dealer_revenue_production.css') }}?v=20260402-2">
@endpush
@section('content')
<div class="rrp-page">
    @php
        $reportTabQuery = [];
        $currentReportScope = trim((string) ($selectedReportScope ?? request('report_scope', '')));
        if ($currentReportScope !== '') {
            $reportTabQuery['report_scope'] = $currentReportScope;
        }
    @endphp
    <div class="reports-tabs-row rrp-tabs-row">
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

    <div class="rrp-filter-row">
        @php
            $currentQuarter = 'Q' . (int) ceil(((int) now()->format('n')) / 3);
            $clearRevenueFiltersUrl = route('admin.reports.revenue', [
                'quarter' => $currentQuarter,
                'year' => (int) now()->format('Y'),
                'report_scope' => 'all',
            ]);
        @endphp
        <form method="GET" class="rrp-filter-form">
            <select name="quarter" class="rrp-filter-select">
                @foreach (['Q1', 'Q2', 'Q3', 'Q4'] as $q)
                    <option value="{{ $q }}" {{ ($selectedQuarter ?? 'Q1') === $q ? 'selected' : '' }}>{{ $q }}</option>
                @endforeach
            </select>
            <select name="year" class="rrp-filter-select">
                @foreach (($yearOptions ?? []) as $y)
                    <option value="{{ $y }}" {{ (int) ($selectedYear ?? now()->format('Y')) === (int) $y ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
            @include('admin.partials.report_scope_picker', [
                'options' => $reportScopeOptions ?? [],
                'selected' => $selectedReportScope ?? 'all',
            ])
            @include('admin.partials.report_filter_actions', [
                'clearUrl' => $clearRevenueFiltersUrl,
                'wrapperClass' => 'rrp-filter-actions report-filter-actions',
                'applyClass' => 'report-filter-apply',
                'clearClass' => 'report-filter-clear',
            ])
        </form>
    </div>

    <section class="rrp-top-grid">
        <div class="rrp-metric-card">
            <div class="rrp-metric-label">Total Inquiries</div>
            <div class="rrp-metric-value">{{ number_format($totalVolume) }}</div>
            <div class="rrp-metric-sub">Total leads assigned to dealers in {{ $selectedQuarter ?? 'Q3' }}, {{ $selectedYear ?? now()->format('Y') }}</div>
        </div>
        <div class="rrp-metric-card">
            <div class="rrp-metric-label">Average Fail Rate</div>
            <div class="rrp-metric-value">{{ number_format($avgRejectionRate, 1) }}%</div>
            <div class="rrp-metric-sub">Across active dealers in {{ $selectedQuarter ?? 'Q3' }}, {{ $selectedYear ?? now()->format('Y') }}</div>
        </div>
        <div class="rrp-metric-card">
            <div class="rrp-metric-label">Top Dealer by Product Conversion</div>
            <div class="rrp-metric-value">{{ $topProductDealer['name'] ?? '-' }}</div>
            <div class="rrp-metric-sub">
                {{ isset($topProductDealer['converted_products']) ? number_format((int) $topProductDealer['converted_products']) : 0 }} converted products in {{ $selectedQuarter ?? 'Q1' }}, {{ $selectedYear ?? now()->format('Y') }}
            </div>
        </div>
    </section>

    <section class="rrp-panel">
        <div class="rrp-panel-header">
            <div>
                <div class="rrp-panel-title">Dealer Volume vs Outcomes</div>
                <div class="rrp-panel-subtitle">Top dealers for {{ $selectedQuarter ?? 'Q1' }}, {{ $selectedYear ?? now()->format('Y') }}</div>
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
                <div class="rrp-panel-subtitle">Sorted by closed products in {{ $selectedQuarter ?? 'Q1' }}, {{ $selectedYear ?? now()->format('Y') }}</div>
            </div>
            <div class="rrp-pill rrp-pill-purple">{{ $selectedQuarter ?? 'Q1' }} {{ $selectedYear ?? now()->format('Y') }}</div>
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
            const labels = @json($chartLabels);
            const volume = @json($chartVolume);
            const closed = @json($chartClosed);
            const rewarded = @json($chartRewarded);
            const isMobile = window.matchMedia('(max-width: 768px)').matches;
            const chartFontSize = isMobile ? 10 : 12;
            const chartBarThickness = isMobile ? 16 : undefined;

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
                                color: '#475569',
                                usePointStyle: true,
                                boxWidth: 8,
                                font: { size: chartFontSize }
                            }
                        },
                        title: {
                            display: false,
                        },
                        tooltip: {
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
                                color: '#475569',
                                font: { size: chartFontSize },
                                maxRotation: isMobile ? 40 : 0,
                                minRotation: isMobile ? 40 : 0,
                            },
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(148, 163, 184, 0.25)' },
                            ticks: {
                                precision: 0,
                                color: '#64748b',
                                font: { size: chartFontSize },
                            },
                            title: {
                                display: true,
                                text: 'Number of inquiries',
                                color: '#64748b',
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

