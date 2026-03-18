@php
    $productNames = $productNames ?? [
        1 => 'SQL Account', 2 => 'SQL Payroll', 3 => 'SQL Production', 4 => 'Mobile Sales',
        5 => 'SQL Ecommerce', 6 => 'SQL EBI Wellness POS', 7 => 'SQL X Suduai', 8 => 'SQL X-Store',
        9 => 'SQL Vision', 10 => 'SQL HRMS', 11 => 'Others',
    ];
@endphp

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
        $statusClass = 'inquiries-status-rewarded';
        $statusDisp = $rawStatus !== '' ? $rawStatus : 'REWARDED';

        // Payouts date: prefer latest REWARDED/PAID activity date; fallback to last modified.
        $payoutDate = '—';
        if (!empty($r->REWARD_DATE)) {
            $payoutDate = date('d/m/Y', strtotime((string) $r->REWARD_DATE));
        } elseif (!empty($r->LASTMODIFIED)) {
            $payoutDate = date('d/m/Y', strtotime((string) $r->LASTMODIFIED));
        } elseif (!empty($r->CREATEDAT)) {
            $payoutDate = date('d/m/Y', strtotime((string) $r->CREATEDAT));
        }

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
    <tr class="payouts-row inquiry-row" data-search="{{ $searchHaystack }}">
        <td data-col="inquiryid">#SQL-{{ $r->LEADID }}</td>
        <td data-col="payoutdate">{{ $payoutDate }}</td>
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
    <tr><td colspan="18" class="inquiries-empty">No rewarded payouts.</td></tr>
@endforelse
