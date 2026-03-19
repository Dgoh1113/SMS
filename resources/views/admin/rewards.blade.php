@extends('layouts.app')
@section('title', 'Rewards - Admin')
@push('styles')
<style>
    /* Keep rewards columns content-sized so short values do not stretch too wide. */
    #completedTable,
    #rewardedTable {
        width: max-content;
        min-width: max-content;
        table-layout: auto;
    }
    #completedTable.rewards-default-layout,
    #rewardedTable.rewards-default-layout {
        width: 100%;
        min-width: 100%;
    }

    #completedPanel .inquiries-table-scroll.rewards-default-layout,
    #rewardedPanel .inquiries-table-scroll.rewards-default-layout {
        overflow-x: hidden;
    }

    /* Keep payouts panels closer to the Inquiries table height so the footer stays visible. */
    #completedPanel .inquiries-table-wrap,
    #rewardedPanel .inquiries-table-wrap {
        min-height: 380px;
    }

    #completedTable th[data-col="inquiryid"],
    #completedTable td[data-col="inquiryid"],
    #rewardedTable th[data-col="inquiryid"],
    #rewardedTable td[data-col="inquiryid"] {
        width: 96px;
        min-width: 96px;
    }

    #completedTable th.inquiries-col-action,
    #completedTable td.inquiries-col-action,
    #rewardedTable th.inquiries-col-action,
    #rewardedTable td.inquiries-col-action {
        position: sticky;
        right: 0;
        width: 140px;
        min-width: 140px;
        max-width: 140px;
    }

    #completedTable th.inquiries-col-action .inquiries-filter-clear,
    #rewardedTable th.inquiries-col-action .inquiries-filter-clear {
        white-space: nowrap;
    }
</style>
@endpush
@section('content')
<div class="inquiries-page-wrap">
<div class="inquiries-mgmt-top-row" style="margin-bottom: 16px;">
<section class="inquiries-mgmt-summary">
        <div class="inquiries-summary-card" id="payoutSummaryCard"
             data-pending-count="{{ number_format($totalCompletedLeads ?? 0) }}"
             data-rewarded-count="{{ number_format($totalRewardedLeads ?? 0) }}">
            <div class="inquiries-summary-icon"><i class="bi bi-coin" id="payoutSummaryIcon"></i></div>
            <div class="inquiries-summary-label" id="payoutSummaryLabel">PENDING REWARD</div>
            <div class="inquiries-summary-value-row">
                <span class="inquiries-summary-value" id="payoutSummaryValue">{{ number_format($totalCompletedLeads ?? 0) }}</span>
            </div>
            <div class="inquiries-summary-note" id="payoutSummaryNote">Pending Referral Payout</div>
        </div>
    </section>
    <section class="inquiries-mgmt-search">
        <div class="inquiries-search-row">
            <div class="inquiries-search-wrap">
                <span class="inquiries-search-icon"><i class="bi bi-search"></i></span>
                <input type="text" class="inquiries-search-input" id="payoutSearchInput" placeholder="Search by customer, company, or Inquiry ID..." autocomplete="off">
                <button type="button" class="inquiries-search-btn" id="payoutSearchBtn">Search</button>
            </div>
            <button type="button" class="inquiries-btn inquiries-btn-secondary inquiries-search-clear-btn" id="payoutClearSearchBtn" title="Clear search">Clear</button>
        </div>
    </section>
</div>

<div class="inquiries-tabs">
    <button type="button" class="inquiries-tab active" data-tab="completed" aria-selected="true">Completed</button>
    <button type="button" class="inquiries-tab" data-tab="rewarded" aria-selected="false">Rewarded</button>
</div>

