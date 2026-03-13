@extends('layouts.app')
@section('title', 'Inquiries Management – Admin')
@section('content')
@php $assignUndo = session('assign_undo'); @endphp
<div class="inquiries-page-wrap">
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

<section class="inquiries-mgmt-search">
    <div class="inquiries-search-wrap">
        <span class="inquiries-search-icon"><i class="bi bi-search"></i></span>
        <input type="text" class="inquiries-search-input" placeholder="Search by customer, company, or Inquiry ID..." id="inquirySearchInput">
        <button type="button" class="inquiries-search-btn" id="inquirySearchBtn">Search</button>
    </div>
</section>

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
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="date"> DATE</label>
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
        <table class="inquiries-table" id="unassignedTable">
            <thead>
                <tr class="inquiries-header-row">
                    <th data-col="inquiryid" class="inquiries-header-cell"><span class="inquiries-header-label">INQUIRY ID</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter" data-col="inquiryid"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                    <th data-col="date" class="inquiries-header-cell"><span class="inquiries-header-label">DATE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter" data-col="date"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                    <th data-col="customername" class="inquiries-header-cell"><span class="inquiries-header-label">CUSTOMER NAME</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter" data-col="customername"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                    <th data-col="source" class="inquiries-header-cell"><span class="inquiries-header-label">SOURCE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter" data-col="source"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                    <th data-col="postcode" class="inquiries-header-cell"><span class="inquiries-header-label">POSTCODE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter" data-col="postcode"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                    <th data-col="city" class="inquiries-header-cell"><span class="inquiries-header-label">CITY</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter" data-col="city"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                    <th data-col="address" class="inquiries-header-cell"><span class="inquiries-header-label">ADDRESS</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter" data-col="address"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                    <th data-col="contactno" class="inquiries-header-cell"><span class="inquiries-header-label">CONTACT NO</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter" data-col="contactno"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                    <th data-col="businessnature" class="inquiries-header-cell"><span class="inquiries-header-label">BUSINESS NATURE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter" data-col="businessnature"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                    <th data-col="users" class="inquiries-header-cell"><span class="inquiries-header-label">USERS</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter" data-col="users"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
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
                            usort($ids, function($a, $b) use ($pillOrder) {
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
                            case 'PENDING':    $statusClass = 'inquiries-status-pending'; break;
                            case 'FOLLOWUP':   $statusClass = 'inquiries-status-followup'; break;
                            case 'DEMO':       $statusClass = 'inquiries-status-demo'; break;
                            case 'CONFIRMED':  $statusClass = 'inquiries-status-confirmed'; break;
                            case 'COMPLETED':  $statusClass = 'inquiries-status-completed'; break;
                            case 'REWARDED':   $statusClass = 'inquiries-status-rewarded'; break;
                            default:           $statusClass = 'inquiries-status-new'; break;
                        }
                    @endphp
                    <td data-col="status"><span class="inquiries-status {{ $statusClass }}">{{ $rawStatus !== '' ? $rawStatus : 'PENDING' }}</span></td>
                    <td class="inquiries-col-action">
                        @php
                            $acompany = trim((string)($r->COMPANYNAME ?? ''));
                            $acontact = trim((string)($r->CONTACTNAME ?? ''));
                            $assignLeadLabel = $acompany !== '' && $acontact !== '' ? ($acompany . ' - ' . $acontact) : ($acompany !== '' ? $acompany : ($acontact !== '' ? $acontact : ('#SQL-' . ($r->LEADID ?? ''))));
                        @endphp
                        <button type="button" class="inquiries-btn inquiries-btn-assign"
                            data-assign-lead="{{ $r->LEADID }}"
                            data-assign-name="{{ e($assignLeadLabel) }}">
                            Assign
                        </button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="15" class="inquiries-empty">No unassigned inquiries.</td></tr>
                @endforelse
            </tbody>
        </table>
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
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="date"> DATE</label>
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
        <table class="inquiries-table" id="assignedTable">
            <thead>
                <tr class="inquiries-header-row">
                    <th data-col="inquiryid" class="inquiries-header-cell"><span class="inquiries-header-label">INQUIRY ID</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter-assigned" data-col="inquiryid"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                    <th data-col="date" class="inquiries-header-cell"><span class="inquiries-header-label">DATE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter-assigned" data-col="date"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                    <th data-col="customername" class="inquiries-header-cell"><span class="inquiries-header-label">CUSTOMER NAME</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter-assigned" data-col="customername"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                    <th data-col="source" class="inquiries-header-cell"><span class="inquiries-header-label">SOURCE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter-assigned" data-col="source"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                    <th data-col="postcode" class="inquiries-header-cell"><span class="inquiries-header-label">POSTCODE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter-assigned" data-col="postcode"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                    <th data-col="city" class="inquiries-header-cell"><span class="inquiries-header-label">CITY</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter-assigned" data-col="city"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                    <th data-col="address" class="inquiries-header-cell"><span class="inquiries-header-label">ADDRESS</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter-assigned" data-col="address"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                    <th data-col="contactno" class="inquiries-header-cell"><span class="inquiries-header-label">CONTACT NO</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter-assigned" data-col="contactno"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                    <th data-col="businessnature" class="inquiries-header-cell"><span class="inquiries-header-label">BUSINESS NATURE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter-assigned" data-col="businessnature"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                    <th data-col="users" class="inquiries-header-cell"><span class="inquiries-header-label">USERS</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter-assigned" data-col="users"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                    <th data-col="existingsw" class="inquiries-header-cell"><span class="inquiries-header-label">EXISTING SW</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter-assigned" data-col="existingsw"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                    <th data-col="demomode" class="inquiries-header-cell"><span class="inquiries-header-label">DEMO MODE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter-assigned" data-col="demomode"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                    <th data-col="products" class="inquiries-header-cell"><span class="inquiries-header-label">PRODUCTS</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter-assigned" data-col="products"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                    <th data-col="message" class="inquiries-header-cell"><span class="inquiries-header-label">MESSAGE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter-assigned" data-col="message"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                    <th data-col="referralcode" class="inquiries-header-cell"><span class="inquiries-header-label">REFERRAL CODE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter-assigned" data-col="referralcode"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                    <th data-col="assignedby" class="inquiries-header-cell"><span class="inquiries-header-label">ASSIGNED BY</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter-assigned" data-col="assignedby"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                    <th data-col="assignedto" class="inquiries-header-cell"><span class="inquiries-header-label">ASSIGNED TO</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter-assigned" data-col="assignedto"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
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
                            usort($ids, function($a, $b) use ($pillOrder) {
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
                    <td data-col="assignedby">{{ $r->CREATEDBY_NAME ?? ($r->CREATEDBY ?? '—') }}</td>
                    <td data-col="assignedto">{{ $r->ASSIGNED_TO_NAME ?? ($r->ASSIGNED_TO ?? '—') }}</td>
                    <td data-col="assigndate">{{ $r->LASTMODIFIED ? date('d/m/Y', strtotime($r->LASTMODIFIED)) : ($r->CREATEDAT ? date('d/m/Y', strtotime($r->CREATEDAT)) : '—') }}</td>
                    @php
                        $arawStatus = strtoupper(trim((string)($r->CURRENTSTATUS ?? '')));
                        $astatusClass = 'inquiries-status-new';
                        switch ($arawStatus) {
                            case 'PENDING':    $astatusClass = 'inquiries-status-pending'; break;
                            case 'FOLLOWUP':   $astatusClass = 'inquiries-status-followup'; break;
                            case 'DEMO':       $astatusClass = 'inquiries-status-demo'; break;
                            case 'CONFIRMED':  $astatusClass = 'inquiries-status-confirmed'; break;
                            case 'COMPLETED':  $astatusClass = 'inquiries-status-completed'; break;
                            case 'REWARDED':   $astatusClass = 'inquiries-status-rewarded'; break;
                            case 'FAILED':     $astatusClass = 'inquiries-status-failed'; break;
                            default:           $astatusClass = 'inquiries-status-new'; break;
                        }
                    @endphp
                    @php $canMarkFailed = !in_array($arawStatus, ['COMPLETED', 'REWARDED', 'FAILED'], true); @endphp
                    <td data-col="status"><span class="inquiries-status {{ $astatusClass }}">{{ $arawStatus !== '' ? $arawStatus : 'PENDING' }}</span></td>
                    <td class="inquiries-col-action inquiries-action-cell {{ $canMarkFailed ? '' : 'inquiries-action-cell-single' }}">
                        <button type="button" class="inquiries-btn inquiries-btn-small inquiries-view-status-btn" data-lead-id="{{ $r->LEADID }}" title="View Status">View Status</button>
                        @if($canMarkFailed)
                        <button type="button" class="inquiries-btn inquiries-btn-small inquiries-btn-secondary inquiries-mark-failed-btn" data-lead-id="{{ $r->LEADID }}" title="Mark As Failed">Mark As Failed</button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="20" class="inquiries-empty">No assigned inquiries.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="inquiries-assigned-pagination" id="assignedPagination" data-assigned-total="{{ $assignedTotal ?? 0 }}" data-assigned-last-page="{{ $assignedLastPage ?? 1 }}" data-assigned-current-page="1" data-assigned-per-page="{{ $assignedPerPage ?? 10 }}" data-assigned-page-url="{{ route('admin.inquiries.assigned-page') }}" @if(($assignedTotal ?? 0) <= 10) style="display:none;" @endif>
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

        var btn = toast.querySelector('.assign-undo-btn');
        if (btn) {
            btn.addEventListener('click', function () {
                clearTimeout(timer);
                form.submit();
            });
        }
    })();

    var STORAGE_KEY = 'inquiryVisibleColumns';
    var DEFAULT_COLUMNS = ['inquiryid', 'date', 'customername', 'postcode', 'city', 'businessnature', 'products', 'message'];
    var ASSIGNED_STORAGE_KEY = 'assignedVisibleColumns';
    var ASSIGNED_DEFAULT_COLUMNS = ['inquiryid', 'date', 'customername', 'postcode', 'city', 'assignedby', 'assignedto', 'assigndate', 'status'];

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
    }

    function applyAssignedColumns(visible) {
        var table = document.getElementById('assignedTable');
        if (!table) return;
        var allCols = ['inquiryid','date','customername','source','city','postcode','address','contactno','businessnature','users','existingsw','demomode','products','message','referralcode','assignedby','assignedto','assigndate','status'];
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
        });
    }

    var aAll = document.getElementById('assignedColumnsAll');
    if (aAll) {
        aAll.addEventListener('click', function() {
            var allCols = ['inquiryid','date','customername','source','city','postcode','address','contactno','businessnature','users','existingsw','demomode','products','message','referralcode','assignedby','assignedto','assigndate','status'];
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

    function applyGridFilters() {
        var table = document.getElementById('unassignedTable');
        var searchInput = document.getElementById('inquirySearchInput');
        var q = (searchInput && searchInput.value) ? (searchInput.value || '').toLowerCase().trim() : '';
        var filters = {};
        if (table) {
            table.querySelectorAll('.inquiries-grid-filter').forEach(function(inp) {
                var col = inp.getAttribute('data-col');
                var val = (inp.value || '').toLowerCase().trim();
                if (col && val) filters[col] = val;
            });
            table.querySelectorAll('tbody .inquiry-row').forEach(function(row) {
                var searchMatch = !q || (row.getAttribute('data-search') || '').indexOf(q) !== -1;
                var colMatch = true;
                for (var col in filters) {
                    var cell = row.querySelector('td[data-col="' + col + '"]');
                    var cellText = (cell && cell.textContent) ? cell.textContent.toLowerCase().trim() : '';
                    if (cellText.indexOf(filters[col]) === -1) { colMatch = false; break; }
                }
                row.style.display = (searchMatch && colMatch) ? '' : 'none';
            });
        }
    }

    function applyAssignedGridFilters() {
        var table = document.getElementById('assignedTable');
        var searchInput = document.getElementById('inquirySearchInput');
        var q = (searchInput && searchInput.value) ? (searchInput.value || '').toLowerCase().trim() : '';
        var filters = {};
        if (!table) return;
        table.querySelectorAll('.inquiries-grid-filter-assigned').forEach(function(inp) {
            var col = inp.getAttribute('data-col');
            var val = (inp.value || '').toLowerCase().trim();
            if (col && val) filters[col] = val;
        });
        table.querySelectorAll('tbody .inquiry-row').forEach(function(row) {
            var searchMatch = !q || (row.getAttribute('data-search') || '').indexOf(q) !== -1;
            var colMatch = true;
            for (var col in filters) {
                var cell = row.querySelector('td[data-col="' + col + '"]');
                var cellText = (cell && cell.textContent) ? cell.textContent.toLowerCase().trim() : '';
                if (cellText.indexOf(filters[col]) === -1) { colMatch = false; break; }
            }
            row.style.display = (searchMatch && colMatch) ? '' : 'none';
        });
    }

    var table = document.getElementById('unassignedTable');
    if (table) {
        table.querySelectorAll('.inquiries-grid-filter').forEach(function(inp) {
            inp.addEventListener('input', applyGridFilters);
            inp.addEventListener('keyup', applyGridFilters);
        });
    }
    var clearFiltersBtn = document.getElementById('inquiryClearFilters');
    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', function() {
            var t = document.getElementById('unassignedTable');
            if (t) t.querySelectorAll('.inquiries-grid-filter').forEach(function(inp) { inp.value = ''; });
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
    }
    var assignedClearBtn = document.getElementById('assignedClearFilters');
    if (assignedClearBtn) {
        assignedClearBtn.addEventListener('click', function() {
            var t = document.getElementById('assignedTable');
            if (t) t.querySelectorAll('.inquiries-grid-filter-assigned').forEach(function(inp) { inp.value = ''; });
            applyAssignedGridFilters();
            if (typeof clearInquiriesSort === 'function') clearInquiriesSort('assignedTable');
        });
    }

    var input = document.getElementById('inquirySearchInput');
    var btn = document.getElementById('inquirySearchBtn');
    if (input) {
        function filterRows() {
            applyGridFilters();
            applyAssignedGridFilters();
        }
        if (btn) btn.addEventListener('click', filterRows);
        input.addEventListener('keydown', function(e) { if (e.key === 'Enter') filterRows(); });
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
        var emptyRow = tbody.querySelector('tr:not(.inquiry-row)');
        rows.sort(function(a, b) {
            var ia = parseInt(a.getAttribute('data-initial-index') || '0', 10);
            var ib = parseInt(b.getAttribute('data-initial-index') || '0', 10);
            return ia - ib;
        });
        rows.forEach(function(r) { tbody.appendChild(r); });
        if (emptyRow) tbody.appendChild(emptyRow);
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

    // Sync buttons (fetch latest inquiries without full reload)
    document.querySelectorAll('.inquiries-sync-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            if (btn.classList.contains('is-syncing')) return;
            btn.classList.add('is-syncing');
            var icon = btn.querySelector('.inquiries-sync-icon');
            if (icon) {
                icon.classList.add('spinning');
            }
            var url = btn.getAttribute('data-sync-url');
            if (!url) url = window.location.href;
            var syncType = btn.getAttribute('data-sync-type') || 'incoming';
            fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                cache: 'no-store'
            }).then(function(res) {
                return res.ok ? res.json() : Promise.reject();
            }).then(function(data) {
                var ua = document.querySelector('#unassignedTable tbody');
                if (ua && data.unassigned !== undefined) {
                    ua.innerHTML = data.unassigned;
                }
                var as = document.querySelector('#assignedTable tbody');
                if (as && data.assigned !== undefined) {
                    as.innerHTML = data.assigned;
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
                var paginationEl = document.getElementById('assignedPagination');
                if (paginationEl && data.assignedTotal !== undefined) {
                    paginationEl.setAttribute('data-assigned-total', data.assignedTotal);
                    paginationEl.setAttribute('data-assigned-last-page', data.assignedLastPage || 1);
                    paginationEl.setAttribute('data-assigned-current-page', '1');
                    var perPage = parseInt(paginationEl.getAttribute('data-assigned-per-page') || '10', 10);
                    var total = parseInt(data.assignedTotal || 0, 10);
                    var lastP = parseInt(data.assignedLastPage || 1, 10);
                    var to = Math.min(perPage, total);
                    var infoEl = document.getElementById('assignedPaginationInfo');
                    if (infoEl) infoEl.textContent = 'Showing 1 to ' + to + ' of ' + total + ' entries (Page 1)';
                    paginationEl.style.display = (data.assignedTotal || 0) > 10 ? '' : 'none';
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
            }).catch(function() {
                // ignore errors, just stop spinner
            }).finally(function() {
                btn.classList.remove('is-syncing');
                if (icon) icon.classList.remove('spinning');
            });
        });
    });

    // Assigned pagination: First / Previous / page numbers / Next / Last; "Showing X to Y of Z entries (Page N)"
    (function initAssignedPagination() {
        var paginationEl = document.getElementById('assignedPagination');
        if (!paginationEl) return;
        var infoEl = document.getElementById('assignedPaginationInfo');
        var firstBtn = document.getElementById('assignedPaginationFirst');
        var prevBtn = document.getElementById('assignedPaginationPrev');
        var nextBtn = document.getElementById('assignedPaginationNext');
        var lastBtn = document.getElementById('assignedPaginationLast');
        var pageNumbersEl = document.getElementById('assignedPageNumbers');
        var tbody = document.querySelector('#assignedTable tbody');

        function getPerPage() { return parseInt(paginationEl.getAttribute('data-assigned-per-page') || '10', 10); }
        function getTotal() { return parseInt(paginationEl.getAttribute('data-assigned-total') || '0', 10); }
        function getLastPage() { return parseInt(paginationEl.getAttribute('data-assigned-last-page') || '1', 10); }
        function getCurrent() { return parseInt(paginationEl.getAttribute('data-assigned-current-page') || '1', 10); }

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

        function updatePaginationState(current, lastPage, total) {
            total = total !== undefined ? total : getTotal();
            paginationEl.setAttribute('data-assigned-current-page', String(current));
            updateInfoText(current, lastPage, total);
            if (firstBtn) firstBtn.disabled = current <= 1;
            if (prevBtn) prevBtn.disabled = current <= 1;
            if (nextBtn) nextBtn.disabled = current >= lastPage;
            if (lastBtn) lastBtn.disabled = current >= lastPage;
            renderAssignedPageNumbers(current, lastPage);
        }

        var lastPage = getLastPage();
        var current = getCurrent();
        var total = getTotal();
        updateInfoText(current, lastPage, total);
        if (firstBtn) firstBtn.disabled = current <= 1;
        if (prevBtn) prevBtn.disabled = current <= 1;
        if (nextBtn) nextBtn.disabled = current >= lastPage;
        if (lastBtn) lastBtn.disabled = current >= lastPage;
        renderAssignedPageNumbers(current, lastPage);

        function loadAssignedPage(page) {
            var url = paginationEl.getAttribute('data-assigned-page-url');
            if (!url || !tbody) return;
            var u = url + (url.indexOf('?') !== -1 ? '&' : '?') + 'page=' + encodeURIComponent(page);
            fetch(u, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, cache: 'no-store' })
                .then(function(res) { return res.ok ? res.json() : Promise.reject(); })
                .then(function(data) {
                    if (data.html !== undefined) tbody.innerHTML = data.html;
                    var last = parseInt(data.lastPage || 1, 10);
                    var cur = parseInt(data.currentPage || 1, 10);
                    var total = parseInt(data.assignedTotal || 0, 10);
                    paginationEl.setAttribute('data-assigned-last-page', String(last));
                    paginationEl.setAttribute('data-assigned-total', String(total));
                    updatePaginationState(cur, last, total);
                    applyAssignedColumns(getAssignedVisibleColumns());
                    applyAssignedGridFilters();
                    if (typeof setInitialOrder === 'function') setInitialOrder('assignedTable');
                    if (typeof clearInquiriesSort === 'function') clearInquiriesSort('assignedTable');
                })
                .catch(function() {});
        }

        function goToPage(deltaOrPage) {
            var cur = getCurrent();
            var last = getLastPage();
            var page;
            if (deltaOrPage === 'next') page = cur + 1;
            else if (deltaOrPage === 'prev') page = cur - 1;
            else if (typeof deltaOrPage === 'number' && deltaOrPage >= 1 && deltaOrPage <= last) page = deltaOrPage;
            else return;
            if (page >= 1 && page <= last) loadAssignedPage(page);
        }

        var navEl = document.querySelector('#assignedPagination .inquiries-assigned-pagination-nav');
        if (navEl) {
            navEl.addEventListener('click', function(e) {
                var btn = e.target && e.target.closest ? e.target.closest('button.inquiries-pagination-btn') : null;
                if (!btn || btn.disabled) return;
                var id = btn.id || '';
                if (id === 'assignedPaginationFirst') loadAssignedPage(1);
                else if (id === 'assignedPaginationPrev') goToPage('prev');
                else if (id === 'assignedPaginationNext') goToPage('next');
                else if (id === 'assignedPaginationLast') loadAssignedPage(getLastPage());
            });
        }

        if (pageNumbersEl) {
            pageNumbersEl.addEventListener('click', function(e) {
                var btn = e.target && e.target.closest && e.target.closest('.inquiries-pagination-num');
                if (btn && !btn.classList.contains('inquiries-pagination-num-active')) {
                    var p = parseInt(btn.getAttribute('data-page') || '1', 10);
                    loadAssignedPage(p);
                }
            });
        }
    })();
});
</script>
@endsection
