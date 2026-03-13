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
    <div class="dashboard-panel-body">
        <div class="table-responsive">
            <table class="dashboard-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Deal Submission</th>
                        <th>Dealer</th>
                        <th>Referrer</th>
                        <th>Status</th>
                        <th>Generated</th>
                        <th>Paid</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $r)
                        <tr>
                            <td>{{ $r->ReferrerPayoutID }}</td>
                            <td>{{ $r->DealsSubmissionID }}</td>
                            <td>{{ $r->DealerID }}</td>
                            <td>{{ $r->ReferrerID }}</td>
                            <td>{{ $r->Status }}</td>
                            <td>{{ $r->DateGenerated ? date('Y-m-d', strtotime($r->DateGenerated)) : '—' }}</td>
                            <td>{{ $r->DatePaid ? date('Y-m-d', strtotime($r->DatePaid)) : '—' }}</td>
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
