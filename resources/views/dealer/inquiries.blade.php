@extends('layouts.app')
@section('title', 'My Inquiries – SQL LMS Dealer Console')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/dealer-inquiries.css') }}?v=20260422-01">
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
    @include('dealer.partials.console-inquiries-tabs', ['dealerConsoleTab' => $dealerConsoleTab ?? 'inquiries'])
    <section class="inquiries-mgmt-panel dealer-inquiries-panel">
        <div class="inquiries-panel-header">
        <div class="inquiries-panel-title-wrap">
            <i class="bi bi-folder2-open inquiries-panel-icon"></i>
            <h2 class="inquiries-panel-title">My Inquiries</h2>
        </div>
        <div class="inquiries-panel-actions">
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
                        <label class="inquiries-columns-check"><input type="checkbox" data-col="state"> STATE</label>
                        <label class="inquiries-columns-check"><input type="checkbox" data-col="country"> COUNTRY</label>
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
                        @if(($dealerConsoleTab ?? 'inquiries') === 'inquiries')
                            <label class="inquiries-columns-check"><input type="checkbox" data-col="status"> STATUS</label>
                        @endif
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
                        <x-tables.text-filter-header col="inquiryid" label="INQUIRY ID" />
                        <x-tables.text-filter-header col="date" label="INQUIRY DATE" />
                        <x-tables.text-filter-header col="customer" label="CUSTOMER NAME" />
                        <x-tables.text-filter-header col="email" label="EMAIL" />
                        <x-tables.text-filter-header col="postcode" label="POSTCODE" />
                        <x-tables.text-filter-header col="city" label="CITY" />
                        <x-tables.text-filter-header col="state" label="STATE" />
                        <x-tables.text-filter-header col="country" label="COUNTRY" />
                        <x-tables.text-filter-header col="address" label="ADDRESS" />
                        <x-tables.text-filter-header col="contactno" label="CONTACT NO" />
                        <x-tables.text-filter-header col="businessnature" label="BUSINESS NATURE" />
                        <x-tables.operator-filter-header col="users" label="USERS" />
                        <x-tables.text-filter-header col="existingsw" label="EXISTING SW" />
                        <x-tables.text-filter-header col="demomode" label="DEMO MODE" />
                        <x-tables.text-filter-header col="products" label="PRODUCTS" />
                        <x-tables.text-filter-header col="assigndate" label="ASSIGN DATE" />
                        <x-tables.text-filter-header col="completiondate" label="COMPLETION DATE" />
                        <x-tables.text-filter-header col="payoutsdate" label="PAYOUTS DATE" />
                        <x-tables.text-filter-header col="message" label="MESSAGE" />
                        <x-tables.text-filter-header col="referralcode" label="REFERRAL CODE" />
                        <x-tables.text-filter-header col="attachment" label="ATTACHMENT" />
                        <x-tables.text-filter-header col="assignby" label="ASSIGN BY" />
                        @if(($dealerConsoleTab ?? 'inquiries') === 'inquiries')
                            <x-tables.status-multi-filter-header col="status" label="STATUS" :options="$statusFilterOptions" select-class="inquiries-grid-filter inquiries-grid-filter-select" />
                        @endif
                        <x-tables.clear-filter-header button-id="dealerInquiryClearFilters" />
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

