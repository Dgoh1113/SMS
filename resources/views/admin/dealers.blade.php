@extends('layouts.app')
@section('title', 'Dealers – Admin')
@section('content')
<section class="dashboard-panel dashboard-table-panel">
    <div class="dashboard-panel-body">
        <div class="dealers-panel-actions dealers-panel-actions-right" style="margin-bottom: 12px;">
            <div class="dealers-panel-buttons">
                <button type="button" class="inquiries-btn inquiries-btn-secondary" id="dealerClearFilters">Clear filters</button>
                <button type="button" class="inquiries-btn inquiries-btn-secondary inquiries-sync-btn" id="dealerSyncBtn" data-sync-url="{{ route('admin.dealers.sync') }}" data-sync-type="dealers">
                    <i class="bi bi-arrow-repeat inquiries-sync-icon" aria-hidden="true"></i>
                    <span>Sync</span>
                </button>
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
                        <th data-col="userid" class="dashboard-table-sortable inquiries-header-cell inquiries-sortable"><span class="inquiries-header-label">User ID</span><span class="inquiries-filter-wrap dealer-filter-input-wrap"><input type="text" class="dealer-grid-filter" data-col="userid"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="alias" class="dashboard-table-sortable inquiries-header-cell inquiries-sortable"><span class="inquiries-header-label">Alias</span><span class="inquiries-filter-wrap dealer-filter-input-wrap"><input type="text" class="dealer-grid-filter" data-col="alias"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="company" class="dashboard-table-sortable inquiries-header-cell inquiries-sortable"><span class="inquiries-header-label">Company name</span><span class="inquiries-filter-wrap dealer-filter-input-wrap"><input type="text" class="dealer-grid-filter" data-col="company"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="email" class="dashboard-table-sortable inquiries-header-cell inquiries-sortable"><span class="inquiries-header-label">Email</span><span class="inquiries-filter-wrap dealer-filter-input-wrap"><input type="text" class="dealer-grid-filter" data-col="email"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="postcode" class="dashboard-table-sortable inquiries-header-cell inquiries-sortable"><span class="inquiries-header-label">Postcode</span><span class="inquiries-filter-wrap dealer-filter-input-wrap"><input type="text" class="dealer-grid-filter" data-col="postcode"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="city" class="dashboard-table-sortable inquiries-header-cell inquiries-sortable"><span class="inquiries-header-label">City</span><span class="inquiries-filter-wrap dealer-filter-input-wrap"><input type="text" class="dealer-grid-filter" data-col="city"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="totallead" class="dashboard-table-sortable inquiries-header-cell inquiries-sortable"><span class="inquiries-header-label">Leads</span><span class="inquiries-filter-wrap dealer-operator-search-wrap"><span class="dealer-operator-search-box"><button type="button" class="dealer-operator-btn" data-col="totallead" data-op="=" aria-haspopup="true" aria-expanded="false" title="Filter operator">=</button><div class="dealer-operator-dropdown" hidden><button type="button" data-op="=">= Equals</button><button type="button" data-op="!=">≠ Does not equal</button><button type="button" data-op="<">&lt; Less than</button><button type="button" data-op="<=">≤ Less than or equal to</button><button type="button" data-op=">">&gt; Greater than</button><button type="button" data-op=">=">≥ Greater than or equal to</button></div><input type="text" class="dealer-grid-filter" data-col="totallead" placeholder="0"></span></span></th>
                        <th data-col="totalongoing" class="dashboard-table-sortable inquiries-header-cell inquiries-sortable"><span class="inquiries-header-label">Ongoing</span><span class="inquiries-filter-wrap dealer-operator-search-wrap"><span class="dealer-operator-search-box"><button type="button" class="dealer-operator-btn" data-col="totalongoing" data-op="=" aria-haspopup="true" aria-expanded="false" title="Filter operator">=</button><div class="dealer-operator-dropdown" hidden><button type="button" data-op="=">= Equals</button><button type="button" data-op="!=">≠ Does not equal</button><button type="button" data-op="<">&lt; Less than</button><button type="button" data-op="<=">≤ Less than or equal to</button><button type="button" data-op=">">&gt; Greater than</button><button type="button" data-op=">=">≥ Greater than or equal to</button></div><input type="text" class="dealer-grid-filter" data-col="totalongoing" placeholder="0"></span></span></th>
                        <th data-col="totalclosed" class="dashboard-table-sortable inquiries-header-cell inquiries-sortable"><span class="inquiries-header-label">Closed</span><span class="inquiries-filter-wrap dealer-operator-search-wrap"><span class="dealer-operator-search-box"><button type="button" class="dealer-operator-btn" data-col="totalclosed" data-op="=" aria-haspopup="true" aria-expanded="false" title="Filter operator">=</button><div class="dealer-operator-dropdown" hidden><button type="button" data-op="=">= Equals</button><button type="button" data-op="!=">≠ Does not equal</button><button type="button" data-op="<">&lt; Less than</button><button type="button" data-op="<=">≤ Less than or equal to</button><button type="button" data-op=">">&gt; Greater than</button><button type="button" data-op=">=">≥ Greater than or equal to</button></div><input type="text" class="dealer-grid-filter" data-col="totalclosed" placeholder="0"></span></span></th>
                        <th data-col="totalfailed" class="dashboard-table-sortable inquiries-header-cell inquiries-sortable"><span class="inquiries-header-label">Failed</span><span class="inquiries-filter-wrap dealer-operator-search-wrap"><span class="dealer-operator-search-box"><button type="button" class="dealer-operator-btn" data-col="totalfailed" data-op="=" aria-haspopup="true" aria-expanded="false" title="Filter operator">=</button><div class="dealer-operator-dropdown" hidden><button type="button" data-op="=">= Equals</button><button type="button" data-op="!=">≠ Does not equal</button><button type="button" data-op="<">&lt; Less than</button><button type="button" data-op="<=">≤ Less than or equal to</button><button type="button" data-op=">">&gt; Greater than</button><button type="button" data-op=">=">≥ Greater than or equal to</button></div><input type="text" class="dealer-grid-filter" data-col="totalfailed" placeholder="0"></span></span></th>
                        <th data-col="conversionrate" class="dashboard-table-sortable inquiries-header-cell inquiries-sortable"><span class="inquiries-header-label">Conversion</span><span class="inquiries-filter-wrap dealer-filter-input-wrap"><input type="text" class="dealer-grid-filter" data-col="conversionrate"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="active" class="dashboard-table-sortable inquiries-header-cell inquiries-sortable"><span class="inquiries-header-label">Active</span><span class="inquiries-filter-wrap dealer-filter-input-wrap"><input type="text" class="dealer-grid-filter" data-col="active"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
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
    </div>
