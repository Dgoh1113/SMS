@extends('layouts.app')
@section('title', 'Dealers – Admin')
@section('content')
<header class="dashboard-header">
    <div>
        <h1 class="dashboard-title">Dealers</h1>
        <p class="dashboard-subtitle">All dealers</p>
    </div>
</header>

<section class="dashboard-panel dashboard-table-panel">
    <div class="dashboard-panel-body">
        <div class="table-responsive">
            <table class="dashboard-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User ID</th>
                        <th>Name</th>
                        <th>Code</th>
                        <th>Bank</th>
                        <th>Join Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $r)
                        <tr>
                            <td>{{ $r->DealerID }}</td>
                            <td>{{ $r->UserID }}</td>
                            <td>{{ $r->DealerName }}</td>
                            <td>{{ $r->DealerCode }}</td>
                            <td>{{ $r->BankName ?? '—' }}</td>
                            <td>{{ $r->JoinDate ? date('Y-m-d', strtotime($r->JoinDate)) : '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6">No dealers yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
@endsection