<div class="inquiries-tab-panel active" id="completedPanel" role="tabpanel">
<section class="inquiries-mgmt-panel">
    <div class="inquiries-panel-header">
        <div class="inquiries-panel-title-wrap">
            <i class="bi bi-check2-circle inquiries-panel-icon"></i>
            <h2 class="inquiries-panel-title">Completed</h2>
        </div>
        <div class="inquiries-panel-actions">
            <button type="button" class="inquiries-btn inquiries-btn-secondary inquiries-sync-btn" data-sync-type="completed" data-sync-url="{{ route('admin.rewards') }}">
                <i class="bi bi-arrow-repeat inquiries-sync-icon"></i>
                <span class="inquiries-sync-label">Sync</span>
            </button>
            <div class="inquiries-columns-dropdown">
                <button type="button" class="inquiries-btn inquiries-btn-secondary" id="completedColumnsBtn" aria-haspopup="true" aria-expanded="false">Columns</button>
                <div class="inquiries-columns-menu" id="completedColumnsMenu" hidden>
                    <div class="inquiries-columns-menu-title">Show columns</div>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="inquiryid"> INQUIRY ID</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="date"> INQUIRY DATE</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="customername"> CUSTOMER NAME</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="source"> SOURCE</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="postcode"> POSTCODE</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="city"> CITY</label>
                                <label class="inquiries-columns-check"><input type="checkbox" data-col="completiondate"> COMPLETION DATE</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="address"> ADDRESS</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="contactno"> CONTACT NO</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="businessnature"> BUSINESS NATURE</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="users"> USERS</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="existingsw"> EXISTING SW</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="demomode"> DEMO MODE</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="products"> PRODUCTS</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="dealtproducts"> DEALT PRODUCTS</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="message"> MESSAGE</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="referralcode"> REFERRAL CODE</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="assignedby"> ASSIGNED BY</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="assignedto"> ASSIGNED TO</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="attachment"> ATTACHMENT</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="assigndate"> ASSIGN DATE</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="status"> STATUS</label>
                    <div class="inquiries-columns-actions">
                        <button type="button" class="inquiries-columns-action-btn" id="completedColumnsAll">All</button>
                        <button type="button" class="inquiries-columns-action-btn" id="completedColumnsNone">None</button>
                    </div>
                    <button type="button" class="inquiries-columns-reset" id="completedColumnsReset">Reset to default</button>
                </div>
            </div>
        </div>
    </div>
    <div class="inquiries-table-wrap">
        <div class="inquiries-table-scroll">
            <table class="inquiries-table" id="completedTable">
                <thead>
                    <tr class="inquiries-header-row">
                        <th data-col="inquiryid" class="inquiries-header-cell"><span class="inquiries-header-label">INQUIRY ID</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter rewards-grid-filter" data-table="completed" data-col="inquiryid"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="customername" class="inquiries-header-cell"><span class="inquiries-header-label">CUSTOMER NAME</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter rewards-grid-filter" data-table="completed" data-col="customername"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="assignedto" class="inquiries-header-cell"><span class="inquiries-header-label">ASSIGNED TO</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter rewards-grid-filter" data-table="completed" data-col="assignedto"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="referralcode" class="inquiries-header-cell"><span class="inquiries-header-label">REFERRAL CODE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter rewards-grid-filter" data-table="completed" data-col="referralcode"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                                <th data-col="completiondate" class="inquiries-header-cell"><span class="inquiries-header-label">COMPLETION DATE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter rewards-grid-filter" data-table="completed" data-col="completiondate"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="status" class="inquiries-header-cell"><span class="inquiries-header-label">STATUS</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter rewards-grid-filter" data-table="completed" data-col="status"><i class="bi bi-search inquiries-filter-icon"></i></span></th>

                        <th data-col="date" class="inquiries-header-cell"><span class="inquiries-header-label">INQUIRY DATE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter rewards-grid-filter" data-table="completed" data-col="date"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="source" class="inquiries-header-cell"><span class="inquiries-header-label">SOURCE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter rewards-grid-filter" data-table="completed" data-col="source"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="postcode" class="inquiries-header-cell"><span class="inquiries-header-label">POSTCODE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter rewards-grid-filter" data-table="completed" data-col="postcode"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="city" class="inquiries-header-cell"><span class="inquiries-header-label">CITY</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter rewards-grid-filter" data-table="completed" data-col="city"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="address" class="inquiries-header-cell"><span class="inquiries-header-label">ADDRESS</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter rewards-grid-filter" data-table="completed" data-col="address"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="contactno" class="inquiries-header-cell"><span class="inquiries-header-label">CONTACT NO</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter rewards-grid-filter" data-table="completed" data-col="contactno"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="businessnature" class="inquiries-header-cell"><span class="inquiries-header-label">BUSINESS NATURE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter rewards-grid-filter" data-table="completed" data-col="businessnature"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="users" class="inquiries-header-cell"><span class="inquiries-header-label">USERS</span><span class="inquiries-filter-wrap dealer-operator-search-wrap"><span class="dealer-operator-search-box"><button type="button" class="dealer-operator-btn" data-col="users" data-op="=" aria-haspopup="true" aria-expanded="false" title="Filter operator">=</button><div class="dealer-operator-dropdown" hidden><button type="button" data-op="=">= Equals</button><button type="button" data-op="!=">!= Does not equal</button><button type="button" data-op="<">&lt; Less than</button><button type="button" data-op="<=">&lt;= Less than or equal to</button><button type="button" data-op=">">&gt; Greater than</button><button type="button" data-op=">=">&gt;= Greater than or equal to</button></div><input type="text" class="inquiries-grid-filter rewards-grid-filter" data-table="completed" data-col="users" placeholder="0"></span></span></th>
                        <th data-col="existingsw" class="inquiries-header-cell"><span class="inquiries-header-label">EXISTING SW</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter rewards-grid-filter" data-table="completed" data-col="existingsw"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="demomode" class="inquiries-header-cell"><span class="inquiries-header-label">DEMO MODE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter rewards-grid-filter" data-table="completed" data-col="demomode"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="products" class="inquiries-header-cell"><span class="inquiries-header-label">PRODUCTS</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter rewards-grid-filter" data-table="completed" data-col="products"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="dealtproducts" class="inquiries-header-cell"><span class="inquiries-header-label">DEALT PRODUCTS</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter rewards-grid-filter" data-table="completed" data-col="dealtproducts"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="message" class="inquiries-header-cell"><span class="inquiries-header-label">MESSAGE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter rewards-grid-filter" data-table="completed" data-col="message"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="assignedby" class="inquiries-header-cell"><span class="inquiries-header-label">ASSIGNED BY</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter rewards-grid-filter" data-table="completed" data-col="assignedby"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="attachment" class="inquiries-header-cell"><span class="inquiries-header-label">ATTACHMENT</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter rewards-grid-filter" data-table="completed" data-col="attachment"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="assigndate" class="inquiries-header-cell"><span class="inquiries-header-label">ASSIGN DATE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter rewards-grid-filter" data-table="completed" data-col="assigndate"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th class="inquiries-col-action inquiries-header-cell"><span class="inquiries-header-label">ACTION</span><button type="button" class="inquiries-filter-clear" id="completedClearFilters">Clear filters</button></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($completed as $r)
                        @php
                            $ccompany = trim((string)($r->COMPANYNAME ?? ''));
                            $ccontact = trim((string)($r->CONTACTNAME ?? ''));
                            $custDisp = $ccompany !== '' && $ccontact !== ''
                                ? ($ccompany . ' - ' . $ccontact)
                                : ($ccompany !== '' ? $ccompany : ($ccontact !== '' ? $ccontact : '&mdash;'));
                            $addr1 = trim((string)($r->ADDRESS1 ?? ''));
                            $addr2 = trim((string)($r->ADDRESS2 ?? ''));
                            $addr = trim($addr1 . ' ' . $addr2);
                            $afullMsg = (string)($r->DESCRIPTION ?? '');
                            $afullMsgTrim = trim($afullMsg);
                            $amsgPreview = $afullMsgTrim === '' ? '-' : (mb_strlen($afullMsgTrim) > 30 ? (mb_substr($afullMsgTrim, 0, 30) . '...') : $afullMsgTrim);
                            $aisLongMsg = $afullMsgTrim !== '' && mb_strlen($afullMsgTrim) > 30;
                            $rawStatus = strtoupper(trim((string)($r->CURRENTSTATUS ?? '')));
                            $statusClass = 'inquiries-status-new';
                            switch ($rawStatus) {
                                case 'CREATED':   $statusClass = 'inquiries-status-created'; break;
                                case 'PENDING':   $statusClass = 'inquiries-status-pending'; break;
                                case 'FOLLOWUP':  $statusClass = 'inquiries-status-followup'; break;
                                case 'FOLLOW UP': $statusClass = 'inquiries-status-followup'; break;
                                case 'DEMO':      $statusClass = 'inquiries-status-demo'; break;
                                case 'CONFIRMED': $statusClass = 'inquiries-status-confirmed'; break;
                                case 'CASE CONFIRMED': $statusClass = 'inquiries-status-confirmed'; break;
                                case 'COMPLETED': $statusClass = 'inquiries-status-completed'; break;
                                case 'CASE COMPLETED': $statusClass = 'inquiries-status-completed'; break;
                                case 'REWARDED':  $statusClass = 'inquiries-status-rewarded'; break;
                                case 'REWARD DISTRIBUTED': $statusClass = 'inquiries-status-rewarded'; break;
                                case 'PAID':      $statusClass = 'inquiries-status-rewarded'; break;
                                case 'FAILED':    $statusClass = 'inquiries-status-failed'; break;
                                default:          $statusClass = 'inquiries-status-new'; break;
                            }
                            $statusDisp = $rawStatus !== '' ? $rawStatus : 'PENDING';
                            $assignDate = $r->LASTMODIFIED ? date('d/m/Y', strtotime($r->LASTMODIFIED)) : ($r->CREATEDAT ? date('d/m/Y', strtotime($r->CREATEDAT)) : '&mdash;');
                            $searchHaystack = strtolower(($r->COMPANYNAME ?? '').' '.($r->CONTACTNAME ?? '').' '.($r->LEADID ?? ''));
                            $productIds = $r->PRODUCTID ? array_map('trim', explode(',', (string)$r->PRODUCTID)) : [];
                            $pillOrder = [1=>10,3=>11,4=>12,2=>20,10=>21,8=>30,5=>31,6=>40,9=>50,7=>60,11=>70];
                            $dealtRaw = $r->DEALTPRODUCT ?? null;
                            $dealtProductIds = [];
                            if ($dealtRaw !== null && trim((string) $dealtRaw) !== '') {
                                $tokens = preg_split('/[,\s\(\)]+/', (string) $dealtRaw);
                                foreach ($tokens as $tok) {
                                    if ($tok === '') continue;
                                    $pid = (int) $tok;
                                    if ($pid >= 1 && $pid <= 11) {
                                        $dealtProductIds[] = $pid;
                                    }
                                }
                                $dealtProductIds = array_values(array_unique($dealtProductIds));
                                usort($dealtProductIds, function($a,$b) use ($pillOrder) { return ($pillOrder[$a] ?? 1000+$a) <=> ($pillOrder[$b] ?? 1000+$b); });
                            }
                            $productIds = array_values(array_filter(array_unique(array_map('intval', $productIds)), fn($v) => $v > 0));
                            usort($productIds, function($a,$b) use ($pillOrder) { return ($pillOrder[$a] ?? 1000+$a) <=> ($pillOrder[$b] ?? 1000+$b); });
                            $payoutAt = $r->REWARDED_AT ?? null;
                            $attachUrls = !empty($r->ASSIGNED_ATTACHMENT_URLS) && is_array($r->ASSIGNED_ATTACHMENT_URLS) ? $r->ASSIGNED_ATTACHMENT_URLS : [];
                        @endphp
                        <tr class="rewards-row inquiry-row" data-search="{{ $searchHaystack }}">
                            <td data-col="inquiryid">#SQL-{{ $r->LEADID }}</td>
                            <td data-col="customername">{{ $custDisp }}</td>
                            <td data-col="assignedto">{{ $r->ASSIGNED_TO_NAME ?? ($r->ASSIGNED_TO ?? '&mdash;') }}</td>
                            <td data-col="referralcode">{{ $r->REFERRALCODE ?? '&mdash;' }}</td>
                            <td data-col="completiondate">
                                @php
                                    $completedAt = $r->COMPLETED_AT ?? null;
                                @endphp
                                {{ $completedAt ? date('d/m/Y', strtotime($completedAt)) : '&mdash;' }}
                            </td>
                            <td data-col="status"><span class="inquiries-status {{ $statusClass }}">{{ $statusDisp }}</span></td>

                            <td data-col="date">{{ $r->CREATEDAT ? date('d/m/Y', strtotime($r->CREATEDAT)) : '&mdash;' }}</td>
                            <td data-col="source">{{ $r->CREATEDBY_NAME ?? ($r->CREATEDBY ?? '&mdash;') }}</td>
                            <td data-col="postcode">{{ $r->POSTCODE ?? '&mdash;' }}</td>
                            <td data-col="city">{{ $r->CITY ?? '&mdash;' }}</td>
                            <td data-col="address">{{ $addr !== '' ? $addr : '&mdash;' }}</td>
                            <td data-col="contactno">{{ $r->CONTACTNO ?? '&mdash;' }}</td>
                            <td data-col="businessnature">{{ $r->BUSINESSNATURE ?? '&mdash;' }}</td>
                            <td data-col="users">{{ $r->USERCOUNT ?? '&mdash;' }}</td>
                            <td data-col="existingsw">{{ $r->EXISTINGSOFTWARE ?? '&mdash;' }}</td>
                            <td data-col="demomode">{{ $r->DEMOMODE ?? '&mdash;' }}</td>
                            <td data-col="products">
                                @if(!empty($productIds))
                                    <div class="inquiries-pill-group">
                                        @foreach($productIds as $id)
                                            @if(isset($productLabels[(int)$id]))
                                                <span class="inquiries-pill inquiries-pill-p{{ (int)$id }}">{{ $productLabels[(int)$id] }}</span>
                                            @endif
                                        @endforeach
                                    </div>
                                @else
                                    &mdash;
                                @endif
                            </td>
                            <td data-col="dealtproducts">
                                @if(!empty($dealtProductIds))
                                    <div class="inquiries-pill-group">
                                        @foreach($dealtProductIds as $id)
                                            @if(isset($productLabels[(int)$id]))
                                                <span class="inquiries-pill inquiries-pill-p{{ (int)$id }}">{{ $productLabels[(int)$id] }}</span>
                                            @endif
                                        @endforeach
                                    </div>
                                @else
                                    &mdash;
                                @endif
                            </td>
                            <td data-col="message" class="inquiries-msg-cell {{ $aisLongMsg ? 'inquiries-msg-clickable' : '' }}" @if($aisLongMsg) data-full-message="{{ e($afullMsgTrim) }}" @endif>{{ $amsgPreview }}</td>
                                        <td data-col="assignedby">{{ $r->ASSIGNEDBY_NAME ?? ($r->ASSIGNEDBY ?? '—') }}</td>
                            <td data-col="attachment">
                                @if(!empty($attachUrls))
                                    <div class="payouts-attachment-list">
                                        @foreach(array_slice($attachUrls, 0, 3) as $u)
                                            <a href="{{ $u }}" target="_blank" rel="noopener" class="payouts-attachment-link">
                                                <img src="{{ $u }}" alt="Attachment" class="payouts-attachment-thumb">
                                            </a>
                                        @endforeach
                                        @if(count($attachUrls) > 3)
                                            <span class="payouts-attachment-more">+{{ count($attachUrls) - 3 }}</span>
                                        @endif
                                    </div>
                                @else
                                    &mdash;
                                @endif
                            </td>
                            <td data-col="assigndate">{{ $assignDate }}</td>
                            <td class="inquiries-col-action inquiries-action-cell">
                                <button type="button"
                                        class="inquiries-btn inquiries-btn-assign inquiries-view-status-btn"
                                        data-lead-id="{{ $r->LEADID }}"
                                        title="View Status"
                                        aria-label="View Status">
                                    <i class="bi bi-eye-fill" aria-hidden="true"></i>
                                </button>
                                <button type="button"
                                        class="inquiries-btn inquiries-btn-assign rewards-email-btn"
                                        data-lead-id="{{ $r->LEADID }}"
                                        title="Send Email"
                                        aria-label="Send Email">
                                    <i class="bi bi-envelope" aria-hidden="true"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="23" class="inquiries-empty">No completed inquiries.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="inquiries-assigned-pagination" id="completedPagination" data-per-page="10" data-current-page="1">
            <span class="inquiries-assigned-pagination-info" id="completedPaginationInfo">Showing 0 to 0 of 0 entries (Page 1)</span>
            <div class="inquiries-assigned-pagination-nav">
                <button type="button" class="inquiries-btn inquiries-btn-secondary inquiries-pagination-btn" id="completedPaginationFirst" aria-label="First page">First</button>
                <button type="button" class="inquiries-btn inquiries-btn-secondary inquiries-pagination-btn" id="completedPaginationPrev" aria-label="Previous page">Previous</button>
                <span class="inquiries-assigned-page-numbers" id="completedPageNumbers"></span>
                <button type="button" class="inquiries-btn inquiries-btn-secondary inquiries-pagination-btn" id="completedPaginationNext" aria-label="Next page">Next</button>
                <button type="button" class="inquiries-btn inquiries-btn-secondary inquiries-pagination-btn" id="completedPaginationLast" aria-label="Last page">Last</button>
        </div>
    </div>
