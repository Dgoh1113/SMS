@forelse($leads as $r)
    @php
        $productIds = [];
        if (isset($r->PRODUCTID) && $r->PRODUCTID !== '' && $r->PRODUCTID !== null) {
            $ids = is_numeric($r->PRODUCTID) ? [(int)$r->PRODUCTID] : array_map('intval', array_filter(explode(',', (string)$r->PRODUCTID)));
            foreach ($ids as $id) {
                if ($id > 0) {
                    $productIds[] = (int) $id;
                }
            }
        }
        $rawStatus = strtoupper(trim($r->ACT_STATUS ?? ''));
        $statusClass = 'inquiries-status-new';
        switch ($rawStatus) {
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
        $statusDisplay = $rawStatus === '' ? '—' : (in_array($rawStatus, ['FOLLOWUP', 'FOLLOW UP'], true) ? 'Follow Up' : $rawStatus);
        $customerName = trim(($r->COMPANYNAME ?? '') . ' ' . ($r->CONTACTNAME ?? '')) ?: '—';
    @endphp
    <tr class="inquiry-row" data-search="{{ strtolower(trim(($r->COMPANYNAME ?? '').' '.($r->CONTACTNAME ?? '').' '.($r->LEADID ?? ''))) }}">
        <td data-col="inquiryid"><strong>#SQL-{{ $r->LEADID }}</strong></td>
        <td data-col="date">{{ $r->CREATEDAT ? date('d/m/Y', strtotime($r->CREATEDAT)) : '—' }}</td>
        <td data-col="customer">{{ $customerName }}</td>
        <td data-col="source">{{ $r->ASSIGNED_BY_EMAIL ?? '—' }}</td>
        <td data-col="postcode">{{ $r->POSTCODE ?? '—' }}</td>
        <td data-col="contactno">{{ $r->CONTACTNO ?? '—' }}</td>
        <td data-col="businessnature">{{ $r->BUSINESSNATURE ?? '—' }}</td>
        <td data-col="users">{{ $r->USERCOUNT ?? '—' }}</td>
        <td data-col="existingsw">{{ $r->EXISTINGSOFTWARE ?? '—' }}</td>
        <td data-col="demomode">{{ $r->DEMOMODE ?? '—' }}</td>
        <td data-col="products">
            @if(!empty($productIds))
                <div class="inquiries-pill-group">
                    @foreach($productIds as $id)
                        <span class="inquiries-pill inquiries-pill-p{{ $id }}">{{ $productNames[$id] ?? ('Product ' . $id) }}</span>
                    @endforeach
                </div>
            @else
                —
            @endif
        </td>
        <td data-col="message">{{ Str::limit($r->DESCRIPTION ?? '—', 20) }}</td>
        <td data-col="referralcode">{{ $r->REFERRALCODE ?? '—' }}</td>
        <td data-col="assignby">{{ $r->ASSIGNED_BY_EMAIL ?? '—' }}</td>
        <td data-col="status"><span class="inquiries-status {{ $statusClass }}">{{ $statusDisplay }}</span></td>
        <td class="inquiries-col-action">
            @php
                $actStatus = strtoupper($r->ACT_STATUS ?? 'PENDING');
                $isFailed = $actStatus === 'FAILED';
            @endphp
            @if ($isFailed)
                <button type="button" class="inquiries-view-btn" data-lead-id="{{ $r->LEADID }}" data-customer="{{ $customerName }}">View</button>
            @else
                <button type="button" class="inquiries-update-btn" data-lead-id="{{ $r->LEADID }}" data-customer="{{ $customerName }}" data-status="{{ $actStatus }}">Update</button>
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

