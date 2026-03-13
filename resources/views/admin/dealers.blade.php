@extends('layouts.app')
@section('title', 'Dealers – Admin')
@section('content')
<header class="dashboard-header">
    <div>
        <h1 class="dashboard-title">Dealers</h1>
        <p class="dashboard-subtitle">Dealer users and performance</p>
    </div>
</header>

<section class="dashboard-panel dashboard-table-panel">
    <div class="dashboard-panel-body">
        <div class="table-responsive">
            <table class="dashboard-table" id="dealersTable">
                <thead>
                    <tr>
                        <th data-col="userid" class="dashboard-table-sortable" title="Sort">User ID</th>
                        <th data-col="email" class="dashboard-table-sortable" title="Sort">Email</th>
                        <th data-col="postcode" class="dashboard-table-sortable" title="Sort">Postcode</th>
                        <th data-col="city" class="dashboard-table-sortable" title="Sort">City</th>
                        <th data-col="active" class="dashboard-table-sortable" title="Sort">Active</th>
                        <th data-col="company" class="dashboard-table-sortable" title="Sort">Company name</th>
                        <th data-col="alias" class="dashboard-table-sortable" title="Sort">Alias</th>
                        <th data-col="totallead" class="dashboard-table-sortable" title="Sort">Total lead</th>
                        <th data-col="totalongoing" class="dashboard-table-sortable" title="Sort">Ongoing</th>
                        <th data-col="totalclosed" class="dashboard-table-sortable" title="Sort">Total closed</th>
                        <th data-col="totalfailed" class="dashboard-table-sortable" title="Sort">Failed</th>
                        <th data-col="conversionrate" class="dashboard-table-sortable" title="Sort">Conversion rate</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $r)
                        <tr class="dealer-row">
                            <td data-col="userid">{{ $r->USERID }}</td>
                            <td data-col="email">{{ $r->EMAIL }}</td>
                            <td data-col="postcode">{{ $r->POSTCODE ?? '—' }}</td>
                            <td data-col="city">{{ $r->CITY ?? '—' }}</td>
                            <td data-col="active">{{ ($r->ISACTIVE ?? 0) ? 'Yes' : 'No' }}</td>
                            <td data-col="company">{{ $r->COMPANY ?? '—' }}</td>
                            <td data-col="alias">{{ $r->ALIAS ?? '—' }}</td>
                            <td data-col="totallead">{{ number_format((int)($r->TOTAL_LEAD ?? 0)) }}</td>
                            <td data-col="totalongoing">{{ number_format((int)($r->TOTAL_ONGOING ?? 0)) }}</td>
                            <td data-col="totalclosed">{{ number_format((int)($r->TOTAL_CLOSED ?? 0)) }}</td>
                            <td data-col="totalfailed">{{ number_format((int)($r->TOTAL_FAILED ?? 0)) }}</td>
                            <td data-col="conversionrate">{{ number_format((float)($r->CONVERSION_RATE ?? 0), 1) }}%</td>
                        </tr>
                    @empty
                        <tr><td colspan="12">No dealers yet.</td></tr>
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
    var state = { col: null, dir: 1 };
    function getVal(row, col) {
        var cell = row.querySelector('td[data-col="' + col + '"]');
        return (cell && cell.textContent) ? cell.textContent.trim().toLowerCase() : '';
    }
    table.querySelectorAll('thead th[data-col]').forEach(function(th) {
        th.style.cursor = 'pointer';
        th.addEventListener('click', function() {
            var col = th.getAttribute('data-col');
            if (!col) return;
            state.dir = (state.col === col) ? -state.dir : 1;
            state.col = col;
            table.querySelectorAll('thead th[data-col]').forEach(function(h) {
                h.classList.remove('dashboard-sort-asc', 'dashboard-sort-desc');
                if (h.getAttribute('data-col') === col) {
                    h.classList.add(state.dir === 1 ? 'dashboard-sort-asc' : 'dashboard-sort-desc');
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
        });
    });
});
</script>
@endpush
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const openBtn = document.getElementById('openAddUserModal');
            const closeBtn = document.getElementById('closeAddUserModal');
            const modal = document.getElementById('addUserModal');
            if (!modal || !openBtn || !closeBtn) return;
            openBtn.addEventListener('click', () => { modal.style.display = 'flex'; });
            closeBtn.addEventListener('click', () => { modal.style.display = 'none'; });
            modal.addEventListener('click', (e) => {
                if (e.target === modal) modal.style.display = 'none';
            });
        });
    </script>
@endpush
