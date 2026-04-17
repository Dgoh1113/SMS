@extends('layouts.app')
@section('title', 'Dealers – Admin')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/pages/admin-inquiries.css') }}?v=20260415-07">
<style>
.dealers-page-wrap {
    padding: 15px;
    box-sizing: border-box;
}

.dealers-page-wrap .dealers-page-panel {
    margin-bottom: 0;
}

.dealers-page-wrap .dealers-page-body {
    padding: 0;
}

.dealers-page-wrap .inquiries-panel-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 16px 18px 12px;
}

.dealers-page-wrap .inquiries-panel-title-wrap {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
}

.dealers-page-wrap .inquiries-panel-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #7c3aed;
    font-size: 21px;
}

.dealers-page-wrap .inquiries-panel-title {
    margin: 0;
    color: #111827;
    font-size: 18px;
    font-weight: 700;
    line-height: 1.3;
}

.dealers-page-wrap .inquiries-panel-actions,
.dealers-page-wrap .dealers-panel-buttons {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.dealers-page-wrap .inquiries-panel-actions {
    margin-left: auto;
}

.dealers-page-wrap .inquiries-table-wrap {
    display: flex;
    flex-direction: column;
    min-height: 0;
}

.dealers-page-wrap .inquiries-table-scroll {
    overflow-x: auto;
    min-height: 0 !important;
    height: auto !important;
    max-height: none !important;
}

.dealers-page-wrap #dealersTable {
    width: max-content;
    min-width: 100%;
    table-layout: auto;
}

.dealers-page-wrap #dealersTable tbody td {
    padding-top: 10px;
    padding-bottom: 10px;
    line-height: 1.45;
}

.dealers-page-wrap #dealersTable th[data-col="totallead"],
.dealers-page-wrap #dealersTable th[data-col="totalongoing"],
.dealers-page-wrap #dealersTable th[data-col="totalclosed"],
.dealers-page-wrap #dealersTable th[data-col="totalfailed"],
.dealers-page-wrap #dealersTable td[data-col="totallead"],
.dealers-page-wrap #dealersTable td[data-col="totalongoing"],
.dealers-page-wrap #dealersTable td[data-col="totalclosed"],
.dealers-page-wrap #dealersTable td[data-col="totalfailed"] {
    width: 78px;
    min-width: 78px;
    max-width: 78px;
    text-align: center;
}

.dealers-page-wrap #dealersTable th[data-col="conversionrate"],
.dealers-page-wrap #dealersTable td[data-col="conversionrate"] {
    min-width: 84px;
    max-width: 92px;
}

.dealers-page-wrap .dealers-pagination,
.dealers-page-wrap .inquiries-assigned-pagination {
    margin-top: 0;
}

#dealersTable tbody tr.dealer-placeholder-row,
#dealersTable tbody tr.dealer-placeholder-row:hover {
    background: transparent !important;
    pointer-events: none;
}

#dealersTable tbody tr.dealer-placeholder-row td,
#dealersTable tbody tr.dealer-placeholder-row:hover td {
    background: transparent !important;
}

#dealersTable .dealer-placeholder-cell {
    padding: 0 !important;
    border-top: none !important;
    border-bottom: none !important;
    background: transparent !important;
    box-shadow: none !important;
}

html.theme-dark #dealersTable tbody tr.dealer-placeholder-row td,
html.theme-dark #dealersTable tbody tr.dealer-placeholder-row:hover td {
    background: transparent !important;
    box-shadow: none !important;
}

@media (min-width: 1440px) and (min-height: 860px) {
    .dealers-page-wrap {
        padding: 20px 22px 18px;
    }

    .dealers-page-wrap .inquiries-panel-header {
        padding: 20px 22px 14px;
        gap: 14px;
    }

    .dealers-page-wrap .inquiries-panel-title-wrap {
        gap: 14px;
    }

    .dealers-page-wrap .inquiries-panel-icon {
        font-size: 24px;
    }

    .dealers-page-wrap .inquiries-panel-title {
        font-size: 20px;
    }

    .dealers-page-wrap .inquiries-btn.inquiries-btn-secondary {
        min-height: 40px;
        padding: 9px 18px;
        font-size: 14px;
    }

    .dealers-page-wrap #dealersTable th,
    .dealers-page-wrap #dealersTable td {
        padding: 10px 12px;
        font-size: 13px;
    }

    .dealers-page-wrap #dealersTable tbody td {
        padding-top: 12px;
        padding-bottom: 12px;
        line-height: 1.5;
    }

    .dealers-page-wrap #dealersTable thead th {
        font-size: 12px;
    }

    .dealers-page-wrap .dealers-table-with-filters thead th.inquiries-header-cell {
        padding: 8px 10px;
    }

    .dealers-page-wrap .dealers-table-with-filters thead th .inquiries-header-label {
        margin-bottom: 6px;
    }

    .dealers-page-wrap .inquiries-header-cell .inquiries-grid-filter,
    .dealers-page-wrap .inquiries-header-cell .inquiries-grid-filter-all,
    .dealers-page-wrap .inquiries-header-cell .inquiries-grid-filter-assigned,
    .dealers-page-wrap .inquiries-header-cell .inquiries-grid-filter-select,
    .dealers-page-wrap .inquiries-header-cell .dealer-grid-filter {
        padding: 7px 28px 7px 10px;
        font-size: 12px;
        min-height: 32px;
    }

    .dealers-page-wrap .inquiries-header-cell .inquiries-filter-icon {
        right: 8px;
        font-size: 13px;
    }

    .dealers-page-wrap #dealersTable .dealer-operator-search-box {
        min-height: 32px;
    }

    .dealers-page-wrap .dealer-conversion-label {
        padding: 5px 10px;
        font-size: 12px;
    }

    .dealers-page-wrap .inquiries-assigned-pagination {
        padding: 10px 14px;
        gap: 10px;
    }

    .dealers-page-wrap .inquiries-assigned-pagination-info {
        font-size: 13px;
    }

    .dealers-page-wrap .inquiries-assigned-pagination-nav {
        gap: 6px;
    }

    .dealers-page-wrap .inquiries-pagination-num,
    .dealers-page-wrap .inquiries-pagination-btn {
        min-width: 30px;
        padding: 5px 9px;
        font-size: 13px;
    }
}

