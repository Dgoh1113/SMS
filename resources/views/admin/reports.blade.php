@extends('layouts.app')
@section('title', 'Report - Monthly Performance Analytics')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/shared/reports-tabs.css') }}?v=20260409-1">
    <link rel="stylesheet" href="{{ asset('css/report_monthly_performance_analytics.css') }}?v=20260409-1">
    <style>
        .reports-page .dashboard-panels-two-column {
            display: grid;
            grid-template-columns: minmax(0, 1.35fr) minmax(0, 1fr);
            gap: 14px;
            min-width: 0;
        }

        .reports-page .dashboard-panels-two-column > *,
        .reports-page .reports-product-section {
            min-width: 0;
        }

        .reports-page .reports-inquiry-section,
        .reports-page .reports-status-section,
        .reports-page .reports-product-section {
            border: 1px solid #e8ecf5;
            border-radius: 18px;
            box-shadow: 0 10px 22px rgba(15, 23, 42, 0.05);
            overflow: hidden;
            background: #ffffff;
        }

        .reports-page .reports-inquiry-section .dashboard-panel-header,
        .reports-page .reports-status-section .dashboard-panel-header,
        .reports-page .reports-product-section .dashboard-panel-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 10px;
            padding: 16px 18px 8px;
            border-bottom: none;
        }

        .reports-inquiry-heading,
        .reports-status-heading,
        .reports-product-heading {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .reports-page .reports-inquiry-heading .dashboard-panel-title,
        .reports-page .reports-status-heading .dashboard-panel-title,
        .reports-page .reports-product-heading .dashboard-panel-title {
            margin: 0;
            font-size: 16px;
            font-weight: 700;
            line-height: 1.2;
            color: #0f172a;
            letter-spacing: -0.01em;
        }

        .reports-inquiry-subtitle,
        .reports-status-subtitle,
        .reports-product-subtitle {
            font-size: 11px;
            font-weight: 500;
            color: #64748b;
            line-height: 1.4;
        }

        .reports-inquiry-meta {
            display: inline-flex;
            align-items: center;
            justify-content: flex-end;
            flex-wrap: wrap;
            gap: 8px;
        }

        .reports-inquiry-chip,
        .reports-product-scale-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 10px;
            border-radius: 999px;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            color: #475569;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        }

        .reports-inquiry-chip-label {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #94a3b8;
        }

        .reports-inquiry-chip-value {
            font-size: 13px;
            font-weight: 700;
            color: #0f172a;
        }

        .admin-inquiry-trend-legend {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
            gap: 14px;
            padding: 6px 0 0;
        }

        .admin-inquiry-trend-legend-button {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 0;
            border: 0;
            background: transparent;
            color: #334155;
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            transition: opacity 0.16s ease, color 0.16s ease;
        }

        .admin-inquiry-trend-legend-button:hover {
            color: #0f172a;
        }

        .admin-inquiry-trend-legend-button.is-hidden {
            opacity: 0.45;
        }

        .admin-inquiry-trend-legend-button:focus-visible {
            outline: 2px solid rgba(127, 90, 240, 0.22);
            outline-offset: 4px;
            border-radius: 999px;
        }

        .admin-inquiry-trend-legend-dot {
            width: 10px;
            height: 10px;
            border-radius: 999px;
            flex-shrink: 0;
        }

        .admin-inquiry-trend-legend-dot--inquiries {
            background: rgba(127, 90, 240, 0.32);
        }

        .admin-inquiry-trend-legend-dot--trend {
            background: #7f5af0;
        }

        .reports-page .reports-inquiry-section .dashboard-panel-body,
        .reports-page .reports-status-section .dashboard-panel-body,
        .reports-page .reports-product-section .dashboard-panel-body {
            padding: 0 18px 16px;
        }

        .dealer-reports-card,
        .dealer-reports-status-card,
        .reports-product-card {
            border: 1px solid #eef2f8;
            border-radius: 16px;
            background: linear-gradient(180deg, #ffffff 0%, #fbfcff 100%);
            padding: 10px 12px 8px;
        }

        .reports-page .reports-inquiry-section .dealer-reports-card {
            padding: 8px 10px 2px;
        }

        .dealer-reports-chart-wrapper,
        .reports-product-chart-wrapper {
            position: relative;
            width: 100%;
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
            border-radius: 10px;
            border: 1px solid #e5e7eb;
            padding: 8px;
            box-sizing: border-box;
        }

        .reports-page .reports-inquiry-section .dealer-reports-chart-wrapper {
            background: #ffffff;
            border: 0;
            border-radius: 0;
            padding: 0 2px;
        }

        .dealer-reports-chart-wrapper canvas,
        .reports-product-chart-wrapper canvas {
            display: block;
            width: 100% !important;
            height: 100% !important;
        }

        .dealer-reports-chart-fallback,
        .reports-product-chart-fallback {
            display: none;
            margin: 0;
            font-size: 13px;
            font-weight: 500;
            color: #64748b;
            text-align: center;
        }

        .dealer-reports-chart-wrapper.is-error,
        .reports-product-chart-wrapper.is-error {
            height: auto !important;
            min-height: 0;
            padding: 0;
            border: 0;
            background: transparent;
        }

        .dealer-reports-chart-wrapper.is-error canvas,
        .reports-product-chart-wrapper.is-error canvas {
            display: none;
        }

        .dealer-reports-chart-wrapper.is-error .dealer-reports-chart-fallback,
        .reports-product-chart-wrapper.is-error .reports-product-chart-fallback {
            display: block;
            padding: 8px 0 4px;
        }

        .dealer-reports-empty,
        .reports-product-empty {
            margin: 0;
            padding: 12px 6px;
            font-size: 13px;
            color: #64748b;
            text-align: center;
        }

        .reports-page .report-status-body {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }

        .reports-page .report-donut-wrapper {
            display: flex;
            justify-content: center;
            width: 100%;
        }

        html.theme-dark .reports-page .reports-inquiry-section,
        html.theme-dark .reports-page .reports-status-section,
        html.theme-dark .reports-page .reports-product-section {
            border-color: #283451;
            background: linear-gradient(180deg, #181f34 0%, #11182b 100%);
            box-shadow: 0 22px 40px rgba(2, 6, 23, 0.32);
        }

        html.theme-dark .reports-page .reports-inquiry-heading .dashboard-panel-title,
        html.theme-dark .reports-page .reports-status-heading .dashboard-panel-title,
        html.theme-dark .reports-page .reports-product-heading .dashboard-panel-title,
        html.theme-dark .reports-page .reports-inquiry-chip-value {
            color: #f6f9ff;
        }

        html.theme-dark .reports-inquiry-subtitle,
        html.theme-dark .reports-status-subtitle,
        html.theme-dark .reports-product-subtitle,
        html.theme-dark .reports-inquiry-chip-label,
        html.theme-dark .dealer-reports-chart-fallback,
        html.theme-dark .reports-product-chart-fallback,
        html.theme-dark .dealer-reports-empty,
        html.theme-dark .reports-product-empty {
            color: #8e9abc;
        }

        html.theme-dark .reports-inquiry-chip,
        html.theme-dark .reports-product-scale-chip {
            border-color: #2f3b5a;
            background: rgba(16, 23, 39, 0.66);
            color: #c8d2eb;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.03);
        }

        html.theme-dark .admin-inquiry-trend-legend-button {
            color: #a6b3d3;
        }

        html.theme-dark .admin-inquiry-trend-legend-button:hover {
            color: #ffffff;
        }

        html.theme-dark .admin-inquiry-trend-legend-button:focus-visible {
            outline-color: rgba(124, 91, 255, 0.34);
        }

        html.theme-dark .dealer-reports-card,
        html.theme-dark .dealer-reports-status-card,
        html.theme-dark .reports-product-card {
            border-color: #25314d;
            background: linear-gradient(180deg, rgba(27, 35, 57, 0.98) 0%, rgba(16, 22, 38, 0.98) 100%);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.03);
        }

        html.theme-dark .dealer-reports-chart-wrapper,
        html.theme-dark .reports-product-chart-wrapper {
            background: linear-gradient(180deg, #131b2f 0%, #0f1728 100%);
            border-color: #27334e;
        }

        html.theme-dark .reports-page .reports-inquiry-section .dealer-reports-chart-wrapper {
            background: transparent;
        }

        .reports-product-scale {
            display: inline-flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
        }

        .reports-product-scale-chip {
            gap: 6px;
            padding: 5px 10px;
            font-size: 11px;
            font-weight: 600;
        }

        .reports-product-scale-dot {
            width: 8px;
            height: 8px;
            border-radius: 999px;
            flex-shrink: 0;
        }

        .reports-product-scale-dot--high {
            background: #22c55e;
        }

        .reports-product-scale-dot--medium {
            background: #f59e0b;
        }

        .reports-product-scale-dot--low {
            background: #ef4444;
        }

        @media (max-width: 1200px) {
            .reports-page .dashboard-panels-two-column {
                grid-template-columns: minmax(0, 1fr);
            }
        }

        @media (max-width: 768px) {
            .reports-page .reports-inquiry-section .dashboard-panel-header,
            .reports-page .reports-status-section .dashboard-panel-header,
            .reports-page .reports-product-section .dashboard-panel-header {
                padding: 16px 16px 10px;
                flex-direction: column;
                align-items: flex-start;
            }

            .reports-page .reports-inquiry-section .dashboard-panel-body,
            .reports-page .reports-status-section .dashboard-panel-body,
            .reports-page .reports-product-section .dashboard-panel-body {
                padding: 0 16px 16px;
            }

            .dealer-reports-card,
            .dealer-reports-status-card,
            .reports-product-card {
                padding: 12px;
                border-radius: 14px;
            }

            .reports-page .reports-inquiry-heading .dashboard-panel-title,
            .reports-page .reports-status-heading .dashboard-panel-title,
            .reports-page .reports-product-heading .dashboard-panel-title {
                font-size: 16px;
            }

            .reports-page .reports-inquiry-meta {
                justify-content: flex-start;
            }
        }
    </style>
@endpush
@section('content')
<div class="reports-page">
<div class="reports-page-layout">
<header class="dashboard-header">
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
@php
    $totalLeads = array_sum($leadStatus);
    $totalPayouts = array_sum($payoutStatus);
@endphp

@php
    $metricCards = [
        [
            'key' => 'unassigned',
            'label' => 'UNASSIGNED',
            'value' => $metricUnassignedLeads,
            'link' => route('admin.inquiries'),
            'link_aria' => 'View unassigned inquiries'
        ],
        ['key' => 'Pending', 'label' => 'PENDING', 'value' => $metricActivityStatus['Pending'] ?? 0, 'dealer' => true],
        ['key' => 'FollowUp', 'label' => 'FOLLOW-UP', 'value' => $metricActivityStatus['FollowUp'] ?? 0, 'dealer' => true],
        ['key' => 'Demo', 'label' => 'DEMO', 'value' => $metricActivityStatus['Demo'] ?? 0, 'dealer' => true],
        ['key' => 'Confirmed', 'label' => 'CONFIRMED', 'value' => $metricActivityStatus['Confirmed'] ?? 0, 'dealer' => true],
        [
            'key' => 'Completed',
            'label' => 'COMPLETED',
            'value' => $metricLeadStatus['Closed'] ?? 0,
            'link' => route('admin.inquiries', ['tab' => 'all']),
            'link_aria' => 'View completed cases in all inquiries'
        ],
        [
            'key' => 'Rewarded',
            'label' => 'REWARDED',
            'value' => $metricActivityStatus['reward'] ?? 0,
            'link' => route('admin.inquiries', ['tab' => 'all']),
            'link_aria' => 'View rewarded cases in all inquiries'
        ],
    ];
@endphp

@php
    $statusReportData = [
        ['label' => 'Unassigned', 'value' => (int) $unassignedLeads, 'color' => '#ef4444'],
        ['label' => 'Pending', 'value' => (int) ($activityStatus['Pending'] ?? 0), 'color' => '#f97316'],
        ['label' => 'FollowUp', 'value' => (int) ($activityStatus['FollowUp'] ?? 0), 'color' => '#f59e0b'],
        ['label' => 'Demo', 'value' => (int) ($activityStatus['Demo'] ?? 0), 'color' => '#eab308'],
        ['label' => 'Confirmed', 'value' => (int) ($activityStatus['Confirmed'] ?? 0), 'color' => '#84cc16'],
        ['label' => 'Completed', 'value' => (int) ($leadStatus['Closed'] ?? 0), 'color' => '#22c55e'],
        ['label' => 'Rewarded', 'value' => (int) ($activityStatus['reward'] ?? 0), 'color' => '#15803d'],
    ];
    $totalStatus = max(array_sum(array_column($statusReportData, 'value')), 1);
    $selectedDays = max((int) ($selectedDaysInMonth ?? now()->daysInMonth), 1);
    $trendByDay = collect($inquiryTrend ?? [])->mapWithKeys(function ($item) {
        return [
            (int) ($item['day'] ?? 0) => (int) ($item['count'] ?? 0),
        ];
    })->all();
    $adminInquiryTrendLabels = array_map(function ($day) {
        return str_pad((string) $day, 2, '0', STR_PAD_LEFT);
    }, range(1, $selectedDays));
    $adminInquiryTrendData = array_map(function ($day) use ($trendByDay) {
        return (int) ($trendByDay[$day] ?? 0);
    }, range(1, $selectedDays));
    $totalInquiry = array_sum($adminInquiryTrendData);
    $hasInquiryTrendData = $totalInquiry > 0;
@endphp

<div class="reports-period-row">
    @php
        $clearMonthlyFiltersUrl = route('admin.reports', [
            'month' => (int) now()->format('n'),
            'year' => (int) now()->format('Y'),
            'report_scope' => 'all',
        ]);
    @endphp
    <form method="get" class="reports-period-form reports-period-form-compact">
        @php
            $months = [
                1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
            ];
        @endphp
        <select name="month" class="reports-period-select reports-period-select--month" aria-label="Select month">
            @foreach ($months as $m => $label)
                <option value="{{ $m }}" {{ (int) ($selectedMonth ?? now()->format('n')) === (int) $m ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        <select name="year" class="reports-period-select reports-period-select--year" aria-label="Select year">
            @foreach (($yearOptions ?? []) as $y)
                <option value="{{ $y }}" {{ (int) ($selectedYear ?? now()->format('Y')) === (int) $y ? 'selected' : '' }}>
                    {{ $y }}
                </option>
            @endforeach
        </select>
        @include('admin.partials.report_scope_picker', [
            'options' => $reportScopeOptions ?? [],
            'selected' => $selectedReportScope ?? 'all',
        ])
        @include('admin.partials.report_filter_actions', [
            'clearUrl' => $clearMonthlyFiltersUrl,
            'wrapperClass' => 'reports-period-actions report-filter-actions',
            'applyClass' => 'report-filter-apply',
            'clearClass' => 'report-filter-clear',
        ])
    </form>
</div>

<section class="dashboard-metrics-grid">
    @foreach ($metricCards as $card)
    @php
        $pct = $metricPercent[$card['key']] ?? 0;
        $trend = $pct > 0 ? 'up' : ($pct < 0 ? 'down' : 'same');
    @endphp
    @if (!empty($card['link']))
    <a href="{{ $card['link'] }}" class="dashboard-metric-card report-metric-link" aria-label="{{ $card['link_aria'] ?? $card['label'] }}">
        <span class="report-metric-chevron" aria-hidden="true">&gt;</span>
    @else
    <div class="dashboard-metric-card">
    @endif
        <div class="dashboard-metric-card-top">
            <div class="dashboard-metric-label">{{ $card['label'] }}</div>
            @if (!empty($card['dealer']))
            <span class="dashboard-metric-dealer-tag">DEALER MANAGED</span>
            @else
            <span class="dashboard-metric-dealer-spacer"></span>
            @endif
        </div>
        <div class="dashboard-metric-value-row">
            <div class="dashboard-metric-value">{{ $card['value'] }}</div>
        </div>
        <div class="dashboard-metric-trend dashboard-metric-trend-{{ $trend }}">
            @if ($trend === 'up')
            <svg class="dashboard-metric-trend-icon" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v13m0-13 4 4m-4-4-4 4"/></svg>
            <span>+{{ $pct }}% vs last month</span>
            @elseif ($trend === 'down')
            <svg class="dashboard-metric-trend-icon" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19V5m0 0-4 4m4-4 4 4"/></svg>
            <span>{{ $pct }}% vs last month</span>
            @else
            <svg class="dashboard-metric-trend-icon dashboard-metric-trend-icon-same" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-4 4 4-4m0 0 4-4"/></svg>
            <span>No change vs last month</span>
            @endif
        </div>
        <div class="dashboard-metric-btn-wrap"></div>
    @if (!empty($card['link']))
        </a>
    @else
        </div>
    @endif
    @endforeach
</section>

<section class="dashboard-panels-two-column">
    <section class="dashboard-panel reports-inquiry-section">
        <div class="dashboard-panel-header">
            <div class="reports-inquiry-heading">
                <div class="dashboard-panel-title">Inquiry Trends</div>
                <div class="reports-inquiry-subtitle">Inquiries for {{ $selectedMonthName }} {{ $selectedYear }}</div>
            </div>
            <div class="reports-inquiry-meta">
                <span class="reports-inquiry-chip">
                    <span class="reports-inquiry-chip-label">Total</span>
                    <span class="reports-inquiry-chip-value">{{ number_format($totalInquiry) }}</span>
                </span>
            </div>
        </div>
        <div class="dashboard-panel-body">
            <div class="dealer-reports-card">
                @if (!$hasInquiryTrendData)
                    <p class="dealer-reports-empty">No leads created in this period yet.</p>
                @else
                    <div class="dealer-reports-chart-wrapper" style="height: 272px;">
                        <p class="dealer-reports-chart-fallback">Unable to load inquiry trend chart.</p>
                        <canvas id="adminInquiryTrendChart"></canvas>
                    </div>
                    <div class="admin-inquiry-trend-legend" id="adminInquiryTrendLegend">
                        <button type="button"
                                class="admin-inquiry-trend-legend-button"
                                data-dataset-index="0"
                                aria-pressed="true">
                            <span class="admin-inquiry-trend-legend-dot admin-inquiry-trend-legend-dot--inquiries"></span>
                            <span>Inquiries</span>
                        </button>
                        <button type="button"
                                class="admin-inquiry-trend-legend-button"
                                data-dataset-index="1"
                                aria-pressed="true">
                            <span class="admin-inquiry-trend-legend-dot admin-inquiry-trend-legend-dot--trend"></span>
                            <span>Trend</span>
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </section>

    <section class="dashboard-panel reports-status-section">
        <div class="dashboard-panel-header">
            <div class="reports-status-heading">
                <div class="dashboard-panel-title">Status Report</div>
                <div class="reports-status-subtitle">Current status distribution for {{ $selectedMonthName }} {{ $selectedYear }}</div>
            </div>
        </div>
        <div class="dashboard-panel-body report-status-body">
            @php
                $segments = [];
                $offset = 0;
                foreach ($statusReportData as $item) {
                    $value = (int) ($item['value'] ?? 0);
                    if ($value <= 0) {
                        continue;
                    }
                    $percent = $value / $totalStatus * 100;
                    $segments[] = [
                        'from' => $offset,
                        'to' => $offset + $percent,
                        'color' => $item['color'] ?? '#e5e7eb',
                    ];
                    $offset += $percent;
                }
                $gradientParts = collect($segments)->map(function ($s) {
                    return $s['color'] . ' ' . $s['from'] . '% ' . $s['to'] . '%';
                })->implode(', ');
            @endphp
            <div class="dealer-reports-status-card">
                <div class="report-donut-wrapper">
                    <div class="report-donut"
                         style="background: conic-gradient({{ $gradientParts ?: '#e5e7eb 0 100%' }});">
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
                        <span class="report-legend-color"
                              style="background-color: {{ $item['color'] ?? '#e5e7eb' }}"></span>
                        <span class="report-legend-label">{{ $item['label'] }}</span>
                        <span class="report-legend-value">{{ $item['value'] }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>
</section>

@php
    $productConversionDisplay = collect($productConversion ?? [])
        ->map(function ($item) {
            return [
                'label' => (string) ($item['label'] ?? ''),
                'count' => (int) ($item['count'] ?? 0),
            ];
        })
        ->sort(function ($a, $b) {
            $aIsOthers = strtoupper(trim((string) ($a['label'] ?? ''))) === 'OTHERS';
            $bIsOthers = strtoupper(trim((string) ($b['label'] ?? ''))) === 'OTHERS';

            if ($aIsOthers && !$bIsOthers) {
                return 1;
            }

            if (!$aIsOthers && $bIsOthers) {
                return -1;
            }

            $countCompare = ($b['count'] ?? 0) <=> ($a['count'] ?? 0);
            if ($countCompare !== 0) {
                return $countCompare;
            }

            return strcasecmp((string) ($a['label'] ?? ''), (string) ($b['label'] ?? ''));
        })
        ->values();
@endphp

<section class="dashboard-panel dashboard-table-panel reports-product-section">
    <div class="dashboard-panel-header">
        <div class="reports-product-heading">
            <div class="dashboard-panel-title">Product Conversion Rate</div>
            <div class="reports-product-subtitle">Closed-case conversions by product for {{ $selectedMonthName }} {{ $selectedYear }}</div>
        </div>
        <div class="reports-product-scale" aria-hidden="true">
            <span class="reports-product-scale-chip">
                <span class="reports-product-scale-dot reports-product-scale-dot--high"></span>
                High
            </span>
            <span class="reports-product-scale-chip">
                <span class="reports-product-scale-dot reports-product-scale-dot--medium"></span>
                Medium
            </span>
            <span class="reports-product-scale-chip">
                <span class="reports-product-scale-dot reports-product-scale-dot--low"></span>
                Low
            </span>
        </div>
    </div>
    <div class="dashboard-panel-body">
        <div class="reports-product-card">
        @if ($productConversionDisplay->isEmpty())
            <p class="reports-product-empty">No closed cases this month yet.</p>
        @else
            @php
                $itemCount = $productConversionDisplay->count();
                $barHeightPx = 16;
                $gapPx = 8;
                $paddingPx = 28;
                $chartHeightPx = max(132, $itemCount * ($barHeightPx + $gapPx) + $paddingPx);
            @endphp
            <div class="reports-product-chart-wrapper" style="height: {{ $chartHeightPx }}px;">
                <p class="reports-product-chart-fallback">Unable to load product conversion chart.</p>
                <canvas id="productConversionChart"></canvas>
            </div>
        @endif
        </div>
    </div>
</section>
</div>
</div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Title dropdown (light purple)
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
                        // Don't re-toggle when clicking inside the menu
                        if (dropdown.contains(e.target)) return;
                        // Button (including arrow) already handled above
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

            const showChartFallback = function (wrapper, fallback, message) {
                if (wrapper) {
                    wrapper.classList.add('is-error');
                }
                if (fallback) {
                    fallback.textContent = message;
                }
            };

            const inquiryCanvas = document.getElementById('adminInquiryTrendChart');
            const inquiryWrapper = inquiryCanvas
                ? inquiryCanvas.closest('.dealer-reports-chart-wrapper')
                : document.querySelector('.dealer-reports-chart-wrapper');
            const inquiryFallback = inquiryWrapper
                ? inquiryWrapper.querySelector('.dealer-reports-chart-fallback')
                : null;
            const inquiryLegend = document.getElementById('adminInquiryTrendLegend');

            const el = document.getElementById('productConversionChart');
            const productChartWrapper = el
                ? el.closest('.reports-product-chart-wrapper')
                : document.querySelector('.reports-product-chart-wrapper');
            const productChartFallback = productChartWrapper
                ? productChartWrapper.querySelector('.reports-product-chart-fallback')
                : null;

            if (typeof Chart === 'undefined') {
                showChartFallback(inquiryWrapper, inquiryFallback, 'Inquiry trend chart could not be loaded right now.');
                showChartFallback(productChartWrapper, productChartFallback, 'Product conversion chart could not be loaded right now.');
                return;
            }

            if (inquiryCanvas) {
                try {
                    const rawInquiryLabels = @json($adminInquiryTrendLabels ?? []);
                    const inquiryValues = @json($adminInquiryTrendData ?? []);
                    const reportPeriod = 'month';
                    const currentMonthName = @json($selectedMonthName ?? now()->format('F'));
                    const brandColor = '#7f5af0';
                    const columnColor = 'rgba(127, 90, 240, 0.32)';
                    const gridColor = 'rgba(148, 163, 184, 0.25)';
                    const axisColor = 'rgba(148, 163, 184, 0.28)';
                    const inquiryLabels = rawInquiryLabels.map(function (label) {
                        return String(label || '').trim();
                    });
                    const tooltipLabels = rawInquiryLabels.map(function (label) {
                        const normalized = String(label || '').trim();
                        if (reportPeriod === 'month' && normalized && /^\d+$/.test(normalized)) {
                            return normalized + ' ' + currentMonthName;
                        }
                        return normalized;
                    });
                    const showAllDayTicks = reportPeriod === 'month';
                    const maxTickCount = inquiryLabels.length > 24 ? 11 : (inquiryLabels.length > 14 ? 9 : (inquiryLabels.length > 7 ? 7 : inquiryLabels.length));
                    const tickStep = inquiryLabels.length > 24 ? 3 : (inquiryLabels.length > 14 ? 2 : 1);
                    const maxInquiryValue = inquiryValues.length ? Math.max.apply(null, inquiryValues) : 0;

                    function clearInquiryHover(chart) {
                        chart.setActiveElements([]);
                        if (chart.tooltip) {
                            chart.tooltip.setActiveElements([], { x: 0, y: 0 });
                        }
                    }

                    function getChartEventPosition(chart, event) {
                        if (!event) {
                            return null;
                        }

                        let x = typeof event.x === 'number' ? event.x : null;
                        let y = typeof event.y === 'number' ? event.y : null;
                        const nativeEvent = event.native || null;
                        const canvas = chart.canvas;
                        const canvasRect = canvas && typeof canvas.getBoundingClientRect === 'function'
                            ? canvas.getBoundingClientRect()
                            : null;

                        if (nativeEvent && canvasRect && canvasRect.width && canvasRect.height) {
                            const touch = nativeEvent.touches && nativeEvent.touches.length ? nativeEvent.touches[0] : null;
                            const clientX = touch
                                ? touch.clientX
                                : (typeof nativeEvent.clientX === 'number' ? nativeEvent.clientX : null);
                            const clientY = touch
                                ? touch.clientY
                                : (typeof nativeEvent.clientY === 'number' ? nativeEvent.clientY : null);

                            if (typeof clientX === 'number' && typeof clientY === 'number') {
                                x = ((clientX - canvasRect.left) / canvasRect.width) * chart.width;
                                y = ((clientY - canvasRect.top) / canvasRect.height) * chart.height;
                            }
                        }

                        if (typeof x !== 'number' || typeof y !== 'number') {
                            return null;
                        }

                        return { x: x, y: y };
                    }

                    function getNearestDateIndex(chart, x) {
                        const labels = chart.data.labels || [];
                        if (!labels.length) {
                            return null;
                        }

                        const lineMeta = chart.getDatasetMeta(1);
                        const barMeta = chart.getDatasetMeta(0);
                        const points = lineMeta && Array.isArray(lineMeta.data) && lineMeta.data.length === labels.length
                            ? lineMeta.data
                            : (barMeta && Array.isArray(barMeta.data) ? barMeta.data : []);

                        if (!points.length) {
                            return null;
                        }

                        let nearestIndex = 0;
                        let smallestDistance = Infinity;
                        for (let i = 0; i < points.length; i++) {
                            const point = points[i];
                            const pointX = point && typeof point.x === 'number' ? point.x : null;
                            if (pointX === null) {
                                continue;
                            }
                            const distance = Math.abs(x - pointX);
                            if (distance < smallestDistance) {
                                smallestDistance = distance;
                                nearestIndex = i;
                            }
                        }

                        return nearestIndex;
                    }

                    const exactDateHover = {
                        id: 'adminInquiryExactDateHover',
                        afterEvent: function (chart, args) {
                            const event = args.event;
                            const chartArea = chart.chartArea;
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

                            const position = getChartEventPosition(chart, event);
                            if (!position) {
                                clearInquiryHover(chart);
                                args.changed = true;
                                return;
                            }

                            if (position.x < chartArea.left || position.x > chartArea.right || position.y < chartArea.top || position.y > chartArea.bottom) {
                                clearInquiryHover(chart);
                                args.changed = true;
                                return;
                            }

                            const nearestIndex = getNearestDateIndex(chart, position.x);
                            if (nearestIndex === null) {
                                clearInquiryHover(chart);
                                args.changed = true;
                                return;
                            }

                            const activeElements = chart.data.datasets.reduce(function (elements, dataset, datasetIndex) {
                                if (chart.isDatasetVisible(datasetIndex)) {
                                    elements.push({ datasetIndex: datasetIndex, index: nearestIndex });
                                }
                                return elements;
                            }, []);

                            if (!activeElements.length) {
                                clearInquiryHover(chart);
                                args.changed = true;
                                return;
                            }
                            const anchorX = chart.scales.x.getPixelForTick(nearestIndex);
                            const anchorY = chart.scales.y.getPixelForValue(Number(inquiryValues[nearestIndex] || 0));

                            chart.setActiveElements(activeElements);
                            if (chart.tooltip) {
                                chart.tooltip.setActiveElements(activeElements, { x: anchorX, y: anchorY });
                            }
                            args.changed = true;
                        }
                    };

                    const activeDateGuide = {
                        id: 'adminInquiryHoverGuide',
                        afterDatasetsDraw: function (chart) {
                            const tooltip = chart.tooltip;
                            if (!tooltip || tooltip.opacity === 0 || !tooltip.dataPoints || !tooltip.dataPoints.length) {
                                return;
                            }

                            const activePoint = tooltip.dataPoints[0] && tooltip.dataPoints[0].element ? tooltip.dataPoints[0].element : null;
                            if (!activePoint) {
                                return;
                            }

                            const ctx = chart.ctx;
                            const chartArea = chart.chartArea;
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

                    const inquiryChart = new Chart(inquiryCanvas.getContext('2d'), {
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
                                    display: false
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
                                        title: function (items) {
                                            if (!items || !items.length) {
                                                return '';
                                            }
                                            const item = items[0];
                                            return tooltipLabels[item.dataIndex] || item.label || '';
                                        },
                                        label: function (context) {
                                            const value = typeof context.parsed.y === 'number' ? context.parsed.y : 0;
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
                                        callback: function (value, index) {
                                            const label = this.getLabelForValue(value);
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
                                        callback: function (value) {
                                            return Math.round(value);
                                        }
                                    }
                                }
                            }
                        }
                    });

                    if (inquiryLegend) {
                        const legendButtons = Array.from(inquiryLegend.querySelectorAll('[data-dataset-index]'));

                        const syncInquiryLegend = function () {
                            legendButtons.forEach(function (button) {
                                const datasetIndex = Number(button.getAttribute('data-dataset-index'));
                                const isVisible = inquiryChart.isDatasetVisible(datasetIndex);
                                button.classList.toggle('is-hidden', !isVisible);
                                button.setAttribute('aria-pressed', isVisible ? 'true' : 'false');
                            });
                        };

                        legendButtons.forEach(function (button) {
                            button.addEventListener('click', function () {
                                const datasetIndex = Number(button.getAttribute('data-dataset-index'));
                                inquiryChart.setDatasetVisibility(datasetIndex, !inquiryChart.isDatasetVisible(datasetIndex));
                                clearInquiryHover(inquiryChart);
                                inquiryChart.update();
                                syncInquiryLegend();
                            });
                        });

                        syncInquiryLegend();
                    }

                    if (inquiryWrapper) {
                        inquiryWrapper.classList.remove('is-error');
                    }
                } catch (error) {
                    console.error('Admin inquiry trend chart failed to render.', error);
                    showChartFallback(inquiryWrapper, inquiryFallback, 'Unable to render inquiry trend chart.');
                }
            }

            if (!el) return;

            const rawProducts = @json($productConversionDisplay->values());
            const products = rawProducts
                .map(function (item) {
                    return {
                        label: String(item.label || ''),
                        count: Number(item.count || 0),
                    };
                })
                .sort(function (a, b) {
                    const aIsOthers = a.label.trim().toUpperCase() === 'OTHERS';
                    const bIsOthers = b.label.trim().toUpperCase() === 'OTHERS';
                    if (aIsOthers && !bIsOthers) return 1;
                    if (!aIsOthers && bIsOthers) return -1;
                    if (b.count !== a.count) return b.count - a.count;
                    return a.label.localeCompare(b.label);
                });

            const labels = products.map(function (item) { return item.label; });
            const dataValues = products.map(function (item) { return item.count; });
            const totalValue = dataValues.reduce(function (sum, value) { return sum + value; }, 0);
            const maxValue = dataValues.length ? Math.max.apply(null, dataValues) : 0;

            const getPerformanceTone = function (value) {
                const ratio = maxValue > 0 ? value / maxValue : 0;
                if (ratio >= 0.67) {
                    return { level: 'High', background: '#22c55e', border: '#16a34a' };
                }
                if (ratio >= 0.34) {
                    return { level: 'Medium', background: '#f59e0b', border: '#d97706' };
                }
                return { level: 'Low', background: '#ef4444', border: '#dc2626' };
            };

            const toneMap = dataValues.map(function (value) {
                return getPerformanceTone(value);
            });
            const barColors = toneMap.map(function (tone) { return tone.background; });
            const borderColors = toneMap.map(function (tone) { return tone.border; });
            const axisMax = maxValue > 0 ? Math.max(maxValue + 1, Math.ceil(maxValue * 1.35)) : 1;

            const endValueLabels = {
                id: 'endValueLabels',
                afterDatasetsDraw(chart) {
                    const meta = chart.getDatasetMeta(0);
                    if (!meta || !meta.data || !meta.data.length) return;

                    const ctx = chart.ctx;
                    const chartArea = chart.chartArea;
                    ctx.save();
                    ctx.font = '600 12px "Public Sans", sans-serif';
                    ctx.fillStyle = '#475569';
                    ctx.textBaseline = 'middle';

                    meta.data.forEach(function (bar, index) {
                        const value = dataValues[index] || 0;
                        const pct = totalValue > 0 ? Math.round((value / totalValue) * 100) : 0;
                        const text = value + ' (' + pct + '%)';
                        const textWidth = ctx.measureText(text).width;
                        let x = bar.x + 10;
                        if (x + textWidth > chartArea.right - 4) {
                            x = chartArea.right - textWidth - 4;
                        }
                        ctx.fillText(text, x, bar.y);
                    });

                    ctx.restore();
                }
            };

            const data = {
                labels: labels,
                datasets: [{
                    axis: 'y',
                    label: 'Product conversions',
                    data: dataValues,
                    backgroundColor: (ctx) => barColors[ctx.dataIndex] || '#94a3b8',
                    borderColor: (ctx) => borderColors[ctx.dataIndex] || '#64748b',
                    borderWidth: 1,
                    borderSkipped: false,
                    borderRadius: 8,
                    barThickness: 18,
                    maxBarThickness: 20,
                    categoryPercentage: 0.78,
                    barPercentage: 0.82,
                    hoverBackgroundColor: (ctx) => borderColors[ctx.dataIndex] || '#475569',
                }]
            };

            const config = {
                type: 'bar',
                data: data,
                plugins: [endValueLabels],
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
                            left: 6,
                        }
                    },
                    plugins: {
                        legend: {
                            display: false,
                        },
                        tooltip: {
                            displayColors: false,
                            backgroundColor: 'rgba(15, 23, 42, 0.94)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            padding: 12,
                            cornerRadius: 10,
                            callbacks: {
                                title: function(items) {
                                    return items[0] && items[0].label ? items[0].label : 'Product';
                                },
                                label: function(ctx) {
                                    const index = ctx.dataIndex;
                                    const val = ctx.parsed && typeof ctx.parsed.x === 'number' ? Math.round(ctx.parsed.x) : 0;
                                    const pct = totalValue > 0 ? ((val / totalValue) * 100).toFixed(1) : '0.0';
                                    return 'Conversions: ' + val + ' (' + pct + '%)';
                                },
                                afterLabel: function(ctx) {
                                    const tone = toneMap[ctx.dataIndex];
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
                                drawTicks: false,
                            },
                            border: {
                                display: false,
                            },
                            ticks: {
                                color: '#94a3b8',
                                font: {
                                    size: 11,
                                    weight: '600',
                                },
                                padding: 8,
                                stepSize: maxValue <= 10 ? 1 : undefined,
                                callback: function(v) { return Math.round(v); }
                            }
                        },
                        y: {
                            grid: {
                                display: false,
                            },
                            border: {
                                display: false,
                            },
                            ticks: {
                                padding: 12,
                                color: '#0f172a',
                                font: {
                                    size: 12,
                                    weight: '600',
                                },
                            },
                        },
                    },
                },
            };

            try {
                new Chart(el.getContext('2d'), config);
                if (productChartWrapper) {
                    productChartWrapper.classList.remove('is-error');
                }
            } catch (error) {
                console.error('Product conversion chart failed to render.', error);
                showChartFallback(productChartWrapper, productChartFallback, 'Unable to render product conversion chart.');
            }
        });
    </script>
@endpush
