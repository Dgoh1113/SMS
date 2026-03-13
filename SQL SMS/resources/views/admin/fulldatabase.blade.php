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
<section class="dashboard-panel dashboard-table-panel" style="margin-bottom:24px;">
    <div class="dashboard-panel-header">
        <div class="dashboard-panel-title">{{ $name }}</div>
    </div>
    <div class="dashboard-panel-body">
        <div class="table-responsive">
            <table class="dashboard-table">
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
                        <tr>
                            @foreach((array) $r as $v)
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
