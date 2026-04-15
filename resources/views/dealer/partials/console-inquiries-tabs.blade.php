@php
    $dealerConsoleTab = $dealerConsoleTab ?? 'inquiries';
    $dealerInquiryCount = (int) ($dealerInquiryCount ?? 0);
    $dealerPendingPayoutCount = (int) ($dealerPendingPayoutCount ?? 0);
    $dealerInquiryCountTooltip = 'Total active leads assigned to you.';
    $dealerPendingPayoutTooltip = 'Total completed leads awaiting referral fee payouts.';
@endphp
<nav class="dealer-console-tabs" aria-label="Dealer inquiry sections">
    <a href="{{ route('dealer.inquiries') }}"
       class="dealer-console-tab {{ $dealerConsoleTab === 'inquiries' ? 'dealer-console-tab-active' : '' }}"
       aria-current="{{ $dealerConsoleTab === 'inquiries' ? 'page' : 'false' }}">
        <span class="inquiries-tab-label">
            My Inquiries
            <span class="inquiries-tab-count"
                  title="{{ $dealerInquiryCountTooltip }}"
                  aria-label="My Inquiries count: {{ number_format($dealerInquiryCount) }}. {{ $dealerInquiryCountTooltip }}"
                  @if($dealerInquiryCount === 0) hidden aria-hidden="true" @endif>{{ number_format($dealerInquiryCount) }}</span>
        </span>
    </a>
    <a href="{{ route('dealer.payouts') }}"
       class="dealer-console-tab {{ $dealerConsoleTab === 'pending-payouts' ? 'dealer-console-tab-active' : '' }}"
       aria-current="{{ $dealerConsoleTab === 'pending-payouts' ? 'page' : 'false' }}">
        <span class="inquiries-tab-label">
            Pending Payouts
            <span class="inquiries-tab-count"
                  title="{{ $dealerPendingPayoutTooltip }}"
                  aria-label="Pending Payouts count: {{ number_format($dealerPendingPayoutCount) }}. {{ $dealerPendingPayoutTooltip }}"
                  @if($dealerPendingPayoutCount === 0) hidden aria-hidden="true" @endif>{{ number_format($dealerPendingPayoutCount) }}</span>
        </span>
    </a>
</nav>
