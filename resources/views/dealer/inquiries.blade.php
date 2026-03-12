@extends('layouts.app')
@section('title', 'Active Inquiries – SQL LMS Dealer Console')
@section('content')
@php
    $productNames = [
        1 => 'SQL Account', 2 => 'SQL Payroll', 3 => 'SQL Production', 4 => 'Mobile Sales',
        5 => 'SQL Ecommerce', 6 => 'SQL EBI Wellness POS', 7 => 'SQL X Suduai', 8 => 'SQL X-Store',
        9 => 'SQL Vision', 10 => 'SQL HRMS', 11 => 'Others',
    ];
@endphp
<div class="dashboard-content inquiries-all-page">
    <section class="dealer-panel dealer-inquiries-panel inquiries-all-table-panel">
        <div class="dealer-panel-header">
            <h2 class="dealer-panel-title">All Inquiries</h2>
            <button type="button" class="inquiries-btn inquiries-btn-columns">
                <span>Columns</span>
            </button>
        </div>
        <div class="dealer-table-wrapper inquiries-table-wrapper">
            <div class="inquiries-table-scroll">
                <table class="inquiries-table" id="dealerInquiriesTable">
                    <thead>
                        <tr class="inquiries-table-header-row">
                        <th class="inquiries-header-cell" data-col="inquiryid">
                            <span class="inquiries-header-label">INQUIRY ID</span>
                            <span class="inquiries-filter-wrap">
                                <input type="text" class="inquiries-grid-filter" data-col="inquiryid">
                                <i class="bi bi-search inquiries-filter-icon"></i>
                            </span>
                        </th>
                        <th class="inquiries-header-cell" data-col="date">
                            <span class="inquiries-header-label">DATE</span>
                            <span class="inquiries-filter-wrap">
                                <input type="text" class="inquiries-grid-filter" data-col="date">
                                <i class="bi bi-search inquiries-filter-icon"></i>
                            </span>
                        </th>
                        <th class="inquiries-header-cell" data-col="customer">
                            <span class="inquiries-header-label">CUSTOMER</span>
                            <span class="inquiries-filter-wrap">
                                <input type="text" class="inquiries-grid-filter" data-col="customer">
                                <i class="bi bi-search inquiries-filter-icon"></i>
                            </span>
                        </th>
                        <th class="inquiries-header-cell" data-col="source">
                            <span class="inquiries-header-label">SOURCE</span>
                            <span class="inquiries-filter-wrap">
                                <input type="text" class="inquiries-grid-filter" data-col="source">
                                <i class="bi bi-search inquiries-filter-icon"></i>
                            </span>
                        </th>
                        <th class="inquiries-header-cell" data-col="postcode">
                            <span class="inquiries-header-label">POSTCODE</span>
                            <span class="inquiries-filter-wrap">
                                <input type="text" class="inquiries-grid-filter" data-col="postcode">
                                <i class="bi bi-search inquiries-filter-icon"></i>
                            </span>
                        </th>
                        <th class="inquiries-header-cell" data-col="contactno">
                            <span class="inquiries-header-label">CONTACT NO</span>
                            <span class="inquiries-filter-wrap">
                                <input type="text" class="inquiries-grid-filter" data-col="contactno">
                                <i class="bi bi-search inquiries-filter-icon"></i>
                            </span>
                        </th>
                        <th class="inquiries-header-cell" data-col="businessnature">
                            <span class="inquiries-header-label">BUSINESS NATURE</span>
                            <span class="inquiries-filter-wrap">
                                <input type="text" class="inquiries-grid-filter" data-col="businessnature">
                                <i class="bi bi-search inquiries-filter-icon"></i>
                            </span>
                        </th>
                        <th class="inquiries-header-cell" data-col="users">
                            <span class="inquiries-header-label">USERS</span>
                            <span class="inquiries-filter-wrap">
                                <input type="text" class="inquiries-grid-filter" data-col="users">
                                <i class="bi bi-search inquiries-filter-icon"></i>
                            </span>
                        </th>
                        <th class="inquiries-header-cell" data-col="existingsw">
                            <span class="inquiries-header-label">EXISTING SW</span>
                            <span class="inquiries-filter-wrap">
                                <input type="text" class="inquiries-grid-filter" data-col="existingsw">
                                <i class="bi bi-search inquiries-filter-icon"></i>
                            </span>
                        </th>
                        <th class="inquiries-header-cell" data-col="demomode">
                            <span class="inquiries-header-label">DEMO MODE</span>
                            <span class="inquiries-filter-wrap">
                                <input type="text" class="inquiries-grid-filter" data-col="demomode">
                                <i class="bi bi-search inquiries-filter-icon"></i>
                            </span>
                        </th>
                        <th class="inquiries-header-cell" data-col="products">
                            <span class="inquiries-header-label">PRODUCTS</span>
                            <span class="inquiries-filter-wrap">
                                <input type="text" class="inquiries-grid-filter" data-col="products">
                                <i class="bi bi-search inquiries-filter-icon"></i>
                            </span>
                        </th>
                        <th class="inquiries-header-cell" data-col="message">
                            <span class="inquiries-header-label">MESSAGE</span>
                            <span class="inquiries-filter-wrap">
                                <input type="text" class="inquiries-grid-filter" data-col="message">
                                <i class="bi bi-search inquiries-filter-icon"></i>
                            </span>
                        </th>
                        <th class="inquiries-header-cell" data-col="referralcode">
                            <span class="inquiries-header-label">REFERRAL CODE</span>
                            <span class="inquiries-filter-wrap">
                                <input type="text" class="inquiries-grid-filter" data-col="referralcode">
                                <i class="bi bi-search inquiries-filter-icon"></i>
                            </span>
                        </th>
                        <th class="inquiries-header-cell" data-col="assignby">
                            <span class="inquiries-header-label">ASSIGN BY</span>
                            <span class="inquiries-filter-wrap">
                                <input type="text" class="inquiries-grid-filter" data-col="assignby">
                                <i class="bi bi-search inquiries-filter-icon"></i>
                            </span>
                        </th>
                        <th class="inquiries-header-cell" data-col="status">
                            <span class="inquiries-header-label">STATUS</span>
                            <span class="inquiries-filter-wrap">
                                <input type="text" class="inquiries-grid-filter" data-col="status">
                                <i class="bi bi-search inquiries-filter-icon"></i>
                            </span>
                        </th>
                        <th class="inquiries-col-action inquiries-header-cell">
                            <span class="inquiries-header-label">ACTION</span>
                            <button type="button" class="inquiries-filter-clear" id="dealerInquiryClearFilters">Clear filters</button>
                        </th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($leads as $r)
                        @php
                            $products = [];
                            if (isset($r->PRODUCTID) && $r->PRODUCTID !== '' && $r->PRODUCTID !== null) {
                                $ids = is_numeric($r->PRODUCTID) ? [(int)$r->PRODUCTID] : array_map('intval', array_filter(explode(',', (string)$r->PRODUCTID)));
                                foreach ($ids as $id) {
                                    if ($id > 0) $products[] = $productNames[$id] ?? ('Product ' . $id);
                                }
                            }
                            if (empty($products)) $products = ['Others'];
                        @endphp
                        <tr class="inquiry-row" data-search="{{ strtolower(trim(($r->COMPANYNAME ?? '').' '.($r->CONTACTNAME ?? '').' '.($r->LEADID ?? ''))) }}">
                            <td data-col="inquiryid"><strong>#SQL-{{ $r->LEADID }}</strong></td>
                            <td data-col="date">{{ $r->CREATEDAT ? date('d/m/Y', strtotime($r->CREATEDAT)) : '—' }}</td>
                            <td data-col="customer">{{ trim(($r->COMPANYNAME ?? '') . ' ' . ($r->CONTACTNAME ?? '')) ?: '—' }}</td>
                            <td data-col="source">{{ $r->CREATEDAT ? date('d/m/Y', strtotime($r->CREATEDAT)) : '—' }}</td>
                            <td data-col="postcode">{{ $r->POSTCODE ?? '—' }}</td>
                            <td data-col="contactno">{{ $r->CONTACTNO ?? '—' }}</td>
                            <td data-col="businessnature">{{ $r->BUSINESSNATURE ?? '—' }}</td>
                            <td data-col="users">{{ $r->USERCOUNT ?? '—' }}</td>
                            <td data-col="existingsw">{{ $r->EXISTINGSOFTWARE ?? '—' }}</td>
                            <td data-col="demomode">{{ $r->DEMOMODE ?? '—' }}</td>
                            <td data-col="products">
                                <div class="inquiries-product-pills">
                                    @foreach($products as $p)
                                        <span class="inquiries-product-pill">{{ $p }}</span>
                                    @endforeach
                                </div>
                            </td>
                            <td data-col="message">{{ Str::limit($r->DESCRIPTION ?? '—', 20) }}</td>
                            <td data-col="referralcode">{{ $r->REFERRALCODE ?? '—' }}</td>
                            <td data-col="assignby">{{ $r->ASSIGNED_BY_EMAIL ?? '—' }}</td>
                            <td data-col="status">@php $s = trim($r->ACT_STATUS ?? ''); echo $s === '' ? '—' : (in_array(strtoupper($s), ['FOLLOWUP', 'FOLLOW UP'], true) ? 'Follow Up' : $s); @endphp</td>
                            <td class="inquiries-col-action">
                                @php $actStatus = strtoupper($r->ACT_STATUS ?? 'PENDING'); $isFailed = $actStatus === 'FAILED'; @endphp
                                @if ($isFailed)
                                    <button type="button" class="inquiries-view-btn" data-lead-id="{{ $r->LEADID }}" data-customer="{{ trim(($r->COMPANYNAME ?? '') . ' ' . ($r->CONTACTNAME ?? '')) ?: '—' }}">View</button>
                                @else
                                    <button type="button" class="inquiries-update-btn" data-lead-id="{{ $r->LEADID }}" data-customer="{{ trim(($r->COMPANYNAME ?? '') . ' ' . ($r->CONTACTNAME ?? '')) ?: '—' }}" data-status="{{ $actStatus }}">Update</button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr class="inquiries-empty-row">
                            <td colspan="16" class="inquiries-empty-cell">
                                <div class="dealer-table-empty">No inquiries assigned yet.</div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        {{-- No pagination for dealer inquiries; show full table like admin Incoming Inquiries --}}
    </section>
