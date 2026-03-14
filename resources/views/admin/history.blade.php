@extends('layouts.app')
@section('title', 'Lead Activities – Admin')
@section('content')
<header class="dashboard-header">
    <div>
        <h1 class="dashboard-title">Lead Activities</h1>
        <p class="dashboard-subtitle">Recent activities (latest 100)</p>
    </div>
</header>

<section class="dashboard-panel dashboard-table-panel">
    <div class="inquiries-search-section" style="margin-bottom:12px;">
        <div class="inquiries-search-row">
            <div class="inquiries-search-wrap">
                <span class="inquiries-search-icon"><i class="bi bi-search"></i></span>
                <input type="text" class="inquiries-search-input" id="historySearchInput" placeholder="Search table..." autocomplete="off">
            </div>
        </div>
    </div>
    <div class="dashboard-panel-body">
        <div class="table-responsive">
            <table class="dashboard-table inquiries-table" id="historyTable">
                <thead>
                    <tr class="inquiries-header-row">
                        <th data-col="id" class="inquiries-header-cell"><span class="inquiries-header-label">ID</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter" data-col="id"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="lead" class="inquiries-header-cell"><span class="inquiries-header-label">Lead</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter" data-col="lead"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="user" class="inquiries-header-cell"><span class="inquiries-header-label">User</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter" data-col="user"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="subject" class="inquiries-header-cell"><span class="inquiries-header-label">Subject</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter" data-col="subject"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="status" class="inquiries-header-cell"><span class="inquiries-header-label">Status</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter" data-col="status"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="date" class="inquiries-header-cell"><span class="inquiries-header-label">Date</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter" data-col="date"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $r)
                        @php
                            $dateStr = $r->CREATIONDATE ? date('Y-m-d H:i', strtotime($r->CREATIONDATE)) : '';
                            $searchHaystack = strtolower(($r->LEAD_ACTID ?? '').' '.($r->LEADID ?? '').' '.($r->USERID ?? '').' '.($r->SUBJECT ?? '').' '.($r->STATUS ?? '').' '.$dateStr);
                        @endphp
                        <tr class="history-row inquiry-row" data-search="{{ $searchHaystack }}">
                            <td data-col="id">{{ $r->LEAD_ACTID }}</td>
                            <td data-col="lead">{{ $r->LEADID }}</td>
                            <td data-col="user">{{ $r->USERID }}</td>
                            <td data-col="subject">{{ $r->SUBJECT ?? '—' }}</td>
                            <td data-col="status">{{ $r->STATUS ?? '—' }}</td>
                            <td data-col="date">{{ $dateStr ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6">No activities yet.</td></tr>
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
    function applyTableFilter() {
        var searchInput = document.getElementById('historySearchInput');
        var q = (searchInput && searchInput.value) ? searchInput.value.toLowerCase().trim() : '';
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
            row.style.display = (searchMatch && colMatch) ? '' : 'none';
        });
    }
    var searchInput = document.getElementById('historySearchInput');
    if (searchInput) searchInput.addEventListener('input', applyTableFilter);
    table.querySelectorAll('thead .inquiries-grid-filter').forEach(function(inp) {
        inp.addEventListener('input', applyTableFilter);
    });
});
</script>
@endpush
