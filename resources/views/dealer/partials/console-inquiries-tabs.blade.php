@php
    $dealerConsoleTab = $dealerConsoleTab ?? 'inquiries';
    $counts = $dealerConsoleCounts ?? [];
    
    // Normalize keys from counts array
    $pendingCount = $counts['pending'] ?? 0;
    $followupCount = $counts['followup'] ?? 0;
    $demoCount = $counts['demo'] ?? 0;
    $confirmedCount = $counts['confirmed'] ?? 0;
    $completedCount = $counts['completed'] ?? 0;
    $rewardedCount = $counts['rewarded'] ?? 0;
    $payoutsCount = $counts['pending_payouts'] ?? 0;
    $cancelledCount = $counts['cancelled'] ?? 0;
    $failedCount = $counts['failed'] ?? 0;
    $totalCount = $counts['inquiries'] ?? 0;

    $tabs = [
        ['id' => 'pending', 'label' => 'Pending', 'count' => $pendingCount, 'tooltip' => 'Leads in Pending/Assigned status.'],
        ['id' => 'followup', 'label' => 'Follow Up', 'count' => $followupCount, 'tooltip' => 'Leads in Follow Up status.'],
        ['id' => 'demo', 'label' => 'Demo', 'count' => $demoCount, 'tooltip' => 'Leads in Demo status.'],
        ['id' => 'confirmed', 'label' => 'Confirmed', 'count' => $confirmedCount, 'tooltip' => 'Leads in Confirmed status.'],
        ['id' => 'completed', 'label' => 'Completed', 'count' => $completedCount, 'tooltip' => 'Leads in Completed status.'],
        ['id' => 'rewarded', 'label' => 'Rewarded', 'count' => $rewardedCount, 'tooltip' => 'Leads in Rewarded status.'],
        ['id' => 'pending-payouts', 'label' => 'Pending Payouts', 'count' => $payoutsCount, 'tooltip' => 'Total completed leads awaiting referral fee payouts.', 'route' => 'dealer.payouts'],
        ['id' => 'cancelled', 'label' => 'Cancelled', 'count' => $cancelledCount, 'tooltip' => 'Leads in Cancelled status.'],
        ['id' => 'failed', 'label' => 'Failed', 'count' => $failedCount, 'tooltip' => 'Leads in Failed status.'],
        ['id' => 'inquiries', 'label' => 'My Inquiries', 'count' => $totalCount, 'tooltip' => 'Total active leads assigned to you.'],
    ];
@endphp

<nav class="dealer-console-tabs" aria-label="Dealer inquiry sections">
    <div class="dealer-console-tabs-inner">
        @foreach($tabs as $tabItem)
            @php
                $isActive = ($dealerConsoleTab === $tabItem['id']);
                $showBadge = ($tabItem['count'] > 0);
            @endphp
            <a href="{{ isset($tabItem['route']) ? route($tabItem['route']) : route('dealer.inquiries', ['tab' => $tabItem['id']]) }}"
               class="dealer-console-tab {{ $isActive ? 'dealer-console-tab-active' : '' }}"
               aria-current="{{ $isActive ? 'page' : 'false' }}">
                <span class="inquiries-tab-label">
                    {{ $tabItem['label'] }}
                    @if($showBadge)
                        <span class="inquiries-tab-count"
                              title="{{ $tabItem['tooltip'] }}"
                              aria-label="{{ $tabItem['label'] }} count: {{ number_format($tabItem['count']) }}. {{ $tabItem['tooltip'] }}">{{ number_format($tabItem['count']) }}</span>
                    @endif
                </span>
            </a>
        @endforeach
    </div>
</nav>
