@extends('layouts.app')
@section('title', 'Maintain Users')
@push('styles')
    <style>
        .maintain-users-page {
            padding: 20px 24px;
            text-align: left;
        }
        .maintain-users-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 16px;
            flex-wrap: wrap;
        }
        .maintain-users-header-left {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .maintain-users-title {
            font-size: 1.25rem;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
        }
        .maintain-users-subtitle {
            font-size: 0.8rem;
            color: #64748b;
        }
        .maintain-users-header-right {
            display: flex;
            flex-direction: row;
            flex-wrap: wrap;
            align-items: center;
            gap: 12px;
        }
        .maintain-users-actions {
            display: flex;
            align-items: center;
        }
        .maintain-users-add-btn {
            border-radius: 999px;
            border: none;
            padding: 8px 14px;
            font-size: 0.85rem;
            font-weight: 700;
            background: #4f46e5;
            color: #ffffff;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            box-shadow: 0 8px 20px rgba(79, 70, 229, 0.3);
        }
        .maintain-users-add-btn:hover {
            filter: brightness(0.97);
        }
        .maintain-users-table-wrap {
            margin-top: 12px;
        }
        .maintain-users-pill-role {
            display: inline-flex;
            align-items: center;
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 0.65rem;
            font-weight: 700;
        }
        .maintain-users-pill-role-admin {
            background: rgba(239, 68, 68, 0.1);
            color: #b91c1c;
        }
        .maintain-users-pill-role-manager {
            background: rgba(59, 130, 246, 0.1);
            color: #1d4ed8;
        }
        .maintain-users-pill-role-dealer {
            background: rgba(34, 197, 94, 0.1);
            color: #15803d;
        }
        .maintain-users-pill-active {
            display: inline-flex;
            align-items: center;
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 0.65rem;
            font-weight: 700;
        }
        .maintain-users-pill-active.yes {
            background: rgba(34, 197, 94, 0.1);
            color: #16a34a;
        }
        .maintain-users-pill-active.no {
            background: rgba(248, 113, 113, 0.1);
            color: #dc2626;
        }
        .maintain-users-empty {
            padding: 14px 16px;
            font-size: 0.8rem;
            color: #94a3b8;
        }
        /* Modal */
        .maintain-users-modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.45);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 80;
        }
        .maintain-users-modal-backdrop.is-open {
            display: flex;
        }
        .maintain-users-modal {
            background: #ffffff;
            border-radius: 16px;
            padding: 20px 22px 18px;
            width: 100%;
            max-width: 480px;
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.3);
        }
        .maintain-users-modal-title {
            font-size: 1rem;
            font-weight: 800;
            margin: 0 0 4px 0;
            color: #0f172a;
        }
        .maintain-users-modal-sub {
            font-size: 0.78rem;
            color: #64748b;
            margin-bottom: 12px;
        }
        .maintain-users-form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px 12px;
            margin-bottom: 12px;
        }
        .maintain-users-form-grid .full {
            grid-column: 1 / -1;
        }
        .maintain-users-field label {
            display: block;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #94a3b8;
            margin-bottom: 3px;
        }
        .maintain-users-field input,
        .maintain-users-field select {
            width: 100%;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            padding: 6px 8px;
            font-size: 0.8rem;
        }
        .maintain-users-modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            margin-top: 8px;
        }
        .maintain-users-btn-secondary,
        .maintain-users-btn-primary {
            border-radius: 999px;
            padding: 6px 14px;
            font-size: 0.8rem;
            font-weight: 700;
            border: 1px solid transparent;
            cursor: pointer;
        }
        .maintain-users-btn-secondary {
            background: #ffffff;
            border-color: #e5e7eb;
            color: #475569;
        }
        .maintain-users-btn-secondary:hover {
            background: #f9fafb;
        }
        .maintain-users-btn-primary {
            background: #4f46e5;
            color: #ffffff;
        }
        .maintain-users-btn-primary:hover {
            filter: brightness(0.97);
        }
        .maintain-users-error {
            margin-top: 8px;
            font-size: 0.78rem;
            color: #dc2626;
        }
        .maintain-users-success {
            margin-top: 8px;
            font-size: 0.78rem;
            color: #16a34a;
        }
        .maintain-users-confirm {
            display: none;
            margin-bottom: 12px;
            padding: 10px 14px;
            font-size: 0.85rem;
            font-weight: 600;
            color: #16a34a;
            background: rgba(34, 197, 94, 0.12);
            border-radius: 10px;
            border: 1px solid rgba(34, 197, 94, 0.3);
        }
        .maintain-users-confirm.is-visible {
            display: block;
        }
        .maintain-users-col-action {
            white-space: nowrap;
            vertical-align: middle;
        }
        .maintain-users-edit-btn {
            border: 1px solid #c7d2fe;
            background: #eef2ff;
            color: #4f46e5;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
        }
        .maintain-users-edit-btn:hover {
            background: #e0e7ff;
            border-color: #818cf8;
        }
        .maintain-users-table-search-wrap {
            display: flex;
            align-items: center;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 0 10px;
            min-width: 140px;
            max-width: 220px;
        }
        .maintain-users-table-search-icon {
            color: #9ca3af;
            font-size: 12px;
            margin-right: 6px;
        }
        .maintain-users-table-search-input {
            flex: 1;
            border: none;
            outline: none;
            padding: 6px 0;
            font-size: 12px;
            min-width: 0;
        }
        .maintain-users-table-search-input::placeholder {
            color: #9ca3af;
        }
        .maintain-users-thead-row th {
            background: #ede9fe;
            color: #4c1d95;
            font-size: 12px;
            font-weight: 600;
            padding: 10px 12px;
            text-align: left;
            border-bottom: 1px solid #ddd6fe;
        }
        .maintain-users-th {
            white-space: nowrap;
        }
    </style>
