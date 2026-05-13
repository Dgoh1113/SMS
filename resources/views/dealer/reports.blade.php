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
    .dealer-reports-page .reports-range-grid {
        display: grid;
        grid-template-columns: 1fr 1fr auto;
        align-items: flex-end;
        gap: 6px;
        width: 100%;
        margin-top: 8px;
    }
    .dealer-reports-page .reports-range-input {
        width: 100%;
        padding-right: 12px;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        color: #0f172a;
    }
    .dealer-reports-page .reports-range-label {
        display: block;
        font-size: 9px;
        font-weight: 800;
        color: #94a3b8;
        margin-bottom: 4px;
        text-transform: uppercase;
    }
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
        .dealer-reports-page .reports-period-form--dealer { --dealer-report-action-width:100%; --dealer-report-action-height:46px; width:100%; display:flex; flex-direction:column; justify-content:stretch; gap: 16px; }
        .dealer-reports-page .reports-filter-container { width: 100% !important; max-width: none !important; }
        .dealer-reports-page .reports-range-grid { grid-template-columns: 1fr 1fr !important; }
        .dealer-reports-page .reports-range-grid .reports-range-col:last-child {
            grid-column: span 2;
            flex-direction: row !important;
            justify-content: flex-end !important;
            gap: 8px !important;
            padding-top: 4px !important;
        }
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
    .dealer-reports-page .reports-admin-metric-trend { display:flex; align-items:center; gap:4px; margin-top:10px; font-size:11px; font-weight:600; }
    .dealer-reports-page .reports-admin-metric-trend--up { color:#10b981; }
    .dealer-reports-page .reports-admin-metric-trend--down { color:#ef4444; }
    .dealer-reports-page .reports-admin-metric-trend--same { color:#94a3b8; }
    .dealer-reports-page .reports-admin-metric-trend-icon { width:14px; height:14px; flex-shrink:0; }
    .dealer-reports-page .reports-admin-metric-trend-icon--same { transform:rotate(-90deg); }
    html.theme-dark .dealer-reports-page .reports-admin-metric-trend--up { color:#34d399; }
    html.theme-dark .dealer-reports-page .reports-admin-metric-trend--down { color:#f87171; }
    html.theme-dark .dealer-reports-page .reports-admin-metric-trend--same { color:#64748b; }
    
    .reports-fullscreen-btn {
        display: none;
        position: absolute;
        top: 8px;
        right: 8px;
        z-index: 50;
        background: rgba(255, 255, 255, 0.94);
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: 6px;
        color: #64748b;
        cursor: pointer;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    }

    .dashboard-panel:-webkit-full-screen {
        background: #ffffff !important;
        padding: 16px !important;
        overflow: auto !important;
        display: flex !important;
        flex-direction: column !important;
        position: fixed !important;
        z-index: 9999;
        height: 100vh !important;
        width: 100vw !important;
    }
    .dashboard-panel:fullscreen {
        background: #ffffff !important;
        padding: 16px !important;
        overflow: auto !important;
        display: flex !important;
        flex-direction: column !important;
        position: fixed !important;
        z-index: 9999;
        height: 100vh !important;
        width: 100vw !important;
    }
    .dashboard-panel:-webkit-full-screen .reports-product-chart-mobile-wrapper {
        display: block !important;
    }
    .dashboard-panel:fullscreen .reports-product-chart-mobile-wrapper {
        display: block !important;
    }
    .dashboard-panel:-webkit-full-screen .reports-product-chart-desktop-wrapper {
        display: none !important;
    }
    .dashboard-panel:fullscreen .reports-product-chart-desktop-wrapper {
        display: none !important;
    }

    @media (max-width: 768px) {
        .reports-fullscreen-btn {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .reports-inquiry-section .dashboard-panel-body {
            padding: 0 8px 12px !important;
        }
        .dealer-reports-card {
            max-width: 100% !important;
            overflow: hidden !important;
        }
        .dealer-reports-chart-scroll-wrapper {
            position: relative;
            display: block !important;
            width: 100% !important;
            overflow-x: auto !important;
            overflow-y: hidden !important;
            -webkit-overflow-scrolling: touch !important;
            padding-bottom: 20px !important;
            cursor: grab;
        }
        .dealer-reports-chart-scroll-wrapper::-webkit-scrollbar {
            height: 10px !important;
        }
        .dealer-reports-chart-scroll-wrapper::-webkit-scrollbar-track {
            background: #f1f5f9 !important;
            border-radius: 10px !important;
            margin: 0 30px !important;
        }
        .dealer-reports-chart-scroll-wrapper::-webkit-scrollbar-thumb {
            background: #7c3aed !important;
            border-radius: 10px !important;
            border: 2px solid #f1f5f9 !important;
        }
        .dealer-reports-chart-wrapper#dealerInquiryTrendChartWrapper {
            width: 900px !important;
            min-width: 900px !important;
            height: 260px !important;
        }
        .dealer-reports-chart-wrapper#dealerInquiryTrendChartWrapper canvas {
            width: 100% !important;
            touch-action: pan-x !important;
        }
        .reports-product-chart-desktop-wrapper {
            display: none !important;
        }
        .reports-product-chart-mobile-wrapper {
            display: block !important;
        }
        .reports-product-chart-wrapper#dealerProductConversionChartMobileWrapper canvas {
            width: 100% !important;
            height: 100% !important;
            touch-action: pan-x !important;
        }
    }
</style>
@endpush
@section('content')
<div class="dashboard-content reports-page dealer-reports-page">
    <header class="reports-header reports-header--dealer">
        <div class="reports-header-actions">
            <form method="get" action="{{ route('dealer.reports') }}" class="reports-period-form reports-period-form-compact reports-period-form--dealer" id="reportsPeriodForm">
                <div class="reports-period-date-group-wrapper reports-filter-container" style="width: 340px; min-height: 90px; display: flex; flex-direction: column;">
                    <div class="reports-range-label">PERIOD</div>
                    <select name="period" id="reportsPeriodSelect" class="reports-period-select reports-period-select--dealer" aria-label="Report period" style="display: {{ ($period ?? '60_days') === 'range' ? 'none' : 'block' }};">
                        <option value="30_days" {{ ($period ?? '60_days') === '30_days' ? 'selected' : '' }}>Last 30 Days</option>
                        <option value="60_days" {{ ($period ?? '60_days') === '60_days' ? 'selected' : '' }}>Last 60 Days</option>
                        <option value="90_days" {{ ($period ?? '60_days') === '90_days' ? 'selected' : '' }}>Last 90 Days</option>
                        <option value="range" {{ ($period ?? '60_days') === 'range' ? 'selected' : '' }}>Custom range…</option>
                    </select>
                    <div id="reportsRangeInline" class="reports-range-grid" style="display: {{ ($period ?? '60_days') === 'range' ? 'grid' : 'none' }};">
                        <div class="reports-range-col">
                            <label class="reports-range-label">Starting</label>
                            <input type="date" name="from" id="reportsRangeFrom" value="{{ $from ?? '' }}" class="reports-range-input" aria-label="From date">
                        </div>
                        <div class="reports-range-col">
                            <label class="reports-range-label">Ending</label>
                            <input type="date" name="to" id="reportsRangeTo" value="{{ $to ?? '' }}" class="reports-range-input" aria-label="To date">
                        </div>
                        <div class="reports-range-col" style="display: flex; flex-direction: column; gap: 4px; align-items: center; justify-content: flex-end; padding-bottom: 2px;">
                            <button type="button" class="reports-range-back-btn" id="reportsRangeReset" title="Reset" style="position: static; margin: 0; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-x-lg" style="font-size: 11px;"></i>
                            </button>
                            <button type="button" class="reports-range-back-btn" id="reportsRangeSubmit" title="Search" style="position: static; margin: 0; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-search" style="font-size: 11px;"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <button type="button" class="report-filter-export reports-period-export" data-export-report-pdf data-export-title="Dealer Performance Report - {{ $periodLabel ?? 'Current Month' }}" data-export-target=".dashboard-content.reports-page" style="flex-shrink: 0;">Export PDF</button>
            </form>
        </div>
    </header>

    {{-- Metric Cards --}}
    <section class="reports-metrics">
        @php
            $metricCards = [
                ['label' => 'PENDING', 'key' => 'PENDING', 'icon' => 'bi-file-earmark', 'icon_class' => 'reports-metric-icon-pending'],
                ['label' => 'FOLLOW UP', 'key' => 'FOLLOW UP', 'icon' => 'bi-calendar-event', 'icon_class' => 'reports-metric-icon-followup'],
                ['label' => 'DEMO', 'key' => 'DEMO', 'icon' => 'bi-person-video2', 'icon_class' => 'reports-metric-icon-demo'],
                ['label' => 'CONFIRMED', 'key' => 'CONFIRMED', 'icon' => 'bi-check-circle', 'icon_class' => 'reports-metric-icon-confirmed'],
                ['label' => 'COMPLETED', 'key' => 'COMPLETED', 'icon' => 'bi-box-seam', 'icon_class' => 'reports-metric-icon-completed'],
                ['label' => 'REWARDED', 'key' => 'REWARDED', 'icon' => 'bi-gift', 'icon_class' => 'reports-metric-icon-reward'],
                ['label' => 'FAILED', 'key' => 'FAILED', 'icon' => 'bi-x-circle', 'icon_class' => 'reports-metric-icon-failed dealer-reports-failed-icon'],
            ];
        @endphp

        @foreach($metricCards as $card)
            @php
                $val = $statusCounts[$card['key']] ?? 0;
                $pct = $metricPercent[$card['key']] ?? 0;
                $trend = $pct > 0 ? 'up' : ($pct < 0 ? 'down' : 'same');
            @endphp
            <div class="reports-metric-card">
                <div class="reports-metric-icon {{ $card['icon_class'] }}"><i class="bi {{ $card['icon'] }}"></i></div>
                <div class="reports-metric-value">{{ $val }}</div>
                <div class="reports-metric-label">{{ $card['label'] }}</div>
                <div class="reports-admin-metric-trend reports-admin-metric-trend--{{ $trend }}">
                    @if ($trend === 'up')
                        <svg class="reports-admin-metric-trend-icon" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v13m0-13 4 4m-4-4-4 4"/></svg>
                        <span>+{{ $pct }}% vs last period</span>
                    @elseif ($trend === 'down')
                        <svg class="reports-admin-metric-trend-icon" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19V5m0 0-4 4m4-4 4 4"/></svg>
                        <span>{{ $pct }}% vs last period</span>
                    @else
                        <svg class="reports-admin-metric-trend-icon reports-admin-metric-trend-icon--same" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-4 4 4-4m0 0 4-4"/></svg>
                        <span>No change</span>
                    @endif
                </div>
            </div>
        @endforeach
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
            <div class="dashboard-panel-header" style="position: relative; padding-right: 40px;">
                <button type="button" class="reports-fullscreen-btn" onclick="toggleChartFullscreen(this.closest('.dashboard-panel'))" aria-label="Toggle Fullscreen">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="20" height="20">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 8V4h4M4 4l5 5M20 8V4h-4M20 4l-5 5M4 16v4h4M4 20l5-5M20 16v4h-4M20 20l-5-5" />
                    </svg>
                </button>
                <div class="reports-inquiry-heading">
                    <div class="dashboard-panel-title">Inquiry Trends</div>
                    <div class="reports-inquiry-subtitle">Inquiries for {{ $periodLabel ?? 'Current Month' }}</div>
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
                        <div class="dealer-reports-chart-scroll-wrapper">
                            <div class="dealer-reports-chart-wrapper" id="dealerInquiryTrendChartWrapper" style="height: 336px;">
                                <p class="dealer-reports-chart-fallback">Unable to load inquiry trend chart.</p>
                                <canvas id="dealerInquiryTrendChart"></canvas>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </section>

        <section class="dashboard-panel dealer-reports-status-section">
            <div class="dashboard-panel-header">
                <div class="dealer-reports-status-heading">
                    <div class="dashboard-panel-title">Status Report</div>
                    <div class="dealer-reports-status-subtitle">Current status distribution for {{ $periodLabel ?? 'Current Month' }}</div>
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
        <div class="dashboard-panel-header" style="position: relative; padding-right: 40px;">
            <button type="button" class="reports-fullscreen-btn" onclick="toggleChartFullscreen(this.closest('.dashboard-panel'))" aria-label="Toggle Fullscreen">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="20" height="20">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 8V4h4M4 4l5 5M20 8V4h-4M20 4l-5 5M4 16v4h4M4 20l5-5M20 16v4h-4M20 20l-5-5" />
                </svg>
            </button>
            <div class="reports-product-heading">
                <div class="dashboard-panel-title">Product Conversion Rate</div>
                <div class="reports-product-subtitle">Closed-case conversions by product for {{ $periodLabel ?? 'Current Month' }}</div>
            </div>
            <div class="reports-product-scale" aria-hidden="true">
                <span class="reports-product-scale-chip"><span class="reports-product-scale-dot reports-product-scale-dot--high"></span>High</span>
                <span class="reports-product-scale-chip"><span class="reports-product-scale-dot reports-product-scale-dot--medium"></span>Medium</span>
                <span class="reports-product-scale-chip"><span class="reports-product-scale-dot reports-product-scale-dot--low"></span>Low</span>
            </div>
        </div>
        <div class="dashboard-panel-body">
            <div class="reports-product-card">
                @php
                    $itemCount = $productConversionDisplay->count();
                    $barHeightPx = 20;
                    $gapPx = 10;
                    $paddingPx = 44;
                    $chartHeightPx = max(220, $itemCount * ($barHeightPx + $gapPx) + $paddingPx);
                    $mobileChartWidthPx = max(750, $itemCount * 110);
                @endphp
                <div class="reports-product-chart-desktop-wrapper" id="dealerProductConversionChartDesktopWrapper" style="height: {{ $chartHeightPx }}px;">
                    <p class="reports-product-chart-fallback">Unable to load product conversion chart.</p>
                    <canvas id="dealerProductConversionChartDesktop"></canvas>
                </div>
                
                <div class="dealer-reports-chart-scroll-wrapper reports-product-chart-mobile-wrapper" style="display: none;">
                    <div class="reports-product-chart-wrapper" id="dealerProductConversionChartMobileWrapper" style="width: {{ $mobileChartWidthPx }}px; min-width: {{ $mobileChartWidthPx }}px; height: 300px;">
                        <p class="reports-product-chart-fallback">Unable to load product conversion chart.</p>
                        <canvas id="dealerProductConversionChartMobile"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </section>

</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function toggleChartFullscreen(element) {
    if (!document.fullscreenElement) {
        if (element.requestFullscreen) {
            element.requestFullscreen().then(() => {
                if (screen.orientation && screen.orientation.lock) {
                    screen.orientation.lock('landscape').catch(() => {});
                }
            }).catch(err => {
                console.error(`Fullscreen failed: ${err.message}`);
            });
        } else if (element.webkitRequestFullscreen) {
            element.webkitRequestFullscreen();
        } else if (element.msRequestFullscreen) {
            element.msRequestFullscreen();
        }
    } else {
        if (document.exitFullscreen) {
            document.exitFullscreen();
            if (screen.orientation && screen.orientation.unlock) {
                screen.orientation.unlock();
            }
        } else if (document.webkitExitFullscreen) {
            document.webkitExitFullscreen();
        } else if (document.msExitFullscreen) {
            document.msExitFullscreen();
        }
    }
}

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
        rangeWrap.style.display = isRange ? 'grid' : 'none';
        periodSelect.style.display = isRange ? 'none' : 'block';
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
                return;
            }

            rangeFrom.value = '';
            rangeTo.value = '';
            submitReportFilter();
        });
        var handleDateSubmit = function(e) {
            if (e.type === 'keydown' && e.key !== 'Enter') {
                return;
            }
            if (e.type === 'keydown' && e.key === 'Enter') {
                e.preventDefault();
            }
            var from = rangeFrom.value;
            var to = rangeTo.value;
            if (from && from.length >= 10 && to && to.length >= 10 && from <= to) {
                submitReportFilter();
            }
        };

        var rangeSubmitBtn = document.getElementById('reportsRangeSubmit');
        if (rangeSubmitBtn) {
            rangeSubmitBtn.addEventListener('click', function(e) {
                handleDateSubmit(e);
            });
        }

        rangeFrom.addEventListener('keydown', handleDateSubmit);
        rangeTo.addEventListener('keydown', handleDateSubmit);
        
        // Date inputs will only submit via Enter key (handled above) or the Search button.
        rangeFrom.addEventListener('input', function() {
            rangeTo.min = rangeFrom.value || '';
            if (rangeTo.value && rangeFrom.value && rangeTo.value < rangeFrom.value) {
                rangeTo.value = rangeFrom.value;
            }
        });

        var rangeReset = document.getElementById('reportsRangeReset');
        if (rangeReset) {
            rangeReset.addEventListener('click', function() {
                periodSelect.value = '60_days';
                syncRangeInputs();
                submitReportFilter();
            });
        }

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
            var reportPeriod = @json($period ?? 'month');
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

            var totalDays = inquiryLabels.length;
            var tickStep = 15;
            if (totalDays > 180) {
                tickStep = 30;
            } else if (totalDays >= 30 && totalDays <= 45) {
                tickStep = 3;
            } else if (totalDays < 30) {
                if (totalDays <= 7) tickStep = 1;
                else if (totalDays <= 14) tickStep = 2;
                else tickStep = 3;
            } else {
                tickStep = 15;
            }

            var maxTickCount = Math.ceil(totalDays / tickStep) + 2;
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
                                    size: 10,
                                    weight: '500'
                                },
                                autoSkip: false,
                                maxTicksLimit: inquiryLabels.length,
                                callback: function(value, index) {
                                    var label = this.getLabelForValue(value);
                                    if (index === 0 || index === inquiryLabels.length - 1 || index % tickStep === 0) {
                                        return label;
                                    }
                                    return '';
                                },
                                maxRotation: 0,
                                minRotation: 0
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


    var renderProductChart = function(canvasId, wrapperId, isMobileChart) {
        var canvas = document.getElementById(canvasId);
        if (!canvas) return;
        var wrapper = document.getElementById(wrapperId);
        var fallback = wrapper ? wrapper.querySelector('.reports-product-chart-fallback') : null;

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
                id: 'dealerEndValueLabels_' + canvasId,
                afterDatasetsDraw: function(chart) {
                    var meta = chart.getDatasetMeta(0);
                    if (!meta || !meta.data || !meta.data.length) return;

                    var ctx = chart.ctx;
                    var chartArea = chart.chartArea;
                    ctx.save();
                    ctx.font = '600 12px "Public Sans", sans-serif';
                    ctx.fillStyle = darkTheme ? '#c8d2eb' : '#475569';
                    
                    if (isMobileChart) {
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'bottom';
                    } else {
                        ctx.textAlign = 'left';
                        ctx.textBaseline = 'middle';
                    }

                    meta.data.forEach(function(bar, index) {
                        var value = dataValues[index] || 0;
                        var pct = totalValue > 0 ? Math.round((value / totalValue) * 100) : 0;
                        var text = value + (isMobileChart ? '' : ' (' + pct + '%)');
                        
                        if (isMobileChart) {
                            var y = bar.y - 8;
                            if (y < chartArea.top + 12) y = chartArea.top + 12;
                            ctx.fillText(text, bar.x, y);
                        } else {
                            var textWidth = ctx.measureText(text).width;
                            var x = bar.x + 10;
                            if (x + textWidth > chartArea.right - 4) {
                                x = chartArea.right - textWidth - 4;
                            }
                            ctx.fillText(text, x, bar.y);
                        }
                    });

                    ctx.restore();
                }
            };

            var chartConfig = {
                type: 'bar',
                plugins: [endValueLabels],
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Product conversions',
                        data: dataValues,
                        backgroundColor: function(context) { return barColors[context.dataIndex] || '#94a3b8'; },
                        borderColor: function(context) { return borderColors[context.dataIndex] || '#64748b'; },
                        borderWidth: 1,
                        borderSkipped: false,
                        borderRadius: 8,
                        hoverBackgroundColor: function(context) { return borderColors[context.dataIndex] || '#475569'; }
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    animation: { duration: 260, easing: 'easeOutCubic' },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            displayColors: false,
                            backgroundColor: 'rgba(15, 23, 42, 0.94)',
                            titleColor: '#ffffff',
                            bodyColor: '#ffffff',
                            padding: 12,
                            cornerRadius: 10,
                            callbacks: {
                                title: function(items) { return items[0] && items[0].label ? items[0].label : 'Product'; },
                                label: function(context) {
                                    var value = context.parsed && typeof context.parsed[isMobileChart ? 'y' : 'x'] === 'number' ? Math.round(context.parsed[isMobileChart ? 'y' : 'x']) : 0;
                                    var pct = totalValue > 0 ? ((value / totalValue) * 100).toFixed(1) : '0.0';
                                    return 'Conversions: ' + value + ' (' + pct + '%)';
                                },
                                afterLabel: function(context) {
                                    var tone = toneMap[context.dataIndex];
                                    return tone ? 'Performance: ' + tone.level : '';
                                }
                            }
                        }
                    }
                }
            };

            if (isMobileChart) {
                chartConfig.options.indexAxis = 'x';
                chartConfig.data.datasets[0].barThickness = 32;
                chartConfig.data.datasets[0].maxBarThickness = 48;
                chartConfig.options.layout = { padding: { top: 24, right: 0, bottom: 0, left: 0 } };
                chartConfig.options.scales = {
                    x: {
                        grid: { display: false },
                        border: { display: false },
                        ticks: {
                            color: darkTheme ? '#9fb0d4' : '#94a3b8',
                            font: { size: 10, weight: '600' },
                            maxRotation: 45,
                            minRotation: 45
                        }
                    },
                    y: {
                        beginAtZero: true,
                        max: axisMax,
                        grid: {
                            color: darkTheme ? 'rgba(148, 163, 184, 0.14)' : 'rgba(148, 163, 184, 0.10)',
                            drawBorder: false,
                            drawTicks: false,
                            borderDash: [4, 4]
                        },
                        border: { display: false },
                        ticks: {
                            color: darkTheme ? '#9fb0d4' : '#94a3b8',
                            font: { size: 11, weight: '600' },
                            padding: 8,
                            stepSize: maxValue <= 10 ? 1 : undefined,
                            callback: function(value) { return Math.round(value); }
                        }
                    }
                };
            } else {
                chartConfig.options.indexAxis = 'y';
                chartConfig.data.datasets[0].barThickness = 14;
                chartConfig.data.datasets[0].maxBarThickness = 16;
                chartConfig.options.layout = { padding: { top: 4, right: 72, bottom: 0, left: 6 } };
                chartConfig.options.scales = {
                    x: {
                        beginAtZero: true,
                        max: axisMax,
                        grid: {
                            color: darkTheme ? 'rgba(148, 163, 184, 0.14)' : 'rgba(148, 163, 184, 0.10)',
                            drawBorder: false,
                            drawTicks: false
                        },
                        border: { display: false },
                        ticks: {
                            color: darkTheme ? '#9fb0d4' : '#94a3b8',
                            font: { size: 11, weight: '600' },
                            padding: 8,
                            stepSize: maxValue <= 10 ? 1 : undefined,
                            callback: function(value) { return Math.round(value); }
                        }
                    },
                    y: {
                        grid: { display: false },
                        border: { display: false },
                        ticks: {
                            autoSkip: false,
                            padding: 10,
                            color: darkTheme ? '#eef2ff' : '#0f172a',
                            font: { size: 11, weight: '600' }
                        }
                    }
                };
            }

            new Chart(canvas.getContext('2d'), chartConfig);
            if (wrapper) wrapper.classList.remove('is-error');
        } catch (error) {
            console.error('Dealer product conversion chart failed to render.', error);
            showChartFallback(wrapper, fallback, 'Unable to render product conversion chart.');
        }
    };

    renderProductChart('dealerProductConversionChartDesktop', 'dealerProductConversionChartDesktopWrapper', false);
    renderProductChart('dealerProductConversionChartMobile', 'dealerProductConversionChartMobileWrapper', true);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initDealerReportsPage, { once: true });
} else {
    initDealerReportsPage();
}
</script>
@endpush
@endsection
