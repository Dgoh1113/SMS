@extends('layouts.app')
@section('title', 'Inquiries Management – Admin')
@section('content')
@php
    $assignUndo = session('assign_undo');
    $assignEmailPending = session('assign_email_pending');
@endphp
<div class="inquiries-page-wrap">
<div class="inquiries-mgmt-top-row">
<section class="inquiries-mgmt-summary">
    <div class="inquiries-summary-card" id="incomingSummaryCard">
        <div class="inquiries-summary-icon"><i class="bi bi-inbox"></i></div>
        <div class="inquiries-summary-label">TOTAL NEW INQUIRIES</div>
        <div class="inquiries-summary-value-row">
            <span class="inquiries-summary-value">{{ number_format($totalNewInquiries) }}</span>
        </div>
        <div class="inquiries-summary-note">New leads waiting to assign</div>
    </div>
    <div class="inquiries-summary-card" id="assignedSummaryCard" style="display:none;">
        <div class="inquiries-summary-icon"><i class="bi bi-arrow-left-right"></i></div>
        <div class="inquiries-summary-label">TOTAL ONGOING</div>
        <div class="inquiries-summary-value-row">
            <span class="inquiries-summary-value">{{ number_format($totalOngoing ?? 0) }}</span>
        </div>
        <div class="inquiries-summary-note">Leads currently in progress</div>
    </div>
</section>

@if($assignUndo)
<div id="assignUndoToast" class="assign-undo-toast assign-undo-toast-hidden"
     data-lead-id="{{ $assignUndo['lead_id'] ?? '' }}"
     data-prev-assigned-to="{{ $assignUndo['prev_assigned_to'] ?? '' }}"
     data-new-assigned-to="{{ $assignUndo['new_assigned_to'] ?? '' }}">
    <span class="assign-undo-message">
        Lead #SQL-{{ $assignUndo['lead_id'] ?? '' }} assigned.
    </span>
    <button type="button" class="assign-undo-btn">Undo</button>
</div>

<form id="assignUndoForm" method="POST" action="{{ route('admin.inquiries.assign-undo') }}" style="display:none;">
    @csrf
    <input type="hidden" name="LEADID" id="assignUndoLeadId">
    <input type="hidden" name="PREV_ASSIGNED_TO" id="assignUndoPrevAssigned">
</form>
@endif

{{-- Delete undo toast (shown via JS after delete) --}}
<div id="deleteUndoToast" class="assign-undo-toast assign-undo-toast-hidden" style="display:none;">
    <span class="assign-undo-message" id="deleteUndoToastMessage">Lead deleted.</span>
    <button type="button" class="assign-undo-btn" id="deleteUndoBtn">Undo</button>
</div>
<form id="deleteUndoForm" method="POST" action="{{ route('admin.inquiries.delete-undo') }}" style="display:none;">
    @csrf
    <input type="hidden" name="LEADID" id="deleteUndoLeadId">
</form>

<section class="inquiries-mgmt-search">
    <div class="inquiries-search-row">
        <div class="inquiries-search-wrap">
            <span class="inquiries-search-icon"><i class="bi bi-search"></i></span>
            <input type="text" class="inquiries-search-input" placeholder="Search by customer, company, or Inquiry ID..." id="inquirySearchInput">
            <button type="button" class="inquiries-search-btn" id="inquirySearchBtn">Search</button>
        </div>
        <button type="button" class="inquiries-btn inquiries-btn-secondary inquiries-search-clear-btn" id="inquiryClearSearchBtn" title="Clear search">Clear</button>
    </div>
</section>
</div>

<div class="inquiries-tabs">
    <button type="button" class="inquiries-tab active" data-tab="incoming" aria-selected="true">Incoming</button>
    <button type="button" class="inquiries-tab" data-tab="assigned" aria-selected="false">Assigned</button>
</div>

<div class="inquiries-tab-panel active" id="incomingPanel" role="tabpanel">
<section class="inquiries-mgmt-panel">
    <div class="inquiries-panel-header">
        <div class="inquiries-panel-title-wrap">
            <i class="bi bi-folder2-open inquiries-panel-icon"></i>
            <h2 class="inquiries-panel-title">Incoming Inquiries</h2>
        </div>
        <div class="inquiries-panel-actions">
            <button type="button" class="inquiries-btn inquiries-btn-secondary inquiries-sync-btn" data-sync-type="incoming" data-sync-url="{{ route('admin.inquiries.sync') }}">
                <i class="bi bi-arrow-repeat inquiries-sync-icon"></i>
                <span class="inquiries-sync-label">Sync</span>
            </button>
            <div class="inquiries-columns-dropdown">
                <button type="button" class="inquiries-btn inquiries-btn-secondary" id="inquiryColumnsBtn" aria-haspopup="true" aria-expanded="false">Columns</button>
                <div class="inquiries-columns-menu" id="inquiryColumnsMenu" hidden>
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
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="message"> MESSAGE</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="referralcode"> REFERRAL CODE</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="status"> STATUS</label>
                    <div class="inquiries-columns-actions">
                        <button type="button" class="inquiries-columns-action-btn" id="inquiryColumnsAll">All</button>
                        <button type="button" class="inquiries-columns-action-btn" id="inquiryColumnsNone">None</button>
                    </div>
                    <button type="button" class="inquiries-columns-reset" id="inquiryColumnsReset">Reset to default</button>
                </div>
            </div>
            <a href="{{ route('admin.inquiries.create') }}" class="inquiries-btn inquiries-btn-primary inquiries-add-btn">
                <i class="bi bi-plus-lg"></i>
                <span>Add new inquiry</span>
            </a>
        </div>
    </div>
    <div class="inquiries-table-wrap">
        <div class="inquiries-table-scroll">
        <table class="inquiries-table" id="unassignedTable">
            <thead>
                <tr class="inquiries-header-row">
                    <th data-col="inquiryid" class="inquiries-header-cell"><span class="inquiries-header-label">INQUIRY ID</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter" data-col="inquiryid"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                    <th data-col="date" class="inquiries-header-cell"><span class="inquiries-header-label">INQUIRY DATE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter" data-col="date"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                    <th data-col="customername" class="inquiries-header-cell"><span class="inquiries-header-label">CUSTOMER NAME</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter" data-col="customername"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                    <th data-col="source" class="inquiries-header-cell"><span class="inquiries-header-label">SOURCE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter" data-col="source"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                    <th data-col="postcode" class="inquiries-header-cell"><span class="inquiries-header-label">POSTCODE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter" data-col="postcode"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                    <th data-col="city" class="inquiries-header-cell"><span class="inquiries-header-label">CITY</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter" data-col="city"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                    <th data-col="address" class="inquiries-header-cell"><span class="inquiries-header-label">ADDRESS</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter" data-col="address"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                    <th data-col="contactno" class="inquiries-header-cell"><span class="inquiries-header-label">CONTACT NO</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter" data-col="contactno"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                    <th data-col="businessnature" class="inquiries-header-cell"><span class="inquiries-header-label">BUSINESS NATURE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter" data-col="businessnature"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                    <th data-col="users" class="inquiries-header-cell"><span class="inquiries-header-label">USERS</span><span class="inquiries-filter-wrap dealer-operator-search-wrap"><span class="dealer-operator-search-box"><button type="button" class="dealer-operator-btn" data-col="users" data-op="=" aria-haspopup="true" aria-expanded="false" title="Filter operator">=</button><div class="dealer-operator-dropdown" hidden><button type="button" data-op="=">= Equals</button><button type="button" data-op="!=">!= Does not equal</button><button type="button" data-op="<">&lt; Less than</button><button type="button" data-op="<=">&lt;= Less than or equal to</button><button type="button" data-op=">">&gt; Greater than</button><button type="button" data-op=">=">&gt;= Greater than or equal to</button></div><input type="text" class="inquiries-grid-filter" data-col="users" placeholder="0"></span></span></th>
                    <th data-col="existingsw" class="inquiries-header-cell"><span class="inquiries-header-label">EXISTING SW</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter" data-col="existingsw"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                    <th data-col="demomode" class="inquiries-header-cell"><span class="inquiries-header-label">DEMO MODE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter" data-col="demomode"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                    <th data-col="products" class="inquiries-header-cell"><span class="inquiries-header-label">PRODUCTS</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter" data-col="products"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                    <th data-col="message" class="inquiries-header-cell"><span class="inquiries-header-label">MESSAGE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter" data-col="message"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                    <th data-col="referralcode" class="inquiries-header-cell"><span class="inquiries-header-label">REFERRAL CODE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter" data-col="referralcode"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                    <th data-col="status" class="inquiries-header-cell"><span class="inquiries-header-label">STATUS</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter" data-col="status"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                    <th class="inquiries-col-action inquiries-header-cell"><span class="inquiries-header-label">ACTION</span><button type="button" class="inquiries-filter-clear" id="inquiryClearFilters">Clear filters</button></th>
                </tr>
            </thead>
            <tbody>
                @forelse($unassigned as $r)
                <tr class="inquiry-row" data-search="{{ strtolower(($r->COMPANYNAME ?? '').' '.($r->CONTACTNAME ?? '').' '.($r->LEADID ?? '')) }}">
                    <td data-col="inquiryid">#SQL-{{ $r->LEADID }}</td>
                    <td data-col="date">{{ $r->CREATEDAT ? date('d/m/Y', strtotime($r->CREATEDAT)) : '—' }}</td>
                    @php
                        $company = trim((string)($r->COMPANYNAME ?? ''));
                        $contact = trim((string)($r->CONTACTNAME ?? ''));
                        $customerDisplay = $company !== '' && $contact !== '' ? ($company . ' - ' . $contact) : ($company !== '' ? $company : ($contact !== '' ? $contact : '—'));
                    @endphp
                    <td data-col="customername">{{ $customerDisplay }}</td>
                    <td data-col="source">{{ $r->CREATEDBY_NAME ?? ($r->CREATEDBY ?? '—') }}</td>
                    <td data-col="postcode">{{ $r->POSTCODE ?? '—' }}</td>
                    <td data-col="city">{{ $r->CITY ?? '—' }}</td>
                    @php
                        $addr1 = trim((string)($r->ADDRESS1 ?? ''));
                        $addr2 = trim((string)($r->ADDRESS2 ?? ''));
                        $addr = trim($addr1 . ' ' . $addr2);
                    @endphp
                    <td data-col="address">{{ $addr !== '' ? $addr : '—' }}</td>
                    <td data-col="contactno">{{ $r->CONTACTNO ?? '—' }}</td>
                    <td data-col="businessnature">{{ $r->BUSINESSNATURE ?? '—' }}</td>
                    <td data-col="users">{{ $r->USERCOUNT ?? '—' }}</td>
                    <td data-col="existingsw">{{ $r->EXISTINGSOFTWARE ?? '—' }}</td>
                    <td data-col="demomode">{{ $r->DEMOMODE ?? '—' }}</td>
                    <td data-col="products">
                        @php
                            $ids = $r->PRODUCTID ? array_map('trim', explode(',', (string)$r->PRODUCTID)) : [];
                            $dealtRaw = $r->DEALTPRODUCT ?? null;
                            $dealtProductIds = $dealtRaw ? array_map('trim', preg_split('/[\\s,\\(\\)]+/', (string)$dealtRaw)) : [];
                            // Group same-color products together for a cleaner look
                            $pillOrder = [
                                1 => 10, 3 => 11, 4 => 12, // Account group
                                2 => 20, 10 => 21,        // Payroll group
                                8 => 30, 5 => 31,         // X-Store group
                                6 => 40,                  // EBI POS
                                9 => 50,                  // Vision
                                7 => 60,                  // Sudu AI
                                11 => 70,                 // Others
                            ];
                            $ids = array_values(array_filter(array_unique(array_map('intval', $ids)), fn($v) => $v > 0));
                            $dealtProductIds = array_values(array_filter(array_unique(array_map('intval', $dealtProductIds)), fn($v) => $v > 0));
                            usort($ids, function($a, $b) use ($pillOrder) {
                                $oa = $pillOrder[$a] ?? (1000 + $a);
                                $ob = $pillOrder[$b] ?? (1000 + $b);
                                return $oa <=> $ob;
                            });
                            usort($dealtProductIds, function($a, $b) use ($pillOrder) {
                                $oa = $pillOrder[$a] ?? (1000 + $a);
                                $ob = $pillOrder[$b] ?? (1000 + $b);
                                return $oa <=> $ob;
                            });
                        @endphp
                        @if(!empty($ids))
                            <div class="inquiries-pill-group">
                                @foreach($ids as $id)
                                    @if(isset($productLabels[(int)$id]))
                                        <span class="inquiries-pill inquiries-pill-p{{ (int)$id }}">{{ $productLabels[(int)$id] }}</span>
                                    @endif
                                @endforeach
                            </div>
                        @else
                            —
                        @endif
                    </td>
                    @php
                        $fullMsg = (string)($r->DESCRIPTION ?? '');
                        $fullMsgTrim = trim($fullMsg);
                        $msgPreview = $fullMsgTrim === '' ? '—' : (mb_strlen($fullMsgTrim) > 30 ? (mb_substr($fullMsgTrim, 0, 30) . '…') : $fullMsgTrim);
                        $isLongMsg = $fullMsgTrim !== '' && mb_strlen($fullMsgTrim) > 30;
                    @endphp
                    <td data-col="message" class="inquiries-msg-cell {{ $isLongMsg ? 'inquiries-msg-clickable' : '' }}"
                        @if($isLongMsg) data-full-message="{{ e($fullMsgTrim) }}" @endif>
                        {{ $msgPreview }}
                    </td>
                    <td data-col="referralcode">{{ $r->REFERRALCODE ?? '—' }}</td>
                    @php
                        $rawStatus = strtoupper(trim((string)($r->CURRENTSTATUS ?? '')));
                        $statusClass = 'inquiries-status-new';
                        switch ($rawStatus) {
                            case 'CREATED':    $statusClass = 'inquiries-status-created'; break;
                            case 'PENDING':    $statusClass = 'inquiries-status-pending'; break;
                            case 'FOLLOWUP':   $statusClass = 'inquiries-status-followup'; break;
                            case 'FOLLOW UP':  $statusClass = 'inquiries-status-followup'; break;
                            case 'DEMO':       $statusClass = 'inquiries-status-demo'; break;
                            case 'CONFIRMED':  $statusClass = 'inquiries-status-confirmed'; break;
                            case 'CASE CONFIRMED': $statusClass = 'inquiries-status-confirmed'; break;
                            case 'COMPLETED':  $statusClass = 'inquiries-status-completed'; break;
                            case 'CASE COMPLETED': $statusClass = 'inquiries-status-completed'; break;
                            case 'REWARDED':   $statusClass = 'inquiries-status-rewarded'; break;
                            case 'REWARD DISTRIBUTED': $statusClass = 'inquiries-status-rewarded'; break;
                            case 'PAID':       $statusClass = 'inquiries-status-rewarded'; break;
                            case 'FAILED':     $statusClass = 'inquiries-status-failed'; break;
                            default:           $statusClass = 'inquiries-status-new'; break;
                        }
                    @endphp
                    <td data-col="status"><span class="inquiries-status {{ $statusClass }}">{{ $rawStatus !== '' ? $rawStatus : 'PENDING' }}</span></td>
                    <td class="inquiries-col-action inquiries-action-cell">
                        @php
                            $acompany = trim((string)($r->COMPANYNAME ?? ''));
                            $acontact = trim((string)($r->CONTACTNAME ?? ''));
                            $assignLeadLabel = $acompany !== '' && $acontact !== '' ? ($acompany . ' - ' . $acontact) : ($acompany !== '' ? $acompany : ($acontact !== '' ? $acontact : ('#SQL-' . ($r->LEADID ?? ''))));
                        @endphp
                        <button type="button" class="inquiries-btn inquiries-btn-assign inquiries-incoming-assign-btn" data-assign-lead="{{ $r->LEADID }}" data-assign-name="{{ e($assignLeadLabel) }}" title="Assign" aria-label="Assign"><i class="bi bi-person-check" aria-hidden="true"></i></button>
                        <a href="{{ route('admin.inquiries.edit', $r->LEADID) }}" class="inquiries-btn inquiries-btn-assign inquiries-edit-inquiry-btn" data-lead-id="{{ $r->LEADID }}" title="Edit" aria-label="Edit"><i class="bi bi-pencil-square" aria-hidden="true"></i></a>
                        <button type="button" class="inquiries-btn inquiries-btn-assign inquiries-delete-inquiry-btn" data-lead-id="{{ $r->LEADID }}" title="Delete" aria-label="Delete"><i class="bi bi-trash" aria-hidden="true"></i></button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="17" class="inquiries-empty">No unassigned inquiries.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
        @php
            $incomingPerPage = $incomingPerPage ?? 10;
            $incomingTotal = isset($unassignedTotal) ? $unassignedTotal : count($unassigned);
            $incomingTo = $incomingTotal === 0 ? 0 : min($incomingPerPage, $incomingTotal);
        @endphp
        <div class="inquiries-assigned-pagination" id="incomingPagination"
             data-incoming-total="{{ $incomingTotal }}"
             data-incoming-per-page="{{ $incomingPerPage }}"
             data-incoming-current-page="1">
            <span class="inquiries-assigned-pagination-info" id="incomingPaginationInfo">
                Showing {{ $incomingTotal === 0 ? 0 : 1 }} to {{ $incomingTo }} of {{ $incomingTotal }} entries (Page 1)
            </span>
            <div class="inquiries-assigned-pagination-nav">
                <button type="button" class="inquiries-btn inquiries-btn-secondary inquiries-pagination-btn" id="incomingPaginationFirst" aria-label="First page">First</button>
                <button type="button" class="inquiries-btn inquiries-btn-secondary inquiries-pagination-btn" id="incomingPaginationPrev" aria-label="Previous page">Previous</button>
                <span class="inquiries-assigned-page-numbers" id="incomingPageNumbers"></span>
                <button type="button" class="inquiries-btn inquiries-btn-secondary inquiries-pagination-btn" id="incomingPaginationNext" aria-label="Next page">Next</button>
                <button type="button" class="inquiries-btn inquiries-btn-secondary inquiries-pagination-btn" id="incomingPaginationLast" aria-label="Last page">Last</button>
            </div>
        </div>
    </div>
