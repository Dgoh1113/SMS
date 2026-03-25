@extends('layouts.app')
@section('title', 'My Inquiries – SQL LMS Dealer Console')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/dealer-inquiries.css') }}?v=20260325-21">
@endpush

@section('content')
@php
    $productNames = [
        1 => 'SQL Account', 2 => 'SQL Payroll', 3 => 'SQL Production', 4 => 'Mobile Sales',
        5 => 'SQL Ecommerce', 6 => 'SQL EBI Wellness POS', 7 => 'SQL X Suduai', 8 => 'SQL X-Store',
        9 => 'SQL Vision', 10 => 'SQL HRMS', 11 => 'Others',
    ];
    $statusFilterOptions = ['Followup', 'Demo', 'Confirmed', 'Completed', 'Rewarded', 'Failed'];
@endphp
<div class="dashboard-content inquiries-page-wrap">
    <section class="inquiries-mgmt-panel dealer-inquiries-panel">
        <div class="inquiries-panel-header">
            <div class="inquiries-panel-title-wrap">
                <i class="bi bi-folder2-open inquiries-panel-icon"></i>
                <h2 class="inquiries-panel-title">My Inquiries</h2>
            </div>
            <div class="inquiries-panel-actions">
                <button type="button" class="inquiries-btn inquiries-btn-secondary inquiries-sync-btn" data-sync-url="{{ route('dealer.inquiries.sync') }}">
                    <i class="bi bi-arrow-repeat inquiries-sync-icon"></i>
                    <span class="inquiries-sync-label">Sync</span>
                </button>
                <div class="inquiries-columns-dropdown">
                    <button type="button" class="inquiries-btn inquiries-btn-secondary" id="dealerInquiryColumnsBtn" aria-haspopup="true" aria-expanded="false">Columns</button>
                    <div class="inquiries-columns-menu" id="dealerInquiryColumnsMenu" hidden>
                        <div class="inquiries-columns-menu-title">Show columns</div>
                        <label class="inquiries-columns-check"><input type="checkbox" data-col="inquiryid"> INQUIRY ID</label>
                        <label class="inquiries-columns-check"><input type="checkbox" data-col="date"> INQUIRY DATE</label>
                        <label class="inquiries-columns-check"><input type="checkbox" data-col="customer"> CUSTOMER NAME</label>
                        <label class="inquiries-columns-check"><input type="checkbox" data-col="email"> EMAIL</label>
                        <label class="inquiries-columns-check"><input type="checkbox" data-col="postcode"> POSTCODE</label>
                        <label class="inquiries-columns-check"><input type="checkbox" data-col="city"> CITY</label>
                        <label class="inquiries-columns-check"><input type="checkbox" data-col="address"> ADDRESS</label>
                        <label class="inquiries-columns-check"><input type="checkbox" data-col="contactno"> CONTACT NO</label>
                        <label class="inquiries-columns-check"><input type="checkbox" data-col="businessnature"> BUSINESS NATURE</label>
                        <label class="inquiries-columns-check"><input type="checkbox" data-col="users"> USERS</label>
                        <label class="inquiries-columns-check"><input type="checkbox" data-col="existingsw"> EXISTING SW</label>
                        <label class="inquiries-columns-check"><input type="checkbox" data-col="demomode"> DEMO MODE</label>
                        <label class="inquiries-columns-check"><input type="checkbox" data-col="products"> PRODUCTS</label>
                        <label class="inquiries-columns-check"><input type="checkbox" data-col="assigndate"> ASSIGN DATE</label>
                        <label class="inquiries-columns-check"><input type="checkbox" data-col="completiondate"> COMPLETION DATE</label>
                        <label class="inquiries-columns-check"><input type="checkbox" data-col="payoutsdate"> PAYOUTS DATE</label>
                        <label class="inquiries-columns-check"><input type="checkbox" data-col="message"> MESSAGE</label>
                        <label class="inquiries-columns-check"><input type="checkbox" data-col="referralcode"> REFERRAL CODE</label>
                        <label class="inquiries-columns-check"><input type="checkbox" data-col="attachment"> ATTACHMENT</label>
                        <label class="inquiries-columns-check"><input type="checkbox" data-col="assignby"> ASSIGN BY</label>
                        <label class="inquiries-columns-check"><input type="checkbox" data-col="status"> STATUS</label>
                        <div class="inquiries-columns-actions">
                            <button type="button" class="inquiries-columns-action-btn" id="dealerInquiryColumnsAll">All</button>
                            <button type="button" class="inquiries-columns-action-btn" id="dealerInquiryColumnsNone">None</button>
                        </div>
                        <button type="button" class="inquiries-columns-reset" id="dealerInquiryColumnsReset">Reset to default</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="inquiries-table-wrap">
            <div class="inquiries-table-scroll">
            <table class="inquiries-table" id="dealerInquiriesTable">
                <thead>
                    <tr class="inquiries-header-row">
                        <th data-col="inquiryid" class="inquiries-header-cell"><span class="inquiries-header-label">INQUIRY ID</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter" data-col="inquiryid"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="date" class="inquiries-header-cell"><span class="inquiries-header-label">INQUIRY DATE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter" data-col="date"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="customer" class="inquiries-header-cell"><span class="inquiries-header-label">CUSTOMER NAME</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter" data-col="customer"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="email" class="inquiries-header-cell"><span class="inquiries-header-label">EMAIL</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter" data-col="email"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="postcode" class="inquiries-header-cell"><span class="inquiries-header-label">POSTCODE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter" data-col="postcode"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="city" class="inquiries-header-cell"><span class="inquiries-header-label">CITY</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter" data-col="city"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="address" class="inquiries-header-cell"><span class="inquiries-header-label">ADDRESS</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter" data-col="address"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="contactno" class="inquiries-header-cell"><span class="inquiries-header-label">CONTACT NO</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter" data-col="contactno"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="businessnature" class="inquiries-header-cell"><span class="inquiries-header-label">BUSINESS NATURE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter" data-col="businessnature"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="users" class="inquiries-header-cell"><span class="inquiries-header-label">USERS</span><span class="inquiries-filter-wrap dealer-operator-search-wrap"><span class="dealer-operator-search-box"><button type="button" class="dealer-operator-btn" data-col="users" data-op="=" aria-haspopup="true" aria-expanded="false" title="Filter operator">=</button><div class="dealer-operator-dropdown" hidden><button type="button" data-op="=">= Equals</button><button type="button" data-op="!=">!= Does not equal</button><button type="button" data-op="<">&lt; Less than</button><button type="button" data-op="<=">&lt;= Less than or equal to</button><button type="button" data-op=">">&gt; Greater than</button><button type="button" data-op=">=">&gt;= Greater than or equal to</button></div><input type="text" class="inquiries-grid-filter" data-col="users" placeholder="0"></span></span></th>
                        <th data-col="existingsw" class="inquiries-header-cell"><span class="inquiries-header-label">EXISTING SW</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter" data-col="existingsw"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="demomode" class="inquiries-header-cell"><span class="inquiries-header-label">DEMO MODE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter" data-col="demomode"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="products" class="inquiries-header-cell"><span class="inquiries-header-label">PRODUCTS</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter" data-col="products"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="assigndate" class="inquiries-header-cell"><span class="inquiries-header-label">ASSIGN DATE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter" data-col="assigndate"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="completiondate" class="inquiries-header-cell"><span class="inquiries-header-label">COMPLETION DATE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter" data-col="completiondate"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="payoutsdate" class="inquiries-header-cell"><span class="inquiries-header-label">PAYOUTS DATE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter" data-col="payoutsdate"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="message" class="inquiries-header-cell"><span class="inquiries-header-label">MESSAGE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter" data-col="message"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="referralcode" class="inquiries-header-cell"><span class="inquiries-header-label">REFERRAL CODE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter" data-col="referralcode"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="attachment" class="inquiries-header-cell"><span class="inquiries-header-label">ATTACHMENT</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter" data-col="attachment"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="assignby" class="inquiries-header-cell"><span class="inquiries-header-label">ASSIGN BY</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter" data-col="assignby"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="status" class="inquiries-header-cell"><span class="inquiries-header-label">STATUS</span><span class="inquiries-filter-wrap"><select class="inquiries-grid-filter inquiries-grid-filter-select" data-col="status"><option value="">All</option>@foreach($statusFilterOptions as $statusOption)<option value="{{ $statusOption }}">{{ $statusOption }}</option>@endforeach</select></span></th>
                        <th class="inquiries-col-action inquiries-header-cell"><span class="inquiries-header-label">ACTION</span><button type="button" class="inquiries-filter-clear" id="dealerInquiryClearFilters">Clear filters</button></th>
                    </tr>
                </thead>
                <tbody>
                    @include('dealer.partials.inquiries_rows', ['leads' => $leads, 'productNames' => $productNames])
                </tbody>
            </table>
            </div>
            @php
                $dealerTotal = isset($leads) ? count($leads) : 0;
                $dealerPerPage = 10;
                $dealerLastPage = $dealerTotal > 0 ? (int) ceil($dealerTotal / $dealerPerPage) : 1;
                $dealerTo = $dealerTotal === 0 ? 0 : min($dealerPerPage, $dealerTotal);
            @endphp
            <div class="inquiries-assigned-pagination" id="dealerInquiriesPagination"
                 data-total="{{ $dealerTotal }}"
                 data-per-page="{{ $dealerPerPage }}"
                 data-current-page="1"
                 data-last-page="{{ $dealerLastPage }}">
                <span class="inquiries-assigned-pagination-info">
                    Showing {{ $dealerTotal === 0 ? 0 : 1 }} to {{ $dealerTo }} of {{ $dealerTotal }} entries (Page 1)
                </span>
                <div class="inquiries-assigned-pagination-nav">
                    <button type="button" class="inquiries-btn inquiries-btn-secondary inquiries-pagination-btn" data-page="first">First</button>
                    <button type="button" class="inquiries-btn inquiries-btn-secondary inquiries-pagination-btn" data-page="prev">Previous</button>
                    <span class="inquiries-assigned-page-numbers" id="dealerInquiriesPageNumbers"></span>
                    <button type="button" class="inquiries-btn inquiries-btn-secondary inquiries-pagination-btn" data-page="next">Next</button>
                    <button type="button" class="inquiries-btn inquiries-btn-secondary inquiries-pagination-btn" data-page="last">Last</button>
                </div>
            </div>
        </div>
    </section>
</div>

