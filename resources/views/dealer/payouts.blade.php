@extends('layouts.app')
@section('title', 'Pending Payouts - SQL LMS Dealer Console')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/dealer-payouts.css') }}?v=20260401-17">
@endpush
@section('content')
@php
    $productNames = [
        1 => 'SQL Account', 2 => 'SQL Payroll', 3 => 'SQL Production', 4 => 'Mobile Sales',
        5 => 'SQL Ecommerce', 6 => 'SQL EBI Wellness POS', 7 => 'SQL X Suduai', 8 => 'SQL X-Store',
        9 => 'SQL Vision', 10 => 'SQL HRMS', 11 => 'Others',
    ];
    $statusFilterOptions = ['Followup', 'Demo', 'Confirmed', 'Completed', 'Rewarded', 'Failed'];
    $completedPerPage = 10;
    $completedTotal = count($completed ?? []);
    $completedLastPage = max(1, (int) ceil($completedTotal / $completedPerPage));
    $completedTo = min($completedTotal, $completedPerPage);
@endphp
<div class="dashboard-content inquiries-page-wrap">
@include('dealer.partials.console-inquiries-tabs', ['dealerConsoleTab' => $dealerConsoleTab ?? 'pending-payouts'])
<div id="completedPanel">
<section class="inquiries-mgmt-panel dealer-payouts-panel">
    <div class="inquiries-panel-header">
        <div class="inquiries-panel-title-wrap">
            <i class="bi bi-piggy-bank inquiries-panel-icon"></i>
            <h2 class="inquiries-panel-title">Pending Payouts</h2>
        </div>
        <div class="inquiries-panel-actions">
            <div class="inquiries-columns-dropdown">
                <button type="button" class="inquiries-btn inquiries-btn-secondary" id="completedColumnsBtn" aria-haspopup="true" aria-expanded="false">Columns</button>
                <div class="inquiries-columns-menu" id="completedColumnsMenu" hidden>
                    <div class="inquiries-columns-menu-title">Show columns</div>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="inquiryid"> INQUIRY ID</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="date"> INQUIRY DATE</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="completeddate"> COMPLETION DATE</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="customer"> CUSTOMER NAME</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="assignedto"> ASSIGNED TO</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="assigndate"> ASSIGN DATE</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="source"> SOURCE</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="postcode"> POSTCODE</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="city"> CITY</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="address"> ADDRESS</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="contactno"> CONTACT NO</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="businessnature"> BUSINESS NATURE</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="users"> USERS</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="existingsw"> EXISTING SW</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="demomode"> DEMO MODE</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="dealtproducts"> DEALT PRODUCTS</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="message"> MESSAGE</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="referralcode"> REFERRAL CODE</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="attachment"> ATTACHMENT</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="assignby"> ASSIGN BY</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="status"> STATUS</label>
                    <div class="inquiries-columns-actions">
                        <button type="button" class="inquiries-columns-action-btn" id="completedColumnsAll">All</button>
                        <button type="button" class="inquiries-columns-action-btn" id="completedColumnsNone">None</button>
                    </div>
                    <button type="button" class="inquiries-columns-reset" id="completedColumnsReset">Reset to default</button>
                </div>
            </div>
        </div>
    </div>
    <div class="inquiries-table-wrap">
        <div class="inquiries-table-scroll">
            <table class="inquiries-table" id="completedTable">
                <thead>
                    <tr class="inquiries-header-row">
                        <x-tables.text-filter-header col="inquiryid" label="INQUIRY ID" input-class="inquiries-grid-filter payouts-grid-filter" table="completed" />
                        <x-tables.text-filter-header col="date" label="INQUIRY DATE" input-class="inquiries-grid-filter payouts-grid-filter" table="completed" />
                        <x-tables.text-filter-header col="customer" label="CUSTOMER NAME" input-class="inquiries-grid-filter payouts-grid-filter" table="completed" />
                        <x-tables.text-filter-header col="assignedto" label="ASSIGNED TO" input-class="inquiries-grid-filter payouts-grid-filter" table="completed" />
                        <x-tables.text-filter-header col="assigndate" label="ASSIGN DATE" input-class="inquiries-grid-filter payouts-grid-filter" table="completed" />
                        <x-tables.text-filter-header col="referralcode" label="REFERRAL CODE" input-class="inquiries-grid-filter payouts-grid-filter" table="completed" placeholder="Has code" />
                        <x-tables.text-filter-header col="completeddate" label="COMPLETION DATE" input-class="inquiries-grid-filter payouts-grid-filter" table="completed" />
                        <x-tables.text-filter-header col="status" label="STATUS" input-class="inquiries-grid-filter payouts-grid-filter" table="completed" placeholder="Completed only" :icon="false" :disabled="true" :readonly="true" />
                        <x-tables.text-filter-header col="dealtproducts" label="DEALT PRODUCTS" input-class="inquiries-grid-filter payouts-grid-filter" table="completed" />
                        <x-tables.text-filter-header col="source" label="SOURCE" input-class="inquiries-grid-filter payouts-grid-filter" table="completed" />
                        <x-tables.text-filter-header col="postcode" label="POSTCODE" input-class="inquiries-grid-filter payouts-grid-filter" table="completed" />
                        <x-tables.text-filter-header col="city" label="CITY" input-class="inquiries-grid-filter payouts-grid-filter" table="completed" />
                        <x-tables.text-filter-header col="address" label="ADDRESS" input-class="inquiries-grid-filter payouts-grid-filter" table="completed" />
                        <x-tables.text-filter-header col="contactno" label="CONTACT NO" input-class="inquiries-grid-filter payouts-grid-filter" table="completed" />
                        <x-tables.text-filter-header col="businessnature" label="BUSINESS NATURE" input-class="inquiries-grid-filter payouts-grid-filter" table="completed" />
                        <x-tables.text-filter-header col="users" label="USERS" input-class="inquiries-grid-filter payouts-grid-filter" table="completed" />
                        <x-tables.text-filter-header col="existingsw" label="EXISTING SW" input-class="inquiries-grid-filter payouts-grid-filter" table="completed" />
                        <x-tables.text-filter-header col="demomode" label="DEMO MODE" input-class="inquiries-grid-filter payouts-grid-filter" table="completed" />
                        <x-tables.text-filter-header col="message" label="MESSAGE" input-class="inquiries-grid-filter payouts-grid-filter" table="completed" />
                        <x-tables.text-filter-header col="attachment" label="ATTACHMENT" input-class="inquiries-grid-filter payouts-grid-filter" table="completed" />
                        <x-tables.text-filter-header col="assignby" label="ASSIGN BY" input-class="inquiries-grid-filter payouts-grid-filter" table="completed" />
                        <x-tables.clear-filter-header button-id="completedClearFilters" />
                    </tr>
                </thead>
                <tbody>
                    @include('dealer.partials.payouts_completed_rows', ['completed' => $completed, 'productLabels' => $productLabels, 'productNames' => $productNames])
                    @php
                        $completedVisibleRows = $completedTotal;
                        $completedPlaceholderRows = $completedVisibleRows > 0
                            ? max(0, $completedPerPage - $completedVisibleRows)
                            : 0;
                    @endphp
                    @for ($i = 0; $i < $completedPlaceholderRows; $i++)
                        <tr class="inquiries-placeholder-row" aria-hidden="true">
                            <td class="inquiries-placeholder-cell" colspan="22" style="height: 57px;"></td>
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>
        <div class="inquiries-assigned-pagination"
             id="completedPagination"
             data-total="{{ $completedTotal }}"
             data-per-page="{{ $completedPerPage }}"
             data-current-page="1"
             data-last-page="{{ $completedLastPage }}">
            <span class="inquiries-assigned-pagination-info" id="completedPaginationInfo">Showing {{ $completedTotal === 0 ? 0 : 1 }} to {{ $completedTo }} of {{ $completedTotal }} entries (Page 1)</span>
            <div class="inquiries-assigned-pagination-nav">
                <button type="button" class="inquiries-btn inquiries-btn-secondary inquiries-pagination-btn" id="completedPaginationFirst" data-page="first" aria-label="First page" @disabled($completedLastPage <= 1)>First</button>
                <button type="button" class="inquiries-btn inquiries-btn-secondary inquiries-pagination-btn" id="completedPaginationPrev" data-page="prev" aria-label="Previous page" @disabled($completedLastPage <= 1)>Previous</button>
                <span class="inquiries-assigned-page-numbers" id="completedPageNumbers">
                    <button type="button" class="inquiries-pagination-num inquiries-pagination-num-active" data-page="1" aria-label="Page 1" aria-current="page">1</button>
                </span>
                <button type="button" class="inquiries-btn inquiries-btn-secondary inquiries-pagination-btn" id="completedPaginationNext" data-page="next" aria-label="Next page" @disabled($completedLastPage <= 1)>Next</button>
                <button type="button" class="inquiries-btn inquiries-btn-secondary inquiries-pagination-btn" id="completedPaginationLast" data-page="last" aria-label="Last page" @disabled($completedLastPage <= 1)>Last</button>
            </div>
        </div>
    </div>
