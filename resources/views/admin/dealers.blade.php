@extends('layouts.app')
@section('title', 'Dealers – Admin')
@section('content')
<section class="dashboard-panel dashboard-table-panel">
    <div class="dashboard-panel-body">
        <div class="dealers-panel-actions dealers-panel-actions-right" style="margin-bottom: 12px;">
            <div class="dealers-panel-buttons">
                <button type="button" class="inquiries-btn inquiries-btn-secondary" id="dealerClearFilters">Clear filters</button>
                <div class="inquiries-columns-dropdown dealers-columns-right">
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
            </div>
        </div>
        <div class="table-responsive">
            <table class="dashboard-table dealers-table-with-filters" id="dealersTable">
                <thead>
                    <tr class="inquiries-header-row">
                        <th data-col="userid" class="dashboard-table-sortable inquiries-header-cell inquiries-sortable"><span class="inquiries-header-label">User ID</span><span class="inquiries-filter-wrap"><input type="text" class="dealer-grid-filter" data-col="userid"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="alias" class="dashboard-table-sortable inquiries-header-cell inquiries-sortable"><span class="inquiries-header-label">Alias</span><span class="inquiries-filter-wrap"><input type="text" class="dealer-grid-filter" data-col="alias"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="company" class="dashboard-table-sortable inquiries-header-cell inquiries-sortable"><span class="inquiries-header-label">Company name</span><span class="inquiries-filter-wrap"><input type="text" class="dealer-grid-filter" data-col="company"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="email" class="dashboard-table-sortable inquiries-header-cell inquiries-sortable"><span class="inquiries-header-label">Email</span><span class="inquiries-filter-wrap"><input type="text" class="dealer-grid-filter" data-col="email"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="postcode" class="dashboard-table-sortable inquiries-header-cell inquiries-sortable"><span class="inquiries-header-label">Postcode</span><span class="inquiries-filter-wrap"><input type="text" class="dealer-grid-filter" data-col="postcode"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="city" class="dashboard-table-sortable inquiries-header-cell inquiries-sortable"><span class="inquiries-header-label">City</span><span class="inquiries-filter-wrap"><input type="text" class="dealer-grid-filter" data-col="city"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="totallead" class="dashboard-table-sortable inquiries-header-cell inquiries-sortable"><span class="inquiries-header-label">Leads</span><span class="inquiries-filter-wrap"><input type="text" class="dealer-grid-filter" data-col="totallead"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="totalongoing" class="dashboard-table-sortable inquiries-header-cell inquiries-sortable"><span class="inquiries-header-label">Ongoing</span><span class="inquiries-filter-wrap"><input type="text" class="dealer-grid-filter" data-col="totalongoing"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="totalclosed" class="dashboard-table-sortable inquiries-header-cell inquiries-sortable"><span class="inquiries-header-label">Closed</span><span class="inquiries-filter-wrap"><input type="text" class="dealer-grid-filter" data-col="totalclosed"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="totalfailed" class="dashboard-table-sortable inquiries-header-cell inquiries-sortable"><span class="inquiries-header-label">Failed</span><span class="inquiries-filter-wrap"><input type="text" class="dealer-grid-filter" data-col="totalfailed"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="conversionrate" class="dashboard-table-sortable inquiries-header-cell inquiries-sortable"><span class="inquiries-header-label">Conversion</span><span class="inquiries-filter-wrap"><input type="text" class="dealer-grid-filter" data-col="conversionrate"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="active" class="dashboard-table-sortable inquiries-header-cell inquiries-sortable"><span class="inquiries-header-label">Active</span><span class="inquiries-filter-wrap"><input type="text" class="dealer-grid-filter" data-col="active"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                    </tr>
                </thead>
                <tbody>
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
                            @php $convRate = (float)($r->CONVERSION_RATE ?? 0); $convClass = $convRate >= 50 ? 'dealer-conversion-high' : ($convRate >= 20 ? 'dealer-conversion-mid' : 'dealer-conversion-low'); @endphp
                            <td data-col="conversionrate"><span class="dealer-conversion-label {{ $convClass }}">{{ number_format($convRate, 1) }}%</span></td>
                            <td data-col="active">{{ ($r->ISACTIVE ?? 0) ? 'Yes' : 'No' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="12" class="inquiries-empty">No dealers yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var table = document.getElementById('dealersTable');
    if (!table) return;
    var state = { col: 'conversionrate', dir: -1 };

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
                if (td.style.display === 'none') return;
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
    window.addEventListener('resize', function() { measureAndSizeDealerColumns(); });

    // ——— Grid filters ———
    function applyDealerGridFilters() {
        var filters = {};
        table.querySelectorAll('.dealer-grid-filter').forEach(function(inp) {
            var col = inp.getAttribute('data-col');
            var val = (inp.value || '').toLowerCase().trim();
            if (col && val) filters[col] = val;
        });
        table.querySelectorAll('tbody tr.dealer-row').forEach(function(row) {
            var match = true;
            for (var col in filters) {
                var cell = row.querySelector('td[data-col="' + col + '"]');
                var cellText = (cell && cell.textContent) ? cell.textContent.toLowerCase().trim() : '';
                if (cellText.indexOf(filters[col]) === -1) { match = false; break; }
            }
            row.style.display = match ? '' : 'none';
        });
    }

    table.querySelectorAll('.dealer-grid-filter').forEach(function(inp) {
        inp.addEventListener('input', applyDealerGridFilters);
        inp.addEventListener('keyup', applyDealerGridFilters);
    });

    var clearBtn = document.getElementById('dealerClearFilters');
    if (clearBtn) {
        clearBtn.addEventListener('click', function() {
            table.querySelectorAll('.dealer-grid-filter').forEach(function(inp) { inp.value = ''; });
            applyDealerGridFilters();
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
    }
    table.querySelectorAll('thead th[data-col]').forEach(function(th) {
        th.style.cursor = 'pointer';
        th.addEventListener('click', function(e) {
            if (e.target.closest('input, button, .inquiries-filter-wrap, .dealer-header-filter-row')) return;
            var col = th.getAttribute('data-col');
            if (!col) return;
            state.dir = (state.col === col) ? -state.dir : 1;
            state.col = col;
            applySort();
        });
    });
    applySort();
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