{{-- View Failed Message Modal --}}
<div class="inquiry-modal-overlay" id="inquiryViewModal" aria-hidden="true">
    <div class="inquiry-modal inquiry-modal--view" role="dialog" aria-labelledby="inquiryViewModalTitle">
        <div class="inquiry-modal-header">
            <h2 id="inquiryViewModalTitle" class="inquiry-modal-title">Failed Inquiry</h2>
            <p class="inquiry-modal-subtitle" id="inquiryViewModalSubtitle">Inquiry ID: #SQL-0 • —</p>
            <button type="button" class="inquiry-modal-close" id="inquiryViewModalClose" aria-label="Close"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="inquiry-modal-body">
            <div class="inquiry-view-message">
                <span class="inquiry-field-label">MESSAGE</span>
                <div class="inquiry-view-message-content" id="inquiryViewMessageContent">—</div>
            </div>
        </div>
        <div class="inquiry-modal-footer">
            <button type="button" class="inquiry-btn inquiry-btn-cancel" id="inquiryViewModalCloseBtn">Close</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var table = document.getElementById('dealerInquiriesTable');
    if (!table) return;

    function normalizeDealerTableStructure() {
        table.querySelectorAll('th[data-col="source"], td[data-col="source"]').forEach(function(el) {
            el.remove();
        });
    }

    normalizeDealerTableStructure();

    (function initMessageModal() {
        if (document.getElementById('inquiryMessageModal')) return;
        var modal = document.createElement('div');
        modal.id = 'inquiryMessageModal';
        modal.className = 'inquiries-msg-modal';
        modal.hidden = true;
        modal.innerHTML = ''
            + '<div class="inquiries-msg-modal-backdrop" data-close="1"></div>'
            + '<div class="inquiries-msg-modal-window" role="dialog" aria-modal="true" aria-labelledby="inquiriesMsgModalTitle">'
            + '  <div class="inquiries-msg-modal-header">'
            + '    <div class="inquiries-msg-modal-title" id="inquiriesMsgModalTitle">Message</div>'
            + '    <button type="button" class="inquiries-msg-modal-close" aria-label="Close" data-close="1">&times;</button>'
            + '  </div>'
            + '  <div class="inquiries-msg-modal-body"><pre class="inquiries-msg-modal-text" id="inquiriesMsgModalText"></pre></div>'
            + '</div>';
        document.body.appendChild(modal);

        function close() { modal.hidden = true; }
        function open(text) {
            var el = document.getElementById('inquiriesMsgModalText');
            if (el) el.textContent = text || '';
            modal.hidden = false;
        }

        document.addEventListener('click', function(e) {
            var cell = e.target && e.target.closest ? e.target.closest('.inquiries-msg-clickable[data-full-message]') : null;
            if (cell && table.contains(cell)) {
                open(cell.getAttribute('data-full-message') || '');
                return;
            }
            if (e.target && e.target.getAttribute && e.target.getAttribute('data-close') === '1') {
                close();
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !modal.hidden) close();
        });
    })();

    // Columns dropdown (admin-like)
    var colsBtn = document.getElementById('dealerInquiryColumnsBtn');
    var colsMenu = document.getElementById('dealerInquiryColumnsMenu');
    var colsAll = document.getElementById('dealerInquiryColumnsAll');
    var colsNone = document.getElementById('dealerInquiryColumnsNone');
    var colsReset = document.getElementById('dealerInquiryColumnsReset');
    var storageKey = 'dealer_inquiries_visible_cols_v10';
    var legacyStorageKey = 'dealer_inquiries_visible_cols_v9';
    var legacyMobileStorageKey = 'dealer_inquiries_visible_cols_mobile_v1';
    var olderLegacyStorageKey = 'dealer_inquiries_visible_cols_v8';
    var oldestLegacyStorageKey = 'dealer_inquiries_visible_cols_v7';
    var oldestLegacyStorageKeyV6 = 'dealer_inquiries_visible_cols_v6';
    var oldestLegacyStorageKeyV5 = 'dealer_inquiries_visible_cols_v5';
    // Dealer defaults should stay the same on desktop and mobile.
    var legacyDefaultCols = ['inquiryid','date','customer','email','postcode','city','products','assignby','status'];
    var olderLegacyDefaultCols = ['inquiryid','date','customer','postcode','city','businessnature','products','assignby','status'];
    var previousDefaultCols = ['inquiryid','date','customer','email','postcode','city','products','status'];
    var compactMobileLegacyCols = ['inquiryid','date','customer'];
    var defaultCols = ['inquiryid','date','customer','email','postcode','city','products','assigndate','status'];
    var allCols = ['inquiryid','date','customer','email','postcode','city','address','contactno','businessnature','users','existingsw','demomode','products','assigndate','completiondate','payoutsdate','message','referralcode','attachment','assignby','status'];

    function getDefaultColsForViewport() {
        return defaultCols.slice();
    }

    var statusCheckbox = colsMenu ? colsMenu.querySelector('input[type="checkbox"][data-col="status"]') : null;
    if (statusCheckbox) {
        // Status is enabled by default, but dealer can toggle it.
        statusCheckbox.checked = true;
        statusCheckbox.disabled = false;
    }

    function setMenuOpen(open) {
        if (!colsMenu || !colsBtn) return;
        colsMenu.hidden = !open;
        colsBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
    }

    function setColVisible(col, visible) {
        table.querySelectorAll('th[data-col="' + col + '"], td[data-col="' + col + '"]').forEach(function(el) {
            el.style.display = visible ? '' : 'none';
        });
    }

    function getSelectedColsFromMenu() {
        if (!colsMenu) return getDefaultColsForViewport();
        var cols = [];
        colsMenu.querySelectorAll('input[type="checkbox"][data-col]').forEach(function(cb) {
            if (cb.checked) cols.push(cb.getAttribute('data-col'));
        });
        return cols;
    }

    function applyVisibleCols(cols) {
        normalizeDealerTableStructure();
        // Hide/show data columns, and hide ACTION too when "None" is selected.
        allCols.forEach(function(c) { setColVisible(c, cols.indexOf(c) !== -1); });
        var showAction = Array.isArray(cols) && cols.length > 0;
        table.querySelectorAll('th.inquiries-col-action, td.inquiries-col-action').forEach(function(el) {
            el.style.display = showAction ? '' : 'none';
        });
        clearDealerColumnWidths();
        updateScrollMode(cols);
        // sync checkboxes
        if (colsMenu) {
            colsMenu.querySelectorAll('input[type="checkbox"][data-col]').forEach(function(cb) {
                var c = cb.getAttribute('data-col');
                cb.checked = cols.indexOf(c) !== -1;
            });
        }
    }

    function updateScrollMode(visibleCols) {
        var scroller = table.closest('.inquiries-table-scroll');
        if (!scroller) return;
        var activeCols = Array.isArray(visibleCols) ? visibleCols : [];
        var hasExtras = activeCols.some(function(col) { return defaultCols.indexOf(col) === -1; });
        var hasProducts = activeCols.indexOf('products') !== -1;
        var enabled = activeCols.length > 0;

        if (window.innerWidth && window.innerWidth < 1200) {
            scroller.classList.remove('inquiries-table-scroll--no-x');
            table.classList.remove('inquiries-table--fit');
            return;
        }

        scroller.classList.toggle('inquiries-table-scroll--no-x', enabled && !hasExtras && !hasProducts);
        table.classList.toggle('inquiries-table--fit', enabled && !hasExtras && !hasProducts);
    }

    function saveCols(cols) {
        try { localStorage.setItem(storageKey, JSON.stringify(cols)); } catch (e) {}
    }

    function loadCols() {
        try {
            var raw = localStorage.getItem(storageKey);
            if (!raw) raw = localStorage.getItem(legacyStorageKey);
            if (!raw) raw = localStorage.getItem(legacyMobileStorageKey);
            if (!raw) raw = localStorage.getItem(olderLegacyStorageKey);
            if (!raw) raw = localStorage.getItem(oldestLegacyStorageKey);
            if (!raw) raw = localStorage.getItem(oldestLegacyStorageKeyV6);
            if (!raw) raw = localStorage.getItem(oldestLegacyStorageKeyV5);
            if (!raw) return null;
            var parsed = JSON.parse(raw);
            if (!Array.isArray(parsed)) return null;

            var isLegacyDefault =
                (parsed.length === legacyDefaultCols.length && parsed.every(function(col, index) {
                    return col === legacyDefaultCols[index];
                })) ||
                (parsed.length === olderLegacyDefaultCols.length && parsed.every(function(col, index) {
                    return col === olderLegacyDefaultCols[index];
                })) ||
                (parsed.length === previousDefaultCols.length && parsed.every(function(col, index) {
                    return col === previousDefaultCols[index];
                })) ||
                (parsed.length === compactMobileLegacyCols.length && parsed.every(function(col, index) {
                    return col === compactMobileLegacyCols[index];
                })) ||
                (parsed.length === defaultCols.length && parsed.every(function(col, index) {
                    return col === defaultCols[index];
                }));
            var migrated = isLegacyDefault ? getDefaultColsForViewport() : parsed.filter(function(col) {
                return allCols.indexOf(col) !== -1;
            });

            try {
                localStorage.setItem(storageKey, JSON.stringify(migrated));
                localStorage.removeItem(legacyStorageKey);
                localStorage.removeItem(legacyMobileStorageKey);
                localStorage.removeItem(olderLegacyStorageKey);
                localStorage.removeItem(oldestLegacyStorageKey);
                localStorage.removeItem(oldestLegacyStorageKeyV6);
                localStorage.removeItem(oldestLegacyStorageKeyV5);
            } catch (e) {}

            return migrated;
        } catch (e) {
            return null;
        }
    }

    function clearDealerColumnWidths() {
        allCols.forEach(function(col) {
            var nodes = table.querySelectorAll('th[data-col="' + col + '"], td[data-col="' + col + '"]');
            if (!nodes.length) return;
            nodes.forEach(function(node) {
                node.style.removeProperty('width');
                node.style.removeProperty('min-width');
                node.style.removeProperty('max-width');
            });
        });
    }

    window.dealerPaginationState = window.dealerPaginationState || { currentPage: 1, perPage: 10 };

    function normalizeDealerInquiryStatus(value) {
        var normalized = String(value || '').toLowerCase().replace(/\s+/g, ' ').trim();
        if (normalized === '' || normalized === 'all') return '';
        if (normalized === 'follow up' || normalized === 'followup') return 'followup';
        if (normalized === 'confirmed' || normalized === 'case confirmed') return 'confirmed';
        if (normalized === 'completed' || normalized === 'case completed') return 'completed';
        if (normalized === 'rewarded' || normalized === 'reward distributed' || normalized === 'paid') return 'rewarded';
        return normalized;
    }

    var DEALER_INQUIRY_NUMERIC_FILTER_COLS = ['users'];

    function parseDealerInquiryFilterNumber(value) {
        var num = parseFloat(String(value || '').replace(/[^0-9.\-]/g, ''));
        return isNaN(num) ? 0 : num;
    }

    function bindDealerInquiryOperatorMenus(tableEl) {
        if (!tableEl) return;
        tableEl.querySelectorAll('.dealer-operator-btn').forEach(function(btn) {
            if (btn.getAttribute('data-operator-bound') === '1') return;
            btn.setAttribute('data-operator-bound', '1');
            var dropdown = btn.parentElement.querySelector('.dealer-operator-dropdown');
            if (!dropdown) return;
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                var isOpen = !dropdown.hidden;
                tableEl.querySelectorAll('.dealer-operator-dropdown').forEach(function(d) { d.hidden = true; });
                tableEl.querySelectorAll('.dealer-operator-btn').forEach(function(b) { b.setAttribute('aria-expanded', 'false'); });
                dropdown.hidden = isOpen;
                btn.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
            });
            dropdown.addEventListener('click', function(e) { e.stopPropagation(); });
            dropdown.querySelectorAll('button[data-op]').forEach(function(option) {
                option.addEventListener('click', function(e) {
                    e.stopPropagation();
                    var op = option.getAttribute('data-op') || '=';
                    btn.setAttribute('data-op', op);
                    btn.textContent = op;
                    btn.setAttribute('aria-expanded', 'false');
                    dropdown.hidden = true;
                    applyDealerGridFilters();
                });
            });
        });
    }

    function resetDealerInquiryOperatorMenus(tableEl) {
        if (!tableEl) return;
        tableEl.querySelectorAll('.dealer-operator-btn').forEach(function(btn) {
            btn.setAttribute('data-op', '=');
            btn.textContent = '=';
            btn.setAttribute('aria-expanded', 'false');
        });
        tableEl.querySelectorAll('.dealer-operator-dropdown').forEach(function(dropdown) {
            dropdown.hidden = true;
        });
    }

    function applyDealerGridFilters() {
        var filters = {};
        table.querySelectorAll('.inquiries-grid-filter').forEach(function(inp) {
            var col = inp.getAttribute('data-col');
            var val = (inp.value || '').trim();
            if (!col) return;
            if (val === '') return;
            if (col === 'status') {
                val = normalizeDealerInquiryStatus(val);
                if (!val) return;
                filters[col] = { numeric: false, val: val };
                return;
            }
            if (DEALER_INQUIRY_NUMERIC_FILTER_COLS.indexOf(col) !== -1) {
                var opBtn = table.querySelector('.dealer-operator-btn[data-col="' + col + '"]');
                filters[col] = {
                    numeric: true,
                    op: opBtn ? (opBtn.getAttribute('data-op') || '=') : '=',
                    val: val
                };
                return;
            }
            filters[col] = { numeric: false, val: val.toLowerCase() };
        });

        table.querySelectorAll('tbody .inquiry-row').forEach(function(row) {
            var colMatch = true;
            for (var col in filters) {
                var cell = row.querySelector('td[data-col="' + col + '"]');
                var cellText = (cell && cell.textContent) ? cell.textContent.trim() : '';
                var filter = filters[col];
                if (filter.numeric) {
                    var cellNum = parseDealerInquiryFilterNumber(cellText);
                    var filterNum = parseDealerInquiryFilterNumber(filter.val);
                    if (filter.op === '=' && cellNum !== filterNum) { colMatch = false; break; }
                    if (filter.op === '!=' && cellNum === filterNum) { colMatch = false; break; }
                    if (filter.op === '>' && cellNum <= filterNum) { colMatch = false; break; }
                    if (filter.op === '>=' && cellNum < filterNum) { colMatch = false; break; }
                    if (filter.op === '<' && cellNum >= filterNum) { colMatch = false; break; }
                    if (filter.op === '<=' && cellNum > filterNum) { colMatch = false; break; }
                    continue;
                }
                if (col === 'status') {
                    var normalizedStatusFilter = normalizeDealerInquiryStatus(filter.val);
                    if (!normalizedStatusFilter) {
                        continue;
                    }
                    if (normalizeDealerInquiryStatus(cellText.toLowerCase()) !== normalizedStatusFilter) {
                        colMatch = false;
                        break;
                    }
                    continue;
                }
                if (cellText.toLowerCase().indexOf(filter.val) === -1) { colMatch = false; break; }
            }
            row.dataset.filterMatch = colMatch ? '1' : '0';
        });

        window.dealerPaginationState.currentPage = 1;
        if (typeof window.dealerApplyPagination === 'function') {
            window.dealerApplyPagination();
        }
    }

    table.querySelectorAll('.inquiries-grid-filter').forEach(function(inp) {
        inp.addEventListener('input', applyDealerGridFilters);
        inp.addEventListener('keyup', applyDealerGridFilters);
        inp.addEventListener('change', applyDealerGridFilters);
    });
    bindDealerInquiryOperatorMenus(table);
    document.addEventListener('click', function() {
        table.querySelectorAll('.dealer-operator-dropdown').forEach(function(dropdown) { dropdown.hidden = true; });
        table.querySelectorAll('.dealer-operator-btn').forEach(function(btn) { btn.setAttribute('aria-expanded', 'false'); });
    });

    var clearBtn = document.getElementById('dealerInquiryClearFilters');
    if (clearBtn) {
        clearBtn.addEventListener('click', function() {
            table.querySelectorAll('.inquiries-grid-filter').forEach(function(inp) { inp.value = ''; });
            resetDealerInquiryOperatorMenus(table);
            applyDealerGridFilters();
        });
    }

    // init columns
    var initialCols = loadCols() || getDefaultColsForViewport();
    applyVisibleCols(initialCols);

    if (colsBtn && colsMenu) {
        colsBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            setMenuOpen(colsMenu.hidden);
        });
        document.addEventListener('click', function() { setMenuOpen(false); });
        colsMenu.addEventListener('click', function(e) { e.stopPropagation(); });

        colsMenu.querySelectorAll('input[type="checkbox"][data-col]').forEach(function(cb) {
            cb.addEventListener('change', function() {
                var col = cb.getAttribute('data-col');
                var cols = getSelectedColsFromMenu();
                applyVisibleCols(cols);
                saveCols(cols);
            });
        });
    }
    if (colsAll) {
        colsAll.addEventListener('click', function() {
            applyVisibleCols(allCols.slice());
            saveCols(allCols.slice());
        });
    }
    if (colsNone) {
        colsNone.addEventListener('click', function() {
            applyVisibleCols([]);
            saveCols([]);
        });
    }
    if (colsReset) {
        colsReset.addEventListener('click', function() {
            var resetCols = getDefaultColsForViewport();
            applyVisibleCols(resetCols);
            saveCols(resetCols);
        });
    }

    applyDealerGridFilters();

    function initDealerPagination() {
        var pagination = document.getElementById('dealerInquiriesPagination');
        if (!pagination) return;

        var infoEl = pagination.querySelector('.inquiries-assigned-pagination-info');
        var pageNumbersEl = document.getElementById('dealerInquiriesPageNumbers');
        var controls = pagination.querySelectorAll('.inquiries-pagination-btn');
        var perPage = parseInt(pagination.getAttribute('data-per-page') || '10', 10);
        var scrollWrap = table ? table.closest('.inquiries-table-scroll') : null;
        window.dealerPaginationState.perPage = perPage;

        function getAllRows() {
            return Array.prototype.slice.call(table.querySelectorAll('tbody .inquiry-row'));
        }

        function getMatchingRows() {
            return getAllRows().filter(function(row) {
                if (!row.dataset.filterMatch) row.dataset.filterMatch = '1';
                return row.dataset.filterMatch !== '0';
            });
        }

        function clearPlaceholderRows() {
            Array.prototype.slice.call(table.querySelectorAll('tbody tr.inquiries-placeholder-row')).forEach(function(row) {
                row.remove();
            });
        }

        function ensureFixedHeight(visibleDataCount) {
            var tbody = table.querySelector('tbody');
            if (!tbody) return;
            clearPlaceholderRows();
            if (scrollWrap) {
                scrollWrap.classList.toggle('inquiries-table-scroll-empty', visibleDataCount === 0);
                scrollWrap.classList.remove('inquiries-table-scroll-short');
            }

            if (window.innerWidth && window.innerWidth <= 768) {
                if (scrollWrap) {
                    scrollWrap.classList.toggle('inquiries-table-scroll-short', visibleDataCount > 0 && visibleDataCount < perPage);
                }
                return;
            }

            if (visibleDataCount > 0 && visibleDataCount < perPage) {
                var sampleRow = tbody.querySelector('tr.inquiry-row');
                var rowHeight = sampleRow ? Math.ceil(sampleRow.getBoundingClientRect().height) - 1 : 55;
                if (!rowHeight || rowHeight < 40) rowHeight = 55;

                var headerCount = table.querySelectorAll('thead tr:first-child th').length || 1;
                for (var i = visibleDataCount; i < perPage; i++) {
                    var row = document.createElement('tr');
                    row.className = 'inquiries-placeholder-row';
                    row.setAttribute('aria-hidden', 'true');

                    var cell = document.createElement('td');
                    cell.className = 'inquiries-placeholder-cell';
                    cell.colSpan = headerCount;
                    cell.style.height = rowHeight + 'px';

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
                btn.addEventListener('click', function() {
                    var page = parseInt(this.getAttribute('data-page') || '1', 10);
                    window.dealerGoToPage(page);
                });
                pageNumbersEl.appendChild(btn);
            }
        }

        window.dealerApplyPagination = function() {
            var rows = getAllRows();
            var matchingRows = getMatchingRows();
            var total = matchingRows.length;
            var lastPage = total > 0 ? Math.ceil(total / perPage) : 1;
            var currentPage = parseInt(window.dealerPaginationState.currentPage || '1', 10);

            currentPage = Math.max(1, Math.min(currentPage, lastPage));
            window.dealerPaginationState.currentPage = currentPage;

            var start = (currentPage - 1) * perPage;
            var end = Math.min(start + perPage, total);
            var pageRows = matchingRows.slice(start, end);

            rows.forEach(function(row) {
                row.style.display = pageRows.indexOf(row) !== -1 ? '' : 'none';
            });
            ensureFixedHeight(pageRows.length);

            pagination.setAttribute('data-total', String(total));
            pagination.setAttribute('data-last-page', String(lastPage));
            pagination.setAttribute('data-current-page', String(currentPage));

            if (infoEl) {
                var from = total === 0 ? 0 : start + 1;
                infoEl.textContent = 'Showing ' + from + ' to ' + end + ' of ' + total + ' entries (Page ' + currentPage + ')';
            }

            controls.forEach(function(btn) {
                var type = btn.getAttribute('data-page');
                if (type === 'first' || type === 'prev') {
                    btn.disabled = currentPage <= 1;
                } else if (type === 'next' || type === 'last') {
                    btn.disabled = currentPage >= lastPage;
                }
            });

            buildPageNumbers(currentPage, lastPage);
        };

        window.dealerGoToPage = function(page) {
            window.dealerPaginationState.currentPage = parseInt(page || '1', 10) || 1;
            window.dealerApplyPagination();
        };

        // Bind controls once (safe because init runs after Sync too)
        controls.forEach(function(btn) {
            btn.onclick = function() {
                var type = btn.getAttribute('data-page');
                var currentPage = window.dealerPaginationState.currentPage;
                if (type === 'first') window.dealerGoToPage(1);
                if (type === 'prev') window.dealerGoToPage(currentPage - 1);
                if (type === 'next') window.dealerGoToPage(currentPage + 1);
                if (type === 'last') window.dealerGoToPage(lastPage);
            };
        });

        window.dealerApplyPagination();
    }

    // Simple client-side pagination: 10 inquiries per page
    initDealerPagination();

    // Sync button (same behaviour pattern as admin inquiries sync)
    var DEALER_INQUIRIES_AUTO_SYNC_MS = 15 * 60 * 1000;

    function triggerDealerInquirySync(btn) {
        if (!btn || btn.classList.contains('is-syncing')) return;
        btn.classList.add('is-syncing');
        var icon = btn.querySelector('.inquiries-sync-icon');
        if (icon) {
            icon.classList.add('spinning');
        }

        var url = btn.getAttribute('data-sync-url') || window.location.href;
        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            cache: 'no-store'
        }).then(function(res) {
            return res.ok ? res.json() : Promise.reject();
        }).then(function(data) {
            var tbody = table.querySelector('tbody');
            if (tbody && data.rows !== undefined) {
                tbody.innerHTML = data.rows;
            }
            normalizeDealerTableStructure();
            // Re-apply filters and column visibility after replacing rows
            applyDealerGridFilters();
            var currentCols = loadCols() || getDefaultColsForViewport();
            applyVisibleCols(currentCols);
            initDealerPagination();
        }).catch(function() {
            // swallow errors for now; button state will reset below
        }).finally(function() {
            btn.classList.remove('is-syncing');
            if (icon) {
                icon.classList.remove('spinning');
            }
        });
    }

    document.querySelectorAll('.inquiries-sync-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            triggerDealerInquirySync(btn);
        });
    });

    window.setInterval(function() {
        triggerDealerInquirySync(document.querySelector('.inquiries-sync-btn'));
    }, DEALER_INQUIRIES_AUTO_SYNC_MS);
});
</script>
@endpush