</div>

{{-- View Failed Message Modal --}}
<div class="inquiry-modal-overlay" id="inquiryViewModal" aria-hidden="true">
    <div class="inquiry-modal inquiry-modal--view" role="dialog" aria-labelledby="inquiryViewModalTitle">
        <div class="inquiry-modal-header">
            <h2 id="inquiryViewModalTitle" class="inquiry-modal-title">Failed Inquiry</h2>
            <p class="inquiry-modal-subtitle" id="inquiryViewModalSubtitle">Inquiry ID: #SQL-0 • —</p>
            <button type="button" class="inquiry-modal-close" id="inquiryViewModalClose" aria-label="Close"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="inquiry-modal-body">
            <div class="inquiry-view-message">
                <span class="inquiry-field-label">MESSAGE</span>
                <div class="inquiry-view-message-content" id="inquiryViewMessageContent">—</div>
            </div>
        </div>
        <div class="inquiry-modal-footer">
            <button type="button" class="inquiry-btn inquiry-btn-cancel" id="inquiryViewModalCloseBtn">Close</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var table = document.getElementById('dealerInquiriesTable');
    if (!table) return;

    function applyDealerGridFilters() {
        var filters = {};
        table.querySelectorAll('.inquiries-grid-filter').forEach(function(inp) {
            var col = inp.getAttribute('data-col');
            var val = (inp.value || '').toLowerCase().trim();
            if (col && val) filters[col] = val;
        });

        table.querySelectorAll('tbody .inquiry-row').forEach(function(row) {
            var colMatch = true;
            for (var col in filters) {
                var cell = row.querySelector('td[data-col="' + col + '"]');
                var cellText = (cell && cell.textContent) ? cell.textContent.toLowerCase().trim() : '';
                if (cellText.indexOf(filters[col]) === -1) { colMatch = false; break; }
            }
            row.style.display = colMatch ? '' : 'none';
        });
    }

    table.querySelectorAll('.inquiries-grid-filter').forEach(function(inp) {
        inp.addEventListener('input', applyDealerGridFilters);
        inp.addEventListener('keyup', applyDealerGridFilters);
    });

    var clearBtn = document.getElementById('dealerInquiryClearFilters');
    if (clearBtn) {
        clearBtn.addEventListener('click', function() {
            table.querySelectorAll('.inquiries-grid-filter').forEach(function(inp) { inp.value = ''; });
            applyDealerGridFilters();
        });
    }

    applyDealerGridFilters();
});
</script>
@endpush