</section>
</div>

<div class="inquiries-tab-panel" id="assignedPanel" role="tabpanel" hidden>
<section class="inquiries-mgmt-panel">
    <div class="inquiries-panel-header">
        <div class="inquiries-panel-title-wrap">
            <i class="bi bi-check2-square inquiries-panel-icon"></i>
            <h2 class="inquiries-panel-title">Assigned Inquiries</h2>
        </div>
        <div class="inquiries-panel-actions">
            <button type="button" class="inquiries-btn inquiries-btn-secondary inquiries-sync-btn" data-sync-type="assigned" data-sync-url="{{ route('admin.inquiries.sync') }}">
                <i class="bi bi-arrow-repeat inquiries-sync-icon"></i>
                <span class="inquiries-sync-label">Sync</span>
            </button>
            <div class="inquiries-columns-dropdown">
                <button type="button" class="inquiries-btn inquiries-btn-secondary" id="assignedColumnsBtn" aria-haspopup="true" aria-expanded="false">Columns</button>
                <div class="inquiries-columns-menu" id="assignedColumnsMenu" hidden>
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
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="message"> MESSAGE</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="referralcode"> REFERRAL CODE</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="status"> STATUS</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="assignedby"> ASSIGNED BY</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="assignedto"> ASSIGNED TO</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="completiondate"> COMPLETION DATE</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="payoutsdate"> PAYOUTS DATE</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="attachment"> ATTACHMENT</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="assigndate"> ASSIGN DATE</label>
                    <div class="inquiries-columns-actions">
                        <button type="button" class="inquiries-columns-action-btn" id="assignedColumnsAll">All</button>
                        <button type="button" class="inquiries-columns-action-btn" id="assignedColumnsNone">None</button>
                    </div>
                    <button type="button" class="inquiries-columns-reset" id="assignedColumnsReset">Reset to default</button>
                </div>
            </div>
        </div>
    </div>
    <div class="inquiries-table-wrap">
        <div class="inquiries-table-scroll">
        <table class="inquiries-table" id="assignedTable">
                <thead>
                <tr class="inquiries-header-row">
                    <th data-col="inquiryid" class="inquiries-header-cell"><span class="inquiries-header-label">INQUIRY ID</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter-assigned" data-col="inquiryid"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                    <th data-col="date" class="inquiries-header-cell"><span class="inquiries-header-label">INQUIRY DATE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter-assigned" data-col="date"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                    <th data-col="customername" class="inquiries-header-cell"><span class="inquiries-header-label">CUSTOMER NAME</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter-assigned" data-col="customername"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                    <th data-col="source" class="inquiries-header-cell"><span class="inquiries-header-label">SOURCE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter-assigned" data-col="source"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                    <th data-col="postcode" class="inquiries-header-cell"><span class="inquiries-header-label">POSTCODE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter-assigned" data-col="postcode"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                    <th data-col="city" class="inquiries-header-cell"><span class="inquiries-header-label">CITY</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter-assigned" data-col="city"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                    <th data-col="address" class="inquiries-header-cell"><span class="inquiries-header-label">ADDRESS</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter-assigned" data-col="address"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                    <th data-col="contactno" class="inquiries-header-cell"><span class="inquiries-header-label">CONTACT NO</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter-assigned" data-col="contactno"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                    <th data-col="businessnature" class="inquiries-header-cell"><span class="inquiries-header-label">BUSINESS NATURE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter-assigned" data-col="businessnature"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                    <th data-col="users" class="inquiries-header-cell"><span class="inquiries-header-label">USERS</span><span class="inquiries-filter-wrap dealer-operator-search-wrap"><span class="dealer-operator-search-box"><button type="button" class="dealer-operator-btn" data-col="users" data-op="=" aria-haspopup="true" aria-expanded="false" title="Filter operator">=</button><div class="dealer-operator-dropdown" hidden><button type="button" data-op="=">= Equals</button><button type="button" data-op="!=">!= Does not equal</button><button type="button" data-op="<">&lt; Less than</button><button type="button" data-op="<=">&lt;= Less than or equal to</button><button type="button" data-op=">">&gt; Greater than</button><button type="button" data-op=">=">&gt;= Greater than or equal to</button></div><input type="text" class="inquiries-grid-filter-assigned" data-col="users" placeholder="0"></span></span></th>
                    <th data-col="existingsw" class="inquiries-header-cell"><span class="inquiries-header-label">EXISTING SW</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter-assigned" data-col="existingsw"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                    <th data-col="demomode" class="inquiries-header-cell"><span class="inquiries-header-label">DEMO MODE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter-assigned" data-col="demomode"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                    <th data-col="products" class="inquiries-header-cell"><span class="inquiries-header-label">PRODUCTS</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter-assigned" data-col="products"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                    <th data-col="dealtproducts" class="inquiries-header-cell"><span class="inquiries-header-label">DEALT PRODUCTS</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter-assigned" data-col="dealtproducts"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                    <th data-col="message" class="inquiries-header-cell"><span class="inquiries-header-label">MESSAGE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter-assigned" data-col="message"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                    <th data-col="referralcode" class="inquiries-header-cell"><span class="inquiries-header-label">REFERRAL CODE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter-assigned" data-col="referralcode"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                    <th data-col="assignedby" class="inquiries-header-cell"><span class="inquiries-header-label">ASSIGNED BY</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter-assigned" data-col="assignedby"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                    <th data-col="assignedto" class="inquiries-header-cell"><span class="inquiries-header-label">ASSIGNED TO</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter-assigned" data-col="assignedto"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                    <th data-col="completiondate" class="inquiries-header-cell"><span class="inquiries-header-label">COMPLETION DATE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter-assigned" data-col="completiondate"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                    <th data-col="payoutsdate" class="inquiries-header-cell"><span class="inquiries-header-label">PAYOUTS DATE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter-assigned" data-col="payoutsdate"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                    <th data-col="attachment" class="inquiries-header-cell"><span class="inquiries-header-label">ATTACHMENT</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter-assigned" data-col="attachment"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                    <th data-col="assigndate" class="inquiries-header-cell"><span class="inquiries-header-label">ASSIGN DATE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter-assigned" data-col="assigndate"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                    <th data-col="status" class="inquiries-header-cell"><span class="inquiries-header-label">STATUS</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter-assigned" data-col="status"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                    <th class="inquiries-col-action inquiries-header-cell"><span class="inquiries-header-label">ACTION</span><button type="button" class="inquiries-filter-clear" id="assignedClearFilters">Clear filters</button></th>
                    </tr>
                </thead>
                <tbody>
                @forelse($assigned as $r)
                <tr class="inquiry-row" data-search="{{ strtolower(($r->COMPANYNAME ?? '').' '.($r->CONTACTNAME ?? '').' '.($r->LEADID ?? '')) }}">
                    <td data-col="inquiryid">#SQL-{{ $r->LEADID }}</td>
                    <td data-col="date">{{ $r->CREATEDAT ? date('d/m/Y', strtotime($r->CREATEDAT)) : '—' }}</td>
                    @php
                        $ccompany = trim((string)($r->COMPANYNAME ?? ''));
                        $ccontact = trim((string)($r->CONTACTNAME ?? ''));
                        $custDisp = $ccompany !== '' && $ccontact !== '' ? ($ccompany . ' - ' . $ccontact) : ($ccompany !== '' ? $ccompany : ($ccontact !== '' ? $ccontact : '—'));
                    @endphp
                    <td data-col="customername">{{ $custDisp }}</td>
                    <td data-col="source">{{ $r->CREATEDBY_NAME ?? ($r->CREATEDBY ?? '—') }}</td>
                    <td data-col="postcode">{{ $r->POSTCODE ?? '—' }}</td>
                    <td data-col="city">{{ $r->CITY ?? '—' }}</td>
                    @php
                        $addr1 = trim((string)($r->ADDRESS1 ?? ''));
                        $addr2 = trim((string)($r->ADDRESS2 ?? ''));
                        $addr = trim($addr1 . ' ' . $addr2);
                    @endphp
                    <td data-col="address">{{ $addr !== '' ? $addr : '—' }}</td>
                    <td data-col="contactno">{{ $r->CONTACTNO ?? '—' }}</td>
                    <td data-col="businessnature">{{ $r->BUSINESSNATURE ?? '—' }}</td>
                    <td data-col="users">{{ $r->USERCOUNT ?? '—' }}</td>
                    <td data-col="existingsw">{{ $r->EXISTINGSOFTWARE ?? '—' }}</td>
                    <td data-col="demomode">{{ $r->DEMOMODE ?? '—' }}</td>
                    <td data-col="products">
                        @php
                            $ids = $r->PRODUCTID ? array_map('trim', explode(',', (string)$r->PRODUCTID)) : [];
                            $dealtRaw = $r->DEALTPRODUCT ?? null;
                            $dealtProductIds = $dealtRaw ? array_map('trim', preg_split('/[\s,\(\)]+/', (string)$dealtRaw)) : [];
                            $pillOrder = [
                                1 => 10, 3 => 11, 4 => 12,
                                2 => 20, 10 => 21,
                                8 => 30, 5 => 31,
                                6 => 40,
                                9 => 50,
                                7 => 60,
                                11 => 70,
                            ];
                            $ids = array_values(array_filter(array_unique(array_map('intval', $ids)), fn($v) => $v > 0));
                            $dealtProductIds = array_values(array_filter(array_unique(array_map('intval', $dealtProductIds)), fn($v) => $v > 0));
                            usort($ids, function($a, $b) use ($pillOrder) {
                                $oa = $pillOrder[$a] ?? (1000 + $a);
                                $ob = $pillOrder[$b] ?? (1000 + $b);
                                return $oa <=> $ob;
                            });
                            usort($dealtProductIds, function($a, $b) use ($pillOrder) {
                                $oa = $pillOrder[$a] ?? (1000 + $a);
                                $ob = $pillOrder[$b] ?? (1000 + $b);
                                return $oa <=> $ob;
                            });
                        @endphp
                        @if(!empty($ids))
                            <div class="inquiries-pill-group">
                                @foreach($ids as $id)
                                    @if(isset($productLabels[(int)$id]))
                                        <span class="inquiries-pill inquiries-pill-p{{ (int)$id }}">{{ $productLabels[(int)$id] }}</span>
                                    @endif
                                @endforeach
                            </div>
                        @else
                            —
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
                    @php
                        $afullMsg = (string)($r->DESCRIPTION ?? '');
                        $afullMsgTrim = trim($afullMsg);
                        $amsgPreview = $afullMsgTrim === '' ? '—' : (mb_strlen($afullMsgTrim) > 30 ? (mb_substr($afullMsgTrim, 0, 30) . '…') : $afullMsgTrim);
                        $aisLongMsg = $afullMsgTrim !== '' && mb_strlen($afullMsgTrim) > 30;
                    @endphp
                    <td data-col="message" class="inquiries-msg-cell {{ $aisLongMsg ? 'inquiries-msg-clickable' : '' }}"
                        @if($aisLongMsg) data-full-message="{{ e($afullMsgTrim) }}" @endif>
                        {{ $amsgPreview }}
                    </td>
                    <td data-col="referralcode">{{ $r->REFERRALCODE ?? '—' }}</td>
                                        <td data-col="assignedby">{{ $r->ASSIGNEDBY_NAME ?? ($r->ASSIGNEDBY ?? '—') }}</td>
                    <td data-col="assignedto">{{ $r->ASSIGNED_TO_NAME ?? ($r->ASSIGNED_TO ?? '—') }}</td>
                    <td data-col="completiondate">{{ !empty($r->COMPLETED_AT) ? date('d/m/Y', strtotime($r->COMPLETED_AT)) : '—' }}</td>
                    <td data-col="payoutsdate">{{ !empty($r->REWARDED_AT) ? date('d/m/Y', strtotime($r->REWARDED_AT)) : '—' }}</td>
                    <td data-col="attachment">
                        @php $assignedAttachUrls = !empty($r->ASSIGNED_ATTACHMENT_URLS) && is_array($r->ASSIGNED_ATTACHMENT_URLS) ? $r->ASSIGNED_ATTACHMENT_URLS : []; @endphp
                        @if(!empty($assignedAttachUrls))
                            <div class="payouts-attachment-list">
                                @foreach(array_slice($assignedAttachUrls, 0, 3) as $u)
                                    <a href="{{ $u }}" target="_blank" rel="noopener" class="payouts-attachment-link">
                                        <img src="{{ $u }}" alt="Attachment" class="payouts-attachment-thumb">
                                    </a>
                                @endforeach
                                @if(count($assignedAttachUrls) > 3)
                                    <span class="payouts-attachment-more">+{{ count($assignedAttachUrls) - 3 }}</span>
                                @endif
                            </div>
                        @else
                            —
                        @endif
                    </td>
                    <td data-col="assigndate">{{ $r->LASTMODIFIED ? date('d/m/Y', strtotime($r->LASTMODIFIED)) : ($r->CREATEDAT ? date('d/m/Y', strtotime($r->CREATEDAT)) : '—') }}</td>
                    @php
                        $arawStatus = strtoupper(trim((string)($r->CURRENTSTATUS ?? '')));
                        $astatusClass = 'inquiries-status-new';
                        switch ($arawStatus) {
                            case 'CREATED':    $astatusClass = 'inquiries-status-created'; break;
                            case 'PENDING':    $astatusClass = 'inquiries-status-pending'; break;
                            case 'FOLLOWUP':   $astatusClass = 'inquiries-status-followup'; break;
                            case 'FOLLOW UP':  $astatusClass = 'inquiries-status-followup'; break;
                            case 'DEMO':       $astatusClass = 'inquiries-status-demo'; break;
                            case 'CONFIRMED':  $astatusClass = 'inquiries-status-confirmed'; break;
                            case 'CASE CONFIRMED': $astatusClass = 'inquiries-status-confirmed'; break;
                            case 'COMPLETED':  $astatusClass = 'inquiries-status-completed'; break;
                            case 'CASE COMPLETED': $astatusClass = 'inquiries-status-completed'; break;
                            case 'REWARDED':   $astatusClass = 'inquiries-status-rewarded'; break;
                            case 'REWARD DISTRIBUTED': $astatusClass = 'inquiries-status-rewarded'; break;
                            case 'PAID':       $astatusClass = 'inquiries-status-rewarded'; break;
                            case 'FAILED':     $astatusClass = 'inquiries-status-failed'; break;
                            default:           $astatusClass = 'inquiries-status-new'; break;
                        }
                    @endphp
                    @php $arawStatusDisp = $arawStatus !== '' ? $arawStatus : 'PENDING'; @endphp
                    <td data-col="status"><span class="inquiries-status {{ $astatusClass }}">{{ $arawStatusDisp }}</span></td>
                    <td class="inquiries-col-action inquiries-action-cell">
                        <button type="button" class="inquiries-btn inquiries-btn-assign inquiries-view-status-btn" data-lead-id="{{ $r->LEADID }}" title="View Status" aria-label="View Status"><i class="bi bi-eye-fill" aria-hidden="true"></i></button>
                        <button type="button" class="inquiries-btn inquiries-btn-assign inquiries-btn-assign-danger inquiries-mark-failed-btn" data-lead-id="{{ $r->LEADID }}" data-status="{{ $arawStatusDisp }}" title="Mark As Failed" aria-label="Mark As Failed"><i class="bi bi-flag-fill" aria-hidden="true"></i></button>
                    </td>
                        </tr>
                    @empty
                    <tr><td colspan="24" class="inquiries-empty">No assigned inquiries.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="inquiries-assigned-pagination" id="assignedPagination" data-assigned-total="{{ $assignedTotal ?? 0 }}" data-assigned-last-page="{{ $assignedLastPage ?? 1 }}" data-assigned-current-page="1" data-assigned-per-page="{{ $assignedPerPage ?? 10 }}" data-assigned-page-url="{{ route('admin.inquiries.assigned-page') }}">
            @php
    $assignedPagPerPage = $assignedPerPage ?? 10;
    $assignedPagTotal = $assignedTotal ?? 0;
    $assignedPagTo = $assignedPagTotal === 0 ? 0 : min($assignedPagPerPage, $assignedPagTotal);