@push('scripts')
<script>
function initDealerInquiriesPage() {
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
    var storageKey = 'dealer_inquiries_visible_cols_v12';
    var legacyStorageKey = 'dealer_inquiries_visible_cols_v12_disabled';
    var legacyMobileStorageKey = 'dealer_inquiries_visible_cols_mobile_v1';
    var olderLegacyStorageKey = 'dealer_inquiries_visible_cols_v8';
    var oldestLegacyStorageKey = 'dealer_inquiries_visible_cols_v7';
    var oldestLegacyStorageKeyV6 = 'dealer_inquiries_visible_cols_v6';
    var oldestLegacyStorageKeyV5 = 'dealer_inquiries_visible_cols_v5';
    // Dealer defaults should stay the same on desktop and mobile.
    var legacyDefaultCols = ['inquiryid','date','customer','email','postcode','city','state','country','products','assignby','status'];
    var olderLegacyDefaultCols = ['inquiryid','date','customer','postcode','city','state','country','businessnature','products','assignby','status'];
    var previousDefaultCols = ['inquiryid','date','customer','email','postcode','city','state','country','products','status'];
    var compactMobileLegacyCols = ['inquiryid','date','customer'];
    var defaultCols = ['inquiryid','date','customer','email','postcode','city','products','assigndate'{!! ($dealerConsoleTab ?? 'inquiries') === 'inquiries' ? ",'status'" : "" !!}];
    var allCols = ['inquiryid','date','customer','email','postcode','city','state','country','address','contactno','businessnature','users','existingsw','demomode','products','assigndate','completiondate','payoutsdate','message','referralcode','attachment','assignby'{!! ($dealerConsoleTab ?? 'inquiries') === 'inquiries' ? ",'status'" : "" !!}];

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

    var dealerInquirySort = { col: null, dir: 1 };

    function clearDealerInquiryPlaceholderRows() {
        Array.prototype.slice.call(table.querySelectorAll('tbody tr.inquiries-placeholder-row')).forEach(function(row) {
            row.remove();
        });
    }

    function getDealerInquirySortValue(row, col) {
        var cell = row.querySelector('td[data-col="' + col + '"]');
        return (cell && cell.textContent) ? cell.textContent.trim().toLowerCase() : '';
    }

    function setDealerInquiryInitialOrder() {
        table.querySelectorAll('tbody tr.inquiry-row').forEach(function(row, index) {
            row.setAttribute('data-initial-index', String(index));
        });
    }

    function refreshDealerInquiryPaginationAfterSort() {
        if (typeof window.refreshDealerPagination === 'function') {
            window.refreshDealerPagination();
            return;
        }
        window.dealerPaginationState.currentPage = 1;
        if (typeof window.dealerApplyPagination === 'function') {
            window.dealerApplyPagination();
        }
    }

    function sortDealerInquiriesTable() {
        var tbody = table.querySelector('tbody');
        if (!tbody || !dealerInquirySort.col) return;
        clearDealerInquiryPlaceholderRows();
        var rows = Array.prototype.slice.call(tbody.querySelectorAll('tr.inquiry-row'));
        var emptyRow = Array.from(tbody.querySelectorAll('tr')).find(function(row) {
            return !row.classList.contains('inquiry-row') && !!row.querySelector('.dealer-table-empty, .inquiries-empty, .inquiries-empty-cell');
        }) || null;

        rows.sort(function(a, b) {
            var va = getDealerInquirySortValue(a, dealerInquirySort.col);
            var vb = getDealerInquirySortValue(b, dealerInquirySort.col);
            var cmp = va.localeCompare(vb, undefined, { numeric: true });
            return dealerInquirySort.dir * cmp;
        });

        rows.forEach(function(row) { tbody.appendChild(row); });
        if (rows.length === 0 && emptyRow) tbody.appendChild(emptyRow);
        refreshDealerInquiryPaginationAfterSort();
    }

    function clearDealerInquiriesSort() {
        var tbody = table.querySelector('tbody');
        if (!tbody) return;
        dealerInquirySort.col = null;
        dealerInquirySort.dir = 1;
        table.querySelectorAll('thead th[data-col]').forEach(function(header) {
            header.classList.remove('inquiries-sort-asc', 'inquiries-sort-desc');
        });
        clearDealerInquiryPlaceholderRows();
        var rows = Array.prototype.slice.call(tbody.querySelectorAll('tr.inquiry-row'));
        var emptyRow = Array.from(tbody.querySelectorAll('tr')).find(function(row) {
            return !row.classList.contains('inquiry-row') && !!row.querySelector('.dealer-table-empty, .inquiries-empty, .inquiries-empty-cell');
        }) || null;

        rows.sort(function(a, b) {
            var ia = parseInt(a.getAttribute('data-initial-index') || '0', 10);
            var ib = parseInt(b.getAttribute('data-initial-index') || '0', 10);
            return ia - ib;
        });

        rows.forEach(function(row) { tbody.appendChild(row); });
        if (rows.length === 0 && emptyRow) tbody.appendChild(emptyRow);
        refreshDealerInquiryPaginationAfterSort();
    }

    function initSortableDealerInquiries() {
        table.querySelectorAll('thead th[data-col]').forEach(function(th) {
            th.classList.add('inquiries-sortable');
            th.style.cursor = 'pointer';
            th.addEventListener('click', function(e) {
                if (e.target.closest('input, select, button, .inquiries-filter-wrap, .dealer-operator-btn, .dealer-operator-dropdown')) return;
                var col = th.getAttribute('data-col');
                if (!col) return;
                dealerInquirySort.dir = (dealerInquirySort.col === col) ? -dealerInquirySort.dir : 1;
                dealerInquirySort.col = col;
                table.querySelectorAll('thead th[data-col]').forEach(function(header) {
                    header.classList.remove('inquiries-sort-asc', 'inquiries-sort-desc');
                    if (header.getAttribute('data-col') === col) {
                        header.classList.add(dealerInquirySort.dir === 1 ? 'inquiries-sort-asc' : 'inquiries-sort-desc');
                    }
                });
                sortDealerInquiriesTable();
            });
        });
    }

    setDealerInquiryInitialOrder();
    initSortableDealerInquiries();

    function applyDealerGridFilters() {
        var filters = {};
        table.querySelectorAll('.inquiries-grid-filter').forEach(function(inp) {
            var col = inp.getAttribute('data-col');
            if (!col) return;

            if (inp.type === 'checkbox') {
                if (!filters[col]) {
                    filters[col] = { numeric: false, isArray: true, val: [] };
                }
                if (inp.checked) {
                    filters[col].val.push(normalizeDealerInquiryStatus(inp.value));
                }
                return;
            }

            var val = (inp.value || '').trim();
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
                    var rowStatus = normalizeDealerInquiryStatus(cellText);
                    if (filter.isArray) {
                        if (filter.val.indexOf(rowStatus) === -1) {
                            colMatch = false;
                            break;
                        }
                    } else {
                        var normalizedStatusFilter = normalizeDealerInquiryStatus(filter.val);
                        if (normalizedStatusFilter && rowStatus !== normalizedStatusFilter) {
                            colMatch = false;
                            break;
                        }
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
            table.querySelectorAll('.status-multi-filter').forEach(function(f) { if (f._resetStatusFilter) f._resetStatusFilter(); });
            resetDealerInquiryOperatorMenus(table);
            applyDealerGridFilters();
            clearDealerInquiriesSort();
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
            clearDealerInquiryPlaceholderRows();
        }

        function getDealerPaginationTargetRows() {
            return perPage;
        }

        function measureDealerInquiryRowHeight(row) {
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
            measureTable.className = table.className || 'inquiries-table';
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

        function getDealerReferenceRowHeight(tbody) {
            if (!tbody) return 0;
            var visibleRow = Array.prototype.slice.call(tbody.querySelectorAll('tr.inquiry-row')).find(function(row) {
                return window.getComputedStyle(row).display !== 'none';
            });
            var referenceRow = visibleRow || tbody.querySelector('tr.inquiry-row');
            return measureDealerInquiryRowHeight(referenceRow);
        }

        function ensureFixedHeight(visibleDataCount) {
            var tbody = table.querySelector('tbody');
            if (!tbody) return;
            clearPlaceholderRows();
            var allRows = getAllRows();
            var allowZeroFill = allRows.length > 0;
            var useShortHeight = (visibleDataCount > 0 && visibleDataCount < perPage) || (visibleDataCount === 0 && allowZeroFill);
            var targetRows = getDealerPaginationTargetRows();
            var placeholderRowHeight = getDealerReferenceRowHeight(tbody);

            if (visibleDataCount < targetRows && (visibleDataCount > 0 || allowZeroFill)) {
                var visibleHeaderCount = Array.prototype.slice.call(table.querySelectorAll('thead tr:first-child th')).filter(function(cell) {
                    return cell.style.display !== 'none' && window.getComputedStyle(cell).display !== 'none';
                }).length || 1;

                for (var i = visibleDataCount; i < targetRows; i++) {
                    var row = document.createElement('tr');
                    row.className = 'inquiries-placeholder-row';
                    row.setAttribute('aria-hidden', 'true');

                    var cell = document.createElement('td');
                    cell.className = 'inquiries-placeholder-cell';
                    cell.colSpan = visibleHeaderCount;
                    if (placeholderRowHeight > 0) {
                        row.style.height = placeholderRowHeight + 'px';
                        cell.style.height = placeholderRowHeight + 'px';
                        cell.style.minHeight = placeholderRowHeight + 'px';
                    }

                    row.appendChild(cell);
                    tbody.appendChild(row);
                }
            }

            if (scrollWrap) {
                scrollWrap.classList.toggle('inquiries-table-scroll-empty', visibleDataCount === 0 && !allowZeroFill);
                scrollWrap.classList.toggle('inquiries-table-scroll-short', useShortHeight);
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

        window.refreshDealerPagination = function() {
            window.dealerPaginationState.currentPage = 1;
            window.dealerApplyPagination();
        };

    // Bind controls once after the table is initialized.
        controls.forEach(function(btn) {
            btn.onclick = function() {
                var type = btn.getAttribute('data-page');
                var currentPage = window.dealerPaginationState.currentPage;
                var currentLastPage = parseInt(pagination.getAttribute('data-last-page') || '1', 10);
                if (type === 'first') window.dealerGoToPage(1);
                else if (type === 'prev') window.dealerGoToPage(currentPage - 1);
                else if (type === 'next') window.dealerGoToPage(currentPage + 1);
                else if (type === 'last') window.dealerGoToPage(currentLastPage);
            };
        });

        if (!window.__dealerInquiryPaginationResizeBound) {
            window.addEventListener('resize', function() {
                if (typeof window.dealerApplyPagination === 'function') {
                    window.dealerApplyPagination();
                }
            });
            window.__dealerInquiryPaginationResizeBound = true;
        }

        window.dealerApplyPagination();
    }

    // Simple client-side pagination: 10 inquiries per page
    initDealerPagination();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initDealerInquiriesPage, { once: true });
} else {
    initDealerInquiriesPage();
}
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
                    <div class="inquiry-connector" data-next-step="FOLLOW UP"></div>
                    <div class="inquiry-step inquiry-step--red" data-step="FOLLOW UP" data-color="red"><span>FOLLOW UP</span></div>
                    <div class="inquiry-connector" data-next-step="DEMO"></div>
                    <div class="inquiry-step inquiry-step--yellow" data-step="DEMO" data-color="yellow"><span>DEMO</span></div>
                    <div class="inquiry-connector" data-next-step="CONFIRMED"></div>
                    <div class="inquiry-step inquiry-step--yellow" data-step="CONFIRMED" data-color="yellow"><span>CONFIRMED</span></div>
                    <div class="inquiry-connector" data-next-step="COMPLETED"></div>
                    <div class="inquiry-step inquiry-step--green" data-step="COMPLETED" data-color="green"><span>COMPLETED</span></div>
                    <div class="inquiry-connector" data-next-step="REWARDED"></div>
                    <div class="inquiry-step inquiry-step--green" data-step="REWARDED" data-color="green"><span>REWARDED</span></div>
                    <div class="inquiry-connector" data-next-step="FAILED"></div>
                    <div class="inquiry-step inquiry-step--failed" data-step="FAILED" data-color="failed"><span>FAILED</span></div>
                </div>
            </div>
            <div class="inquiry-view-message inquiry-latest-failed-notice" id="inquiryLatestFailedNotice" hidden>
                <div class="inquiry-latest-failed-head">
                    <div class="inquiry-latest-failed-title">
                        <i class="bi bi-x-circle-fill"></i>
                        <span>Lead Marked as Failed</span>
                    </div>
                </div>
                <div class="inquiry-latest-failed-grid">
                    <div class="inquiry-latest-failed-block">
                        <span class="inquiry-field-label">Reason</span>
                        <div class="inquiry-latest-failed-value" id="inquiryLatestFailedReason">—</div>
                    </div>
                    <div class="inquiry-latest-failed-meta" id="inquiryLatestFailedMeta">—</div>
                </div>
            </div>
            <div class="inquiry-followup" id="inquiryFollowupSection">
                <div class="inquiry-followup-header">
                    <i class="bi bi-calendar3"></i>
                    <span>Update Details</span>
                </div>
                <div class="inquiry-followup-fields">
                    <label class="inquiry-field">
                        <span class="inquiry-field-label" id="inquiryDateLabel">FOLLOW-UP DATE</span>
                        <div class="inquiry-field-input-wrap">
                            <i class="bi bi-calendar3"></i>
                            <input type="date" class="inquiry-field-input" id="inquiryFollowupDate">
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
                        <span class="inquiry-field-label" id="inquiryAttachmentLabel">ATTACHMENT (images)</span>
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
        'REWARDED': 'Add specific notes about this reward...',
        'FAILED': 'Add specific notes about this failed inquiry...'
    };
    var dateTimeLabels = {
        'PENDING': { date: 'FOLLOW-UP DATE', time: 'FOLLOW-UP TIME' },
        'FOLLOW UP': { date: 'FOLLOW-UP DATE', time: 'FOLLOW-UP TIME' },
        'DEMO': { date: 'DEMO DATE', time: 'DEMO TIME' },
        'CONFIRMED': { date: 'CONFIRMED DATE', time: 'CONFIRMED TIME' },
        'COMPLETED': { date: 'COMPLETED DATE', time: 'COMPLETED TIME' },
        'REWARDED': { date: 'REWARD DATE', time: 'REWARD TIME' },
        'FAILED': { date: 'FAILED DATE', time: 'FAILED TIME' }
    };
    var statusMap = {
        'PENDING': 'PENDING', 'FOLLOW UP': 'FOLLOW UP', 'FOLLOWUP': 'FOLLOW UP',
        'DEMO': 'DEMO', 'CONFIRMED': 'CONFIRMED', 'CASE CONFIRMED': 'CONFIRMED',
        'COMPLETED': 'COMPLETED', 'CASE COMPLETED': 'COMPLETED',
        'REWARD': 'REWARDED', 'REWARDED': 'REWARDED', 'REWARD DISTRIBUTED': 'REWARDED',
        'FAILED': 'FAILED'
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
    var latestStatusRaw = '';
    var latestNonFailedStatusRaw = '';

    function escapeInquiryHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

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
            case 'FAILED': return 'Failed';
            default: return String(status || '').trim();
        }
    }

    function normalizeStatus(status) {
        var key = (status || '').toString().toUpperCase().trim().replace(/\s+/g, ' ');
        return statusMap[key] || key || 'PENDING';
    }

    function getInquiryActivityTimestamp(activity) {
        if (!activity || !activity.created_at) return 0;
        var ts = Date.parse(activity.created_at);
        return isNaN(ts) ? 0 : ts;
    }

    function sortInquiryActivitiesLatestFirst(activities) {
        return (activities || []).slice().sort(function(a, b) {
            var timeCompare = getInquiryActivityTimestamp(b) - getInquiryActivityTimestamp(a);
            if (timeCompare !== 0) return timeCompare;

            return (parseInt(b && b.lead_act_id, 10) || 0) - (parseInt(a && a.lead_act_id, 10) || 0);
        });
    }

    function findLatestFailedActivity() {
        for (var i = 0; i < cachedActivities.length; i++) {
            var activity = cachedActivities[i];
            if (activity && activity.type === 'activity' && normalizeStatus(activity.status) === 'FAILED') {
                return activity;
            }
        }
        return null;
    }

    function getDisplayProgressionStatus(latestStatus, latestNonFailedStatus) {
        var normalizedLatest = normalizeStatus(latestStatus);
        if (normalizedLatest !== 'FAILED' && statusOrder.indexOf(normalizedLatest) >= 0) {
            return normalizedLatest;
        }

        var normalizedNonFailed = normalizeStatus(latestNonFailedStatus);
        if (normalizedNonFailed !== 'FAILED' && statusOrder.indexOf(normalizedNonFailed) >= 0) {
            return normalizedNonFailed;
        }

        for (var i = 0; i < cachedActivities.length; i++) {
            var normalized = normalizeStatus(cachedActivities[i] && cachedActivities[i].status);
            if (normalized !== 'FAILED' && statusOrder.indexOf(normalized) >= 0) {
                return normalized;
            }
        }

        return 'PENDING';
    }

    function stripFailedStatusPrefix(text) {
        return String(text || '')
            .replace(/^status changed to failed by\b.*?[.:]\s*/i, '')
            .replace(/^failed by\b.*?[.:]\s*/i, '')
            .replace(/^status changed to failed by\b\s*/i, '')
            .replace(/^failed by\b\s*/i, '')
            .trim();
    }

    function parseFailedDescription(description) {
        var raw = String(description || '').trim();
        var parsed = {
            reason: '',
            detail: ''
        };

        if (raw === '') {
            return parsed;
        }

        raw.split(/\r?\n+/).forEach(function(line) {
            var text = String(line || '').trim();
            var lower = text.toLowerCase();
            if (text === '') {
                return;
            }
            if (lower.indexOf('failure reason:') === 0) {
                parsed.reason = text.slice('Failure reason:'.length).trim();
                return;
            }
            if (lower.indexOf('additional details:') === 0) {
                parsed.detail = text.slice('Additional details:'.length).trim();
                return;
            }
            if (lower.indexOf('status changed to failed by ') === 0) {
                var legacyText = stripFailedStatusPrefix(text);
                if (legacyText !== '') {
                    parsed.reason = legacyText;
                }
                return;
            }
            if (parsed.reason === '') {
                parsed.reason = stripFailedStatusPrefix(text);
                return;
            }
            parsed.detail += (parsed.detail ? '\n' : '') + text;
        });

        if (parsed.reason === '') {
            parsed.reason = stripFailedStatusPrefix(raw);
        }

        return parsed;
    }

    function prettifyFailedReason(reason) {
        var text = stripFailedStatusPrefix(reason).replace(/\s+/g, ' ');
        if (!text) return '';
        return text;
    }

    function formatFailedDateTime(value) {
        var date = new Date(value);
        if (isNaN(date.getTime())) return '';

        var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        return date.getDate() + ' ' + months[date.getMonth()] + ' ' + date.getFullYear() + ', ' +
            String(date.getHours()).padStart(2, '0') + ':' + String(date.getMinutes()).padStart(2, '0');
    }

    function buildFailedMeta(activity) {
        var parts = [];
        var failedOn = formatFailedDateTime(activity && activity.created_at);
        var updatedBy = String((activity && (activity.user_id || activity.user)) || '').trim();

        if (failedOn) {
            parts.push(failedOn);
        }

        if (updatedBy) {
            parts.push('Updated by ' + updatedBy);
        }

        return parts.join(' • ') || 'No failed activity details recorded.';
    }

    function renderLatestFailedNotice(activity) {
        var noticeEl = document.getElementById('inquiryLatestFailedNotice');
        var reasonEl = document.getElementById('inquiryLatestFailedReason');
        var metaEl = document.getElementById('inquiryLatestFailedMeta');
        if (!noticeEl || !reasonEl || !metaEl) return;

        var shouldShow = normalizeStatus(latestStatusRaw) === 'FAILED' && !!activity;
        noticeEl.hidden = !shouldShow;
        if (!shouldShow) {
            reasonEl.textContent = '—';
            metaEl.textContent = '—';
            return;
        }

        var parsed = parseFailedDescription(activity.description || '');
        reasonEl.textContent = prettifyFailedReason(parsed.reason || 'No failure reason recorded.');
        metaEl.textContent = buildFailedMeta(activity);
    }

    function getProgressionStep(stepName) {
        if (!progressionSteps) return null;
        return progressionSteps.querySelector('.inquiry-step[data-step="' + stepName + '"]');
    }

    function getProgressionConnector(nextStepName) {
        if (!progressionSteps) return null;
        return progressionSteps.querySelector('.inquiry-connector[data-next-step="' + nextStepName + '"]');
    }

    function isFailedLatestStatus() {
        return normalizeStatus(latestStatusRaw) === 'FAILED';
    }

    function isFailedSelected() {
        return isFailedLatestStatus() && selectedStatusIdx === -1;
    }

    function getSelectedStatusName() {
        return isFailedSelected() ? 'FAILED' : (statusOrder[selectedStatusIdx] || 'PENDING');
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
        var highlightNewStatus = !viewMode && !isFailedSelected() && selectedStatusIdx > currentStatusIdx;
        var failedLatest = isFailedLatestStatus();

        progressionSteps.classList.toggle('inquiry-progression-steps--failed', failedLatest);

        statusOrder.forEach(function(stepName, i) {
            var step = getProgressionStep(stepName);
            var connector = getProgressionConnector(stepName);
            if (!step) return;
            step.classList.remove('inquiry-step--done', 'inquiry-step--active', 'inquiry-step--selected', 'inquiry-step--clickable', 'inquiry-step--no-click', 'inquiry-step--viewable', 'inquiry-step--past-muted', 'inquiry-step--failed-terminal');
            step.hidden = false;
            step.style.display = failedLatest && i > currentStatusIdx ? 'none' : 'flex';
            step.style.order = String(i * 2);
            if (connector) {
                connector.hidden = false;
                connector.style.display = failedLatest && i > currentStatusIdx ? 'none' : 'block';
                connector.style.order = String((i * 2) - 1);
            }
            var label = getStepDisplayLabel(i);
            var displayText = label || stepName;
            var isDone = i <= currentStatusIdx;
            if (isDone) {
                step.classList.add('inquiry-step--done', 'inquiry-step--viewable');
                if (highlightNewStatus || failedLatest) step.classList.add('inquiry-step--past-muted');
                if (i === selectedStatusIdx && viewMode) step.classList.add('inquiry-step--selected');
                step.innerHTML = '<i class="bi bi-check"></i><span>' + escapeInquiryHtml(displayText) + '</span>';
                return;
            }

            var canClick = canSelectFutureStatus(currentStatus, stepName);
            step.innerHTML = '<span>' + escapeInquiryHtml(displayText) + '</span>';
            if (i === selectedStatusIdx && canClick) {
                step.classList.add('inquiry-step--active', 'inquiry-step--selected');
            } else if (canClick) {
                step.classList.add('inquiry-step--clickable');
            } else {
                step.classList.add('inquiry-step--no-click');
            }
        });

        var failedStep = getProgressionStep('FAILED');
        var failedConnector = getProgressionConnector('FAILED');
        if (failedStep) {
            failedStep.classList.remove('inquiry-step--done', 'inquiry-step--active', 'inquiry-step--selected', 'inquiry-step--clickable', 'inquiry-step--no-click', 'inquiry-step--viewable', 'inquiry-step--past-muted', 'inquiry-step--failed-terminal');
            if (failedLatest) {
                if (failedConnector) {
                    failedConnector.hidden = false;
                    failedConnector.style.display = 'block';
                    failedConnector.style.order = String((currentStatusIdx * 2) + 1);
                }
                failedStep.hidden = false;
                failedStep.style.display = 'flex';
                failedStep.style.order = String((currentStatusIdx * 2) + 2);
                if (isFailedSelected()) {
                    failedStep.classList.add('inquiry-step--active', 'inquiry-step--selected', 'inquiry-step--viewable', 'inquiry-step--failed-terminal');
                } else {
                    failedStep.classList.add('inquiry-step--done', 'inquiry-step--viewable', 'inquiry-step--failed-terminal');
                }
                failedStep.innerHTML = '<i class="bi bi-x-lg"></i><span>FAILED</span>';
            } else {
                if (failedConnector) {
                    failedConnector.hidden = true;
                    failedConnector.style.display = 'none';
                }
                failedStep.hidden = true;
                failedStep.style.display = 'none';
                failedStep.classList.add('inquiry-step--no-click');
                failedStep.innerHTML = '<span>FAILED</span>';
            }
        }
    }

    function validateFollowupDateTime() {
        var dateEl = document.getElementById('inquiryFollowupDate');
        var timeEl = document.getElementById('inquiryFollowupTime');
        var activityTime = timeEl ? (timeEl.value || '').trim() : '';
        var activityDate = getFollowupDateIso();
        var rawDate = dateEl ? (dateEl.value || '').trim() : '';

        if (dateEl) {
            dateEl.setCustomValidity('');
        }
        if (timeEl) {
            timeEl.setCustomValidity('');
        }

        if (dateEl && (hasInvalidFollowupDateInput() || (rawDate !== '' && activityDate === ''))) {
            return {
                valid: false,
                field: dateEl,
                message: 'Please choose a valid date.'
            };
        }

        if (activityDate !== '' && latestMinDate && activityDate < latestMinDate) {
            return {
                valid: false,
                field: dateEl,
                message: 'Date cannot before previous activity'
            };
        }

        if (activityDate !== '') {
            var maxFuture = new Date();
            maxFuture.setDate(maxFuture.getDate() + 3);
            maxFuture.setHours(23, 59, 59, 999);
            var selected = new Date(activityDate + 'T00:00:00');
            if (selected > maxFuture) {
                return {
                    valid: false,
                    field: dateEl,
                    message: 'Date cannot be more than 3 days in the future.'
                };
            }
        }

        return {
            valid: true,
            field: null,
            message: '',
            activityDate: activityDate,
            activityTime: activityTime
        };
    }

    function hasActivityForStatus(statusOrderName) {
        return !!findActivityForStatus(statusOrderName);
    }

    function setProgression(currentStatus) {
        var progressionStatus = getDisplayProgressionStatus(currentStatus, latestNonFailedStatusRaw);
        var normalized = normalizeStatus(progressionStatus);
        var idx = statusOrder.indexOf(normalized);
        if (idx < 0) idx = 0;
        currentStatusIdx = idx;
        // In view mode, keep selection on the latest saved status.
        // In edit mode, select the first allowed next status.
        selectedStatusIdx = viewMode ? (isFailedLatestStatus() ? -1 : idx) : getDefaultSelectedStatusIdx(idx);
        refreshProgressionState();
        var selectedStatus = getSelectedStatusName();
        var remarkEl = document.getElementById('inquiryRemark');
        if (remarkEl) remarkEl.placeholder = remarkPlaceholders[selectedStatus] || remarkPlaceholders['PENDING'];
        setDateTimeLabels(selectedStatus);
        toggleAddCalendarButton();
        toggleProductChecklist();
        toggleUpdateButton();
        toggleFailedSummaryMode();
    }

    function setRemarkPlaceholder(status) {
        var remarkEl = document.getElementById('inquiryRemark');
        if (remarkEl) remarkEl.placeholder = remarkPlaceholders[status] || remarkPlaceholders['PENDING'];
    }

    function setDateTimeLabels(status) {
        var labels = dateTimeLabels[status] || dateTimeLabels['FOLLOW UP'];
        var dateLabel = document.getElementById('inquiryDateLabel');
        var timeLabel = document.getElementById('inquiryTimeLabel');
        var attachmentLabel = document.getElementById('inquiryAttachmentLabel');

        if (dateLabel) dateLabel.textContent = labels.date;
        if (timeLabel) timeLabel.textContent = labels.time;
        
        if (attachmentLabel) {
            if (status === 'COMPLETED') {
                attachmentLabel.innerHTML = 'Upload Your own company\'s invoice <span class="inquiry-field-required">*</span>';
            } else if (status === 'REWARDED') {
                attachmentLabel.innerHTML = 'UPLOAD REFERRAL PAYOUT PROOF <span class="inquiry-field-required">*</span>';
            } else {
                attachmentLabel.textContent = 'ATTACHMENT (images)';
            }
        }
    }

    function resetEditableFieldsForSelectedStatus() {
        var remarkEl = document.getElementById('inquiryRemark');
        var dateEl = document.getElementById('inquiryFollowupDate');
        var timeEl = document.getElementById('inquiryFollowupTime');
        var productBoxes = document.querySelectorAll('.inquiry-product-checkbox');
        if (remarkEl) remarkEl.value = '';
        if (dateEl) dateEl.value = getDefaultDate();
        if (timeEl) timeEl.value = getDefaultTime();
        if (productBoxes.length) {
            productBoxes.forEach(function(cb) { cb.checked = false; });
        }
        clearAttachmentPreviews();
    }

    function syncModalAfterSuccessfulStatusSave(savedStatus) {
        var normalizedSavedStatus = normalizeStatus(savedStatus);
        if (normalizedSavedStatus) {
            latestStatusRaw = normalizedSavedStatus;
            if (normalizedSavedStatus !== 'FAILED') {
                latestNonFailedStatusRaw = normalizedSavedStatus;
            }
        }

        var displayStatus = getDisplayProgressionStatus(normalizedSavedStatus, latestNonFailedStatusRaw);
        var savedIdx = statusOrder.indexOf(normalizeStatus(displayStatus));
        if (savedIdx < 0) savedIdx = 0;
        var nextIdx = isFailedLatestStatus() ? -1 : getDefaultSelectedStatusIdx(savedIdx);
        var hasNextEditableStep = nextIdx > savedIdx;

        if (hasNextEditableStep) {
            viewMode = false;
            setProgression(displayStatus);
            setFieldsReadOnly(false);
            setRemarkPlaceholder(getSelectedStatusName());
            setDateTimeLabels(getSelectedStatusName());
            resetEditableFieldsForSelectedStatus();
        } else {
            viewMode = true;
            setProgression(displayStatus);
            setFieldsReadOnly(true);
            setDateTimeLabels(getSelectedStatusName());
            clearAttachmentPreviews();
        }

        toggleAddCalendarButton();
        toggleProductChecklist();
        toggleUpdateButton();
    }

    function toggleAddCalendarButton() {
        var btn = document.getElementById('inquiryModalAddCalendar');
        if (!btn) return;
        if (isFailedLatestStatus()) {
            btn.style.display = 'none';
            btn.disabled = true;
            btn.classList.add('inquiry-btn-update--disabled');
            return;
        }
        var isDemo = getSelectedStatusName() === 'DEMO';
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

    function toggleFailedSummaryMode() {
        var followupSection = document.getElementById('inquiryFollowupSection');
        var shouldCompact = isFailedLatestStatus();
        if (followupSection) {
            followupSection.hidden = shouldCompact;
        }
        if (updateBtn) {
            updateBtn.hidden = shouldCompact;
        }
    }

    function toggleProductChecklist() {
        var field = document.getElementById('inquiryProductsField');
        var isCompleted = getSelectedStatusName() === 'COMPLETED' && !viewMode;
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
        // Only allow submitting a real future step.
        var selectedName = getSelectedStatusName();
        var isCurrentOrOlderStep = selectedStatusIdx <= currentStatusIdx;
        var isBlockedFuture = selectedStatusIdx > currentStatusIdx && !canSelectFutureStatus(statusOrder[currentStatusIdx] || 'PENDING', selectedName);
        var disable = isRewarded || viewMode || isCurrentOrOlderStep || isBlockedFuture;
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
        if (act && act.status) return String(act.status || '').toUpperCase() || stepName;
        if (stepIdx === currentStatusIdx) return 'CURRENT';
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
        if (dateEl) dateEl.value = isNaN(d.getTime()) ? '' : d.toISOString().slice(0, 10);
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
            var user = escapeInquiryHtml(a.user || 'System');
            var timeStr = formatActivityTime(a.created_at, now);
            var html = '<span class="inquiry-activity-bullet"></span><div class="inquiry-activity-content">';
            if (a.type === 'created') {
                html += '<strong>' + user + '</strong> created inquiry <span class="inquiry-activity-link">#SQL-' + currentLeadId + '</span>';
            } else {
                var subj = escapeInquiryHtml(a.subject || '');
                var desc = escapeInquiryHtml(a.description || '').replace(/\r?\n/g, '<br>');
                var status = escapeInquiryHtml(a.status || '');
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
        statusOrder.forEach(function(stepName, i) {
            var step = getProgressionStep(stepName);
            if (!step) return;
            var label = getStepDisplayLabel(i);
            var text = label || stepName;
            if (step.classList.contains('inquiry-step--done')) {
                step.innerHTML = '<i class="bi bi-check"></i><span>' + escapeInquiryHtml(text) + '</span>';
            } else {
                step.innerHTML = '<span>' + escapeInquiryHtml(text) + '</span>';
            }
        });
    }

    function loadActivity(leadId) {
        var url = '{{ route("dealer.inquiries.activity", ["leadId" => "__ID__"]) }}'.replace('__ID__', leadId);
        fetch(url, { headers: { 'Accept': 'application/json' } })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                cachedActivities = sortInquiryActivitiesLatestFirst(data.activities || []);
                latestStatusRaw = normalizeStatus(data && data.latest_status);
                latestNonFailedStatusRaw = normalizeStatus(data && data.latest_non_failed_status);
                renderActivity(cachedActivities);
                refreshStepLabels();
                renderLatestFailedNotice(findLatestFailedActivity());

                // Re-compute the latest status from DB activities (by created_at) to keep UI in sync
                // with whatever Firebird considers the latest row.
                if (!userPickedStep) {
                    // Prefer server-computed latest (based on CREATIONDATE + LEAD_ACTID).
                    if (data && data.latest_status) {
                        setProgression(getDisplayProgressionStatus(data.latest_status, data.latest_non_failed_status));
                    }

                    // If opened from the edit icon, auto-select only a real NEXT status for editing.
                    if (openStartNext) {
                        var nextStatusIdx = getDefaultSelectedStatusIdx(currentStatusIdx);
                        if (nextStatusIdx > currentStatusIdx) {
                            // Switch to edit mode and re-run progression so the NEXT step becomes active.
                            // (setProgression chooses next step when viewMode=false)
                            viewMode = false;
                            if (data && data.latest_status) {
                                setProgression(getDisplayProgressionStatus(data.latest_status, data.latest_non_failed_status));
                            }
                            setFieldsReadOnly(false);
                            setRemarkPlaceholder(getSelectedStatusName());
                            setDateTimeLabels(getSelectedStatusName());

                            var remarkEl = document.getElementById('inquiryRemark');
                            var dateEl = document.getElementById('inquiryFollowupDate');
                            var timeEl = document.getElementById('inquiryFollowupTime');
                            if (remarkEl) remarkEl.value = '';
                            if (dateEl) dateEl.value = getDefaultDate();
                            if (timeEl) timeEl.value = getDefaultTime();
                        }

                        // One-shot behavior per open.
                        openStartNext = false;
                    }
                }

                latestMinDate = '';
                latestMinTime = '';
                if (data && data.latest_created_at) {
                    var dmin = new Date(data.latest_created_at);
                    if (!isNaN(dmin.getTime())) {
                        latestMinDate = dmin.toISOString().slice(0, 10);
                        latestMinTime = String(dmin.getHours()).padStart(2, '0') + ':' + String(dmin.getMinutes()).padStart(2, '0');
                    }
                }

                // In view mode, always populate the selected (submitted) status from DB.
                // Submitted statuses are view-only (read-only fields).
                if (modal && modal.classList.contains('inquiry-modal-open') && viewMode && selectedStatusIdx <= currentStatusIdx) {
                    var cur = statusOrder[selectedStatusIdx] || 'PENDING';
                    var act = findActivityForStatus(cur);
                    populateFormFromActivity(act);
                    setFieldsReadOnly(true);
                    setDateTimeLabels(cur);
                } else if (modal && modal.classList.contains('inquiry-modal-open') && viewMode && isFailedSelected()) {
                    populateFormFromActivity(findLatestFailedActivity());
                    setFieldsReadOnly(true);
                    setDateTimeLabels('FAILED');
                }

                var details = data.last_reward_details;
                if (details && currentStatusIdx === statusOrder.length - 1) {
                    var dateEl = document.getElementById('inquiryFollowupDate');
                    var timeEl = document.getElementById('inquiryFollowupTime');
                    var remarkEl = document.getElementById('inquiryRemark');
                    if (dateEl && details.date) dateEl.value = details.date;
                    if (timeEl && details.time) timeEl.value = details.time;
                    if (remarkEl && details.description) remarkEl.value = details.description;
                    setFieldsReadOnly(true);
                }
            })
            .catch(function() {
                latestStatusRaw = '';
                latestNonFailedStatusRaw = '';
                renderLatestFailedNotice(null);
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

        // If we are in a specific status tab (not 'inquiries' or 'all'), and the status changed, 
        // the row should be removed because it no longer belongs here.
        var currentTab = '{{ $dealerConsoleTab ?? 'inquiries' }}';
        var activeStatuses = ['PENDING', 'FOLLOWUP', 'FOLLOW UP', 'DEMO', 'CONFIRMED'];
        
        var shouldRemove = false;
        if (currentTab === 'inquiries') {
            if (activeStatuses.indexOf(rawUpper) === -1) shouldRemove = true;
        } else if (currentTab === 'pending-payouts') {
            // Payouts handles its own logic
        } else {
            var tabStatuses = [];
            switch (currentTab) {
                case 'pending': tabStatuses = ['PENDING']; break;
                case 'followup': tabStatuses = ['FOLLOWUP', 'FOLLOW UP']; break;
                case 'demo': tabStatuses = ['DEMO']; break;
                case 'confirmed': tabStatuses = ['CONFIRMED']; break;
                case 'completed': tabStatuses = ['COMPLETED', 'CASE COMPLETED']; break;
                case 'failed': tabStatuses = ['FAILED']; break;
                case 'cancelled': tabStatuses = ['CANCELLED']; break;
                case 'rewarded': tabStatuses = ['REWARDED', 'REWARD', 'REWARD DISTRIBUTED']; break;
            }
            if (tabStatuses.indexOf(rawUpper) === -1) shouldRemove = true;
        }

        if (shouldRemove) {
            row.style.transition = 'opacity 0.3s ease';
            row.style.opacity = '0';
            setTimeout(function() {
                row.remove();
                if (typeof window.dealerApplyPagination === 'function') {
                    window.dealerApplyPagination();
                }
            }, 300);
        }

        // Optionally show saved data (date/time/remark) as a tooltip on the status badge
        if (meta && (meta.date || meta.time || meta.remark)) {
            var parts = [];
            if (meta.date) parts.push('Date: ' + meta.date);
            if (meta.time) parts.push('Time: ' + meta.time);
            if (meta.remark) parts.push('Remark: ' + meta.remark);
            statusCell.title = parts.join(' | ');
        }
    }

    function refreshDealerConsoleCounts(counts) {
        if (!counts) return;
        var tabsNav = document.querySelector('.dealer-console-tabs');
        if (!tabsNav) return;

        var tabMap = {
            'inquiries': counts.inquiries,
            'pending': counts.pending,
            'followup': counts.followup,
            'demo': counts.demo,
            'confirmed': counts.confirmed,
            'completed': counts.completed,
            'failed': counts.failed,
            'cancelled': counts.cancelled,
            'rewarded': counts.rewarded,
            'pending-payouts': counts.pending_payouts
        };

        for (var tabId in tabMap) {
            var count = tabMap[tabId];
            var tabLink = tabsNav.querySelector('a[href*="tab=' + tabId + '"]');
            if (tabId === 'pending-payouts') {
                tabLink = tabsNav.querySelector('a[href*="/payouts"]');
            } else if (tabId === 'inquiries') {
                tabLink = tabsNav.querySelector('a[href$="/inquiries"]') || tabsNav.querySelector('a[href*="tab=inquiries"]');
            }

            if (tabLink) {
                var countEl = tabLink.querySelector('.inquiries-tab-count');
                if (countEl) {
                    countEl.textContent = new Intl.NumberFormat().format(count);
                    countEl.hidden = (count === 0);
                    if (count === 0) countEl.setAttribute('aria-hidden', 'true');
                    else countEl.removeAttribute('aria-hidden');
                }
            }
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
            var maxSizeBytes = 5 * 1024 * 1024; // 5MB
            for (var i = 0; i < files.length; i++) {
                var file = files[i];
                if (file.type && file.type.indexOf('image/') === 0) {
                    if (file.size > maxSizeBytes) {
                        showDealerInquiryToast('File "' + file.name + '" exceeds the 5MB limit.');
                        continue;
                    }
                    attachmentFiles.push(file);
                } else {
                    showDealerInquiryToast('File "' + file.name + '" is not a valid image.');
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

    function hasInvalidFollowupDateInput() {
        var dateEl = document.getElementById('inquiryFollowupDate');
        return !!(dateEl && dateEl.validity && dateEl.validity.badInput);
    }

    function getFollowupDateIso() {
        var dateEl = document.getElementById('inquiryFollowupDate');
        var raw = dateEl ? String(dateEl.value || '').trim() : '';
        if (!raw) return '';
        if (/^\d{4}-\d{2}-\d{2}$/.test(raw)) return raw;
        return '';
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
        return getTodayIsoDate();
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
        cachedActivities = [];
        latestStatusRaw = normalizeStatus(status);
        latestNonFailedStatusRaw = '';
        renderLatestFailedNotice(null);
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
         if (dateEl) {
             dateEl.value = '';
             var maxD = new Date();
             maxD.setDate(maxD.getDate() + 3);
             var dateD = maxD.toISOString().split('T')[0];
             dateEl.setAttribute('max', dateD);
         }
         if (timeEl) timeEl.value = '';
        var productBoxes = document.querySelectorAll('.inquiry-product-checkbox');
        if (productBoxes.length) productBoxes.forEach(function(b) { b.checked = false; });
        clearAttachmentPreviews();
        setFieldsReadOnly(true);
        setDateTimeLabels(getSelectedStatusName());
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
            if (step.dataset.step === 'FAILED' && isFailedLatestStatus()) {
                selectedStatusIdx = -1;
                viewMode = true;
                populateFormFromActivity(findLatestFailedActivity());
                setFieldsReadOnly(true);
                setDateTimeLabels('FAILED');
                refreshProgressionState();
                toggleAddCalendarButton();
                toggleProductChecklist();
                toggleUpdateButton();
                return;
            }
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
                setRemarkPlaceholder(statusOrder[stepIdx]);
                setDateTimeLabels(statusOrder[stepIdx]);
            }
            refreshProgressionState();
            toggleAddCalendarButton();
            toggleProductChecklist();
            toggleUpdateButton();
        });
    }

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
            // after the table body is re-rendered.
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
            currentUpdateButtonEl = null;
            openModal(
                viewBtnEl.dataset.leadId,
                viewBtnEl.dataset.customer,
                viewBtnEl.dataset.status || 'FAILED',
                false,
                viewBtnEl.dataset.referralCode
            );
        }
    });
    [closeBtn, cancelBtn].forEach(function(btn) {
        if (btn) btn.addEventListener('click', closeModal);
    });

    modal.addEventListener('click', function(e) {
        if (e.target === modal) closeModal();
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            if (modal.classList.contains('inquiry-modal-open')) closeModal();
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
            if (dateEl && (hasInvalidFollowupDateInput() || ((dateEl.value || '').trim() !== '' && dateStr === ''))) {
                showDealerInquiryToast('Please choose a valid date.');
                dateEl.focus();
                return;
            }
            if (dateStr !== '' && latestMinDate && dateStr < latestMinDate) {
                showDealerInquiryToast('Date cannot before previous activity');
                dateEl.focus();
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
        followupDateEl.setCustomValidity('');
        toggleAddCalendarButton();
    });
    if (followupTimeEl) followupTimeEl.addEventListener('input', toggleAddCalendarButton);
    if (followupDateEl) followupDateEl.addEventListener('blur', function() {
        followupDateEl.setCustomValidity('');
    });
    if (followupTimeEl) followupTimeEl.addEventListener('input', function() {
        followupTimeEl.setCustomValidity('');
    });
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
        if (this.disabled) return;
        if (selectedStatusIdx <= currentStatusIdx) {
            var currentStatus = statusOrder[currentStatusIdx] || 'PENDING';
            showDealerInquiryToast('Status is already ' + formatStatusLabel(currentStatus) + '. Please choose the next available status.');
            return;
        }
        var toStatus = getSelectedStatusName();
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
        if (toStatus === 'COMPLETED' && attachmentFiles.length === 0) {
            showDealerInquiryToast('Please upload your own company\'s invoice for COMPLETED status.');
            if (attachmentInput) attachmentInput.focus();
            return;
        }
        if (toStatus === 'REWARDED' && attachmentFiles.length === 0) {
            showDealerInquiryToast('Please upload the Referral Payout Proof.');
            if (attachmentInput) attachmentInput.focus();
            return;
        }
        var leadId = currentLeadId;
        var remarkEl = document.getElementById('inquiryRemark');
        var remark = remarkEl ? remarkEl.value.trim() : '';
        var validation = validateFollowupDateTime();
        if (!validation.valid) {
            showDealerInquiryToast(validation.message);
            if (validation.field) {
                validation.field.focus();
            }
            return;
        }
        var activityDate = validation.activityDate;
        var activityTime = validation.activityTime;
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
        .then(function(r) {
            return r.text().then(function(text) {
                var data;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    console.error('Non-JSON response:', text);
                    return { ok: r.ok, data: { message: 'Server error: ' + r.status + ' ' + r.statusText } };
                }
                return { ok: r.ok, data: data };
            });
        })
        .then(function(res) {
            updateBtn.disabled = false;
            if (res.ok && res.data && res.data.success) {
                // Refresh only the Update Inquiry Status form/modal state instead of reloading the whole page
                var leadIdNow = currentLeadId;
                var newStatus = toStatus;

                userPickedStep = false;
                openStartNext = false;
                syncModalAfterSuccessfulStatusSave(newStatus);

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
                if (res.data.counts) {
                    refreshDealerConsoleCounts(res.data.counts);
                }
                showDealerInquiryToast(res.data.message || 'Status updated successfully');
            } else {
                var errMsg = (res.data && res.data.message) ? res.data.message : 'Update failed';
                showDealerInquiryToast(errMsg);
            }
        })
        .catch(function(err) {
            console.error('Update request error:', err);
            updateBtn.disabled = false;
            showDealerInquiryToast('Update failed. Please try again.');
        });
    });
})();
</script>
@endpush
@endsection
