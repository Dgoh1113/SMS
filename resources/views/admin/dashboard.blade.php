@extends('layouts.app')
@section('title', 'Dashboard – Admin')
@section('content')
<section class="dashboard-metrics">
    <div class="dashboard-metric-card">
        <div class="dashboard-metric-icon dashboard-metric-icon-leads"><i class="bi bi-graph-up-arrow"></i></div>
        <div class="dashboard-metric-label">Total Leads</div>
        <div class="dashboard-metric-value-row">
            <div class="dashboard-metric-value">{{ number_format($totalLeads) }}</div>
            <span class="dashboard-metric-pill dashboard-metric-pill-up">↑12%</span>
        </div>
    </div>
    <div class="dashboard-metric-card">
        <div class="dashboard-metric-icon dashboard-metric-icon-closed"><i class="bi bi-cash-stack"></i></div>
        <div class="dashboard-metric-label">Total Closed</div>
        <div class="dashboard-metric-value-row">
            <div class="dashboard-metric-value">{{ number_format($totalClosed) }}</div>
            <span class="dashboard-metric-pill dashboard-metric-pill-up">↑8%</span>
        </div>
    </div>
    <div class="dashboard-metric-card">
        <div class="dashboard-metric-icon dashboard-metric-icon-inquiries"><i class="bi bi-inbox"></i></div>
        <div class="dashboard-metric-label">Active Inquiries</div>
        <div class="dashboard-metric-value-row">
            <div class="dashboard-metric-value">{{ number_format($activeInquiries) }}</div>
            <span class="dashboard-metric-pill dashboard-metric-pill-up">↑5%</span>
        </div>
    </div>
    <div class="dashboard-metric-card">
        <div class="dashboard-metric-icon dashboard-metric-icon-conversion"><i class="bi bi-percent"></i></div>
        <div class="dashboard-metric-label">Conversion Rate</div>
        <div class="dashboard-metric-value-row">
            <div class="dashboard-metric-value">{{ $conversionRate }}%</div>
            <span class="dashboard-metric-pill dashboard-metric-pill-up">↑2.4%</span>
        </div>
    </div>
</section>

