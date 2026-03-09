@extends('layouts.app')
@section('title', 'Dashboard – Dealer')
@section('content')
<header class="dashboard-header">
    <div>
        <h1 class="dashboard-title">Dashboard</h1>
        <p class="dashboard-subtitle">Your deals overview</p>
    </div>
</header>

<section class="dashboard-panel dashboard-table-panel">
    <div class="dashboard-panel-header">
        <div class="dashboard-panel-title">My Deals</div>
    </div>
    <div class="dashboard-panel-body">
        <div class="table-responsive">
            <table class="dashboard-table">
                <thead>
                    <tr>
                        <th>Deal ID</th>
                        <th>Client Lead</th>
                        <th>Status</th>
                        <th>Revenue (RM)</th>
                        <th>Assigned</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($deals as $r)
                        <tr>
                            <td>{{ $r->DealsSubmissionID }}</td>
                            <td>{{ $r->ClientsLeadID }}</td>
                            <td>{{ $r->PipelineStatus }}</td>
                            <td>{{ number_format($r->ExpectedTotalRevenueRM ?? 0, 2) }}</td>
                            <td>{{ $r->DateAssigned ? date('Y-m-d', strtotime($r->DateAssigned)) : '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5">No deals assigned yet. Demo user may see no data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
@endsection
