@forelse($items as $r)
    @php
        $searchHaystack = strtolower(trim(($r->USERID ?? '').' '.($r->EMAIL ?? '').' '.($r->POSTCODE ?? '').' '.($r->CITY ?? '').' '.($r->COMPANY ?? '').' '.($r->ALIAS ?? '')));
        $convRate = (float)($r->CONVERSION_RATE ?? 0);
        $convClass = $convRate >= 60 ? 'dealer-conversion-high' : ($convRate >= 40 ? 'dealer-conversion-mid' : 'dealer-conversion-low');
    @endphp
    <tr class="dealer-row inquiry-row" data-search="{{ $searchHaystack }}">
        <td data-col="userid">{{ $r->USERID }}</td>
        <td data-col="alias">{{ $r->ALIAS ?? '-' }}</td>
        <td data-col="company">{{ $r->COMPANY ?? '-' }}</td>
        <td data-col="email">{{ $r->EMAIL }}</td>
        <td data-col="postcode">{{ $r->POSTCODE ?? '-' }}</td>
        <td data-col="city">{{ $r->CITY ?? '-' }}</td>
        <td data-col="totallead">{{ number_format((int)($r->TOTAL_LEAD ?? 0)) }}</td>
        <td data-col="totalongoing">{{ number_format((int)($r->TOTAL_ONGOING ?? 0)) }}</td>
        <td data-col="totalclosed">{{ number_format((int)($r->TOTAL_CLOSED ?? 0)) }}</td>
        <td data-col="totalfailed">{{ number_format((int)($r->TOTAL_FAILED ?? 0)) }}</td>
        <td data-col="conversionrate"><span class="dealer-conversion-label {{ $convClass }}">{{ number_format($convRate, 1) }}%</span></td>
        <td data-col="active">{{ ($r->ISACTIVE ?? 0) ? 'Yes' : 'No' }}</td>
    </tr>
@empty
    <tr><td colspan="12" class="inquiries-empty">No dealers yet.</td></tr>
@endforelse
