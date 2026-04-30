@php
    $dealerConsoleTab = $dealerConsoleTab ?? 'inquiries';
    $counts = $dealerConsoleCounts ?? [];
    
    $tabs = [
        ['id' => 'pending', 'label' => 'Pending', 'count' => $counts['pending'] ?? 0, 'tooltip' => 'Leads in Pending/Assigned status.'],
        ['id' => 'followup', 'label' => 'Follow Up', 'count' => $counts['followup'] ?? 0, 'tooltip' => 'Leads in Follow Up status.'],
        ['id' => 'demo', 'label' => 'Demo', 'count' => $counts['demo'] ?? 0, 'tooltip' => 'Leads in Demo status.'],
        ['id' => 'confirmed', 'label' => 'Confirmed', 'count' => $counts['confirmed'] ?? 0, 'tooltip' => 'Leads in Confirmed status.'],
        ['id' => 'completed', 'label' => 'Completed', 'count' => $counts['completed'] ?? 0, 'tooltip' => 'Leads in Completed status.'],
        ['id' => 'rewarded', 'label' => 'Rewarded', 'count' => $counts['rewarded'] ?? 0, 'tooltip' => 'Leads in Rewarded status.'],
        ['id' => 'pending-payouts', 'label' => 'Pending Payouts', 'count' => $counts['pending_payouts'] ?? 0, 'tooltip' => 'Total completed leads awaiting referral fee payouts.', 'route' => 'dealer.payouts'],
        ['id' => 'cancelled', 'label' => 'Cancelled', 'count' => $counts['cancelled'] ?? 0, 'tooltip' => 'Leads in Cancelled status.'],
        ['id' => 'failed', 'label' => 'Failed', 'count' => $counts['failed'] ?? 0, 'tooltip' => 'Leads in Failed status.'],
        ['id' => 'inquiries', 'label' => 'My Inquiries', 'count' => $counts['inquiries'] ?? 0, 'tooltip' => 'Total active leads assigned to you.'],
    ];
@endphp
<nav class="dealer-console-tabs" aria-label="Dealer inquiry sections" style="overflow-x: auto; white-space: nowrap; padding-bottom: 5px;">
    @foreach($tabs as $tabItem)
        <a href="{{ isset($tabItem['route']) ? route($tabItem['route']) : route('dealer.inquiries', ['tab' => $tabItem['id']]) }}"
           class="dealer-console-tab {{ $dealerConsoleTab === $tabItem['id'] ? 'dealer-console-tab-active' : '' }}"
           aria-current="{{ $dealerConsoleTab === $tabItem['id'] ? 'page' : 'false' }}">
            <span class="inquiries-tab-label">
                {{ $tabItem['label'] }}
                @php
                    $hideBadge = ($tabItem['count'] == 0);
                @endphp
                @if(!$hideBadge)
                    <span class="inquiries-tab-count"
                          title="{{ $tabItem['tooltip'] }}"
                          aria-label="{{ $tabItem['label'] }} count: {{ number_format($tabItem['count']) }}. {{ $tabItem['tooltip'] }}">{{ number_format($tabItem['count']) }}</span>
                @endif
            </span>
        </a>
    @endforeach
</nav>
