@extends('layouts.app')
@section('title', 'History – SQL LMS Dealer Console')
@section('content')
<div class="dashboard-content">
    <header class="dashboard-header">
        <div>
            <h1 class="dashboard-title">History</h1>
            <p class="dashboard-subtitle">Lead activity history</p>
        </div>
    </header>

    <section class="dashboard-panel dashboard-table-panel">
        <div class="dashboard-panel-body">
            <div class="table-responsive">
                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th>Activity ID</th>
                            <th>Lead ID</th>
                            <th>Date</th>
                            <th>Subject</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(($activities ?? []) as $a)
                            <tr>
                                <td>{{ $a->LEAD_ACTID }}</td>
                                <td>{{ $a->LEADID }}</td>
                                <td>{{ $a->CREATIONDATE ? date('M j, Y H:i', strtotime($a->CREATIONDATE)) : '—' }}</td>
                                <td>{{ $a->SUBJECT ?? '—' }}</td>
                                <td>{{ $a->STATUS ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5">No activity history yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>
@endsection
