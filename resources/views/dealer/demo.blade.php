@extends('layouts.app')
@section('title', 'Demo Schedule – Dealer')
@section('content')
<header class="dashboard-header">
    <div>
        <h1 class="dashboard-title">Demo Schedule</h1>
        <p class="dashboard-subtitle">Your demos</p>
    </div>
</header>

<section class="dashboard-panel dashboard-table-panel">
    <div class="dashboard-panel-body">
        <div class="table-responsive">
            <table class="dashboard-table">
                <thead>
                    <tr>
                        <th>Deal ID</th>
                        <th>Client Lead</th>
                        <th>Status</th>
                        <th>Demo completed</th>
                        <th>Demo date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($deals as $r)
                        <tr>
                            <td>{{ $r->DealsSubmissionID }}</td>
                            <td>{{ $r->ClientsLeadID }}</td>
                            <td>{{ $r->PipelineStatus }}</td>
                            <td>{{ $r->IsDemoCompleted ? 'Yes' : 'No' }}</td>
                            <td>{{ $r->DemoDate ? date('Y-m-d', strtotime($r->DemoDate)) : '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5">No demo records. Demo user may see no data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
@endsection
