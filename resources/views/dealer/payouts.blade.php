@extends('layouts.app')
@section('title', 'Payouts – SQL LMS Dealer Console')
@section('content')
@php
    $productNames = [
        1 => 'SQL Account', 2 => 'SQL Payroll', 3 => 'SQL Production', 4 => 'Mobile Sales',
        5 => 'SQL Ecommerce', 6 => 'SQL EBI Wellness POS', 7 => 'SQL X Suduai', 8 => 'SQL X-Store',
        9 => 'SQL Vision', 10 => 'SQL HRMS', 11 => 'Others',
    ];
@endphp
<div class="inquiries-page-wrap">
<div class="inquiries-mgmt-top-row" style="margin-bottom: 16px;">
    <section class="inquiries-mgmt-summary">
        <div class="inquiries-summary-card">
            <div class="inquiries-summary-icon"><i class="bi bi-check2-circle"></i></div>
            <div class="inquiries-summary-label">PENDING PAYOUT</div>
            <div class="inquiries-summary-value-row">
                <span class="inquiries-summary-value">{{ number_format($totalCompletedLeads ?? 0) }}</span>
            </div>
            <div class="inquiries-summary-note">Pending Referral Payout</div>
        </div>
    </section>
    <section class="inquiries-mgmt-search">
        <div class="inquiries-search-row">
            <div class="inquiries-search-wrap">
                <span class="inquiries-search-icon"><i class="bi bi-search"></i></span>
                <input type="text" class="inquiries-search-input" id="payoutSearchInput" placeholder="Search table..." autocomplete="off">
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
            <div class="inquiries-columns-dropdown">
                <button type="button" class="inquiries-btn inquiries-btn-secondary" id="completedColumnsBtn" aria-haspopup="true" aria-expanded="false">Columns</button>
                <div class="inquiries-columns-menu" id="completedColumnsMenu" hidden>
                    <div class="inquiries-columns-menu-title">Show columns</div>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="inquiryid"> INQUIRY ID</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="date"> INQUIRY DATE</label>
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="customer"> CUSTOMER NAME</label>
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
                    <label class="inquiries-columns-check"><input type="checkbox" data-col="assignby"> ASSIGN BY</label>
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
                        <th data-col="inquiryid" class="inquiries-header-cell"><span class="inquiries-header-label">INQUIRY ID</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter payouts-grid-filter" data-table="completed" data-col="inquiryid"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="date" class="inquiries-header-cell"><span class="inquiries-header-label">INQUIRY DATE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter payouts-grid-filter" data-table="completed" data-col="date"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="customer" class="inquiries-header-cell"><span class="inquiries-header-label">CUSTOMER NAME</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter payouts-grid-filter" data-table="completed" data-col="customer"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="source" class="inquiries-header-cell"><span class="inquiries-header-label">SOURCE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter payouts-grid-filter" data-table="completed" data-col="source"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="postcode" class="inquiries-header-cell"><span class="inquiries-header-label">POSTCODE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter payouts-grid-filter" data-table="completed" data-col="postcode"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="city" class="inquiries-header-cell"><span class="inquiries-header-label">CITY</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter payouts-grid-filter" data-table="completed" data-col="city"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="address" class="inquiries-header-cell"><span class="inquiries-header-label">ADDRESS</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter payouts-grid-filter" data-table="completed" data-col="address"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="contactno" class="inquiries-header-cell"><span class="inquiries-header-label">CONTACT NO</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter payouts-grid-filter" data-table="completed" data-col="contactno"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="businessnature" class="inquiries-header-cell"><span class="inquiries-header-label">BUSINESS NATURE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter payouts-grid-filter" data-table="completed" data-col="businessnature"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="users" class="inquiries-header-cell"><span class="inquiries-header-label">USERS</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter payouts-grid-filter" data-table="completed" data-col="users"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="existingsw" class="inquiries-header-cell"><span class="inquiries-header-label">EXISTING SW</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter payouts-grid-filter" data-table="completed" data-col="existingsw"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="demomode" class="inquiries-header-cell"><span class="inquiries-header-label">DEMO MODE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter payouts-grid-filter" data-table="completed" data-col="demomode"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="products" class="inquiries-header-cell"><span class="inquiries-header-label">PRODUCTS</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter payouts-grid-filter" data-table="completed" data-col="products"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="message" class="inquiries-header-cell"><span class="inquiries-header-label">MESSAGE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter payouts-grid-filter" data-table="completed" data-col="message"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="referralcode" class="inquiries-header-cell"><span class="inquiries-header-label">REFERRAL CODE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter payouts-grid-filter" data-table="completed" data-col="referralcode"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="assignby" class="inquiries-header-cell"><span class="inquiries-header-label">ASSIGN BY</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter payouts-grid-filter" data-table="completed" data-col="assignby"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="status" class="inquiries-header-cell"><span class="inquiries-header-label">STATUS</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter payouts-grid-filter" data-table="completed" data-col="status"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
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
                                : ($ccompany !== '' ? $ccompany : ($ccontact !== '' ? $ccontact : '—'));
                            $addr1 = trim((string)($r->ADDRESS1 ?? ''));
                            $addr2 = trim((string)($r->ADDRESS2 ?? ''));
                            $addr = trim($addr1 . ' ' . $addr2);
                            $afullMsg = (string)($r->DESCRIPTION ?? '');
                            $afullMsgTrim = trim($afullMsg);
                            $amsgPreview = $afullMsgTrim === '' ? '—' : (mb_strlen($afullMsgTrim) > 30 ? (mb_substr($afullMsgTrim, 0, 30) . '…') : $afullMsgTrim);
                            $rawStatus = strtoupper(trim((string)($r->CURRENTSTATUS ?? '')));
                            $statusClass = 'inquiries-status-new';
                            switch ($rawStatus) {
                                case 'PENDING':   $statusClass = 'inquiries-status-pending'; break;
                                case 'FOLLOWUP':  $statusClass = 'inquiries-status-followup'; break;
                                case 'DEMO':      $statusClass = 'inquiries-status-demo'; break;
                                case 'CONFIRMED': $statusClass = 'inquiries-status-confirmed'; break;
                                case 'COMPLETED': $statusClass = 'inquiries-status-completed'; break;
                                case 'REWARDED':  $statusClass = 'inquiries-status-rewarded'; break;
                                case 'FAILED':    $statusClass = 'inquiries-status-failed'; break;
                                default:          $statusClass = 'inquiries-status-new'; break;
                            }
                            $statusDisp = $rawStatus !== '' ? $rawStatus : 'PENDING';
                            $assignDate = $r->LASTMODIFIED ? date('d/m/Y', strtotime($r->LASTMODIFIED)) : ($r->CREATEDAT ? date('d/m/Y', strtotime($r->CREATEDAT)) : '—');
                            $searchHaystack = strtolower(($r->COMPANYNAME ?? '').' '.($r->CONTACTNAME ?? '').' '.($r->LEADID ?? ''));
                            $productIds = $r->PRODUCTID ? array_map('trim', explode(',', (string)$r->PRODUCTID)) : [];
                            $pillOrder = [1=>10,3=>11,4=>12,2=>20,10=>21,8=>30,5=>31,6=>40,9=>50,7=>60,11=>70];
                            $productIds = array_values(array_filter(array_unique(array_map('intval', $productIds)), fn($v) => $v > 0));
                            usort($productIds, function($a,$b) use ($pillOrder) { return ($pillOrder[$a] ?? 1000+$a) <=> ($pillOrder[$b] ?? 1000+$b); });
                        @endphp
                        <tr class="payouts-row inquiry-row" data-search="{{ $searchHaystack }}">
                            <td data-col="inquiryid">#SQL-{{ $r->LEADID }}</td>
                            <td data-col="date">{{ $r->CREATEDAT ? date('d/m/Y', strtotime($r->CREATEDAT)) : '—' }}</td>
                            <td data-col="customer">{{ $custDisp }}</td>
                            <td data-col="source">{{ $r->CREATEDBY_NAME ?? ($r->CREATEDBY ?? '—') }}</td>
                            <td data-col="postcode">{{ $r->POSTCODE ?? '—' }}</td>
                            <td data-col="city">{{ $r->CITY ?? '—' }}</td>
                            <td data-col="address">{{ $addr !== '' ? $addr : '—' }}</td>
                            <td data-col="contactno">{{ $r->CONTACTNO ?? '—' }}</td>
                            <td data-col="businessnature">{{ $r->BUSINESSNATURE ?? '—' }}</td>
                            <td data-col="users">{{ $r->USERCOUNT ?? '—' }}</td>
                            <td data-col="existingsw">{{ $r->EXISTINGSOFTWARE ?? '—' }}</td>
                            <td data-col="demomode">{{ $r->DEMOMODE ?? '—' }}</td>
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
                                    —
                                @endif
                            </td>
                            <td data-col="message">{{ $amsgPreview }}</td>
                            <td data-col="referralcode">{{ $r->REFERRALCODE ?? '—' }}</td>
                            <td data-col="assignby">{{ $r->CREATEDBY_NAME ?? ($r->CREATEDBY ?? '—') }}</td>
                            <td data-col="status"><span class="inquiries-status {{ $statusClass }}">{{ $statusDisp }}</span></td>
                            <td class="inquiries-col-action inquiries-action-cell">
                                <button type="button"
                                        class="inquiries-btn inquiries-btn-assign inquiries-edit-inquiry-btn inquiries-update-btn"
                                        data-lead-id="{{ $r->LEADID }}"
                                        data-customer="{{ $custDisp }}"
                                        data-status="{{ $statusDisp }}"
                                        title="Update"
                                        aria-label="Update">
                                    <i class="bi bi-pencil-square" aria-hidden="true"></i>
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
            <i class="bi bi-cash-coin inquiries-panel-icon"></i>
            <h2 class="inquiries-panel-title">Rewarded</h2>
        </div>
    </div>
    <div class="inquiries-table-wrap">
        <div class="inquiries-table-scroll">
            <table class="inquiries-table" id="rewardedTable">
                <thead>
                    <tr class="inquiries-header-row">
                        <th data-col="inquiryid" class="inquiries-header-cell"><span class="inquiries-header-label">INQUIRY ID</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter payouts-grid-filter" data-table="rewarded" data-col="inquiryid"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="date" class="inquiries-header-cell"><span class="inquiries-header-label">INQUIRY DATE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter payouts-grid-filter" data-table="rewarded" data-col="date"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="customer" class="inquiries-header-cell"><span class="inquiries-header-label">CUSTOMER NAME</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter payouts-grid-filter" data-table="rewarded" data-col="customer"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="source" class="inquiries-header-cell"><span class="inquiries-header-label">SOURCE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter payouts-grid-filter" data-table="rewarded" data-col="source"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="postcode" class="inquiries-header-cell"><span class="inquiries-header-label">POSTCODE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter payouts-grid-filter" data-table="rewarded" data-col="postcode"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="city" class="inquiries-header-cell"><span class="inquiries-header-label">CITY</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter payouts-grid-filter" data-table="rewarded" data-col="city"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="address" class="inquiries-header-cell"><span class="inquiries-header-label">ADDRESS</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter payouts-grid-filter" data-table="rewarded" data-col="address"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="contactno" class="inquiries-header-cell"><span class="inquiries-header-label">CONTACT NO</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter payouts-grid-filter" data-table="rewarded" data-col="contactno"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="businessnature" class="inquiries-header-cell"><span class="inquiries-header-label">BUSINESS NATURE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter payouts-grid-filter" data-table="rewarded" data-col="businessnature"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="users" class="inquiries-header-cell"><span class="inquiries-header-label">USERS</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter payouts-grid-filter" data-table="rewarded" data-col="users"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="existingsw" class="inquiries-header-cell"><span class="inquiries-header-label">EXISTING SW</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter payouts-grid-filter" data-table="rewarded" data-col="existingsw"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="demomode" class="inquiries-header-cell"><span class="inquiries-header-label">DEMO MODE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter payouts-grid-filter" data-table="rewarded" data-col="demomode"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="products" class="inquiries-header-cell"><span class="inquiries-header-label">PRODUCTS</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter payouts-grid-filter" data-table="rewarded" data-col="products"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="message" class="inquiries-header-cell"><span class="inquiries-header-label">MESSAGE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter payouts-grid-filter" data-table="rewarded" data-col="message"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="referralcode" class="inquiries-header-cell"><span class="inquiries-header-label">REFERRAL CODE</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter payouts-grid-filter" data-table="rewarded" data-col="referralcode"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="assignby" class="inquiries-header-cell"><span class="inquiries-header-label">ASSIGN BY</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter payouts-grid-filter" data-table="rewarded" data-col="assignby"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                        <th data-col="status" class="inquiries-header-cell"><span class="inquiries-header-label">STATUS</span><span class="inquiries-filter-wrap"><input type="text" class="inquiries-grid-filter payouts-grid-filter" data-table="rewarded" data-col="status"><i class="bi bi-search inquiries-filter-icon"></i></span></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rewarded as $r)
                        @php
                            $ccompany = trim((string)($r->COMPANYNAME ?? ''));
                            $ccontact = trim((string)($r->CONTACTNAME ?? ''));
                            $custDisp = $ccompany !== '' && $ccontact !== ''
                                ? ($ccompany . ' - ' . $ccontact)
                                : ($ccompany !== '' ? $ccompany : ($ccontact !== '' ? $ccontact : '—'));
                            $addr1 = trim((string)($r->ADDRESS1 ?? ''));
                            $addr2 = trim((string)($r->ADDRESS2 ?? ''));
                            $addr = trim($addr1 . ' ' . $addr2);
                            $afullMsg = (string)($r->DESCRIPTION ?? '');
                            $afullMsgTrim = trim($afullMsg);
                            $amsgPreview = $afullMsgTrim === '' ? '—' : (mb_strlen($afullMsgTrim) > 30 ? (mb_substr($afullMsgTrim, 0, 30) . '…') : $afullMsgTrim);
                            $rawStatus = strtoupper(trim((string)($r->CURRENTSTATUS ?? '')));
                            $statusClass = 'inquiries-status-new';
                            switch ($rawStatus) {
                                case 'PENDING':   $statusClass = 'inquiries-status-pending'; break;
                                case 'FOLLOWUP':  $statusClass = 'inquiries-status-followup'; break;
                                case 'DEMO':      $statusClass = 'inquiries-status-demo'; break;
                                case 'CONFIRMED': $statusClass = 'inquiries-status-confirmed'; break;
                                case 'COMPLETED': $statusClass = 'inquiries-status-completed'; break;
                                case 'REWARDED':  $statusClass = 'inquiries-status-rewarded'; break;
                                case 'FAILED':    $statusClass = 'inquiries-status-failed'; break;
                                default:          $statusClass = 'inquiries-status-new'; break;
                            }
                            $statusDisp = $rawStatus !== '' ? $rawStatus : 'PENDING';
                            $assignDate = $r->LASTMODIFIED ? date('d/m/Y', strtotime($r->LASTMODIFIED)) : ($r->CREATEDAT ? date('d/m/Y', strtotime($r->CREATEDAT)) : '—');
                            $searchHaystack = strtolower(($r->COMPANYNAME ?? '').' '.($r->CONTACTNAME ?? '').' '.($r->LEADID ?? ''));
                            $productIds = $r->PRODUCTID ? array_map('trim', explode(',', (string)$r->PRODUCTID)) : [];
                            $pillOrder = [1=>10,3=>11,4=>12,2=>20,10=>21,8=>30,5=>31,6=>40,9=>50,7=>60,11=>70];
                            $productIds = array_values(array_filter(array_unique(array_map('intval', $productIds)), fn($v) => $v > 0));
                            usort($productIds, function($a,$b) use ($pillOrder) { return ($pillOrder[$a] ?? 1000+$a) <=> ($pillOrder[$b] ?? 1000+$b); });
                        @endphp
                        <tr class="payouts-row inquiry-row" data-search="{{ $searchHaystack }}">
                            <td data-col="inquiryid">#SQL-{{ $r->LEADID }}</td>
                            <td data-col="date">{{ $r->CREATEDAT ? date('d/m/Y', strtotime($r->CREATEDAT)) : '—' }}</td>
                            <td data-col="customer">{{ $custDisp }}</td>
                            <td data-col="source">{{ $r->CREATEDBY_NAME ?? ($r->CREATEDBY ?? '—') }}</td>
                            <td data-col="postcode">{{ $r->POSTCODE ?? '—' }}</td>
                            <td data-col="city">{{ $r->CITY ?? '—' }}</td>
                            <td data-col="address">{{ $addr !== '' ? $addr : '—' }}</td>
                            <td data-col="contactno">{{ $r->CONTACTNO ?? '—' }}</td>
                            <td data-col="businessnature">{{ $r->BUSINESSNATURE ?? '—' }}</td>
                            <td data-col="users">{{ $r->USERCOUNT ?? '—' }}</td>
                            <td data-col="existingsw">{{ $r->EXISTINGSOFTWARE ?? '—' }}</td>
                            <td data-col="demomode">{{ $r->DEMOMODE ?? '—' }}</td>
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
                                    —
                                @endif
                            </td>
                            <td data-col="message">{{ $amsgPreview }}</td>
                            <td data-col="referralcode">{{ $r->REFERRALCODE ?? '—' }}</td>
                            <td data-col="assignby">{{ $r->CREATEDBY_NAME ?? ($r->CREATEDBY ?? '—') }}</td>
                            <td data-col="status"><span class="inquiries-status {{ $statusClass }}">{{ $statusDisp }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="18" class="inquiries-empty">No rewarded payouts.</td></tr>
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
</div>

{{-- Update Inquiry Status Modal (same as dealer inquiries) --}}
<div class="inquiry-modal-overlay" id="inquiryUpdateModal" aria-hidden="true">
    <div class="inquiry-modal" role="dialog" aria-labelledby="inquiryModalTitle">
        <div class="inquiry-modal-header">
            <h2 id="inquiryModalTitle" class="inquiry-modal-title">Update Inquiry Status</h2>
            <p class="inquiry-modal-subtitle" id="inquiryModalSubtitle">Inquiry ID: #SQL-0 • —</p>
            <button type="button" class="inquiry-modal-close" id="inquiryModalClose" aria-label="Close"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="inquiry-modal-body">
            <div class="inquiry-progression">
                <span class="inquiry-progression-label">INQUIRY PROGRESSION</span>
                <div class="inquiry-progression-steps" id="inquiryProgressionSteps">
                    <div class="inquiry-step inquiry-step--red" data-step="PENDING" data-color="red"><span>PENDING</span></div>
                    <div class="inquiry-connector"></div>
                    <div class="inquiry-step inquiry-step--red" data-step="FOLLOW UP" data-color="red"><span>FOLLOW UP</span></div>
                    <div class="inquiry-connector"></div>
                    <div class="inquiry-step inquiry-step--yellow" data-step="DEMO" data-color="yellow"><span>DEMO</span></div>
                    <div class="inquiry-connector"></div>
                    <div class="inquiry-step inquiry-step--yellow" data-step="CONFIRMED" data-color="yellow"><span>CONFIRMED</span></div>
                    <div class="inquiry-connector"></div>
                    <div class="inquiry-step inquiry-step--green" data-step="COMPLETED" data-color="green"><span>COMPLETED</span></div>
                    <div class="inquiry-connector"></div>
                    <div class="inquiry-step inquiry-step--green" data-step="REWARDED" data-color="green"><span>REWARDED</span></div>
                </div>
            </div>
            <div class="inquiry-followup">
                <div class="inquiry-followup-header">
                    <i class="bi bi-calendar3"></i>
                    <span>Update Details</span>
                </div>
                <div class="inquiry-followup-fields">
                    <label class="inquiry-field">
                        <span class="inquiry-field-label" id="inquiryDateLabel">FOLLOW-UP DATE</span>
                        <div class="inquiry-field-input-wrap">
                            <i class="bi bi-calendar3"></i>
                            <input type="date" class="inquiry-field-input" id="inquiryFollowupDate">
                        </div>
                    </label>
                    <label class="inquiry-field">
                        <span class="inquiry-field-label" id="inquiryTimeLabel">FOLLOW-UP TIME</span>
                        <div class="inquiry-field-input-wrap">
                            <i class="bi bi-clock"></i>
                            <input type="time" class="inquiry-field-input" id="inquiryFollowupTime">
                        </div>
                    </label>
                    <label class="inquiry-field inquiry-field-products" id="inquiryProductsField" style="display:none;">
                        <span class="inquiry-field-label">PRODUCTS <span class="inquiry-field-required">*</span></span>
                        <div class="inquiry-products-checklist" id="inquiryProductsChecklist">
                            @foreach($productNames as $id => $name)
                                <label class="inquiry-product-check">
                                    <input type="checkbox" class="inquiry-product-checkbox" name="inquiry_products[]" value="{{ $id }}" data-name="{{ $name }}">
                                    <span>{{ $name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </label>
                    <label class="inquiry-field">
                        <span class="inquiry-field-label">ATTACHMENT (images)</span>
                        <div class="inquiry-field-input-wrap">
                            <i class="bi bi-image"></i>
                            <input type="file" class="inquiry-field-input inquiry-field-file" id="inquiryAttachment" accept="image/*" multiple>
                        </div>
                        <div class="inquiry-attachment-preview-wrap" id="inquiryAttachmentPreview" aria-live="polite"></div>
                    </label>
                    <label class="inquiry-field">
                        <span class="inquiry-field-label">REMARK</span>
                        <textarea class="inquiry-field-input inquiry-field-textarea" id="inquiryRemark" placeholder="" rows="6"></textarea>
                    </label>
                </div>
            </div>
            <div class="inquiry-activity">
                <div class="inquiry-activity-header">
                    <h3 class="inquiry-activity-title">Activity</h3>
                </div>
                <div class="inquiry-activity-timeline" id="inquiryActivityTimeline">
                    <div class="inquiry-activity-item">
                        <span class="inquiry-activity-bullet"></span>
                        <div class="inquiry-activity-content">
                            <strong>System</strong> created inquiry <span class="inquiry-activity-link" id="inquiryActivityLink">#SQL-0</span> <span class="inquiry-activity-time">—</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="inquiry-modal-footer">
            <button type="button" class="inquiry-btn inquiry-btn-cancel" id="inquiryModalCancel">Cancel</button>
            <button type="button" class="inquiry-btn inquiry-btn-calendar" id="inquiryModalAddCalendar" style="display:none;"><i class="bi bi-calendar-plus"></i> Add Calendar</button>
            <button type="button" class="inquiry-btn inquiry-btn-update" id="inquiryModalUpdate"><i class="bi bi-check-lg"></i> Update Status</button>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var COMPLETED_STORAGE_KEY = 'dealerPayoutCompletedVisibleColumns';
    // Default + all columns: match dealer inquiries table column IDs
    var COMPLETED_DEFAULT_COLUMNS = ['inquiryid','date','customer','postcode','city','businessnature','products','assignby','status'];
    var COMPLETED_ALL_COLUMNS = ['inquiryid','date','customer','source','postcode','city','address','contactno','businessnature','users','existingsw','demomode','products','message','referralcode','assignby','status'];

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

    refreshCompletedColumnState();

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

    var COMPLETED_PER_PAGE = 10;
    var REWARDED_PER_PAGE = 10;

    function getVisibleDataRows(table) {
        if (!table) return [];
        var rows = table.querySelectorAll('tbody tr.payouts-row');
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
        table.querySelectorAll('thead .payouts-grid-filter').forEach(function(inp) {
            var col = inp.getAttribute('data-col');
            var val = (inp.value || '').toLowerCase().trim();
            if (col && val) filters[col] = val;
        });
        table.querySelectorAll('tbody tr.payouts-row').forEach(function(row) {
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
        table.querySelectorAll('thead .payouts-grid-filter').forEach(function(inp) {
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
                t.querySelectorAll('thead .payouts-grid-filter').forEach(function(inp) { inp.value = ''; });
            });
            applyAllTables();
        });
    }

    var completedClearFilters = document.getElementById('completedClearFilters');
    if (completedClearFilters) {
        completedClearFilters.addEventListener('click', function() {
            var table = document.getElementById('completedTable');
            if (table) table.querySelectorAll('thead .payouts-grid-filter').forEach(function(inp) { inp.value = ''; });
            applyTableFilter('completedTable');
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
        });
    });
});
</script>
@endpush
@push('scripts')
<script>
(function() {
    var modal = document.getElementById('inquiryUpdateModal');
    var subtitle = document.getElementById('inquiryModalSubtitle');
    var closeBtn = document.getElementById('inquiryModalClose');
    var cancelBtn = document.getElementById('inquiryModalCancel');
    var updateBtn = document.getElementById('inquiryModalUpdate');

    var activityLink = document.getElementById('inquiryActivityLink');
    var progressionSteps = document.getElementById('inquiryProgressionSteps');
    var activityTimeline = document.getElementById('inquiryActivityTimeline');

    var statusOrder = ['PENDING', 'FOLLOW UP', 'DEMO', 'CONFIRMED', 'COMPLETED', 'REWARDED'];
    var remarkPlaceholders = {
        'PENDING': 'Add specific notes about this inquiry...',
        'FOLLOW UP': 'Add specific notes about this follow-up...',
        'DEMO': 'Add specific notes about this demo...',
        'CONFIRMED': 'Add specific notes about this confirmation...',
        'COMPLETED': 'Add specific notes about this completion...',
        'REWARDED': 'Add specific notes about this reward...'
    };
    var dateTimeLabels = {
        'PENDING': { date: 'FOLLOW-UP DATE', time: 'FOLLOW-UP TIME' },
        'FOLLOW UP': { date: 'FOLLOW-UP DATE', time: 'FOLLOW-UP TIME' },
        'DEMO': { date: 'DEMO DATE', time: 'DEMO TIME' },
        'CONFIRMED': { date: 'CONFIRMED DATE', time: 'CONFIRMED TIME' },
        'COMPLETED': { date: 'COMPLETED DATE', time: 'COMPLETED TIME' },
        'REWARDED': { date: 'REWARD DATE', time: 'REWARD TIME' }
    };
    var statusMap = {
        'PENDING': 'PENDING', 'FOLLOW UP': 'FOLLOW UP', 'FOLLOWUP': 'FOLLOW UP',
        'DEMO': 'DEMO', 'CONFIRMED': 'CONFIRMED', 'CASE CONFIRMED': 'CONFIRMED',
        'COMPLETED': 'COMPLETED', 'CASE COMPLETED': 'COMPLETED',
        'REWARD': 'REWARDED', 'REWARDED': 'REWARDED', 'REWARD DISTRIBUTED': 'REWARDED'
    };

    var currentLeadId = '';
    var currentCustomer = '';
    var currentStatusIdx = 0;
    var selectedStatusIdx = 0;
    var viewMode = false;
    var cachedActivities = [];

    function setProgression(currentStatus) {
        var normalized = statusMap[currentStatus] || 'PENDING';
        var idx = statusOrder.indexOf(normalized);
        if (idx < 0) idx = 0;
        currentStatusIdx = idx;
        selectedStatusIdx = Math.min(idx + 1, statusOrder.length - 1);
        if (progressionSteps) {
            var steps = progressionSteps.querySelectorAll('.inquiry-step');
            var showDone = function(i) { return i <= idx; };
            steps.forEach(function(step, i) {
                step.classList.remove('inquiry-step--done', 'inquiry-step--active', 'inquiry-step--selected', 'inquiry-step--clickable', 'inquiry-step--no-click', 'inquiry-step--viewable');
                step.innerHTML = '<span>' + step.dataset.step + '</span>';
                if (showDone(i)) {
                    step.classList.add('inquiry-step--done', 'inquiry-step--viewable');
                    step.innerHTML = '<i class="bi bi-check"></i><span>' + step.dataset.step + '</span>';
                } else if (i === selectedStatusIdx) {
                    step.classList.add('inquiry-step--active', 'inquiry-step--selected');
                } else if (i === 0) {
                    step.classList.add('inquiry-step--no-click');
                } else {
                    step.classList.add('inquiry-step--clickable');
                }
            });
        }
        var remarkEl = document.getElementById('inquiryRemark');
        if (remarkEl) remarkEl.placeholder = remarkPlaceholders[statusOrder[selectedStatusIdx]] || remarkPlaceholders['PENDING'];
        setDateTimeLabels(statusOrder[selectedStatusIdx]);
        toggleAddCalendarButton();
        toggleProductChecklist();
        toggleUpdateButton();
    }

    function setRemarkPlaceholder(status) {
        var remarkEl = document.getElementById('inquiryRemark');
        if (remarkEl) remarkEl.placeholder = remarkPlaceholders[status] || remarkPlaceholders['PENDING'];
    }

    function setDateTimeLabels(status) {
        var labels = dateTimeLabels[status] || dateTimeLabels['FOLLOW UP'];
        var dateLabel = document.getElementById('inquiryDateLabel');
        var timeLabel = document.getElementById('inquiryTimeLabel');
        if (dateLabel) dateLabel.textContent = labels.date;
        if (timeLabel) timeLabel.textContent = labels.time;
    }

    function toggleAddCalendarButton() {
        var btn = document.getElementById('inquiryModalAddCalendar');
        if (btn) btn.style.display = statusOrder[selectedStatusIdx] === 'DEMO' ? '' : 'none';
    }

    function toggleProductChecklist() {
        var field = document.getElementById('inquiryProductsField');
        var isCompleted = statusOrder[selectedStatusIdx] === 'COMPLETED' && !viewMode;
        if (field) {
            field.style.display = isCompleted ? '' : 'none';
            if (!isCompleted) {
                var boxes = field.querySelectorAll('.inquiry-product-checkbox');
                boxes.forEach(function(b) { b.checked = false; });
            }
        }
    }

    function toggleUpdateButton() {
        var isRewarded = currentStatusIdx === statusOrder.length - 1;
        var disable = isRewarded || viewMode;
        updateBtn.disabled = disable;
        updateBtn.classList.toggle('inquiry-btn-update--disabled', disable);
    }

    function statusMatches(orderName, activityStatus) {
        var a = (orderName || '').toUpperCase().replace(/\s+/g, '');
        var b = (activityStatus || '').toUpperCase().replace(/\s+/g, '').replace('REWARDDISTRIBUTED', 'REWARDED');
        if (a === 'REWARDED') return b === 'REWARDED' || b === 'REWARDDISTRIBUTED';
        return a === b;
    }

    function findActivityForStatus(statusOrderName) {
        for (var j = 0; j < cachedActivities.length; j++) {
            if (cachedActivities[j].type === 'activity' && statusMatches(statusOrderName, cachedActivities[j].status)) {
                return cachedActivities[j];
            }
        }
        return null;
    }

    function populateFormFromActivity(activity) {
        var dateEl = document.getElementById('inquiryFollowupDate');
        var timeEl = document.getElementById('inquiryFollowupTime');
        var remarkEl = document.getElementById('inquiryRemark');
        if (!activity || !activity.created_at) {
            if (dateEl) dateEl.value = '';
            if (timeEl) timeEl.value = '';
            if (remarkEl) remarkEl.value = '';
            return;
        }
        var d = new Date(activity.created_at);
        if (dateEl) dateEl.value = isNaN(d.getTime()) ? '' : d.toISOString().slice(0, 10);
        if (timeEl) timeEl.value = isNaN(d.getTime()) ? '' : String(d.getHours()).padStart(2, '0') + ':' + String(d.getMinutes()).padStart(2, '0');
        if (remarkEl) remarkEl.value = activity.description || '';
    }

    function setFieldsReadOnly(readOnly) {
        var dateEl = document.getElementById('inquiryFollowupDate');
        var timeEl = document.getElementById('inquiryFollowupTime');
        var remarkEl = document.getElementById('inquiryRemark');
        var fileEl = document.getElementById('inquiryAttachment');
        var productBoxes = document.querySelectorAll('.inquiry-product-checkbox');
        if (dateEl) dateEl.readOnly = readOnly;
        if (timeEl) timeEl.readOnly = readOnly;
        if (remarkEl) remarkEl.readOnly = readOnly;
        if (fileEl) fileEl.disabled = readOnly;
        productBoxes.forEach(function(b) { b.disabled = readOnly; });
    }

    function formatActivityTime(isoStr, now) {
        if (!isoStr) return '—';

        var d = null;

        if (typeof isoStr === 'string') {
            var m = isoStr.match(/^(\d{4})-(\d{2})-(\d{2})[ T](\d{2}):(\d{2})(?::(\d{2}))?/);
            if (m) {
                var year = parseInt(m[1], 10);
                var month = parseInt(m[2], 10) - 1;
                var day = parseInt(m[3], 10);
                var hour = parseInt(m[4], 10);
                var min = parseInt(m[5], 10);
                var sec = m[6] ? parseInt(m[6], 10) : 0;
                d = new Date(year, month, day, hour, min, sec);
            }
        }

        if (!d) {
            d = new Date(isoStr);
        }

        if (isNaN(d.getTime())) return isoStr;
        if (!now || !(now instanceof Date)) now = new Date();
        var diffSec = Math.floor((now - d) / 1000);
        if (diffSec < 0) return 'just now';
        if (diffSec < 60) return 'just now';
        if (diffSec < 3600) return Math.floor(diffSec / 60) + ' min ago';
        if (diffSec < 86400) return Math.floor(diffSec / 3600) + ' hr ago';
        if (diffSec < 172800) return '1 day ago';
        if (diffSec < 604800) {
            var days = Math.floor(diffSec / 86400);
            return days + ' day' + (days === 1 ? '' : 's') + ' ago';
        }
        if (diffSec < 1209600) return '1 week ago';
        if (diffSec < 2592000) return Math.floor(diffSec / 604800) + ' weeks ago';
        if (diffSec < 5184000) return '1 month ago';
        if (diffSec < 31536000) return Math.floor(diffSec / 2592000) + ' months ago';
        if (diffSec < 63072000) return '1 year ago';
        return Math.floor(diffSec / 31536000) + ' years ago';
    }

    function renderActivity(activities) {
        if (!activityTimeline) return;
        activityTimeline.innerHTML = '';
        var now = new Date();
        if (!activities || activities.length === 0) {
            activityTimeline.innerHTML = '<div class="inquiry-activity-item"><span class="inquiry-activity-bullet"></span><div class="inquiry-activity-content">No activity yet.</div></div>';
            return;
        }
        activities.forEach(function(a) {
            var item = document.createElement('div');
            item.className = 'inquiry-activity-item';
            var user = (a.user || 'System').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            var timeStr = formatActivityTime(a.created_at, now);
            var html = '<span class="inquiry-activity-bullet"></span><div class="inquiry-activity-content">';
            if (a.type === 'created') {
                html += '<strong>' + user + '</strong> created inquiry <span class="inquiry-activity-link">#SQL-' + currentLeadId + '</span>';
            } else {
                var subj = (a.subject || '').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                var desc = (a.description || '').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                var status = (a.status || '').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                html += '<strong>' + user + '</strong> ' + (subj ? subj + ' ' : '');
                if (status) html += 'changed status to <strong class="inquiry-activity-status">' + status + '</strong> ';
                if (desc) html += '<span class="inquiry-activity-desc">' + desc + '</span> ';
            }
            html += '<span class="inquiry-activity-time">' + timeStr + '</span>';
            if (a.attachment_urls && a.attachment_urls.length > 0) {
                html += '<div class="inquiry-activity-attachments">';
                a.attachment_urls.forEach(function(url) {
                    var safe = (url || '').replace(/"/g, '&quot;');
                    html += '<a href="' + safe + '" target="_blank" rel="noopener" class="inquiry-activity-attachment-link"><img src="' + safe + '" alt="Attachment" class="inquiry-activity-attachment-img"></a>';
                });
                html += '</div>';
            }
            html += '</div>';
            item.innerHTML = html;
            activityTimeline.appendChild(item);
        });
    }

    function loadActivity(leadId) {
        var url = '{{ route("dealer.inquiries.activity", ["leadId" => "__ID__"]) }}'.replace('__ID__', leadId);
        fetch(url, { headers: { 'Accept': 'application/json' } })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                cachedActivities = data.activities || [];
                renderActivity(cachedActivities);
                var details = data.last_reward_details;
                if (details && currentStatusIdx === statusOrder.length - 1) {
                    var dateEl = document.getElementById('inquiryFollowupDate');
                    var timeEl = document.getElementById('inquiryFollowupTime');
                    var remarkEl = document.getElementById('inquiryRemark');
                    if (dateEl && details.date) dateEl.value = details.date;
                    if (timeEl && details.time) timeEl.value = details.time;
                    if (remarkEl && details.description) remarkEl.value = details.description;
                    setFieldsReadOnly(true);
                }
            })
            .catch(function() {
                renderActivity([]);
            });
    }

    var attachmentFiles = [];
    var attachmentPreviewUrls = [];

    function clearAttachmentPreviews() {
        attachmentPreviewUrls.forEach(function(url) { try { URL.revokeObjectURL(url); } catch (e) {} });
        attachmentPreviewUrls = [];
        attachmentFiles = [];
        var previewEl = document.getElementById('inquiryAttachmentPreview');
        if (previewEl) previewEl.innerHTML = '';
        var inputEl = document.getElementById('inquiryAttachment');
        if (inputEl) inputEl.value = '';
    }

    function renderAttachmentPreviews() {
        var previewEl = document.getElementById('inquiryAttachmentPreview');
        if (!previewEl) return;
        attachmentPreviewUrls.forEach(function(url) { try { URL.revokeObjectURL(url); } catch (e) {} });
        attachmentPreviewUrls = [];
        previewEl.innerHTML = '';
        attachmentFiles.forEach(function(file, index) {
            if (!file.type || file.type.indexOf('image/') !== 0) return;
            var url = URL.createObjectURL(file);
            attachmentPreviewUrls.push(url);
            var item = document.createElement('div');
            item.className = 'inquiry-attachment-preview-item';
            var img = document.createElement('img');
            img.src = url;
            img.alt = file.name || 'Image';
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'inquiry-attachment-preview-remove';
            btn.setAttribute('aria-label', 'Remove image');
            btn.innerHTML = '&times;';
            btn.addEventListener('click', function() {
                attachmentFiles.splice(index, 1);
                renderAttachmentPreviews();
            });
            item.appendChild(img);
            item.appendChild(btn);
            previewEl.appendChild(item);
        });
    }

    var attachmentInput = document.getElementById('inquiryAttachment');
    if (attachmentInput) {
        attachmentInput.addEventListener('change', function() {
            var files = this.files;
            if (!files || !files.length) return;
            for (var i = 0; i < files.length; i++) {
                if (files[i].type && files[i].type.indexOf('image/') === 0) {
                    attachmentFiles.push(files[i]);
                }
            }
            renderAttachmentPreviews();
            this.value = '';
        });
    }

    function openModal(leadId, customer, status) {
        currentLeadId = leadId;
        currentCustomer = customer || '—';
        viewMode = false;
        subtitle.textContent = 'Inquiry ID: #SQL-' + leadId + ' • ' + currentCustomer;
        if (activityLink) activityLink.textContent = '#SQL-' + leadId;
        setProgression(status || 'PENDING');
        var remarkEl = document.getElementById('inquiryRemark');
        var dateEl = document.getElementById('inquiryFollowupDate');
        var timeEl = document.getElementById('inquiryFollowupTime');
        if (remarkEl) remarkEl.value = '';
        if (dateEl) dateEl.value = '';
        if (timeEl) timeEl.value = '';
        var productBoxes = document.querySelectorAll('.inquiry-product-checkbox');
        if (productBoxes.length) productBoxes.forEach(function(b) { b.checked = false; });
        clearAttachmentPreviews();
        setFieldsReadOnly(false);
        loadActivity(leadId);
        modal.setAttribute('aria-hidden', 'false');
        modal.classList.add('inquiry-modal-open');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        clearAttachmentPreviews();
        modal.setAttribute('aria-hidden', 'true');
        modal.classList.remove('inquiry-modal-open');
        document.body.style.overflow = '';
    }

    if (progressionSteps) {
        progressionSteps.addEventListener('click', function(e) {
            var step = e.target.closest('.inquiry-step');
            if (!step) return;
            if (step.classList.contains('inquiry-step--no-click')) return;
            var stepIdx = statusOrder.indexOf(step.dataset.step);
            if (stepIdx < 0) return;
            var isDoneStep = stepIdx <= currentStatusIdx;
            var isFutureStep = step.classList.contains('inquiry-step--clickable');
            if (!isDoneStep && !isFutureStep) return;
            selectedStatusIdx = stepIdx;
            if (isDoneStep) {
                viewMode = true;
                var act = findActivityForStatus(statusOrder[stepIdx]);
                populateFormFromActivity(act);
                setFieldsReadOnly(true);
                setDateTimeLabels(statusOrder[stepIdx]);
            } else {
                viewMode = false;
                var remarkEl = document.getElementById('inquiryRemark');
                var dateEl = document.getElementById('inquiryFollowupDate');
                var timeEl = document.getElementById('inquiryFollowupTime');
                if (remarkEl) remarkEl.value = '';
                if (dateEl) dateEl.value = '';
                if (timeEl) timeEl.value = '';
                setFieldsReadOnly(false);
                setRemarkPlaceholder(statusOrder[stepIdx]);
                setDateTimeLabels(statusOrder[stepIdx]);
            }
            progressionSteps.querySelectorAll('.inquiry-step').forEach(function(s, i) {
                s.classList.remove('inquiry-step--active', 'inquiry-step--selected', 'inquiry-step--clickable', 'inquiry-step--no-click', 'inquiry-step--viewable');
                var stepName = s.dataset.step;
                var sIsDone = i <= currentStatusIdx;
                if (sIsDone) {
                    s.classList.add('inquiry-step--done', 'inquiry-step--viewable');
                    if (i === selectedStatusIdx && viewMode) s.classList.add('inquiry-step--selected');
                    s.innerHTML = '<i class="bi bi-check"></i><span>' + stepName + '</span>';
                } else if (i === selectedStatusIdx) {
                    s.classList.add('inquiry-step--active', 'inquiry-step--selected');
                    s.innerHTML = '<span>' + stepName + '</span>';
                } else if (i === 0) {
                    s.classList.add('inquiry-step--no-click');
                    s.innerHTML = '<span>' + stepName + '</span>';
                } else {
                    s.classList.add('inquiry-step--clickable');
                    s.innerHTML = '<span>' + stepName + '</span>';
                }
            });
            toggleAddCalendarButton();
            toggleProductChecklist();
            toggleUpdateButton();
        });
    }

    document.addEventListener('click', function(e) {
        var updateBtnEl = e.target.closest('.inquiries-update-btn');
        if (updateBtnEl) {
            e.preventDefault();
            openModal(updateBtnEl.dataset.leadId, updateBtnEl.dataset.customer, updateBtnEl.dataset.status);
            return;
        }
    });

    [closeBtn, cancelBtn].forEach(function(btn) {
        if (btn) btn.addEventListener('click', closeModal);
    });

    modal.addEventListener('click', function(e) {
        if (e.target === modal) closeModal();
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal.classList.contains('inquiry-modal-open')) {
            closeModal();
        }
    });

    var addCalendarBtn = document.getElementById('inquiryModalAddCalendar');
    if (addCalendarBtn) {
        addCalendarBtn.addEventListener('click', function() {
            var dateEl = document.getElementById('inquiryFollowupDate');
            var timeEl = document.getElementById('inquiryFollowupTime');
            var remarkEl = document.getElementById('inquiryRemark');
            var dateStr = dateEl ? dateEl.value : '';
            var timeStr = timeEl ? timeEl.value : '';
            var remark = remarkEl ? remarkEl.value.trim() : '';
            var title = 'Demo: #SQL-' + currentLeadId + ' - ' + currentCustomer;
            var details = 'Inquiry #SQL-' + currentLeadId + '\nCustomer: ' + currentCustomer + (remark ? '\n\n' + remark : '');
            var startDate, endDate;
            if (dateStr) {
                var d = new Date(dateStr + (timeStr ? 'T' + timeStr : 'T09:00:00'));
                if (isNaN(d.getTime())) d = new Date();
                startDate = d;
                endDate = new Date(d.getTime() + 60 * 60 * 1000);
            } else {
                startDate = new Date();
                endDate = new Date(startDate.getTime() + 60 * 60 * 1000);
            }
            var fmt = function(d) {
                var y = d.getFullYear(), m = String(d.getMonth() + 1).padStart(2, '0'), day = String(d.getDate()).padStart(2, '0');
                var h = String(d.getHours()).padStart(2, '0'), min = String(d.getMinutes()).padStart(2, '0'), s = String(d.getSeconds()).padStart(2, '0');
                return y + m + day + 'T' + h + min + s;
            };
            var url = 'https://calendar.google.com/calendar/render?action=TEMPLATE&text=' + encodeURIComponent(title) + '&dates=' + fmt(startDate) + '/' + fmt(endDate) + '&details=' + encodeURIComponent(details);
            window.open(url, '_blank', 'noopener,noreferrer');
        });
    }

    updateBtn.addEventListener('click', function() {
        if (this.disabled || selectedStatusIdx <= currentStatusIdx) return;
        var toStatus = statusOrder[selectedStatusIdx];
        if (toStatus === 'DEMO' && currentStatusIdx < 1) {
            alert('You must complete the follow-up (status: FOLLOW UP) before updating to DEMO. Please update the status to FOLLOW UP first.');
            return;
        }
        if (toStatus === 'REWARDED' && currentStatusIdx < 4) {
            alert('You must complete the inquiry (status: COMPLETED) before updating to REWARDED. Please update the status to COMPLETED first.');
            return;
        }
        if (toStatus === 'COMPLETED') {
            var checked = document.querySelectorAll('.inquiry-product-checkbox:checked');
            if (!checked.length) {
                alert('Please select at least one product for COMPLETED status.');
                return;
            }
        }
        var leadId = currentLeadId;
        var remarkEl = document.getElementById('inquiryRemark');
        var remark = remarkEl ? remarkEl.value.trim() : '';
        var products = [];
        if (toStatus === 'COMPLETED') {
            document.querySelectorAll('.inquiry-product-checkbox:checked').forEach(function(cb) {
                products.push({ id: cb.value, name: cb.dataset.name });
            });
        }
        var updateUrl = '{{ route("dealer.inquiries.update-status") }}';
        var csrfToken = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '';
        updateBtn.disabled = true;
        var body;
        var headers = {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest'
        };
        if (attachmentFiles.length > 0) {
            var formData = new FormData();
            formData.append('lead_id', leadId);
            formData.append('status', toStatus);
            formData.append('remark', remark);
            formData.append('products', JSON.stringify(products));
            attachmentFiles.forEach(function(file) {
                formData.append('attachments[]', file);
            });
            body = formData;
        } else {
            headers['Content-Type'] = 'application/json';
            body = JSON.stringify({ lead_id: leadId, status: toStatus, remark: remark, products: products });
        }
        fetch(updateUrl, {
            method: 'POST',
            headers: headers,
            body: body
        })
        .then(function(r) { return r.json().then(function(d) { return { ok: r.ok, data: d }; }); })
        .then(function(res) {
            updateBtn.disabled = false;
            if (res.ok && res.data.success) {
                closeModal();
                window.location.reload();
            } else {
                alert(res.data.message || 'Update failed');
            }
        })
        .catch(function() {
            updateBtn.disabled = false;
            alert('Update failed. Please try again.');
        });
    });
})();
</script>
@endpush
