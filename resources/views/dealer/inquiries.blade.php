@extends('layouts.app')
@section('title', 'My Inquiries – SQL LMS Dealer Console')
@section('content')
<div class="dashboard-content">
    <header class="dashboard-header">
        <div>
            <h1 class="dashboard-title">My Inquiries</h1>
            <p class="dashboard-subtitle">Your assigned leads and inquiries</p>
        </div>
    </header>

    <section class="dashboard-panel dashboard-table-panel">
        <div class="dashboard-panel-body">
            <div class="table-responsive">
                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th>Inquiry ID</th>
                            <th>Company</th>
                            <th>Contact</th>
                            <th>Status</th>
                            <th>Last Modified</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($leads as $r)
                            <tr>
                                <td><strong>#LX-{{ $r->LEADID }}</strong></td>
                                <td>{{ $r->COMPANYNAME }}</td>
                                <td>{{ $r->CONTACTNAME ?? '—' }}</td>
                                <td>{{ $r->CURRENTSTATUS ?? '—' }}</td>
                                <td>{{ $r->LASTMODIFIED ? date('M j, Y', strtotime($r->LASTMODIFIED)) : '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5">No inquiries assigned yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>
@endsection