</section>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var table = document.getElementById('dealersTable');
    if (!table) return;
    var tbody = document.getElementById('dealersTableBody');
    var syncBtn = document.getElementById('dealerSyncBtn');
    var DEALERS_AUTO_SYNC_MS = 15 * 60 * 1000;
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
    window.addEventListener('resize', function() { measureAndSizeDealerColumns(); });

    // ——— Grid filters (text: contains; Leads/Ongoing/Closed/Failed: = or > or <) ———
    var DEALER_NUMERIC_COLS = ['totallead','totalongoing','totalclosed','totalfailed'];
    function parseNum(s) {
        var n = parseInt(String(s).replace(/\s|,/g, ''), 10);
        return isNaN(n) ? 0 : n;
    }
    function applyDealerGridFilters() {
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
            row.style.display = match ? '' : 'none';
        });
    }

    function triggerDealerSync(btn) {
        if (!btn || !tbody || btn.classList.contains('is-syncing')) return;

        btn.classList.add('is-syncing');
        btn.disabled = true;

        var icon = btn.querySelector('.inquiries-sync-icon');
        if (icon) {
            icon.classList.add('spinning');
        }

        var url = btn.getAttribute('data-sync-url') || window.location.href;

        fetch(url + (url.indexOf('?') === -1 ? '?' : '&') + '_=' + Date.now(), {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            cache: 'no-store',
            credentials: 'same-origin'
        }).then(function(response) {
            if (!response.ok) {
                throw new Error('Unable to refresh dealers.');
            }

            return response.json();
        }).then(function(payload) {
            tbody.innerHTML = typeof payload.rows_html === 'string' ? payload.rows_html : '';
            refreshDealerColumnState();
            applyDealerGridFilters();
            applySort();
            measureAndSizeDealerColumns();
        }).catch(function(error) {
            console.error(error);
        }).finally(function() {
            btn.disabled = false;
            btn.classList.remove('is-syncing');

            if (icon) {
                icon.classList.remove('spinning');
            }
        });
    }

    table.querySelectorAll('.dealer-grid-filter').forEach(function(inp) {
        inp.addEventListener('input', applyDealerGridFilters);
        inp.addEventListener('keyup', applyDealerGridFilters);
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
                applyDealerGridFilters();
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
            applyDealerGridFilters();
            table.querySelectorAll('thead th[data-col]').forEach(function(h) {
                h.classList.remove('inquiries-sort-asc', 'inquiries-sort-desc');
            });
            state.col = null;
            state.dir = 1;
        });
    }

    if (syncBtn) {
        syncBtn.addEventListener('click', function() {
            triggerDealerSync(syncBtn);
        });

        window.setInterval(function() {
            triggerDealerSync(syncBtn);
        }, DEALERS_AUTO_SYNC_MS);
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
            if (e.target.closest('input, button, select, .inquiries-filter-wrap, .dealer-header-filter-row, .dealer-operator-btn, .dealer-operator-dropdown')) return;
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
