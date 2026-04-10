@extends('layouts.app')
@section('title', 'History - Admin')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/admin-history.css') }}?v=20260409-02">
@endpush
@section('content')
<section class="dashboard-panel dashboard-table-panel">
    <div class="dashboard-panel-body">
        <div class="history-toolbar">
            <form method="GET" action="{{ route('admin.history') }}" class="history-date-filter-form" id="historyDateFilterForm">
                <label class="history-date-range-field" for="historyDateRange">
                    <span>Date Range:</span>
                    <select name="date_range" id="historyDateRange" class="history-date-range-select">
                        <option value="today" @selected($dateRange === 'today')>Today</option>
                        <option value="yesterday" @selected($dateRange === 'yesterday')>Yesterday</option>
                        <option value="2_days_ago" @selected($dateRange === '2_days_ago')>2 Days Ago</option>
                        <option value="this_week" @selected($dateRange === 'this_week')>This week</option>
                        <option value="custom" @selected($dateRange === 'custom')>Custom</option>
                    </select>
                </label>
                <div class="history-custom-range" id="historyCustomRange" @if($dateRange !== 'custom') hidden @endif>
                    <label class="history-date-input-field" for="historyStartDate">
                        <span>Start</span>
                        <input type="date" id="historyStartDate" name="start_date" value="{{ $startDateInput }}">
                    </label>
                    <label class="history-date-input-field" for="historyEndDate">
                        <span>End</span>
                        <input type="date" id="historyEndDate" name="end_date" value="{{ $endDateInput }}">
                    </label>
                    <button type="submit" class="inquiries-btn inquiries-btn-secondary history-apply-btn">Apply</button>
                </div>
                <div class="history-date-summary" id="historyDateSummary" @if($dateRange === 'custom') hidden @endif>
                    <span><strong>Start:</strong> {{ $filterStartDate }}</span>
                    <span><strong>End:</strong> {{ $filterEndDate }}</span>
                </div>
            </form>
            <div class="history-toolbar-actions">
                <button type="button" class="inquiries-btn inquiries-btn-secondary" id="historyClearFilters">Clear filters</button>
                <label class="history-checkbox-filter" for="historySystemMarkedFailOnly">
                    <input type="checkbox" id="historySystemMarkedFailOnly">
                    <span>System Marked Fail</span>
                </label>
            </div>
        </div>
        <div class="table-responsive">
            <table class="dashboard-table inquiries-table" id="historyTable">
                <thead>
                    <tr class="inquiries-header-row">
                        <th data-col="id" class="inquiries-header-cell"><span class="inquiries-header-label">ID</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter" data-col="id"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="inquiryid" class="inquiries-header-cell"><span class="inquiries-header-label">Inquiry ID</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter" data-col="inquiryid"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="user" class="inquiries-header-cell"><span class="inquiries-header-label">User</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter" data-col="user"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="subject" class="inquiries-header-cell"><span class="inquiries-header-label">Subject</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter" data-col="subject"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="description" class="inquiries-header-cell"><span class="inquiries-header-label">Description</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter" data-col="description"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="status" class="inquiries-header-cell"><span class="inquiries-header-label">Status</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter" data-col="status"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="date" class="inquiries-header-cell"><span class="inquiries-header-label">Date</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter" data-col="date"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $r)
                        @php
                            $dateStr = $r->CREATIONDATE ? date('Y-m-d H:i', strtotime($r->CREATIONDATE)) : '';
                            $inquiryId = isset($r->LEADID) ? ('#SQL-' . $r->LEADID) : '';
                            $fullDesc = (string) ($r->DESCRIPTION ?? '');
                            $fullDescTrim = trim($fullDesc);
                            $subjectText = trim((string) ($r->SUBJECT ?? ''));
                            $descPreview = $fullDescTrim === '' ? '-' : (mb_strlen($fullDescTrim) > 50 ? (mb_substr($fullDescTrim, 0, 50) . '...') : $fullDescTrim);
                            $isLongDesc = $fullDescTrim !== '' && mb_strlen($fullDescTrim) > 50;
                            $isSystemMarkedFail = in_array(strtoupper($subjectText), ['STATUS CHANGED TO FAILED (AUTO AFTER 8 MONTHS)', 'LEAD FAILED'], true)
                                || in_array(strtoupper($fullDescTrim), ['STATUS CHANGED TO FAILED (AUTO AFTER 8 MONTHS)', 'LEAD IS EXPIRED AFTER 8 MONTHS OF INQUIRY DATE'], true)
                                || str_contains(strtolower($fullDescTrim), 'expired automatically because it has been open for more than 8 months');
                            $searchHaystack = strtolower(($r->LEAD_ACTID ?? '').' '.$inquiryId.' '.($r->USERID ?? '').' '.$subjectText.' '.$fullDescTrim.' '.($r->STATUS ?? '').' '.$dateStr);
                        @endphp
                        <tr class="history-row inquiry-row"
                            data-search="{{ $searchHaystack }}"
                            data-system-marked-fail="{{ $isSystemMarkedFail ? '1' : '0' }}">
                            <td data-col="id">{{ $r->LEAD_ACTID }}</td>
                            <td data-col="inquiryid">{{ $inquiryId }}</td>
                            <td data-col="user">{{ $r->USERID }}</td>
                            <td data-col="subject">{{ $subjectText !== '' ? $subjectText : '-' }}</td>
                            <td data-col="description"
                                class="inquiries-msg-cell {{ $isLongDesc ? 'inquiries-msg-clickable' : '' }}"
                                @if($isLongDesc) data-full-message="{{ e($fullDescTrim) }}" @endif>
                                {{ $descPreview }}
                            </td>
                            <td data-col="status">{{ $r->STATUS ?? '-' }}</td>
                            <td data-col="date">{{ $dateStr ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7">No activities found for the selected date range.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var table = document.getElementById('historyTable');
    if (!table) return;
    var systemMarkedFailOnly = document.getElementById('historySystemMarkedFailOnly');
    var dateFilterForm = document.getElementById('historyDateFilterForm');
    var dateRangeSelect = document.getElementById('historyDateRange');
    var customRange = document.getElementById('historyCustomRange');
    var dateSummary = document.getElementById('historyDateSummary');
    var startDateField = document.getElementById('historyStartDate');
    var endDateField = document.getElementById('historyEndDate');

    function syncDateRangeUi() {
        var isCustom = !!(dateRangeSelect && dateRangeSelect.value === 'custom');
        if (customRange) {
            customRange.hidden = !isCustom;
        }
        if (dateSummary) {
            dateSummary.hidden = isCustom;
        }
        if (startDateField) {
            startDateField.required = isCustom;
        }
        if (endDateField) {
            endDateField.required = isCustom;
        }
    }

    function applyTableFilter() {
        var searchInput = document.getElementById('historySearchInput');
        var q = (searchInput && searchInput.value) ? searchInput.value.toLowerCase().trim() : '';
        var failOnly = !!(systemMarkedFailOnly && systemMarkedFailOnly.checked);
        var filters = {};
        table.querySelectorAll('thead .inquiries-grid-filter').forEach(function(inp) {
            var col = inp.getAttribute('data-col');
            var val = (inp.value || '').toLowerCase().trim();
            if (col && val) filters[col] = val;
        });
        table.querySelectorAll('tbody tr.history-row').forEach(function(row) {
            var hay = (row.getAttribute('data-search') || '').toLowerCase();
            var searchMatch = !q || hay.indexOf(q) !== -1;
            var colMatch = true;
            for (var col in filters) {
                var cell = row.querySelector('td[data-col="' + col + '"]');
                var cellText = (cell && cell.textContent) ? cell.textContent.toLowerCase().trim() : '';
                if (cellText.indexOf(filters[col]) === -1) { colMatch = false; break; }
            }
            var systemFailMatch = !failOnly || (row.getAttribute('data-system-marked-fail') === '1');
            row.style.display = (searchMatch && colMatch && systemFailMatch) ? '' : 'none';
        });
    }
    var searchInput = document.getElementById('historySearchInput');
    if (searchInput) searchInput.addEventListener('input', applyTableFilter);
    table.querySelectorAll('thead .inquiries-grid-filter').forEach(function(inp) {
        inp.addEventListener('input', applyTableFilter);
    });
    if (dateRangeSelect) {
        dateRangeSelect.addEventListener('change', function() {
            syncDateRangeUi();
            if (dateRangeSelect.value !== 'custom' && dateFilterForm) {
                if (startDateField) startDateField.value = '';
                if (endDateField) endDateField.value = '';
                dateFilterForm.submit();
            }
        });
        syncDateRangeUi();
    }
    if (systemMarkedFailOnly) {
        systemMarkedFailOnly.addEventListener('change', applyTableFilter);
    }

    var clearFiltersBtn = document.getElementById('historyClearFilters');
    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', function() {
            if (searchInput) searchInput.value = '';
            table.querySelectorAll('thead .inquiries-grid-filter').forEach(function(inp) {
                inp.value = '';
            });
            if (systemMarkedFailOnly) systemMarkedFailOnly.checked = false;
            var shouldReload = false;
            if (dateRangeSelect && dateRangeSelect.value !== 'today') shouldReload = true;
            if (startDateField && startDateField.value) shouldReload = true;
            if (endDateField && endDateField.value) shouldReload = true;
            if (shouldReload) {
                window.location = '{{ route('admin.history') }}';
                return;
            }
            applyTableFilter();
        });
    }

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
            + '    <div class="inquiries-msg-modal-title" id="inquiriesMsgModalTitle">Description</div>'
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
            if (cell) {
                open(cell.getAttribute('data-full-message') || '');
                return;
            }
            var closer = e.target && e.target.closest ? e.target.closest('[data-close="1"]') : null;
            if (closer && modal && !modal.hidden && modal.contains(closer)) close();
        });
        window.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !modal.hidden) close();
        });
    })();
});
</script>
@endpush