@media (max-height: 900px) {
    .dealers-page-wrap .inquiries-panel-header {
        padding: 12px 14px 10px;
    }

    .dealers-page-wrap .inquiries-btn.inquiries-btn-secondary {
        min-height: 34px;
        padding: 7px 14px;
        font-size: 12px;
    }

    .dealers-page-wrap #dealersTable th,
    .dealers-page-wrap #dealersTable td {
        padding: 7px 10px;
        font-size: 12px;
    }

    .dealers-page-wrap #dealersTable tbody td {
        padding-top: 8px;
        padding-bottom: 8px;
    }

    .dealers-page-wrap #dealersTable thead th {
        font-size: 11px;
    }

    .dealers-page-wrap .dealers-table-with-filters thead th.inquiries-header-cell {
        padding: 6px 8px;
    }

    .dealers-page-wrap .dealers-table-with-filters thead th .inquiries-header-label {
        margin-bottom: 4px;
    }

    .dealers-page-wrap .inquiries-header-cell .inquiries-grid-filter,
    .dealers-page-wrap .inquiries-header-cell .inquiries-grid-filter-all,
    .dealers-page-wrap .inquiries-header-cell .inquiries-grid-filter-assigned,
    .dealers-page-wrap .inquiries-header-cell .inquiries-grid-filter-select,
    .dealers-page-wrap .inquiries-header-cell .dealer-grid-filter {
        padding: 5px 24px 5px 8px;
        font-size: 11px;
        min-height: 28px;
    }

    .dealers-page-wrap .inquiries-header-cell .inquiries-filter-icon {
        right: 7px;
        font-size: 12px;
    }

    .dealers-page-wrap #dealersTable .dealer-operator-search-box {
        min-height: 28px;
    }

    .dealers-page-wrap .dealer-conversion-label {
        padding: 3px 8px;
        font-size: 11px;
    }

    .dealers-page-wrap .inquiries-assigned-pagination {
        margin-top: 6px;
        padding: 8px 10px;
        gap: 8px;
    }

    .dealers-page-wrap .inquiries-assigned-pagination-info {
        font-size: 12px;
    }

    .dealers-page-wrap .inquiries-assigned-pagination-nav {
        gap: 4px;
    }

    .dealers-page-wrap .inquiries-pagination-num,
    .dealers-page-wrap .inquiries-pagination-btn {
        min-width: 26px;
        padding: 4px 7px;
        font-size: 12px;
    }
}