</section>
</div>

<div class="inquiries-tab-panel" id="rewardedPanel" role="tabpanel" hidden>
<section class="inquiries-mgmt-panel">
    <div class="inquiries-panel-header">
        <div class="inquiries-panel-title-wrap">
            <i class="bi bi-gift inquiries-panel-icon"></i>
            <h2 class="inquiries-panel-title">Rewarded</h2>
        </div>
        <div class="inquiries-panel-actions">
            <button type="button" class="inquiries-btn inquiries-btn-secondary inquiries-sync-btn" data-sync-type="rewarded" data-sync-url="{{ route('admin.rewards') }}">
                <i class="bi bi-arrow-repeat inquiries-sync-icon"></i>
                <span class="inquiries-sync-label">Sync</span>
            </button>
            <div class="inquiries-columns-dropdown">
                <button type="button" class="inquiries-btn inquiries-btn-secondary" id="rewardedColumnsBtn" aria-haspopup="true" aria-expanded="false">Columns</button>
                <div class="inquiries-columns-menu" id="rewardedColumnsMenu" hidden>
                    <div class="inquiries-columns-menu-title">Show columns</div>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="inquiryid"> INQUIRY ID</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="date"> INQUIRY DATE</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="customername"> CUSTOMER NAME</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="source"> SOURCE</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="postcode"> POSTCODE</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="city"> CITY</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="address"> ADDRESS</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="contactno"> CONTACT NO</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="businessnature"> BUSINESS NATURE</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="users"> USERS</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="existingsw"> EXISTING SW</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="demomode"> DEMO MODE</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="products"> PRODUCTS</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="dealtproducts"> DEALT PRODUCTS</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="message"> MESSAGE</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="assignedby"> ASSIGNED BY</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="assignedto"> ASSIGNED TO</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="completiondate"> COMPLETION DATE</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="payoutsdate"> PAYOUTS DATE</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="attachment"> ATTACHMENT</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="referralcode"> REFERRAL CODE</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="assigndate"> ASSIGN DATE</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="status"> STATUS</label>
                    <div class="inquiries-columns-actions">
                        <button type="button" class="inquiries-columns-action-btn" id="rewardedColumnsAll">All</button>
                        <button type="button" class="inquiries-columns-action-btn" id="rewardedColumnsNone">None</button>
                    </div>
                    <button type="button" class="inquiries-columns-reset" id="rewardedColumnsReset">Reset to default</button>
                </div>
            </div>
        </div>
    </div>
    <div class="inquiries-table-wrap">
        <div class="inquiries-table-scroll">
            <table class="inquiries-table" id="rewardedTable">
                <thead>
                    <tr class="inquiries-header-row">
                        <th data-col="inquiryid" class="inquiries-header-cell"><span class="inquiries-header-label">INQUIRY ID</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter rewards-grid-filter" data-table="rewarded" data-col="inquiryid"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="date" class="inquiries-header-cell"><span class="inquiries-header-label">INQUIRY DATE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter rewards-grid-filter" data-table="rewarded" data-col="date"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="customername" class="inquiries-header-cell"><span class="inquiries-header-label">CUSTOMER NAME</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter rewards-grid-filter" data-table="rewarded" data-col="customername"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="source" class="inquiries-header-cell"><span class="inquiries-header-label">SOURCE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter rewards-grid-filter" data-table="rewarded" data-col="source"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="postcode" class="inquiries-header-cell"><span class="inquiries-header-label">POSTCODE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter rewards-grid-filter" data-table="rewarded" data-col="postcode"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="city" class="inquiries-header-cell"><span class="inquiries-header-label">CITY</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter rewards-grid-filter" data-table="rewarded" data-col="city"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="address" class="inquiries-header-cell"><span class="inquiries-header-label">ADDRESS</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter rewards-grid-filter" data-table="rewarded" data-col="address"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="contactno" class="inquiries-header-cell"><span class="inquiries-header-label">CONTACT NO</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter rewards-grid-filter" data-table="rewarded" data-col="contactno"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="businessnature" class="inquiries-header-cell"><span class="inquiries-header-label">BUSINESS NATURE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter rewards-grid-filter" data-table="rewarded" data-col="businessnature"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="users" class="inquiries-header-cell"><span class="inquiries-header-label">USERS</span><span class="inquiries-filter-wrap dealer-operator-search-wrap"><span class="dealer-operator-search-box"><button type="button" class="dealer-operator-btn" data-col="users" data-op="=" aria-haspopup="true" aria-expanded="false" title="Filter operator">=</button><div class="dealer-operator-dropdown" hidden><button type="button" data-op="=">= Equals</button><button type="button" data-op="!=">!= Does not equal</button><button type="button" data-op="<">&lt; Less than</button><button type="button" data-op="<=">&lt;= Less than or equal to</button><button type="button" data-op=">">&gt; Greater than</button><button type="button" data-op=">=">&gt;= Greater than or equal to</button></div><input type="text" class="inquiries-grid-filter rewards-grid-filter" data-table="rewarded" data-col="users" placeholder="0"></span></span></th>
                        <th data-col="existingsw" class="inquiries-header-cell"><span class="inquiries-header-label">EXISTING SW</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter rewards-grid-filter" data-table="rewarded" data-col="existingsw"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="demomode" class="inquiries-header-cell"><span class="inquiries-header-label">DEMO MODE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter rewards-grid-filter" data-table="rewarded" data-col="demomode"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="products" class="inquiries-header-cell"><span class="inquiries-header-label">PRODUCTS</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter rewards-grid-filter" data-table="rewarded" data-col="products"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="dealtproducts" class="inquiries-header-cell"><span class="inquiries-header-label">DEALT PRODUCTS</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter rewards-grid-filter" data-table="rewarded" data-col="dealtproducts"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="message" class="inquiries-header-cell"><span class="inquiries-header-label">MESSAGE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter rewards-grid-filter" data-table="rewarded" data-col="message"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="assignedby" class="inquiries-header-cell"><span class="inquiries-header-label">ASSIGNED BY</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter rewards-grid-filter" data-table="rewarded" data-col="assignedby"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="assignedto" class="inquiries-header-cell"><span class="inquiries-header-label">ASSIGNED TO</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter rewards-grid-filter" data-table="rewarded" data-col="assignedto"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="completiondate" class="inquiries-header-cell"><span class="inquiries-header-label">COMPLETION DATE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter rewards-grid-filter" data-table="rewarded" data-col="completiondate"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="payoutsdate" class="inquiries-header-cell"><span class="inquiries-header-label">PAYOUTS DATE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter rewards-grid-filter" data-table="rewarded" data-col="payoutsdate"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="attachment" class="inquiries-header-cell"><span class="inquiries-header-label">ATTACHMENT</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter rewards-grid-filter" data-table="rewarded" data-col="attachment"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="referralcode" class="inquiries-header-cell"><span class="inquiries-header-label">REFERRAL CODE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter rewards-grid-filter" data-table="rewarded" data-col="referralcode"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="assigndate" class="inquiries-header-cell"><span class="inquiries-header-label">ASSIGN DATE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter rewards-grid-filter" data-table="rewarded" data-col="assigndate"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="status" class="inquiries-header-cell"><span class="inquiries-header-label">STATUS</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter rewards-grid-filter" data-table="rewarded" data-col="status"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th class="inquiries-col-action inquiries-header-cell"><span class="inquiries-header-label">ACTION</span><button type="button" class="inquiries-filter-clear" id="rewardedClearFilters">Clear filters</button></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rewarded as $r)
                        @php
                            $ccompany = trim((string)($r->COMPANYNAME ?? ''));
                            $ccontact = trim((string)($r->CONTACTNAME ?? ''));
                            $custDisp = $ccompany !== '' && $ccontact !== ''
                                ? ($ccompany . ' - ' . $ccontact)
                                : ($ccompany !== '' ? $ccompany : ($ccontact !== '' ? $ccontact : '&mdash;'));
                            $addr1 = trim((string)($r->ADDRESS1 ?? ''));
                            $addr2 = trim((string)($r->ADDRESS2 ?? ''));
                            $addr = trim($addr1 . ' ' . $addr2);
                            $afullMsg = (string)($r->DESCRIPTION ?? '');
                            $afullMsgTrim = trim($afullMsg);
                            $amsgPreview = $afullMsgTrim === '' ? '-' : (mb_strlen($afullMsgTrim) > 30 ? (mb_substr($afullMsgTrim, 0, 30) . '...') : $afullMsgTrim);
                            $aisLongMsg = $afullMsgTrim !== '' && mb_strlen($afullMsgTrim) > 30;
                            $rawStatus = strtoupper(trim((string)($r->CURRENTSTATUS ?? '')));
                            $statusClass = 'inquiries-status-new';
                            switch ($rawStatus) {
                                case 'CREATED':   $statusClass = 'inquiries-status-created'; break;
                                case 'PENDING':   $statusClass = 'inquiries-status-pending'; break;
                                case 'FOLLOWUP':  $statusClass = 'inquiries-status-followup'; break;
                                case 'FOLLOW UP': $statusClass = 'inquiries-status-followup'; break;
                                case 'DEMO':      $statusClass = 'inquiries-status-demo'; break;
                                case 'CONFIRMED': $statusClass = 'inquiries-status-confirmed'; break;
                                case 'CASE CONFIRMED': $statusClass = 'inquiries-status-confirmed'; break;
                                case 'COMPLETED': $statusClass = 'inquiries-status-completed'; break;
                                case 'CASE COMPLETED': $statusClass = 'inquiries-status-completed'; break;
                                case 'REWARDED':  $statusClass = 'inquiries-status-rewarded'; break;
                                case 'REWARD DISTRIBUTED': $statusClass = 'inquiries-status-rewarded'; break;
                                case 'PAID':      $statusClass = 'inquiries-status-rewarded'; break;
                                case 'FAILED':    $statusClass = 'inquiries-status-failed'; break;
                                default:          $statusClass = 'inquiries-status-new'; break;
                            }
                            $statusDisp = $rawStatus !== '' ? $rawStatus : 'REWARDED';
                            $completedAt = $r->COMPLETED_AT ?? null;
                            $payoutAt = $r->REWARDED_AT ?? null;
                            $searchHaystack = strtolower(($r->COMPANYNAME ?? '').' '.($r->CONTACTNAME ?? '').' '.($r->LEADID ?? ''));
                            $assignDate = $r->LASTMODIFIED ? date('d/m/Y', strtotime($r->LASTMODIFIED)) : ($r->CREATEDAT ? date('d/m/Y', strtotime($r->CREATEDAT)) : '&mdash;');
                            $productIds = $r->PRODUCTID ? array_map('trim', explode(',', (string)$r->PRODUCTID)) : [];
                            $dealtRaw = $r->DEALTPRODUCT ?? null;
                            $dealtProductIds = $dealtRaw ? array_map('trim', preg_split('/[\s,\(\)]+/', (string)$dealtRaw)) : [];
                            $pillOrder = [1=>10,3=>11,4=>12,2=>20,10=>21,8=>30,5=>31,6=>40,9=>50,7=>60,11=>70];
                            $productIds = array_values(array_filter(array_unique(array_map('intval', $productIds)), fn($v) => $v > 0));
                            $dealtProductIds = array_values(array_filter(array_unique(array_map('intval', $dealtProductIds)), fn($v) => $v > 0));
                            usort($productIds, function($a,$b) use ($pillOrder) { return ($pillOrder[$a] ?? 1000+$a) <=> ($pillOrder[$b] ?? 1000+$b); });
                            usort($dealtProductIds, function($a,$b) use ($pillOrder) { return ($pillOrder[$a] ?? 1000+$a) <=> ($pillOrder[$b] ?? 1000+$b); });
                            $attachUrls = [];
                            if (!empty($r->REWARD_ATTACHMENT_URLS) && is_array($r->REWARD_ATTACHMENT_URLS)) {
                                $attachUrls = $r->REWARD_ATTACHMENT_URLS;
                            }
                            $referralCode = trim((string) ($r->REFERRALCODE ?? ''));
                        @endphp
                        <tr class="rewards-row inquiry-row" data-search="{{ $searchHaystack }}">
                            <td data-col="inquiryid">#SQL-{{ $r->LEADID }}</td>
                            <td data-col="date">{{ $r->CREATEDAT ? date('d/m/Y', strtotime($r->CREATEDAT)) : '&mdash;' }}</td>
                            <td data-col="customername">{{ $custDisp }}</td>
                            <td data-col="source">{{ $r->CREATEDBY_NAME ?? ($r->CREATEDBY ?? '&mdash;') }}</td>
                            <td data-col="postcode">{{ $r->POSTCODE ?? '&mdash;' }}</td>
                            <td data-col="city">{{ $r->CITY ?? '&mdash;' }}</td>
                            <td data-col="address">{{ $addr !== '' ? $addr : '&mdash;' }}</td>
                            <td data-col="contactno">{{ $r->CONTACTNO ?? '&mdash;' }}</td>
                            <td data-col="businessnature">{{ $r->BUSINESSNATURE ?? '&mdash;' }}</td>
                            <td data-col="users">{{ $r->USERCOUNT ?? '&mdash;' }}</td>
                            <td data-col="existingsw">{{ $r->EXISTINGSOFTWARE ?? '&mdash;' }}</td>
                            <td data-col="demomode">{{ $r->DEMOMODE ?? '&mdash;' }}</td>
                            <td data-col="products">
                                @if(!empty($productIds))
                                    <div class="inquiries-pill-group">
                                        @foreach($productIds as $id)
                                            @if(isset($productLabels[(int)$id]))
                                                <span class="inquiries-pill inquiries-pill-p{{ (int)$id }}">{{ $productLabels[(int)$id] }}</span>
                                            @endif
                                        @endforeach
                                    </div>
                                @else
                                    &mdash;
                                @endif
                            </td>
                            <td data-col="dealtproducts">
                                @if(!empty($dealtProductIds))
                                    <div class="inquiries-pill-group">
                                        @foreach($dealtProductIds as $id)
                                            @if(isset($productLabels[(int)$id]))
                                                <span class="inquiries-pill inquiries-pill-p{{ (int)$id }}">{{ $productLabels[(int)$id] }}</span>
                                            @endif
                                        @endforeach
                                    </div>
                                @else
                                    &mdash;
                                @endif
                            </td>
                            <td data-col="message" class="inquiries-msg-cell {{ $aisLongMsg ? 'inquiries-msg-clickable' : '' }}" @if($aisLongMsg) data-full-message="{{ e($afullMsgTrim) }}" @endif>{{ $amsgPreview }}</td>
                                        <td data-col="assignedby">{{ $r->ASSIGNEDBY_NAME ?? ($r->ASSIGNEDBY ?? '—') }}</td>
                            <td data-col="assignedto">{{ $r->ASSIGNED_TO_NAME ?? ($r->ASSIGNED_TO ?? '&mdash;') }}</td>
                            <td data-col="completiondate">{{ $completedAt ? date('d/m/Y', strtotime($completedAt)) : '&mdash;' }}</td>
                            <td data-col="payoutsdate">{{ $payoutAt ? date('d/m/Y', strtotime($payoutAt)) : '&mdash;' }}</td>
                            <td data-col="attachment">
                                @if(!empty($attachUrls))
                                    <div class="payouts-attachment-list">
                                        @foreach(array_slice($attachUrls, 0, 3) as $u)
                                            <a href="{{ $u }}" target="_blank" rel="noopener" class="payouts-attachment-link">
                                                <img src="{{ $u }}" alt="Attachment" class="payouts-attachment-thumb">
                                            </a>
                                        @endforeach
                                        @if(count($attachUrls) > 3)
                                            <span class="payouts-attachment-more">+{{ count($attachUrls) - 3 }}</span>
                                        @endif
                                    </div>
                                @else
                                    &mdash;
                                @endif
                            </td>
                            <td data-col="referralcode">{!! $referralCode !== '' ? e($referralCode) : '&mdash;' !!}</td>
                            <td data-col="assigndate">{{ $assignDate }}</td>
                            <td data-col="status"><span class="inquiries-status {{ $statusClass }}">{{ $statusDisp }}</span></td>
                            <td class="inquiries-col-action inquiries-action-cell">
                                <button type="button"
                                        class="inquiries-btn inquiries-btn-assign inquiries-view-status-btn"
                                        data-lead-id="{{ $r->LEADID }}"
                                        title="View Status"
                                        aria-label="View Status">
                                    <i class="bi bi-eye-fill" aria-hidden="true"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="24" class="inquiries-empty">No rewarded payouts.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="inquiries-assigned-pagination" id="rewardedPagination" data-per-page="10" data-current-page="1">
        <span class="inquiries-assigned-pagination-info" id="rewardedPaginationInfo">Showing 0 to 0 of 0 entries (Page 1)</span>
        <div class="inquiries-assigned-pagination-nav">
            <button type="button" class="inquiries-btn inquiries-btn-secondary inquiries-pagination-btn" id="rewardedPaginationFirst" aria-label="First page">First</button>
            <button type="button" class="inquiries-btn inquiries-btn-secondary inquiries-pagination-btn" id="rewardedPaginationPrev" aria-label="Previous page">Previous</button>
            <span class="inquiries-assigned-page-numbers" id="rewardedPageNumbers"></span>
            <button type="button" class="inquiries-btn inquiries-btn-secondary inquiries-pagination-btn" id="rewardedPaginationNext" aria-label="Next page">Next</button>
            <button type="button" class="inquiries-btn inquiries-btn-secondary inquiries-pagination-btn" id="rewardedPaginationLast" aria-label="Last page">Last</button>
        </div>
    </div>