@endphp
            <span class="inquiries-assigned-pagination-info" id="assignedPaginationInfo">Showing {{ $assignedPagTotal === 0 ? 0 : 1 }} to {{ $assignedPagTo }} of {{ $assignedPagTotal }} entries (Page 1)</span>
            <div class="inquiries-assigned-pagination-nav">
                <button type="button" class="inquiries-btn inquiries-btn-secondary inquiries-pagination-btn" id="assignedPaginationFirst" aria-label="First page (latest inquiries)" title="First page – latest inquiries">First</button>
                <button type="button" class="inquiries-btn inquiries-btn-secondary inquiries-pagination-btn" id="assignedPaginationPrev" aria-label="Previous page">Previous</button>
                <span class="inquiries-assigned-page-numbers" id="assignedPageNumbers"></span>
                <button type="button" class="inquiries-btn inquiries-btn-secondary inquiries-pagination-btn" id="assignedPaginationNext" aria-label="Next page">Next</button>
                <button type="button" class="inquiries-btn inquiries-btn-secondary inquiries-pagination-btn" id="assignedPaginationLast" aria-label="Last page (oldest inquiries)" title="Last page – oldest inquiries">Last</button>
            </div>
        </div>
    </div>
</section>
</div>
</div>

<div class="inquiries-assign-modal" id="assignModal" hidden>
    <div class="inquiries-assign-backdrop" data-assign-close="1"></div>
    <div class="inquiries-assign-window" role="dialog" aria-modal="true" aria-labelledby="assignModalTitle">
        <div class="inquiries-assign-header">
            <div class="inquiries-assign-title" id="assignModalTitle">Assign lead</div>
            <button type="button" class="inquiries-assign-close" aria-label="Close" data-assign-close="1">&times;</button>
        </div>
        <form method="POST" action="{{ route('admin.inquiries.assign') }}" class="inquiries-assign-body">
            @csrf
            <input type="hidden" name="LEADID" id="assignLeadId">
            <input type="hidden" name="ASSIGNED_TO" id="assignToHidden" required>
            <div class="inquiries-assign-row">
                <div class="inquiries-assign-label">Lead</div>
                <div class="inquiries-assign-value" id="assignLeadLabel">—</div>
            </div>
            <div class="inquiries-assign-dealers">
                <div class="inquiries-assign-dealers-title">Select dealer</div>
                <div class="inquiries-assign-dealers-tablewrap">
                    <table class="inquiries-assign-dealers-table">
                        <thead>
                            <tr>
                                <th>Alias</th>
                                <th>Company</th>
                                <th>Postcode</th>
                                <th>City</th>
                                <th>Email</th>
                                <th>Active</th>
                                <th>Total Lead</th>
                                <th>Total Closed</th>
                                <th>Conversion Rate</th>
                            </tr>
                            <tr class="inquiries-assign-dealers-filter-row">
                                <th><span class="inquiries-filter-wrap"><input type="text" class="inquiries-assign-filter" data-col="alias"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                                <th><span class="inquiries-filter-wrap"><input type="text" class="inquiries-assign-filter" data-col="company"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                                <th><span class="inquiries-filter-wrap"><input type="text" class="inquiries-assign-filter" data-col="postcode"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                                <th><span class="inquiries-filter-wrap"><input type="text" class="inquiries-assign-filter" data-col="city"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                                <th><span class="inquiries-filter-wrap"><input type="text" class="inquiries-assign-filter" data-col="email"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                                <th><span class="inquiries-filter-wrap"><input type="text" class="inquiries-assign-filter" data-col="active"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                                <th><span class="inquiries-filter-wrap"><input type="text" class="inquiries-assign-filter" data-col="totallead"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                                <th><span class="inquiries-filter-wrap"><input type="text" class="inquiries-assign-filter" data-col="totalclosed"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                                <th><span class="inquiries-filter-wrap"><input type="text" class="inquiries-assign-filter" data-col="conversion"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse(($dealers ?? []) as $d)
                                @php
                                    $uid = trim((string)($d->USERID ?? ''));
                                    $email = trim((string)($d->EMAIL ?? ''));
                                    $postcode = trim((string)($d->POSTCODE ?? ''));
                                    $city = trim((string)($d->CITY ?? ''));
                                    $active = (int)($d->ISACTIVE ?? 0) ? 'Yes' : 'No';
                                    $company = trim((string)($d->COMPANY ?? ''));
                                    $alias = trim((string)($d->ALIAS ?? ''));
                                    $totalLead = (int)($d->TOTAL_LEAD ?? 0);
                                    $totalClosed = (int)($d->TOTAL_CLOSED ?? 0);
                                    $conv = (float)($d->CONVERSION_RATE ?? 0);
                                    $convLabel = $conv > 0 ? number_format($conv, 1) . '%' : '0%';
                                    $label = $alias !== '' ? $alias : ($company !== '' ? $company : ($email !== '' ? $email : $uid));
                                @endphp
                                <tr class="inquiries-assign-dealer-row"
                                    data-assign-userid="{{ $uid }}"
                                    data-assign-label="{{ e($label) }}"
                                    data-f-email="{{ e(strtolower($email)) }}"
                                    data-f-postcode="{{ e(strtolower($postcode)) }}"
                                    data-f-city="{{ e(strtolower($city)) }}"
                                    data-f-active="{{ e(strtolower($active)) }}"
                                    data-f-company="{{ e(strtolower($company)) }}"
                                    data-f-alias="{{ e(strtolower($alias)) }}"
                                    data-f-totallead="{{ $totalLead }}"
                                    data-f-totalclosed="{{ $totalClosed }}"
                                    data-f-conversion="{{ strtolower($convLabel) }}">
                                    <td>{{ $alias ?: '—' }}</td>
                                    <td>{{ $company ?: '—' }}</td>
                                    <td>{{ $postcode ?: '—' }}</td>
                                    <td>{{ $city ?: '—' }}</td>
                                    <td>{{ $email ?: '—' }}</td>
                                    <td>{{ $active }}</td>
                                    <td>{{ $totalLead }}</td>
                                    <td>{{ $totalClosed }}</td>
                                    <td>{{ $convLabel }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="inquiries-empty">No dealers found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="inquiries-assign-hint">Tip: click a row to select.</div>
            </div>
            <div class="inquiries-assign-actions">
                <button type="button" class="inquiries-btn inquiries-btn-secondary" data-assign-close="1">Cancel</button>
                <button type="submit" class="inquiries-btn inquiries-btn-primary" id="assignSubmitBtn" disabled>Assign</button>
            </div>
        </form>
    </div>
</div>

<div class="inquiries-assign-modal" id="statusModal" hidden>
    <div class="inquiries-assign-backdrop" data-status-close="1"></div>
    <div class="inquiries-assign-window" role="dialog" aria-modal="true" aria-labelledby="statusModalTitle">
        <div class="inquiries-assign-header">
            <div class="inquiries-assign-title" id="statusModalTitle">Status — Lead #SQL-<span id="statusModalLeadId"></span></div>
            <button type="button" class="inquiries-assign-close" aria-label="Close" data-status-close="1">&times;</button>
        </div>
        <div class="inquiries-assign-body">
            <div class="inquiries-status-table-wrap">
                <table class="inquiries-table">
                    <thead><tr><th>Date</th><th>Subject</th><th>Status</th><th>Description</th><th>User</th></tr></thead>
                    <tbody id="statusModalBody"></tbody>
                </table>
            </div>
            <p id="statusModalEmpty" class="inquiries-empty" style="display:none;">No status history.</p>
        </div>
    </div>
</div>

<div class="inquiries-assign-modal" id="markFailedModal" hidden>
    <div class="inquiries-assign-backdrop" data-markfailed-close="1"></div>
    <div class="inquiries-assign-window" role="dialog" aria-modal="true" aria-labelledby="markFailedModalTitle">
        <div class="inquiries-assign-header">
            <div class="inquiries-assign-title" id="markFailedModalTitle">Mark as Failed — Lead #SQL-<span id="markFailedModalLeadId"></span></div>
            <button type="button" class="inquiries-assign-close" aria-label="Close" data-markfailed-close="1">&times;</button>
        </div>
        <form id="markFailedForm" method="POST" action="{{ route('admin.inquiries.mark-failed') }}" class="inquiries-assign-body">
            @csrf
            <input type="hidden" name="LEADID" id="markFailedLeadId">
            <div class="inquiries-assign-row">
                <label class="inquiries-assign-label" for="markFailedDescription">Description <span class="required">*</span></label>
                <textarea id="markFailedDescription" name="DESCRIPTION" class="inquiry-form-input" rows="4" maxlength="4000" required></textarea>
            </div>
            <div class="inquiries-assign-actions">
                <button type="button" class="inquiries-btn inquiries-btn-secondary" data-markfailed-close="1">Cancel</button>
                <button type="submit" class="inquiries-btn inquiries-btn-primary">Mark As Failed</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
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
                    var date = it.CREATIONDATE ? String(it.CREATIONDATE).substring(0, 19) : '—';
                    tr.innerHTML = '<td>' + date + '</td><td>' + (it.SUBJECT || '—') + '</td><td>' + (it.STATUS || '—') + '</td><td>' + (it.DESCRIPTION || '—') + '</td><td>' + (it.USERID || '—') + '</td>';
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
    (function initMarkFailed() {
        var modal = document.getElementById('markFailedModal');
        var form = document.getElementById('markFailedForm');
        var input = document.getElementById('markFailedLeadId');
        var titleLeadId = document.getElementById('markFailedModalLeadId');
        var textarea = document.getElementById('markFailedDescription');
        if (!modal || !form || !input) return;
        function close() {
            modal.hidden = true;
            if (textarea) textarea.value = '';
        }
        document.addEventListener('click', function(e) {
            var btn = e.target && e.target.closest ? e.target.closest('.inquiries-mark-failed-btn') : null;
            if (btn) {
                var status = (btn.getAttribute('data-status') || '').toUpperCase();
                if (['COMPLETED', 'FAILED', 'REWARDED'].indexOf(status) !== -1) {
                    showMarkFailedBlockedToast();
                    return;
                }
                var leadId = btn.getAttribute('data-lead-id');
                if (leadId) {
                    input.value = leadId;
                    if (titleLeadId) titleLeadId.textContent = leadId;
                    if (textarea) textarea.value = '';
                    modal.hidden = false;
                    if (textarea) textarea.focus();
                }
                return;
            }
            if (e.target && (e.target.getAttribute('data-markfailed-close') === '1')) close();
        });
        function showMarkFailedBlockedToast() {
            var id = 'inquiries-mark-failed-blocked-toast';
            var el = document.getElementById(id);
            if (!el) {
                el = document.createElement('div');
                el.id = id;
                el.className = 'inquiries-mark-failed-blocked-toast';
                el.setAttribute('role', 'status');
                document.body.appendChild(el);
            }
            el.textContent = 'Status Completed, Failed or Rewarded cannot be marked as failed.';
            el.classList.remove('inquiries-mark-failed-blocked-toast-hidden');
            clearTimeout(el._hideTimer);
            el._hideTimer = setTimeout(function() {
                el.classList.add('inquiries-mark-failed-blocked-toast-hidden');
            }, 4000);
        }
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal && !modal.hidden) close();
        });
    })();

    // Message preview modal
    (function initMessageModal() {
        if (document.getElementById('inquiryMessageModal')) return;
        var modal = document.createElement('div');
        modal.id = 'inquiryMessageModal';
        modal.className = 'inquiries-msg-modal';
        modal.hidden = true;
        modal.innerHTML = ''
            + '<div class="inquiries-msg-modal-backdrop" data-close="1"></div>'
            + '<div class="inquiries-msg-modal-window" role="dialog" aria-modal="true" aria-labelledby="inquiriesMsgModalTitle">'
            + '  <div class="inquiries-msg-modal-header">'
            + '    <div class="inquiries-msg-modal-title" id="inquiriesMsgModalTitle">Message</div>'
            + '    <button type="button" class="inquiries-msg-modal-close" aria-label="Close" data-close="1">&times;</button>'
            + '  </div>'
            + '  <div class="inquiries-msg-modal-body"><pre class="inquiries-msg-modal-text" id="inquiriesMsgModalText"></pre></div>'
            + '</div>';
        document.body.appendChild(modal);

        function close() { modal.hidden = true; }
        function open(text) {
            var el = document.getElementById('inquiriesMsgModalText');
            if (el) el.textContent = text || '';
            modal.hidden = false;
        }

        document.addEventListener('click', function(e) {
            var cell = e.target && e.target.closest ? e.target.closest('.inquiries-msg-clickable[data-full-message]') : null;
            if (cell) {
                open(cell.getAttribute('data-full-message') || '');
                return;
            }
            if (e.target && (e.target.getAttribute('data-close') === '1')) close();
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !modal.hidden) close();
        });
    })();

    // Assign undo toast (Gmail-style)
    (function initAssignUndoToast() {
        var toast = document.getElementById('assignUndoToast');
        if (!toast) return;
        var form = document.getElementById('assignUndoForm');
        var leadInput = document.getElementById('assignUndoLeadId');
        var prevInput = document.getElementById('assignUndoPrevAssigned');
        if (!form || !leadInput || !prevInput) return;

        var leadId = toast.getAttribute('data-lead-id') || '';
        var prevAssigned = toast.getAttribute('data-prev-assigned-to') || '';
        if (!leadId) return;
        leadInput.value = leadId;
        prevInput.value = prevAssigned;

        toast.classList.remove('assign-undo-toast-hidden');

        var timer = setTimeout(function () {
            toast.classList.add('assign-undo-toast-hidden');
        }, 5000);

        var emailTimer = null;
        var newAssignedTo = toast.getAttribute('data-new-assigned-to') || '';
        if (leadId && newAssignedTo) {
            emailTimer = setTimeout(function () {
                var tok = (document.querySelector('meta[name="csrf-token"]') || {}).content;
                if (tok) {
                    fetch('{{ route('admin.inquiries.send-assignment-email') }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': tok },
                        body: JSON.stringify({ lead_id: parseInt(leadId, 10), assigned_to: newAssignedTo, _token: tok })
                    });
                }
            }, 6000);
        }

        var btn = toast.querySelector('.assign-undo-btn');
        if (btn) {
            btn.addEventListener('click', function () {
                clearTimeout(timer);
                if (emailTimer !== null) clearTimeout(emailTimer);
                form.submit();
            });
        }
    })();

    @if($assignEmailPending)
    (function () {
        var leadId = {{ (int) ($assignEmailPending['lead_id'] ?? 0) }};
        var assignedTo = {!! json_encode($assignEmailPending['assigned_to'] ?? '') !!};
        if (leadId && assignedTo) {
            setTimeout(function () {
                var tok = (document.querySelector('meta[name="csrf-token"]') || {}).content;
                if (tok) {
                    fetch('{{ route('admin.inquiries.send-assignment-email') }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': tok },
                        body: JSON.stringify({ lead_id: leadId, assigned_to: assignedTo, _token: tok })
                    });
                }
            }, 6000);
        }
    })();
    @endif

    var STORAGE_KEY = 'inquiryVisibleColumns_v2';
    var DEFAULT_COLUMNS = ['inquiryid', 'date', 'customername', 'postcode', 'city', 'businessnature', 'products', 'status'];
    var ASSIGNED_STORAGE_KEY = 'assignedVisibleColumns_v2';
    // Default Assigned layout (can still toggle INQUIRY DATE from Columns menu)
    var ASSIGNED_DEFAULT_COLUMNS = ['inquiryid', 'customername', 'postcode', 'city', 'assignedto', 'assigndate', 'status'];

    function updateTableScrollMode(table, visible, defaults) {
        if (!table) return;
        var scroller = table.closest('.inquiries-table-scroll');
        if (!scroller) return;
        var visibleCols = Array.isArray(visible) ? visible : [];
        var hasExtras = visibleCols.some(function(c) { return defaults.indexOf(c) === -1; });
        // If user selected "None", we keep current scrolling behavior (doesn't matter).
        var enabled = visibleCols.length > 0;

        // On smaller screens, always allow horizontal scroll so no columns feel "blocked".
        // Fit-mode is desktop-only.
        if (window.innerWidth && window.innerWidth < 1200) {
            scroller.classList.remove('inquiries-table-scroll--no-x');
            table.classList.remove('inquiries-table--fit');
            return;
        }

        scroller.classList.toggle('inquiries-table-scroll--no-x', enabled && !hasExtras);
        table.classList.toggle('inquiries-table--fit', enabled && !hasExtras);
    }

    function getVisibleColumns() {
        try {
            var raw = localStorage.getItem(STORAGE_KEY);
            if (raw !== null) {
                var arr = JSON.parse(raw);
                if (Array.isArray(arr)) return arr; // allow empty array for "None"
            }
        } catch (e) {}
        return DEFAULT_COLUMNS.slice();
    }

    function setVisibleColumns(cols) {
        try { localStorage.setItem(STORAGE_KEY, JSON.stringify(cols)); } catch (e) {}
    }

    function getAssignedVisibleColumns() {
        try {
            var raw = localStorage.getItem(ASSIGNED_STORAGE_KEY);
            if (raw !== null) {
                var arr = JSON.parse(raw);
                if (Array.isArray(arr)) return arr; // allow empty array for "None"
            }
        } catch (e) {}
        return ASSIGNED_DEFAULT_COLUMNS.slice();
    }
    function setAssignedVisibleColumns(cols) {
        try { localStorage.setItem(ASSIGNED_STORAGE_KEY, JSON.stringify(cols)); } catch (e) {}
    }

    function applyColumns(visible) {
        var table = document.getElementById('unassignedTable');
        if (!table) return;
        var allCols = ['inquiryid','date','customername','source','city','postcode','address','contactno','businessnature','users','existingsw','demomode','products','message','referralcode','status'];
        allCols.forEach(function(col) {
            var show = visible.indexOf(col) !== -1;
            table.querySelectorAll('th[data-col="' + col + '"], td[data-col="' + col + '"]').forEach(function(el) {
                el.style.display = show ? '' : 'none';
            });
        });

        // If user selected "None", hide ACTION as well
        var showAction = Array.isArray(visible) && visible.length > 0;
        table.querySelectorAll('th.inquiries-col-action, td.inquiries-col-action').forEach(function(el) {
            el.style.display = showAction ? '' : 'none';
        });

        updateTableScrollMode(table, visible, DEFAULT_COLUMNS);
    }

    function applyAssignedColumns(visible) {
        var table = document.getElementById('assignedTable');
        if (!table) return;
        var allCols = ['inquiryid','date','customername','source','city','postcode','address','contactno','businessnature','users','existingsw','demomode','products','dealtproducts','message','referralcode','assignedby','assignedto','completiondate','payoutsdate','attachment','assigndate','status'];
        allCols.forEach(function(col) {
            var show = visible.indexOf(col) !== -1;
            table.querySelectorAll('th[data-col="' + col + '"], td[data-col="' + col + '"]').forEach(function(el) {
                el.style.display = show ? '' : 'none';
            });
        });

        var showAction = Array.isArray(visible) && visible.length > 0;
        table.querySelectorAll('th.inquiries-col-action, td.inquiries-col-action').forEach(function(el) {
            el.style.display = showAction ? '' : 'none';
        });

        updateTableScrollMode(table, visible, ASSIGNED_DEFAULT_COLUMNS);
    }

    // Columns are not user-resizable (fixed by CSS)

    function syncCheckboxes(visible) {
        var menu = document.getElementById('inquiryColumnsMenu');
        if (!menu) return;
        menu.querySelectorAll('input[data-col]').forEach(function(cb) {
            var col = cb.getAttribute('data-col');
            cb.checked = visible.indexOf(col) !== -1;
        });
    }

    function refreshColumnState() {
        var visible = getVisibleColumns();
        syncCheckboxes(visible);
        applyColumns(visible);
    }

    function syncAssignedCheckboxes(visible) {
        var menu = document.getElementById('assignedColumnsMenu');
        if (!menu) return;
        menu.querySelectorAll('input[data-col]').forEach(function(cb) {
            var col = cb.getAttribute('data-col');
            cb.checked = visible.indexOf(col) !== -1;
        });
    }
    function refreshAssignedColumnState() {
        var visible = getAssignedVisibleColumns();
        syncAssignedCheckboxes(visible);
        applyAssignedColumns(visible);
    }

    var colBtn = document.getElementById('inquiryColumnsBtn');
    var colMenu = document.getElementById('inquiryColumnsMenu');
    if (colBtn && colMenu) {
        colBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            var isOpen = !colMenu.hidden;
            colMenu.hidden = isOpen;
            colBtn.setAttribute('aria-expanded', !isOpen);
            if (!isOpen) {
                colMenu.scrollTop = 0;
                refreshColumnState();
            }
        });
        document.addEventListener('click', function() {
            colMenu.hidden = true;
            colBtn.setAttribute('aria-expanded', 'false');
        });
        colMenu.addEventListener('click', function(e) { e.stopPropagation(); });
    }

    var menu = document.getElementById('inquiryColumnsMenu');
    if (menu) {
        menu.querySelectorAll('input[data-col]').forEach(function(cb) {
            cb.addEventListener('change', function() {
                var visible = [];
                menu.querySelectorAll('input[data-col]:checked').forEach(function(c) {
                    visible.push(c.getAttribute('data-col'));
                });
                setVisibleColumns(visible);
                applyColumns(visible);
            });
        });
    }

    var resetBtn = document.getElementById('inquiryColumnsReset');
    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            setVisibleColumns(DEFAULT_COLUMNS.slice());
            refreshColumnState();
            var wrap = document.querySelector('#incomingPanel .inquiries-table-scroll');
            if (wrap) wrap.scrollLeft = 0;
        });
    }

    var allBtn = document.getElementById('inquiryColumnsAll');
    if (allBtn) {
        allBtn.addEventListener('click', function() {
            var allCols = ['inquiryid','date','customername','source','city','postcode','address','contactno','businessnature','users','existingsw','demomode','products','message','referralcode','status'];
            setVisibleColumns(allCols.slice());
            refreshColumnState();
        });
    }

    var noneBtn = document.getElementById('inquiryColumnsNone');
    if (noneBtn) {
        noneBtn.addEventListener('click', function() {
            setVisibleColumns([]);
            refreshColumnState();
        });
    }

    refreshColumnState();
    refreshAssignedColumnState();

    // Assigned columns dropdown
    var aBtn = document.getElementById('assignedColumnsBtn');
    var aMenu = document.getElementById('assignedColumnsMenu');
    if (aBtn && aMenu) {
        aBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            var isOpen = !aMenu.hidden;
            aMenu.hidden = isOpen;
            aBtn.setAttribute('aria-expanded', !isOpen);
            if (!isOpen) {
                aMenu.scrollTop = 0;
                refreshAssignedColumnState();
            }
        });
        document.addEventListener('click', function() {
            aMenu.hidden = true;
            aBtn.setAttribute('aria-expanded', 'false');
        });
        aMenu.addEventListener('click', function(e) { e.stopPropagation(); });
    }
    if (aMenu) {
        aMenu.querySelectorAll('input[data-col]').forEach(function(cb) {
            cb.addEventListener('change', function() {
                var visible = [];
                aMenu.querySelectorAll('input[data-col]:checked').forEach(function(c) {
                    visible.push(c.getAttribute('data-col'));
                });
                setAssignedVisibleColumns(visible);
                applyAssignedColumns(visible);
            });
        });
    }
    var aReset = document.getElementById('assignedColumnsReset');
    if (aReset) {
        aReset.addEventListener('click', function() {
            setAssignedVisibleColumns(ASSIGNED_DEFAULT_COLUMNS.slice());
            refreshAssignedColumnState();
            var wrap = document.querySelector('#assignedPanel .inquiries-table-scroll');
            if (wrap) wrap.scrollLeft = 0;
        });
    }

    var aAll = document.getElementById('assignedColumnsAll');
    if (aAll) {
        aAll.addEventListener('click', function() {
            var allCols = ['inquiryid','date','customername','source','city','postcode','address','contactno','businessnature','users','existingsw','demomode','products','dealtproducts','message','referralcode','assignedby','assignedto','completiondate','payoutsdate','attachment','assigndate','status'];
            setAssignedVisibleColumns(allCols.slice());
            refreshAssignedColumnState();
        });
    }

    var aNone = document.getElementById('assignedColumnsNone');
    if (aNone) {
        aNone.addEventListener('click', function() {
            setAssignedVisibleColumns([]);
            refreshAssignedColumnState();
        });
    }

    function rowMatchesBigSearch(row, q) {
        if (!q) return true;
        var idCell = row.querySelector('td[data-col="inquiryid"]');
        var nameCell = row.querySelector('td[data-col="customername"]');
        var idText = (idCell && idCell.textContent) ? idCell.textContent.toLowerCase().trim() : '';
        var nameText = (nameCell && nameCell.textContent) ? nameCell.textContent.toLowerCase().trim() : '';
        var searchable = (idText + ' ' + nameText).replace(/\s+/g, '');
        var normQ = q.replace(/\s+/g, '');
        return searchable.indexOf(normQ) !== -1;
    }

    window.bigSearchSubmitted = false;
    window.getBigSearchQuery = function() {
        if (!window.bigSearchSubmitted) return '';
        var searchInput = document.getElementById('inquirySearchInput');
        return (searchInput && searchInput.value) ? (searchInput.value || '').toLowerCase().trim().replace(/\s+/g, ' ') : '';
    };

    var INQUIRY_NUMERIC_FILTER_COLS = ['users'];

    function parseInquiryFilterNumber(value) {
        var num = parseFloat(String(value || '').replace(/[^0-9.\-]/g, ''));
        return isNaN(num) ? 0 : num;
    }

    function collectInquiryColumnFilters(tableEl, inputSelector) {
        var filters = {};
        if (!tableEl) return filters;
        tableEl.querySelectorAll(inputSelector).forEach(function(inp) {
            var col = inp.getAttribute('data-col');
            var val = (inp.value || '').trim();
            if (!col || val === '') return;
            if (INQUIRY_NUMERIC_FILTER_COLS.indexOf(col) !== -1) {
                var opBtn = tableEl.querySelector('.dealer-operator-btn[data-col="' + col + '"]');
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

    function inquiryRowMatchesColumnFilters(row, filters) {
        for (var col in filters) {
            var cell = row.querySelector('td[data-col="' + col + '"]');
            var cellText = (cell && cell.textContent) ? cell.textContent.trim() : '';
            var filter = filters[col];
            if (filter.numeric) {
                var cellNum = parseInquiryFilterNumber(cellText);
                var filterNum = parseInquiryFilterNumber(filter.val);
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

    function bindInquiryOperatorMenus(tableEl, applyFn) {
        if (!tableEl) return;
        tableEl.querySelectorAll('.dealer-operator-btn').forEach(function(btn) {
            if (btn.getAttribute('data-operator-bound') === '1') return;
            btn.setAttribute('data-operator-bound', '1');
            var dropdown = btn.parentElement.querySelector('.dealer-operator-dropdown');
            if (!dropdown) return;
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                var isOpen = !dropdown.hidden;
                tableEl.querySelectorAll('.dealer-operator-dropdown').forEach(function(d) { d.hidden = true; });
                tableEl.querySelectorAll('.dealer-operator-btn').forEach(function(b) { b.setAttribute('aria-expanded', 'false'); });
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
                    applyFn();
                });
            });
        });
    }

    function resetInquiryOperatorMenus(tableEl) {
        if (!tableEl) return;
        tableEl.querySelectorAll('.dealer-operator-btn').forEach(function(btn) {
            btn.setAttribute('data-op', '=');
            btn.textContent = '=';
            btn.setAttribute('aria-expanded', 'false');
        });
        tableEl.querySelectorAll('.dealer-operator-dropdown').forEach(function(dropdown) {
            dropdown.hidden = true;
        });
    }

    function applyGridFilters() {
        var table = document.getElementById('unassignedTable');
        var q = window.getBigSearchQuery ? window.getBigSearchQuery() : '';
        var filters = {};
        if (table) {
            filters = collectInquiryColumnFilters(table, '.inquiries-grid-filter');
            table.querySelectorAll('tbody .inquiry-row').forEach(function(row) {
                var searchMatch = rowMatchesBigSearch(row, q);
                var colMatch = inquiryRowMatchesColumnFilters(row, filters);
                row.style.display = (searchMatch && colMatch) ? '' : 'none';
            });
            if (typeof window.refreshIncomingPagination === 'function') window.refreshIncomingPagination();
        }
    }

    function applyAssignedGridFilters() {
        if (typeof window.refreshAssignedPagination === 'function') window.refreshAssignedPagination();
    }

    var table = document.getElementById('unassignedTable');
    if (table) {
        table.querySelectorAll('.inquiries-grid-filter').forEach(function(inp) {
            inp.addEventListener('input', applyGridFilters);
            inp.addEventListener('keyup', applyGridFilters);
        });
        bindInquiryOperatorMenus(table, applyGridFilters);
    }
    var clearFiltersBtn = document.getElementById('inquiryClearFilters');
    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', function() {
            var t = document.getElementById('unassignedTable');
            if (t) t.querySelectorAll('.inquiries-grid-filter').forEach(function(inp) { inp.value = ''; });
            resetInquiryOperatorMenus(t);
            applyGridFilters();
            if (typeof clearInquiriesSort === 'function') clearInquiriesSort('unassignedTable');
        });
    }

    var assignedTable = document.getElementById('assignedTable');
    if (assignedTable) {
        assignedTable.querySelectorAll('.inquiries-grid-filter-assigned').forEach(function(inp) {
            inp.addEventListener('input', applyAssignedGridFilters);
            inp.addEventListener('keyup', applyAssignedGridFilters);
        });
        bindInquiryOperatorMenus(assignedTable, applyAssignedGridFilters);
    }
    var assignedClearBtn = document.getElementById('assignedClearFilters');
    if (assignedClearBtn) {
        assignedClearBtn.addEventListener('click', function() {
            var t = document.getElementById('assignedTable');
            if (t) t.querySelectorAll('.inquiries-grid-filter-assigned').forEach(function(inp) { inp.value = ''; });
            resetInquiryOperatorMenus(t);
            applyAssignedGridFilters();
            if (typeof clearInquiriesSort === 'function') clearInquiriesSort('assignedTable');
        });
    }

    document.addEventListener('click', function() {
        [document.getElementById('unassignedTable'), document.getElementById('assignedTable')].forEach(function(tableEl) {
            if (!tableEl) return;
            tableEl.querySelectorAll('.dealer-operator-dropdown').forEach(function(dropdown) { dropdown.hidden = true; });
            tableEl.querySelectorAll('.dealer-operator-btn').forEach(function(btn) { btn.setAttribute('aria-expanded', 'false'); });
        });
    });

    var clearSearchBtn = document.getElementById('inquiryClearSearchBtn');
    if (clearSearchBtn) {
        clearSearchBtn.addEventListener('click', function() {
            window.bigSearchSubmitted = false;
            var searchInput = document.getElementById('inquirySearchInput');
            if (searchInput) searchInput.value = '';
            applyGridFilters();
            applyAssignedGridFilters();
            if (typeof window.refreshIncomingPagination === 'function') window.refreshIncomingPagination();
            if (typeof window.refreshAssignedPagination === 'function') window.refreshAssignedPagination();
        });
    }

    var input = document.getElementById('inquirySearchInput');
    var btn = document.getElementById('inquirySearchBtn');
    if (input) {
        function filterRows() {
            window.bigSearchSubmitted = true;
            applyGridFilters();
            applyAssignedGridFilters();
            if (typeof window.refreshIncomingPagination === 'function') window.refreshIncomingPagination();
            if (typeof window.refreshAssignedPagination === 'function') window.refreshAssignedPagination();
        }
        if (btn) btn.addEventListener('click', filterRows);
        input.addEventListener('keydown', function(e) { if (e.key === 'Enter') filterRows(); });
        input.addEventListener('input', function() {
            // Auto search as user types, same behaviour as payouts search
            filterRows();
        });
    }
    applyGridFilters();
    applyAssignedGridFilters();

    // Sort by column (Incoming + Assigned)
    var unassignedSort = { col: null, dir: 1 };
    var assignedSort = { col: null, dir: 1 };
    function getSortValue(row, col) {
        var cell = row.querySelector('td[data-col="' + col + '"]');
        return (cell && cell.textContent) ? cell.textContent.trim().toLowerCase() : '';
    }
    function sortInquiriesTable(tableId, state) {
        var table = document.getElementById(tableId);
        if (!table) return;
        var tbody = table.querySelector('tbody');
        if (!tbody || !state.col) return;
        var rows = [].slice.call(tbody.querySelectorAll('tr.inquiry-row'));
        var emptyRow = tbody.querySelector('tr:not(.inquiry-row)');
        rows.sort(function(a, b) {
            var va = getSortValue(a, state.col);
            var vb = getSortValue(b, state.col);
            var cmp = va.localeCompare(vb, undefined, { numeric: true });
            return state.dir * cmp;
        });
        rows.forEach(function(r) { tbody.appendChild(r); });
        if (emptyRow) tbody.appendChild(emptyRow);
    }
    function setInitialOrder(tableId) {
        var table = document.getElementById(tableId);
        if (!table) return;
        var tbody = table.querySelector('tbody');
        if (!tbody) return;
        tbody.querySelectorAll('tr.inquiry-row').forEach(function(row, i) {
            row.setAttribute('data-initial-index', String(i));
        });
    }
    function clearInquiriesSort(tableId) {
        var table = document.getElementById(tableId);
        if (!table) return;
        var state = tableId === 'unassignedTable' ? unassignedSort : assignedSort;
        state.col = null;
        state.dir = 1;
        table.querySelectorAll('thead th[data-col]').forEach(function(h) {
            h.classList.remove('inquiries-sort-asc', 'inquiries-sort-desc');
        });
        var tbody = table.querySelector('tbody');
        if (!tbody) return;
        var rows = [].slice.call(tbody.querySelectorAll('tr.inquiry-row'));
        var emptyRow = Array.from(tbody.querySelectorAll('tr')).find(function(row) {
            return !row.classList.contains('inquiry-row') && !!row.querySelector('.inquiries-empty');
        }) || null;
        rows.sort(function(a, b) {
            var ia = parseInt(a.getAttribute('data-initial-index') || '0', 10);
            var ib = parseInt(b.getAttribute('data-initial-index') || '0', 10);
            return ia - ib;
        });
        rows.forEach(function(r) { tbody.appendChild(r); });
        if (rows.length === 0 && emptyRow) tbody.appendChild(emptyRow);
    }
    function initSortableInquiries() {
        ['unassignedTable', 'assignedTable'].forEach(function(tableId) {
            var table = document.getElementById(tableId);
            if (!table) return;
            var state = tableId === 'unassignedTable' ? unassignedSort : assignedSort;
            table.querySelectorAll('thead th[data-col]').forEach(function(th) {
                th.classList.add('inquiries-sortable');
                th.style.cursor = 'pointer';
                th.addEventListener('click', function(e) {
                    if (e.target.tagName === 'INPUT' || e.target.tagName === 'BUTTON' || e.target.closest('button')) return;
                    var col = th.getAttribute('data-col');
                    if (!col) return;
                    state.dir = (state.col === col) ? -state.dir : 1;
                    state.col = col;
                    table.querySelectorAll('thead th[data-col]').forEach(function(h) {
                        h.classList.remove('inquiries-sort-asc', 'inquiries-sort-desc');
                        if (h.getAttribute('data-col') === col) {
                            h.classList.add(state.dir === 1 ? 'inquiries-sort-asc' : 'inquiries-sort-desc');
                        }
                    });
                    sortInquiriesTable(tableId, state);
                });
            });
        });
        setInitialOrder('unassignedTable');
        setInitialOrder('assignedTable');
    }
    initSortableInquiries();

    function normalizeInquiryTbody(tbody) {
        if (!tbody) return;
        Array.from(tbody.querySelectorAll('tr')).forEach(function(row) {
            if (row.classList.contains('inquiry-row')) {
                var rowText = (row.textContent || '').replace(/\s+/g, '');
                var hasVisualContent = !!row.querySelector('button, a, img, .inquiries-status, .inquiries-pill, .payouts-attachment-list');
                if (rowText === '' && !hasVisualContent) {
                    row.remove();
                }
                return;
            }
            if (row.querySelector('.inquiries-empty')) return;
            var cells = row.querySelectorAll('td,th');
            var text = (row.textContent || '').replace(/\s+/g, '');
            if (cells.length === 0 || text === '') {
                row.remove();
            }
        });
    }

    function resetInquiryTableScroll(panelId) {
        var panel = document.getElementById(panelId);
        if (!panel) return;
        var scrollWrap = panel.querySelector('.inquiries-table-scroll');
        if (!scrollWrap) return;
        scrollWrap.scrollTop = 0;
    }

    function showInquiriesActionToast(message) {
        var id = 'inquiries-action-toast';
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

    function shouldAutoSyncInquiries(message) {
        var msg = (message || '').toLowerCase();
        return msg.indexOf('please sync and try again') !== -1 || msg.indexOf('already assigned') !== -1 || msg.indexOf('already rewarded') !== -1 || msg.indexOf('already completed') !== -1 || msg.indexOf('already failed') !== -1;
    }

    document.querySelectorAll('.inquiries-tab').forEach(function(tab) {
        tab.addEventListener('click', function() {
            var t = this.getAttribute('data-tab');
            document.querySelectorAll('.inquiries-tab').forEach(function(bt) {
                bt.classList.toggle('active', bt.getAttribute('data-tab') === t);
                bt.setAttribute('aria-selected', bt.getAttribute('data-tab') === t ? 'true' : 'false');
            });
            var incomingPanel = document.getElementById('incomingPanel');
            var assignedPanel = document.getElementById('assignedPanel');
            if (incomingPanel) { incomingPanel.classList.toggle('active', t === 'incoming'); incomingPanel.hidden = t !== 'incoming'; }
            if (assignedPanel) { assignedPanel.classList.toggle('active', t === 'assigned'); assignedPanel.hidden = t !== 'assigned'; }

            var incomingSummary = document.getElementById('incomingSummaryCard');
            var assignedSummary = document.getElementById('assignedSummaryCard');
            if (incomingSummary && assignedSummary) {
                if (t === 'incoming') {
                    incomingSummary.style.display = '';
                    assignedSummary.style.display = 'none';
                } else {
                    incomingSummary.style.display = 'none';
                    assignedSummary.style.display = '';
                }
            }
        });
    });

    // Assign modal
    (function initAssignModal() {
        var modal = document.getElementById('assignModal');
        if (!modal) return;
        var leadIdInput = document.getElementById('assignLeadId');
        var leadLabel = document.getElementById('assignLeadLabel');
        var hiddenTo = document.getElementById('assignToHidden');
        var assignBtn = document.getElementById('assignSubmitBtn');

        function close() { modal.hidden = true; }
        function open(leadId, label) {
            if (leadIdInput) leadIdInput.value = String(leadId || '');
            if (leadLabel) leadLabel.textContent = label || ('#SQL-' + leadId);
            if (hiddenTo) hiddenTo.value = '';
            if (assignBtn) assignBtn.disabled = true;
            modal.querySelectorAll('.inquiries-assign-filter').forEach(function(inp) { inp.value = ''; });
            modal.querySelectorAll('.inquiries-assign-dealer-row').forEach(function(r) { r.style.display = ''; });
            modal.querySelectorAll('.inquiries-assign-dealer-row').forEach(function(r) { r.classList.remove('selected'); });
            modal.hidden = false;
        }

        function applyDealerFilters() {
            var filters = {};
            modal.querySelectorAll('.inquiries-assign-filter').forEach(function(inp) {
                var col = inp.getAttribute('data-col');
                var val = (inp.value || '').toLowerCase().trim();
                if (col && val) filters[col] = val;
            });
            modal.querySelectorAll('.inquiries-assign-dealer-row').forEach(function(row) {
                var ok = true;
                for (var col in filters) {
                    var hay = (row.getAttribute('data-f-' + col) || '');
                    if (hay.indexOf(filters[col]) === -1) { ok = false; break; }
                }
                row.style.display = ok ? '' : 'none';
            });
        }

        modal.querySelectorAll('.inquiries-assign-filter').forEach(function(inp) {
            inp.addEventListener('input', applyDealerFilters);
            inp.addEventListener('keyup', applyDealerFilters);
        });

        document.addEventListener('click', function(e) {
            var btn = e.target && e.target.closest ? e.target.closest('[data-assign-lead]') : null;
            if (btn) {
                open(btn.getAttribute('data-assign-lead'), btn.getAttribute('data-assign-name'));
                return;
            }
            var row = e.target && e.target.closest ? e.target.closest('.inquiries-assign-dealer-row') : null;
            if (row && modal.contains(row)) {
                var uid = row.getAttribute('data-assign-userid') || '';
                if (hiddenTo) hiddenTo.value = uid;
                if (assignBtn) assignBtn.disabled = !uid;
                modal.querySelectorAll('.inquiries-assign-dealer-row.selected').forEach(function(r) { r.classList.remove('selected'); });
                row.classList.add('selected');
                return;
            }
            if (e.target && e.target.getAttribute && e.target.getAttribute('data-assign-close') === '1') close();
        });
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !modal.hidden) close();
        });
    })();

    // Delete inquiry (Incoming): confirm, then POST; show undo toast on success
    (function initDeleteInquiry() {
        var baseUrl = '{{ route("admin.inquiries") }}';
        var deleteToast = document.getElementById('deleteUndoToast');
        var deleteToastMessage = document.getElementById('deleteUndoToastMessage');
        var deleteUndoForm = document.getElementById('deleteUndoForm');
        var deleteUndoLeadId = document.getElementById('deleteUndoLeadId');
        var deleteUndoBtn = document.getElementById('deleteUndoBtn');
        var deleteUndoTimer = null;

        function showDeleteUndoToast(leadId) {
            if (!deleteToast || !deleteToastMessage || !deleteUndoForm || !deleteUndoLeadId) return;
            deleteToastMessage.textContent = 'Lead #SQL-' + leadId + ' deleted.';
            deleteUndoLeadId.value = leadId;
            deleteToast.style.display = '';
            deleteToast.classList.remove('assign-undo-toast-hidden');
            if (deleteUndoTimer) clearTimeout(deleteUndoTimer);
            deleteUndoTimer = setTimeout(function() {
                deleteToast.classList.add('assign-undo-toast-hidden');
                deleteToast.style.display = 'none';
            }, 5000);
        }

        if (deleteUndoBtn && deleteUndoForm) {
            deleteUndoBtn.addEventListener('click', function() {
                if (deleteUndoTimer) clearTimeout(deleteUndoTimer);
                deleteUndoForm.submit();
            });
        }

        document.addEventListener('click', function(e) {
            var btn = e.target && e.target.closest ? e.target.closest('.inquiries-delete-inquiry-btn') : null;
            if (!btn) return;
            e.preventDefault();
            var leadId = btn.getAttribute('data-lead-id');
            if (!leadId) return;
            if (!confirm('Delete inquiry #SQL-' + leadId + '? You can undo this from the message below.')) return;
            var deleteUrl = baseUrl + '/' + leadId + '/delete';
            var csrf = document.querySelector('meta[name="csrf-token"]');
            var token = csrf ? csrf.getAttribute('content') : '';
            fetch(deleteUrl, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': token, 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({ _token: token })
            }).then(function(res) {
                if (res.ok) {
                    var row = btn.closest('tr');
                    if (row) row.remove();
                    showDeleteUndoToast(leadId);
                } else {
                    res.json().catch(function() { return {}; }).then(function(d) {
                        var message = d.message || 'Could not delete inquiry.';
                        showInquiriesActionToast(message);
                        if (shouldAutoSyncInquiries(message)) {
                            var syncBtn = document.querySelector('.inquiries-sync-btn[data-sync-type="incoming"]') || document.querySelector('.inquiries-sync-btn');
                            triggerInquiriesSync(syncBtn);
                        }
                    });
                }
            }).catch(function() { showInquiriesActionToast('Could not delete inquiry.'); });
        });
    })();

    // Sync buttons (fetch latest inquiries without full reload)
    var INQUIRIES_AUTO_SYNC_MS = 15 * 60 * 1000;

    function triggerInquiriesSync(btn) {
        if (!btn || btn.classList.contains('is-syncing')) return;
        btn.classList.add('is-syncing');
        var icon = btn.querySelector('.inquiries-sync-icon');
        if (icon) {
            icon.classList.add('spinning');
        }
        var url = btn.getAttribute('data-sync-url');
        if (!url) url = window.location.href;
        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            cache: 'no-store'
        }).then(function(res) {
            return res.ok ? res.json() : Promise.reject();
        }).then(function(data) {
            var ua = document.querySelector('#unassignedTable tbody');
            if (ua && data.unassigned !== undefined) {
                ua.innerHTML = data.unassigned;
                normalizeInquiryTbody(ua);
                resetInquiryTableScroll('incomingPanel');
            }
            var as = document.querySelector('#assignedTable tbody');
            if (as && data.assigned !== undefined) {
                as.innerHTML = data.assigned;
                normalizeInquiryTbody(as);
                resetInquiryTableScroll('assignedPanel');
            }

            // Update summary counts
            var incomingSummary = document.getElementById('incomingSummaryCard');
            var assignedSummary = document.getElementById('assignedSummaryCard');
            if (data.totalNewInquiries !== undefined && incomingSummary) {
                var v1 = incomingSummary.querySelector('.inquiries-summary-value');
                if (v1) v1.textContent = new Intl.NumberFormat().format(data.totalNewInquiries);
            }
            if (data.totalOngoing !== undefined && assignedSummary) {
                var v2 = assignedSummary.querySelector('.inquiries-summary-value');
                if (v2) v2.textContent = new Intl.NumberFormat().format(data.totalOngoing);
            }
            var incomingPag = document.getElementById('incomingPagination');
            if (incomingPag && data.totalNewInquiries !== undefined) {
                var inTotal = parseInt(data.totalNewInquiries || 0, 10);
                var inPer = parseInt(incomingPag.getAttribute('data-incoming-per-page') || '10', 10);
                var inInfo = document.getElementById('incomingPaginationInfo');
                var inTo = inTotal === 0 ? 0 : Math.min(inPer, inTotal);
                incomingPag.setAttribute('data-incoming-total', String(inTotal));
                incomingPag.setAttribute('data-incoming-current-page', '1');
                if (inInfo) {
                    inInfo.textContent = 'Showing ' + (inTotal === 0 ? 0 : 1) + ' to ' + inTo + ' of ' + inTotal + ' entries (Page 1)';
                }
                if (typeof refreshIncomingPagination === 'function') refreshIncomingPagination();
            }
            var paginationEl = document.getElementById('assignedPagination');
            if (paginationEl && data.assignedTotal !== undefined) {
                paginationEl.setAttribute('data-assigned-total', data.assignedTotal);
                paginationEl.setAttribute('data-assigned-last-page', data.assignedLastPage || 1);
                paginationEl.setAttribute('data-assigned-current-page', '1');
                var perPage = parseInt(data.assignedPerPage || paginationEl.getAttribute('data-assigned-per-page') || '10', 10);
                if (data.assignedPerPage !== undefined) paginationEl.setAttribute('data-assigned-per-page', data.assignedPerPage);
                var total = parseInt(data.assignedTotal || 0, 10);
                var lastP = parseInt(data.assignedLastPage || 1, 10);
                var to = Math.min(perPage, total);
                var infoEl = document.getElementById('assignedPaginationInfo');
                if (infoEl) infoEl.textContent = 'Showing ' + (total === 0 ? 0 : 1) + ' to ' + to + ' of ' + total + ' entries (Page 1)';
                var firstBtn = document.getElementById('assignedPaginationFirst');
                var prevBtn = document.getElementById('assignedPaginationPrev');
                var nextBtn = document.getElementById('assignedPaginationNext');
                var lastBtn = document.getElementById('assignedPaginationLast');
                if (firstBtn) firstBtn.disabled = true;
                if (prevBtn) prevBtn.disabled = true;
                if (nextBtn) nextBtn.disabled = lastP <= 1;
                if (lastBtn) lastBtn.disabled = lastP <= 1;
                if (typeof renderAssignedPageNumbers === 'function') renderAssignedPageNumbers(1, lastP);
            }

            // Re-apply current column visibility and filters so layout stays the same
            applyColumns(getVisibleColumns());
            applyAssignedColumns(getAssignedVisibleColumns());
            applyGridFilters();
            applyAssignedGridFilters();
            // Set initial order on new rows then clear sort state
            if (typeof setInitialOrder === 'function') {
                setInitialOrder('unassignedTable');
                setInitialOrder('assignedTable');
            }
            if (typeof clearInquiriesSort === 'function') {
                clearInquiriesSort('unassignedTable');
                clearInquiriesSort('assignedTable');
            }
            normalizeInquiryTbody(document.querySelector('#unassignedTable tbody'));
            normalizeInquiryTbody(document.querySelector('#assignedTable tbody'));
        }).catch(function() {
            // ignore errors, just stop spinner
        }).finally(function() {
            btn.classList.remove('is-syncing');
            if (icon) icon.classList.remove('spinning');
        });
    }

    document.querySelectorAll('.inquiries-sync-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            triggerInquiriesSync(btn);
        });
    });

    window.setInterval(function() {
        var autoBtn = document.querySelector('.inquiries-sync-btn[data-sync-type="incoming"]') || document.querySelector('.inquiries-sync-btn');
        triggerInquiriesSync(autoBtn);
    }, INQUIRIES_AUTO_SYNC_MS);

    // Incoming pagination (client-side, 10 per page) – operates on filtered rows only (big search + column filters)
    (function initIncomingPagination() {
        var paginationEl = document.getElementById('incomingPagination');
        if (!paginationEl) return;
        var infoEl = document.getElementById('incomingPaginationInfo');
        var firstBtn = document.getElementById('incomingPaginationFirst');
        var prevBtn = document.getElementById('incomingPaginationPrev');
        var nextBtn = document.getElementById('incomingPaginationNext');
        var lastBtn = document.getElementById('incomingPaginationLast');
        var pageNumbersEl = document.getElementById('incomingPageNumbers');
        var table = document.getElementById('unassignedTable');
        var tbody = table ? table.querySelector('tbody') : null;
        if (!tbody) return;

        function getPerPage() { return parseInt(paginationEl.getAttribute('data-incoming-per-page') || '10', 10); }

        function getAllRows() {
            return Array.from(tbody.querySelectorAll('tr.inquiry-row'));
        }

        function getMatchingRows() {
            var q = typeof window.getBigSearchQuery === 'function' ? window.getBigSearchQuery() : '';
            var filters = collectInquiryColumnFilters(table, '.inquiries-grid-filter');
            return getAllRows().filter(function(row) {
                var searchMatch = !q || rowMatchesBigSearch(row, q);
                if (!searchMatch) return false;
                return inquiryRowMatchesColumnFilters(row, filters);
            });
        }

        var ROW_HEIGHT_PX = 44;
        function ensureFixedHeight(visibleDataCount) {
            tbody.style.minHeight = '';
        }

        function applyPage(current) {
            var per = getPerPage();
            var matchingRows = getMatchingRows();
            var total = matchingRows.length;
            var lastPage = total === 0 ? 1 : Math.ceil(total / per);
            if (current < 1) current = 1;
            if (current > lastPage) current = lastPage;
            var from = (current - 1) * per;
            var to = current * per;
            var pageRows = matchingRows.slice(from, to);
            getAllRows().forEach(function(row) {
                row.style.display = pageRows.indexOf(row) !== -1 ? '' : 'none';
            });
            ensureFixedHeight(pageRows.length);
            paginationEl.setAttribute('data-incoming-current-page', String(current));
            if (infoEl) {
                var showFrom = total === 0 ? 0 : from + 1;
                var showTo = Math.min(to, total);
                infoEl.textContent = 'Showing ' + (total === 0 ? 0 : showFrom) + ' to ' + showTo + ' of ' + total + ' entries (Page ' + current + ')';
            }
            if (firstBtn) firstBtn.disabled = current <= 1;
            if (prevBtn) prevBtn.disabled = current <= 1;
            if (nextBtn) nextBtn.disabled = current >= lastPage;
            if (lastBtn) lastBtn.disabled = current >= lastPage;

            if (pageNumbersEl) {
                pageNumbersEl.innerHTML = '';
                if (lastPage > 1) {
                    for (var i = 1; i <= lastPage; i++) {
                        var b = document.createElement('button');
                        b.type = 'button';
                        b.className = 'inquiries-pagination-num' + (i === current ? ' inquiries-pagination-num-active' : '');
                        b.textContent = String(i);
                        b.setAttribute('data-page', String(i));
                        pageNumbersEl.appendChild(b);
                    }
                }
            }
        }

        // Initial render
        applyPage(1);

        var navEl = document.querySelector('#incomingPagination .inquiries-assigned-pagination-nav');
        if (navEl) {
            navEl.addEventListener('click', function(e) {
                var btn = e.target && e.target.closest ? e.target.closest('button.inquiries-pagination-btn') : null;
                if (!btn || btn.disabled) return;
                var id = btn.id || '';
                var cur = parseInt(paginationEl.getAttribute('data-incoming-current-page') || '1', 10);
                var matchingRows = getMatchingRows();
                var last = matchingRows.length === 0 ? 1 : Math.ceil(matchingRows.length / getPerPage());
                if (id === 'incomingPaginationFirst') applyPage(1);
                else if (id === 'incomingPaginationPrev') applyPage(cur - 1);
                else if (id === 'incomingPaginationNext') applyPage(cur + 1);
                else if (id === 'incomingPaginationLast') applyPage(last);
            });
        }
        if (pageNumbersEl) {
            pageNumbersEl.addEventListener('click', function(e) {
                var btn = e.target && e.target.closest ? e.target.closest('.inquiries-pagination-num') : null;
                if (!btn || btn.classList.contains('inquiries-pagination-num-active')) return;
                var p = parseInt(btn.getAttribute('data-page') || '1', 10);
                applyPage(p);
            });
        }

        window.refreshIncomingPagination = function() {
            applyPage(1);
        };
    })();

    // Assigned pagination: client-side filter-aware (like Incoming); "Showing X to Y of Z" uses filtered count
    (function initAssignedPagination() {
        var paginationEl = document.getElementById('assignedPagination');
        if (!paginationEl) return;
        var infoEl = document.getElementById('assignedPaginationInfo');
        var firstBtn = document.getElementById('assignedPaginationFirst');
        var prevBtn = document.getElementById('assignedPaginationPrev');
        var nextBtn = document.getElementById('assignedPaginationNext');
        var lastBtn = document.getElementById('assignedPaginationLast');
        var pageNumbersEl = document.getElementById('assignedPageNumbers');
        var assignedTable = document.getElementById('assignedTable');
        var tbody = assignedTable ? assignedTable.querySelector('tbody') : null;
        if (!tbody) return;

        function getPerPage() { return parseInt(paginationEl.getAttribute('data-assigned-per-page') || '10', 10); }
        function getCurrent() { return parseInt(paginationEl.getAttribute('data-assigned-current-page') || '1', 10); }

        function getAllRowsAssigned() {
            return Array.from(tbody.querySelectorAll('tr.inquiry-row'));
        }

        function getMatchingRowsAssigned() {
            var q = typeof window.getBigSearchQuery === 'function' ? window.getBigSearchQuery() : '';
            var filters = collectInquiryColumnFilters(assignedTable, '.inquiries-grid-filter-assigned');
            return getAllRowsAssigned().filter(function(row) {
                var searchMatch = !q || rowMatchesBigSearch(row, q);
                if (!searchMatch) return false;
                return inquiryRowMatchesColumnFilters(row, filters);
            });
        }

        function updateInfoText(current, lastPage, total) {
            if (!infoEl) return;
            var perPage = getPerPage();
            var from = total === 0 ? 0 : (current - 1) * perPage + 1;
            var to = Math.min(current * perPage, total);
            if (total === 0) infoEl.textContent = 'Showing 0 to 0 of 0 entries (Page 1)';
            else infoEl.textContent = 'Showing ' + from + ' to ' + to + ' of ' + total + ' entries (Page ' + current + ')';
        }

        window.renderAssignedPageNumbers = function(current, lastPage) {
            if (!pageNumbersEl) return;
            pageNumbersEl.innerHTML = '';
            if (lastPage <= 1) return;
            var frag = document.createDocumentFragment();
            var show = [];
            if (lastPage <= 5) {
                for (var i = 1; i <= lastPage; i++) show.push(i);
            } else {
                show.push(1, 2, 3);
                if (current > 3 && current < lastPage) show.push('...', current);
                if (lastPage > 3) show.push('...', lastPage);
            }
            show.forEach(function(p) {
                if (p === '...') {
                    var span = document.createElement('span');
                    span.className = 'inquiries-pagination-ellipsis';
                    span.textContent = '...';
                    frag.appendChild(span);
                } else {
                    var a = document.createElement('button');
                    a.type = 'button';
                    a.className = 'inquiries-pagination-num' + (p === current ? ' inquiries-pagination-num-active' : '');
                    a.textContent = String(p);
                    a.setAttribute('data-page', String(p));
                    frag.appendChild(a);
                }
            });
            pageNumbersEl.appendChild(frag);
        };

        function ensureFixedHeight(visibleDataCount) {
            tbody.style.minHeight = '';
        }

        function applyAssignedPage(current) {
            var per = getPerPage();
            var matchingRows = getMatchingRowsAssigned();
            var total = matchingRows.length;
            var lastPage = total === 0 ? 1 : Math.ceil(total / per);
            if (current < 1) current = 1;
            if (current > lastPage) current = lastPage;
            var from = (current - 1) * per;
            var to = current * per;
            var pageRows = matchingRows.slice(from, to);
            getAllRowsAssigned().forEach(function(row) {
                row.style.display = pageRows.indexOf(row) !== -1 ? '' : 'none';
            });
            ensureFixedHeight(pageRows.length);
            paginationEl.setAttribute('data-assigned-current-page', String(current));
            paginationEl.setAttribute('data-assigned-total', String(total));
            paginationEl.setAttribute('data-assigned-last-page', String(lastPage));
            updateInfoText(current, lastPage, total);
            if (firstBtn) firstBtn.disabled = current <= 1;
            if (prevBtn) prevBtn.disabled = current <= 1;
            if (nextBtn) nextBtn.disabled = current >= lastPage;
            if (lastBtn) lastBtn.disabled = current >= lastPage;
            renderAssignedPageNumbers(current, lastPage);
        }

        window.refreshAssignedPagination = function() { applyAssignedPage(1); };

        applyAssignedPage(1);

        var navEl = document.querySelector('#assignedPagination .inquiries-assigned-pagination-nav');
        if (navEl) {
            navEl.addEventListener('click', function(e) {
                var btn = e.target && e.target.closest ? e.target.closest('button.inquiries-pagination-btn') : null;
                if (!btn || btn.disabled) return;
                var id = btn.id || '';
                var cur = getCurrent();
                var matchingRows = getMatchingRowsAssigned();
                var last = matchingRows.length === 0 ? 1 : Math.ceil(matchingRows.length / getPerPage());
                if (id === 'assignedPaginationFirst') applyAssignedPage(1);
                else if (id === 'assignedPaginationPrev') applyAssignedPage(cur - 1);
                else if (id === 'assignedPaginationNext') applyAssignedPage(cur + 1);
                else if (id === 'assignedPaginationLast') applyAssignedPage(last);
            });
        }

        if (pageNumbersEl) {
            pageNumbersEl.addEventListener('click', function(e) {
                var btn = e.target && e.target.closest && e.target.closest('.inquiries-pagination-num');
                if (btn && !btn.classList.contains('inquiries-pagination-num-active')) {
                    var p = parseInt(btn.getAttribute('data-page') || '1', 10);
                    applyAssignedPage(p);
                }
            });
        }
    })();
});
</script>
@endsection