@media (max-width: 768px) {
    .dealers-page-wrap .inquiries-panel-header {
        flex-direction: column;
        align-items: stretch;
        padding: 14px 16px 10px;
        gap: 10px;
    }

    .dealers-page-wrap .inquiries-panel-actions,
    .dealers-page-wrap .dealers-panel-buttons {
        width: 100%;
        display: grid;
        grid-template-columns: 1fr;
        gap: 10px;
    }

    .dealers-page-wrap .inquiries-panel-actions > .inquiries-btn,
    .dealers-page-wrap .inquiries-panel-actions > .inquiries-columns-dropdown {
        width: 100%;
    }
}
</style>
@endpush
@section('content')
<div class="inquiries-page-wrap dealers-page-wrap">
<section class="dealers-page-panel inquiries-mgmt-panel">
    <div class="dealers-page-body">
        @php
            $dealersTotal = count($items);
            $dealersPerPage = 10;
            $dealersLastPage = $dealersTotal > 0 ? (int) ceil($dealersTotal / $dealersPerPage) : 1;
            $dealersTo = $dealersTotal === 0 ? 0 : min($dealersPerPage, $dealersTotal);
        @endphp
        <div class="inquiries-panel-header">
            <div class="inquiries-panel-title-wrap">
                <i class="bi bi-people inquiries-panel-icon"></i>
                <h2 class="inquiries-panel-title">Dealers</h2>
            </div>
            <div class="inquiries-panel-actions">
                <div class="inquiries-columns-dropdown">
                    <button type="button" class="inquiries-btn inquiries-btn-secondary" id="dealerColumnsBtn" aria-haspopup="true" aria-expanded="false">Columns</button>
                    <div class="inquiries-columns-menu" id="dealerColumnsMenu" hidden>
                    <div class="inquiries-columns-menu-title">Show columns</div>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="userid"> User ID</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="alias"> Alias</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="company"> Company name</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="email"> Email</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="postcode"> Postcode</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="city"> City</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="active"> Active</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="totallead"> Leads</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="totalongoing"> Ongoing</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="totalclosed"> Closed</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="totalfailed"> Failed</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="conversionrate"> Conversion</label>
                    <div class="inquiries-columns-actions">
                        <button type="button" class="inquiries-columns-action-btn" id="dealerColumnsAll">All</button>
                        <button type="button" class="inquiries-columns-action-btn" id="dealerColumnsNone">None</button>
                    </div>
                    <button type="button" class="inquiries-columns-reset" id="dealerColumnsReset">Reset to default</button>
                    </div>
                </div>
                <button type="button" class="inquiries-btn inquiries-btn-secondary" id="dealerClearFilters">Clear filters</button>
            </div>
        </div>
        <div class="inquiries-table-wrap">
            <div class="inquiries-table-scroll table-responsive">
            <table class="inquiries-table dealers-table-with-filters" id="dealersTable">
                <thead>
                    <tr class="inquiries-header-row">
                        <x-tables.text-filter-header col="userid" label="User ID" input-class="dealer-grid-filter" cell-class="dashboard-table-sortable inquiries-header-cell inquiries-sortable" wrap-class="inquiries-filter-wrap dealer-filter-input-wrap" />
                        <x-tables.text-filter-header col="alias" label="Alias" input-class="dealer-grid-filter" cell-class="dashboard-table-sortable inquiries-header-cell inquiries-sortable" wrap-class="inquiries-filter-wrap dealer-filter-input-wrap" />
                        <x-tables.text-filter-header col="company" label="Company name" input-class="dealer-grid-filter" cell-class="dashboard-table-sortable inquiries-header-cell inquiries-sortable" wrap-class="inquiries-filter-wrap dealer-filter-input-wrap" />
                        <x-tables.text-filter-header col="email" label="Email" input-class="dealer-grid-filter" cell-class="dashboard-table-sortable inquiries-header-cell inquiries-sortable" wrap-class="inquiries-filter-wrap dealer-filter-input-wrap" />
                        <x-tables.text-filter-header col="postcode" label="Postcode" input-class="dealer-grid-filter" cell-class="dashboard-table-sortable inquiries-header-cell inquiries-sortable" wrap-class="inquiries-filter-wrap dealer-filter-input-wrap" />
                        <x-tables.text-filter-header col="city" label="City" input-class="dealer-grid-filter" cell-class="dashboard-table-sortable inquiries-header-cell inquiries-sortable" wrap-class="inquiries-filter-wrap dealer-filter-input-wrap" />
                        <x-tables.operator-filter-header col="totallead" label="Leads" input-class="dealer-grid-filter" cell-class="dashboard-table-sortable inquiries-header-cell inquiries-sortable" />
                        <x-tables.operator-filter-header col="totalongoing" label="Ongoing" input-class="dealer-grid-filter" cell-class="dashboard-table-sortable inquiries-header-cell inquiries-sortable" />
                        <x-tables.operator-filter-header col="totalclosed" label="Closed" input-class="dealer-grid-filter" cell-class="dashboard-table-sortable inquiries-header-cell inquiries-sortable" />
                        <x-tables.operator-filter-header col="totalfailed" label="Failed" input-class="dealer-grid-filter" cell-class="dashboard-table-sortable inquiries-header-cell inquiries-sortable" />
                        <x-tables.text-filter-header col="conversionrate" label="Conversion" input-class="dealer-grid-filter" cell-class="dashboard-table-sortable inquiries-header-cell inquiries-sortable" wrap-class="inquiries-filter-wrap dealer-filter-input-wrap" />
                        <x-tables.text-filter-header col="active" label="Active" input-class="dealer-grid-filter" cell-class="dashboard-table-sortable inquiries-header-cell inquiries-sortable" wrap-class="inquiries-filter-wrap dealer-filter-input-wrap" />
                    </tr>
                </thead>
                <tbody id="dealersTableBody">
                    @include('admin.partials.dealers_rows', ['items' => $items])
                    @if(false)
                    @forelse($items as $r)
                        @php
                            $searchHaystack = strtolower(trim(($r->USERID ?? '').' '.($r->EMAIL ?? '').' '.($r->POSTCODE ?? '').' '.($r->CITY ?? '').' '.($r->COMPANY ?? '').' '.($r->ALIAS ?? '')));
                        @endphp
                        <tr class="dealer-row inquiry-row" data-search="{{ $searchHaystack }}">
                            <td data-col="userid">{{ $r->USERID }}</td>
                            <td data-col="alias">{{ $r->ALIAS ?? '—' }}</td>
                            <td data-col="company">{{ $r->COMPANY ?? '—' }}</td>
                            <td data-col="email">{{ $r->EMAIL }}</td>
                            <td data-col="postcode">{{ $r->POSTCODE ?? '—' }}</td>
                            <td data-col="city">{{ $r->CITY ?? '—' }}</td>
                            <td data-col="totallead">{{ number_format((int)($r->TOTAL_LEAD ?? 0)) }}</td>
                            <td data-col="totalongoing">{{ number_format((int)($r->TOTAL_ONGOING ?? 0)) }}</td>
                            <td data-col="totalclosed">{{ number_format((int)($r->TOTAL_CLOSED ?? 0)) }}</td>
                            <td data-col="totalfailed">{{ number_format((int)($r->TOTAL_FAILED ?? 0)) }}</td>
                            @php $convRate = (float)($r->CONVERSION_RATE ?? 0); $convClass = $convRate >= 60 ? 'dealer-conversion-high' : ($convRate >= 40 ? 'dealer-conversion-mid' : 'dealer-conversion-low'); @endphp
                            <td data-col="conversionrate"><span class="dealer-conversion-label {{ $convClass }}">{{ number_format($convRate, 1) }}%</span></td>
                            <td data-col="active">{{ ($r->ISACTIVE ?? 0) ? 'Yes' : 'No' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="12" class="inquiries-empty">No dealers yet.</td></tr>
                    @endforelse
                    @endif
                </tbody>
            </table>
            </div>
            <div class="inquiries-assigned-pagination dealers-pagination" id="dealersPagination"
             data-total="{{ $dealersTotal }}"
             data-per-page="{{ $dealersPerPage }}"
             data-current-page="1"
             data-last-page="{{ $dealersLastPage }}">
            <span class="inquiries-assigned-pagination-info">
                Showing {{ $dealersTotal === 0 ? 0 : 1 }} to {{ $dealersTo }} of {{ $dealersTotal }} entries (Page 1)
            </span>
            <div class="inquiries-assigned-pagination-nav">
                <button type="button" class="inquiries-btn inquiries-btn-secondary inquiries-pagination-btn" data-page="first">First</button>
                <button type="button" class="inquiries-btn inquiries-btn-secondary inquiries-pagination-btn" data-page="prev">Previous</button>
                <span class="inquiries-assigned-page-numbers" id="dealersPageNumbers"></span>
                <button type="button" class="inquiries-btn inquiries-btn-secondary inquiries-pagination-btn" data-page="next">Next</button>
                <button type="button" class="inquiries-btn inquiries-btn-secondary inquiries-pagination-btn" data-page="last">Last</button>
            </div>
        </div>
        </div>
    </div>
</section>
</div>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var table = document.getElementById('dealersTable');
    if (!table) return;
    var pagination = document.getElementById('dealersPagination');
    var state = { col: 'conversionrate', dir: -1 };
    window.dealersPaginationState = window.dealersPaginationState || { currentPage: 1, perPage: 10 };

    // ——— Column visibility (customizable like inquiries), Active hidden by default ———
    var DEALER_STORAGE_KEY = 'dealerVisibleColumns';
    var DEALER_DEFAULT_COLUMNS = ['userid','alias','company','email','postcode','city','totallead','totalongoing','totalclosed','totalfailed','conversionrate'];
    var DEALER_ALL_COLUMNS = ['userid','alias','company','email','postcode','city','active','totallead','totalongoing','totalclosed','totalfailed','conversionrate'];

    function getDealerVisibleColumns() {
        try {
            var raw = localStorage.getItem(DEALER_STORAGE_KEY);
            if (raw !== null) {
                var arr = JSON.parse(raw);
                if (Array.isArray(arr)) return arr;
            }
        } catch (e) {}
        return DEALER_DEFAULT_COLUMNS.slice();
    }
    function setDealerVisibleColumns(cols) {
        try { localStorage.setItem(DEALER_STORAGE_KEY, JSON.stringify(cols)); } catch (e) {}
    }
    function applyDealerColumns(visible) {
        if (!table) return;
        DEALER_ALL_COLUMNS.forEach(function(col) {
            var show = visible.indexOf(col) !== -1;
            table.querySelectorAll('th[data-col="' + col + '"], td[data-col="' + col + '"]').forEach(function(el) {
                el.style.display = show ? '' : 'none';
            });
        });
    }
    function syncDealerCheckboxes(visible) {
        var menu = document.getElementById('dealerColumnsMenu');
        if (!menu) return;
        menu.querySelectorAll('input[data-col]').forEach(function(cb) {
            var col = cb.getAttribute('data-col');
            cb.checked = visible.indexOf(col) !== -1;
        });
    }
    function refreshDealerColumnState() {
        var visible = getDealerVisibleColumns();
        syncDealerCheckboxes(visible);
        applyDealerColumns(visible);
    }

    var colBtn = document.getElementById('dealerColumnsBtn');
    var colMenu = document.getElementById('dealerColumnsMenu');
    if (colBtn && colMenu) {
        colBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            var isOpen = !colMenu.hidden;
            colMenu.hidden = isOpen;
            colBtn.setAttribute('aria-expanded', !isOpen);
            if (!isOpen) {
                colMenu.scrollTop = 0;
                refreshDealerColumnState();
            }
        });
        document.addEventListener('click', function() {
            colMenu.hidden = true;
            colBtn.setAttribute('aria-expanded', 'false');
        });
        colMenu.addEventListener('click', function(e) { e.stopPropagation(); });
    }
    if (colMenu) {
        colMenu.querySelectorAll('input[data-col]').forEach(function(cb) {
            cb.addEventListener('change', function() {
                var visible = [];
                colMenu.querySelectorAll('input[data-col]:checked').forEach(function(c) {
                    visible.push(c.getAttribute('data-col'));
                });
                setDealerVisibleColumns(visible);
                applyDealerColumns(visible);
                if (typeof measureAndSizeDealerColumns === 'function') measureAndSizeDealerColumns();
            });
        });
    }
    var resetBtn = document.getElementById('dealerColumnsReset');
    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            setDealerVisibleColumns(DEALER_DEFAULT_COLUMNS.slice());
            refreshDealerColumnState();
            if (typeof measureAndSizeDealerColumns === 'function') measureAndSizeDealerColumns();
        });
    }
    var allBtn = document.getElementById('dealerColumnsAll');
    if (allBtn) {
        allBtn.addEventListener('click', function() {
            setDealerVisibleColumns(DEALER_ALL_COLUMNS.slice());
            refreshDealerColumnState();
            if (typeof measureAndSizeDealerColumns === 'function') measureAndSizeDealerColumns();
        });
    }
    var noneBtn = document.getElementById('dealerColumnsNone');
    if (noneBtn) {
        noneBtn.addEventListener('click', function() {
            setDealerVisibleColumns([]);
            refreshDealerColumnState();
            if (typeof measureAndSizeDealerColumns === 'function') measureAndSizeDealerColumns();
        });
    }
    refreshDealerColumnState();

    // ——— Size columns (and search boxes) from longest data so all columns can show ———
    function measureAndSizeDealerColumns() {
        var headerRow = table.querySelector('thead tr');
        if (!headerRow) return;
        var padding = 40;
        var measureEl = document.createElement('span');
        measureEl.setAttribute('aria-hidden', 'true');
        measureEl.style.cssText = 'position:absolute;left:-9999px;top:0;white-space:nowrap;visibility:hidden;pointer-events:none;';
        measureEl.style.font = window.getComputedStyle(table).font;
        document.body.appendChild(measureEl);
        var widths = [];
        var theads = [];
        headerRow.querySelectorAll('th[data-col]').forEach(function(th) {
            var col = th.getAttribute('data-col');
            if (!col) return;
            if (th.style.display === 'none') return;
            var label = th.querySelector('.inquiries-header-label');
            var maxW = 0;
            if (label) {
                measureEl.textContent = label.textContent.trim();
                maxW = Math.max(maxW, measureEl.offsetWidth);
            }
            table.querySelectorAll('tbody td[data-col="' + col + '"]').forEach(function(td) {
                var t = td.textContent.trim();
                if (t) {
                    measureEl.textContent = t;
                    maxW = Math.max(maxW, measureEl.offsetWidth);
                }
            });
            var minW = (col === 'conversionrate') ? 100 : (col === 'city' || col === 'postcode') ? 80 : (col === 'company' || col === 'email') ? 100 : 64;
            var w = Math.min(Math.max(maxW + padding, minW), 320);
            widths.push(w);
            theads.push({ th: th, col: col });
        });
        document.body.removeChild(measureEl);
        var NUMERIC_COLS = ['totallead','totalongoing','totalclosed','totalfailed'];
        var numericIdx = NUMERIC_COLS.map(function(c) {
            var i = theads.findIndex(function(t) { return t.col === c; });
            return i;
        }).filter(function(i) { return i >= 0; });
        if (numericIdx.length > 0) {
            var numericWidth = Math.max.apply(null, numericIdx.map(function(i) { return widths[i]; }));
            numericWidth = Math.max(numericWidth, 90);
            numericIdx.forEach(function(i) { widths[i] = numericWidth; });
        }
        theads.forEach(function(item, i) {
            var w = widths[i];
            var wpx = w + 'px';
            item.th.style.width = wpx;
            item.th.style.maxWidth = wpx;
            item.th.style.minWidth = wpx;
            table.querySelectorAll('td[data-col="' + item.col + '"]').forEach(function(td) {
                td.style.width = wpx;
                td.style.maxWidth = wpx;
                td.style.minWidth = wpx;
            });
        });
    }
    requestAnimationFrame(function() { measureAndSizeDealerColumns(); });
    window.addEventListener('resize', function() {
        measureAndSizeDealerColumns();
        if (typeof window.dealersGoToPage === 'function') {
            window.dealersGoToPage(window.dealersPaginationState.currentPage || 1);
        }
    });

    // ——— Grid filters (text: contains; Leads/Ongoing/Closed/Failed: = or > or <) ———
    var DEALER_NUMERIC_COLS = ['totallead','totalongoing','totalclosed','totalfailed'];
    function parseNum(s) {
        var n = parseInt(String(s).replace(/\s|,/g, ''), 10);
        return isNaN(n) ? 0 : n;
    }
    function initDealerPagination() {
        if (!table || !pagination) return;

        var infoEl = pagination.querySelector('.inquiries-assigned-pagination-info');
        var pageNumbersEl = document.getElementById('dealersPageNumbers');
        var controls = pagination.querySelectorAll('.inquiries-pagination-btn');
        var perPage = parseInt(pagination.getAttribute('data-per-page') || '10', 10);
        window.dealersPaginationState.perPage = perPage;

        function getAllRows() {
            return Array.prototype.slice.call(table.querySelectorAll('tbody tr.dealer-row'));
        }

        function getMatchingRows() {
            return getAllRows().filter(function(row) {
                if (!row.dataset.filterMatch) row.dataset.filterMatch = '1';
                return row.dataset.filterMatch !== '0';
            });
        }

        function clearPlaceholderRows() {
            Array.prototype.slice.call(table.querySelectorAll('tbody tr.dealer-placeholder-row')).forEach(function(row) {
                row.remove();
            });
        }

        function measureDealerRowHeight(row) {
            if (!row) return 0;
            var directHeight = Math.round(row.getBoundingClientRect().height || row.offsetHeight || 0);
            if (directHeight > 0) {
                return directHeight;
            }

            var host = document.createElement('div');
            host.style.position = 'absolute';
            host.style.left = '-10000px';
            host.style.top = '0';
            host.style.visibility = 'hidden';
            host.style.pointerEvents = 'none';
            host.style.width = Math.round((table.getBoundingClientRect().width || 0)) > 0
                ? Math.round(table.getBoundingClientRect().width || 0) + 'px'
                : '1400px';

            var measureTable = document.createElement('table');
            measureTable.className = table.className || 'inquiries-table dealers-table-with-filters';
            measureTable.style.width = '100%';

            var measureBody = document.createElement('tbody');
            var clone = row.cloneNode(true);
            clone.style.display = '';
            measureBody.appendChild(clone);
            measureTable.appendChild(measureBody);
            host.appendChild(measureTable);
            document.body.appendChild(host);

            var measuredHeight = Math.round(clone.getBoundingClientRect().height || clone.offsetHeight || 0);
            host.remove();
            return measuredHeight > 0 ? measuredHeight : 0;
        }

        function ensureFixedHeight(visibleRows) {
            var tbody = table.querySelector('tbody');
            if (!tbody) return;
            clearPlaceholderRows();

            var visibleCount = Array.isArray(visibleRows) ? visibleRows.length : 0;
            if (visibleCount > 0 && visibleCount < perPage) {
                var sampleRow = visibleRows.find(function(row) {
                    return row && row.style.display !== 'none';
                }) || tbody.querySelector('tr.dealer-row:not([style*="display: none"])') || tbody.querySelector('tr.dealer-row');
                var rowHeight = measureDealerRowHeight(sampleRow);
                if (!rowHeight) rowHeight = 46;

                var headerCount = table.querySelectorAll('thead tr:first-child th[data-col]').length || 1;
                for (var i = visibleCount; i < perPage; i++) {
                    var row = document.createElement('tr');
                    row.className = 'dealer-placeholder-row';
                    row.setAttribute('aria-hidden', 'true');

                    var cell = document.createElement('td');
                    cell.className = 'dealer-placeholder-cell';
                    cell.colSpan = headerCount;
                    row.style.height = rowHeight + 'px';
                    cell.style.height = rowHeight + 'px';
                    cell.style.minHeight = rowHeight + 'px';

                    row.appendChild(cell);
                    tbody.appendChild(row);
                }
            }
        }

        function buildPageNumbers(currentPage, lastPage) {
            if (!pageNumbersEl) return;
            pageNumbersEl.innerHTML = '';
            for (var p = 1; p <= lastPage; p++) {
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'inquiries-pagination-num' + (p === currentPage ? ' inquiries-pagination-num-active' : '');
                btn.setAttribute('data-page', String(p));
                btn.textContent = String(p);
                btn.addEventListener('click', function(e) {
                    var target = e.currentTarget.getAttribute('data-page') || '1';
                    window.dealersGoToPage(target);
                });
                pageNumbersEl.appendChild(btn);
            }
        }

        function renderPage(page) {
            var matchingRows = getMatchingRows();
            var total = matchingRows.length;
            var lastPage = total > 0 ? Math.ceil(total / perPage) : 1;
            var safePage = Math.min(Math.max(parseInt(page || 1, 10), 1), lastPage);
            var start = total === 0 ? 0 : (safePage - 1) * perPage;
            var end = total === 0 ? 0 : Math.min(start + perPage, total);

            pagination.setAttribute('data-total', String(total));
            pagination.setAttribute('data-current-page', String(safePage));
            pagination.setAttribute('data-last-page', String(lastPage));
            window.dealersPaginationState.currentPage = safePage;
            clearPlaceholderRows();

            getAllRows().forEach(function(row) {
                row.style.display = 'none';
            });

            var visibleRows = matchingRows.slice(start, end);
            visibleRows.forEach(function(row) {
                row.style.display = '';
            });
            ensureFixedHeight(visibleRows);

            if (infoEl) {
                var fromText = total === 0 ? 0 : start + 1;
                infoEl.textContent = 'Showing ' + fromText + ' to ' + end + ' of ' + total + ' entries (Page ' + safePage + ')';
            }

            buildPageNumbers(safePage, lastPage);

            controls.forEach(function(btn) {
                var action = btn.getAttribute('data-page');
                var disabled = false;
                if (total === 0) {
                    disabled = true;
                } else if (action === 'first' || action === 'prev') {
                    disabled = safePage <= 1;
                } else if (action === 'next' || action === 'last') {
                    disabled = safePage >= lastPage;
                }
                btn.disabled = disabled;
            });
        }

        window.dealersGoToPage = function(page) {
            var action = String(page || '').toLowerCase();
            var currentPage = window.dealersPaginationState.currentPage || 1;
            var lastPage = parseInt(pagination.getAttribute('data-last-page') || '1', 10);
            var targetPage = currentPage;

            if (action === 'first') targetPage = 1;
            else if (action === 'prev') targetPage = currentPage - 1;
            else if (action === 'next') targetPage = currentPage + 1;
            else if (action === 'last') targetPage = lastPage;
            else targetPage = parseInt(page || '1', 10);

            renderPage(targetPage);
        };

        controls.forEach(function(btn) {
            if (btn.getAttribute('data-pagination-bound') === '1') return;
            btn.setAttribute('data-pagination-bound', '1');
            btn.addEventListener('click', function() {
                window.dealersGoToPage(btn.getAttribute('data-page') || '1');
            });
        });

        renderPage(window.dealersPaginationState.currentPage || 1);
    }

    function applyDealerGridFilters(resetPage) {
        var filters = {};
        table.querySelectorAll('.dealer-grid-filter').forEach(function(inp) {
            var col = inp.getAttribute('data-col');
            var val = (inp.value || '').trim();
            if (!col) return;
            if (DEALER_NUMERIC_COLS.indexOf(col) !== -1) {
                if (val === '') return;
                var opBtn = table.querySelector('.dealer-operator-btn[data-col="' + col + '"]');
                var op = (opBtn && opBtn.getAttribute('data-op')) ? opBtn.getAttribute('data-op') : '=';
                filters[col] = { val: val, op: op, numeric: true };
            } else {
                if (val === '') return;
                filters[col] = { val: val.toLowerCase(), numeric: false };
            }
        });
        table.querySelectorAll('tbody tr.dealer-row').forEach(function(row) {
            var match = true;
            for (var col in filters) {
                var cell = row.querySelector('td[data-col="' + col + '"]');
                var cellText = (cell && cell.textContent) ? cell.textContent.trim() : '';
                var f = filters[col];
                if (f.numeric) {
                    var cellNum = parseNum(cellText);
                    var filterNum = parseNum(f.val);
                    if (f.op === '=') { if (cellNum !== filterNum) match = false; }
                    else if (f.op === '!=') { if (cellNum === filterNum) match = false; }
                    else if (f.op === '>') { if (cellNum <= filterNum) match = false; }
                    else if (f.op === '>=') { if (cellNum < filterNum) match = false; }
                    else if (f.op === '<') { if (cellNum >= filterNum) match = false; }
                    else if (f.op === '<=') { if (cellNum > filterNum) match = false; }
                } else {
                    if (cellText.toLowerCase().indexOf(f.val) === -1) match = false;
                }
                if (!match) break;
            }
            row.dataset.filterMatch = match ? '1' : '0';
        });
        initDealerPagination();
        if (window.dealersGoToPage) {
            window.dealersGoToPage(resetPage ? 1 : (window.dealersPaginationState.currentPage || 1));
        }
    }

    table.querySelectorAll('.dealer-grid-filter').forEach(function(inp) {
        inp.addEventListener('input', function() { applyDealerGridFilters(true); });
        inp.addEventListener('keyup', function() { applyDealerGridFilters(true); });
    });
    table.querySelectorAll('.dealer-operator-btn').forEach(function(btn) {
        var col = btn.getAttribute('data-col');
        var dropdown = btn.parentElement.querySelector('.dealer-operator-dropdown');
        if (!dropdown) return;
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            var open = !dropdown.hidden;
            table.querySelectorAll('.dealer-operator-dropdown').forEach(function(d) { d.hidden = true; });
            table.querySelectorAll('.dealer-operator-btn').forEach(function(b) { b.setAttribute('aria-expanded', 'false'); });
            dropdown.hidden = open;
            btn.setAttribute('aria-expanded', !open);
        });
        dropdown.addEventListener('click', function(e) { e.stopPropagation(); });
        dropdown.querySelectorAll('button[data-op]').forEach(function(opt) {
            opt.addEventListener('click', function(e) {
                e.stopPropagation();
                var op = opt.getAttribute('data-op');
                var label = op === '=' ? '=' : op === '!=' ? '≠' : op === '<=' ? '≤' : op === '>=' ? '≥' : op;
                btn.setAttribute('data-op', op);
                btn.textContent = label;
                dropdown.hidden = true;
                btn.setAttribute('aria-expanded', 'false');
                applyDealerGridFilters(true);
            });
        });
    });
    document.addEventListener('click', function() {
        table.querySelectorAll('.dealer-operator-dropdown').forEach(function(d) { d.hidden = true; });
        table.querySelectorAll('.dealer-operator-btn').forEach(function(b) { b.setAttribute('aria-expanded', 'false'); });
    });

    var clearBtn = document.getElementById('dealerClearFilters');
    if (clearBtn) {
        clearBtn.addEventListener('click', function() {
            table.querySelectorAll('.dealer-grid-filter').forEach(function(inp) { inp.value = ''; });
            table.querySelectorAll('.dealer-operator-btn').forEach(function(btn) {
                btn.setAttribute('data-op', '=');
                btn.textContent = '=';
                btn.setAttribute('aria-expanded', 'false');
            });
            table.querySelectorAll('.dealer-operator-dropdown').forEach(function(d) { d.hidden = true; });
            applyDealerGridFilters(true);
            table.querySelectorAll('thead th[data-col]').forEach(function(h) {
                h.classList.remove('inquiries-sort-asc', 'inquiries-sort-desc');
            });
            state.col = null;
            state.dir = 1;
        });
    }

    // ——— Sort ———
    function getVal(row, col) {
        var cell = row.querySelector('td[data-col="' + col + '"]');
        return (cell && cell.textContent) ? cell.textContent.trim().toLowerCase() : '';
    }
    function applySort() {
        var col = state.col;
        if (!col) return;
        table.querySelectorAll('thead th[data-col]').forEach(function(h) {
            h.classList.remove('inquiries-sort-asc', 'inquiries-sort-desc');
            if (h.getAttribute('data-col') === col) {
                h.classList.add(state.dir === 1 ? 'inquiries-sort-asc' : 'inquiries-sort-desc');
            }
        });
        var tbody = table.querySelector('tbody');
        var emptyRow = tbody.querySelector('tr:not(.dealer-row)');
        var rows = [].slice.call(tbody.querySelectorAll('tr.dealer-row'));
        rows.sort(function(a, b) {
            var va = getVal(a, col);
            var vb = getVal(b, col);
            var cmp = va.localeCompare(vb, undefined, { numeric: true });
            return state.dir * cmp;
        });
        rows.forEach(function(r) { tbody.appendChild(r); });
        if (emptyRow) tbody.appendChild(emptyRow);
        initDealerPagination();
        if (window.dealersGoToPage) {
            window.dealersGoToPage(window.dealersPaginationState.currentPage || 1);
        }
    }
    table.querySelectorAll('thead th[data-col]').forEach(function(th) {
        th.style.cursor = 'pointer';
        th.addEventListener('click', function(e) {
            if (e.target.closest('input, button, select, .inquiries-filter-wrap, .dealer-header-filter-row, .dealer-operator-btn, .dealer-operator-dropdown')) return;
            var col = th.getAttribute('data-col');
            if (!col) return;
            state.dir = (state.col === col) ? -state.dir : 1;
            state.col = col;
            applySort();
        });
    });
    applySort();
    table.querySelectorAll('tbody tr.dealer-row').forEach(function(row) {
        row.dataset.filterMatch = '1';
    });
    initDealerPagination();
    applyDealerGridFilters(false);
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var openBtn = document.getElementById('openAddUserModal');
    var closeBtn = document.getElementById('closeAddUserModal');
    var modal = document.getElementById('addUserModal');
    if (!modal || !openBtn || !closeBtn) return;
    openBtn.addEventListener('click', function() { modal.style.display = 'flex'; });
    closeBtn.addEventListener('click', function() { modal.style.display = 'none'; });
    modal.addEventListener('click', function(e) {
        if (e.target === modal) modal.style.display = 'none';
    });
});
</script>
@endpush
@endsection
