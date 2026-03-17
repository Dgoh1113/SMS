@extends('layouts.app')
@section('title', 'Rewards â€“ Admin')
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
            <button type="button" class="inquiries-btn inquiries-btn-secondary inquiries-sync-btn" data-sync-type="assigned" data-sync-url="{{ route('admin.inquiries.sync') }}">
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
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="completiondate"> PAYOUTS DATE</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="address"> ADDRESS</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="contactno"> CONTACT NO</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="businessnature"> BUSINESS NATURE</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="users"> USERS</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="existingsw"> EXISTING SW</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="demomode"> DEMO MODE</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="dealtproducts"> DEALT PRODUCTS</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="message"> MESSAGE</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="referralcode"> REFERRAL CODE</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="assignedby"> ASSIGNED BY</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="assignedto"> ASSIGNED TO</label>
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
                        <th data-col="completiondate" class="inquiries-header-cell"><span class="inquiries-header-label">PAYOUTS DATE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter rewards-grid-filter" data-table="completed" data-col="completiondate"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="status" class="inquiries-header-cell"><span class="inquiries-header-label">STATUS</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter rewards-grid-filter" data-table="completed" data-col="status"><i class="bi bi-search inquiries-filter-icon"></i></span></th>

                        <th data-col="date" class="inquiries-header-cell"><span class="inquiries-header-label">INQUIRY DATE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter rewards-grid-filter" data-table="completed" data-col="date"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="source" class="inquiries-header-cell"><span class="inquiries-header-label">SOURCE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter rewards-grid-filter" data-table="completed" data-col="source"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="postcode" class="inquiries-header-cell"><span class="inquiries-header-label">POSTCODE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter rewards-grid-filter" data-table="completed" data-col="postcode"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="city" class="inquiries-header-cell"><span class="inquiries-header-label">CITY</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter rewards-grid-filter" data-table="completed" data-col="city"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="address" class="inquiries-header-cell"><span class="inquiries-header-label">ADDRESS</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter rewards-grid-filter" data-table="completed" data-col="address"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="contactno" class="inquiries-header-cell"><span class="inquiries-header-label">CONTACT NO</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter rewards-grid-filter" data-table="completed" data-col="contactno"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="businessnature" class="inquiries-header-cell"><span class="inquiries-header-label">BUSINESS NATURE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter rewards-grid-filter" data-table="completed" data-col="businessnature"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="users" class="inquiries-header-cell"><span class="inquiries-header-label">USERS</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter rewards-grid-filter" data-table="completed" data-col="users"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="existingsw" class="inquiries-header-cell"><span class="inquiries-header-label">EXISTING SW</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter rewards-grid-filter" data-table="completed" data-col="existingsw"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="demomode" class="inquiries-header-cell"><span class="inquiries-header-label">DEMO MODE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter rewards-grid-filter" data-table="completed" data-col="demomode"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="dealtproducts" class="inquiries-header-cell"><span class="inquiries-header-label">DEALT PRODUCTS</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter rewards-grid-filter" data-table="completed" data-col="dealtproducts"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="message" class="inquiries-header-cell"><span class="inquiries-header-label">MESSAGE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter rewards-grid-filter" data-table="completed" data-col="message"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="assignedby" class="inquiries-header-cell"><span class="inquiries-header-label">ASSIGNED BY</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter rewards-grid-filter" data-table="completed" data-col="assignedby"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
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
                                : ($ccompany !== '' ? $ccompany : ($ccontact !== '' ? $ccontact : 'â€”'));
                            $addr1 = trim((string)($r->ADDRESS1 ?? ''));
                            $addr2 = trim((string)($r->ADDRESS2 ?? ''));
                            $addr = trim($addr1 . ' ' . $addr2);
                            $afullMsg = (string)($r->DESCRIPTION ?? '');
                            $afullMsgTrim = trim($afullMsg);
                            $amsgPreview = $afullMsgTrim === '' ? 'â€”' : (mb_strlen($afullMsgTrim) > 30 ? (mb_substr($afullMsgTrim, 0, 30) . 'â€¦') : $afullMsgTrim);
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
                            $assignDate = $r->LASTMODIFIED ? date('d/m/Y', strtotime($r->LASTMODIFIED)) : ($r->CREATEDAT ? date('d/m/Y', strtotime($r->CREATEDAT)) : 'â€”');
                            $searchHaystack = strtolower(($r->COMPANYNAME ?? '').' '.($r->CONTACTNAME ?? '').' '.($r->LEADID ?? ''));
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
                        @endphp
                        <tr class="rewards-row inquiry-row" data-search="{{ $searchHaystack }}">
                            <td data-col="inquiryid">#SQL-{{ $r->LEADID }}</td>
                            <td data-col="customername">{{ $custDisp }}</td>
                            <td data-col="assignedto">{{ $r->ASSIGNED_TO_NAME ?? ($r->ASSIGNED_TO ?? 'â€”') }}</td>
                            <td data-col="referralcode">{{ $r->REFERRALCODE ?? 'â€”' }}</td>
                            <td data-col="completiondate">
                                @php
                                    $completedAt = $r->COMPLETED_AT ?? null;
                                @endphp
                                {{ $completedAt ? date('d/m/Y', strtotime($completedAt)) : 'â€”' }}
                            </td>
                            <td data-col="status"><span class="inquiries-status {{ $statusClass }}">{{ $statusDisp }}</span></td>

                            <td data-col="date">{{ $r->CREATEDAT ? date('d/m/Y', strtotime($r->CREATEDAT)) : 'â€”' }}</td>
                            <td data-col="source">{{ $r->CREATEDBY_NAME ?? ($r->CREATEDBY ?? 'â€”') }}</td>
                            <td data-col="postcode">{{ $r->POSTCODE ?? 'â€”' }}</td>
                            <td data-col="city">{{ $r->CITY ?? 'â€”' }}</td>
                            <td data-col="address">{{ $addr !== '' ? $addr : 'â€”' }}</td>
                            <td data-col="contactno">{{ $r->CONTACTNO ?? 'â€”' }}</td>
                            <td data-col="businessnature">{{ $r->BUSINESSNATURE ?? 'â€”' }}</td>
                            <td data-col="users">{{ $r->USERCOUNT ?? 'â€”' }}</td>
                            <td data-col="existingsw">{{ $r->EXISTINGSOFTWARE ?? 'â€”' }}</td>
                            <td data-col="demomode">{{ $r->DEMOMODE ?? 'â€”' }}</td><td data-col="dealtproducts">
                                @if(!empty($dealtProductIds))
                                    <div class="inquiries-pill-group">
                                        @foreach($dealtProductIds as $id)
                                            @if(isset($productLabels[(int)$id]))
                                                <span class="inquiries-pill inquiries-pill-p{{ (int)$id }}">{{ $productLabels[(int)$id] }}</span>
                                            @endif
                                        @endforeach
                                    </div>
                                @else
                                    Ã¢â‚¬â€
                                @endif
                            </td>
                            <td data-col="message" class="inquiries-msg-cell {{ $aisLongMsg ? 'inquiries-msg-clickable' : '' }}" @if($aisLongMsg) data-full-message="{{ e($afullMsgTrim) }}" @endif>{{ $amsgPreview }}</td>
                            <td data-col="assignedby">{{ $r->CREATEDBY_NAME ?? ($r->CREATEDBY ?? 'â€”') }}</td>
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
                        <tr><td colspan="20" class="inquiries-empty">No completed payouts.</td></tr>
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
            <button type="button" class="inquiries-btn inquiries-btn-secondary inquiries-sync-btn" data-sync-type="rewarded" data-sync-url="{{ route('admin.inquiries.sync') }}">
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
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="completiondate"> PAYOUTS DATE</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="address"> ADDRESS</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="contactno"> CONTACT NO</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="businessnature"> BUSINESS NATURE</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="users"> USERS</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="existingsw"> EXISTING SW</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="demomode"> DEMO MODE</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="products"> PRODUCTS</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="message"> MESSAGE</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="referralcode"> REFERRAL CODE</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="assignedby"> ASSIGNED BY</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="assignedto"> ASSIGNED TO</label>
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
                        <th data-col="completiondate" class="inquiries-header-cell"><span class="inquiries-header-label">PAYOUTS DATE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter rewards-grid-filter" data-table="rewarded" data-col="completiondate"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="address" class="inquiries-header-cell"><span class="inquiries-header-label">ADDRESS</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter rewards-grid-filter" data-table="rewarded" data-col="address"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="contactno" class="inquiries-header-cell"><span class="inquiries-header-label">CONTACT NO</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter rewards-grid-filter" data-table="rewarded" data-col="contactno"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="businessnature" class="inquiries-header-cell"><span class="inquiries-header-label">BUSINESS NATURE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter rewards-grid-filter" data-table="rewarded" data-col="businessnature"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="users" class="inquiries-header-cell"><span class="inquiries-header-label">USERS</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter rewards-grid-filter" data-table="rewarded" data-col="users"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="existingsw" class="inquiries-header-cell"><span class="inquiries-header-label">EXISTING SW</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter rewards-grid-filter" data-table="rewarded" data-col="existingsw"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="demomode" class="inquiries-header-cell"><span class="inquiries-header-label">DEMO MODE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter rewards-grid-filter" data-table="rewarded" data-col="demomode"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="products" class="inquiries-header-cell"><span class="inquiries-header-label">PRODUCTS</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter rewards-grid-filter" data-table="rewarded" data-col="products"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="message" class="inquiries-header-cell"><span class="inquiries-header-label">MESSAGE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter rewards-grid-filter" data-table="rewarded" data-col="message"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="referralcode" class="inquiries-header-cell"><span class="inquiries-header-label">REFERRAL CODE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter rewards-grid-filter" data-table="rewarded" data-col="referralcode"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="assignedby" class="inquiries-header-cell"><span class="inquiries-header-label">ASSIGNED BY</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter rewards-grid-filter" data-table="rewarded" data-col="assignedby"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="assignedto" class="inquiries-header-cell"><span class="inquiries-header-label">ASSIGNED TO</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter rewards-grid-filter" data-table="rewarded" data-col="assignedto"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
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
                                : ($ccompany !== '' ? $ccompany : ($ccontact !== '' ? $ccontact : 'â€”'));
                            $addr1 = trim((string)($r->ADDRESS1 ?? ''));
                            $addr2 = trim((string)($r->ADDRESS2 ?? ''));
                            $addr = trim($addr1 . ' ' . $addr2);
                            $afullMsg = (string)($r->DESCRIPTION ?? '');
                            $afullMsgTrim = trim($afullMsg);
                            $amsgPreview = $afullMsgTrim === '' ? 'â€”' : (mb_strlen($afullMsgTrim) > 30 ? (mb_substr($afullMsgTrim, 0, 30) . 'â€¦') : $afullMsgTrim);
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
                            $searchHaystack = strtolower(($r->COMPANYNAME ?? '').' '.($r->CONTACTNAME ?? '').' '.($r->LEADID ?? ''));
                            $assignDate = $r->LASTMODIFIED ? date('d/m/Y', strtotime($r->LASTMODIFIED)) : ($r->CREATEDAT ? date('d/m/Y', strtotime($r->CREATEDAT)) : 'â€”');
                            $productIds = $r->PRODUCTID ? array_map('trim', explode(',', (string)$r->PRODUCTID)) : [];
                            $pillOrder = [1=>10,3=>11,4=>12,2=>20,10=>21,8=>30,5=>31,6=>40,9=>50,7=>60,11=>70];
                            $productIds = array_values(array_filter(array_unique(array_map('intval', $productIds)), fn($v) => $v > 0));
                            usort($productIds, function($a,$b) use ($pillOrder) { return ($pillOrder[$a] ?? 1000+$a) <=> ($pillOrder[$b] ?? 1000+$b); });
                        @endphp
                        <tr class="rewards-row inquiry-row" data-search="{{ $searchHaystack }}">
                            <td data-col="inquiryid">#SQL-{{ $r->LEADID }}</td>
                            <td data-col="date">{{ $r->CREATEDAT ? date('d/m/Y', strtotime($r->CREATEDAT)) : 'â€”' }}</td>
                            <td data-col="customername">{{ $custDisp }}</td>
                            <td data-col="source">{{ $r->CREATEDBY_NAME ?? ($r->CREATEDBY ?? 'â€”') }}</td>
                            <td data-col="postcode">{{ $r->POSTCODE ?? 'â€”' }}</td>
                            <td data-col="city">{{ $r->CITY ?? 'â€”' }}</td>
                            <td data-col="completiondate">{{ $completedAt ? date('d/m/Y', strtotime($completedAt)) : 'â€”' }}</td>
                            <td data-col="address">{{ $addr !== '' ? $addr : 'â€”' }}</td>
                            <td data-col="contactno">{{ $r->CONTACTNO ?? 'â€”' }}</td>
                            <td data-col="businessnature">{{ $r->BUSINESSNATURE ?? 'â€”' }}</td>
                            <td data-col="users">{{ $r->USERCOUNT ?? 'â€”' }}</td>
                            <td data-col="existingsw">{{ $r->EXISTINGSOFTWARE ?? 'â€”' }}</td>
                            <td data-col="demomode">{{ $r->DEMOMODE ?? 'â€”' }}</td>
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
                                    â€”
                                @endif
                            </td>
                            <td data-col="message" class="inquiries-msg-cell {{ $aisLongMsg ? 'inquiries-msg-clickable' : '' }}" @if($aisLongMsg) data-full-message="{{ e($afullMsgTrim) }}" @endif>{{ $amsgPreview }}</td>
                            <td data-col="referralcode">{{ $r->REFERRALCODE ?? 'â€”' }}</td>
                            <td data-col="assignedby">{{ $r->CREATEDBY_NAME ?? ($r->CREATEDBY ?? 'â€”') }}</td>
                            <td data-col="assignedto">{{ $r->ASSIGNED_TO_NAME ?? ($r->ASSIGNED_TO ?? 'â€”') }}</td>
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
                        <tr><td colspan="20" class="inquiries-empty">No rewarded payouts.</td></tr>
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
                <div class="inquiries-assign-title" id="statusModalTitle">Status â€” Lead #SQL-<span id="statusModalLeadId"></span></div>
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
    var COMPLETED_ALL_COLUMNS = ['inquiryid','date','customername','source','postcode','city','address','contactno','businessnature','users','existingsw','demomode','dealtproducts','message','referralcode','assignedby','assignedto','completiondate','assigndate','status'];
    var REWARDED_STORAGE_KEY = 'payoutRewardedVisibleColumns';
    var REWARDED_DEFAULT_COLUMNS = ['inquiryid','customername','assignedto','referralcode','completiondate','status'];
    var REWARDED_ALL_COLUMNS = ['inquiryid','date','customername','source','postcode','city','completiondate','address','contactno','businessnature','users','existingsw','demomode','products','message','referralcode','assignedby','assignedto','assigndate','status'];

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

    document.querySelectorAll('.inquiries-sync-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            if (btn.classList.contains('is-syncing')) return;
            btn.classList.add('is-syncing');
            var icon = btn.querySelector('.inquiries-sync-icon');
            if (icon) icon.classList.add('spinning');
            var url = btn.getAttribute('data-sync-url');
            if (!url) url = '{{ route('admin.inquiries.sync') }}';
            fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                cache: 'no-store'
            }).then(function(res) {
                return res.ok ? res.json() : Promise.reject();
            }).then(function(data) {
                // Silent success â€“ no popup needed.
            }).catch(function() {
                alert('Failed to sync data.');
            }).finally(function() {
                btn.classList.remove('is-syncing');
                if (icon) icon.classList.remove('spinning');
            });
        });
    });

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

    document.querySelectorAll('.rewards-email-btn').forEach(function(btn) {
        var leadId = btn.getAttribute('data-lead-id');
        if (!leadId) return;
        var icon = btn.querySelector('i.bi');
        var count = emailCounts[leadId] || 0;
        if (icon) {
            if (count > 0) {
                icon.classList.remove('bi-envelope');
                icon.classList.add('bi-envelope-fill');
            } else {
                icon.classList.remove('bi-envelope-fill');
                icon.classList.add('bi-envelope');
            }
        }
    });

    document.querySelectorAll('.rewards-email-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var leadId = btn.getAttribute('data-lead-id');
            if (!leadId) return;
            if (!confirm('Remind the assigned dealer to pay out the referral fee?')) return;
            var token = (document.querySelector('meta[name="csrf-token"]') || {}).content;
            if (!token) { alert('Session expired. Please refresh the page.'); return; }
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
                    alert(result.data.message || 'Email sent.');
                    var current = emailCounts[leadId] || 0;
                    var next = current + 1;
                    emailCounts[leadId] = next;
                    saveEmailCounts(emailCounts);
                    var icon = btn.querySelector('i.bi');
                    if (icon) {
                        icon.classList.remove('bi-envelope');
                        icon.classList.add('bi-envelope-fill');
                    }
                } else {
                    alert(result.data.message || 'Failed to send email.');
                }
            }).catch(function() {
                alert('Failed to send email.');
            }).finally(function() {
                btn.disabled = false;
            });
        });
    });

    var completedClearFilters = document.getElementById('completedClearFilters');
    if (completedClearFilters) {
        completedClearFilters.addEventListener('click', function() {
            var table = document.getElementById('completedTable');
            if (table) table.querySelectorAll('thead .rewards-grid-filter').forEach(function(inp) { inp.value = ''; });
            applyTableFilter('completedTable');
        });
    }

    var rewardedClearFilters = document.getElementById('rewardedClearFilters');
    if (rewardedClearFilters) {
        rewardedClearFilters.addEventListener('click', function() {
            var table = document.getElementById('rewardedTable');
            if (table) table.querySelectorAll('thead .rewards-grid-filter').forEach(function(inp) { inp.value = ''; });
            applyTableFilter('rewardedTable');
        });
    }

    function getVisibleDataRows(table) {
        if (!table) return [];
        var rows = table.querySelectorAll('tbody tr.rewards-row');
        var out = [];
        for (var i = 0; i < rows.length; i++) {
            if (rows[i].style.display !== 'none') out.push(rows[i]);
        }
        return out;
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
        var visible = getVisibleDataRows(table);
        var total = visible.length;
        var perPage = COMPLETED_PER_PAGE;
        var lastPage = total > 0 ? Math.ceil(total / perPage) : 1;
        var current = parseInt(pagEl.getAttribute('data-current-page') || '1', 10);
        current = Math.max(1, Math.min(current, lastPage));
        pagEl.setAttribute('data-current-page', String(current));
        var start = (current - 1) * perPage;
        var end = Math.min(start + perPage, total);
        for (var i = 0; i < visible.length; i++) {
            visible[i].style.display = (i >= start && i < end) ? '' : 'none';
        }
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
        var visible = getVisibleDataRows(table);
        var total = visible.length;
        var perPage = REWARDED_PER_PAGE;
        var lastPage = total > 0 ? Math.ceil(total / perPage) : 1;
        var current = parseInt(pagEl.getAttribute('data-current-page') || '1', 10);
        current = Math.max(1, Math.min(current, lastPage));
        pagEl.setAttribute('data-current-page', String(current));
        var start = (current - 1) * perPage;
        var end = Math.min(start + perPage, total);
        for (var i = 0; i < visible.length; i++) {
            visible[i].style.display = (i >= start && i < end) ? '' : 'none';
        }
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

    function applyTableFilter(tableId) {
        var table = document.getElementById(tableId);
        var searchInput = document.getElementById('payoutSearchInput');
    if (!table) return;
        var q = (searchInput && searchInput.value) ? searchInput.value.toLowerCase().trim() : '';
        var filters = {};
        table.querySelectorAll('thead .rewards-grid-filter').forEach(function(inp) {
            var col = inp.getAttribute('data-col');
            var val = (inp.value || '').toLowerCase().trim();
            if (col && val) filters[col] = val;
        });
        table.querySelectorAll('tbody tr.rewards-row').forEach(function(row) {
            var hay = (row.getAttribute('data-search') || '').toLowerCase();
            var searchMatch = !q || hay.indexOf(q) !== -1;
            var colMatch = true;
            for (var col in filters) {
                var cell = row.querySelector('td[data-col="' + col + '"]');
                var cellText = (cell && cell.textContent) ? cell.textContent.toLowerCase().trim() : '';
                if (cellText.indexOf(filters[col]) === -1) { colMatch = false; break; }
            }
            row.style.display = (searchMatch && colMatch) ? '' : 'none';
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
    }
    bindTable('completedTable');
    bindTable('rewardedTable');

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
            var last = Math.max(1, Math.ceil(getVisibleDataRows(document.getElementById('completedTable')).length / COMPLETED_PER_PAGE));
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
            var last = Math.max(1, Math.ceil(getVisibleDataRows(document.getElementById('rewardedTable')).length / REWARDED_PER_PAGE));
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
                    var date = it.CREATIONDATE ? String(it.CREATIONDATE).substring(0, 19) : 'â€”';
                    tr.innerHTML =
                        '<td>' + date + '</td>' +
                        '<td>' + (it.SUBJECT || 'â€”') + '</td>' +
                        '<td>' + (it.STATUS || 'â€”') + '</td>' +
                        '<td>' + (it.DESCRIPTION || 'â€”') + '</td>' +
                        '<td>' + (it.USERID || 'â€”') + '</td>';
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
