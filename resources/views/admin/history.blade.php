@extends('layouts.app')
@section('title', 'History - Admin')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/admin-history.css') }}?v=20260409-02">
    <style>
        .history-custom-range .history-date-input-field input[type="date"] {
            padding-right: 32px;
        }
        .history-custom-range .history-date-input-field input[type="date"]::-webkit-calendar-picker-indicator {
            display: none;
        }
        .history-custom-calendar-icon {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #64748b;
            font-size: 14px;
            pointer-events: auto;
        }
        .history-custom-calendar-icon:hover {
            color: #3b82f6;
        }
        .history-row.is-expanded .desc-full {
            background-color: transparent !important;
            border: none !important;
            padding: 0 !important;
            margin-top: 0 !important;
            color: inherit !important;
        }
        #historyTable th[data-col="subject"],
        #historyTable td[data-col="subject"],
        #historyTable th[data-col="description"],
        #historyTable td[data-col="description"] {
            max-width: 280px;
        }
        #historyTable td {
            vertical-align: top;
        }
        .history-range-grid {
            display: grid;
            grid-template-columns: auto auto auto;
            align-items: flex-end;
            gap: 12px;
        }
        .history-range-apply-col {
            display: flex;
            align-items: flex-end;
            padding-bottom: 2px;
        }
        @media (max-width: 768px) {
            .history-range-grid {
                grid-template-columns: 1fr 1fr;
            }
            .history-range-apply-col {
                grid-column: span 2;
                justify-content: flex-end;
            }
        }
    </style>
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
                    <div class="history-range-grid">
                        <label class="history-date-input-field" for="historyStartDate" style="position: relative;">
                            <span>Start</span>
                            <input type="date" id="historyStartDate" name="start_date" value="{{ $startDateInput }}">
                            <i class="bi bi-calendar3 history-custom-calendar-icon" onclick="document.getElementById('historyStartDate').showPicker()"></i>
                        </label>
                        <label class="history-date-input-field" for="historyEndDate" style="position: relative;">
                            <span>End</span>
                            <input type="date" id="historyEndDate" name="end_date" value="{{ $endDateInput }}">
                            <i class="bi bi-calendar3 history-custom-calendar-icon" onclick="document.getElementById('historyEndDate').showPicker()"></i>
                        </label>
                        <div class="history-range-apply-col">
                            <button type="submit" class="inquiries-btn inquiries-btn-secondary history-apply-btn">Apply</button>
                        </div>
                    </div>
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
                <label class="history-checkbox-filter" for="historyMarkedCancelledOnly">
                    <input type="checkbox" id="historyMarkedCancelledOnly">
                    <span>Marked Cancelled</span>
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
                        <th data-col="customer" class="inquiries-header-cell"><span class="inquiries-header-label">Customer Name</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter" data-col="customer"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="postcode" class="inquiries-header-cell"><span class="inquiries-header-label">Postcode</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter" data-col="postcode"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="city" class="inquiries-header-cell"><span class="inquiries-header-label">City</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter" data-col="city"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
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
                            $fullDescTrim = preg_replace('/[\r\n]+/', "\n", trim($fullDesc));
                            $subjectText = preg_replace('/[\r\n]+/', "\n", trim((string) ($r->SUBJECT ?? '')));
                            $descPreview = $fullDescTrim === '' ? '-' : (mb_strlen($fullDescTrim) > 50 ? (mb_substr($fullDescTrim, 0, 50) . '...') : $fullDescTrim);
                            $isLongDesc = $fullDescTrim !== '' && mb_strlen($fullDescTrim) > 50;
                            $subjectPreview = $subjectText === '' ? '-' : (mb_strlen($subjectText) > 27 ? (mb_substr($subjectText, 0, 27) . '...') : $subjectText);
                            $isLongSubject = $subjectText !== '' && mb_strlen($subjectText) > 27;
                            $isSystemMarkedFail = in_array(strtoupper($subjectText), ['STATUS CHANGED TO FAILED (AUTO AFTER 8 MONTHS)', 'LEAD FAILED'], true)
                                || in_array(strtoupper($fullDescTrim), ['STATUS CHANGED TO FAILED (AUTO AFTER 8 MONTHS)', 'LEAD IS EXPIRED AFTER 8 MONTHS OF INQUIRY DATE'], true)
                                || (str_contains(strtolower($fullDescTrim), 'expired automatically because it has been open for more than 8 months') && strtoupper($r->STATUS ?? '') === 'FAILED');
                            $isMarkedCancelled = strtoupper(trim((string) ($r->STATUS ?? ''))) === 'CANCELLED';
                            $company = trim((string) ($r->COMPANYNAME ?? ''));
                            $contact = trim((string) ($r->CONTACTNAME ?? ''));
                            $customerDisplay = $company !== '' && $contact !== '' ? ($company . ' - ' . $contact) : ($company !== '' ? $company : ($contact !== '' ? $contact : '-'));
                            $userDisplay = trim((string) ($r->ALIAS ?? '')) !== '' ? $r->ALIAS : $r->USERID;
                            $searchHaystack = strtolower(($r->LEAD_ACTID ?? '').' '.$inquiryId.' '.$userDisplay.' '.$customerDisplay.' '.($r->POSTCODE ?? '').' '.($r->CITY ?? '').' '.$subjectText.' '.$fullDescTrim.' '.($r->STATUS ?? '').' '.$dateStr);
                        @endphp
                        <tr class="history-row inquiry-row"
                            data-search="{{ $searchHaystack }}"
                            data-system-marked-fail="{{ $isSystemMarkedFail ? '1' : '0' }}"
                            data-marked-cancelled="{{ $isMarkedCancelled ? '1' : '0' }}">
                            <td data-col="id">{{ $r->LEAD_ACTID }}</td>
                            <td data-col="inquiryid">{{ $inquiryId }}</td>
                            <td data-col="user">{{ $userDisplay }}</td>
                            <td data-col="customer">{{ $customerDisplay }}</td>
                            <td data-col="postcode">{{ $r->POSTCODE ?? '-' }}</td>
                            <td data-col="city">{{ $r->CITY ?? '-' }}</td>
                            <td data-col="subject"
                                class="inquiries-msg-cell {{ $isLongSubject ? 'expandable-desc' : '' }}">
                                <div class="desc-preview">{{ $subjectPreview }}</div>
                                @if($isLongSubject)
                                    <div class="desc-full">{!! nl2br(e($subjectText)) !!}</div>
                                @endif
                            </td>
                            <td data-col="description"
                                class="inquiries-msg-cell {{ $isLongDesc ? 'expandable-desc' : '' }}">
                                <div class="desc-preview">{{ $descPreview }}</div>
                                @if($isLongDesc)
                                    <div class="desc-full">{!! nl2br(e($fullDescTrim)) !!}</div>
                                @endif
                            </td>
                            <td data-col="status">{{ $r->STATUS ?? '-' }}</td>
                            <td data-col="date">{{ $dateStr ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="10">No activities found for the selected date range.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @include('partials.common_pagination', [
            'id' => 'historyPagination',
            'total' => $total,
            'perPage' => $perPage,
            'currentPage' => $currentPageNum,
            'lastPage' => $lastPage,
            'containerClass' => 'history-pagination-container'
        ])
    </div>
</section>
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var table = document.getElementById('historyTable');
    if (!table) return;
    var systemMarkedFailOnly = document.getElementById('historySystemMarkedFailOnly');
    var markedCancelledOnly = document.getElementById('historyMarkedCancelledOnly');
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
        var cancelledOnly = !!(markedCancelledOnly && markedCancelledOnly.checked);
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
            var systemFailMatch = row.getAttribute('data-system-marked-fail') === '1';
            var cancelledMatch = row.getAttribute('data-marked-cancelled') === '1';
            
            var checkboxMatch = true;
            if (failOnly && cancelledOnly) {
                checkboxMatch = systemFailMatch || cancelledMatch;
            } else if (failOnly) {
                checkboxMatch = systemFailMatch;
            } else if (cancelledOnly) {
                checkboxMatch = cancelledMatch;
            }

            row.style.display = (searchMatch && colMatch && checkboxMatch) ? '' : 'none';
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
    
    // Removed auto-submit on individual date changes to allow picking range then clicking Apply
    if (systemMarkedFailOnly) {
        systemMarkedFailOnly.addEventListener('change', applyTableFilter);
    }
    if (markedCancelledOnly) {
        markedCancelledOnly.addEventListener('change', applyTableFilter);
    }

    var clearFiltersBtn = document.getElementById('historyClearFilters');
    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', function() {
            if (searchInput) searchInput.value = '';
            table.querySelectorAll('thead .inquiries-grid-filter').forEach(function(inp) {
                inp.value = '';
            });
            if (systemMarkedFailOnly) systemMarkedFailOnly.checked = false;
            if (markedCancelledOnly) markedCancelledOnly.checked = false;
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

    // Row Click to Expand
    table.addEventListener('click', function(e) {
        var row = e.target.closest('tr.history-row');
        if (!row) return;
        var descCells = row.querySelectorAll('.expandable-desc');
        var isExpanded = false;
        descCells.forEach(function(cell) {
            cell.classList.toggle('expanded');
            if (cell.classList.contains('expanded')) isExpanded = true;
        });
        if (isExpanded) {
            row.classList.add('is-expanded');
        } else {
            row.classList.remove('is-expanded');
        }
    });

    // Context Menu for "Full Expand"
    var contextMenu = document.createElement('div');
    contextMenu.className = 'history-context-menu hidden';
    contextMenu.innerHTML = ''
        + '<div class="context-menu-item" id="menuExpandAll"><i class="bi bi-arrows-expand"></i> Full Expand All</div>'
        + '<div class="context-menu-item" id="menuCollapseAll"><i class="bi bi-arrows-collapse"></i> Collapse All</div>';
    document.body.appendChild(contextMenu);

    table.addEventListener('contextmenu', function(e) {
        var row = e.target.closest('tr.history-row');
        if (!row) return;

        e.preventDefault();
        
        var rect = contextMenu.getBoundingClientRect();
        var x = e.clientX;
        var y = e.clientY;
        
        contextMenu.style.left = x + 'px';
        contextMenu.style.top = y + 'px';
        contextMenu.classList.remove('hidden');
    });

    document.addEventListener('click', function(e) {
        if (!contextMenu.contains(e.target)) {
            contextMenu.classList.add('hidden');
        }
    });

    document.getElementById('menuExpandAll').addEventListener('click', function() {
        table.querySelectorAll('.expandable-desc').forEach(function(cell) {
            cell.classList.add('expanded');
        });
        table.querySelectorAll('tr.history-row').forEach(function(row) {
            if (row.querySelector('.expandable-desc')) {
                row.classList.add('is-expanded');
            }
        });
        contextMenu.classList.add('hidden');
    });

    document.getElementById('menuCollapseAll').addEventListener('click', function() {
        table.querySelectorAll('.expandable-desc').forEach(function(cell) {
            cell.classList.remove('expanded');
        });
        table.querySelectorAll('tr.history-row').forEach(function(row) {
            row.classList.remove('is-expanded');
        });
        contextMenu.classList.add('hidden');
    });

    // Pagination Logic
    function buildHistorySmartPageNumbers() {
        var paginationEl = document.getElementById('historyPagination');
        if (!paginationEl) return;
        var pageNumbersEl = document.getElementById('historyPaginationPageNumbers');
        if (!pageNumbersEl) return;
        
        var current = parseInt(paginationEl.dataset.currentPage);
        var lastPage = parseInt(paginationEl.dataset.lastPage);
        
        pageNumbersEl.innerHTML = '';
        
        function addBtn(p, isActive) {
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'inquiries-pagination-num' + (isActive ? ' inquiries-pagination-num-active' : '');
            btn.textContent = p;
            btn.addEventListener('click', function() {
                if (!isActive) goToHistoryPage(p);
            });
            pageNumbersEl.appendChild(btn);
        }
        
        function addDots() {
            var span = document.createElement('span');
            span.className = 'inquiries-pagination-num inquiries-pagination-dots';
            span.textContent = '...';
            pageNumbersEl.appendChild(span);
        }

        if (lastPage <= 5) {
            for (var i = 1; i <= lastPage; i++) addBtn(i, i === current);
        } else {
            if (current <= 3) {
                addBtn(1, current === 1);
                addBtn(2, current === 2);
                addBtn(3, current === 3);
                addDots();
                addBtn(lastPage, false);
            } else if (current >= lastPage - 2) {
                addBtn(1, false);
                addDots();
                addBtn(lastPage - 2, current === lastPage - 2);
                addBtn(lastPage - 1, current === lastPage - 1);
                addBtn(lastPage, current === lastPage);
            } else {
                addBtn(1, false);
                addDots();
                addBtn(current, true);
                addBtn(current + 1, false);
                addDots();
                addBtn(lastPage, false);
            }
        }
    }

    function goToHistoryPage(p) {
        var url = new URL(window.location.href);
        url.searchParams.set('page', p);
        window.location.href = url.toString();
    }

    document.querySelectorAll('.inquiries-pagination-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var paginationEl = document.getElementById('historyPagination');
            if (!paginationEl) return;
            
            var action = this.dataset.page;
            var current = parseInt(paginationEl.dataset.currentPage);
            var lastPage = parseInt(paginationEl.dataset.lastPage);
            var target = current;

            if (action === 'first') target = 1;
            else if (action === 'prev') target = Math.max(1, current - 1);
            else if (action === 'next') target = Math.min(lastPage, current + 1);
            else if (action === 'last') target = lastPage;

            if (target !== current) goToHistoryPage(target);
        });
    });

    buildHistorySmartPageNumbers();
});
</script>
@endpush
