@extends('layouts.app')
@section('title', 'Reports – SQL LMS Dealer Console')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/shared/reports-tabs.css') }}?v=20260423-4">
<style>
    .dealer-reports-page .dashboard-panels-two-column { display:grid; grid-template-columns:minmax(0,1.35fr) minmax(0,1fr); gap:20px; min-width:0; }
    .dealer-reports-page .dashboard-panels-two-column > *, .dealer-reports-page .reports-product-section { min-width:0; }
    .dealer-reports-page .reports-inquiry-section, .dealer-reports-page .dealer-reports-status-section, .dealer-reports-page .reports-product-section { border:1px solid #e8ecf5; border-radius:20px; box-shadow:0 14px 28px rgba(15,23,42,.05); overflow:hidden; background:#fff; }
    .dealer-reports-page .reports-inquiry-section .dashboard-panel-header, .dealer-reports-page .dealer-reports-status-section .dashboard-panel-header, .dealer-reports-page .reports-product-section .dashboard-panel-header { display:flex; align-items:flex-start; justify-content:space-between; gap:16px; padding:20px 22px 12px; border-bottom:none; }
    .dealer-reports-page .reports-inquiry-section .dashboard-panel-body, .dealer-reports-page .dealer-reports-status-section .dashboard-panel-body, .dealer-reports-page .reports-product-section .dashboard-panel-body { padding:0 22px 22px; }
    .dealer-reports-page .reports-inquiry-heading, .dealer-reports-page .reports-product-heading, .dealer-reports-page .dealer-reports-status-heading { display:flex; flex-direction:column; gap:4px; }
    .dealer-reports-page .reports-inquiry-heading .dashboard-panel-title, .dealer-reports-page .reports-product-heading .dashboard-panel-title, .dealer-reports-page .dealer-reports-status-heading .dashboard-panel-title { margin:0; font-size:18px; font-weight:700; line-height:1.2; color:#0f172a; letter-spacing:-.01em; }
    .dealer-reports-page .reports-inquiry-subtitle, .dealer-reports-page .reports-product-subtitle, .dealer-reports-page .dealer-reports-status-subtitle { font-size:13px; font-weight:600; color:#64748b; line-height:1.45; }
    .dealer-reports-page .reports-inquiry-meta { display:inline-flex; align-items:center; justify-content:flex-end; flex-wrap:wrap; gap:10px; }
    .dealer-reports-page .reports-inquiry-chip, .dealer-reports-page .reports-product-scale-chip { display:inline-flex; align-items:center; gap:8px; padding:7px 12px; border-radius:999px; border:1px solid #e2e8f0; background:#fff; color:#475569; box-shadow:0 1px 2px rgba(15,23,42,.04); }
    .dealer-reports-page .reports-inquiry-chip-label { font-size:10px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:#94a3b8; }
    .dealer-reports-page .reports-inquiry-chip-value { font-size:14px; font-weight:700; color:#0f172a; }
    .dealer-reports-page .dealer-reports-card, .dealer-reports-page .reports-product-card, .dealer-reports-page .dealer-reports-status-card { border:1px solid #eef2f8; border-radius:18px; background:linear-gradient(180deg,#fff 0%,#fbfcff 100%); padding:14px 16px 12px; }
    .dealer-reports-page .reports-inquiry-section .dealer-reports-card { padding:12px 14px 6px; }
    .dealer-reports-page .dealer-reports-chart-wrapper, .dealer-reports-page .reports-product-chart-wrapper { position:relative; width:100%; background:linear-gradient(180deg,#fff 0%,#f8fafc 100%); border-radius:10px; border:1px solid #e5e7eb; padding:10px; box-sizing:border-box; }
    .dealer-reports-page .reports-inquiry-section .dealer-reports-chart-wrapper { background:#fff; border:0; border-radius:0; padding:0 2px; }
    .dealer-reports-page .dealer-reports-chart-wrapper canvas, .dealer-reports-page .reports-product-chart-wrapper canvas { display:block; width:100% !important; height:100% !important; }
    .dealer-reports-page .dealer-reports-chart-fallback, .dealer-reports-page .reports-product-chart-fallback { display:none; margin:0; font-size:13px; font-weight:500; color:#64748b; text-align:center; }
    .dealer-reports-page .dealer-reports-chart-wrapper.is-error, .dealer-reports-page .reports-product-chart-wrapper.is-error { height:auto !important; min-height:0; padding:0; border:0; background:transparent; }
    .dealer-reports-page .dealer-reports-chart-wrapper.is-error canvas, .dealer-reports-page .reports-product-chart-wrapper.is-error canvas { display:none; }
    .dealer-reports-page .dealer-reports-chart-wrapper.is-error .dealer-reports-chart-fallback, .dealer-reports-page .reports-product-chart-wrapper.is-error .reports-product-chart-fallback { display:block; padding:8px 0 4px; }
    .dealer-reports-page .dealer-reports-empty, .dealer-reports-page .reports-product-empty { margin:0; padding:20px 8px; font-size:14px; color:#64748b; text-align:center; }
    .dealer-reports-page .reports-product-scale { display:inline-flex; align-items:center; flex-wrap:wrap; gap:8px; }
    .dealer-reports-page .reports-product-scale-chip { gap:6px; padding:5px 10px; font-size:11px; font-weight:600; }
    .dealer-reports-page .reports-product-scale-dot { width:8px; height:8px; border-radius:999px; flex-shrink:0; }
    .dealer-reports-page .reports-product-scale-dot--high { background:#22c55e; } .dealer-reports-page .reports-product-scale-dot--medium { background:#f59e0b; } .dealer-reports-page .reports-product-scale-dot--low { background:#ef4444; }
    .dealer-reports-page .reports-period-form--dealer { --dealer-report-action-width:180px; --dealer-report-action-height:44px; gap:8px; align-items:center; }
    .dealer-reports-page .reports-period-quick-group { display:inline-flex; align-items:center; box-sizing:border-box; flex:0 0 var(--dealer-report-action-width); width:var(--dealer-report-action-width); min-width:var(--dealer-report-action-width); max-width:var(--dealer-report-action-width); height:var(--dealer-report-action-height); overflow:hidden; border:1px solid rgba(124,91,255,.2); border-radius:14px; background:linear-gradient(180deg,#fff 0%,#fcfbff 100%); box-shadow:0 8px 18px rgba(91,63,215,.08), inset 0 1px 0 rgba(255,255,255,.95); transition:transform .16s ease, border-color .16s ease, box-shadow .16s ease, background-color .16s ease; }
    .dealer-reports-page .reports-period-quick-group:hover { transform:translateY(-1px); border-color:rgba(124,91,255,.34); background:#fff; box-shadow:0 12px 26px rgba(91,63,215,.13), inset 0 1px 0 rgba(255,255,255,.98); }
    .dealer-reports-page .reports-period-quick-group:focus-within { transform:translateY(-1px); border-color:rgba(124,91,255,.34); box-shadow:0 0 0 3px rgba(124,91,255,.1), 0 12px 26px rgba(91,63,215,.13), inset 0 1px 0 rgba(255,255,255,.95); }
    .dealer-reports-page .reports-period-quick-group .reports-period-select--dealer { flex:1 1 auto; min-width:0; height:100%; padding:0 22px 0 16px; border:0; border-radius:0; outline:0; background:transparent; box-shadow:none; color:#111827; font-family:"Public Sans", sans-serif; font-size:13px; font-weight:800; line-height:1; letter-spacing:.045em; cursor:pointer; }
    .dealer-reports-page .reports-period-quick-group .reports-period-select--dealer:focus, .dealer-reports-page .reports-period-quick-group .reports-period-select--dealer:focus-visible { outline:0; box-shadow:none; }
    .dealer-reports-page .reports-period-range-inline { display:inline-flex; gap:0; align-items:center; box-sizing:border-box; height:var(--dealer-report-action-height); overflow:hidden; border:1px solid rgba(124,91,255,.18); border-radius:14px; background:linear-gradient(180deg,#fff 0%,#fcfbff 100%); box-shadow:0 8px 18px rgba(91,63,215,.07), inset 0 1px 0 rgba(255,255,255,.95); transition:border-color .18s ease, box-shadow .18s ease; }
    .dealer-reports-page .reports-period-range-inline:focus-within { border-color:rgba(124,91,255,.32); box-shadow:0 0 0 3px rgba(124,91,255,.09), 0 10px 22px rgba(91,63,215,.09), inset 0 1px 0 rgba(255,255,255,.95); }
    .dealer-reports-page .reports-period-range-inline .reports-period-date { width:150px; min-width:150px; height:100%; padding:0 12px 0 14px; border:0; border-radius:0; outline:0; background:transparent; box-shadow:none; color:#111827; font-weight:700; }
    .dealer-reports-page .reports-period-range-inline .reports-period-date::-webkit-calendar-picker-indicator { margin-left:8px; margin-right:2px; opacity:.68; cursor:pointer; }
    .dealer-reports-page .reports-period-range-inline .reports-period-date + .reports-period-date { border-left:1px solid rgba(124,91,255,.12); }
    .dealer-reports-page .reports-period-range-inline .reports-period-date:focus, .dealer-reports-page .reports-period-range-inline .reports-period-date:focus-visible { outline:0; box-shadow:none; }
    .dealer-reports-page .reports-period-range-inline.is-hidden { display:none; }
    .dealer-reports-page .report-status-body { display:flex; flex-direction:column; align-items:center; gap:16px; }
    .dealer-reports-page .report-donut-wrapper { display:flex; justify-content:center; width:100%; }
    .dealer-reports-page .report-donut { width:196px; height:196px; border-radius:50%; position:relative; border:1px solid #e5e7eb; box-shadow:0 14px 30px rgba(15,23,42,.08); }
    .dealer-reports-page .report-donut-center { position:absolute; inset:24px; border-radius:50%; background:#fff; display:flex; flex-direction:column; align-items:center; justify-content:center; box-shadow:0 10px 30px rgba(15,23,42,.08); }
    .dealer-reports-page .report-donut-total { font-size:22px; font-weight:700; color:#0f172a; } .dealer-reports-page .report-donut-label { font-size:12px; color:#64748b; }
    .dealer-reports-page .report-legend { list-style:none; margin:0; padding:0 8px 4px; display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:6px 16px; font-size:12px; width:100%; }
    .dealer-reports-page .report-legend li { display:flex; align-items:center; justify-content:space-between; gap:8px; min-width:0; }
    .dealer-reports-page .report-legend-color { width:10px; height:10px; border-radius:999px; flex-shrink:0; }
    .dealer-reports-page .report-legend-label { flex:1; color:#475569; } .dealer-reports-page .report-legend-value { font-weight:600; color:#0f172a; }
    .dealer-reports-page .reports-period-export { box-sizing:border-box; flex:0 0 var(--dealer-report-action-width); width:var(--dealer-report-action-width); min-width:var(--dealer-report-action-width); max-width:var(--dealer-report-action-width); height:var(--dealer-report-action-height); min-height:var(--dealer-report-action-height); }
    html.theme-dark .dealer-reports-page .reports-metric-card { border-color:#283451; background:linear-gradient(180deg,#181f34 0%,#11182b 100%); box-shadow:0 18px 36px rgba(2,6,23,.26); }
    html.theme-dark .dealer-reports-page .reports-metric-icon-failed,
    html.theme-dark .dealer-reports-page .reports-metric-icon-failed::before { background:linear-gradient(135deg,#f8fafc 0%,#dbe5f6 100%) !important; }
    html.theme-dark .dealer-reports-page .reports-metric-icon-failed { color:#111827 !important; box-shadow:0 0 0 1px rgba(255,255,255,.18), 0 12px 24px rgba(2,6,23,.22) !important; }
    html.theme-dark .dealer-reports-page .reports-metric-label { color:#a8b4d4; }
    html.theme-dark .dealer-reports-page .reports-metric-value { color:#f8fbff; }
    html.theme-dark .dealer-reports-page .reports-inquiry-section, html.theme-dark .dealer-reports-page .dealer-reports-status-section, html.theme-dark .dealer-reports-page .reports-product-section { border-color:#283451; background:linear-gradient(180deg,#181f34 0%,#11182b 100%); box-shadow:0 22px 40px rgba(2,6,23,.32); }
    html.theme-dark .dealer-reports-page .reports-inquiry-heading .dashboard-panel-title, html.theme-dark .dealer-reports-page .reports-product-heading .dashboard-panel-title, html.theme-dark .dealer-reports-page .dealer-reports-status-heading .dashboard-panel-title, html.theme-dark .dealer-reports-page .reports-inquiry-chip-value, html.theme-dark .dealer-reports-page .report-donut-total, html.theme-dark .dealer-reports-page .report-legend-value { color:#f6f9ff; }
    html.theme-dark .dealer-reports-page .reports-inquiry-subtitle, html.theme-dark .dealer-reports-page .reports-product-subtitle, html.theme-dark .dealer-reports-page .dealer-reports-status-subtitle, html.theme-dark .dealer-reports-page .reports-inquiry-chip-label, html.theme-dark .dealer-reports-page .dealer-reports-chart-fallback, html.theme-dark .dealer-reports-page .reports-product-chart-fallback, html.theme-dark .dealer-reports-page .dealer-reports-empty, html.theme-dark .dealer-reports-page .reports-product-empty, html.theme-dark .dealer-reports-page .report-donut-label, html.theme-dark .dealer-reports-page .report-legend-label { color:#8e9abc; }
    html.theme-dark .dealer-reports-page .reports-inquiry-chip, html.theme-dark .dealer-reports-page .reports-product-scale-chip { border-color:#2f3b5a; background:rgba(16,23,39,.66); color:#c8d2eb; box-shadow:inset 0 1px 0 rgba(255,255,255,.03); }
    html.theme-dark .dealer-reports-page .reports-period-quick-group { border-color:rgba(156,137,255,.26); background:linear-gradient(180deg,#18223a 0%,#101827 100%); box-shadow:0 10px 24px rgba(2,6,23,.3), inset 0 1px 0 rgba(255,255,255,.05); }
    html.theme-dark .dealer-reports-page .reports-period-quick-group:focus-within { border-color:rgba(156,137,255,.42); box-shadow:0 0 0 3px rgba(156,137,255,.13), 0 12px 28px rgba(2,6,23,.32), inset 0 1px 0 rgba(255,255,255,.05); }
    html.theme-dark .dealer-reports-page .reports-period-quick-group .reports-period-select--dealer { color:#f3f6ff; }
    html.theme-dark .dealer-reports-page .reports-period-range-inline { border-color:rgba(156,137,255,.24); background:linear-gradient(180deg,#18223a 0%,#101827 100%); box-shadow:0 10px 24px rgba(2,6,23,.28), inset 0 1px 0 rgba(255,255,255,.05); }
    html.theme-dark .dealer-reports-page .reports-period-range-inline:focus-within { border-color:rgba(156,137,255,.4); box-shadow:0 0 0 3px rgba(156,137,255,.12), 0 12px 28px rgba(2,6,23,.3), inset 0 1px 0 rgba(255,255,255,.05); }
    html.theme-dark .dealer-reports-page .reports-period-range-inline .reports-period-date { color:#f3f6ff; }
    html.theme-dark .dealer-reports-page .reports-period-range-inline .reports-period-date + .reports-period-date { border-left-color:rgba(156,137,255,.18); }
    html.theme-dark .dealer-reports-page .dealer-reports-card, html.theme-dark .dealer-reports-page .reports-product-card, html.theme-dark .dealer-reports-page .dealer-reports-status-card { border-color:#25314d; background:linear-gradient(180deg,rgba(27,35,57,.98) 0%,rgba(16,22,38,.98) 100%); box-shadow:inset 0 1px 0 rgba(255,255,255,.03); }
    html.theme-dark .dealer-reports-page .dealer-reports-chart-wrapper, html.theme-dark .dealer-reports-page .reports-product-chart-wrapper { background:linear-gradient(180deg,#131b2f 0%,#0f1728 100%); border-color:#27334e; }
    html.theme-dark .dealer-reports-page .reports-inquiry-section .dealer-reports-chart-wrapper { background:transparent; border-color:transparent; }
    html.theme-dark .dealer-reports-page .report-donut { border-color:#27334e; box-shadow:0 14px 30px rgba(2,6,23,.24); }
    html.theme-dark .dealer-reports-page .report-donut-center { background:linear-gradient(180deg,#131b2f 0%,#0f1728 100%); box-shadow:0 12px 28px rgba(2,6,23,.28); }
    html.theme-dark .dealer-reports-page .report-legend-color--failed { background:#f8fafc !important; box-shadow:0 0 0 1px rgba(148,163,184,.4); }
    @media (min-width:1024px) and (max-width:1600px) and (max-height:920px) {
        .dashboard-content.reports-page.dealer-reports-page { padding-top:10px; padding-bottom:12px; gap:8px; }
        .dealer-reports-page .reports-header { margin-bottom:6px; }
        .dealer-reports-page .reports-period-form--dealer { width:min(100%, 690px); gap:7px; flex-wrap:nowrap; --dealer-report-action-width:156px; --dealer-report-action-height:40px; --report-scope-picker-height:36px; --report-filter-radius:10px; --report-filter-apply-height:36px; --report-filter-apply-padding:0 13px; --report-filter-apply-font-size:11px; --report-filter-clear-height:32px; --report-filter-clear-padding:0 10px; --report-filter-clear-font-size:10.5px; }
        .dealer-reports-page .reports-period-select { height:36px; padding:0 10px; font-size:11px; border-radius:10px; }
        .dealer-reports-page .reports-period-quick-group .reports-period-select--dealer { height:100%; padding:0 18px 0 13px; border-radius:0; }
        .dealer-reports-page .reports-period-range-inline { flex:0 0 270px; width:270px; }
        .dealer-reports-page .reports-period-range-inline .reports-period-date { width:135px; min-width:0; height:100%; padding:0 9px 0 11px; border-radius:0; font-size:10.5px; }
        .dealer-reports-page .reports-period-export { font-size:10.5px; }
        .dealer-reports-page .reports-metrics { gap:10px; margin-bottom:10px; }
        .dealer-reports-page .reports-metric-card { min-height:118px; padding:12px 14px; border-radius:15px; }
        .dealer-reports-page .reports-metric-icon { width:40px; height:40px; margin-bottom:8px; font-size:19px; }
        .dealer-reports-page .reports-metric-value { font-size:21px; line-height:1; }
        .dealer-reports-page .reports-metric-label { margin-top:7px; font-size:11px; letter-spacing:.08em; }
        .dealer-reports-page .dashboard-panels-two-column { gap:14px; }
        .dealer-reports-page .reports-inquiry-section, .dealer-reports-page .dealer-reports-status-section, .dealer-reports-page .reports-product-section { border-radius:18px; }
        .dealer-reports-page .reports-inquiry-section .dashboard-panel-header, .dealer-reports-page .dealer-reports-status-section .dashboard-panel-header, .dealer-reports-page .reports-product-section .dashboard-panel-header { gap:10px; padding:14px 16px 7px; }
        .dealer-reports-page .reports-inquiry-section .dashboard-panel-body, .dealer-reports-page .dealer-reports-status-section .dashboard-panel-body, .dealer-reports-page .reports-product-section .dashboard-panel-body { padding:0 16px 14px; }
        .dealer-reports-page .reports-inquiry-heading .dashboard-panel-title, .dealer-reports-page .reports-product-heading .dashboard-panel-title, .dealer-reports-page .dealer-reports-status-heading .dashboard-panel-title { font-size:16px; }
        .dealer-reports-page .reports-inquiry-subtitle, .dealer-reports-page .reports-product-subtitle, .dealer-reports-page .dealer-reports-status-subtitle { font-size:11.5px; }
        .dealer-reports-page .reports-inquiry-chip { padding:5px 9px; }
        .dealer-reports-page .dealer-reports-card, .dealer-reports-page .reports-product-card, .dealer-reports-page .dealer-reports-status-card { border-radius:15px; padding:10px 12px 8px; }
        .dealer-reports-page .reports-inquiry-section .dealer-reports-card { padding:7px 9px 3px; }
        .dealer-reports-page .reports-inquiry-section .dealer-reports-chart-wrapper { height:250px !important; }
        .dealer-reports-page .dealer-reports-empty, .dealer-reports-page .reports-product-empty { padding:14px 8px; font-size:12px; }
        .dealer-reports-page .report-status-body { gap:10px; }
        .dealer-reports-page .report-donut { width:160px; height:160px; }
        .dealer-reports-page .report-donut-center { inset:20px; }
        .dealer-reports-page .report-donut-total { font-size:19px; }
        .dealer-reports-page .report-legend { padding:0 2px 2px; gap:5px 10px; font-size:10.5px; }
        .dealer-reports-page .reports-product-scale-chip { padding:3px 7px; font-size:9.5px; }
        .dealer-reports-page .reports-product-chart-wrapper { padding:7px; }
    }
    @media (max-width:1200px) { .dealer-reports-page .dashboard-panels-two-column { grid-template-columns:minmax(0,1fr); } }
    @media (max-width:768px) {
        .dealer-reports-page .reports-period-form--dealer { --dealer-report-action-width:100%; --dealer-report-action-height:46px; width:100%; display:grid; grid-template-columns:1fr; justify-content:stretch; }
        .dealer-reports-page .reports-period-quick-group, .dealer-reports-page .reports-period-export { width:100%; min-width:0; }
        .dealer-reports-page .reports-period-range-inline { width:100%; display:flex; }
        .dealer-reports-page .reports-period-range-inline.is-hidden { display:none; }
        .dealer-reports-page .reports-period-range-inline .reports-period-date { width:50%; min-width:0; }
        .dealer-reports-page .reports-inquiry-section .dashboard-panel-header, .dealer-reports-page .dealer-reports-status-section .dashboard-panel-header, .dealer-reports-page .reports-product-section .dashboard-panel-header { padding:16px 16px 10px; flex-direction:column; align-items:flex-start; }
        .dealer-reports-page .reports-inquiry-section .dashboard-panel-body, .dealer-reports-page .dealer-reports-status-section .dashboard-panel-body, .dealer-reports-page .reports-product-section .dashboard-panel-body { padding:0 16px 16px; }
        .dealer-reports-page .dealer-reports-card, .dealer-reports-page .reports-product-card, .dealer-reports-page .dealer-reports-status-card { padding:12px; border-radius:14px; }
        .dealer-reports-page .reports-inquiry-heading .dashboard-panel-title, .dealer-reports-page .reports-product-heading .dashboard-panel-title, .dealer-reports-page .dealer-reports-status-heading .dashboard-panel-title { font-size:16px; }
        .dealer-reports-page .reports-inquiry-meta { justify-content:flex-start; }
        .dealer-reports-page .report-legend { grid-template-columns:1fr; }
    }
</style>
@endpush
@section('content')
<div class="dashboard-content reports-page dealer-reports-page">
    <header class="reports-header reports-header--dealer">
        <div class="reports-header-actions">
            <form method="get" action="{{ route('dealer.reports') }}" class="reports-period-form reports-period-form-compact reports-period-form--dealer" id="reportsPeriodForm">
                <div class="reports-period-quick-group">
                    <select name="period" id="reportsPeriodSelect" class="reports-period-select reports-period-select--dealer" aria-label="Report period">
                        <option value="30_days" {{ ($period ?? '60_days') === '30_days' ? 'selected' : '' }}>30 Days</option>
                        <option value="60_days" {{ ($period ?? '60_days') === '60_days' ? 'selected' : '' }}>60 Days</option>
                        <option value="90_days" {{ ($period ?? '60_days') === '90_days' ? 'selected' : '' }}>90 Days</option>
                        <option value="range" {{ ($period ?? '60_days') === 'range' ? 'selected' : '' }}>Range</option>
                    </select>
                </div>
                <div class="reports-period-range-inline{{ ($period ?? '60_days') === 'range' ? '' : ' is-hidden' }}" id="reportsRangeInline">
                    <input type="date" name="from" id="reportsRangeFrom" class="reports-period-select reports-period-select--dealer reports-period-date" value="{{ $from ?? '' }}" aria-label="From date">
                    <input type="date" name="to" id="reportsRangeTo" class="reports-period-select reports-period-select--dealer reports-period-date" value="{{ $to ?? '' }}" aria-label="To date">
                </div>
                <button type="button" class="report-filter-export reports-period-export" data-export-report-pdf data-export-title="Dealer Performance Report - {{ $periodLabel ?? '60 Days' }}" data-export-target=".dashboard-content.reports-page">Export PDF</button>
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
        <div class="reports-metric-card">
            <div class="reports-metric-icon reports-metric-icon-failed dealer-reports-failed-icon"><i class="bi bi-x-circle"></i></div>
            <div class="reports-metric-value">{{ $statusCounts['FAILED'] ?? 0 }}</div>
            <div class="reports-metric-label">FAILED</div>
        </div>
    </section>

    @php
        $statusReportData = [
            ['label' => 'Pending', 'value' => (int) ($statusCounts['PENDING'] ?? 0), 'color' => '#f97316', 'dark_color' => '#f97316'],
            ['label' => 'Follow Up', 'value' => (int) ($statusCounts['FOLLOW UP'] ?? 0), 'color' => '#f59e0b', 'dark_color' => '#f59e0b'],
            ['label' => 'Demo', 'value' => (int) ($statusCounts['DEMO'] ?? 0), 'color' => '#eab308', 'dark_color' => '#eab308'],
            ['label' => 'Confirmed', 'value' => (int) ($statusCounts['CONFIRMED'] ?? 0), 'color' => '#84cc16', 'dark_color' => '#84cc16'],
            ['label' => 'Completed', 'value' => (int) ($statusCounts['COMPLETED'] ?? 0), 'color' => '#22c55e', 'dark_color' => '#22c55e'],
            ['label' => 'Rewarded', 'value' => (int) ($statusCounts['REWARDED'] ?? 0), 'color' => '#15803d', 'dark_color' => '#15803d'],
            ['label' => 'Failed', 'value' => (int) ($statusCounts['FAILED'] ?? 0), 'color' => '#111827', 'dark_color' => '#f8fafc'],
        ];
        $totalStatus = max(array_sum(array_column($statusReportData, 'value')), 1);
        $buildStatusGradient = function (string $colorKey) use ($statusReportData, $totalStatus) {
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
                    'color' => $item[$colorKey] ?? $item['color'] ?? '#e5e7eb',
                ];
                $offset += $percent;
            }

            return collect($segments)->map(function ($segment) {
                return $segment['color'] . ' ' . $segment['from'] . '% ' . $segment['to'] . '%';
            })->implode(', ');
        };
        $statusGradient = $buildStatusGradient('color');
        $statusGradientDark = $buildStatusGradient('dark_color');
        $productNames = [
            1 => 'SQL Account',
            2 => 'SQL Payroll',
            3 => 'SQL Production',
            4 => 'Mobile Sales',
            5 => 'SQL Ecommerce',
            6 => 'SQL EBI Wellness POS',
            7 => 'SQL X Suduai',
            8 => 'SQL X-Store',
            9 => 'SQL Vision',
            10 => 'SQL HRMS',
            11 => 'Others',
        ];
        $productCounts = $productCounts ?? array_fill(0, 11, 0);
        $productConversionDisplay = collect(range(1, 11))
            ->map(function ($productId) use ($productNames, $productCounts) {
                return [
                    'label' => (string) ($productNames[$productId] ?? 'Product'),
                    'count' => (int) ($productCounts[$productId - 1] ?? 0),
                ];
            })
            ->sort(function ($a, $b) {
                $aIsOthers = strtoupper(trim((string) ($a['label'] ?? ''))) === 'OTHERS';
                $bIsOthers = strtoupper(trim((string) ($b['label'] ?? ''))) === 'OTHERS';
                if ($aIsOthers && !$bIsOthers) return 1;
                if (!$aIsOthers && $bIsOthers) return -1;
                $countCompare = ($b['count'] ?? 0) <=> ($a['count'] ?? 0);
                if ($countCompare !== 0) return $countCompare;
                return strcasecmp((string) ($a['label'] ?? ''), (string) ($b['label'] ?? ''));
            })
            ->values();
        $hasInquiryTrendData = (int) ($totalInquiry ?? 0) > 0 && collect($inquiryTrendData ?? [])->sum() > 0;
        $productChartHeightPx = max(260, $productConversionDisplay->count() * 24 + 44);
    @endphp

    <section class="dashboard-panels-two-column">
        <section class="dashboard-panel reports-inquiry-section">
            <div class="dashboard-panel-header">
                <div class="reports-inquiry-heading">
                    <div class="dashboard-panel-title">Inquiry Trends</div>
                    <div class="reports-inquiry-subtitle">Inquiries for {{ $periodLabel ?? '60 Days' }}</div>
                </div>
                <div class="reports-inquiry-meta">
                    <span class="reports-inquiry-chip">
                        <span class="reports-inquiry-chip-label">Total</span>
                        <span class="reports-inquiry-chip-value">{{ number_format($totalInquiry ?? 0) }}</span>
                    </span>
                </div>
            </div>
            <div class="dashboard-panel-body">
                <div class="dealer-reports-card">
                    @if (!$hasInquiryTrendData)
                        <p class="dealer-reports-empty">No leads created in this period yet.</p>
                    @else
                        <div class="dealer-reports-chart-wrapper" style="height: 336px;">
                            <p class="dealer-reports-chart-fallback">Unable to load inquiry trend chart.</p>
                            <canvas id="dealerInquiryTrendChart"></canvas>
                        </div>
                    @endif
                </div>
            </div>
        </section>

        <section class="dashboard-panel dealer-reports-status-section">
            <div class="dashboard-panel-header">
                <div class="dealer-reports-status-heading">
                    <div class="dashboard-panel-title">Status Report</div>
                    <div class="dealer-reports-status-subtitle">Current status distribution for {{ $periodLabel ?? '60 Days' }}</div>
                </div>
            </div>
            <div class="dashboard-panel-body report-status-body">
                <div class="dealer-reports-status-card">
                    <div class="report-donut-wrapper">
                        <div class="report-donut"
                             data-light-gradient="{{ $statusGradient ?: '#e5e7eb 0 100%' }}"
                             data-dark-gradient="{{ $statusGradientDark ?: '#334155 0 100%' }}"
                             style="background: conic-gradient({{ $statusGradient ?: '#e5e7eb 0 100%' }});">
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
                            <span class="report-legend-color{{ ($item['label'] ?? '') === 'Failed' ? ' report-legend-color--failed' : '' }}"
                                  data-light-color="{{ $item['color'] ?? '#e5e7eb' }}"
                                  data-dark-color="{{ $item['dark_color'] ?? ($item['color'] ?? '#e5e7eb') }}"
                                  style="background-color: {{ $item['color'] ?? '#e5e7eb' }}"></span>
                            <span class="report-legend-label">{{ $item['label'] }}</span>
                            <span class="report-legend-value">{{ $item['value'] }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    </section>

    <section class="dashboard-panel dashboard-table-panel reports-product-section">
        <div class="dashboard-panel-header">
            <div class="reports-product-heading">
                <div class="dashboard-panel-title">Product Conversion Rate</div>
                <div class="reports-product-subtitle">Closed-case conversions by product for {{ $periodLabel ?? '60 Days' }}</div>
            </div>
            <div class="reports-product-scale" aria-hidden="true">
                <span class="reports-product-scale-chip"><span class="reports-product-scale-dot reports-product-scale-dot--high"></span>High</span>
                <span class="reports-product-scale-chip"><span class="reports-product-scale-dot reports-product-scale-dot--medium"></span>Medium</span>
                <span class="reports-product-scale-chip"><span class="reports-product-scale-dot reports-product-scale-dot--low"></span>Low</span>
            </div>
        </div>
        <div class="dashboard-panel-body">
            <div class="reports-product-card">
                <div class="reports-product-chart-wrapper" style="height: {{ $productChartHeightPx }}px;">
                    <p class="reports-product-chart-fallback">Unable to load product conversion chart.</p>
                    <canvas id="dealerProductConversionChart"></canvas>
                </div>
            </div>
        </div>
    </section>

</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function initDealerReportsPage() {
    var periodSelect = document.getElementById('reportsPeriodSelect');
    var rangeWrap = document.getElementById('reportsRangeInline');
    var rangeFrom = document.getElementById('reportsRangeFrom');
    var rangeTo = document.getElementById('reportsRangeTo');
    var form = document.getElementById('reportsPeriodForm');
    var rangeSubmitTimer = null;

    function getTodayValue() {
        var today = new Date();
        var year = today.getFullYear();
        var month = String(today.getMonth() + 1).padStart(2, '0');
        var day = String(today.getDate()).padStart(2, '0');
        return year + '-' + month + '-' + day;
    }

    function syncRangeInputs() {
        if (!periodSelect || !rangeWrap || !rangeFrom || !rangeTo) return;
        var isRange = periodSelect.value === 'range';
        rangeWrap.classList.toggle('is-hidden', !isRange);
        rangeFrom.disabled = !isRange;
        rangeTo.disabled = !isRange;
        rangeFrom.required = isRange;
        rangeTo.required = isRange;
        rangeTo.min = isRange ? (rangeFrom.value || '') : '';
    }

    function scheduleRangeSubmit() {
        if (!form || !periodSelect || !rangeFrom || !rangeTo) return;
        if (periodSelect.value !== 'range') return;

        var from = rangeFrom.value;
        var to = rangeTo.value;
        if (!from || !to || from > to) return;

        window.clearTimeout(rangeSubmitTimer);
        rangeSubmitTimer = window.setTimeout(function() {
            if (typeof form.requestSubmit === 'function') {
                form.requestSubmit();
            } else {
                form.submit();
            }
        }, 180);
    }

    function submitReportFilter() {
        if (!form) return;
        window.clearTimeout(rangeSubmitTimer);
        window.setTimeout(function() {
            if (typeof form.requestSubmit === 'function') {
                form.requestSubmit();
            } else {
                form.submit();
            }
        }, 80);
    }

    if (periodSelect && rangeWrap && rangeFrom && rangeTo) {
        periodSelect.addEventListener('change', function() {
            syncRangeInputs();

            if (periodSelect.value === 'range') {
                var todayValue = getTodayValue();
                rangeFrom.value = rangeFrom.value || todayValue;
                rangeTo.value = rangeTo.value || todayValue;
                rangeTo.min = rangeFrom.value || '';
                scheduleRangeSubmit();
                return;
            }

            rangeFrom.value = '';
            rangeTo.value = '';
            submitReportFilter();
        });
        rangeFrom.addEventListener('change', function() {
            rangeTo.min = rangeFrom.value || '';
            scheduleRangeSubmit();
        });
        rangeTo.addEventListener('change', scheduleRangeSubmit);
        rangeFrom.addEventListener('input', scheduleRangeSubmit);
        rangeTo.addEventListener('input', scheduleRangeSubmit);
        syncRangeInputs();
    }

    if (form && periodSelect && rangeFrom && rangeTo) {
        form.addEventListener('submit', function(e) {
            if (periodSelect.value !== 'range') return;
            var from = rangeFrom.value;
            var to = rangeTo.value;
            if (!from || !to || from > to) {
                e.preventDefault();
                rangeFrom.focus();
            }
        });
    }

    function syncStatusReportTheme() {
        var darkTheme = document.documentElement.classList.contains('theme-dark');
        var donut = document.querySelector('.dealer-reports-page .report-donut');
        var failedMetricIcon = document.querySelector('.dealer-reports-page .dealer-reports-failed-icon');
        var defaultLightGradient = '#e5e7eb 0 100%';
        var defaultDarkGradient = '#334155 0 100%';

        if (donut) {
            var gradient = donut.getAttribute(darkTheme ? 'data-dark-gradient' : 'data-light-gradient') || (darkTheme ? defaultDarkGradient : defaultLightGradient);
            donut.style.background = 'conic-gradient(' + gradient + ')';
        }

        document.querySelectorAll('.dealer-reports-page .report-legend-color').forEach(function(node) {
            var color = node.getAttribute(darkTheme ? 'data-dark-color' : 'data-light-color') || (darkTheme ? '#cbd5e1' : '#e5e7eb');
            node.style.backgroundColor = color;
            if (node.classList.contains('report-legend-color--failed')) {
                node.style.boxShadow = darkTheme ? '0 0 0 1px rgba(148,163,184,.4)' : '';
            } else {
                node.style.boxShadow = '';
            }
        });

        if (failedMetricIcon) {
            if (darkTheme) {
                failedMetricIcon.style.background = 'linear-gradient(135deg,#f8fafc 0%,#dbe5f6 100%)';
                failedMetricIcon.style.color = '#111827';
                failedMetricIcon.style.boxShadow = '0 0 0 1px rgba(255,255,255,.18), 0 12px 24px rgba(2,6,23,.22)';
            } else {
                failedMetricIcon.style.background = '';
                failedMetricIcon.style.color = '';
                failedMetricIcon.style.boxShadow = '';
            }
        }
    }

    syncStatusReportTheme();

    if (!window.__dealerReportsThemeObserverBound) {
        var dealerReportsThemeObserver = new MutationObserver(function() {
            syncStatusReportTheme();
        });
        dealerReportsThemeObserver.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['class']
        });
        window.__dealerReportsThemeObserverBound = true;
    }

    function showChartFallback(wrapper, fallback, message) {
        if (wrapper) wrapper.classList.add('is-error');
        if (fallback) fallback.textContent = message;
    }

    if (typeof Chart === 'undefined') {
        var missingInquiryWrapper = document.querySelector('.dealer-reports-chart-wrapper');
        var missingInquiryFallback = missingInquiryWrapper ? missingInquiryWrapper.querySelector('.dealer-reports-chart-fallback') : null;
        showChartFallback(missingInquiryWrapper, missingInquiryFallback, 'Inquiry trend chart could not be loaded right now.');

        var missingProductWrapper = document.querySelector('.reports-product-chart-wrapper');
        var missingProductFallback = missingProductWrapper ? missingProductWrapper.querySelector('.reports-product-chart-fallback') : null;
        showChartFallback(missingProductWrapper, missingProductFallback, 'Product conversion chart could not be loaded right now.');
        return;
    }

    var inquiryCanvas = document.getElementById('dealerInquiryTrendChart');
    if (inquiryCanvas) {
        var inquiryWrapper = inquiryCanvas.closest('.dealer-reports-chart-wrapper');
        var inquiryFallback = inquiryWrapper ? inquiryWrapper.querySelector('.dealer-reports-chart-fallback') : null;

        try {
            var rawInquiryLabels = @json(array_values($trendLabels ?? []));
            var inquiryValues = @json(array_values($inquiryTrendData ?? []));
            var reportPeriod = @json($period ?? '60_days');
            var currentMonthName = @json(now()->format('F'));
            var currentYear = @json(now()->format('Y'));
            var currentMonthNumber = @json((int) now()->format('n'));
            var darkTheme = document.documentElement.classList.contains('theme-dark');
            var brandColor = darkTheme ? '#b296ff' : '#7f5af0';
            var columnColor = darkTheme ? 'rgba(178, 150, 255, 0.34)' : 'rgba(127, 90, 240, 0.32)';
            var gridColor = darkTheme ? 'rgba(148, 163, 184, 0.16)' : 'rgba(148, 163, 184, 0.25)';
            var axisColor = darkTheme ? 'rgba(148, 163, 184, 0.22)' : 'rgba(148, 163, 184, 0.28)';
            var legendColor = darkTheme ? '#c8d2eb' : '#334155';
            var tickColor = darkTheme ? '#9fb0d4' : '#8b95b5';
            var inquiryLabels = rawInquiryLabels.map(function(label) {
                return String(label || '').trim();
            });
            var tooltipLabels = rawInquiryLabels.map(function(label) {
                var normalized = String(label || '').trim();
                if (reportPeriod === 'month' && normalized && /^\d+$/.test(normalized)) {
                    return normalized + ' ' + currentMonthName;
                }
                return normalized;
            });

            function buildMonthlyWeekBuckets(labels, values) {
                var monthStart = new Date(Date.UTC(Number(currentYear), Math.max(Number(currentMonthNumber) - 1, 0), 1));
                var daysInMonth = new Date(Date.UTC(Number(currentYear), Number(currentMonthNumber), 0)).getUTCDate();
                var firstWeekday = monthStart.getUTCDay();
                var mondayOffset = firstWeekday === 0 ? 6 : firstWeekday - 1;
                var totalWeeks = Math.floor((mondayOffset + daysInMonth - 1) / 7) + 1;

                var bucketLabels = [];
                var bucketValues = new Array(totalWeeks).fill(0);
                var bucketRanges = new Array(totalWeeks).fill(null).map(function() {
                    return { firstDay: null, lastDay: null };
                });

                for (var weekIndex = 0; weekIndex < totalWeeks; weekIndex++) {
                    bucketLabels.push('Week ' + (weekIndex + 1));
                }

                labels.forEach(function(label, index) {
                    var dayNumber = parseInt(String(label || '').trim(), 10);
                    if (!Number.isFinite(dayNumber) || dayNumber < 1 || dayNumber > daysInMonth) {
                        return;
                    }

                    var weekIndex = Math.floor((mondayOffset + dayNumber - 1) / 7);
                    var safeWeekIndex = Math.max(0, Math.min(totalWeeks - 1, weekIndex));
                    bucketValues[safeWeekIndex] += Number(values[index]) || 0;

                    if (bucketRanges[safeWeekIndex].firstDay === null || dayNumber < bucketRanges[safeWeekIndex].firstDay) {
                        bucketRanges[safeWeekIndex].firstDay = dayNumber;
                    }

                    if (bucketRanges[safeWeekIndex].lastDay === null || dayNumber > bucketRanges[safeWeekIndex].lastDay) {
                        bucketRanges[safeWeekIndex].lastDay = dayNumber;
                    }
                });

                var bucketTooltips = bucketRanges.map(function(range, index) {
                    if (range.firstDay !== null && range.lastDay !== null) {
                        return currentMonthName + ' ' + range.firstDay + ', ' + currentYear + ' - ' + currentMonthName + ' ' + range.lastDay + ', ' + currentYear;
                    }

                    return 'Week ' + (index + 1);
                });

                return {
                    labels: bucketLabels,
                    values: bucketValues,
                    tooltips: bucketTooltips
                };
            }

            if (reportPeriod === 'month') {
                var monthBuckets = buildMonthlyWeekBuckets(inquiryLabels, inquiryValues);
                inquiryLabels = monthBuckets.labels;
                inquiryValues = monthBuckets.values;
                tooltipLabels = monthBuckets.tooltips;
            }

            var showAllMonthWeekTicks = reportPeriod === 'month';
            var showAllYearTicks = reportPeriod === 'year';
            var showAllPeriodTicks = showAllMonthWeekTicks || showAllYearTicks;
            var maxTickCount = inquiryLabels.length > 24 ? 11 : (inquiryLabels.length > 14 ? 9 : (inquiryLabels.length > 7 ? 7 : inquiryLabels.length));
            var tickStep = inquiryLabels.length > 24 ? 3 : (inquiryLabels.length > 14 ? 2 : 1);
            var maxInquiryValue = inquiryValues.length ? Math.max.apply(null, inquiryValues) : 0;
            function clearInquiryHover(chart) {
                chart.setActiveElements([]);
                if (chart.tooltip) {
                    chart.tooltip.setActiveElements([], { x: 0, y: 0 });
                }
            }

            function getNearestDateIndex(chart, x) {
                var scale = chart.scales.x;
                var labels = chart.data.labels || [];
                if (!scale || !labels.length) {
                    return null;
                }

                var nearestIndex = 0;
                var smallestDistance = Infinity;
                for (var i = 0; i < labels.length; i++) {
                    var tickPixel = scale.getPixelForTick(i);
                    var distance = Math.abs(x - tickPixel);
                    if (distance < smallestDistance) {
                        smallestDistance = distance;
                        nearestIndex = i;
                    }
                }

                return nearestIndex;
            }

            var exactDateHover = {
                id: 'dealerInquiryExactDateHover',
                afterEvent: function(chart, args) {
                    var event = args.event;
                    var chartArea = chart.chartArea;
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

                    if (event.x < chartArea.left || event.x > chartArea.right || event.y < chartArea.top || event.y > chartArea.bottom) {
                        clearInquiryHover(chart);
                        args.changed = true;
                        return;
                    }

                    var nearestIndex = getNearestDateIndex(chart, event.x);
                    if (nearestIndex === null) {
                        clearInquiryHover(chart);
                        args.changed = true;
                        return;
                    }

                    var activeElements = chart.data.datasets.map(function(dataset, datasetIndex) {
                        return { datasetIndex: datasetIndex, index: nearestIndex };
                    });
                    var anchorX = chart.scales.x.getPixelForTick(nearestIndex);
                    var anchorY = chart.scales.y.getPixelForValue(Number(inquiryValues[nearestIndex] || 0));

                    chart.setActiveElements(activeElements);
                    if (chart.tooltip) {
                        chart.tooltip.setActiveElements(activeElements, { x: anchorX, y: anchorY });
                    }
                    args.changed = true;
                }
            };
            var activeDateGuide = {
                id: 'dealerInquiryHoverGuide',
                afterDatasetsDraw: function(chart) {
                    var tooltip = chart.tooltip;
                    if (!tooltip || tooltip.opacity === 0 || !tooltip.dataPoints || !tooltip.dataPoints.length) {
                        return;
                    }

                    var activePoint = tooltip.dataPoints[0] && tooltip.dataPoints[0].element ? tooltip.dataPoints[0].element : null;
                    if (!activePoint) {
                        return;
                    }

                    var ctx = chart.ctx;
                    var chartArea = chart.chartArea;
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

            new Chart(inquiryCanvas.getContext('2d'), {
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
                            display: true,
                            position: 'bottom',
                            align: 'center',
                            labels: {
                                usePointStyle: true,
                                pointStyle: 'circle',
                                boxWidth: 9,
                                boxHeight: 9,
                                padding: 18,
                                color: legendColor,
                                font: {
                                    size: 12,
                                    weight: '500'
                                }
                            }
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
                                title: function(items) {
                                    if (!items || !items.length) {
                                        return '';
                                    }
                                    var item = items[0];
                                    return tooltipLabels[item.dataIndex] || item.label || '';
                                },
                                label: function(context) {
                                    var value = typeof context.parsed.y === 'number' ? context.parsed.y : 0;
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
                                color: tickColor,
                                padding: 4,
                                font: {
                                    size: showAllMonthWeekTicks ? 10 : (showAllYearTicks ? 10 : (inquiryLabels.length > 10 ? 10 : 11)),
                                    weight: '500'
                                },
                                autoSkip: !showAllPeriodTicks,
                                maxTicksLimit: showAllPeriodTicks ? inquiryLabels.length : maxTickCount,
                                callback: function(value, index) {
                                    var label = this.getLabelForValue(value);
                                    if (showAllPeriodTicks) {
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
                                maxRotation: showAllMonthWeekTicks ? 0 : (showAllYearTicks ? 0 : (inquiryLabels.length > 7 ? 35 : 0)),
                                minRotation: showAllMonthWeekTicks ? 0 : (showAllYearTicks ? 0 : (inquiryLabels.length > 7 ? 35 : 0))
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
                                color: tickColor,
                                padding: 6,
                                font: {
                                    size: 10,
                                    weight: '500'
                                },
                                stepSize: maxInquiryValue <= 6 ? 1 : undefined,
                                callback: function(value) {
                                    return Math.round(value);
                                }
                            }
                        }
                    }
                }
            });

            if (inquiryWrapper) inquiryWrapper.classList.remove('is-error');
        } catch (error) {
            console.error('Dealer inquiry trend chart failed to render.', error);
            showChartFallback(inquiryWrapper, inquiryFallback, 'Unable to render inquiry trend chart.');
        }
    }

    var productCanvas = document.getElementById('dealerProductConversionChart');
    if (productCanvas) {
        var productWrapper = productCanvas.closest('.reports-product-chart-wrapper');
        var productFallback = productWrapper ? productWrapper.querySelector('.reports-product-chart-fallback') : null;

        try {
            var rawProducts = @json($productConversionDisplay->values());
            var darkTheme = document.documentElement.classList.contains('theme-dark');
            var products = rawProducts.map(function(item) {
                return {
                    label: String(item.label || ''),
                    count: Number(item.count || 0)
                };
            });

            var labels = products.map(function(item) { return item.label; });
            var dataValues = products.map(function(item) { return item.count; });
            var totalValue = dataValues.reduce(function(sum, value) { return sum + value; }, 0);
            var maxValue = dataValues.length ? Math.max.apply(null, dataValues) : 0;

            function getPerformanceTone(value) {
                var ratio = maxValue > 0 ? value / maxValue : 0;
                if (ratio >= 0.67) return { level: 'High', background: '#22c55e', border: '#16a34a' };
                if (ratio >= 0.34) return { level: 'Medium', background: '#f59e0b', border: '#d97706' };
                return { level: 'Low', background: '#ef4444', border: '#dc2626' };
            }

            var toneMap = dataValues.map(function(value) { return getPerformanceTone(value); });
            var barColors = toneMap.map(function(tone) { return tone.background; });
            var borderColors = toneMap.map(function(tone) { return tone.border; });
            var axisMax = maxValue > 0 ? Math.max(maxValue + 1, Math.ceil(maxValue * 1.35)) : 1;

            var endValueLabels = {
                id: 'dealerEndValueLabels',
                afterDatasetsDraw: function(chart) {
                    var meta = chart.getDatasetMeta(0);
                    if (!meta || !meta.data || !meta.data.length) return;

                    var ctx = chart.ctx;
                    var chartArea = chart.chartArea;
                    ctx.save();
                    ctx.font = '600 12px "Public Sans", sans-serif';
                    ctx.fillStyle = darkTheme ? '#c8d2eb' : '#475569';
                    ctx.textBaseline = 'middle';

                    meta.data.forEach(function(bar, index) {
                        var value = dataValues[index] || 0;
                        var pct = totalValue > 0 ? Math.round((value / totalValue) * 100) : 0;
                        var text = value + ' (' + pct + '%)';
                        var textWidth = ctx.measureText(text).width;
                        var x = bar.x + 10;
                        if (x + textWidth > chartArea.right - 4) {
                            x = chartArea.right - textWidth - 4;
                        }
                        ctx.fillText(text, x, bar.y);
                    });

                    ctx.restore();
                }
            };

            new Chart(productCanvas.getContext('2d'), {
                type: 'bar',
                plugins: [endValueLabels],
                data: {
                    labels: labels,
                    datasets: [{
                        axis: 'y',
                        label: 'Product conversions',
                        data: dataValues,
                        backgroundColor: function(context) {
                            return barColors[context.dataIndex] || '#94a3b8';
                        },
                        borderColor: function(context) {
                            return borderColors[context.dataIndex] || '#64748b';
                        },
                        borderWidth: 1,
                        borderSkipped: false,
                        borderRadius: 8,
                        barThickness: 14,
                        maxBarThickness: 16,
                        categoryPercentage: 0.78,
                        barPercentage: 0.82,
                        hoverBackgroundColor: function(context) {
                            return borderColors[context.dataIndex] || '#475569';
                        }
                    }]
                },
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
                            left: 6
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            displayColors: false,
                            backgroundColor: 'rgba(15, 23, 42, 0.94)',
                            titleColor: '#ffffff',
                            bodyColor: '#ffffff',
                            padding: 12,
                            cornerRadius: 10,
                            callbacks: {
                                title: function(items) {
                                    return items[0] && items[0].label ? items[0].label : 'Product';
                                },
                                label: function(context) {
                                    var value = context.parsed && typeof context.parsed.x === 'number' ? Math.round(context.parsed.x) : 0;
                                    var pct = totalValue > 0 ? ((value / totalValue) * 100).toFixed(1) : '0.0';
                                    return 'Conversions: ' + value + ' (' + pct + '%)';
                                },
                                afterLabel: function(context) {
                                    var tone = toneMap[context.dataIndex];
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
                                color: darkTheme ? 'rgba(148, 163, 184, 0.14)' : 'rgba(148, 163, 184, 0.10)',
                                drawBorder: false,
                                drawTicks: false
                            },
                            border: {
                                display: false
                            },
                            ticks: {
                                color: darkTheme ? '#9fb0d4' : '#94a3b8',
                                font: {
                                    size: 11,
                                    weight: '600'
                                },
                                padding: 8,
                                stepSize: maxValue <= 10 ? 1 : undefined,
                                callback: function(value) {
                                    return Math.round(value);
                                }
                            }
                        },
                        y: {
                            grid: {
                                display: false
                            },
                            border: {
                                display: false
                            },
                            ticks: {
                                autoSkip: false,
                                padding: 10,
                                color: darkTheme ? '#eef2ff' : '#0f172a',
                                font: {
                                    size: 11,
                                    weight: '600'
                                }
                            }
                        }
                    }
                }
            });

            if (productWrapper) productWrapper.classList.remove('is-error');
        } catch (error) {
            console.error('Dealer product conversion chart failed to render.', error);
            showChartFallback(productWrapper, productFallback, 'Unable to render product conversion chart.');
        }
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initDealerReportsPage, { once: true });
} else {
    initDealerReportsPage();
}
</script>
@endpush
@endsection