@endpush

@section('content')
<div class="maintain-users-page">
    <div class="maintain-users-header">
        <div class="maintain-users-header-left">
            <h2 class="maintain-users-title">Maintain Users</h2>
            <div class="maintain-users-subtitle">Create and manage admin, manager, and dealer accounts.</div>
        </div>
        <div class="maintain-users-header-right">
            <div class="maintain-users-table-search-wrap">
                <span class="maintain-users-table-search-icon"><i class="bi bi-search"></i></span>
                <input type="text" class="maintain-users-table-search-input" id="maintainUsersSearchInput" placeholder="Search table..." autocomplete="off">
            </div>
            <div class="maintain-users-actions">
                <button type="button" class="maintain-users-add-btn" id="maintainUsersAddBtn">
                    <span>+ Add User</span>
                </button>
            </div>
        </div>
    </div>

    <div class="maintain-users-table-wrap">
        @if (session('error'))
            <div class="maintain-users-error">{{ session('error') }}</div>
        @elseif (session('success'))
            <div class="maintain-users-success">{{ session('success') }}</div>
        @endif
        @if (count($users) === 0)
            <div class="maintain-users-empty">No users found.</div>
        @else
            <div class="dashboard-table-wrapper">
            <table class="dashboard-table maintain-users-table" id="maintainUsersTable">
                <thead>
                <tr class="maintain-users-thead-row inquiries-header-row">
                    <th data-col="userid" class="inquiries-header-cell">
                        <span class="inquiries-header-label">USER ID</span>
                        <span class="inquiries-filter-wrap">
                            <input type="text" class="inquiries-grid-filter" data-col="userid" placeholder="Search">
                            <i class="bi bi-search inquiries-filter-icon"></i>
                        </span>
                    </th>
                    <th data-col="email" class="inquiries-header-cell">
                        <span class="inquiries-header-label">EMAIL</span>
                        <span class="inquiries-filter-wrap">
                            <input type="text" class="inquiries-grid-filter" data-col="email" placeholder="Search">
                            <i class="bi bi-search inquiries-filter-icon"></i>
                        </span>
                    </th>
                    <th data-col="role" class="inquiries-header-cell">
                        <span class="inquiries-header-label">ROLE</span>
                        <span class="inquiries-filter-wrap">
                            <input type="text" class="inquiries-grid-filter" data-col="role" placeholder="Search">
                            <i class="bi bi-search inquiries-filter-icon"></i>
                        </span>
                    </th>
                    <th data-col="alias" class="inquiries-header-cell">
                        <span class="inquiries-header-label">ALIAS</span>
                        <span class="inquiries-filter-wrap">
                            <input type="text" class="inquiries-grid-filter" data-col="alias" placeholder="Search">
                            <i class="bi bi-search inquiries-filter-icon"></i>
                        </span>
                    </th>
                    <th data-col="company" class="inquiries-header-cell">
                        <span class="inquiries-header-label">COMPANY</span>
                        <span class="inquiries-filter-wrap">
                            <input type="text" class="inquiries-grid-filter" data-col="company" placeholder="Search">
                            <i class="bi bi-search inquiries-filter-icon"></i>
                        </span>
                    </th>
                    <th data-col="active" class="inquiries-header-cell">
                        <span class="inquiries-header-label">ACTIVE</span>
                        <span class="inquiries-filter-wrap">
                            <input type="text" class="inquiries-grid-filter" data-col="active" placeholder="Search">
                            <i class="bi bi-search inquiries-filter-icon"></i>
                        </span>
                    </th>
                    <th data-col="lastlogin" class="inquiries-header-cell">
                        <span class="inquiries-header-label">LAST LOGIN</span>
                        <span class="inquiries-filter-wrap">
                            <input type="text" class="inquiries-grid-filter" data-col="lastlogin" placeholder="Search">
                            <i class="bi bi-search inquiries-filter-icon"></i>
                        </span>
                    </th>
                    <th class="maintain-users-col-action">
                        <span class="inquiries-header-label">ACTION</span>
                    </th>
                </tr>
                </thead>
                <tbody>
                @foreach ($users as $u)
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
                        data-active="{{ $u['ISACTIVE'] ? '1' : '0' }}">
                        <td data-col="userid">{{ $u['USERID'] }}</td>
                        <td data-col="email">{{ $u['EMAIL'] }}</td>
                        <td data-col="role">
                            <span class="maintain-users-pill-role {{ $roleClass }}">{{ $roleUpper ?: '—' }}</span>
                        </td>
                        <td data-col="alias">{{ $u['ALIAS'] ?: '—' }}</td>
                        <td data-col="company">{{ $u['COMPANY'] ?: '—' }}</td>
                        <td data-col="active">
                            <span class="maintain-users-pill-active {{ $u['ISACTIVE'] ? 'yes' : 'no' }}">
                                {{ $u['ISACTIVE'] ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td data-col="lastlogin">
                            @if ($u['LASTLOGIN'])
                                {{ \Carbon\Carbon::parse($u['LASTLOGIN'])->format('Y-m-d H:i') }}
                            @else
                                —
                            @endif
                        </td>
                        <td class="maintain-users-col-action">
                            <button type="button" class="maintain-users-edit-btn" data-userid="{{ $u['USERID'] }}" aria-label="Edit user">Edit</button>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            </div>
        @endif
    </div>
</div>

<div class="maintain-users-modal-backdrop" id="maintainUsersModal">
    <div class="maintain-users-modal">
        <h3 class="maintain-users-modal-title">Add User</h3>
        <div class="maintain-users-modal-sub">Create a new admin, manager, or dealer account.</div>
        <form method="POST" action="{{ route('admin.maintain-users.store') }}">
            @csrf
            <div class="maintain-users-form-grid">
                <div class="maintain-users-field full">
                    <label for="EMAIL">Email</label>
                    <input type="email" id="EMAIL" name="EMAIL" maxlength="50" required>
                </div>
                <div class="maintain-users-field">
                    <label for="PASSWORD">Password</label>
                    <input type="password" id="PASSWORD" name="PASSWORD" required>
                </div>
                <div class="maintain-users-field">
                    <label for="SYSTEMROLE">Role</label>
                    <select id="SYSTEMROLE" name="SYSTEMROLE" required>
                        <option value="ADMIN">Admin</option>
                        <option value="MANAGER">Manager</option>
                        <option value="DEALER" selected>Dealer</option>
                    </select>
                </div>
                <div class="maintain-users-field">
                    <label for="ALIAS">Alias</label>
                    <input type="text" id="ALIAS" name="ALIAS" maxlength="50">
                </div>
                <div class="maintain-users-field">
                    <label for="COMPANY">Company</label>
                    <input type="text" id="COMPANY" name="COMPANY" maxlength="40">
                </div>
                <div class="maintain-users-field">
                    <label for="POSTCODE">Postcode</label>
                    <input type="text" id="POSTCODE" name="POSTCODE" maxlength="10" required>
                </div>
                <div class="maintain-users-field">
                    <label for="CITY">City</label>
                    <input type="text" id="CITY" name="CITY" maxlength="100" required>
                </div>
                <div class="maintain-users-field">
                    <label for="ISACTIVE">Active</label>
                    <select id="ISACTIVE" name="ISACTIVE">
                        <option value="1" selected>Yes</option>
                        <option value="0">No</option>
                    </select>
                </div>
            </div>
            <div class="maintain-users-modal-actions">
                <button type="button" class="maintain-users-btn-secondary" id="maintainUsersCancelBtn">Cancel</button>
                <button type="submit" class="maintain-users-btn-primary">Create user</button>
            </div>
        </form>
    </div>
</div>

<div class="maintain-users-modal-backdrop" id="maintainUsersEditModal">
    <div class="maintain-users-modal">
        <h3 class="maintain-users-modal-title">Edit User</h3>
        <div class="maintain-users-modal-sub">Update email, alias, company, active status, or password.</div>
        <form method="POST" id="maintainUsersEditForm" action="">
            @csrf
            @method('PUT')
            <div class="maintain-users-form-grid">
                <div class="maintain-users-field full">
                    <label for="edit_EMAIL">Email</label>
                    <input type="email" id="edit_EMAIL" name="EMAIL" maxlength="50" required>
                </div>
                <div class="maintain-users-field">
                    <label for="edit_ALIAS">Alias</label>
                    <input type="text" id="edit_ALIAS" name="ALIAS" maxlength="50">
                </div>
                <div class="maintain-users-field">
                    <label for="edit_COMPANY">Company</label>
                    <input type="text" id="edit_COMPANY" name="COMPANY" maxlength="40">
                </div>
                <div class="maintain-users-field">
                    <label for="edit_ISACTIVE">Active</label>
                    <select id="edit_ISACTIVE" name="ISACTIVE">
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>
                </div>
                <div class="maintain-users-field full">
                    <label for="edit_PASSWORD">New password (leave blank to keep current)</label>
                    <input type="password" id="edit_PASSWORD" name="PASSWORD" placeholder="Optional">
                </div>
            </div>
            <div class="maintain-users-modal-actions">
                <button type="button" class="maintain-users-btn-secondary" id="maintainUsersEditCancelBtn">Cancel</button>
                <button type="submit" class="maintain-users-btn-primary">Update user</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const addBtn = document.getElementById('maintainUsersAddBtn');
            const modal = document.getElementById('maintainUsersModal');
            const cancelBtn = document.getElementById('maintainUsersCancelBtn');

            function openModal() {
                if (modal) modal.classList.add('is-open');
            }
            function closeModal() {
                if (modal) modal.classList.remove('is-open');
            }

            if (addBtn) addBtn.addEventListener('click', function (e) {
                e.preventDefault();
                openModal();
            });
            if (cancelBtn) cancelBtn.addEventListener('click', function (e) {
                e.preventDefault();
                closeModal();
            });
            if (modal) {
                modal.addEventListener('click', function (e) {
                    if (e.target === modal) {
                        closeModal();
                    }
                });
            }

            // Edit user modal
            const editModal = document.getElementById('maintainUsersEditModal');
            const editForm = document.getElementById('maintainUsersEditForm');
            const editCancelBtn = document.getElementById('maintainUsersEditCancelBtn');
            const updateUrlBase = '{{ url("admin/maintain-users") }}';
            function openEditModal(row) {
                if (!editModal || !editForm || !row) return;
                var userid = row.getAttribute('data-userid');
                var email = row.getAttribute('data-email') || '';
                var alias = row.getAttribute('data-alias') || '';
                var company = row.getAttribute('data-company') || '';
                var active = row.getAttribute('data-active') || '1';
                editForm.action = updateUrlBase + '/' + encodeURIComponent(userid);
                document.getElementById('edit_EMAIL').value = email;
                document.getElementById('edit_ALIAS').value = alias;
                document.getElementById('edit_COMPANY').value = company;
                document.getElementById('edit_ISACTIVE').value = active;
                document.getElementById('edit_PASSWORD').value = '';
                editModal.classList.add('is-open');
            }
            function closeEditModal() {
                if (editModal) editModal.classList.remove('is-open');
            }
            document.querySelectorAll('.maintain-users-edit-btn').forEach(function (btn) {
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    var row = btn.closest('tr.maintain-users-row');
                    if (row) openEditModal(row);
                });
            });
            if (editCancelBtn) editCancelBtn.addEventListener('click', function (e) {
                e.preventDefault();
                closeEditModal();
            });
            if (editModal) {
                editModal.addEventListener('click', function (e) {
                    if (e.target === editModal) closeEditModal();
                });
            }

            // Live search: top search box + per-column filters (all apply as you type)
            const searchInput = document.getElementById('maintainUsersSearchInput');
            const table = document.getElementById('maintainUsersTable');
            function applyTableFilter() {
                if (!table) return;
                const q = (searchInput && searchInput.value) ? (searchInput.value || '').toLowerCase().trim() : '';
                const filters = {};
                table.querySelectorAll('thead .inquiries-grid-filter').forEach(function (inp) {
                    const col = inp.getAttribute('data-col');
                    const val = (inp.value || '').toLowerCase().trim();
                    if (col && val) filters[col] = val;
                });
                table.querySelectorAll('tbody .maintain-users-row').forEach(function (row) {
                    const hay = (row.getAttribute('data-search') || '').toLowerCase();
                    const searchMatch = !q || hay.indexOf(q) !== -1;
                    let colMatch = true;
                    for (const col in filters) {
                        const cell = row.querySelector('td[data-col="' + col + '"]');
                        const cellText = (cell && cell.textContent) ? cell.textContent.toLowerCase().trim() : '';
                        if (cellText.indexOf(filters[col]) === -1) {
                            colMatch = false;
                            break;
                        }
                    }
                    row.style.display = (searchMatch && colMatch) ? '' : 'none';
                });
            }
            if (searchInput) searchInput.addEventListener('input', applyTableFilter);
            if (table) {
                table.querySelectorAll('thead .inquiries-grid-filter').forEach(function (inp) {
                    inp.addEventListener('input', applyTableFilter);
                });
            }
        });
    </script>
@endpush

