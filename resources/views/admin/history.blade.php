@extends('layouts.app')
@section('title', 'History – Admin')
@section('content')
<header class="dashboard-header">
    <div>
        <h1 class="dashboard-title">Deal Status History</h1>
        <p class="dashboard-subtitle">Recent status changes (latest 100)</p>
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
                        <th>Previous</th>
                        <th>New</th>
                        <th>Changed By</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $r)
                        <tr>
                            <td>{{ $r->DealHistoryStatusID }}</td>
                            <td>{{ $r->DealsSubmissionID }}</td>
                            <td>{{ $r->PreviousStatus }}</td>
                            <td>{{ $r->NewStatus }}</td>
                            <td>{{ $r->ChangedByID }}</td>
                            <td>{{ $r->ChangeDate ? date('Y-m-d H:i', strtotime($r->ChangeDate)) : '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6">No history yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
@endsection
