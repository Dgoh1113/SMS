@extends('layouts.app')
@section('title', 'Maintain Users')
@push('styles')
    <style>
        .maintain-users-page {
            padding: 22px 24px;
            text-align: left;
        }
        .maintain-users-header {
            display: flex;
            justify-content: flex-end;
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
            font-size: 1.3rem;
            font-weight: 800;
            color: #312e81;
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
            gap: 10px;
        }
        .maintain-users-add-btn {
            border-radius: 999px;
            border: none;
            padding: 9px 16px;
            font-size: 0.85rem;
            font-weight: 700;
            background: #7f5af0;
            color: #ffffff;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            box-shadow: 0 10px 24px rgba(127, 90, 240, 0.24);
            transition: background 0.18s ease, box-shadow 0.18s ease;
        }
        .maintain-users-add-btn:hover {
            background: #6d4ad9;
            box-shadow: 0 14px 28px rgba(109, 74, 217, 0.28);
        }
        .maintain-users-batch-form {
            margin: 0;
        }
        .maintain-users-batch-btn {
            border-radius: 999px;
            border: 1px solid #e9dfff;
            padding: 9px 14px;
            font-size: 0.8rem;
            font-weight: 700;
            background: #f3ebff;
            color: #7f5af0;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            transition: background 0.18s ease, color 0.18s ease;
        }
        .maintain-users-batch-btn:hover {
            background: #e9dfff;
        }
        .maintain-users-sync-btn.is-syncing {
            opacity: 0.72;
            cursor: wait;
        }
        .maintain-users-sync-icon.spinning {
            animation: maintain-users-sync-spin 0.8s linear infinite;
        }
        @keyframes maintain-users-sync-spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        .maintain-users-table-wrap {
            margin-top: 12px;
            background: #ffffff;
            border: 1px solid #ede9fe;
            border-radius: 20px;
            padding: 18px 18px 12px;
            box-shadow: 0 10px 28px rgba(15, 23, 42, 0.05);
        }
        .maintain-users-pill-role {
            display: inline-flex;
            align-items: center;
            padding: 2px 6px;
            border-radius: 999px;
            font-size: 0.62rem;
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
        .maintain-users-pill-password {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 108px;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 0.74rem;
            font-weight: 700;
            letter-spacing: 0.02em;
            border: 1px solid transparent;
        }
        .maintain-users-pill-password.set {
            background: rgba(79, 70, 229, 0.1);
            color: #4f46e5;
            border-color: rgba(165, 180, 252, 0.65);
        }
        .maintain-users-pill-password.empty {
            background: rgba(148, 163, 184, 0.12);
            color: #64748b;
            border-color: rgba(203, 213, 225, 0.72);
        }
        .maintain-users-temp-password {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 108px;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 0.74rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            font-family: Consolas, 'Courier New', monospace;
            color: #4338ca;
            background: rgba(79, 70, 229, 0.1);
            border: 1px solid rgba(165, 180, 252, 0.65);
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
            border-radius: 18px;
            padding: 22px 24px 20px;
            width: 100%;
            max-width: 560px;
            box-shadow: 0 20px 48px rgba(15, 23, 42, 0.14);
            border: 1px solid #ddd6fe;
        }
        .maintain-users-modal-title {
            font-size: 1rem;
            font-weight: 800;
            margin: 0 0 4px 0;
            color: #312e81;
        }
        .maintain-users-modal-sub {
            font-size: 0.78rem;
            color: #64748b;
            margin-bottom: 14px;
        }
        .maintain-users-form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px 14px;
            margin-bottom: 14px;
        }
        .maintain-users-form-grid-nested {
            margin-bottom: 0;
            gap: 12px 14px;
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
            color: #7c3aed;
            margin-bottom: 5px;
        }
        .maintain-users-field input,
        .maintain-users-field select {
            width: 100%;
            border-radius: 12px;
            border: 1px solid #c4b5fd;
            padding: 9px 11px;
            font-size: 0.8rem;
            background: #ffffff;
            color: #1e293b;
            transition: border-color 0.18s ease, box-shadow 0.18s ease;
        }
        .maintain-users-field input:focus,
        .maintain-users-field select:focus {
            outline: none;
            border-color: #8b5cf6;
            box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.14);
            background: #ffffff;
        }
        .maintain-users-field input:disabled,
        .maintain-users-field select:disabled {
            background: rgba(241, 245, 249, 0.92);
            color: #94a3b8;
            cursor: not-allowed;
        }
        .maintain-users-field.is-hidden {
            display: none;
        }
        .maintain-users-dealer-section {
            border-top: 1px solid #ede9fe;
            padding-top: 14px;
        }
        .maintain-users-dealer-section.is-hidden {
            display: none;
        }
        .maintain-users-location-presets {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 10px;
        }
        .maintain-users-location-option {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 10px;
            border: 1px solid #ddd6fe;
            border-radius: 999px;
            background: #faf7ff;
            color: #6b7280;
            font-size: 0.72rem;
            font-weight: 600;
            cursor: pointer;
            user-select: none;
        }
        .maintain-users-location-option input {
            width: 12px;
            height: 12px;
            margin: 0;
            accent-color: #7f5af0;
        }
        .maintain-users-location-option.is-active {
            border-color: #c4b5fd;
            background: #f3ebff;
            color: #6d4ad9;
        }
        .maintain-users-readonly {
            background: rgba(245, 243, 255, 0.95) !important;
            color: #4c1d95 !important;
            font-weight: 700;
        }
        .maintain-users-reset-option {
            display: flex;
            align-items: center;
            gap: 8px;
            border: 1px solid #ddd6fe;
            border-radius: 14px;
            padding: 10px 12px;
            background: #faf7ff;
        }
        .maintain-users-reset-option input {
            width: 14px;
            height: 14px;
            margin: 0;
        }
        .maintain-users-reset-option-text {
            font-size: 0.8rem;
            color: #334155;
            font-weight: 600;
        }
        .maintain-users-modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            margin-top: 8px;
        }
        .maintain-users-btn-secondary,
        .maintain-users-btn-soft,
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
            border-color: #ddd6fe;
            color: #6b7280;
        }
        .maintain-users-btn-secondary:hover {
            background: #f8f4ff;
        }
        .maintain-users-btn-soft {
            background: #f3ebff;
            border-color: #e9dfff;
            color: #7f5af0;
        }
        .maintain-users-btn-soft:hover {
            background: #e9dfff;
        }
        .maintain-users-btn-primary {
            background: #7f5af0;
            color: #ffffff;
            box-shadow: 0 10px 24px rgba(127, 90, 240, 0.22);
        }
        .maintain-users-btn-primary:hover {
            background: #6d4ad9;
        }
        .maintain-users-error {
            margin-top: 8px;
            margin-bottom: 10px;
            font-size: 0.8rem;
            color: #b91c1c;
            padding: 12px 14px;
            border-radius: 14px;
            border: 1px solid rgba(252, 165, 165, 0.7);
            background: rgba(254, 242, 242, 0.95);
        }
        .maintain-users-success {
            margin-top: 8px;
            margin-bottom: 10px;
            font-size: 0.8rem;
            color: #15803d;
            padding: 12px 14px;
            border-radius: 14px;
            border: 1px solid rgba(134, 239, 172, 0.72);
            background: rgba(240, 253, 244, 0.96);
            transition: opacity 0.25s ease;
        }
        .maintain-users-success.is-hiding {
            opacity: 0;
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
        .maintain-users-action-cell {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
        }
        .maintain-users-inline-form {
            margin: 0;
        }
        .maintain-users-edit-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 36px;
            min-height: 36px;
            border: none;
            background: transparent;
            color: #4b5563;
            padding: 6px;
            border-radius: 6px;
            font-size: 1rem;
            line-height: 1;
            cursor: pointer;
            text-decoration: none;
        }
        .maintain-users-edit-btn:hover {
            background: rgba(139, 92, 246, 0.1);
            color: #6d28d9;
        }
        .maintain-users-edit-btn .bi {
            font-size: 1.35rem;
        }
        .maintain-users-temp-send-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 36px;
            min-height: 36px;
            border: none;
            background: transparent;
            color: #4b5563;
            padding: 6px;
            border-radius: 6px;
            font-size: 1rem;
            line-height: 1;
            cursor: pointer;
        }
        .maintain-users-temp-send-btn:hover {
            background: rgba(139, 92, 246, 0.1);
            color: #6d28d9;
        }
        .maintain-users-temp-send-btn .bi {
            font-size: 1.2rem;
        }
        .maintain-users-temp-help {
            margin-top: 4px;
            font-size: 0.72rem;
            color: #64748b;
        }
        .maintain-users-batch-summary {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 12px;
            font-size: 0.8rem;
            color: #475569;
        }
        .maintain-users-batch-toggle {
            border: none;
            background: transparent;
            color: #4f46e5;
            font-size: 0.78rem;
            font-weight: 700;
            cursor: pointer;
            padding: 0;
        }
        .maintain-users-batch-list {
            max-height: 320px;
            overflow-y: auto;
            border: 1px solid #ddd6fe;
            border-radius: 16px;
            background: #faf7ff;
            padding: 8px;
        }
        .maintain-users-batch-item {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 10px;
            background: #ffffff;
        }
        .maintain-users-batch-item + .maintain-users-batch-item {
            margin-top: 8px;
        }
        .maintain-users-batch-item input[type="checkbox"] {
            margin-top: 2px;
        }
        .maintain-users-batch-item-main {
            min-width: 0;
            flex: 1;
        }
        .maintain-users-batch-item-name {
            font-size: 0.85rem;
            font-weight: 700;
            color: #0f172a;
        }
        .maintain-users-batch-item-meta {
            margin-top: 2px;
            font-size: 0.76rem;
            color: #64748b;
        }
        .maintain-users-batch-empty {
            padding: 24px 12px;
            text-align: center;
            font-size: 0.82rem;
            color: #64748b;
        }
        .maintain-users-table-search-wrap {
            display: flex;
            align-items: center;
            background: #ffffff;
            border: 1px solid #ddd6fe;
            border-radius: 12px;
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
        <div class="maintain-users-header-right">
            <div class="maintain-users-actions">
                <button type="button" class="maintain-users-batch-btn maintain-users-sync-btn" id="maintainUsersSyncBtn">
                    <i class="bi bi-arrow-repeat maintain-users-sync-icon"></i>
                    <span>Sync</span>
                </button>
                <div class="maintain-users-batch-form">
                    <button type="button" class="maintain-users-batch-btn" id="maintainUsersBatchOpenBtn">
                        <i class="bi bi-envelope"></i>
                        <span>Send Temp Passwords</span>
                    </button>
                </div>
                <button type="button" class="maintain-users-add-btn" id="maintainUsersAddBtn">
                    <span>+ Add User</span>
                </button>
            </div>
        </div>
    </div>

    <div class="maintain-users-table-wrap">
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
                            <input type="text" class="inquiries-grid-filter" data-col="userid" placeholder="">
                            <i class="bi bi-search inquiries-filter-icon"></i>
                        </span>
                    </th>
                    <th data-col="email" class="inquiries-header-cell">
                        <span class="inquiries-header-label">EMAIL</span>
                        <span class="inquiries-filter-wrap">
                            <input type="text" class="inquiries-grid-filter" data-col="email" placeholder="">
                            <i class="bi bi-search inquiries-filter-icon"></i>
                        </span>
                    </th>
                    <th data-col="role" class="inquiries-header-cell">
                        <span class="inquiries-header-label">ROLE</span>
                        <span class="inquiries-filter-wrap">
                            <input type="text" class="inquiries-grid-filter" data-col="role" placeholder="">
                            <i class="bi bi-search inquiries-filter-icon"></i>
                        </span>
                    </th>
                    <th data-col="alias" class="inquiries-header-cell">
                        <span class="inquiries-header-label">ALIAS</span>
                        <span class="inquiries-filter-wrap">
                            <input type="text" class="inquiries-grid-filter" data-col="alias" placeholder="">
                            <i class="bi bi-search inquiries-filter-icon"></i>
                        </span>
                    </th>
                    <th data-col="company" class="inquiries-header-cell">
                        <span class="inquiries-header-label">COMPANY</span>
                        <span class="inquiries-filter-wrap">
                            <input type="text" class="inquiries-grid-filter" data-col="company" placeholder="">
                            <i class="bi bi-search inquiries-filter-icon"></i>
                        </span>
                    </th>
                    <th data-col="password" class="inquiries-header-cell">
                        <span class="inquiries-header-label">PASSWORD</span>
                        <span class="inquiries-filter-wrap">
                            <input type="text" class="inquiries-grid-filter" data-col="password" placeholder="">
                            <i class="bi bi-search inquiries-filter-icon"></i>
                        </span>
                    </th>
                    <th data-col="active" class="inquiries-header-cell">
                        <span class="inquiries-header-label">ACTIVE</span>
                        <span class="inquiries-filter-wrap">
                            <input type="text" class="inquiries-grid-filter" data-col="active" placeholder="">
                            <i class="bi bi-search inquiries-filter-icon"></i>
                        </span>
                    </th>
                    <th data-col="lastlogin" class="inquiries-header-cell">
                        <span class="inquiries-header-label">LAST LOGIN</span>
                        <span class="inquiries-filter-wrap">
                            <input type="text" class="inquiries-grid-filter" data-col="lastlogin" placeholder="">
                            <i class="bi bi-search inquiries-filter-icon"></i>
                        </span>
                    </th>
                    <th class="maintain-users-col-action inquiries-header-cell">
                        <span class="inquiries-header-label">ACTION</span>
                        <button type="button" class="inquiries-filter-clear" id="maintainUsersClearFilters">Clear filters</button>
                    </th>
                </tr>
                </thead>
                <tbody id="maintainUsersTableBody">
                @include('admin.partials.maintain_users_rows', ['users' => $users])
                </tbody>
            </table>
            </div>
        @endif
    </div>
</div>

<div class="maintain-users-modal-backdrop" id="maintainUsersModal">
    <div class="maintain-users-modal">
        <h3 class="maintain-users-modal-title">Add User</h3>
        <div class="maintain-users-modal-sub">Create a new admin, manager, or dealer account with a temporary password.</div>
        <form method="POST" action="{{ route('admin.maintain-users.store') }}">
            @csrf
            <div class="maintain-users-form-grid">
                <div class="maintain-users-field full">
                    <label for="EMAIL">Email</label>
                    <input type="email" id="EMAIL" name="EMAIL" maxlength="50" required>
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
                    <label for="ISACTIVE">Active</label>
                    <select id="ISACTIVE" name="ISACTIVE">
                        <option value="1" selected>Yes</option>
                        <option value="0">No</option>
                    </select>
                </div>
                <div class="maintain-users-field">
                    <label for="COMPANY">Company Name</label>
                    <input type="text" id="COMPANY" name="COMPANY" maxlength="40">
                </div>
                <div class="maintain-users-field">
                    <label for="ALIAS">Alias</label>
                    <input type="text" id="ALIAS" name="ALIAS" maxlength="50">
                </div>
                <div class="maintain-users-dealer-section full" id="maintainUsersDealerFields">
                    <div class="maintain-users-location-presets">
                        <label class="maintain-users-location-option" for="locationPresetSetapak">
                            <input type="checkbox" id="locationPresetSetapak" data-location-preset data-postcode="53300" data-city="Setapak">
                            <span>Setapak</span>
                        </label>
                        <label class="maintain-users-location-option" for="locationPresetSetiaAlam">
                            <input type="checkbox" id="locationPresetSetiaAlam" data-location-preset data-postcode="40170" data-city="Setia Alam">
                            <span>Setia Alam</span>
                        </label>
                    </div>
                    <div class="maintain-users-form-grid maintain-users-form-grid-nested">
                        <div class="maintain-users-field" data-dealer-field>
                            <label for="POSTCODE">Postcode</label>
                            <input type="text" id="POSTCODE" name="POSTCODE" maxlength="10" inputmode="numeric">
                        </div>
                        <div class="maintain-users-field" data-dealer-field>
                            <label for="CITY">City</label>
                            <input type="text" id="CITY" name="CITY" maxlength="100">
                        </div>
                    </div>
                </div>
                <div class="maintain-users-field full">
                    <label for="PASSWORD">Temporary Password</label>
                    <input type="text" id="PASSWORD" name="PASSWORD" maxlength="255" readonly>
                </div>
            </div>
            <div class="maintain-users-modal-actions">
                <button type="button" class="maintain-users-btn-secondary" id="maintainUsersCancelBtn">Cancel</button>
                <button type="submit" name="CREATE_ACTION" value="create" class="maintain-users-btn-soft">Create user</button>
                <button type="submit" name="CREATE_ACTION" value="create_email" class="maintain-users-btn-primary">Create &amp; Email</button>
            </div>
        </form>
    </div>
</div>

<div class="maintain-users-modal-backdrop" id="maintainUsersEditModal">
    <div class="maintain-users-modal">
        <h3 class="maintain-users-modal-title">Edit User</h3>
        <div class="maintain-users-modal-sub">Update account details and optionally send a reset link.</div>
        <form method="POST" id="maintainUsersEditForm" action="">
            @csrf
            @method('PUT')
            <div class="maintain-users-form-grid">
                <div class="maintain-users-field full">
                    <label for="edit_EMAIL">Email</label>
                    <input type="email" id="edit_EMAIL" name="EMAIL" maxlength="50" required>
                </div>
                <div class="maintain-users-field">
                    <label for="edit_SYSTEMROLE_LABEL">Role</label>
                    <input type="text" id="edit_SYSTEMROLE_LABEL" class="maintain-users-readonly" readonly>
                </div>
                <div class="maintain-users-field">
                    <label for="edit_ISACTIVE">Active</label>
                    <select id="edit_ISACTIVE" name="ISACTIVE">
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>
                </div>
                <div class="maintain-users-field">
                    <label for="edit_COMPANY">Company Name</label>
                    <input type="text" id="edit_COMPANY" name="COMPANY" maxlength="40">
                </div>
                <div class="maintain-users-field">
                    <label for="edit_ALIAS">Alias</label>
                    <input type="text" id="edit_ALIAS" name="ALIAS" maxlength="50">
                </div>
                <div class="maintain-users-dealer-section full" id="maintainUsersEditDealerFields">
                    <div class="maintain-users-form-grid maintain-users-form-grid-nested">
                        <div class="maintain-users-field" data-dealer-field>
                            <label for="edit_POSTCODE">Postcode</label>
                            <input type="text" id="edit_POSTCODE" name="POSTCODE" maxlength="10" inputmode="numeric">
                        </div>
                        <div class="maintain-users-field" data-dealer-field>
                            <label for="edit_CITY">City</label>
                            <input type="text" id="edit_CITY" name="CITY" maxlength="100">
                        </div>
                    </div>
                </div>
                <div class="maintain-users-field full">
                    <label for="edit_SEND_RESET_LINK">Send password reset link</label>
                    <label class="maintain-users-reset-option" for="edit_SEND_RESET_LINK">
                        <input type="checkbox" id="edit_SEND_RESET_LINK" name="SEND_RESET_LINK" value="1">
                        <span class="maintain-users-reset-option-text">Email a secure reset link after update</span>
                    </label>
                </div>
            </div>
            <div class="maintain-users-modal-actions">
                <button type="button" class="maintain-users-btn-secondary" id="maintainUsersEditCancelBtn">Cancel</button>
                <button type="submit" class="maintain-users-btn-primary">Update user</button>
            </div>
        </form>
    </div>
</div>

<div class="maintain-users-modal-backdrop" id="maintainUsersBatchModal">
    <div class="maintain-users-modal">
        <h3 class="maintain-users-modal-title">Send Temporary Passwords</h3>
        <form method="POST" action="{{ route('admin.maintain-users.send-temp-passwords') }}" id="maintainUsersBatchForm">
            @csrf
            <div class="maintain-users-batch-summary">
                <span id="maintainUsersBatchCount">{{ count($batchEligibleUsers) }} eligible user(s)</span>
                <button type="button" class="maintain-users-batch-toggle" id="maintainUsersBatchToggleAll" {{ count($batchEligibleUsers) > 0 ? '' : 'hidden' }}>Uncheck all</button>
            </div>
            <div class="maintain-users-batch-list" id="maintainUsersBatchList">
                @include('admin.partials.maintain_users_batch_items', ['batchEligibleUsers' => $batchEligibleUsers])
            </div>
            <div class="maintain-users-modal-actions">
                <button type="button" class="maintain-users-btn-secondary" id="maintainUsersBatchCancelBtn">Cancel</button>
                <button type="submit" class="maintain-users-btn-primary" id="maintainUsersBatchSubmitBtn" {{ count($batchEligibleUsers) === 0 ? 'disabled' : '' }}>Send selected</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const MAINTAIN_USERS_AUTO_SYNC_MS = 15 * 60 * 1000;
            const addBtn = document.getElementById('maintainUsersAddBtn');
            const syncBtn = document.getElementById('maintainUsersSyncBtn');
            const modal = document.getElementById('maintainUsersModal');
            const cancelBtn = document.getElementById('maintainUsersCancelBtn');
            const tempPasswordInput = document.getElementById('PASSWORD');
            const addForm = modal ? modal.querySelector('form') : null;
            const roleSelect = document.getElementById('SYSTEMROLE');
            const aliasInput = document.getElementById('ALIAS');
            const companyInput = document.getElementById('COMPANY');
            const dealerFieldsSection = document.getElementById('maintainUsersDealerFields');
            const postcodeInput = document.getElementById('POSTCODE');
            const cityInput = document.getElementById('CITY');
            const locationPresetInputs = Array.from(document.querySelectorAll('[data-location-preset]'));
            const ESTREAM_COMPANY = 'E Stream Sdn Bhd';

            function generateTemporaryPassword(length) {
                const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789';
                let password = '';
                for (let i = 0; i < length; i += 1) {
                    password += chars.charAt(Math.floor(Math.random() * chars.length));
                }
                return password;
            }

            function setDealerSectionState(section, isDealer) {
                if (!section) return;
                section.classList.toggle('is-hidden', !isDealer);
                section.querySelectorAll('[data-dealer-field]').forEach(function (field) {
                    field.classList.toggle('is-hidden', !isDealer);
                    const input = field.querySelector('input, select, textarea');
                    if (!input) return;
                    input.disabled = !isDealer;
                    input.required = isDealer;
                });
            }

            function setCompanyFieldState(input, isDealer) {
                if (!input) return;
                if (isDealer) {
                    input.readOnly = false;
                    input.classList.remove('maintain-users-readonly');
                    input.required = true;
                    input.value = input.dataset.dealerDraft || '';
                    return;
                }

                const currentValue = (input.value || '').trim();
                if (currentValue !== '' && currentValue !== ESTREAM_COMPANY) {
                    input.dataset.dealerDraft = currentValue;
                }

                input.value = ESTREAM_COMPANY;
                input.readOnly = true;
                input.required = false;
                input.classList.add('maintain-users-readonly');
            }

            function syncAddRoleState() {
                const isDealer = !roleSelect || roleSelect.value === 'DEALER';
                setDealerSectionState(dealerFieldsSection, isDealer);
                setCompanyFieldState(companyInput, isDealer);
                if (aliasInput) {
                    aliasInput.required = isDealer;
                }
            }

            function updateLocationPresetVisuals() {
                locationPresetInputs.forEach(function (input) {
                    var label = input.closest('.maintain-users-location-option');
                    if (!label) return;
                    label.classList.toggle('is-active', input.checked);
                });
            }

            function clearLocationPresets() {
                locationPresetInputs.forEach(function (input) {
                    input.checked = false;
                });
                updateLocationPresetVisuals();
            }

            locationPresetInputs.forEach(function (input) {
                input.addEventListener('change', function () {
                    if (input.checked) {
                        locationPresetInputs.forEach(function (other) {
                            if (other !== input) {
                                other.checked = false;
                            }
                        });
                        if (postcodeInput) {
                            postcodeInput.value = input.getAttribute('data-postcode') || '';
                        }
                        if (cityInput) {
                            cityInput.value = input.getAttribute('data-city') || '';
                        }
                    }
                    updateLocationPresetVisuals();
                });
            });

            function openModal() {
                if (addForm) {
                    addForm.reset();
                }
                if (tempPasswordInput) {
                    tempPasswordInput.value = generateTemporaryPassword(10);
                }
                if (roleSelect) {
                    roleSelect.value = 'DEALER';
                }
                if (companyInput) {
                    companyInput.dataset.dealerDraft = '';
                }
                clearLocationPresets();
                syncAddRoleState();
                if (modal) modal.classList.add('is-open');
            }
            function closeModal() {
                if (modal) modal.classList.remove('is-open');
            }

            if (addBtn) addBtn.addEventListener('click', function (e) {
                e.preventDefault();
                openModal();
            });
            function triggerMaintainUsersSync() {
                if (!syncBtn || !tableBody) return;
                if (syncBtn.classList.contains('is-syncing')) return;
                syncBtn.classList.add('is-syncing');
                const syncIcon = syncBtn.querySelector('.maintain-users-sync-icon');
                if (syncIcon) {
                    syncIcon.classList.add('spinning');
                }
                syncBtn.disabled = true;
                fetch(syncUrl + '?partial=1&_=' + Date.now(), {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                })
                    .then(function (response) {
                        if (!response.ok) {
                            throw new Error('Unable to refresh users.');
                        }
                        return response.json();
                    })
                    .then(function (payload) {
                        tableBody.innerHTML = typeof payload.rows_html === 'string' ? payload.rows_html : '';
                        if (batchList) {
                            batchList.innerHTML = typeof payload.batch_html === 'string' ? payload.batch_html : '';
                        }
                        if (batchCount) {
                            batchCount.textContent = (payload.batch_count || 0) + ' eligible user(s)';
                        }
                        if (batchToggleAllBtn) {
                            batchToggleAllBtn.hidden = (payload.batch_count || 0) === 0;
                        }
                        updateBatchSelectionState();
                        applyTableFilter();
                    })
                    .catch(function (error) {
                        console.error(error);
                    })
                    .finally(function () {
                        syncBtn.disabled = false;
                        syncBtn.classList.remove('is-syncing');
                        const syncIcon = syncBtn.querySelector('.maintain-users-sync-icon');
                        if (syncIcon) {
                            syncIcon.classList.remove('spinning');
                        }
                    });
            }
            if (syncBtn) {
                syncBtn.addEventListener('click', function () {
                    triggerMaintainUsersSync();
                });
                window.setInterval(function () {
                    triggerMaintainUsersSync();
                }, MAINTAIN_USERS_AUTO_SYNC_MS);
            }
            if (roleSelect) {
                roleSelect.addEventListener('change', syncAddRoleState);
            }
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
            const batchOpenBtn = document.getElementById('maintainUsersBatchOpenBtn');
            const batchModal = document.getElementById('maintainUsersBatchModal');
            const batchCancelBtn = document.getElementById('maintainUsersBatchCancelBtn');
            const batchToggleAllBtn = document.getElementById('maintainUsersBatchToggleAll');
            const batchForm = document.getElementById('maintainUsersBatchForm');
            const batchSubmitBtn = document.getElementById('maintainUsersBatchSubmitBtn');
            const batchCount = document.getElementById('maintainUsersBatchCount');
            const batchList = document.getElementById('maintainUsersBatchList');
            const editRoleLabel = document.getElementById('edit_SYSTEMROLE_LABEL');
            const editAliasInput = document.getElementById('edit_ALIAS');
            const editCompanyInput = document.getElementById('edit_COMPANY');
            const editPostcodeInput = document.getElementById('edit_POSTCODE');
            const editCityInput = document.getElementById('edit_CITY');
            const editDealerFieldsSection = document.getElementById('maintainUsersEditDealerFields');
            const table = document.getElementById('maintainUsersTable');
            const tableBody = document.getElementById('maintainUsersTableBody');
            const syncUrl = '{{ route('admin.maintain-users') }}';

            function formatRoleLabel(role) {
                if (!role) return '';
                return role.charAt(0) + role.slice(1).toLowerCase();
            }

            function getBatchCheckboxes() {
                return batchForm ? Array.from(batchForm.querySelectorAll('input[name="USERIDS[]"]')) : [];
            }

            function updateBatchSelectionState() {
                const boxes = getBatchCheckboxes();
                const checkedCount = boxes.filter(function (box) { return box.checked; }).length;
                if (batchSubmitBtn) {
                    batchSubmitBtn.disabled = checkedCount === 0;
                }
                if (batchToggleAllBtn) {
                    batchToggleAllBtn.textContent = checkedCount === boxes.length && boxes.length > 0 ? 'Uncheck all' : 'Check all';
                }
            }

            function openBatchModal() {
                if (!batchModal) return;
                updateBatchSelectionState();
                batchModal.classList.add('is-open');
            }

            function closeBatchModal() {
                if (batchModal) {
                    batchModal.classList.remove('is-open');
                }
            }

            function openEditModal(row) {
                if (!editModal || !editForm || !row) return;
                var userid = row.getAttribute('data-userid');
                var email = row.getAttribute('data-email') || '';
                var alias = row.getAttribute('data-alias') || '';
                var company = row.getAttribute('data-company') || '';
                var postcode = row.getAttribute('data-postcode') || '';
                var city = row.getAttribute('data-city') || '';
                var role = row.getAttribute('data-role') || '';
                var active = row.getAttribute('data-active') || '1';
                editForm.action = updateUrlBase + '/' + encodeURIComponent(userid);
                document.getElementById('edit_EMAIL').value = email;
                if (editRoleLabel) {
                    editRoleLabel.value = formatRoleLabel(role);
                }
                if (editAliasInput) {
                    editAliasInput.value = alias;
                    editAliasInput.required = role === 'DEALER';
                }
                if (editCompanyInput) {
                    editCompanyInput.dataset.dealerDraft = company;
                }
                if (editPostcodeInput) {
                    editPostcodeInput.value = postcode;
                }
                if (editCityInput) {
                    editCityInput.value = city;
                }
                setDealerSectionState(editDealerFieldsSection, role === 'DEALER');
                setCompanyFieldState(editCompanyInput, role === 'DEALER');
                document.getElementById('edit_ISACTIVE').value = active;
                document.getElementById('edit_SEND_RESET_LINK').checked = false;
                editModal.classList.add('is-open');
            }
            function closeEditModal() {
                if (editModal) editModal.classList.remove('is-open');
            }
            if (table) {
                table.addEventListener('click', function (e) {
                    var btn = e.target.closest('.maintain-users-edit-btn');
                    if (!btn) return;
                    e.preventDefault();
                    var row = btn.closest('tr.maintain-users-row');
                    if (row) openEditModal(row);
                });
            }
            if (editCancelBtn) editCancelBtn.addEventListener('click', function (e) {
                e.preventDefault();
                closeEditModal();
            });
            if (editModal) {
                editModal.addEventListener('click', function (e) {
                    if (e.target === editModal) closeEditModal();
                });
            }
            if (batchOpenBtn) {
                batchOpenBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    openBatchModal();
                });
            }
            if (batchCancelBtn) {
                batchCancelBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    closeBatchModal();
                });
            }
            if (batchModal) {
                batchModal.addEventListener('click', function (e) {
                    if (e.target === batchModal) {
                        closeBatchModal();
                    }
                });
            }
            if (batchToggleAllBtn) {
                batchToggleAllBtn.addEventListener('click', function () {
                    const boxes = getBatchCheckboxes();
                    const shouldCheckAll = boxes.some(function (box) { return !box.checked; });
                    boxes.forEach(function (box) {
                        box.checked = shouldCheckAll;
                    });
                    updateBatchSelectionState();
                });
            }
            if (batchForm) {
                batchForm.addEventListener('change', function (e) {
                    if (e.target && e.target.matches('input[name="USERIDS[]"]')) {
                        updateBatchSelectionState();
                    }
                });
            }
            updateBatchSelectionState();
            updateLocationPresetVisuals();
            syncAddRoleState();

            // Live search: top search box + per-column filters (all apply as you type)
            function applyTableFilter() {
                if (!table) return;
                const filters = {};
                table.querySelectorAll('thead .inquiries-grid-filter').forEach(function (inp) {
                    const col = inp.getAttribute('data-col');
                    const val = (inp.value || '').toLowerCase().trim();
                    if (col && val) filters[col] = val;
                });
                table.querySelectorAll('tbody .maintain-users-row').forEach(function (row) {
                    let colMatch = true;
                    for (const col in filters) {
                        const cell = row.querySelector('td[data-col="' + col + '"]');
                        const cellText = (cell && cell.textContent) ? cell.textContent.toLowerCase().trim() : '';
                        if (cellText.indexOf(filters[col]) === -1) {
                            colMatch = false;
                            break;
                        }
                    }
                    row.style.display = colMatch ? '' : 'none';
                });
            }
            if (table) {
                table.querySelectorAll('thead .inquiries-grid-filter').forEach(function (inp) {
                    inp.addEventListener('input', applyTableFilter);
                });
            }

            const clearFiltersBtn = document.getElementById('maintainUsersClearFilters');
            if (clearFiltersBtn) {
                clearFiltersBtn.addEventListener('click', function () {
                    if (table) {
                        table.querySelectorAll('thead .inquiries-grid-filter').forEach(function (inp) {
                            inp.value = '';
                        });
                    }
                    applyTableFilter();
                });
            }

            const successFlash = document.querySelector('.maintain-users-success');
            if (successFlash) {
                window.setTimeout(function () {
                    successFlash.classList.add('is-hiding');
                    window.setTimeout(function () {
                        successFlash.remove();
                    }, 250);
                }, 3000);
            }
        });
    </script>
@endpush