</section>
</div>
</div>

{{-- Update Inquiry Status Modal (same as dealer inquiries) --}}
<div class="inquiry-modal-overlay" id="inquiryUpdateModal" aria-hidden="true">
    <div class="inquiry-modal" role="dialog" aria-labelledby="inquiryModalTitle">
        <div class="inquiry-modal-header">
            <h2 id="inquiryModalTitle" class="inquiry-modal-title">Update Inquiry Status</h2>
            <p class="inquiry-modal-subtitle" id="inquiryModalSubtitle">Inquiry ID: #SQL-0 &mdash; &mdash;</p>
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
                    <label class="inquiry-field inquiry-field-products" id="inquiryProductsField" style="display:none;">
                        <span class="inquiry-field-label">PRODUCTS <span class="inquiry-field-required">*</span></span>
                        <div class="inquiry-products-checklist" id="inquiryProductsChecklist">
                            @foreach($productNames as $id => $name)
                                <label class="inquiry-product-check">
                                    <input type="checkbox" class="inquiry-product-checkbox" name="inquiry_products[]" value="{{ $id }}" data-name="{{ $name }}">
                                    <span>{{ $name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </label>
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
                            <strong>System</strong> created inquiry <span class="inquiry-activity-link" id="inquiryActivityLink">#SQL-0</span> <span class="inquiry-activity-time">&mdash;</span>
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
@endsection
@push('scripts')
<script>
function initDealerPendingPayoutsPage() {
    var COMPLETED_STORAGE_KEY = 'dealerPayoutCompletedVisibleColumns_v4';
    var COMPLETED_LEGACY_STORAGE_KEY = 'dealerPayoutCompletedVisibleColumns_v3';
    var COMPLETED_OLDER_LEGACY_STORAGE_KEY = 'dealerPayoutCompletedVisibleColumns_v2';
    // Default + all columns: match dealer inquiries table column IDs
    var COMPLETED_LEGACY_DEFAULT_COLUMNS = ['inquiryid','completeddate','customer','dealtproducts','referralcode','assignby','status'];
    var COMPLETED_OLDER_LEGACY_DEFAULT_COLUMNS = ['inquiryid','completeddate','customer','dealtproducts','referralcode','attachment','assignby','status'];
    var COMPLETED_PREVIOUS_DEFAULT_COLUMNS = ['inquiryid','customer','assignedto','referralcode','completeddate','status','dealtproducts'];
    var COMPLETED_DEFAULT_COLUMNS = ['inquiryid','customer','assignedto','referralcode','completeddate','status','dealtproducts'];
    var COMPLETED_ALL_COLUMNS = ['inquiryid','date','customer','assignedto','assigndate','referralcode','completeddate','status','dealtproducts','source','postcode','city','address','contactno','businessnature','users','existingsw','demomode','message','attachment','assignby'];

    var REWARDED_STORAGE_KEY = 'dealerPayoutRewardedVisibleColumns_v2';
    var REWARDED_DEFAULT_COLUMNS = ['inquiryid','payoutdate','customer','dealtproducts','referralcode','attachment','assignby','status'];
    var REWARDED_ALL_COLUMNS = ['inquiryid','date','payoutdate','completiondate','assigndate','customer','source','postcode','city','address','contactno','businessnature','users','existingsw','demomode','dealtproducts','message','referralcode','attachment','assignby','status'];

    function getCompletedVisibleColumns() {
        try {
            var raw = localStorage.getItem(COMPLETED_STORAGE_KEY);
            if (raw === null) raw = localStorage.getItem(COMPLETED_LEGACY_STORAGE_KEY);
            if (raw === null) raw = localStorage.getItem(COMPLETED_OLDER_LEGACY_STORAGE_KEY);
            if (raw !== null) {
                var arr = JSON.parse(raw);
                if (Array.isArray(arr)) {
                    var isLegacyDefault =
                        (arr.length === COMPLETED_LEGACY_DEFAULT_COLUMNS.length && arr.every(function(col, index) {
                            return col === COMPLETED_LEGACY_DEFAULT_COLUMNS[index];
                        })) ||
                        (arr.length === COMPLETED_OLDER_LEGACY_DEFAULT_COLUMNS.length && arr.every(function(col, index) {
                            return col === COMPLETED_OLDER_LEGACY_DEFAULT_COLUMNS[index];
                        })) ||
                        (arr.length === COMPLETED_PREVIOUS_DEFAULT_COLUMNS.length && arr.every(function(col, index) {
                            return col === COMPLETED_PREVIOUS_DEFAULT_COLUMNS[index];
                        }));
                    var migrated = isLegacyDefault ? COMPLETED_DEFAULT_COLUMNS.slice() : arr.filter(function(col) {
                        return COMPLETED_ALL_COLUMNS.indexOf(col) !== -1;
                    });
                    try {
                        localStorage.setItem(COMPLETED_STORAGE_KEY, JSON.stringify(migrated));
                        localStorage.removeItem(COMPLETED_LEGACY_STORAGE_KEY);
                        localStorage.removeItem(COMPLETED_OLDER_LEGACY_STORAGE_KEY);
                    } catch (e) {}
                    return migrated;
                }
            }
        } catch (e) {}
        return COMPLETED_DEFAULT_COLUMNS.slice();
    }
    function setCompletedVisibleColumns(cols) {
        try { localStorage.setItem(COMPLETED_STORAGE_KEY, JSON.stringify(cols)); } catch (e) {}
    }
    function updateCompletedScrollMode(visible) {
        var table = document.getElementById('completedTable');
        var scroller = document.querySelector('#completedPanel .inquiries-table-scroll');
        if (!table || !scroller) return;
        var hasExtras = (visible || []).some(function(col) {
            return COMPLETED_DEFAULT_COLUMNS.indexOf(col) === -1;
        });
        table.classList.toggle('payouts-default-layout', !hasExtras);
        scroller.classList.toggle('payouts-default-layout', !hasExtras);
    }
    function applyCompletedColumns(visible) {
        var table = document.getElementById('completedTable');
        if (!table) return;
        COMPLETED_ALL_COLUMNS.forEach(function(col) {
            var show = visible.indexOf(col) !== -1;
            table.querySelectorAll('th[data-col="' + col + '"], td[data-col="' + col + '"]').forEach(function(el) {
                el.style.display = show ? '' : 'none';
            });
        });
        var showAction = Array.isArray(visible) && visible.length > 0;
        table.querySelectorAll('th.inquiries-col-action, td.inquiries-col-action').forEach(function(el) {
            el.style.display = showAction ? '' : 'none';
        });
        updateCompletedScrollMode(visible);
        if (typeof applyCompletedPagination === 'function') {
            applyCompletedPagination();
        }
    }
    function syncCompletedCheckboxes(visible) {
        var menu = document.getElementById('completedColumnsMenu');
        if (!menu) return;
        menu.querySelectorAll('input[data-col]').forEach(function(cb) {
            var col = cb.getAttribute('data-col');
            cb.checked = visible.indexOf(col) !== -1;
        });
    }
    function refreshCompletedColumnState() {
        var visible = getCompletedVisibleColumns();
        syncCompletedCheckboxes(visible);
        applyCompletedColumns(visible);
    }

    refreshCompletedColumnState();

    var completedColBtn = document.getElementById('completedColumnsBtn');
    var completedColMenu = document.getElementById('completedColumnsMenu');
    if (completedColBtn && completedColMenu) {
        completedColBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            var isOpen = !completedColMenu.hidden;
            completedColMenu.hidden = isOpen;
            completedColBtn.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
            if (!isOpen) {
                completedColMenu.scrollTop = 0;
                refreshCompletedColumnState();
            }
        });
        document.addEventListener('click', function() {
            completedColMenu.hidden = true;
            completedColBtn.setAttribute('aria-expanded', 'false');
        });
        completedColMenu.addEventListener('click', function(e) { e.stopPropagation(); });
    }
    if (completedColMenu) {
        completedColMenu.querySelectorAll('input[data-col]').forEach(function(cb) {
            cb.addEventListener('change', function() {
                var visible = [];
                completedColMenu.querySelectorAll('input[data-col]:checked').forEach(function(c) {
                    visible.push(c.getAttribute('data-col'));
                });
                setCompletedVisibleColumns(visible);
                applyCompletedColumns(visible);
            });
        });
    }
    var completedReset = document.getElementById('completedColumnsReset');
    if (completedReset) {
        completedReset.addEventListener('click', function() {
            setCompletedVisibleColumns(COMPLETED_DEFAULT_COLUMNS.slice());
            refreshCompletedColumnState();
            var wrap = document.querySelector('#completedPanel .inquiries-table-scroll');
            if (wrap) wrap.scrollLeft = 0;
        });
    }
    var completedAll = document.getElementById('completedColumnsAll');
    if (completedAll) {
        completedAll.addEventListener('click', function() {
            setCompletedVisibleColumns(COMPLETED_ALL_COLUMNS.slice());
            refreshCompletedColumnState();
        });
    }
    var completedNone = document.getElementById('completedColumnsNone');
    if (completedNone) {
        completedNone.addEventListener('click', function() {
            setCompletedVisibleColumns([]);
            refreshCompletedColumnState();
        });
    }

    function getRewardedVisibleColumns() {
        try {
            var raw = localStorage.getItem(REWARDED_STORAGE_KEY);
            if (raw !== null) {
                var arr = JSON.parse(raw);
                if (Array.isArray(arr)) return arr;
            }
        } catch (e) {}
        return REWARDED_DEFAULT_COLUMNS.slice();
    }
    function setRewardedVisibleColumns(cols) {
        try { localStorage.setItem(REWARDED_STORAGE_KEY, JSON.stringify(cols)); } catch (e) {}
    }
    function updateRewardedScrollMode(visible) {
        var table = document.getElementById('rewardedTable');
        var scroller = document.querySelector('#rewardedPanel .inquiries-table-scroll');
        if (!table || !scroller) return;
        var hasExtras = (visible || []).some(function(col) {
            return REWARDED_DEFAULT_COLUMNS.indexOf(col) === -1;
        });
        table.classList.toggle('payouts-default-layout', !hasExtras);
        scroller.classList.toggle('payouts-default-layout', !hasExtras);
    }
    function applyRewardedColumns(visible) {
        var table = document.getElementById('rewardedTable');
        if (!table) return;
        REWARDED_ALL_COLUMNS.forEach(function(col) {
            var show = visible.indexOf(col) !== -1;
            table.querySelectorAll('th[data-col="' + col + '"], td[data-col="' + col + '"]').forEach(function(el) {
                el.style.display = show ? '' : 'none';
            });
        });
        updateRewardedScrollMode(visible);
    }
    function syncRewardedCheckboxes(visible) {
        var menu = document.getElementById('rewardedColumnsMenu');
        if (!menu) return;
        menu.querySelectorAll('input[data-col]').forEach(function(cb) {
            var col = cb.getAttribute('data-col');
            cb.checked = visible.indexOf(col) !== -1;
        });
    }
    function refreshRewardedColumnState() {
        var visible = getRewardedVisibleColumns();
        syncRewardedCheckboxes(visible);
        applyRewardedColumns(visible);
    }

    var COMPLETED_PER_PAGE = 10;
    var REWARDED_PER_PAGE = 10;

    function getFilteredPayoutRows(table) {
        if (!table) return [];
        var rows = table.querySelectorAll('tbody tr.payouts-row');
        var out = [];
        for (var i = 0; i < rows.length; i++) {
            var filterMatch = rows[i].getAttribute('data-filter-match');
            if (filterMatch === null || filterMatch === '1') out.push(rows[i]);
        }
        return out;
    }

    function clearPayoutPlaceholderRows(table) {
        if (!table) return;
        Array.prototype.slice.call(table.querySelectorAll('tbody tr.inquiries-placeholder-row')).forEach(function(row) {
            row.remove();
        });
    }

    var completedPayoutSort = { col: null, dir: 1 };

    function getCompletedPayoutSortValue(row, col) {
        var cell = row.querySelector('td[data-col="' + col + '"]');
        return (cell && cell.textContent) ? cell.textContent.trim().toLowerCase() : '';
    }

    function setCompletedPayoutInitialOrder() {
        var table = document.getElementById('completedTable');
        if (!table) return;
        table.querySelectorAll('tbody tr.payouts-row').forEach(function(row, index) {
            row.setAttribute('data-initial-index', String(index));
        });
    }

    function sortCompletedPayoutTable() {
        var table = document.getElementById('completedTable');
        if (!table || !completedPayoutSort.col) return;
        var tbody = table.querySelector('tbody');
        if (!tbody) return;
        clearPayoutPlaceholderRows(table);
        var rows = Array.prototype.slice.call(tbody.querySelectorAll('tr.payouts-row'));
        var emptyRow = Array.from(tbody.querySelectorAll('tr')).find(function(row) {
            return !row.classList.contains('payouts-row') && !!row.querySelector('.dealer-table-empty, .inquiries-empty, .inquiries-empty-cell');
        }) || null;

        rows.sort(function(a, b) {
            var va = getCompletedPayoutSortValue(a, completedPayoutSort.col);
            var vb = getCompletedPayoutSortValue(b, completedPayoutSort.col);
            var cmp = va.localeCompare(vb, undefined, { numeric: true });
            return completedPayoutSort.dir * cmp;
        });

        rows.forEach(function(row) { tbody.appendChild(row); });
        if (rows.length === 0 && emptyRow) tbody.appendChild(emptyRow);
        goToCompletedPage(1);
    }

    function clearCompletedPayoutSort() {
        var table = document.getElementById('completedTable');
        if (!table) return;
        var tbody = table.querySelector('tbody');
        if (!tbody) return;
        completedPayoutSort.col = null;
        completedPayoutSort.dir = 1;
        table.querySelectorAll('thead th[data-col]').forEach(function(header) {
            header.classList.remove('inquiries-sort-asc', 'inquiries-sort-desc');
        });
        clearPayoutPlaceholderRows(table);
        var rows = Array.prototype.slice.call(tbody.querySelectorAll('tr.payouts-row'));
        var emptyRow = Array.from(tbody.querySelectorAll('tr')).find(function(row) {
            return !row.classList.contains('payouts-row') && !!row.querySelector('.dealer-table-empty, .inquiries-empty, .inquiries-empty-cell');
        }) || null;

        rows.sort(function(a, b) {
            var ia = parseInt(a.getAttribute('data-initial-index') || '0', 10);
            var ib = parseInt(b.getAttribute('data-initial-index') || '0', 10);
            return ia - ib;
        });

        rows.forEach(function(row) { tbody.appendChild(row); });
        if (rows.length === 0 && emptyRow) tbody.appendChild(emptyRow);
        goToCompletedPage(1);
    }

    function initSortableCompletedPayouts() {
        var table = document.getElementById('completedTable');
        if (!table) return;
        table.querySelectorAll('thead th[data-col]').forEach(function(th) {
            th.classList.add('inquiries-sortable');
            th.style.cursor = 'pointer';
            th.addEventListener('click', function(e) {
                if (e.target.closest('input, select, button, .inquiries-filter-wrap')) return;
                var col = th.getAttribute('data-col');
                if (!col) return;
                completedPayoutSort.dir = (completedPayoutSort.col === col) ? -completedPayoutSort.dir : 1;
                completedPayoutSort.col = col;
                table.querySelectorAll('thead th[data-col]').forEach(function(header) {
                    header.classList.remove('inquiries-sort-asc', 'inquiries-sort-desc');
                    if (header.getAttribute('data-col') === col) {
                        header.classList.add(completedPayoutSort.dir === 1 ? 'inquiries-sort-asc' : 'inquiries-sort-desc');
                    }
                });
                sortCompletedPayoutTable();
            });
        });
    }

    function syncPayoutTableHeight(table, visibleDataCount, perPage) {
        if (!table) return;
        var tbody = table.querySelector('tbody');
        var scrollWrap = table.closest('.inquiries-table-scroll');
        clearPayoutPlaceholderRows(table);
        if (tbody) tbody.style.minHeight = '';
        if (!scrollWrap) return;
        var allRows = Array.prototype.slice.call(table.querySelectorAll('tbody tr.payouts-row'));
        var allowZeroFill = allRows.length > 0;
        var useShortHeight = (visibleDataCount > 0 && visibleDataCount < perPage) || (visibleDataCount === 0 && allowZeroFill);
        var targetRows = window.matchMedia('(max-width: 1366px), (max-height: 900px)').matches
            ? Math.min(perPage, 6)
            : perPage;

        if (tbody && visibleDataCount < targetRows && (visibleDataCount > 0 || allowZeroFill)) {
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

                row.appendChild(cell);
                tbody.appendChild(row);
            }
        }

        scrollWrap.classList.toggle('inquiries-table-scroll-empty', visibleDataCount === 0 && !allowZeroFill);
        scrollWrap.classList.toggle('inquiries-table-scroll-short', useShortHeight);
    }

    function applyCompletedPagination() {
        var table = document.getElementById('completedTable');
        var pagEl = document.getElementById('completedPagination');
        var infoEl = document.getElementById('completedPaginationInfo');
        var pageNumbersEl = document.getElementById('completedPageNumbers');
        var controls = pagEl ? pagEl.querySelectorAll('.inquiries-pagination-btn') : [];
        if (!table || !pagEl) return;
        var allRows = table.querySelectorAll('tbody tr.payouts-row');
        var filteredRows = getFilteredPayoutRows(table);
        var total = filteredRows.length;
        var perPage = parseInt(pagEl.getAttribute('data-per-page') || String(COMPLETED_PER_PAGE), 10);
        var lastPage = total > 0 ? Math.ceil(total / perPage) : 1;
        var current = parseInt(pagEl.getAttribute('data-current-page') || '1', 10);
        current = Math.max(1, Math.min(current, lastPage));
        pagEl.setAttribute('data-total', String(total));
        pagEl.setAttribute('data-last-page', String(lastPage));
        pagEl.setAttribute('data-current-page', String(current));
        var start = (current - 1) * perPage;
        var end = Math.min(start + perPage, total);
        var pageRows = filteredRows.slice(start, end);
        for (var i = 0; i < allRows.length; i++) {
            allRows[i].style.display = 'none';
        }
        for (var j = 0; j < pageRows.length; j++) {
            pageRows[j].style.display = '';
        }
        syncPayoutTableHeight(table, pageRows.length, perPage);
        var from = total === 0 ? 0 : start + 1;
        var to = end;
        if (infoEl) infoEl.textContent = 'Showing ' + from + ' to ' + to + ' of ' + total + ' entries (Page ' + current + ')';
        controls.forEach(function(btn) {
            var type = btn.getAttribute('data-page');
            if (type === 'first' || type === 'prev') {
                btn.disabled = current <= 1;
            } else if (type === 'next' || type === 'last') {
                btn.disabled = current >= lastPage;
            }
        });
        if (pageNumbersEl) {
            pageNumbersEl.innerHTML = '';
            for (var p = 1; p <= lastPage; p++) {
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'inquiries-pagination-num' + (p === current ? ' inquiries-pagination-num-active' : '');
                btn.setAttribute('data-page', String(p));
                btn.setAttribute('aria-label', 'Page ' + p);
                if (p === current) btn.setAttribute('aria-current', 'page');
                btn.textContent = String(p);
                btn.addEventListener('click', function() {
                    var page = parseInt(this.getAttribute('data-page') || '1', 10);
                    goToCompletedPage(page);
                });
                pageNumbersEl.appendChild(btn);
            }
        }
    }

    function goToCompletedPage(page) {
        var pagEl = document.getElementById('completedPagination');
        if (!pagEl) return;
        pagEl.setAttribute('data-current-page', String(parseInt(page || '1', 10) || 1));
        applyCompletedPagination();
    }

    function applyRewardedPagination() {
        var table = document.getElementById('rewardedTable');
        var pagEl = document.getElementById('rewardedPagination');
        var infoEl = document.getElementById('rewardedPaginationInfo');
        var firstBtn = document.getElementById('rewardedPaginationFirst');
        var prevBtn = document.getElementById('rewardedPaginationPrev');
        var nextBtn = document.getElementById('rewardedPaginationNext');
        var lastBtn = document.getElementById('rewardedPaginationLast');
        var pageNumbersEl = document.getElementById('rewardedPageNumbers');
        if (!table || !pagEl) return;
        var allRows = table.querySelectorAll('tbody tr.payouts-row');
        var filteredRows = getFilteredPayoutRows(table);
        var total = filteredRows.length;
        var perPage = REWARDED_PER_PAGE;
        var lastPage = total > 0 ? Math.ceil(total / perPage) : 1;
        var current = parseInt(pagEl.getAttribute('data-current-page') || '1', 10);
        current = Math.max(1, Math.min(current, lastPage));
        pagEl.setAttribute('data-current-page', String(current));
        var start = (current - 1) * perPage;
        var end = Math.min(start + perPage, total);
        var pageRows = filteredRows.slice(start, end);
        for (var i = 0; i < allRows.length; i++) {
            allRows[i].style.display = 'none';
        }
        for (var j = 0; j < pageRows.length; j++) {
            pageRows[j].style.display = '';
        }
        syncPayoutTableHeight(table, pageRows.length, perPage);
        var from = total === 0 ? 0 : start + 1;
        var to = end;
        if (infoEl) infoEl.textContent = 'Showing ' + from + ' to ' + to + ' of ' + total + ' entries (Page ' + current + ')';
        if (firstBtn) firstBtn.disabled = current <= 1;
        if (prevBtn) prevBtn.disabled = current <= 1;
        if (nextBtn) nextBtn.disabled = current >= lastPage;
        if (lastBtn) lastBtn.disabled = current >= lastPage;
        if (pageNumbersEl) {
            var html = '';
            for (var p = 1; p <= lastPage; p++) {
                var active = p === current;
                html += '<button type="button" class="inquiries-btn inquiries-btn-secondary inquiries-pagination-btn inquiries-page-num' + (active ? ' active' : '') + '" data-page="' + p + '" aria-label="Page ' + p + '"' + (active ? ' aria-current="page"' : '') + '>' + p + '</button>';
            }
            pageNumbersEl.innerHTML = html;
            pageNumbersEl.querySelectorAll('[data-page]').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var p = parseInt(btn.getAttribute('data-page'), 10);
                    pagEl.setAttribute('data-current-page', String(p));
                    applyRewardedPagination();
                });
            });
        }
    }

    function normalizeDealerPayoutStatus(value) {
        var normalized = String(value || '').toLowerCase().replace(/\s+/g, ' ').trim();
        if (normalized === '' || normalized === 'all') return '';
        if (normalized === 'follow up' || normalized === 'followup') return 'followup';
        if (normalized === 'confirmed' || normalized === 'case confirmed') return 'confirmed';
        if (normalized === 'completed' || normalized === 'case completed') return 'completed';
        if (normalized === 'rewarded' || normalized === 'reward distributed' || normalized === 'paid') return 'rewarded';
        return normalized;
    }

    function normalizeDealerPayoutSearch(value) {
        return String(value || '').toLowerCase().replace(/\s+/g, '');
    }

    function applyTableFilter(tableId) {
        var table = document.getElementById(tableId);
        if (!table) return;
        var filters = {};
        table.querySelectorAll('thead .payouts-grid-filter').forEach(function(inp) {
            var col = inp.getAttribute('data-col');
            var val = (inp.value || '').toLowerCase().trim();
            if (!col) return;
            if (col === 'status') {
                val = normalizeDealerPayoutStatus(val);
            }
            if (val) filters[col] = val;
        });
        table.querySelectorAll('tbody tr.payouts-row').forEach(function(row) {
            var colMatch = true;
            for (var col in filters) {
                var cell = row.querySelector('td[data-col="' + col + '"]');
                var cellText = (cell && cell.textContent) ? cell.textContent.toLowerCase().trim() : '';
                if (col === 'status') {
                    var normalizedStatusFilter = normalizeDealerPayoutStatus(filters[col]);
                    if (!normalizedStatusFilter) {
                        continue;
                    }
                    if (normalizeDealerPayoutStatus(cellText) !== normalizedStatusFilter) {
                        colMatch = false;
                        break;
                    }
                    continue;
                }
                if (cellText.indexOf(filters[col]) === -1) { colMatch = false; break; }
            }
            row.setAttribute('data-filter-match', colMatch ? '1' : '0');
        });
        if (tableId === 'completedTable') {
            var cp = document.getElementById('completedPagination');
            if (cp) cp.setAttribute('data-current-page', '1');
            applyCompletedPagination();
        } else if (tableId === 'rewardedTable') {
            var rp = document.getElementById('rewardedPagination');
            if (rp) rp.setAttribute('data-current-page', '1');
            applyRewardedPagination();
        }
    }
    function bindTable(tableId) {
        var table = document.getElementById(tableId);
        if (!table) return;
        table.querySelectorAll('thead .payouts-grid-filter').forEach(function(inp) {
            inp.addEventListener('input', function() { applyTableFilter(tableId); });
            inp.addEventListener('change', function() { applyTableFilter(tableId); });
        });
    }
    setCompletedPayoutInitialOrder();
    initSortableCompletedPayouts();
    bindTable('completedTable');

    applyCompletedPagination();

    var completedControls = document.querySelectorAll('#completedPagination .inquiries-pagination-btn');
    completedControls.forEach(function(btn) {
        btn.onclick = function() {
            var pagEl = document.getElementById('completedPagination');
            if (!pagEl) return;
            var type = btn.getAttribute('data-page');
            var current = parseInt(pagEl.getAttribute('data-current-page') || '1', 10);
            var currentLastPage = parseInt(pagEl.getAttribute('data-last-page') || '1', 10);
            if (type === 'first') goToCompletedPage(1);
            else if (type === 'prev') goToCompletedPage(current - 1);
            else if (type === 'next') goToCompletedPage(current + 1);
            else if (type === 'last') goToCompletedPage(currentLastPage);
        };
    });

    if (!window.__dealerPayoutPaginationResizeBound) {
        window.addEventListener('resize', function() {
            applyCompletedPagination();
            applyRewardedPagination();
        });
        window.__dealerPayoutPaginationResizeBound = true;
    }
    var completedClearFilters = document.getElementById('completedClearFilters');
    if (completedClearFilters) {
        completedClearFilters.addEventListener('click', function() {
            var table = document.getElementById('completedTable');
            if (table) table.querySelectorAll('thead .payouts-grid-filter').forEach(function(inp) { inp.value = ''; });
            applyTableFilter('completedTable');
            clearCompletedPayoutSort();
        });
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initDealerPendingPayoutsPage, { once: true });
} else {
    initDealerPendingPayoutsPage();
}
</script>
@endpush
@push('scripts')
<script>
(function() {
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
            var stepName = step.dataset.step;
            var isDone = i <= currentStatusIdx;
            if (isDone) {
                step.classList.add('inquiry-step--done', 'inquiry-step--viewable');
                if (i === selectedStatusIdx && viewMode) step.classList.add('inquiry-step--selected');
                step.innerHTML = '<i class="bi bi-check"></i><span>' + stepName + '</span>';
                return;
            }

            var canClick = canSelectFutureStatus(currentStatus, statusOrder[i]);
            step.innerHTML = '<span>' + stepName + '</span>';
            if (i === selectedStatusIdx && canClick) {
                step.classList.add('inquiry-step--active', 'inquiry-step--selected');
            } else if (canClick) {
                step.classList.add('inquiry-step--clickable');
            } else {
                step.classList.add('inquiry-step--no-click');
            }
        });
    }

    function setProgression(currentStatus) {
        var normalized = statusMap[currentStatus] || 'PENDING';
        var idx = statusOrder.indexOf(normalized);
        if (idx < 0) idx = 0;
        currentStatusIdx = idx;
        selectedStatusIdx = getDefaultSelectedStatusIdx(idx);
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
        if (btn) btn.style.display = statusOrder[selectedStatusIdx] === 'DEMO' ? '' : 'none';
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
        var selectedName = statusOrder[selectedStatusIdx] || '';
        var isBlockedFuture = selectedStatusIdx > currentStatusIdx && !canSelectFutureStatus(statusOrder[currentStatusIdx] || 'PENDING', selectedName);
        var disable = isRewarded || viewMode || selectedStatusIdx <= currentStatusIdx || isBlockedFuture;
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

    function populateFormFromActivity(activity) {
        var dateEl = document.getElementById('inquiryFollowupDate');
        var timeEl = document.getElementById('inquiryFollowupTime');
        var remarkEl = document.getElementById('inquiryRemark');
        if (!activity || !activity.created_at) {
            if (dateEl) dateEl.value = '';
            if (timeEl) timeEl.value = '';
            if (remarkEl) remarkEl.value = '';
            return;
        }
        var d = new Date(activity.created_at);
        if (dateEl) dateEl.value = isNaN(d.getTime()) ? '' : d.toISOString().slice(0, 10);
        if (timeEl) timeEl.value = isNaN(d.getTime()) ? '' : String(d.getHours()).padStart(2, '0') + ':' + String(d.getMinutes()).padStart(2, '0');
        if (remarkEl) remarkEl.value = activity.description || '';
    }

    function setFieldsReadOnly(readOnly) {
        var dateEl = document.getElementById('inquiryFollowupDate');
        var timeEl = document.getElementById('inquiryFollowupTime');
        var remarkEl = document.getElementById('inquiryRemark');
        var fileEl = document.getElementById('inquiryAttachment');
        var productBoxes = document.querySelectorAll('.inquiry-product-checkbox');
        if (dateEl) dateEl.readOnly = readOnly;
        if (timeEl) timeEl.readOnly = readOnly;
        if (remarkEl) remarkEl.readOnly = readOnly;
        if (fileEl) fileEl.disabled = readOnly;
        productBoxes.forEach(function(b) { b.disabled = readOnly; });
    }

    function formatActivityTime(isoStr, now) {
        if (!isoStr) return '&mdash;';

        var d = null;

        if (typeof isoStr === 'string') {
            // Prefer native parsing first so ISO8601 timezone offsets are respected.
            var nativeParsed = new Date(isoStr);
            if (!isNaN(nativeParsed.getTime())) {
                d = nativeParsed;
            } else {
                var m = isoStr.match(/^(\d{4})-(\d{2})-(\d{2})[ T](\d{2}):(\d{2})(?::(\d{2}))?/);
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
        } else {
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

    function loadActivity(leadId) {
        var url = '{{ route("dealer.inquiries.activity", ["leadId" => "__ID__"]) }}'.replace('__ID__', leadId);
        fetch(url, { headers: { 'Accept': 'application/json' } })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                cachedActivities = data.activities || [];
                renderActivity(cachedActivities);
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
                renderActivity([]);
            });
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

    function openModal(leadId, customer, status, referralCode) {
        currentLeadId = leadId;
        currentReferralCode = String(referralCode || '').trim();
        currentCustomer = customer || '&mdash;';
        viewMode = false;
        subtitle.textContent = 'Inquiry ID: #SQL-' + leadId + ' &mdash; ' + currentCustomer;
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
        setFieldsReadOnly(false);
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
                if (dateEl) dateEl.value = '';
                if (timeEl) timeEl.value = '';
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

    document.addEventListener('click', function(e) {
        var updateBtnEl = e.target.closest('.inquiries-update-btn');
        if (updateBtnEl) {
            e.preventDefault();
            openModal(updateBtnEl.dataset.leadId, updateBtnEl.dataset.customer, updateBtnEl.dataset.status, updateBtnEl.dataset.referralCode);
            return;
        }
    });

    [closeBtn, cancelBtn].forEach(function(btn) {
        if (btn) btn.addEventListener('click', closeModal);
    });

    modal.addEventListener('click', function(e) {
        if (e.target === modal) closeModal();
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal.classList.contains('inquiry-modal-open')) {
            closeModal();
        }
    });

    var addCalendarBtn = document.getElementById('inquiryModalAddCalendar');
    if (addCalendarBtn) {
        addCalendarBtn.addEventListener('click', function() {
            var dateEl = document.getElementById('inquiryFollowupDate');
            var timeEl = document.getElementById('inquiryFollowupTime');
            var remarkEl = document.getElementById('inquiryRemark');
            var dateStr = dateEl ? dateEl.value : '';
            var timeStr = timeEl ? timeEl.value : '';
            var remark = remarkEl ? remarkEl.value.trim() : '';
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
    function showDealerPayoutToast(message) {
        var id = 'dealer-payout-action-toast';
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
        if (this.disabled || selectedStatusIdx <= currentStatusIdx) return;
        var toStatus = statusOrder[selectedStatusIdx];
        var fromStatus = statusOrder[currentStatusIdx] || 'PENDING';
        if (!canSelectFutureStatus(fromStatus, toStatus)) {
            showDealerPayoutToast(getBlockedStatusMessage(fromStatus, toStatus));
            return;
        }
        if (toStatus === 'COMPLETED') {
            var checked = document.querySelectorAll('.inquiry-product-checkbox:checked');
            if (!checked.length) {
                showDealerPayoutToast('Please select at least one product for COMPLETED status.');
                return;
            }
        }
        var leadId = currentLeadId;
        var remarkEl = document.getElementById('inquiryRemark');
        var remark = remarkEl ? remarkEl.value.trim() : '';
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
            formData.append('products', JSON.stringify(products));
            attachmentFiles.forEach(function(file) {
                formData.append('attachments[]', file);
            });
            body = formData;
        } else {
            headers['Content-Type'] = 'application/json';
            body = JSON.stringify({ lead_id: leadId, status: toStatus, remark: remark, products: products });
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
                closeModal();
                window.location.reload();
            } else {
                showDealerPayoutToast(res.data.message || 'Update failed');
            }
        })
        .catch(function() {
            updateBtn.disabled = false;
            showDealerPayoutToast('Update failed. Please try again.');
        });
    });
})();
</script>
@endpush
