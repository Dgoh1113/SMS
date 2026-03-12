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