<section class="dashboard-charts-container">
    <div class="dashboard-charts-container-header">
        <div class="dashboard-chart-tabs" id="closedCaseRangeTabs">
            <button type="button" class="dashboard-chart-tab active" data-range="week">Week</button>
            <button type="button" class="dashboard-chart-tab" data-range="month">Month</button>
            <button type="button" class="dashboard-chart-tab" data-range="year">Year</button>
        </div>
    </div>
    <div class="dashboard-row">
        <div class="dashboard-panel dashboard-chart-panel">
            <div class="dashboard-panel-header">
                <div class="dashboard-panel-title">
                    Closed Case
                    <i class="bi bi-info-circle dashboard-info-icon"
                       title="Count of leads turned into closed cases grouped by creation date (week/month/year)."></i>
                </div>
            </div>
            <div class="dashboard-panel-body">
                <div class="dashboard-chart-container">
                    <canvas id="closedCaseChart" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="dashboard-panel dashboard-chart-panel">
            <div class="dashboard-panel-header">
                <div class="dashboard-panel-title">
                    Recent Referral Activity
                    <i class="bi bi-info-circle dashboard-info-icon"
                       title="Count of leads turning into Follow-Up status by dealer (weekly/monthly/yearly)."></i>
                </div>
                <span class="dashboard-metric-pill dashboard-metric-pill-up">+14% vs last week</span>
            </div>
            <div class="dashboard-panel-body">
                <div class="dashboard-chart-container">
                    <canvas id="referralChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="dashboard-panel dashboard-table-panel">
    <div class="dashboard-panel-header">
        <div class="dashboard-panel-title">Top Active Dealers</div>
        <a href="{{ route('admin.dealers') }}" class="dashboard-panel-link">View All Dealers</a>
    </div>
    <div class="dashboard-panel-body">
        <div class="table-responsive">
            <table class="dashboard-table">
                <thead>
                    <tr>
                        <th>Dealer Name</th>
                        <th>Location</th>
                        <th>Leads</th>
                        <th>Closed</th>
                        <th>Failed</th>
                        <th>Conversion</th>
                        <th>Avg. Closing Time</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topDealers as $d)
                        <tr>
                            <td>{{ $d['dealer_name'] }}</td>
                            <td>{{ $d['location'] }}</td>
                            <td>{{ number_format($d['total_leads']) }}</td>
                            <td>{{ number_format($d['closed_count']) }}</td>
                            <td>{{ number_format($d['failed_count'] ?? 0) }}</td>
                            @php
                                $cr = (float) ($d['conversion_rate'] ?? 0);
                                $crLabel = $cr >= 60 ? 'High' : ($cr >= 40 ? 'Medium' : 'Low');
                                $crClass = $cr >= 60 ? 'dashboard-badge-green' : ($cr >= 40 ? 'dashboard-badge-orange' : 'dashboard-badge-red');
                            @endphp
                            <td>
                                <span class="dashboard-badge {{ $crClass }}">
                                    <span class="dashboard-badge-dot"></span>
                                    <span class="dashboard-badge-text">{{ rtrim(rtrim(number_format($cr, 1), '0'), '.') }}% {{ $crLabel }}</span>
                                </span>
                            </td>
                            <td>{{ $d['avg_closing_time'] }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7">No dealer data yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx1 = document.getElementById('closedCaseChart')?.getContext('2d');
    const ctx2 = document.getElementById('referralChart')?.getContext('2d');
    const weekLabels = @json($chartLabels);
    const weekData = @json($chartData);
    const monthLabels = @json($chartMonthLabels);
    const monthData = @json($chartMonthData);
    const yearLabels = @json($chartYearLabels);
    const yearData = @json($chartYearData);
    const referralWeekData = @json($referralWeekData);
    const referralMonthData = @json($referralMonthData);
    const referralYearData = @json($referralYearData);

    const ranges = {
        week: { labels: weekLabels, data: weekData },
        month: { labels: monthLabels, data: monthData },
        year: { labels: yearLabels, data: yearData },
    };

    let closedChart = null;
    let referralChart = null;

    if (ctx1 && weekData) {
        closedChart = new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: weekLabels,
                datasets: [{
                    label: 'Closed',
                    data: weekData,
                    backgroundColor: 'rgba(127, 90, 240, 0.6)',
                    borderColor: 'rgba(127, 90, 240, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    }

    if (ctx2 && referralWeekData) {
        referralChart = new Chart(ctx2, {
            type: 'line',
            data: {
                labels: weekLabels,
                datasets: [{
                    label: 'Activity',
                    data: referralWeekData,
                    borderColor: 'rgba(127, 90, 240, 1)',
                    backgroundColor: 'rgba(127, 90, 240, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    }

    document.querySelectorAll('#closedCaseRangeTabs .dashboard-chart-tab[data-range]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const range = btn.getAttribute('data-range');
            if (!range || !ranges[range]) return;

            document.querySelectorAll('#closedCaseRangeTabs .dashboard-chart-tab[data-range]').forEach((b) => b.classList.remove('active'));
            btn.classList.add('active');

            const refMap = { week: referralWeekData, month: referralMonthData, year: referralYearData };
            const refData = refMap[range] ?? referralWeekData;

            if (closedChart) {
                closedChart.data.labels = ranges[range].labels;
                closedChart.data.datasets[0].data = ranges[range].data;
                closedChart.update();
            }
            if (referralChart) {
                referralChart.data.labels = ranges[range].labels;
                referralChart.data.datasets[0].data = refData;
                referralChart.update();
            }
        });
    });
});
</script>
@endpush
@endsection
