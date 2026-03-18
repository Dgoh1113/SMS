@forelse ($users as $u)
    @php
        $roleUpper = strtoupper(trim($u['SYSTEMROLE'] ?? ''));
        $roleClass = $roleUpper === 'ADMIN'
            ? 'maintain-users-pill-role-admin'
            : ($roleUpper === 'MANAGER' ? 'maintain-users-pill-role-manager' : 'maintain-users-pill-role-dealer');
        $lastLoginStr = $u['LASTLOGIN'] ? \Carbon\Carbon::parse($u['LASTLOGIN'])->format('Y-m-d H:i') : '';
        $searchHaystack = strtolower(
            ($u['USERID'] ?? '') . ' ' .
            ($u['EMAIL'] ?? '') . ' ' .
            ($u['ALIAS'] ?? '') . ' ' .
            ($u['COMPANY'] ?? '') . ' ' .
            ($u['TEMP_PASSWORD'] ?? '') . ' ' .
            (($u['HAS_LOGGED_IN'] ?? false) ? 'logged in' : 'not logged in') . ' ' .
            $roleUpper . ' ' .
            ($u['ISACTIVE'] ? 'active' : 'inactive') . ' ' .
            $lastLoginStr
        );
    @endphp
    <tr class="maintain-users-row" data-search="{{ $searchHaystack }}"
        data-userid="{{ $u['USERID'] }}"
        data-email="{{ e($u['EMAIL']) }}"
        data-role="{{ $roleUpper }}"
        data-alias="{{ e($u['ALIAS'] ?? '') }}"
        data-company="{{ e($u['COMPANY'] ?? '') }}"
        data-postcode="{{ e($u['POSTCODE'] ?? '') }}"
        data-city="{{ e($u['CITY'] ?? '') }}"
        data-password="{{ ($u['TEMP_PASSWORD'] ?? '') !== '' ? $u['TEMP_PASSWORD'] : (($u['HAS_LOGGED_IN'] ?? false) ? 'protected' : 'not generated') }}"
        data-active="{{ $u['ISACTIVE'] ? '1' : '0' }}">
        <td data-col="userid">{{ $u['USERID'] }}</td>
        <td data-col="email">{{ $u['EMAIL'] }}</td>
        <td data-col="role">
            <span class="maintain-users-pill-role {{ $roleClass }}">{{ $roleUpper ?: '-' }}</span>
        </td>
        <td data-col="alias">{{ $u['ALIAS'] ?: '-' }}</td>
        <td data-col="company">{{ $u['COMPANY'] ?: '-' }}</td>
        <td data-col="password">
            @if (!($u['HAS_LOGGED_IN'] ?? false) && ($u['TEMP_PASSWORD'] ?? '') !== '')
                <span class="maintain-users-temp-password">{{ $u['TEMP_PASSWORD'] }}</span>
            @elseif (!($u['HAS_LOGGED_IN'] ?? false))
                <span class="maintain-users-pill-password empty">Not generated</span>
            @else
                <span class="maintain-users-pill-password set">Protected</span>
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
                    <form method="POST" action="{{ route('admin.maintain-users.send-temp-password', $u['USERID']) }}" class="maintain-users-inline-form">
                        @csrf
                        <button type="submit" class="maintain-users-temp-send-btn" title="{{ ($u['TEMP_PASSWORD_EMAILED'] ?? false) ? 'Temporary password already emailed - send again' : 'Send temporary password' }}" aria-label="Send temporary password">
                            <i class="bi {{ ($u['TEMP_PASSWORD_EMAILED'] ?? false) ? 'bi-envelope-fill' : 'bi-envelope' }}" aria-hidden="true"></i>
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