</section>
</div>
    <div class="inquiries-assign-modal" id="statusModal" hidden>
        <div class="inquiries-assign-backdrop" data-status-close="1"></div>
        <div class="inquiries-assign-window" role="dialog" aria-modal="true" aria-labelledby="statusModalTitle">
            <div class="inquiries-assign-header">
                <div class="inquiries-assign-title" id="statusModalTitle">Status &mdash; Lead #SQL-<span id="statusModalLeadId"></span></div>
                <button type="button" class="inquiries-assign-close" aria-label="Close" data-status-close="1">&times;</button>
            </div>
            <div class="inquiries-assign-body">
                <div class="inquiries-status-table-wrap">
                    <table class="inquiries-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Subject</th>
                                <th>Status</th>
                                <th>Description</th>
                                <th>User</th>
                            </tr>
                        </thead>
                        <tbody id="statusModalBody"></tbody>
                    </table>
                </div>
                <p id="statusModalEmpty" class="inquiries-empty" style="display:none;">No status history.</p>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var COMPLETED_STORAGE_KEY = 'payoutCompletedVisibleColumns';
    var COMPLETED_DEFAULT_COLUMNS = ['inquiryid','customername','assignedto','referralcode','completiondate','dealtproducts','status'];
    var COMPLETED_ALL_COLUMNS = ['inquiryid','date','customername','source','postcode','city','address','contactno','businessnature','users','existingsw','demomode','products','dealtproducts','message','referralcode','assignedby','assignedto','completiondate','attachment','assigndate','status'];
    var REWARDED_STORAGE_KEY = 'payoutRewardedVisibleColumnsV2';
    var REWARDED_DEFAULT_COLUMNS = ['inquiryid','customername','assignedto','payoutsdate','attachment','referralcode','status'];
    var REWARDED_ALL_COLUMNS = ['inquiryid','date','customername','source','postcode','city','address','contactno','businessnature','users','existingsw','demomode','products','dealtproducts','message','assignedby','assignedto','completiondate','payoutsdate','attachment','referralcode','assigndate','status'];
    function columnsKey(cols) {
        if (!Array.isArray(cols)) return '';
        var uniq = [];
        cols.forEach(function(c) {
            c = String(c || '');
            if (c && uniq.indexOf(c) === -1) uniq.push(c);
        });
        uniq.sort();
        return uniq.join('|');
    }
    var COMPLETED_DEFAULT_KEY = columnsKey(COMPLETED_DEFAULT_COLUMNS);
    var REWARDED_DEFAULT_KEY = columnsKey(REWARDED_DEFAULT_COLUMNS);

    function getCompletedVisibleColumns() {
        try {
            var raw = localStorage.getItem(COMPLETED_STORAGE_KEY);
            if (raw !== null) {
                var arr = JSON.parse(raw);
                if (Array.isArray(arr)) return arr;
            }
        } catch (e) {}
        return COMPLETED_DEFAULT_COLUMNS.slice();
    }
    function setCompletedVisibleColumns(cols) {
        try { localStorage.setItem(COMPLETED_STORAGE_KEY, JSON.stringify(cols)); } catch (e) {}
    }
    function applyCompletedColumns(visible) {
        var table = document.getElementById('completedTable');
        if (!table) return;
        var isDefault = columnsKey(visible) === COMPLETED_DEFAULT_KEY;
        COMPLETED_ALL_COLUMNS.forEach(function(col) {
            var show = visible.indexOf(col) !== -1;
            table.querySelectorAll('th[data-col="' + col + '"], td[data-col="' + col + '"]').forEach(function(el) {
                el.style.display = show ? '' : 'none';
            });
        });
        var showAction = Array.isArray(visible) && visible.length > 0;
        table.querySelectorAll('th.inquiries-col-action, td.inquiries-col-action').forEach(function(el) {
            el.style.display = showAction ? '' : 'none';
        });
        table.classList.toggle('rewards-default-layout', isDefault);
        var wrap = table.closest('.inquiries-table-scroll');
        if (wrap) wrap.classList.toggle('rewards-default-layout', isDefault);
    }
    function syncCompletedCheckboxes(visible) {
        var menu = document.getElementById('completedColumnsMenu');
        if (!menu) return;
        menu.querySelectorAll('input[data-col]').forEach(function(cb) {
            var col = cb.getAttribute('data-col');
            cb.checked = visible.indexOf(col) !== -1;
        });
    }
    function refreshCompletedColumnState() {
        var visible = getCompletedVisibleColumns();
        syncCompletedCheckboxes(visible);
        applyCompletedColumns(visible);
    }

    function getRewardedVisibleColumns() {
        try {
            var raw = localStorage.getItem(REWARDED_STORAGE_KEY);
            if (raw !== null) {
                var arr = JSON.parse(raw);
                if (Array.isArray(arr)) return arr;
            }
        } catch (e) {}
        return REWARDED_DEFAULT_COLUMNS.slice();
    }
    function setRewardedVisibleColumns(cols) {
        try { localStorage.setItem(REWARDED_STORAGE_KEY, JSON.stringify(cols)); } catch (e) {}
    }
    function applyRewardedColumns(visible) {
        var table = document.getElementById('rewardedTable');
        if (!table) return;
        var isDefault = columnsKey(visible) === REWARDED_DEFAULT_KEY;
        REWARDED_ALL_COLUMNS.forEach(function(col) {
            var show = visible.indexOf(col) !== -1;
            table.querySelectorAll('th[data-col="' + col + '"], td[data-col="' + col + '"]').forEach(function(el) {
                el.style.display = show ? '' : 'none';
            });
        });
        var showAction = Array.isArray(visible) && visible.length > 0;
        table.querySelectorAll('th.inquiries-col-action, td.inquiries-col-action').forEach(function(el) {
            el.style.display = showAction ? '' : 'none';
        });
        table.classList.toggle('rewards-default-layout', isDefault);
        var wrap = table.closest('.inquiries-table-scroll');
        if (wrap) wrap.classList.toggle('rewards-default-layout', isDefault);
    }
    function syncRewardedCheckboxes(visible) {
        var menu = document.getElementById('rewardedColumnsMenu');
        if (!menu) return;
        menu.querySelectorAll('input[data-col]').forEach(function(cb) {
            var col = cb.getAttribute('data-col');
            cb.checked = visible.indexOf(col) !== -1;
        });
    }
    function refreshRewardedColumnState() {
        var visible = getRewardedVisibleColumns();
        syncRewardedCheckboxes(visible);
        applyRewardedColumns(visible);
    }

    refreshCompletedColumnState();
    refreshRewardedColumnState();

    var completedColBtn = document.getElementById('completedColumnsBtn');
    var completedColMenu = document.getElementById('completedColumnsMenu');
    if (completedColBtn && completedColMenu) {
        completedColBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            var isOpen = !completedColMenu.hidden;
            completedColMenu.hidden = isOpen;
            completedColBtn.setAttribute('aria-expanded', !isOpen);
            if (!isOpen) {
                completedColMenu.scrollTop = 0;
                refreshCompletedColumnState();
            }
        });
        document.addEventListener('click', function() {
            completedColMenu.hidden = true;
            completedColBtn.setAttribute('aria-expanded', 'false');
        });
        completedColMenu.addEventListener('click', function(e) { e.stopPropagation(); });
    }
    if (completedColMenu) {
        completedColMenu.querySelectorAll('input[data-col]').forEach(function(cb) {
            cb.addEventListener('change', function() {
                var visible = [];
                completedColMenu.querySelectorAll('input[data-col]:checked').forEach(function(c) {
                    visible.push(c.getAttribute('data-col'));
                });
                setCompletedVisibleColumns(visible);
                applyCompletedColumns(visible);
            });
        });
    }
    var completedReset = document.getElementById('completedColumnsReset');
    if (completedReset) {
        completedReset.addEventListener('click', function() {
            setCompletedVisibleColumns(COMPLETED_DEFAULT_COLUMNS.slice());
            refreshCompletedColumnState();
            var wrap = document.querySelector('#completedPanel .inquiries-table-scroll');
            if (wrap) wrap.scrollLeft = 0;
        });
    }
    var completedAll = document.getElementById('completedColumnsAll');
    if (completedAll) {
        completedAll.addEventListener('click', function() {
            setCompletedVisibleColumns(COMPLETED_ALL_COLUMNS.slice());
            refreshCompletedColumnState();
        });
    }
    var completedNone = document.getElementById('completedColumnsNone');
    if (completedNone) {
        completedNone.addEventListener('click', function() {
            setCompletedVisibleColumns([]);
            refreshCompletedColumnState();
        });
    }

    var rewardedColBtn = document.getElementById('rewardedColumnsBtn');
    var rewardedColMenu = document.getElementById('rewardedColumnsMenu');
    if (rewardedColBtn && rewardedColMenu) {
        rewardedColBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            var isOpen = !rewardedColMenu.hidden;
            rewardedColMenu.hidden = isOpen;
            rewardedColBtn.setAttribute('aria-expanded', !isOpen);
            if (!isOpen) {
                rewardedColMenu.scrollTop = 0;
                refreshRewardedColumnState();
            }
        });
        document.addEventListener('click', function() {
            rewardedColMenu.hidden = true;
            rewardedColBtn.setAttribute('aria-expanded', 'false');
        });
        rewardedColMenu.addEventListener('click', function(e) { e.stopPropagation(); });
    }
    if (rewardedColMenu) {
        rewardedColMenu.querySelectorAll('input[data-col]').forEach(function(cb) {
            cb.addEventListener('change', function() {
                var visible = [];
                rewardedColMenu.querySelectorAll('input[data-col]:checked').forEach(function(c) {
                    visible.push(c.getAttribute('data-col'));
                });
                setRewardedVisibleColumns(visible);
                applyRewardedColumns(visible);
            });
        });
    }
    var rewardedReset = document.getElementById('rewardedColumnsReset');
    if (rewardedReset) {
        rewardedReset.addEventListener('click', function() {
            setRewardedVisibleColumns(REWARDED_DEFAULT_COLUMNS.slice());
            refreshRewardedColumnState();
            var wrap = document.querySelector('#rewardedPanel .inquiries-table-scroll');
            if (wrap) wrap.scrollLeft = 0;
        });
    }
    var rewardedAll = document.getElementById('rewardedColumnsAll');
    if (rewardedAll) {
        rewardedAll.addEventListener('click', function() {
            setRewardedVisibleColumns(REWARDED_ALL_COLUMNS.slice());
            refreshRewardedColumnState();
        });
    }
    var rewardedNone = document.getElementById('rewardedColumnsNone');
    if (rewardedNone) {
        rewardedNone.addEventListener('click', function() {
            setRewardedVisibleColumns([]);
            refreshRewardedColumnState();
        });
    }

    function replaceRewardsTableBody(nextDoc, tableId) {
        var currentBody = document.querySelector('#' + tableId + ' tbody');
        var nextBody = nextDoc.querySelector('#' + tableId + ' tbody');
        if (!currentBody || !nextBody) return false;
        currentBody.innerHTML = nextBody.innerHTML;
        return true;
    }

    function refreshSummaryCardFromDoc(nextDoc) {
        var card = document.getElementById('payoutSummaryCard');
        var nextCard = nextDoc.querySelector('#payoutSummaryCard');
        if (!card || !nextCard) return;
        card.setAttribute('data-pending-count', nextCard.getAttribute('data-pending-count') || '0');
        card.setAttribute('data-rewarded-count', nextCard.getAttribute('data-rewarded-count') || '0');
    }

    function showRewardsToast(message) {
        var id = 'rewards-action-toast';
        var el = document.getElementById(id);
        if (!el) {
            el = document.createElement('div');
            el.id = id;
            el.className = 'inquiries-mark-failed-blocked-toast inquiries-mark-failed-blocked-toast-hidden';
            el.setAttribute('role', 'status');
            document.body.appendChild(el);
        }
        el.textContent = message || 'Done.';
        el.classList.remove('inquiries-mark-failed-blocked-toast-hidden');
        clearTimeout(el._hideTimer);
        el._hideTimer = setTimeout(function() {
            el.classList.add('inquiries-mark-failed-blocked-toast-hidden');
        }, 4000);
    }

    function shouldAutoSyncRewards(message) {
        var msg = (message || '').toLowerCase();
        return msg.indexOf('please sync and try again') !== -1 || msg.indexOf('already rewarded') !== -1 || msg.indexOf('already paid') !== -1 || msg.indexOf('already completed') !== -1;
    }

    function syncRewardsForButton(btn) {
        var panel = btn && btn.closest ? btn.closest('.inquiries-tab-panel') : null;
        var syncBtn = panel ? panel.querySelector('.inquiries-sync-btn') : null;
        if (!syncBtn) {
            syncBtn = document.querySelector('.inquiries-sync-btn[data-sync-type="completed"]') || document.querySelector('.inquiries-sync-btn');
        }
        triggerRewardsSync(syncBtn, true);
    }

    var REWARDS_AUTO_SYNC_MS = 15 * 60 * 1000;

    function triggerRewardsSync(btn, silent) {
        if (!btn || btn.classList.contains('is-syncing')) return;
        btn.classList.add('is-syncing');
        var icon = btn.querySelector('.inquiries-sync-icon');
        if (icon) icon.classList.add('spinning');
        var url = btn.getAttribute('data-sync-url');
        if (!url) url = '{{ route('admin.rewards') }}';
        var syncType = (btn.getAttribute('data-sync-type') || '').toLowerCase();
        var req = new URL(url, window.location.origin);
        req.searchParams.set('tab', syncType === 'rewarded' ? 'rewarded' : 'completed');
        req.searchParams.set('_sync', Date.now().toString());
        fetch(req.toString(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            cache: 'no-store'
        }).then(function(res) {
            return res.ok ? res.text() : Promise.reject();
        }).then(function(html) {
            var parser = new DOMParser();
            var nextDoc = parser.parseFromString(html, 'text/html');
            var completedUpdated = replaceRewardsTableBody(nextDoc, 'completedTable');
            var rewardedUpdated = replaceRewardsTableBody(nextDoc, 'rewardedTable');
            if (!completedUpdated && !rewardedUpdated) {
                return Promise.reject();
            }
            refreshSummaryCardFromDoc(nextDoc);
            bindRewardEmailButtons();
            applyCompletedColumns(getCompletedVisibleColumns());
            applyRewardedColumns(getRewardedVisibleColumns());
            applyAllTables();
            var activeTab = document.querySelector('.inquiries-tab.active');
            if (activeTab && typeof activeTab.click === 'function') {
                activeTab.click();
            }
        }).catch(function() {
            if (!silent) showRewardsToast('Failed to sync data.');
        }).finally(function() {
            btn.classList.remove('is-syncing');
            if (icon) icon.classList.remove('spinning');
        });
    }

    document.querySelectorAll('.inquiries-sync-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            triggerRewardsSync(btn, false);
        });
    });

    window.setInterval(function() {
        var autoBtn = document.querySelector('.inquiries-sync-btn[data-sync-type="completed"]') || document.querySelector('.inquiries-sync-btn');
        triggerRewardsSync(autoBtn, true);
    }, REWARDS_AUTO_SYNC_MS);

    var COMPLETED_PER_PAGE = 10;
    var REWARDED_PER_PAGE = 10;

    var EMAIL_COUNT_KEY = 'payoutEmailCountByLead';
    function loadEmailCounts() {
        try {
            var raw = localStorage.getItem(EMAIL_COUNT_KEY);
            if (raw) {
                var obj = JSON.parse(raw);
                if (obj && typeof obj === 'object') return obj;
            }
        } catch (e) {}
        return {};
    }
    function saveEmailCounts(map) {
        try { localStorage.setItem(EMAIL_COUNT_KEY, JSON.stringify(map)); } catch (e) {}
    }
    var emailCounts = loadEmailCounts();

    function applyRewardEmailIcon(btn, leadId) {
        var icon = btn.querySelector('i.bi');
        var count = emailCounts[leadId] || 0;
        if (!icon) return;
        if (count > 0) {
            icon.classList.remove('bi-envelope');
            icon.classList.add('bi-envelope-fill');
        } else {
            icon.classList.remove('bi-envelope-fill');
            icon.classList.add('bi-envelope');
        }
    }

    function bindRewardEmailButtons() {
        document.querySelectorAll('.rewards-email-btn').forEach(function(btn) {
            var leadId = btn.getAttribute('data-lead-id');
            if (!leadId) return;
            applyRewardEmailIcon(btn, leadId);
            if (btn.getAttribute('data-email-bound') === '1') return;
            btn.setAttribute('data-email-bound', '1');
            btn.addEventListener('click', function() {
                if (!confirm('Remind the assigned dealer to pay out the referral fee?')) return;
                var token = (document.querySelector('meta[name="csrf-token"]') || {}).content;
                if (!token) { showRewardsToast('Session expired. Please refresh the page.'); return; }
                btn.disabled = true;
                fetch('{{ route('admin.rewards.send-email') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ lead_id: parseInt(leadId, 10) })
                }).then(function(res) { return res.json().then(function(data) { return { ok: res.ok, data: data }; }); })
                .then(function(result) {
                    if (result.ok && result.data.success) {
                        showRewardsToast(result.data.message || 'Email sent.');
                        var current = emailCounts[leadId] || 0;
                        var next = current + 1;
                        emailCounts[leadId] = next;
                        saveEmailCounts(emailCounts);
                        applyRewardEmailIcon(btn, leadId);
                    } else {
                        var message = result.data.message || 'Failed to send email.';
                        showRewardsToast(message);
                        if (shouldAutoSyncRewards(message)) {
                            syncRewardsForButton(btn);
                        }
                    }
                }).catch(function() {
                    showRewardsToast('Failed to send email.');
                }).finally(function() {
                    btn.disabled = false;
                });
            });
        });
    }

    bindRewardEmailButtons();

    var completedClearFilters = document.getElementById('completedClearFilters');
    if (completedClearFilters) {
        completedClearFilters.addEventListener('click', function() {
            var table = document.getElementById('completedTable');
            if (table) table.querySelectorAll('thead .rewards-grid-filter').forEach(function(inp) { inp.value = ''; });
            resetRewardsOperatorMenus(table);
            applyTableFilter('completedTable');
        });
    }

    var rewardedClearFilters = document.getElementById('rewardedClearFilters');
    if (rewardedClearFilters) {
        rewardedClearFilters.addEventListener('click', function() {
            var table = document.getElementById('rewardedTable');
            if (table) table.querySelectorAll('thead .rewards-grid-filter').forEach(function(inp) { inp.value = ''; });
            resetRewardsOperatorMenus(table);
            applyTableFilter('rewardedTable');
        });
    }

    var REWARDS_ROW_HEIGHT_PX = 44;

    function getFilteredRewardsRows(table) {
        if (!table) return [];
        var rows = table.querySelectorAll('tbody tr.rewards-row');
        var out = [];
        for (var i = 0; i < rows.length; i++) {
            var filterMatch = rows[i].getAttribute('data-filter-match');
            if (filterMatch === null || filterMatch === '1') out.push(rows[i]);
        }
        return out;
    }

    function ensureRewardsFixedHeight(table, visibleDataCount, perPage) {
        if (!table) return;
        var tbody = table.querySelector('tbody');
        if (!tbody) return;
        if (visibleDataCount >= perPage) {
            tbody.style.minHeight = '';
        } else {
            tbody.style.minHeight = (perPage * REWARDS_ROW_HEIGHT_PX) + 'px';
        }
    }

    function applyCompletedPagination() {
        var table = document.getElementById('completedTable');
        var pagEl = document.getElementById('completedPagination');
        var infoEl = document.getElementById('completedPaginationInfo');
        var firstBtn = document.getElementById('completedPaginationFirst');
        var prevBtn = document.getElementById('completedPaginationPrev');
        var nextBtn = document.getElementById('completedPaginationNext');
        var lastBtn = document.getElementById('completedPaginationLast');
        var pageNumbersEl = document.getElementById('completedPageNumbers');
        if (!table || !pagEl) return;
        var allRows = table.querySelectorAll('tbody tr.rewards-row');
        var filteredRows = getFilteredRewardsRows(table);
        var total = filteredRows.length;
        var perPage = COMPLETED_PER_PAGE;
        var lastPage = total > 0 ? Math.ceil(total / perPage) : 1;
        var current = parseInt(pagEl.getAttribute('data-current-page') || '1', 10);
        current = Math.max(1, Math.min(current, lastPage));
        pagEl.setAttribute('data-current-page', String(current));
        var start = (current - 1) * perPage;
        var end = Math.min(start + perPage, total);
        var pageRows = filteredRows.slice(start, end);
        for (var i = 0; i < allRows.length; i++) {
            allRows[i].style.display = 'none';
        }
        for (var j = 0; j < pageRows.length; j++) {
            pageRows[j].style.display = '';
        }
        ensureRewardsFixedHeight(table, pageRows.length, perPage);
        var from = total === 0 ? 0 : start + 1;
        var to = end;
        if (infoEl) infoEl.textContent = 'Showing ' + from + ' to ' + to + ' of ' + total + ' entries (Page ' + current + ')';
        if (firstBtn) firstBtn.disabled = current <= 1;
        if (prevBtn) prevBtn.disabled = current <= 1;
        if (nextBtn) nextBtn.disabled = current >= lastPage;
        if (lastBtn) lastBtn.disabled = current >= lastPage;
        if (pageNumbersEl) {
            var html = '';
            for (var p = 1; p <= lastPage; p++) {
                var active = p === current;
                html += '<button type="button" class="inquiries-btn inquiries-btn-secondary inquiries-pagination-btn inquiries-page-num' + (active ? ' active' : '') + '" data-page="' + p + '" aria-label="Page ' + p + '"' + (active ? ' aria-current="page"' : '') + '>' + p + '</button>';
            }
            pageNumbersEl.innerHTML = html;
            pageNumbersEl.querySelectorAll('[data-page]').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var p = parseInt(btn.getAttribute('data-page'), 10);
                    pagEl.setAttribute('data-current-page', String(p));
                    applyCompletedPagination();
                });
            });
        }
    }

    function applyRewardedPagination() {
        var table = document.getElementById('rewardedTable');
        var pagEl = document.getElementById('rewardedPagination');
        var infoEl = document.getElementById('rewardedPaginationInfo');
        var firstBtn = document.getElementById('rewardedPaginationFirst');
        var prevBtn = document.getElementById('rewardedPaginationPrev');
        var nextBtn = document.getElementById('rewardedPaginationNext');
        var lastBtn = document.getElementById('rewardedPaginationLast');
        var pageNumbersEl = document.getElementById('rewardedPageNumbers');
        if (!table || !pagEl) return;
        var allRows = table.querySelectorAll('tbody tr.rewards-row');
        var filteredRows = getFilteredRewardsRows(table);
        var total = filteredRows.length;
        var perPage = REWARDED_PER_PAGE;
        var lastPage = total > 0 ? Math.ceil(total / perPage) : 1;
        var current = parseInt(pagEl.getAttribute('data-current-page') || '1', 10);
        current = Math.max(1, Math.min(current, lastPage));
        pagEl.setAttribute('data-current-page', String(current));
        var start = (current - 1) * perPage;
        var end = Math.min(start + perPage, total);
        var pageRows = filteredRows.slice(start, end);
        for (var i = 0; i < allRows.length; i++) {
            allRows[i].style.display = 'none';
        }
        for (var j = 0; j < pageRows.length; j++) {
            pageRows[j].style.display = '';
        }
        ensureRewardsFixedHeight(table, pageRows.length, perPage);
        var from = total === 0 ? 0 : start + 1;
        var to = end;
        if (infoEl) infoEl.textContent = 'Showing ' + from + ' to ' + to + ' of ' + total + ' entries (Page ' + current + ')';
        if (firstBtn) firstBtn.disabled = current <= 1;
        if (prevBtn) prevBtn.disabled = current <= 1;
        if (nextBtn) nextBtn.disabled = current >= lastPage;
        if (lastBtn) lastBtn.disabled = current >= lastPage;
        if (pageNumbersEl) {
            var html = '';
            for (var p = 1; p <= lastPage; p++) {
                var active = p === current;
                html += '<button type="button" class="inquiries-btn inquiries-btn-secondary inquiries-pagination-btn inquiries-page-num' + (active ? ' active' : '') + '" data-page="' + p + '" aria-label="Page ' + p + '"' + (active ? ' aria-current="page"' : '') + '>' + p + '</button>';
            }
            pageNumbersEl.innerHTML = html;
            pageNumbersEl.querySelectorAll('[data-page]').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var p = parseInt(btn.getAttribute('data-page'), 10);
                    pagEl.setAttribute('data-current-page', String(p));
                    applyRewardedPagination();
                });
            });
        }
    }

    var REWARDS_NUMERIC_FILTER_COLS = ['users'];

    function parseRewardsFilterNumber(value) {
        var num = parseFloat(String(value || '').replace(/[^0-9.\-]/g, ''));
        return isNaN(num) ? 0 : num;
    }

    function collectRewardsFilters(table) {
        var filters = {};
        if (!table) return filters;
        table.querySelectorAll('thead .rewards-grid-filter').forEach(function(inp) {
            var col = inp.getAttribute('data-col');
            var val = (inp.value || '').trim();
            if (!col || val === '') return;
            if (REWARDS_NUMERIC_FILTER_COLS.indexOf(col) !== -1) {
                var opBtn = table.querySelector('.dealer-operator-btn[data-col="' + col + '"]');
                filters[col] = {
                    numeric: true,
                    op: opBtn ? (opBtn.getAttribute('data-op') || '=') : '=',
                    val: val
                };
            } else {
                filters[col] = { numeric: false, val: val.toLowerCase() };
            }
        });
        return filters;
    }

    function rewardsRowMatchesFilters(row, filters) {
        for (var col in filters) {
            var cell = row.querySelector('td[data-col="' + col + '"]');
            var cellText = (cell && cell.textContent) ? cell.textContent.trim() : '';
            var filter = filters[col];
            if (filter.numeric) {
                var cellNum = parseRewardsFilterNumber(cellText);
                var filterNum = parseRewardsFilterNumber(filter.val);
                if (filter.op === '=' && cellNum !== filterNum) return false;
                if (filter.op === '!=' && cellNum === filterNum) return false;
                if (filter.op === '>' && cellNum <= filterNum) return false;
                if (filter.op === '>=' && cellNum < filterNum) return false;
                if (filter.op === '<' && cellNum >= filterNum) return false;
                if (filter.op === '<=' && cellNum > filterNum) return false;
            } else if (cellText.toLowerCase().indexOf(filter.val) === -1) {
                return false;
            }
        }
        return true;
    }

    function resetRewardsOperatorMenus(table) {
        if (!table) return;
        table.querySelectorAll('.dealer-operator-btn').forEach(function(btn) {
            btn.setAttribute('data-op', '=');
            btn.textContent = '=';
            btn.setAttribute('aria-expanded', 'false');
        });
        table.querySelectorAll('.dealer-operator-dropdown').forEach(function(dropdown) {
            dropdown.hidden = true;
        });
    }

    function bindRewardsOperatorMenus(table, tableId) {
        if (!table) return;
        table.querySelectorAll('.dealer-operator-btn').forEach(function(btn) {
            if (btn.getAttribute('data-operator-bound') === '1') return;
            btn.setAttribute('data-operator-bound', '1');
            var dropdown = btn.parentElement.querySelector('.dealer-operator-dropdown');
            if (!dropdown) return;
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                var isOpen = !dropdown.hidden;
                table.querySelectorAll('.dealer-operator-dropdown').forEach(function(d) { d.hidden = true; });
                table.querySelectorAll('.dealer-operator-btn').forEach(function(b) { b.setAttribute('aria-expanded', 'false'); });
                dropdown.hidden = isOpen;
                btn.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
            });
            dropdown.addEventListener('click', function(e) { e.stopPropagation(); });
            dropdown.querySelectorAll('button[data-op]').forEach(function(option) {
                option.addEventListener('click', function(e) {
                    e.stopPropagation();
                    var op = option.getAttribute('data-op') || '=';
                    btn.setAttribute('data-op', op);
                    btn.textContent = op;
                    btn.setAttribute('aria-expanded', 'false');
                    dropdown.hidden = true;
                    applyTableFilter(tableId);
                });
            });
        });
    }

    function applyTableFilter(tableId) {
        var table = document.getElementById(tableId);
        var searchInput = document.getElementById('payoutSearchInput');
    if (!table) return;
        var q = (searchInput && searchInput.value) ? searchInput.value.toLowerCase().trim() : '';
        var filters = collectRewardsFilters(table);
        table.querySelectorAll('tbody tr.rewards-row').forEach(function(row) {
            var hay = (row.getAttribute('data-search') || '').toLowerCase();
            var searchMatch = !q || hay.indexOf(q) !== -1;
            var colMatch = rewardsRowMatchesFilters(row, filters);
            row.setAttribute('data-filter-match', (searchMatch && colMatch) ? '1' : '0');
        });
        if (tableId === 'completedTable') {
            var cp = document.getElementById('completedPagination');
            if (cp) cp.setAttribute('data-current-page', '1');
            applyCompletedPagination();
        } else if (tableId === 'rewardedTable') {
            var rp = document.getElementById('rewardedPagination');
            if (rp) rp.setAttribute('data-current-page', '1');
            applyRewardedPagination();
        }
    }
    function bindTable(tableId) {
        var table = document.getElementById(tableId);
        if (!table) return;
        table.querySelectorAll('thead .rewards-grid-filter').forEach(function(inp) {
            inp.addEventListener('input', function() { applyTableFilter(tableId); });
        });
        bindRewardsOperatorMenus(table, tableId);
    }
    bindTable('completedTable');
    bindTable('rewardedTable');

    document.addEventListener('click', function() {
        ['completedTable', 'rewardedTable'].forEach(function(tableId) {
            var table = document.getElementById(tableId);
            if (!table) return;
            table.querySelectorAll('.dealer-operator-dropdown').forEach(function(dropdown) { dropdown.hidden = true; });
            table.querySelectorAll('.dealer-operator-btn').forEach(function(btn) { btn.setAttribute('aria-expanded', 'false'); });
        });
    });

    applyCompletedPagination();
    applyRewardedPagination();

    var completedPagNav = document.querySelector('#completedPagination .inquiries-assigned-pagination-nav');
    if (completedPagNav) {
        completedPagNav.addEventListener('click', function(e) {
            var btn = e.target.closest('button');
            if (!btn || !btn.id) return;
            var pagEl = document.getElementById('completedPagination');
            if (!pagEl) return;
            var cur = parseInt(pagEl.getAttribute('data-current-page') || '1', 10);
            var last = Math.max(1, Math.ceil(getFilteredRewardsRows(document.getElementById('completedTable')).length / COMPLETED_PER_PAGE));
            if (btn.id === 'completedPaginationFirst') pagEl.setAttribute('data-current-page', '1');
            else if (btn.id === 'completedPaginationPrev') pagEl.setAttribute('data-current-page', String(Math.max(1, cur - 1)));
            else if (btn.id === 'completedPaginationNext') pagEl.setAttribute('data-current-page', String(Math.min(last, cur + 1)));
            else if (btn.id === 'completedPaginationLast') pagEl.setAttribute('data-current-page', String(last));
            else return;
            applyCompletedPagination();
        });
    }
    var rewardedPagNav = document.querySelector('#rewardedPagination .inquiries-assigned-pagination-nav');
    if (rewardedPagNav) {
        rewardedPagNav.addEventListener('click', function(e) {
            var btn = e.target.closest('button');
            if (!btn || !btn.id) return;
            var pagEl = document.getElementById('rewardedPagination');
            if (!pagEl) return;
            var cur = parseInt(pagEl.getAttribute('data-current-page') || '1', 10);
            var last = Math.max(1, Math.ceil(getFilteredRewardsRows(document.getElementById('rewardedTable')).length / REWARDED_PER_PAGE));
            if (btn.id === 'rewardedPaginationFirst') pagEl.setAttribute('data-current-page', '1');
            else if (btn.id === 'rewardedPaginationPrev') pagEl.setAttribute('data-current-page', String(Math.max(1, cur - 1)));
            else if (btn.id === 'rewardedPaginationNext') pagEl.setAttribute('data-current-page', String(Math.min(last, cur + 1)));
            else if (btn.id === 'rewardedPaginationLast') pagEl.setAttribute('data-current-page', String(last));
            else return;
            applyRewardedPagination();
        });
    }

    var payoutSearchInput = document.getElementById('payoutSearchInput');
    var payoutSearchBtn = document.getElementById('payoutSearchBtn');
    var payoutClearBtn = document.getElementById('payoutClearSearchBtn');
    function applyAllTables() {
        applyTableFilter('completedTable');
        applyTableFilter('rewardedTable');
    }
    if (payoutSearchInput) {
        payoutSearchInput.addEventListener('input', applyAllTables);
        payoutSearchInput.addEventListener('keyup', function(e) { if (e.key === 'Enter') applyAllTables(); });
    }
    if (payoutSearchBtn) {
        payoutSearchBtn.addEventListener('click', applyAllTables);
    }
    if (payoutClearBtn) {
        payoutClearBtn.addEventListener('click', function() {
            if (payoutSearchInput) payoutSearchInput.value = '';
            ['completedTable','rewardedTable'].forEach(function(tid) {
                var t = document.getElementById(tid);
                if (!t) return;
                t.querySelectorAll('thead .rewards-grid-filter').forEach(function(inp) { inp.value = ''; });
            });
            applyAllTables();
        });
    }

    (function initMessageModal() {
        if (document.getElementById('rewardMessageModal')) return;
        var modal = document.createElement('div');
        modal.id = 'rewardMessageModal';
        modal.className = 'inquiries-msg-modal';
        modal.hidden = true;
        modal.innerHTML = ''
            + '<div class="inquiries-msg-modal-backdrop" data-close="1"></div>'
            + '<div class="inquiries-msg-modal-window" role="dialog" aria-modal="true" aria-labelledby="rewardsMsgModalTitle">'
            + '  <div class="inquiries-msg-modal-header">'
            + '    <div class="inquiries-msg-modal-title" id="rewardsMsgModalTitle">Message</div>'
            + '    <button type="button" class="inquiries-msg-modal-close" aria-label="Close" data-close="1">&times;</button>'
            + '  </div>'
            + '  <div class="inquiries-msg-modal-body"><pre class="inquiries-msg-modal-text" id="rewardsMsgModalText"></pre></div>'
            + '</div>';
        document.body.appendChild(modal);

        function close() { modal.hidden = true; }
        function open(text) {
            var el = document.getElementById('rewardsMsgModalText');
            if (el) el.textContent = text || '';
            modal.hidden = false;
        }

        document.addEventListener('click', function(e) {
            var target = e.target && e.target.nodeType === 3 ? e.target.parentElement : e.target;
            var cell = target && target.closest ? target.closest('.inquiries-msg-clickable[data-full-message]') : null;
            if (cell && ((cell.closest && cell.closest('#completedTable')) || (cell.closest && cell.closest('#rewardedTable')))) {
                e.preventDefault();
                open(cell.getAttribute('data-full-message') || '');
                return;
            }
            if (target && (target.getAttribute('data-close') === '1')) close();
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal && !modal.hidden) close();
        });
    })();

    document.querySelectorAll('.inquiries-tab').forEach(function(tab) {
        tab.addEventListener('click', function() {
            var t = this.getAttribute('data-tab');
            document.querySelectorAll('.inquiries-tab').forEach(function(bt) {
                bt.classList.toggle('active', bt.getAttribute('data-tab') === t);
                bt.setAttribute('aria-selected', bt.getAttribute('data-tab') === t ? 'true' : 'false');
            });
            var completedPanel = document.getElementById('completedPanel');
            var rewardedPanel = document.getElementById('rewardedPanel');
            if (completedPanel) { completedPanel.classList.toggle('active', t === 'completed'); completedPanel.hidden = t !== 'completed'; }
            if (rewardedPanel) { rewardedPanel.classList.toggle('active', t === 'rewarded'); rewardedPanel.hidden = t !== 'rewarded'; }

            var card = document.getElementById('payoutSummaryCard');
            var label = document.getElementById('payoutSummaryLabel');
            var value = document.getElementById('payoutSummaryValue');
            var note = document.getElementById('payoutSummaryNote');
            var iconEl = document.getElementById('payoutSummaryIcon');
            if (card && label && value && note) {
                var pendingCount = card.getAttribute('data-pending-count') || '0';
                var rewardedCount = card.getAttribute('data-rewarded-count') || '0';
                if (t === 'rewarded') {
                    label.textContent = 'TOTAL REWARDED';
                    value.textContent = rewardedCount;
                    note.textContent = 'Completed Referral Payouts';
                    if (iconEl) {
                        iconEl.classList.remove('bi-coin');
                        iconEl.classList.remove('bi-check2-circle');
                        iconEl.classList.add('bi-piggy-bank');
                    }
                } else {
                    label.textContent = 'PENDING REWARD';
                    value.textContent = pendingCount;
                    note.textContent = 'Pending Referral Payout';
                    if (iconEl) {
                        iconEl.classList.remove('bi-piggy-bank');
                        iconEl.classList.remove('bi-check2-circle');
                        iconEl.classList.add('bi-coin');
                    }
                }
            }
        });
    });

    // Ensure tab + summary reflect initial tab from query (?tab=rewarded|completed)
    (function initInitialTab() {
        var params = new URLSearchParams(window.location.search || '');
        var tab = params.get('tab') === 'rewarded' ? 'rewarded' : 'completed';
        var fakeTab = document.querySelector('.inquiries-tab[data-tab="' + tab + '"]');
        if (fakeTab && typeof fakeTab.click === 'function') {
            fakeTab.click();
        }
    })();

    (function initStatusModal() {
        var modal = document.getElementById('statusModal');
        var titleLeadId = document.getElementById('statusModalLeadId');
        var body = document.getElementById('statusModalBody');
        var emptyEl = document.getElementById('statusModalEmpty');
        if (!modal || !body) return;
        function closeStatus() { modal.hidden = true; }
        function openStatus(leadId, items) {
            if (titleLeadId) titleLeadId.textContent = leadId;
            body.innerHTML = '';
            if (!items || items.length === 0) {
                if (emptyEl) emptyEl.style.display = 'block';
            } else {
                if (emptyEl) emptyEl.style.display = 'none';
                items.forEach(function(it) {
                    var tr = document.createElement('tr');
                    var date = it.CREATIONDATE ? String(it.CREATIONDATE).substring(0, 19) : '&mdash;';
                    tr.innerHTML =
                        '<td>' + date + '</td>' +
                        '<td>' + (it.SUBJECT || '&mdash;') + '</td>' +
                        '<td>' + (it.STATUS || '&mdash;') + '</td>' +
                        '<td>' + (it.DESCRIPTION || '&mdash;') + '</td>' +
                        '<td>' + (it.USERID || '&mdash;') + '</td>';
                    body.appendChild(tr);
                });
            }
            modal.hidden = false;
        }
        document.addEventListener('click', function(e) {
            var btn = e.target && e.target.closest ? e.target.closest('.inquiries-view-status-btn') : null;
            if (btn) {
                var leadId = btn.getAttribute('data-lead-id');
                if (leadId) {
                    fetch('{{ url("/admin/inquiries") }}/' + leadId + '/status', { headers: { 'Accept': 'application/json' } })
                        .then(function(r) { return r.json(); })
                        .then(function(data) { openStatus(leadId, data.items || []); })
                        .catch(function() { openStatus(leadId, []); });
                }
                return;
            }
            if (e.target && (e.target.getAttribute('data-status-close') === '1')) closeStatus();
        });
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal && !modal.hidden) closeStatus();
        });
    })();
});
</script>
@endpush

