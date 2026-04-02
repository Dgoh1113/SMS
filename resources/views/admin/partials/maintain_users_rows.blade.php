@forelse ($users as $u)
    @php
        $roleUpper = strtoupper(trim($u['SYSTEMROLE'] ?? ''));
        $roleClass = $roleUpper === 'ADMIN'
            ? 'maintain-users-pill-role-admin'
            : ($roleUpper === 'MANAGER' ? 'maintain-users-pill-role-manager' : 'maintain-users-pill-role-dealer');
        $lastLoginStr = $u['LASTLOGIN'] ? \Carbon\Carbon::parse($u['LASTLOGIN'])->format('Y-m-d H:i') : '';
        $passkeyStatus = ($u['HAS_LOGGED_IN'] ?? false)
            ? 'protected'
            : (($u['PASSKEY_SETUP_LINK_SENT'] ?? false)
                ? (($u['PASSKEY_SETUP_LINK_EXPIRED'] ?? false) ? 'link expired' : 'link sent')
                : 'ready to send');
        $searchHaystack = strtolower(
            ($u['USERID'] ?? '') . ' ' .
            ($u['EMAIL'] ?? '') . ' ' .
            ($u['ALIAS'] ?? '') . ' ' .
            ($u['COMPANY'] ?? '') . ' ' .
            $passkeyStatus . ' ' .
            (($u['HAS_LOGGED_IN'] ?? false) ? 'logged in' : 'not logged in') . ' ' .
            $roleUpper . ' ' .
            ($u['ISACTIVE'] ? 'active' : 'inactive') . ' ' .
            $lastLoginStr
        );
        $setupLinkTitle = ($u['PASSKEY_SETUP_LINK_SENT'] ?? false)
            ? 'Passkey setup link already emailed - send again'
            : 'Send passkey setup link';
    @endphp
    <tr class="maintain-users-row" data-search="{{ $searchHaystack }}"
        data-userid="{{ $u['USERID'] }}"
        data-email="{{ e($u['EMAIL']) }}"
        data-role="{{ $roleUpper }}"
        data-alias="{{ e($u['ALIAS'] ?? '') }}"
        data-company="{{ e($u['COMPANY'] ?? '') }}"
        data-postcode="{{ e($u['POSTCODE'] ?? '') }}"
        data-city="{{ e($u['CITY'] ?? '') }}"
        data-passkey="{{ $passkeyStatus }}"
        data-active="{{ $u['ISACTIVE'] ? '1' : '0' }}">
        <td data-col="userid">{{ $u['USERID'] }}</td>
        <td data-col="email">{{ $u['EMAIL'] }}</td>
        <td data-col="role">
            <span class="maintain-users-pill-role {{ $roleClass }}">{{ $roleUpper ?: '-' }}</span>
        </td>
        <td data-col="alias">{{ $u['ALIAS'] ?: '-' }}</td>
        <td data-col="company">{{ $u['COMPANY'] ?: '-' }}</td>
        <td data-col="passkey">
            @if (!($u['HAS_LOGGED_IN'] ?? false) && ($u['PASSKEY_SETUP_LINK_SENT'] ?? false) && !($u['PASSKEY_SETUP_LINK_EXPIRED'] ?? false))
                <span class="maintain-users-pill-passkey sent">Link sent</span>
            @elseif (!($u['HAS_LOGGED_IN'] ?? false) && ($u['PASSKEY_SETUP_LINK_EXPIRED'] ?? false))
                <span class="maintain-users-pill-passkey expired">Link expired</span>
            @elseif (!($u['HAS_LOGGED_IN'] ?? false))
                <span class="maintain-users-pill-passkey empty">Ready to send</span>
            @else
                <span class="maintain-users-pill-passkey set">Protected</span>
            @endif
        </td>
        <td data-col="active">
            <span class="maintain-users-pill-active {{ $u['ISACTIVE'] ? 'yes' : 'no' }}">
                {{ $u['ISACTIVE'] ? 'Active' : 'Inactive' }}
            </span>
        </td>
        <td data-col="lastlogin">
            @if ($u['LASTLOGIN'])
                {{ \Carbon\Carbon::parse($u['LASTLOGIN'])->format('Y-m-d H:i') }}
            @else
                -
            @endif
        </td>
        <td class="maintain-users-col-action">
            <div class="maintain-users-action-cell">
                <button type="button" class="maintain-users-edit-btn" data-userid="{{ $u['USERID'] }}" title="Edit" aria-label="Edit user">
                    <i class="bi bi-pencil-square" aria-hidden="true"></i>
                </button>
                @if (!($u['HAS_LOGGED_IN'] ?? false))
                    <form method="POST" action="{{ route('admin.maintain-users.send-passkey-setup-link', $u['USERID']) }}" class="maintain-users-inline-form">
                        @csrf
                        <button type="submit" class="maintain-users-temp-send-btn" title="{{ $setupLinkTitle }}" aria-label="Send passkey setup link">
                            <i class="bi {{ ($u['PASSKEY_SETUP_LINK_SENT'] ?? false) ? 'bi-envelope-fill' : 'bi-envelope' }}" aria-hidden="true"></i>
                        </button>
                    </form>
                @endif
            </div>
        </td>
    </tr>
@empty
    <tr class="maintain-users-empty-row">
        <td colspan="9">
            <div class="maintain-users-empty">No users found.</div>
        </td>
    </tr>
@endforelse
