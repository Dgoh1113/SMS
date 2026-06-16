@props([
    'statusReportData',
    'statusGradient',
    'statusGradientDark',
])

<div class="dashboard-panel-body report-status-body">
    <div class="dealer-reports-status-card">
        <div class="report-donut-wrapper">
            <div class="report-donut"
                 data-light-gradient="{{ $statusGradient ?: '#e5e7eb 0 100%' }}"
                 data-dark-gradient="{{ $statusGradientDark ?: '#334155 0 100%' }}"
                 style="background: conic-gradient({{ $statusGradient ?: '#e5e7eb 0 100%' }});">
                <div class="report-donut-center">
                    <div class="report-donut-total">{{ array_sum(array_column($statusReportData, 'value')) }}</div>
                    <div class="report-donut-label">Activities</div>
                </div>
            </div>
        </div>
    </div>
    <ul class="report-legend">
        @foreach ($statusReportData as $item)
            <li>
                <span class="report-legend-color{{ ($item['label'] ?? '') === 'Failed' ? ' report-legend-color--failed' : '' }}"
                      data-light-color="{{ $item['color'] ?? '#e5e7eb' }}"
                      data-dark-color="{{ $item['dark_color'] ?? ($item['color'] ?? '#e5e7eb') }}"
                      style="background-color: {{ $item['color'] ?? '#e5e7eb' }}"></span>
                <span class="report-legend-label">{{ $item['label'] }}</span>
                <span class="report-legend-value">{{ $item['value'] }}</span>
            </li>
        @endforeach
    </ul>
</div>
