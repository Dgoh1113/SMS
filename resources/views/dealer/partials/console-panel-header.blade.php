@php
    $tabIcons = [
        'inquiries' => 'bi-folder2-open',
        'pending' => 'bi-hourglass-split',
        'followup' => 'bi-calendar-event',
        'demo' => 'bi-person-video2',
        'confirmed' => 'bi-check-circle',
        'completed' => 'bi-box-seam',
        'failed' => 'bi-x-circle',
        'cancelled' => 'bi-slash-circle',
        'rewarded' => 'bi-gift',
        'pending-payouts' => 'bi-piggy-bank',
    ];

    $tabLabels = [
        'inquiries' => 'My Inquiries',
        'pending' => 'Pending',
        'followup' => 'Follow Up',
        'demo' => 'Demo',
        'confirmed' => 'Confirmed',
        'completed' => 'Completed',
        'failed' => 'Failed',
        'cancelled' => 'Cancelled',
        'rewarded' => 'Rewarded',
        'pending-payouts' => 'Pending Payouts',
    ];

    $currentTab = $dealerConsoleTab ?? 'inquiries';
    $icon = $tabIcons[$currentTab] ?? 'bi-folder2-open';
    $label = $tabLabels[$currentTab] ?? 'My Inquiries';
    
    // Normalize tab ID for counts array
    $countKey = str_replace('-', '_', $currentTab);
    $count = $dealerConsoleCounts[$countKey] ?? ($dealerConsoleCounts[$currentTab] ?? 0);
    
    // Fallback for payouts page specific count variables
    if ($currentTab === 'pending-payouts' && $count === 0 && isset($totalCompletedLeads)) {
        $count = $totalCompletedLeads;
    }
@endphp

<div class="inquiries-panel-header">
    <div class="inquiries-panel-title-wrap">
        <i class="bi {{ $icon }} inquiries-panel-icon"></i>
        <h2 class="inquiries-panel-title">{{ $label }} <span class="inquiries-title-count">({{ number_format($count) }})</span></h2>
    </div>
    <div class="inquiries-panel-actions">
        {!! $actions ?? '' !!}
    </div>
</div>
