@extends('layouts.app')
@section('title', 'Full Database – Admin')
@section('content')
<header class="dashboard-header">
    <div>
        <h1 class="dashboard-title">Full Database</h1>
        <p class="dashboard-subtitle">All tables (read-only view)</p>
    </div>
</header>

@foreach($tables as $name => $rows)
@php
    // Normalize display names so they match the actual Firebird table names
    $displayNames = [
        'lead' => 'LEAD',
        'lead_act' => 'LEAD_ACT',
        'referrer_payout' => 'REFERRER_PAYOUT',
        'users' => 'USERS',
        'user_passkey' => 'USER_PASSKEY',
    ];
    $displayName = $displayNames[$name] ?? strtoupper($name);
    $tableId = 'fulldb-' . preg_replace('/[^a-z0-9]/i', '-', $displayName);
@endphp
<section class="dashboard-panel dashboard-table-panel" style="margin-bottom:24px;">
    <div class="dashboard-panel-header" style="flex-wrap:wrap; gap:12px;">
        <div class="dashboard-panel-title">{{ $displayName }}</div>
        <div class="inquiries-search-wrap" style="margin-left:auto; max-width:280px;">
            <span class="inquiries-search-icon"><i class="bi bi-search"></i></span>
            <input type="text" class="inquiries-search-input" data-fulldb-table="{{ $tableId }}" placeholder="Search this table..." autocomplete="off">
        </div>
    </div>
    <div class="dashboard-panel-body">
        <div class="table-responsive">
            <table class="dashboard-table inquiries-table" id="{{ $tableId }}">
                <thead>
                    <tr>
                        @if (count($rows) > 0)
                            @foreach((array) $rows[0] as $col => $v)
                                <th>{{ $col }}</th>
                            @endforeach
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $r)
                        @php
                            $cells = (array) $r;
                            $searchHaystack = strtolower(implode(' ', array_map(function($v) {
                                if (is_object($v) || is_array($v)) return json_encode($v);
                                $s = (string) $v;
                                return strlen($s) > 50 ? substr($s, 0, 50) : $s;
                            }, $cells)));
                        @endphp
                        <tr class="fulldb-row inquiry-row" data-search="{{ $searchHaystack }}">
                            @foreach($cells as $v)
                                <td>{{ is_object($v) || is_array($v) ? json_encode($v) : (strlen((string)$v) > 50 ? substr($v, 0, 50) . '…' : $v) }}</td>
                            @endforeach
                        </tr>
                    @empty
                        <tr><td>No rows</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
@endforeach
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('input[data-fulldb-table]').forEach(function(inp) {
        var tableId = inp.getAttribute('data-fulldb-table');
        var table = document.getElementById(tableId);
        if (!table) return;
        inp.addEventListener('input', function() {
            var q = (inp.value || '').toLowerCase().trim();
            table.querySelectorAll('tbody tr.fulldb-row').forEach(function(row) {
                var hay = (row.getAttribute('data-search') || '').toLowerCase();
                row.style.display = !q || hay.indexOf(q) !== -1 ? '' : 'none';
            });
        });
    });
});
</script>
@endpush
