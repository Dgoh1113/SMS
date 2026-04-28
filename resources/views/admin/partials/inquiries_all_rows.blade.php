@forelse($allRows ?? [] as $r)
<tr class="inquiry-row" data-search="{{ strtolower(($r->COMPANYNAME ?? '') . ' ' . ($r->CONTACTNAME ?? '') . ' ' . ($r->LEADID ?? '')) }}">
    <td data-col="inquiryid">#SQL-{{ $r->LEADID }}</td>
    <td data-col="date">{{ $r->CREATEDAT ? date('d/m/Y', strtotime($r->CREATEDAT)) : '-' }}</td>
    @php
        $company = trim((string) ($r->COMPANYNAME ?? ''));
        $contact = trim((string) ($r->CONTACTNAME ?? ''));
        $customerDisplay = $company !== '' && $contact !== '' ? ($company . ' - ' . $contact) : ($company !== '' ? $company : ($contact !== '' ? $contact : '-'));
        $addr1 = trim((string) ($r->ADDRESS1 ?? ''));
        $addr2 = trim((string) ($r->ADDRESS2 ?? ''));
        $addressDisplay = trim($addr1 . ' ' . $addr2);
        $assignedTo = trim((string) ($r->ASSIGNED_TO ?? ''));
        $rawStatus = strtoupper(trim((string) ($r->CURRENTSTATUS ?? '')));
        $statusDisplay = $rawStatus === 'OPEN' ? 'CREATED' : ($rawStatus !== '' ? $rawStatus : 'PENDING');
        $statusClass = 'inquiries-status-new';
        switch ($statusDisplay) {
            case 'CREATED': $statusClass = 'inquiries-status-created'; break;
            case 'PENDING':
            case 'ONGOING': $statusClass = 'inquiries-status-pending'; break;
            case 'FOLLOWUP':
            case 'FOLLOW UP': $statusClass = 'inquiries-status-followup'; break;
            case 'DEMO': $statusClass = 'inquiries-status-demo'; break;
            case 'CONFIRMED':
            case 'CASE CONFIRMED': $statusClass = 'inquiries-status-confirmed'; break;
            case 'COMPLETED':
            case 'CASE COMPLETED': $statusClass = 'inquiries-status-completed'; break;
            case 'REWARDED':
            case 'REWARD DISTRIBUTED':
            case 'PAID': $statusClass = 'inquiries-status-rewarded'; break;
            case 'FAILED': $statusClass = 'inquiries-status-failed'; break;
            default: $statusClass = 'inquiries-status-new'; break;
        }
        $fullMsg = trim((string) ($r->DESCRIPTION ?? ''));
        $msgPreview = $fullMsg === '' ? '-' : (mb_strlen($fullMsg) > 30 ? (mb_substr($fullMsg, 0, 30) . '...') : $fullMsg);
        $isLongMsg = $fullMsg !== '' && mb_strlen($fullMsg) > 30;
        $ids = $r->PRODUCTID ? array_map('trim', explode(',', (string) $r->PRODUCTID)) : [];
        $dealtRaw = $r->DEALTPRODUCT ?? null;
        $dealtProductIds = $dealtRaw ? array_map('trim', preg_split('/[\s,\(\)]+/', (string) $dealtRaw)) : [];
        $pillOrder = [
            1 => 10, 3 => 11, 4 => 12,
            2 => 20, 10 => 21,
            8 => 30, 5 => 31,
            6 => 40,
            9 => 50,
            7 => 60,
            11 => 70,
        ];
        $ids = array_values(array_filter(array_unique(array_map('intval', $ids)), fn ($v) => $v > 0));
        $dealtProductIds = array_values(array_filter(array_unique(array_map('intval', $dealtProductIds)), fn ($v) => $v > 0));
        usort($ids, function ($a, $b) use ($pillOrder) {
            $oa = $pillOrder[$a] ?? (1000 + $a);
            $ob = $pillOrder[$b] ?? (1000 + $b);
            return $oa <=> $ob;
        });
        usort($dealtProductIds, function ($a, $b) use ($pillOrder) {
            $oa = $pillOrder[$a] ?? (1000 + $a);
            $ob = $pillOrder[$b] ?? (1000 + $b);
            return $oa <=> $ob;
        });
        $assignedAttachUrls = !empty($r->ASSIGNED_ATTACHMENT_URLS) && is_array($r->ASSIGNED_ATTACHMENT_URLS) ? $r->ASSIGNED_ATTACHMENT_URLS : [];
        $isAssignedRow = $assignedTo !== '';
        $canAssignRow = !$isAssignedRow && in_array($rawStatus, ['OPEN', 'CREATED'], true);
        $assignLeadLabel = $customerDisplay !== '-' ? $customerDisplay : ('#SQL-' . ($r->LEADID ?? ''));
    @endphp
    <td data-col="customername">{{ $customerDisplay }}</td>
    <td data-col="email">{{ $r->EMAIL ?? '-' }}</td>
    <td data-col="source">{{ $r->CREATEDBY_NAME ?? ($r->CREATEDBY ?? '-') }}</td>
    <td data-col="postcode">{{ $r->POSTCODE ?? '-' }}</td>
    <td data-col="city">{{ $r->CITY ?? '-' }}</td>
    <td data-col="address">{{ $addressDisplay !== '' ? $addressDisplay : '-' }}</td>
    <td data-col="contactno">{{ $r->CONTACTNO ?? '-' }}</td>
    <td data-col="businessnature">{{ $r->BUSINESSNATURE ?? '-' }}</td>
    <td data-col="users">{{ $r->USERCOUNT ?? '-' }}</td>
    <td data-col="existingsw">{{ $r->EXISTINGSOFTWARE ?? '-' }}</td>
    <td data-col="demomode">{{ $r->DEMOMODE ?? '-' }}</td>
    <td data-col="products">
        @if(!empty($ids))
            <div class="inquiries-pill-group">
                @foreach($ids as $id)
                    @if(isset($productLabels[(int) $id]))
                        <span class="inquiries-pill inquiries-pill-p{{ (int) $id }}">{{ $productLabels[(int) $id] }}</span>
                    @endif
                @endforeach
            </div>
        @else
            -
        @endif
    </td>
    <td data-col="dealtproducts">
        @if(!empty($dealtProductIds))
            <div class="inquiries-pill-group">
                @foreach($dealtProductIds as $id)
                    @if(isset($productLabels[(int) $id]))
                        <span class="inquiries-pill inquiries-pill-p{{ (int) $id }}">{{ $productLabels[(int) $id] }}</span>
                    @endif
                @endforeach
            </div>
        @else
            -
        @endif
    </td>
    <td data-col="message" class="inquiries-msg-cell {{ $isLongMsg ? 'inquiries-msg-clickable' : '' }}"
        @if($isLongMsg) data-full-message="{{ e($fullMsg) }}" @endif>
        {{ $msgPreview }}
    </td>
    <td data-col="referralcode">{{ $r->REFERRALCODE ?? '-' }}</td>
    <td data-col="assignedby">{{ $r->ASSIGNEDBY_NAME ?? ($r->ASSIGNEDBY ?? '-') }}</td>
    <td data-col="assignedto" title="{{ $r->ASSIGNED_TO_NAME ?? ($r->ASSIGNED_TO ?? '-') }}">{{ $r->ASSIGNED_TO_NAME ?? ($r->ASSIGNED_TO ?? '-') }}</td>
    <td data-col="completiondate">{{ !empty($r->COMPLETED_AT) ? date('d/m/Y', strtotime($r->COMPLETED_AT)) : '-' }}</td>
    <td data-col="payoutsdate">{{ !empty($r->REWARDED_AT) ? date('d/m/Y', strtotime($r->REWARDED_AT)) : '-' }}</td>
    <td data-col="attachment">
        @php
            $assignedAttachUrls = !empty($assignedAttachUrls) && is_array($assignedAttachUrls)
                ? array_values(array_filter($assignedAttachUrls, function ($url) {
                    $normalized = is_string($url) ? trim($url) : '';
                    return $normalized !== '' && !in_array(strtolower($normalized), ['-', 'null', 'undefined', '#'], true);
                }))
                : [];
        @endphp
        @if(!empty($assignedAttachUrls))
            <a href="{{ $assignedAttachUrls[0] }}" target="_blank" rel="noopener" class="inquiries-btn inquiries-btn-secondary">Attachment</a>
        @else
            -
        @endif
    </td>
    <td data-col="assigndate">{{ $r->LASTMODIFIED ? date('d/m/Y', strtotime($r->LASTMODIFIED)) : ($r->CREATEDAT ? date('d/m/Y', strtotime($r->CREATEDAT)) : '-') }}</td>
    <td data-col="status"><span class="inquiries-status {{ $statusClass }}">{{ $statusDisplay }}</span></td>
    <td class="inquiries-col-action inquiries-action-cell">
        @if($isAssignedRow)
            @if(in_array($rawStatus, ['', 'OPEN', 'CREATED', 'PENDING', 'ONGOING'], true))
                <a href="{{ route('admin.inquiries.edit', ['leadId' => $r->LEADID, 'tab' => 'all']) }}" class="inquiries-btn inquiries-btn-assign inquiries-edit-inquiry-btn" data-lead-id="{{ $r->LEADID }}" title="Edit" aria-label="Edit"><i class="bi bi-pencil-square" aria-hidden="true"></i></a>
            @endif
            <button type="button" class="inquiries-btn inquiries-btn-assign inquiries-view-status-btn" data-lead-id="{{ $r->LEADID }}" title="View Status" aria-label="View Status"><i class="bi bi-eye" aria-hidden="true"></i></button>
            <button type="button" class="inquiries-btn inquiries-btn-assign inquiries-btn-assign-danger inquiries-mark-failed-btn" data-lead-id="{{ $r->LEADID }}" data-status="{{ $statusDisplay }}" title="Mark As Failed" aria-label="Mark As Failed"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
        @else
            @if($canAssignRow)
                <button type="button" class="inquiries-btn inquiries-btn-assign inquiries-incoming-assign-btn" data-assign-lead="{{ $r->LEADID }}" data-assign-name="{{ e($assignLeadLabel) }}" title="Assign" aria-label="Assign"><i class="bi bi-person-check" aria-hidden="true"></i></button>
            @endif
            <a href="{{ route('admin.inquiries.edit', ['leadId' => $r->LEADID, 'tab' => 'all']) }}" class="inquiries-btn inquiries-btn-assign inquiries-edit-inquiry-btn" data-lead-id="{{ $r->LEADID }}" title="Edit" aria-label="Edit"><i class="bi bi-pencil-square" aria-hidden="true"></i></a>
            <button type="button" class="inquiries-btn inquiries-btn-assign inquiries-delete-inquiry-btn" data-lead-id="{{ $r->LEADID }}" title="Delete" aria-label="Delete"><i class="bi bi-trash" aria-hidden="true"></i></button>
        @endif
    </td>
</tr>
@empty
<tr><td colspan="25" class="inquiries-empty">No inquiries found.</td></tr>
@endforelse
