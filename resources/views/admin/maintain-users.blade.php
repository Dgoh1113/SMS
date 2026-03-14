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
            align-items: flex-end;
            gap: 16px;
            margin-bottom: 16px;
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
            flex-direction: column;
            gap: 8px;
            align-items: flex-end;
        }
        .maintain-users-actions {
            display: flex;
            justify-content: flex-end;
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
        .maintain-users-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
        }
        .maintain-users-search {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 6px 10px;
            border-radius: 999px;
            border: 1px solid #e5e7eb;
            background: #ffffff;
        }
        .maintain-users-search input {
            border: none;
            outline: none;
            font-size: 0.8rem;
            min-width: 160px;
        }
        .maintain-users-role-select {
            border-radius: 999px;
            border: 1px solid #e5e7eb;
            padding: 6px 10px;
            font-size: 0.8rem;
            background: #ffffff;
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
            <div class="maintain-users-actions">
                <button type="button" class="maintain-users-add-btn" id="maintainUsersAddBtn">
                    <span>+ Add User</span>
                </button>
            </div>
            <form method="GET" class="maintain-users-filters">
                <select name="role" class="maintain-users-role-select">
                    <option value="">All roles</option>
                    <option value="ADMIN" {{ $filterRole === 'ADMIN' ? 'selected' : '' }}>Admin</option>
                    <option value="MANAGER" {{ $filterRole === 'MANAGER' ? 'selected' : '' }}>Manager</option>
                    <option value="DEALER" {{ $filterRole === 'DEALER' ? 'selected' : '' }}>Dealer</option>
                </select>
                <div class="maintain-users-search">
                    <input type="text" id="maintainUsersSearchInput" name="q" value="{{ $search }}" placeholder="Search email, alias, or company...">
                </div>
                <button type="submit" class="maintain-users-btn-secondary" id="maintainUsersFilterBtn">Filter</button>
            </form>
        </div>
    </div>

    <div class="maintain-users-table-wrap">
        @if (session('error'))
            <div class="maintain-users-error">{{ session('error') }}</div>
        @elseif (session('success'))
            <div class="maintain-users-success">{{ session('success') }}</div>
        @endif
        @if (count($users) === 0)
            <div class="maintain-users-empty">No users found for the selected filters.</div>
        @else
            <div class="dashboard-table-wrapper">
            <table class="dashboard-table maintain-users-table" id="maintainUsersTable">
                <thead>
                <tr>
                    <th data-col="userid" class="inquiries-header-cell">
                        <span class="inquiries-header-label">USER ID</span>
                        <span class="inquiries-filter-wrap">
                            <input type="text" class="maintain-users-grid-filter" data-col="userid">
                            <i class="bi bi-search inquiries-filter-icon"></i>
                        </span>
                    </th>
                    <th data-col="email" class="inquiries-header-cell">
                        <span class="inquiries-header-label">EMAIL</span>
                        <span class="inquiries-filter-wrap">
                            <input type="text" class="maintain-users-grid-filter" data-col="email">
                            <i class="bi bi-search inquiries-filter-icon"></i>
                        </span>
                    </th>
                    <th data-col="role" class="inquiries-header-cell">
                        <span class="inquiries-header-label">ROLE</span>
                        <span class="inquiries-filter-wrap">
                            <input type="text" class="maintain-users-grid-filter" data-col="role">
                            <i class="bi bi-search inquiries-filter-icon"></i>
                        </span>
                    </th>
                    <th data-col="alias" class="inquiries-header-cell">
                        <span class="inquiries-header-label">ALIAS</span>
                        <span class="inquiries-filter-wrap">
                            <input type="text" class="maintain-users-grid-filter" data-col="alias">
                            <i class="bi bi-search inquiries-filter-icon"></i>
                        </span>
                    </th>
                    <th data-col="company" class="inquiries-header-cell">
                        <span class="inquiries-header-label">COMPANY</span>
                        <span class="inquiries-filter-wrap">
                            <input type="text" class="maintain-users-grid-filter" data-col="company">
                            <i class="bi bi-search inquiries-filter-icon"></i>
                        </span>
                    </th>
                    <th data-col="active" class="inquiries-header-cell">
                        <span class="inquiries-header-label">ACTIVE</span>
                        <span class="inquiries-filter-wrap">
                            <input type="text" class="maintain-users-grid-filter" data-col="active">
                            <i class="bi bi-search inquiries-filter-icon"></i>
                        </span>
                    </th>
                    <th data-col="lastlogin" class="inquiries-header-cell">
                        <span class="inquiries-header-label">LAST LOGIN</span>
                        <span class="inquiries-filter-wrap">
                            <input type="text" class="maintain-users-grid-filter" data-col="lastlogin">
                            <i class="bi bi-search inquiries-filter-icon"></i>
                        </span>
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
                        $searchHaystack = strtolower(
                            ($u['USERID'] ?? '') . ' ' .
                            ($u['EMAIL'] ?? '') . ' ' .
                            ($u['ALIAS'] ?? '') . ' ' .
                            ($u['COMPANY'] ?? '') . ' ' .
                            $roleUpper
                        );
                    @endphp
                    <tr class="maintain-users-row" data-search="{{ $searchHaystack }}">
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
                    <input type="email" id="EMAIL" name="EMAIL" required>
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
                    <input type="text" id="ALIAS" name="ALIAS">
                </div>
                <div class="maintain-users-field">
                    <label for="COMPANY">Company</label>
                    <input type="text" id="COMPANY" name="COMPANY">
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

            // Client-side search + column filters on table (like inquiries grid)
            const searchInput = document.getElementById('maintainUsersSearchInput');
            const filterBtn = document.getElementById('maintainUsersFilterBtn');
            const table = document.getElementById('maintainUsersTable');
            function applyMaintainUsersFilter() {
                if (!table || !searchInput) return;
                const q = (searchInput.value || '').toLowerCase().trim();
                const filters = {};
                table.querySelectorAll('.maintain-users-grid-filter').forEach(function (inp) {
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
            if (searchInput) {
                searchInput.addEventListener('input', applyMaintainUsersFilter);
                if (filterBtn) {
                    filterBtn.addEventListener('click', function (e) {
                        // prevent full reload when clicking Filter, use client-side filter instead
                        e.preventDefault();
                        applyMaintainUsersFilter();
                    });
                }
            }
        });
    </script>
@endpush