{{-- Update Inquiry Status Modal --}}
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
                        <span class="inquiry-field-label">ATTACHMENT</span>
                        <div class="inquiry-field-input-wrap">
                            <i class="bi bi-paperclip"></i>
                            <input type="file" class="inquiry-field-input inquiry-field-file" id="inquiryAttachment" multiple>
                        </div>
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

    function formatActivityTime(isoStr) {
        if (!isoStr) return '—';
        var d = new Date(isoStr);
        if (isNaN(d.getTime())) return isoStr;
        var now = new Date();
        var diff = Math.floor((now - d) / 1000);
        if (diff < 60) return 'just now';
        if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
        if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
        if (diff < 604800) return Math.floor(diff / 86400) + 'd ago';
        return d.toLocaleDateString() + ' ' + d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }

    function renderActivity(activities) {
        if (!activityTimeline) return;
        activityTimeline.innerHTML = '';
        if (!activities || activities.length === 0) {
            activityTimeline.innerHTML = '<div class="inquiry-activity-item"><span class="inquiry-activity-bullet"></span><div class="inquiry-activity-content">No activity yet.</div></div>';
            return;
        }
        activities.forEach(function(a) {
            var item = document.createElement('div');
            item.className = 'inquiry-activity-item';
            var user = (a.user || 'System').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            var timeStr = formatActivityTime(a.created_at);
            var html = '<span class="inquiry-activity-bullet"></span><div class="inquiry-activity-content">';
            if (a.type === 'created') {
                html += '<strong>' + user + '</strong> created inquiry <span class="inquiry-activity-link">#SQL-' + currentLeadId + '</span>';
            } else {
                var subj = (a.subject || '').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                var desc = (a.description || '').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                var status = (a.status || '').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                html += '<strong>' + user + '</strong> ' + (subj ? subj + ' ' : '');
                if (status) html += 'changed status to <span class="inquiry-activity-status">' + status + '</span> ';
                if (desc) html += '<span class="inquiry-activity-desc">' + desc + '</span> ';
            }
            html += '<span class="inquiry-activity-time">' + timeStr + '</span></div>';
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
        setFieldsReadOnly(false);
        loadActivity(leadId);
        modal.setAttribute('aria-hidden', 'false');
        modal.classList.add('inquiry-modal-open');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
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

    document.querySelectorAll('.inquiries-update-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            openModal(btn.dataset.leadId, btn.dataset.customer, btn.dataset.status);
        });
    });

    var viewModal = document.getElementById('inquiryViewModal');
    var viewSubtitle = document.getElementById('inquiryViewModalSubtitle');
    var viewContent = document.getElementById('inquiryViewMessageContent');
    var viewCloseBtn = document.getElementById('inquiryViewModalClose');
    var viewCloseBtnFooter = document.getElementById('inquiryViewModalCloseBtn');
    document.querySelectorAll('.inquiries-view-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var leadId = btn.dataset.leadId;
            var customer = btn.dataset.customer || '—';
            if (viewSubtitle) viewSubtitle.textContent = 'Inquiry ID: #SQL-' + leadId + ' • ' + customer;
            if (viewContent) viewContent.textContent = 'Loading...';
            viewModal.setAttribute('aria-hidden', 'false');
            viewModal.classList.add('inquiry-modal-open');
            document.body.style.overflow = 'hidden';
            fetch('{{ url("/dealer/inquiries") }}/' + leadId + '/failed-description', {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (viewContent) viewContent.textContent = (data.description || '').trim() || '—';
            })
            .catch(function() {
                if (viewContent) viewContent.textContent = '—';
            });
        });
    });
    function closeViewModal() {
        viewModal.setAttribute('aria-hidden', 'true');
        viewModal.classList.remove('inquiry-modal-open');
        document.body.style.overflow = '';
    }
    if (viewCloseBtn) viewCloseBtn.addEventListener('click', closeViewModal);
    if (viewCloseBtnFooter) viewCloseBtnFooter.addEventListener('click', closeViewModal);
    viewModal.addEventListener('click', function(e) {
        if (e.target === viewModal) closeViewModal();
    });

    [closeBtn, cancelBtn].forEach(function(btn) {
        if (btn) btn.addEventListener('click', closeModal);
    });

    modal.addEventListener('click', function(e) {
        if (e.target === modal) closeModal();
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            if (viewModal && viewModal.classList.contains('inquiry-modal-open')) closeViewModal();
            else if (modal.classList.contains('inquiry-modal-open')) closeModal();
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
        fetch(updateUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ lead_id: leadId, status: toStatus, remark: remark, products: products })
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
@endsection
