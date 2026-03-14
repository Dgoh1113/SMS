@extends('layouts.app')
@section('title', 'Rewards – Admin')
@section('content')
<header class="dashboard-header">
    <div>
        <h1 class="dashboard-title">Referrer Payouts</h1>
        <p class="dashboard-subtitle">Recent payouts (latest 100)</p>
    </div>
</header>

<section class="dashboard-panel dashboard-table-panel">
    <div class="inquiries-search-section" style="margin-bottom:12px;">
        <div class="inquiries-search-row">
            <div class="inquiries-search-wrap">
                <span class="inquiries-search-icon"><i class="bi bi-search"></i></span>
                <input type="text" class="inquiries-search-input" id="rewardsSearchInput" placeholder="Search table..." autocomplete="off">
            </div>
        </div>
    </div>
    <div class="dashboard-panel-body">
        <div class="table-responsive">
            <table class="dashboard-table inquiries-table" id="rewardsTable">
                <thead>
                    <tr class="inquiries-header-row">
                        <th data-col="id" class="inquiries-header-cell"><span class="inquiries-header-label">ID</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter" data-col="id"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="dealsubmission" class="inquiries-header-cell"><span class="inquiries-header-label">Deal Submission</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter" data-col="dealsubmission"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="user" class="inquiries-header-cell"><span class="inquiries-header-label">User</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter" data-col="user"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="referrer" class="inquiries-header-cell"><span class="inquiries-header-label">Referrer</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter" data-col="referrer"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="status" class="inquiries-header-cell"><span class="inquiries-header-label">Status</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter" data-col="status"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="generated" class="inquiries-header-cell"><span class="inquiries-header-label">Generated</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter" data-col="generated"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="paid" class="inquiries-header-cell"><span class="inquiries-header-label">Paid</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter" data-col="paid"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $r)
                        @php
                            $gen = $r->DATEGENERATED ? date('Y-m-d', strtotime($r->DATEGENERATED)) : '';
                            $paid = $r->DATEPAID ? date('Y-m-d', strtotime($r->DATEPAID)) : '';
                            $searchHaystack = strtolower(($r->REFERRERPAYOUTID ?? '').' '.($r->DEALSUBMISSIONID ?? '').' '.($r->USERID ?? '').' '.($r->REFERRERID ?? '').' '.($r->STATUS ?? '').' '.$gen.' '.$paid);
                        @endphp
                        <tr class="rewards-row inquiry-row" data-search="{{ $searchHaystack }}">
                            <td data-col="id">{{ $r->REFERRERPAYOUTID }}</td>
                            <td data-col="dealsubmission">{{ $r->DEALSUBMISSIONID ?? '—' }}</td>
                            <td data-col="user">{{ $r->USERID ?? '—' }}</td>
                            <td data-col="referrer">{{ $r->REFERRERID ?? '—' }}</td>
                            <td data-col="status">{{ $r->STATUS ?? '—' }}</td>
                            <td data-col="generated">{{ $gen ?: '—' }}</td>
                            <td data-col="paid">{{ $paid ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7">No payouts yet.</td></tr>
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
    var table = document.getElementById('rewardsTable');
    if (!table) return;
    function applyTableFilter() {
        var searchInput = document.getElementById('rewardsSearchInput');
        var q = (searchInput && searchInput.value) ? searchInput.value.toLowerCase().trim() : '';
        var filters = {};
        table.querySelectorAll('thead .inquiries-grid-filter').forEach(function(inp) {
            var col = inp.getAttribute('data-col');
            var val = (inp.value || '').toLowerCase().trim();
            if (col && val) filters[col] = val;
        });
        table.querySelectorAll('tbody tr.rewards-row').forEach(function(row) {
            var hay = (row.getAttribute('data-search') || '').toLowerCase();
            var searchMatch = !q || hay.indexOf(q) !== -1;
            var colMatch = true;
            for (var col in filters) {
                var cell = row.querySelector('td[data-col="' + col + '"]');
                var cellText = (cell && cell.textContent) ? cell.textContent.toLowerCase().trim() : '';
                if (cellText.indexOf(filters[col]) === -1) { colMatch = false; break; }
            }
            row.style.display = (searchMatch && colMatch) ? '' : 'none';
        });
    }
    var searchInput = document.getElementById('rewardsSearchInput');
    if (searchInput) searchInput.addEventListener('input', applyTableFilter);
    table.querySelectorAll('thead .inquiries-grid-filter').forEach(function(inp) {
        inp.addEventListener('input', applyTableFilter);
    });
});
</script>
@endpush
