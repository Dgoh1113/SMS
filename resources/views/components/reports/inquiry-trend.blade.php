@props([
    'hasData',
    'emptyMessage',
    'chartId',
    'wrapperId',
    'legendId',
    'currentRangeDays' => 30,
])

<div class="dealer-reports-card">
    @if (!$hasData)
        <p class="dealer-reports-empty">{{ $emptyMessage }}</p>
    @else
        <div class="dealer-reports-chart-scroll-wrapper">
            <div class="dealer-reports-chart-wrapper" id="{{ $wrapperId }}" style="height: 272px;">
                <p class="dealer-reports-chart-fallback">Unable to load inquiry trend chart.</p>
                <canvas id="{{ $chartId }}"></canvas>
            </div>
        </div>

        <div class="admin-inquiry-trend-legend" id="{{ $legendId }}" style="justify-content: center; gap: 20px; margin-top: 8px;">
            <button class="admin-inquiry-trend-legend-button" data-dataset-index="0" type="button">
                <span class="admin-inquiry-trend-legend-dot admin-inquiry-trend-legend-dot--trend"></span>
                <span>This {{ $currentRangeDays }} Days</span>
            </button>
            <button class="admin-inquiry-trend-legend-button" data-dataset-index="1" type="button">
                <span class="admin-inquiry-trend-legend-dot admin-inquiry-trend-legend-dot--ma"></span>
                <span>Previous {{ $currentRangeDays }} Days</span>
            </button>
        </div>
    @endif
</div>
