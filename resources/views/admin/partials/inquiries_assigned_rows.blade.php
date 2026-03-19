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


