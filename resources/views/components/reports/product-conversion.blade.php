@props([
    'productConversionDisplay',
    'desktopWrapperId',
    'desktopChartId',
    'mobileWrapperId',
    'mobileChartId',
])

<div class="reports-product-card">
    @if ($productConversionDisplay->isEmpty())
        <p class="reports-product-empty">No closed cases this month yet.</p>
    @else
        @php
            $itemCount = $productConversionDisplay->count();
            $barHeightPx = 20;
            $gapPx = 10;
            $paddingPx = 44;
            $chartHeightPx = max(220, $itemCount * ($barHeightPx + $gapPx) + $paddingPx);
            $mobileChartWidthPx = max(750, $itemCount * 110);
        @endphp
        <div class="reports-product-chart-desktop-wrapper" id="{{ $desktopWrapperId }}" style="height: {{ $chartHeightPx }}px;">
            <p class="reports-product-chart-fallback">Unable to load product conversion chart.</p>
            <canvas id="{{ $desktopChartId }}"></canvas>
        </div>
        
        <div class="dealer-reports-chart-scroll-wrapper reports-product-chart-mobile-wrapper" style="display: none;">
            <div class="reports-product-chart-wrapper" id="{{ $mobileWrapperId }}" style="width: {{ $mobileChartWidthPx }}px; min-width: {{ $mobileChartWidthPx }}px; height: 300px;">
                <p class="reports-product-chart-fallback">Unable to load product conversion chart.</p>
                <canvas id="{{ $mobileChartId }}"></canvas>
            </div>
        </div>
    @endif
</div>
