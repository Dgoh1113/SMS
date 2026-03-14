@extends('layouts.app')
@section('title', 'Report - Dealer Revenue Production')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/report_dealer_revenue_production.css') }}">
@endpush
@section('content')
<div class="rrp-page">
    <header class="rrp-header">
        <div class="rrp-header-left">
            <div class="rrp-title-eyebrow">Quarterly Overview</div>
            <div id="reportsTitleHover" class="reports-title-hover">
                <div class="reports-title-dropdown">
                    <button id="dropdownHoverButton" data-dropdown-toggle="dropdownHover" data-dropdown-trigger="hover" class="reports-dropdown-btn" type="button">
                        Report - Dealer Revenue Production
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
                <div class="rrp-title-sub">Managing growth and partner efficiency for {{ $selectedQuarter ?? 'Q3' }}, {{ $selectedYear ?? now()->format('Y') }}.</div>
            </div>
        </div>
        <div class="rrp-header-right">
            <div class="rrp-dd">
                <button type="button" class="rrp-pill" id="rrpQuarterBtn">
                    {{ $selectedQuarter ?? 'Q3' }}
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
                        <path d="m19 9-7 7-7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
                <div class="rrp-dd-menu" id="rrpQuarterMenu">
                    @foreach (['Q1','Q2','Q3','Q4'] as $q)
                        <button type="button" class="rrp-dd-item" data-quarter="{{ $q }}">{{ $q }}</button>
                    @endforeach
                </div>
            </div>
            <div class="rrp-dd">
                <button type="button" class="rrp-pill" id="rrpYearBtn">
                    {{ $selectedYear ?? now()->format('Y') }}
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
                        <path d="m19 9-7 7-7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
                <div class="rrp-dd-menu" id="rrpYearMenu">
                    @foreach (($yearOptions ?? []) as $y)
                        <button type="button" class="rrp-dd-item" data-year="{{ $y }}">{{ $y }}</button>
                    @endforeach
                </div>
            </div>
        </div>
    </header>

    <section class="rrp-top-grid">
        <div class="rrp-metric-card">
            <div class="rrp-metric-label">Total Volume Accepted</div>
            <div class="rrp-metric-value">{{ number_format($totalVolume) }}</div>
            <div class="rrp-metric-sub">Total leads assigned to dealers in {{ $selectedQuarter ?? 'Q3' }}, {{ $selectedYear ?? now()->format('Y') }}</div>
        </div>
        <div class="rrp-metric-card">
            <div class="rrp-metric-label">Avg Rejection Rate</div>
            <div class="rrp-metric-value">{{ number_format($avgRejectionRate, 1) }}%</div>
            <div class="rrp-metric-sub">Across active dealers in {{ $selectedQuarter ?? 'Q3' }}, {{ $selectedYear ?? now()->format('Y') }}</div>
        </div>
        <div class="rrp-metric-card">
            <div class="rrp-metric-label">Top Performing Dealer</div>
            <div class="rrp-metric-value">{{ $topDealer['email'] ?? '—' }}</div>
            <div class="rrp-metric-sub">Highest revenue (closed volume)</div>
        </div>
    </section>

    <section class="rrp-panel">
        <div class="rrp-panel-header">
            <div>
                <div class="rrp-panel-title">Volume vs. Revenue Analysis</div>
                <div class="rrp-panel-subtitle">Performance by leading dealer branches</div>
            </div>
            <div class="rrp-legend">
                <span class="rrp-legend-item"><span class="rrp-dot rrp-dot-purple"></span> Total Volume</span>
                <span class="rrp-legend-item"><span class="rrp-dot rrp-dot-gold"></span> Closed Volume</span>
                <span class="rrp-legend-item"><span class="rrp-dot rrp-dot-green"></span> Rewarded Only</span>
            </div>
        </div>
        <div class="rrp-panel-body">
            <canvas id="rrpVolumeChart"></canvas>
        </div>
    </section>

    <section class="rrp-panel">
        <div class="rrp-panel-header">
            <div>
                <div class="rrp-panel-title">Dealer Rankings</div>
                <div class="rrp-panel-subtitle">Sorted by total revenue (closed volume × 1,000)</div>
            </div>
            <div class="rrp-pill rrp-pill-filter">
                All Territories
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
                    <path d="m19 9-7 7-7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
        </div>
        <div class="rrp-panel-body">
            <div class="table-responsive">
                <table class="dashboard-table rrp-table">
                    <thead>
                        <tr>
                            <th>Dealer</th>
                            <th>Total Leads</th>
                            <th>Agreements Closed</th>
                            <th>Rejection Rate</th>
                            <th>Total Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rankings as $row)
                            <tr>
                                <td>
                                    <div class="rrp-dealer-name">{{ $row['email'] }}</div>
                                    <div class="rrp-dealer-id">ID: {{ $row['dealer_id'] }}</div>
                                </td>
                                <td>{{ number_format($row['total']) }}</td>
                                <td>{{ number_format($row['closed']) }}</td>
                                <td>{{ number_format($row['rejection_rate'], 1) }}%</td>
                                <td>${{ number_format($row['revenue']) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5">No dealer data for the selected period.</td></tr>
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
            // Quarter/Year dropdowns -> reload with query params
            const quarterBtn = document.getElementById('rrpQuarterBtn');
            const quarterMenu = document.getElementById('rrpQuarterMenu');
            const yearBtn = document.getElementById('rrpYearBtn');
            const yearMenu = document.getElementById('rrpYearMenu');

            const toggleMenu = (menu) => menu && menu.classList.toggle('is-open');
            const closeMenus = () => {
                if (quarterMenu) quarterMenu.classList.remove('is-open');
                if (yearMenu) yearMenu.classList.remove('is-open');
            };

            if (quarterBtn && quarterMenu) {
                quarterBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    toggleMenu(quarterMenu);
                    if (yearMenu) yearMenu.classList.remove('is-open');
                });
            }
            if (yearBtn && yearMenu) {
                yearBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    toggleMenu(yearMenu);
                    if (quarterMenu) quarterMenu.classList.remove('is-open');
                });
            }

            const currentQuarter = quarterBtn ? quarterBtn.textContent.trim() : 'Q3';
            const currentYear = yearBtn ? yearBtn.textContent.trim() : String(new Date().getFullYear());

            const apply = (q, y) => {
                const url = new URL(window.location.href);
                url.searchParams.set('quarter', q);
                url.searchParams.set('year', y);
                window.location.href = url.toString();
            };

            document.querySelectorAll('.rrp-dd-item[data-quarter]').forEach((btn) => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    apply(btn.dataset.quarter, currentYear);
                });
            });
            document.querySelectorAll('.rrp-dd-item[data-year]').forEach((btn) => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    apply(currentQuarter, btn.dataset.year);
                });
            });

            document.addEventListener('click', () => closeMenus());
            window.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeMenus(); });

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

            // Total bar first (main), then Closed and Rewarded; same categoryPercentage so each dealer's bars stick together
            const data = {
                labels: labels,
                datasets: [
                    {
                        label: 'Total Volume',
                        data: volume,
                        backgroundColor: 'rgba(124, 58, 237, 0.9)',
                        barPercentage: 0.85,
                        categoryPercentage: 0.9,
                    },
                    {
                        label: 'Closed Volume',
                        data: closed,
                        backgroundColor: 'rgba(234, 179, 8, 0.9)',
                        barPercentage: 0.85,
                        categoryPercentage: 0.9,
                    },
                    {
                        label: 'Rewarded Only',
                        data: rewarded,
                        backgroundColor: 'rgba(34, 197, 94, 0.9)',
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
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: false,
                        },
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: '#e5e7eb' },
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

