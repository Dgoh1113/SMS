@extends('layouts.app')
@section('title', 'Inquiries – Admin')
@section('content')
<header class="dashboard-header">
    <div>
        <h1 class="dashboard-title">Customer Inquiries</h1>
        <p class="dashboard-subtitle">Recent inquiries (latest 100)</p>
    </div>
</header>

<section class="dashboard-panel dashboard-table-panel">
    <div class="dashboard-panel-body">
        <div class="table-responsive">
            <table class="dashboard-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Company</th>
                        <th>Contact</th>
                        <th>Email</th>
                        <th>City</th>
                        <th>Resolved</th>
                        <th>Submitted</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $r)
                        <tr>
                            <td>{{ $r->CustomerInquiryID }}</td>
                            <td>{{ $r->CompanyName }}</td>
                            <td>{{ $r->ContactName }}</td>
                            <td>{{ $r->EmailAddress }}</td>
                            <td>{{ $r->City ?? '—' }}</td>
                            <td>{{ $r->IsResolved ? 'Yes' : 'No' }}</td>
                            <td>{{ $r->SubmittedAt ? date('Y-m-d H:i', strtotime($r->SubmittedAt)) : '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7">No inquiries yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
@endsection
