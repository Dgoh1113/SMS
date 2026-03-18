@if (count($batchEligibleUsers) === 0)
    <div class="maintain-users-batch-empty">No users are waiting for a first login email.</div>
@else
    @foreach ($batchEligibleUsers as $u)
        @php
            $batchDisplayName = trim((string) ($u['COMPANY'] ?? '')) !== ''
                ? trim((string) ($u['COMPANY'] ?? ''))
                : (trim((string) ($u['ALIAS'] ?? '')) !== '' ? trim((string) ($u['ALIAS'] ?? '')) : trim((string) ($u['EMAIL'] ?? '')));
        @endphp
        <label class="maintain-users-batch-item">
            <input type="checkbox" name="USERIDS[]" value="{{ $u['USERID'] }}" checked>
            <div class="maintain-users-batch-item-main">
                <div class="maintain-users-batch-item-name">{{ $batchDisplayName }}</div>
                <div class="maintain-users-batch-item-meta">{{ $u['USERID'] }} &bull; {{ $u['EMAIL'] }}</div>
            </div>
        </label>
    @endforeach
@endif