{{-- Update Inquiry Status Modal --}}
<div class="inquiry-modal-overlay" id="inquiryUpdateModal" aria-hidden="true">
    <div class="inquiry-modal" role="dialog" aria-labelledby="inquiryModalTitle">
        <div class="inquiry-modal-header">
            <h2 id="inquiryModalTitle" class="inquiry-modal-title">Update Inquiry Status</h2>
            <p class="inquiry-modal-subtitle" id="inquiryModalSubtitle">Inquiry ID: #SQL-0 • —</p>
            <button type="button" class="inquiry-modal-close" id="inquiryModalClose" aria-label="Close"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="inquiry-modal-body">
            <div class="inquiry-progression">
                <span class="inquiry-progression-label">INQUIRY PROGRESSION</span>
                <div class="inquiry-progression-steps" id="inquiryProgressionSteps">
                    <div class="inquiry-step inquiry-step--red" data-step="PENDING" data-color="red"><span>PENDING</span></div>
                    <div class="inquiry-connector"></div>
                    <div class="inquiry-step inquiry-step--red" data-step="FOLLOW UP" data-color="red"><span>FOLLOW UP</span></div>
                    <div class="inquiry-connector"></div>
                    <div class="inquiry-step inquiry-step--yellow" data-step="DEMO" data-color="yellow"><span>DEMO</span></div>
                    <div class="inquiry-connector"></div>
                    <div class="inquiry-step inquiry-step--yellow" data-step="CONFIRMED" data-color="yellow"><span>CONFIRMED</span></div>
                    <div class="inquiry-connector"></div>
                    <div class="inquiry-step inquiry-step--green" data-step="COMPLETED" data-color="green"><span>COMPLETED</span></div>
                    <div class="inquiry-connector"></div>
                    <div class="inquiry-step inquiry-step--green" data-step="REWARDED" data-color="green"><span>REWARDED</span></div>
                </div>
            </div>
            <div class="inquiry-followup">
                <div class="inquiry-followup-header">
                    <i class="bi bi-calendar3"></i>
                    <span>Update Details</span>
                </div>
                <div class="inquiry-followup-fields">
                    <label class="inquiry-field">
                        <span class="inquiry-field-label" id="inquiryDateLabel">FOLLOW-UP DATE</span>
                        <div class="inquiry-field-input-wrap">
                            <i class="bi bi-calendar3"></i>
                            <input type="text" class="inquiry-field-input" id="inquiryFollowupDate" inputmode="numeric" placeholder="dd/mm/yyyy" autocomplete="off">
                        </div>
                    </label>
                    <label class="inquiry-field">
                        <span class="inquiry-field-label" id="inquiryTimeLabel">FOLLOW-UP TIME</span>
                        <div class="inquiry-field-input-wrap">
                            <i class="bi bi-clock"></i>
                            <input type="time" class="inquiry-field-input" id="inquiryFollowupTime">
                        </div>
                    </label>
                    
                    {{-- CHANGED FROM <label> to <div> to fix the "click anywhere ticks the first box" issue --}}
                    <div class="inquiry-field inquiry-field-products" id="inquiryProductsField" style="display:none;">
                        <span class="inquiry-field-label">PRODUCTS <span class="inquiry-field-required">*</span></span>
                        <div class="inquiry-products-checklist" id="inquiryProductsChecklist">
                            @foreach($productNames as $id => $name)
                                <label class="inquiry-product-check">
                                    <input type="checkbox" class="inquiry-product-checkbox" name="inquiry_products[]" value="{{ $id }}" data-name="{{ $name }}">
                                    <span>{{ $name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    
                    <label class="inquiry-field">
                        <span class="inquiry-field-label">ATTACHMENT (images)</span>
                        <div class="inquiry-field-input-wrap">
                            <i class="bi bi-image"></i>
                            <input type="file" class="inquiry-field-input inquiry-field-file" id="inquiryAttachment" accept="image/*" multiple>
                        </div>
                        <div class="inquiry-attachment-preview-wrap" id="inquiryAttachmentPreview" aria-live="polite"></div>
                    </label>
                    <label class="inquiry-field">
                        <span class="inquiry-field-label">REMARK</span>
                        <textarea class="inquiry-field-input inquiry-field-textarea" id="inquiryRemark" placeholder="" rows="6"></textarea>
                    </label>
                </div>
            </div>
            <div class="inquiry-activity">
                <div class="inquiry-activity-header">
                    <h3 class="inquiry-activity-title">Activity</h3>
                </div>
                <div class="inquiry-activity-timeline" id="inquiryActivityTimeline">
                    <div class="inquiry-activity-item">
                        <span class="inquiry-activity-bullet"></span>
                        <div class="inquiry-activity-content">
                            <strong>System</strong> created inquiry <span class="inquiry-activity-link" id="inquiryActivityLink">#SQL-0</span> <span class="inquiry-activity-time">—</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="inquiry-modal-footer">
            <button type="button" class="inquiry-btn inquiry-btn-cancel" id="inquiryModalCancel">Cancel</button>
            <button type="button" class="inquiry-btn inquiry-btn-calendar" id="inquiryModalAddCalendar" style="display:none;"><i class="bi bi-calendar-plus"></i> Add Calendar</button>
            <button type="button" class="inquiry-btn inquiry-btn-update" id="inquiryModalUpdate"><i class="bi bi-check-lg"></i> Update Status</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function() {
    var table = document.getElementById('dealerInquiriesTable');
    var modal = document.getElementById('inquiryUpdateModal');
    var subtitle = document.getElementById('inquiryModalSubtitle');
    var closeBtn = document.getElementById('inquiryModalClose');
    var cancelBtn = document.getElementById('inquiryModalCancel');
    var updateBtn = document.getElementById('inquiryModalUpdate');

    var activityLink = document.getElementById('inquiryActivityLink');
    var progressionSteps = document.getElementById('inquiryProgressionSteps');
    var activityTimeline = document.getElementById('inquiryActivityTimeline');

    var statusOrder = ['PENDING', 'FOLLOW UP', 'DEMO', 'CONFIRMED', 'COMPLETED', 'REWARDED'];
    var remarkPlaceholders = {
        'PENDING': 'Add specific notes about this inquiry...',
        'FOLLOW UP': 'Add specific notes about this follow-up...',
        'DEMO': 'Add specific notes about this demo...',
        'CONFIRMED': 'Add specific notes about this confirmation...',
        'COMPLETED': 'Add specific notes about this completion...',
        'REWARDED': 'Add specific notes about this reward...'
    };
    var dateTimeLabels = {
        'PENDING': { date: 'FOLLOW-UP DATE', time: 'FOLLOW-UP TIME' },
        'FOLLOW UP': { date: 'FOLLOW-UP DATE', time: 'FOLLOW-UP TIME' },
        'DEMO': { date: 'DEMO DATE', time: 'DEMO TIME' },
        'CONFIRMED': { date: 'CONFIRMED DATE', time: 'CONFIRMED TIME' },
        'COMPLETED': { date: 'COMPLETED DATE', time: 'COMPLETED TIME' },
        'REWARDED': { date: 'REWARD DATE', time: 'REWARD TIME' }
    };
    var statusMap = {
        'PENDING': 'PENDING', 'FOLLOW UP': 'FOLLOW UP', 'FOLLOWUP': 'FOLLOW UP',
        'DEMO': 'DEMO', 'CONFIRMED': 'CONFIRMED', 'CASE CONFIRMED': 'CONFIRMED',
        'COMPLETED': 'COMPLETED', 'CASE COMPLETED': 'COMPLETED',
        'REWARD': 'REWARDED', 'REWARDED': 'REWARDED', 'REWARD DISTRIBUTED': 'REWARDED'
    };

    var currentLeadId = '';
    var currentCustomer = '';
    var currentStatusIdx = 0;
    var selectedStatusIdx = 0;
    var viewMode = false;
    var cachedActivities = [];
    var userPickedStep = false;
    var latestMinDate = '';
    var latestMinTime = '';
    var currentReferralCode = '';

    function hasReferralCode(value) {
        var normalized = String(value || '').trim();
        return normalized !== '' && normalized !== '-' && normalized !== '—';
    }

    function formatStatusLabel(status) {
        switch (status) {
            case 'FOLLOW UP': return 'Follow Up';
            case 'PENDING': return 'Pending';
            case 'DEMO': return 'Demo';
            case 'CONFIRMED': return 'Confirmed';
            case 'COMPLETED': return 'Completed';
            case 'REWARDED': return 'Rewarded';
            default: return String(status || '').trim();
        }
    }

    function canSelectFutureStatus(fromStatus, toStatus) {
        if (!fromStatus || !toStatus) return false;
        var fromIdx = statusOrder.indexOf(fromStatus);
        var toIdx = statusOrder.indexOf(toStatus);
        if (fromIdx < 0 || toIdx <= fromIdx) return false;

        switch (fromStatus) {
            case 'PENDING':
                return toStatus === 'FOLLOW UP';
            case 'FOLLOW UP':
                return toStatus === 'DEMO' || toStatus === 'CONFIRMED' || toStatus === 'COMPLETED';
            case 'DEMO':
                return toStatus === 'CONFIRMED' || toStatus === 'COMPLETED';
            case 'CONFIRMED':
                return toStatus === 'COMPLETED';
            case 'COMPLETED':
                return toStatus === 'REWARDED' && hasReferralCode(currentReferralCode);
            default:
                return false;
        }
    }

    function getDefaultSelectedStatusIdx(idx) {
        var currentStatus = statusOrder[idx] || 'PENDING';
        for (var i = idx + 1; i < statusOrder.length; i++) {
            if (canSelectFutureStatus(currentStatus, statusOrder[i])) {
                return i;
            }
        }
        return idx;
    }

    function getBlockedStatusMessage(fromStatus, toStatus) {
        if (fromStatus === 'PENDING' && toStatus !== 'FOLLOW UP') {
            return 'You cant change status from Pending To ' + formatStatusLabel(toStatus) + ', Please Follow Up First';
        }
        if (toStatus === 'REWARDED' && !hasReferralCode(currentReferralCode)) {
            return 'You cant change status to Rewarded, Referral Code is required first';
        }
        if (toStatus === 'REWARDED') {
            return 'You cant change status to Rewarded, Please Complete First';
        }
        return 'You cant change status from ' + formatStatusLabel(fromStatus) + ' To ' + formatStatusLabel(toStatus);
    }

    function refreshProgressionState() {
        if (!progressionSteps) return;
        var currentStatus = statusOrder[currentStatusIdx] || 'PENDING';
        progressionSteps.querySelectorAll('.inquiry-step').forEach(function(step, i) {
            step.classList.remove('inquiry-step--done', 'inquiry-step--active', 'inquiry-step--selected', 'inquiry-step--clickable', 'inquiry-step--no-click', 'inquiry-step--viewable');
            var label = getStepDisplayLabel(i);
            var displayText = label || step.dataset.step;
            var isDone = i <= currentStatusIdx;
            if (isDone) {
                step.classList.add('inquiry-step--done', 'inquiry-step--viewable');
                if (i === selectedStatusIdx && viewMode) step.classList.add('inquiry-step--selected');
                step.innerHTML = '<i class="bi bi-check"></i><span>' + displayText + '</span>';
                return;
            }

            var canClick = canSelectFutureStatus(currentStatus, statusOrder[i]);
            step.innerHTML = '<span>' + displayText + '</span>';
            if (i === selectedStatusIdx && canClick) {
                step.classList.add('inquiry-step--active', 'inquiry-step--selected');
            } else if (canClick) {
                step.classList.add('inquiry-step--clickable');
            } else {
                step.classList.add('inquiry-step--no-click');
            }
        });
    }

    function setDateTimeMinConstraints() {
        var dateEl = document.getElementById('inquiryFollowupDate');
        var timeEl = document.getElementById('inquiryFollowupTime');
        if (!dateEl || !timeEl) return;

        // Only enforce min constraints while EDITING (not viewing).
        if (viewMode) {
            dateEl.min = '';
            timeEl.min = '';
            return;
        }

        if (!latestMinDate) {
            dateEl.min = '';
            timeEl.min = '';
            return;
        }

        dateEl.min = latestMinDate;

        // Time constraint only matters when the selected date equals the min date.
        var curDate = getFollowupDateIso();
        var rawDate = (dateEl.value || '').trim();
        if (rawDate !== '' && curDate === '') {
            dateEl.setCustomValidity('Use dd/mm/yyyy');
            timeEl.min = '';
            return;
        }
        if (curDate !== '' && latestMinDate && curDate < latestMinDate) {
            dateEl.setCustomValidity('Date must be on or after ' + formatIsoDateForDisplay(latestMinDate));
        } else {
            dateEl.setCustomValidity('');
        }
        var todayIso = getTodayIsoDate();
        if (curDate !== '' && curDate > todayIso) {
            dateEl.setCustomValidity('Date cannot be in the future.');
        }
        if (curDate === latestMinDate && latestMinTime) {
            timeEl.min = latestMinTime;
        } else {
            timeEl.min = '';
        }
        if (curDate === todayIso) {
            timeEl.max = getCurrentLocalTime();
        } else {
            timeEl.max = '';
        }
        var currentTime = (timeEl.value || '').trim();
        if (curDate === todayIso && currentTime !== '' && currentTime > getCurrentLocalTime()) {
            timeEl.setCustomValidity('Time cannot be in the future.');
        } else {
            timeEl.setCustomValidity('');
        }
    }

    function hasActivityForStatus(statusOrderName) {
        return !!findActivityForStatus(statusOrderName);
    }

    function setProgression(currentStatus) {
        var key = (currentStatus || '').toString().toUpperCase().trim();
        // Normalize common DB/API variants (e.g. "FollowUp" -> "FOLLOWUP")
        key = key.replace(/\s+/g, ' ');
        var normalized = statusMap[key] || 'PENDING';
        var idx = statusOrder.indexOf(normalized);
        if (idx < 0) idx = 0;
        currentStatusIdx = idx;
        // In view mode, keep selection on the latest saved status.
        // In edit mode, select the first allowed next status.
        selectedStatusIdx = viewMode ? idx : getDefaultSelectedStatusIdx(idx);
        refreshProgressionState();
        var remarkEl = document.getElementById('inquiryRemark');
        if (remarkEl) remarkEl.placeholder = remarkPlaceholders[statusOrder[selectedStatusIdx]] || remarkPlaceholders['PENDING'];
        setDateTimeLabels(statusOrder[selectedStatusIdx]);
        toggleAddCalendarButton();
        toggleProductChecklist();
        toggleUpdateButton();
    }

    function setRemarkPlaceholder(status) {
        var remarkEl = document.getElementById('inquiryRemark');
        if (remarkEl) remarkEl.placeholder = remarkPlaceholders[status] || remarkPlaceholders['PENDING'];
    }

    function setDateTimeLabels(status) {
        var labels = dateTimeLabels[status] || dateTimeLabels['FOLLOW UP'];
        var dateLabel = document.getElementById('inquiryDateLabel');
        var timeLabel = document.getElementById('inquiryTimeLabel');
        if (dateLabel) dateLabel.textContent = labels.date;
        if (timeLabel) timeLabel.textContent = labels.time;
    }

    function toggleAddCalendarButton() {
        var btn = document.getElementById('inquiryModalAddCalendar');
        if (!btn) return;
        var isDemo = statusOrder[selectedStatusIdx] === 'DEMO';
        btn.style.display = isDemo ? '' : 'none';
        if (!isDemo) return;

        var dateEl = document.getElementById('inquiryFollowupDate');
        var timeEl = document.getElementById('inquiryFollowupTime');
        var hasDate = !!getFollowupDateIso();
        var hasTime = !!(timeEl && timeEl.value && timeEl.value.trim());
        var canUse = hasDate; // time is optional; defaults to 09:00 in click handler
        btn.disabled = !canUse;
        btn.classList.toggle('inquiry-btn-update--disabled', !canUse);
    }

    function toggleProductChecklist() {
        var field = document.getElementById('inquiryProductsField');
        var isCompleted = statusOrder[selectedStatusIdx] === 'COMPLETED' && !viewMode;
        if (field) {
            field.style.display = isCompleted ? '' : 'none';
            if (!isCompleted) {
                var boxes = field.querySelectorAll('.inquiry-product-checkbox');
                boxes.forEach(function(b) { b.checked = false; });
            }
        }
    }

    function toggleUpdateButton() {
        var isRewarded = currentStatusIdx === statusOrder.length - 1;
        // Disable when viewing, or when attempting to re-submit an older step.
        // Allow editing/re-submitting the CURRENT status, and submitting the NEXT status.
        var selectedName = statusOrder[selectedStatusIdx] || '';
        var isOlderStep = selectedStatusIdx < currentStatusIdx;
        var isBlockedFuture = selectedStatusIdx > currentStatusIdx && !canSelectFutureStatus(statusOrder[currentStatusIdx] || 'PENDING', selectedName);
        var disable = isRewarded || viewMode || isOlderStep || isBlockedFuture;
        updateBtn.disabled = disable;
        updateBtn.classList.toggle('inquiry-btn-update--disabled', disable);
    }

    function statusMatches(orderName, activityStatus) {
        var a = (orderName || '').toUpperCase().replace(/\s+/g, '');
        var b = (activityStatus || '').toUpperCase().replace(/\s+/g, '').replace('REWARDDISTRIBUTED', 'REWARDED');
        if (a === 'REWARDED') return b === 'REWARDED' || b === 'REWARDDISTRIBUTED';
        return a === b;
    }

    function findActivityForStatus(statusOrderName) {
        for (var j = 0; j < cachedActivities.length; j++) {
            if (cachedActivities[j].type === 'activity' && statusMatches(statusOrderName, cachedActivities[j].status)) {
                return cachedActivities[j];
            }
        }
        return null;
    }

    /** Display label for a step: DB status when activity exists, "Current" when it's the current step but no record yet, else canonical name. */
    function getStepDisplayLabel(stepIdx) {
        var stepName = statusOrder[stepIdx];
        if (stepIdx > currentStatusIdx) return stepName;
        var act = findActivityForStatus(stepName);
        if (act && act.status) return (act.status || '').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;') || stepName;
        if (stepIdx === currentStatusIdx) return 'Current';
        return stepName;
    }

    function populateFormFromActivity(activity) {
        var dateEl = document.getElementById('inquiryFollowupDate');
        var timeEl = document.getElementById('inquiryFollowupTime');
        var remarkEl = document.getElementById('inquiryRemark');
        var productBoxes = document.querySelectorAll('.inquiry-product-checkbox');
        if (!activity || !activity.created_at) {
            if (dateEl) dateEl.value = '';
            if (timeEl) timeEl.value = '';
            if (remarkEl) remarkEl.value = '';
            if (productBoxes.length) {
                productBoxes.forEach(function(cb) { cb.checked = false; });
            }
            return;
        }
        var d = new Date(activity.created_at);
        if (dateEl) dateEl.value = isNaN(d.getTime()) ? '' : formatIsoDateForDisplay(d.toISOString().slice(0, 10));
        if (timeEl) timeEl.value = isNaN(d.getTime()) ? '' : String(d.getHours()).padStart(2, '0') + ':' + String(d.getMinutes()).padStart(2, '0');
        if (remarkEl) remarkEl.value = activity.description || '';

        // Restore product selection (e.g. SQL Account) for completed/rewarded activities
        if (productBoxes.length) {
            productBoxes.forEach(function(cb) { cb.checked = false; });
            if (activity && Array.isArray(activity.product_ids)) {
                var ids = activity.product_ids.map(function(v) { return parseInt(v, 10); }).filter(function(v) { return !isNaN(v); });
                productBoxes.forEach(function(cb) {
                    var pid = parseInt(cb.value, 10);
                    if (!isNaN(pid) && ids.indexOf(pid) !== -1) {
                        cb.checked = true;
                    }
                });
            }
        }
    }

    function setFieldsReadOnly(readOnly) {
        var dateEl = document.getElementById('inquiryFollowupDate');
        var timeEl = document.getElementById('inquiryFollowupTime');
        var remarkEl = document.getElementById('inquiryRemark');
        var fileEl = document.getElementById('inquiryAttachment');
        var productBoxes = document.querySelectorAll('.inquiry-product-checkbox');
        // Using readOnly on <input type="date/time"> is buggy in some browsers
        // (it can prevent typing/picker until refocus). Use disabled instead.
        if (dateEl) dateEl.disabled = !!readOnly;
        if (timeEl) timeEl.disabled = !!readOnly;
        if (remarkEl) remarkEl.readOnly = readOnly;
        if (fileEl) fileEl.disabled = readOnly;
        productBoxes.forEach(function(b) { b.disabled = readOnly; });
    }

    function formatActivityTime(isoStr, now) {
        if (!isoStr) return '—';

        var d = null;

        // Prefer parsing as local wall-clock time (ignore timezone), so
        // \"now\" in the database and \"now\" in the browser line up visually.
        if (typeof isoStr === 'string') {
            var m = isoStr.match(/^(\\d{4})-(\\d{2})-(\\d{2})[ T](\\d{2}):(\\d{2})(?::(\\d{2}))?/);
            if (m) {
                var year = parseInt(m[1], 10);
                var month = parseInt(m[2], 10) - 1;
                var day = parseInt(m[3], 10);
                var hour = parseInt(m[4], 10);
                var min = parseInt(m[5], 10);
                var sec = m[6] ? parseInt(m[6], 10) : 0;
                d = new Date(year, month, day, hour, min, sec);
            }
        }

        if (!d) {
            d = new Date(isoStr);
        }

        if (isNaN(d.getTime())) return isoStr;
        if (!now || !(now instanceof Date)) now = new Date();
        var diffSec = Math.floor((now - d) / 1000);
        if (diffSec < 0) return 'just now';
        if (diffSec < 60) return 'just now';
        if (diffSec < 3600) return Math.floor(diffSec / 60) + ' min ago';
        if (diffSec < 86400) return Math.floor(diffSec / 3600) + ' hr ago';
        if (diffSec < 172800) return '1 day ago';
        if (diffSec < 604800) {
            var days = Math.floor(diffSec / 86400);
            return days + ' day' + (days === 1 ? '' : 's') + ' ago';
        }
        if (diffSec < 1209600) return '1 week ago';
        if (diffSec < 2592000) return Math.floor(diffSec / 604800) + ' weeks ago';
        if (diffSec < 5184000) return '1 month ago';
        if (diffSec < 31536000) return Math.floor(diffSec / 2592000) + ' months ago';
        if (diffSec < 63072000) return '1 year ago';
        return Math.floor(diffSec / 31536000) + ' years ago';
    }

    function renderActivity(activities) {
        if (!activityTimeline) return;
        activityTimeline.innerHTML = '';
        var now = new Date();
        if (!activities || activities.length === 0) {
            activityTimeline.innerHTML = '<div class="inquiry-activity-item"><span class="inquiry-activity-bullet"></span><div class="inquiry-activity-content">No activity yet.</div></div>';
            return;
        }
        activities.forEach(function(a) {
            var item = document.createElement('div');
            item.className = 'inquiry-activity-item';
            var user = (a.user || 'System').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            var timeStr = formatActivityTime(a.created_at, now);
            var html = '<span class="inquiry-activity-bullet"></span><div class="inquiry-activity-content">';
            if (a.type === 'created') {
                html += '<strong>' + user + '</strong> created inquiry <span class="inquiry-activity-link">#SQL-' + currentLeadId + '</span>';
            } else {
                var subj = (a.subject || '').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                var desc = (a.description || '').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                var status = (a.status || '').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                html += '<strong>' + user + '</strong> ' + (subj ? subj + ' ' : '');
                if (status) html += 'changed status to <strong class="inquiry-activity-status">' + status + '</strong> ';
                if (desc) html += '<span class="inquiry-activity-desc">' + desc + '</span> ';
            }
            html += '<span class="inquiry-activity-time">' + timeStr + '</span>';
            if (a.attachment_urls && a.attachment_urls.length > 0) {
                html += '<div class="inquiry-activity-attachments">';
                a.attachment_urls.forEach(function(url) {
                    var safe = (url || '').replace(/"/g, '&quot;');
                    html += '<a href="' + safe + '" target="_blank" rel="noopener" class="inquiry-activity-attachment-link"><img src="' + safe + '" alt="Attachment" class="inquiry-activity-attachment-img"></a>';
                });
                html += '</div>';
            }
            html += '</div>';
            item.innerHTML = html;
            activityTimeline.appendChild(item);
        });
    }

    function refreshStepLabels() {
        if (!progressionSteps) return;
        progressionSteps.querySelectorAll('.inquiry-step').forEach(function(step, i) {
            var label = getStepDisplayLabel(i);
            var text = label || step.dataset.step;
            if (step.classList.contains('inquiry-step--done')) {
                step.innerHTML = '<i class="bi bi-check"></i><span>' + text + '</span>';
            } else {
                step.innerHTML = '<span>' + text + '</span>';
            }
        });
    }

    function loadActivity(leadId) {
        var url = '{{ route("dealer.inquiries.activity", ["leadId" => "__ID__"]) }}'.replace('__ID__', leadId);
        fetch(url, { headers: { 'Accept': 'application/json' } })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                cachedActivities = data.activities || [];
                renderActivity(cachedActivities);
                refreshStepLabels();

                // Re-compute the latest status from DB activities (by created_at) to keep UI in sync
                // with whatever Firebird considers the latest row.
                if (!userPickedStep) {
                    // Prefer server-computed latest (based on CREATIONDATE + LEAD_ACTID).
                    if (data && data.latest_status) {
                        setProgression(data.latest_status);
                    }

                    // If opened from the edit icon, auto-select the NEXT status for editing.
                    if (openStartNext) {
                        // Switch to edit mode and re-run progression so the NEXT step becomes active.
                        // (setProgression chooses next step when viewMode=false)
                        viewMode = false;
                        if (data && data.latest_status) {
                            setProgression(data.latest_status);
                        }
                        setFieldsReadOnly(false);
                        setRemarkPlaceholder(statusOrder[selectedStatusIdx]);
                        setDateTimeLabels(statusOrder[selectedStatusIdx]);

                        var remarkEl = document.getElementById('inquiryRemark');
                        var dateEl = document.getElementById('inquiryFollowupDate');
                        var timeEl = document.getElementById('inquiryFollowupTime');
                        if (remarkEl) remarkEl.value = '';
                        if (dateEl) dateEl.value = getDefaultDate();
                        if (timeEl) timeEl.value = getDefaultTime();

                        // One-shot behavior per open.
                        openStartNext = false;
                    }
                }

                // Min-date validation for the calendar picker (disable wrong dates).
                // Use server-provided latest_created_at (based on LEAD_ACT.CREATIONDATE).
                latestMinDate = '';
                latestMinTime = '';
                if (data && data.latest_created_at) {
                    var dmin = new Date(data.latest_created_at);
                    if (!isNaN(dmin.getTime())) {
                        latestMinDate = dmin.toISOString().slice(0, 10);
                        latestMinTime = String(dmin.getHours()).padStart(2, '0') + ':' + String(dmin.getMinutes()).padStart(2, '0');
                    }
                }
                setDateTimeMinConstraints();

                // In view mode, always populate the selected (submitted) status from DB.
                // Submitted statuses are view-only (read-only fields).
                if (modal && modal.classList.contains('inquiry-modal-open') && viewMode && selectedStatusIdx <= currentStatusIdx) {
                    var cur = statusOrder[selectedStatusIdx] || 'PENDING';
                    var act = findActivityForStatus(cur);
                    populateFormFromActivity(act);
                    setFieldsReadOnly(true);
                    setDateTimeLabels(cur);
                }

                var details = data.last_reward_details;
                if (details && currentStatusIdx === statusOrder.length - 1) {
                    var dateEl = document.getElementById('inquiryFollowupDate');
                    var timeEl = document.getElementById('inquiryFollowupTime');
                    var remarkEl = document.getElementById('inquiryRemark');
                    if (dateEl && details.date) dateEl.value = formatIsoDateForDisplay(details.date);
                    if (timeEl && details.time) timeEl.value = details.time;
                    if (remarkEl && details.description) remarkEl.value = details.description;
                    setFieldsReadOnly(true);
                }
            })
            .catch(function() {
                renderActivity([]);
            });
    }

    function applyRowStatusUpdate(buttonEl, toStatus, meta) {
        if (!buttonEl) return;
        var row = buttonEl.closest('.inquiry-row');
        if (!row) return;
        var statusCell = row.querySelector('td[data-col="status"] .inquiries-status');
        if (!statusCell) return;

        // Normalized status names used in the UI
        var label, rawUpper, cls;
        var s = (toStatus || '').toUpperCase();
        switch (s) {
            case 'PENDING':
                rawUpper = 'PENDING';
                label = 'PENDING';
                cls = 'inquiries-status-pending';
                break;
            case 'FOLLOW UP':
                rawUpper = 'FOLLOWUP';
                label = 'Follow Up';
                cls = 'inquiries-status-followup';
                break;
            case 'DEMO':
                rawUpper = 'DEMO';
                label = 'DEMO';
                cls = 'inquiries-status-demo';
                break;
            case 'CONFIRMED':
                rawUpper = 'CONFIRMED';
                label = 'CONFIRMED';
                cls = 'inquiries-status-confirmed';
                break;
            case 'COMPLETED':
                rawUpper = 'COMPLETED';
                label = 'COMPLETED';
                cls = 'inquiries-status-completed';
                break;
            case 'REWARDED':
            case 'REWARD DISTRIBUTED':
                rawUpper = 'REWARDED';
                label = 'REWARDED';
                cls = 'inquiries-status-rewarded';
                break;
            case 'FAILED':
                rawUpper = 'FAILED';
                label = 'FAILED';
                cls = 'inquiries-status-failed';
                break;
            default:
                rawUpper = s || 'PENDING';
                label = rawUpper;
                cls = 'inquiries-status-new';
                break;
        }

        // Update the button's data-status so next modal open uses the new status
        buttonEl.dataset.status = rawUpper;

        // Update the badge text + class
        statusCell.textContent = label;
        statusCell.className = 'inquiries-status ' + cls;

        // Optionally show saved data (date/time/remark) as a tooltip on the status badge
        if (meta && (meta.date || meta.time || meta.remark)) {
            var parts = [];
            if (meta.date) parts.push('Date: ' + meta.date);
            if (meta.time) parts.push('Time: ' + meta.time);
            if (meta.remark) parts.push('Remark: ' + meta.remark);
            statusCell.title = parts.join(' | ');
        }
    }

    var attachmentFiles = [];
    var attachmentPreviewUrls = [];

    function clearAttachmentPreviews() {
        attachmentPreviewUrls.forEach(function(url) { try { URL.revokeObjectURL(url); } catch (e) {} });
        attachmentPreviewUrls = [];
        attachmentFiles = [];
        var previewEl = document.getElementById('inquiryAttachmentPreview');
        if (previewEl) previewEl.innerHTML = '';
        var inputEl = document.getElementById('inquiryAttachment');
        if (inputEl) inputEl.value = '';
    }

    function renderAttachmentPreviews() {
        var previewEl = document.getElementById('inquiryAttachmentPreview');
        if (!previewEl) return;
        attachmentPreviewUrls.forEach(function(url) { try { URL.revokeObjectURL(url); } catch (e) {} });
        attachmentPreviewUrls = [];
        previewEl.innerHTML = '';
        attachmentFiles.forEach(function(file, index) {
            if (!file.type || file.type.indexOf('image/') !== 0) return;
            var url = URL.createObjectURL(file);
            attachmentPreviewUrls.push(url);
            var item = document.createElement('div');
            item.className = 'inquiry-attachment-preview-item';
            var img = document.createElement('img');
            img.src = url;
            img.alt = file.name || 'Image';
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'inquiry-attachment-preview-remove';
            btn.setAttribute('aria-label', 'Remove image');
            btn.innerHTML = '&times;';
            btn.addEventListener('click', function() {
                attachmentFiles.splice(index, 1);
                renderAttachmentPreviews();
            });
            item.appendChild(img);
            item.appendChild(btn);
            previewEl.appendChild(item);
        });
    }

    var attachmentInput = document.getElementById('inquiryAttachment');
    if (attachmentInput) {
        attachmentInput.addEventListener('change', function() {
            var files = this.files;
            if (!files || !files.length) return;
            for (var i = 0; i < files.length; i++) {
                if (files[i].type && files[i].type.indexOf('image/') === 0) {
                    attachmentFiles.push(files[i]);
                }
            }
            renderAttachmentPreviews();
            this.value = '';
        });
    }

    function formatIsoDateForDisplay(value) {
        var raw = String(value || '').trim();
        if (!raw) return '';
        var isoMatch = raw.match(/^(\d{4})-(\d{2})-(\d{2})/);
        if (isoMatch) {
            return isoMatch[3] + '/' + isoMatch[2] + '/' + isoMatch[1];
        }
        var parsed = new Date(raw);
        if (isNaN(parsed.getTime())) return '';
        return String(parsed.getDate()).padStart(2, '0') + '/' + String(parsed.getMonth() + 1).padStart(2, '0') + '/' + parsed.getFullYear();
    }

    function normalizeFollowupDateInputValue(value) {
        var digits = String(value || '').replace(/\D/g, '').slice(0, 8);
        var parts = [];
        if (digits.length > 0) parts.push(digits.slice(0, 2));
        if (digits.length > 2) parts.push(digits.slice(2, 4));
        if (digits.length > 4) parts.push(digits.slice(4, 8));
        return parts.join('/');
    }

    function getFollowupDateIso() {
        var dateEl = document.getElementById('inquiryFollowupDate');
        var raw = dateEl ? String(dateEl.value || '').trim() : '';
        if (!raw) return '';
        if (/^\d{4}-\d{2}-\d{2}$/.test(raw)) return raw;
        var match = raw.match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
        if (!match) return '';
        var day = parseInt(match[1], 10);
        var month = parseInt(match[2], 10);
        var year = parseInt(match[3], 10);
        var parsed = new Date(year, month - 1, day);
        if (parsed.getFullYear() !== year || parsed.getMonth() !== month - 1 || parsed.getDate() !== day) {
            return '';
        }
        return year + '-' + String(month).padStart(2, '0') + '-' + String(day).padStart(2, '0');
    }

    function getTodayIsoDate() {
        var d = new Date();
        return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
    }

    function getCurrentLocalTime() {
        var d = new Date();
        return String(d.getHours()).padStart(2, '0') + ':' + String(d.getMinutes()).padStart(2, '0');
    }

    function getDefaultDate() {
        var d = new Date();
        return String(d.getDate()).padStart(2, '0') + '/' + String(d.getMonth() + 1).padStart(2, '0') + '/' + d.getFullYear();
    }
    function getDefaultTime() {
        var d = new Date();
        return String(d.getHours()).padStart(2, '0') + ':' + String(d.getMinutes()).padStart(2, '0');
    }

    var openStartNext = false;

    function openModal(leadId, customer, status, startNext, referralCode) {
        currentLeadId = leadId;
        currentReferralCode = String(referralCode || '').trim();
        currentCustomer = customer || '—';
        userPickedStep = false;
        openStartNext = !!startNext;
        // Default: show the LAST submitted status details from DB (read-only).
        // Dealer must click the next step to create a new update.
        viewMode = true;
        subtitle.textContent = 'Inquiry ID: #SQL-' + leadId + ' • ' + currentCustomer;
        if (activityLink) activityLink.textContent = '#SQL-' + leadId;
        setProgression(status || 'PENDING');
        var remarkEl = document.getElementById('inquiryRemark');
        var dateEl = document.getElementById('inquiryFollowupDate');
        var timeEl = document.getElementById('inquiryFollowupTime');
        if (remarkEl) remarkEl.value = '';
        if (dateEl) dateEl.value = '';
        if (timeEl) timeEl.value = '';
        var productBoxes = document.querySelectorAll('.inquiry-product-checkbox');
        if (productBoxes.length) productBoxes.forEach(function(b) { b.checked = false; });
        clearAttachmentPreviews();
        // Select current (last submitted) step for viewing
        selectedStatusIdx = currentStatusIdx;
        setFieldsReadOnly(true);
        setDateTimeLabels(statusOrder[currentStatusIdx] || 'FOLLOW UP');
        loadActivity(leadId);
        modal.setAttribute('aria-hidden', 'false');
        modal.classList.add('inquiry-modal-open');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        clearAttachmentPreviews();
        modal.setAttribute('aria-hidden', 'true');
        modal.classList.remove('inquiry-modal-open');
        document.body.style.overflow = '';
    }

    if (progressionSteps) {
        progressionSteps.addEventListener('click', function(e) {
            var step = e.target.closest('.inquiry-step');
            if (!step) return;
            userPickedStep = true;
            if (step.classList.contains('inquiry-step--no-click')) return;
            var stepIdx = statusOrder.indexOf(step.dataset.step);
            if (stepIdx < 0) return;
            var isDoneStep = stepIdx <= currentStatusIdx;
            var isFutureStep = step.classList.contains('inquiry-step--clickable');
            if (!isDoneStep && !isFutureStep) return;
            selectedStatusIdx = stepIdx;
            if (isDoneStep) {
                viewMode = true;
                var act = findActivityForStatus(statusOrder[stepIdx]);
                populateFormFromActivity(act);
                setFieldsReadOnly(true);
                setDateTimeLabels(statusOrder[stepIdx]);
            } else {
                viewMode = false;
                var remarkEl = document.getElementById('inquiryRemark');
                var dateEl = document.getElementById('inquiryFollowupDate');
                var timeEl = document.getElementById('inquiryFollowupTime');
                if (remarkEl) remarkEl.value = '';
                if (dateEl) dateEl.value = getDefaultDate();
                if (timeEl) timeEl.value = getDefaultTime();
                setFieldsReadOnly(false);
                setDateTimeMinConstraints();
                setRemarkPlaceholder(statusOrder[stepIdx]);
                setDateTimeLabels(statusOrder[stepIdx]);
            }
            refreshProgressionState();
            toggleAddCalendarButton();
            toggleProductChecklist();
            toggleUpdateButton();
        });
    }

    var viewModal = document.getElementById('inquiryViewModal');
    var viewSubtitle = document.getElementById('inquiryViewModalSubtitle');
    var viewContent = document.getElementById('inquiryViewMessageContent');
    var viewCloseBtn = document.getElementById('inquiryViewModalClose');
    var viewCloseBtnFooter = document.getElementById('inquiryViewModalCloseBtn');
    var currentUpdateButtonEl = null;
    var notificationFocusLeadId = null;

    function clearNotificationFocusParams() {
        if (window.history && typeof window.history.replaceState === 'function') {
            var url = new URL(window.location.href);
            url.searchParams.delete('lead');
            url.searchParams.delete('fromNotif');
            url.searchParams.delete('notif');
            url.searchParams.delete('action');
            window.history.replaceState({}, document.title, url.pathname + url.search + url.hash);
        }
    }

    function focusLeadFromNotification(lead, attempt) {
        attempt = attempt || 0;
        if (!lead || !table) {
            clearNotificationFocusParams();
            return;
        }

        var row = table.querySelector('tr.inquiry-row[data-lead-id="' + lead + '"]');
        if (!row) {
            if (attempt >= 4) {
                clearNotificationFocusParams();
                return;
            }
            setTimeout(function() { focusLeadFromNotification(lead, attempt + 1); }, 180);
            return;
        }

        var page = parseInt(row.getAttribute('data-page') || '1', 10);
        if (typeof window.dealerGoToPage === 'function') {
            window.dealerGoToPage(page);
        }

        setTimeout(function() {
            var activeRow = table.querySelector('tr.inquiry-row[data-lead-id="' + lead + '"]');
            if (!activeRow) {
                if (attempt >= 4) {
                    clearNotificationFocusParams();
                    return;
                }
                focusLeadFromNotification(lead, attempt + 1);
                return;
            }

            activeRow.classList.add('inquiry-row--notif-highlight');
            notificationFocusLeadId = lead;
            try { activeRow.scrollIntoView({ behavior: 'smooth', block: 'center' }); } catch (e) {}
            setTimeout(function() {
                activeRow.classList.remove('inquiry-row--notif-highlight');
                if (notificationFocusLeadId === lead) notificationFocusLeadId = null;
            }, 8000);

            clearNotificationFocusParams();
        }, 180);
    }

    function openLeadActionFromQuery(lead, action, attempt) {
        attempt = attempt || 0;
        if (!lead || !action || !table) {
            clearNotificationFocusParams();
            return;
        }

        var row = table.querySelector('tr.inquiry-row[data-lead-id="' + lead + '"]');
        if (!row) {
            if (attempt >= 4) {
                clearNotificationFocusParams();
                return;
            }
            setTimeout(function() { openLeadActionFromQuery(lead, action, attempt + 1); }, 180);
            return;
        }

        var page = parseInt(row.getAttribute('data-page') || '1', 10);
        if (typeof window.dealerGoToPage === 'function') {
            window.dealerGoToPage(page);
        }

        setTimeout(function() {
            var activeRow = table.querySelector('tr.inquiry-row[data-lead-id="' + lead + '"]');
            if (!activeRow) {
                if (attempt >= 4) {
                    clearNotificationFocusParams();
                    return;
                }
                openLeadActionFromQuery(lead, action, attempt + 1);
                return;
            }

            activeRow.classList.add('inquiry-row--notif-highlight');
            notificationFocusLeadId = lead;
            try { activeRow.scrollIntoView({ behavior: 'smooth', block: 'center' }); } catch (e) {}
            setTimeout(function() {
                activeRow.classList.remove('inquiry-row--notif-highlight');
                if (notificationFocusLeadId === lead) notificationFocusLeadId = null;
            }, 8000);

            var actionBtn = null;
            if (action === 'update') {
                actionBtn = activeRow.querySelector('.inquiries-update-btn');
            }

            if (!actionBtn) {
                clearNotificationFocusParams();
                return;
            }

            setTimeout(function() {
                currentUpdateButtonEl = actionBtn;
                actionBtn.click();
                clearNotificationFocusParams();
            }, 200);
        }, 180);
    }

    // If URL has ?lead=ID, jump to that row and highlight it.
    (function() {
        var params = new URLSearchParams(window.location.search);
        var lead = params.get('lead');
        var action = String(params.get('action') || '').toLowerCase().trim();
        if (lead && table) {
            if (action === 'update') {
                setTimeout(function() { openLeadActionFromQuery(lead, action, 0); }, 120);
            } else {
                setTimeout(function() { focusLeadFromNotification(lead, 0); }, 120);
            }
        }
    })();

    // Delegate click handling for Update / View buttons so it continues to work
    // after the table body is replaced by Sync.
    document.addEventListener('click', function(e) {
        var updateBtnEl = e.target.closest('.inquiries-update-btn');
        if (updateBtnEl) {
            e.preventDefault();
            currentUpdateButtonEl = updateBtnEl;
            // Clicking edit icon should jump to the next status to be updated.
            openModal(updateBtnEl.dataset.leadId, updateBtnEl.dataset.customer, updateBtnEl.dataset.status, true, updateBtnEl.dataset.referralCode);
            return;
        }

        var viewBtnEl = e.target.closest('.inquiries-view-btn');
        if (viewBtnEl) {
            e.preventDefault();
            var leadId = viewBtnEl.dataset.leadId;
            var customer = viewBtnEl.dataset.customer || '—';
            if (viewSubtitle) viewSubtitle.textContent = 'Inquiry ID: #SQL-' + leadId + ' • ' + customer;
            if (viewContent) viewContent.textContent = 'Loading...';
            viewModal.setAttribute('aria-hidden', 'false');
            viewModal.classList.add('inquiry-modal-open');
            document.body.style.overflow = 'hidden';
            fetch('{{ url("/dealer/inquiries") }}/' + leadId + '/failed-description', {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (viewContent) viewContent.textContent = (data.description || '').trim() || '—';
            })
            .catch(function() {
                if (viewContent) viewContent.textContent = '—';
            });
        }
    });
    function closeViewModal() {
        viewModal.setAttribute('aria-hidden', 'true');
        viewModal.classList.remove('inquiry-modal-open');
        document.body.style.overflow = '';
        if (notificationFocusLeadId && table) {
            var highlightedRow = table.querySelector('tr.inquiry-row[data-lead-id="' + notificationFocusLeadId + '"]');
            if (highlightedRow) {
                highlightedRow.classList.remove('inquiry-row--notif-highlight');
                void highlightedRow.offsetWidth;
                highlightedRow.classList.add('inquiry-row--notif-highlight');
                try { highlightedRow.scrollIntoView({ behavior: 'smooth', block: 'center' }); } catch (e) {}
            }
        }
    }
    if (viewCloseBtn) viewCloseBtn.addEventListener('click', closeViewModal);
    if (viewCloseBtnFooter) viewCloseBtnFooter.addEventListener('click', closeViewModal);
    viewModal.addEventListener('click', function(e) {
        if (e.target === viewModal) closeViewModal();
    });

    [closeBtn, cancelBtn].forEach(function(btn) {
        if (btn) btn.addEventListener('click', closeModal);
    });

    modal.addEventListener('click', function(e) {
        if (e.target === modal) closeModal();
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            if (viewModal && viewModal.classList.contains('inquiry-modal-open')) closeViewModal();
            else if (modal.classList.contains('inquiry-modal-open')) closeModal();
        }
    });

    var addCalendarBtn = document.getElementById('inquiryModalAddCalendar');
    if (addCalendarBtn) {
        addCalendarBtn.addEventListener('click', function() {
            if (addCalendarBtn.disabled) return;
            var dateEl = document.getElementById('inquiryFollowupDate');
            var timeEl = document.getElementById('inquiryFollowupTime');
            var remarkEl = document.getElementById('inquiryRemark');
            var dateStr = getFollowupDateIso();
            var timeStr = timeEl ? timeEl.value : '';
            var remark = remarkEl ? remarkEl.value.trim() : '';
            if (dateEl && (dateEl.value || '').trim() !== '' && dateStr === '') {
                showDealerInquiryToast('Please use dd/mm/yyyy for the date.');
                dateEl.focus();
                return;
            }
            if (dateStr !== '' && dateStr > getTodayIsoDate()) {
                showDealerInquiryToast('Date cannot be in the future.');
                dateEl.focus();
                return;
            }
            if (dateStr === getTodayIsoDate() && timeEl && timeStr && timeStr > getCurrentLocalTime()) {
                showDealerInquiryToast('Time cannot be in the future.');
                timeEl.focus();
                return;
            }
            var title = 'Demo: #SQL-' + currentLeadId + ' - ' + currentCustomer;
            var details = 'Inquiry #SQL-' + currentLeadId + '\nCustomer: ' + currentCustomer + (remark ? '\n\n' + remark : '');
            var startDate, endDate;
            if (dateStr) {
                var d = new Date(dateStr + (timeStr ? 'T' + timeStr : 'T09:00:00'));
                if (isNaN(d.getTime())) d = new Date();
                startDate = d;
                endDate = new Date(d.getTime() + 60 * 60 * 1000);
            } else {
                startDate = new Date();
                endDate = new Date(startDate.getTime() + 60 * 60 * 1000);
            }
            var fmt = function(d) {
                var y = d.getFullYear(), m = String(d.getMonth() + 1).padStart(2, '0'), day = String(d.getDate()).padStart(2, '0');
                var h = String(d.getHours()).padStart(2, '0'), min = String(d.getMinutes()).padStart(2, '0'), s = String(d.getSeconds()).padStart(2, '0');
                return y + m + day + 'T' + h + min + s;
            };
            var url = 'https://calendar.google.com/calendar/render?action=TEMPLATE&text=' + encodeURIComponent(title) + '&dates=' + fmt(startDate) + '/' + fmt(endDate) + '&details=' + encodeURIComponent(details);
            window.open(url, '_blank', 'noopener,noreferrer');
        });
    }

    // Keep Add Calendar enabled/disabled in sync with date/time inputs.
    var followupDateEl = document.getElementById('inquiryFollowupDate');
    var followupTimeEl = document.getElementById('inquiryFollowupTime');
    if (followupDateEl) followupDateEl.addEventListener('input', function() {
        followupDateEl.value = normalizeFollowupDateInputValue(followupDateEl.value);
        toggleAddCalendarButton();
    });
    if (followupTimeEl) followupTimeEl.addEventListener('input', toggleAddCalendarButton);
    if (followupDateEl) followupDateEl.addEventListener('input', setDateTimeMinConstraints);
    if (followupDateEl) followupDateEl.addEventListener('blur', function() {
        var iso = getFollowupDateIso();
        var raw = (followupDateEl.value || '').trim();
        if (raw === '') {
            followupDateEl.setCustomValidity('');
            return;
        }
        if (iso === '') {
            followupDateEl.setCustomValidity('Use dd/mm/yyyy');
            followupDateEl.reportValidity();
            return;
        }
        followupDateEl.value = formatIsoDateForDisplay(iso);
        setDateTimeMinConstraints();
    });
    if (followupTimeEl) followupTimeEl.addEventListener('input', setDateTimeMinConstraints);
    function showDealerInquiryToast(message) {
        var id = 'dealer-inquiry-action-toast';
        var el = document.getElementById(id);
        if (!el) {
            el = document.createElement('div');
            el.id = id;
            el.className = 'inquiries-mark-failed-blocked-toast inquiries-mark-failed-blocked-toast-hidden';
            el.setAttribute('role', 'status');
            el.setAttribute('aria-live', 'polite');
            document.body.appendChild(el);
        }
        el.textContent = message || 'Done.';
        el.classList.remove('inquiries-mark-failed-blocked-toast-hidden');
        clearTimeout(el._hideTimer);
        el._hideTimer = setTimeout(function() {
            el.classList.add('inquiries-mark-failed-blocked-toast-hidden');
        }, 4200);
    }

    updateBtn.addEventListener('click', function() {
        // Allow submitting the selected step:
        // - current status: allowed (acts like "edit" by inserting a new LEAD_ACT row with same status)
        // - next/future status: allowed
        var selectedName = statusOrder[selectedStatusIdx];
        if (this.disabled) return;
        if (selectedStatusIdx < currentStatusIdx) return;
        var toStatus = statusOrder[selectedStatusIdx];
        var fromStatus = statusOrder[currentStatusIdx] || 'PENDING';
        if (selectedStatusIdx > currentStatusIdx && !canSelectFutureStatus(fromStatus, toStatus)) {
            showDealerInquiryToast(getBlockedStatusMessage(fromStatus, toStatus));
            return;
        }
        if (toStatus === 'COMPLETED') {
            var checked = document.querySelectorAll('.inquiry-product-checkbox:checked');
            if (!checked.length) {
                showDealerInquiryToast('Please select at least one product for COMPLETED status.');
                return;
            }
        }
        if (toStatus === 'REWARDED' && attachmentFiles.length === 0) {
            showDealerInquiryToast('Please upload at least one attachment for REWARDED status.');
            if (attachmentInput) attachmentInput.focus();
            return;
        }
        var leadId = currentLeadId;
        var remarkEl = document.getElementById('inquiryRemark');
        var dateEl = document.getElementById('inquiryFollowupDate');
        var timeEl = document.getElementById('inquiryFollowupTime');
        var remark = remarkEl ? remarkEl.value.trim() : '';
        var activityDate = getFollowupDateIso();
        var activityTime = timeEl ? timeEl.value.trim() : '';
        if (dateEl && (dateEl.value || '').trim() !== '' && activityDate === '') {
            showDealerInquiryToast('Please use dd/mm/yyyy for the date.');
            dateEl.focus();
            return;
        }
        if (activityDate !== '' && activityDate > getTodayIsoDate()) {
            showDealerInquiryToast('Date cannot be in the future.');
            dateEl.focus();
            return;
        }
        if (activityDate === getTodayIsoDate() && activityTime !== '' && activityTime > getCurrentLocalTime()) {
            showDealerInquiryToast('Time cannot be in the future.');
            timeEl.focus();
            return;
        }
        var products = [];
        if (toStatus === 'COMPLETED') {
            document.querySelectorAll('.inquiry-product-checkbox:checked').forEach(function(cb) {
                products.push({ id: cb.value, name: cb.dataset.name });
            });
        }
        var updateUrl = '{{ route("dealer.inquiries.update-status") }}';
        var csrfToken = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '';
        updateBtn.disabled = true;
        var body;
        var headers = {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest'
        };
        if (attachmentFiles.length > 0) {
            var formData = new FormData();
            formData.append('lead_id', leadId);
            formData.append('status', toStatus);
            formData.append('remark', remark);
            formData.append('activity_date', activityDate);
            formData.append('activity_time', activityTime);
            formData.append('products', JSON.stringify(products));
            attachmentFiles.forEach(function(file) {
                formData.append('attachments[]', file);
            });
            body = formData;
        } else {
            headers['Content-Type'] = 'application/json';
            body = JSON.stringify({ lead_id: leadId, status: toStatus, remark: remark, activity_date: activityDate, activity_time: activityTime, products: products });
        }
        fetch(updateUrl, {
            method: 'POST',
            headers: headers,
            body: body
        })
        .then(function(r) { return r.json().then(function(d) { return { ok: r.ok, data: d }; }); })
        .then(function(res) {
            updateBtn.disabled = false;
            if (res.ok && res.data.success) {
                // Refresh only the Update Inquiry Status form/modal state instead of reloading the whole page
                var leadIdNow = currentLeadId;
                var newStatus = toStatus;

                // Move progression to the submitted status (tick it), and
                // auto-advance selection to the NEXT status for the next update.
                setProgression(newStatus);
                // Show what was just submitted (saved DB data) in view-only mode.
                viewMode = true;
                selectedStatusIdx = currentStatusIdx;
                setFieldsReadOnly(true);
                setDateTimeLabels(statusOrder[currentStatusIdx] || 'FOLLOW UP');

                // Clear attachments UI and files (they are already saved)
                clearAttachmentPreviews();

                // Reload activity/timeline and step labels from the database
                if (leadIdNow) {
                    loadActivity(leadIdNow);
                }

                // Update the row in the table (badge + button data-status) so it reflects the new status
                if (currentUpdateButtonEl) {
                    var meta = {
                        date: activityDate,
                        time: activityTime,
                        remark: remark
                    };
                    applyRowStatusUpdate(currentUpdateButtonEl, newStatus, meta);
                }

                // Re‑evaluate controls for the new status
                toggleAddCalendarButton();
                toggleProductChecklist();
                toggleUpdateButton();

                showDealerInquiryToast(res.data.message || 'Status updated successfully');
            } else {
                showDealerInquiryToast(res.data.message || 'Update failed');
            }
        })
        .catch(function() {
            updateBtn.disabled = false;
            showDealerInquiryToast('Update failed. Please try again.');
        });
    });
})();
</script>
@endpush
@endsection
