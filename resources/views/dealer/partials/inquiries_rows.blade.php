@forelse($leads as $r)
    @php
        $productIds = [];
        $attachmentUrls = is_array($r->ATTACHMENT_URLS ?? null) ? array_values(array_filter($r->ATTACHMENT_URLS, function ($url) {
            $normalized = is_string($url) ? trim($url) : '';
            return $normalized !== '' && !in_array(strtolower($normalized), ['-', 'null', 'undefined', '#'], true);
        })) : [];
        if (isset($r->PRODUCTID) && $r->PRODUCTID !== '' && $r->PRODUCTID !== null) {
            $ids = is_numeric($r->PRODUCTID) ? [(int) $r->PRODUCTID] : array_map('intval', array_filter(explode(',', (string) $r->PRODUCTID)));
            foreach ($ids as $id) {
                if ($id > 0) {
                    $productIds[] = (int) $id;
                }
            }
        }
        $rawStatus = strtoupper(trim($r->ACT_STATUS ?? ''));
        $statusClass = 'inquiries-status-new';
        switch ($rawStatus) {
            case 'CREATED':             $statusClass = 'inquiries-status-created'; break;
            case 'PENDING':             $statusClass = 'inquiries-status-pending'; break;
            case 'FOLLOWUP':            $statusClass = 'inquiries-status-followup'; break;
            case 'FOLLOW UP':           $statusClass = 'inquiries-status-followup'; break;
            case 'DEMO':                $statusClass = 'inquiries-status-demo'; break;
            case 'CONFIRMED':           $statusClass = 'inquiries-status-confirmed'; break;
            case 'COMPLETED':           $statusClass = 'inquiries-status-completed'; break;
            case 'REWARDED':            $statusClass = 'inquiries-status-rewarded'; break;
            case 'REWARD DISTRIBUTED':  $statusClass = 'inquiries-status-rewarded'; break;
            case 'CASE COMPLETED':      $statusClass = 'inquiries-status-completed'; break;
            case 'FAILED':              $statusClass = 'inquiries-status-failed'; break;
            default:                    $statusClass = 'inquiries-status-new'; break;
        }
        $statusDisplay = $rawStatus === '' ? '-' : (in_array($rawStatus, ['FOLLOWUP', 'FOLLOW UP'], true) ? 'Follow Up' : $rawStatus);
        $companyName = trim((string) ($r->COMPANYNAME ?? ''));
        $contactName = trim((string) ($r->CONTACTNAME ?? ''));
        $customerName = $companyName !== '' && $contactName !== ''
            ? ($companyName . '-' . $contactName)
            : ($companyName !== '' ? $companyName : ($contactName !== '' ? $contactName : '-'));
        $addr1 = trim((string) ($r->ADDRESS1 ?? ''));
        $addr2 = trim((string) ($r->ADDRESS2 ?? ''));
        $addressDisplay = trim($addr1 . ' ' . $addr2) ?: '-';
        $rowPage = (int) floor(($loop->index ?? 0) / 10) + 1;
        $isFocusLead = (int) ($focusLeadId ?? 0) > 0 && (int) ($r->LEADID ?? 0) === (int) ($focusLeadId ?? 0);
    @endphp
    <tr class="inquiry-row{{ $isFocusLead ? ' inquiry-row--notif-highlight' : '' }}" data-lead-id="{{ $r->LEADID }}" data-search="{{ strtolower(trim($companyName . ' ' . $contactName . ' ' . ($r->LEADID ?? ''))) }}" data-page="{{ $rowPage }}">
        <td data-col="inquiryid">#SQL-{{ $r->LEADID }}</td>
        <td data-col="date">{{ $r->CREATEDAT ? date('d/m/Y', strtotime($r->CREATEDAT)) : '-' }}</td>
        <td data-col="customer">
            <span title="{{ $customerName !== '-' ? $customerName : '' }}">{{ $customerName }}</span>
        </td>
        <td data-col="email">{{ $r->EMAIL ?? '-' }}</td>
        <td data-col="postcode">{{ $r->POSTCODE ?? '-' }}</td>
        <td data-col="city">{{ $r->CITY ?? '-' }}</td>
        <td data-col="address">{{ $addressDisplay }}</td>
        <td data-col="contactno">{{ $r->CONTACTNO ?? '-' }}</td>
        <td data-col="businessnature">{{ $r->BUSINESSNATURE ?? '-' }}</td>
        <td data-col="users">{{ $r->USERCOUNT ?? '-' }}</td>
        <td data-col="existingsw">{{ $r->EXISTINGSOFTWARE ?? '-' }}</td>
        <td data-col="demomode">{{ $r->DEMOMODE ?? '-' }}</td>
        <td data-col="products">
            @if(!empty($productIds))
                <div class="inquiries-pill-group">
                    @foreach($productIds as $id)
                        <span class="inquiries-pill inquiries-pill-p{{ $id }}">{{ $productNames[$id] ?? ('Product ' . $id) }}</span>
                    @endforeach
                </div>
            @else
                -
            @endif
        </td>
        <td data-col="assigndate">{{ !empty($r->ASSIGNDATE) ? date('d/m/Y', strtotime($r->ASSIGNDATE)) : ($r->CREATEDAT ? date('d/m/Y', strtotime($r->CREATEDAT)) : '-') }}</td>
        <td data-col="completiondate">{{ !empty($r->COMPLETED_AT) ? date('d/m/Y', strtotime($r->COMPLETED_AT)) : '-' }}</td>
        <td data-col="payoutsdate">{{ !empty($r->REWARDED_AT) ? date('d/m/Y', strtotime($r->REWARDED_AT)) : '-' }}</td>
        @php
            $fullMsg = (string) ($r->DESCRIPTION ?? '');
            $fullMsgTrim = trim($fullMsg);
            $msgPreview = $fullMsgTrim === '' ? '-' : (mb_strlen($fullMsgTrim) > 30 ? (mb_substr($fullMsgTrim, 0, 30) . '...') : $fullMsgTrim);
            $isLongMsg = $fullMsgTrim !== '' && mb_strlen($fullMsgTrim) > 30;
        @endphp
        <td data-col="message" class="inquiries-msg-cell {{ $isLongMsg ? 'inquiries-msg-clickable' : '' }}"
            @if($isLongMsg) data-full-message="{{ e($fullMsgTrim) }}" @endif>
            {{ $msgPreview }}
        </td>
        <td data-col="referralcode">{{ $r->REFERRALCODE ?? '-' }}</td>
        <td data-col="attachment">
            @if(!empty($attachmentUrls))
                <a href="{{ $attachmentUrls[0] }}" target="_blank" rel="noopener" class="inquiries-btn inquiries-btn-secondary">Attachment</a>
            @else
                -
            @endif
        </td>
        <td data-col="assignby">{{ $r->CREATEDBY_NAME ?? $r->ASSIGNED_BY_EMAIL ?? '-' }}</td>
        <td data-col="status"><span class="inquiries-status {{ $statusClass }}">{{ $statusDisplay }}</span></td>
        <td class="inquiries-col-action inquiries-action-cell">
            @php
                $actStatus = strtoupper($r->ACT_STATUS ?? 'PENDING');
                $isFailed = $actStatus === 'FAILED';
            @endphp
            @if ($isFailed)
                <button type="button" class="inquiries-btn inquiries-btn-assign inquiries-view-status-btn inquiries-view-btn" data-lead-id="{{ $r->LEADID }}" data-customer="{{ $customerName }}" data-status="{{ $actStatus }}" data-referral-code="{{ trim((string) ($r->REFERRALCODE ?? '')) }}" title="View" aria-label="View"><i class="bi bi-eye" aria-hidden="true"></i></button>
            @else
                <button type="button" class="inquiries-btn inquiries-btn-assign inquiries-edit-inquiry-btn inquiries-update-btn" data-lead-id="{{ $r->LEADID }}" data-customer="{{ $customerName }}" data-status="{{ $actStatus }}" data-referral-code="{{ trim((string) ($r->REFERRALCODE ?? '')) }}" title="Update" aria-label="Update"><i class="bi bi-pencil-square" aria-hidden="true"></i></button>
            @endif
        </td>
    </tr>
@empty
    <tr class="inquiries-empty-row">
        <td colspan="22" class="inquiries-empty-cell">
            <div class="dealer-table-empty">No inquiries assigned yet.</div>
        </td>
    </tr>
@endforelse
