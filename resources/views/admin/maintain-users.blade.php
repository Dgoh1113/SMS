@extends('layouts.app')
@section('title', 'Maintain Users')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/admin-maintain-users.css') }}?v=20260421-03">
@endpush

@section('content')
<div class="maintain-users-page">
    <div class="maintain-users-table-wrap">
        <div class="maintain-users-header">
            <div class="maintain-users-header-right">
                <div class="maintain-users-actions">
                    <div class="maintain-users-batch-form">
                        <button type="button" class="maintain-users-batch-btn" id="maintainUsersBatchOpenBtn">
                            <i class="bi bi-envelope"></i>
                            <span>Send Passkey Setup Links</span>
                        </button>
                    </div>
                    <button type="button" class="maintain-users-add-btn" id="maintainUsersAddBtn">
                        <span>+ Add User</span>
                    </button>
                </div>
            </div>
        </div>

        @if (count($users) === 0)
            <div class="maintain-users-empty">No users found.</div>
        @else
            @php
                $maintainUsersTotal = count($users);
                $maintainUsersPerPage = 10;
                $maintainUsersLastPage = $maintainUsersTotal > 0 ? (int) ceil($maintainUsersTotal / $maintainUsersPerPage) : 1;
                $maintainUsersTo = $maintainUsersTotal === 0 ? 0 : min($maintainUsersPerPage, $maintainUsersTotal);
            @endphp
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
                    <th data-col="passkey" class="inquiries-header-cell">
                        <span class="inquiries-header-label">PASSKEY SETUP</span>
                        <span class="inquiries-filter-wrap">
                            <input type="text" class="inquiries-grid-filter" data-col="passkey" placeholder="">
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
            @include('partials.common_pagination', [
                'id' => 'maintainUsersPagination',
                'total' => $maintainUsersTotal,
                'perPage' => $maintainUsersPerPage,
                'currentPage' => 1,
                'lastPage' => $maintainUsersLastPage,
                'containerClass' => 'maintain-users-pagination',
                'pageNumbersId' => 'maintainUsersPageNumbers'
            ])
        @endif
    </div>
</div>

<div class="maintain-users-modal-backdrop" id="maintainUsersModal">
    <div class="maintain-users-modal">
        <h3 class="maintain-users-modal-title">Add User</h3>
        <div class="maintain-users-modal-sub">Create a new admin, manager, or dealer account and optionally email a passkey setup link.</div>
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

            </div>
            <div class="maintain-users-modal-actions">
                <button type="button" class="maintain-users-btn-secondary" id="maintainUsersCancelBtn">Cancel</button>
                <button type="submit" name="CREATE_ACTION" value="create" class="maintain-users-btn-soft">Create user</button>
                <button type="submit" name="CREATE_ACTION" value="create_email" class="maintain-users-btn-primary">Create &amp; Email Setup Link</button>
            </div>
        </form>
    </div>
</div>

<div class="maintain-users-modal-backdrop" id="maintainUsersEditModal">
    <div class="maintain-users-modal">
        <h3 class="maintain-users-modal-title">Edit User</h3>
        <div class="maintain-users-modal-sub">Update account details and optionally send a passkey setup link.</div>
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
                    <label for="edit_SEND_PASSKEY_SETUP_LINK">Send passkey setup link</label>
                    <label class="maintain-users-reset-option" for="edit_SEND_PASSKEY_SETUP_LINK">
                        <input type="checkbox" id="edit_SEND_PASSKEY_SETUP_LINK" name="SEND_PASSKEY_SETUP_LINK" value="1">
                        <span class="maintain-users-reset-option-text">Email a secure passkey setup link after update</span>
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
        <h3 class="maintain-users-modal-title">Send Passkey Setup Links</h3>
        <form method="POST" action="{{ route('admin.maintain-users.send-passkey-setup-links') }}" id="maintainUsersBatchForm">
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
            const addBtn = document.getElementById('maintainUsersAddBtn');
            const modal = document.getElementById('maintainUsersModal');
            const cancelBtn = document.getElementById('maintainUsersCancelBtn');
            const addForm = modal ? modal.querySelector('form') : null;
            const roleSelect = document.getElementById('SYSTEMROLE');
            const aliasInput = document.getElementById('ALIAS');
            const companyInput = document.getElementById('COMPANY');
            const dealerFieldsSection = document.getElementById('maintainUsersDealerFields');
            const postcodeInput = document.getElementById('POSTCODE');
            const cityInput = document.getElementById('CITY');
            const locationPresetInputs = Array.from(document.querySelectorAll('[data-location-preset]'));
            const ESTREAM_COMPANY = 'E Stream Sdn Bhd';


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
            const pagination = document.getElementById('maintainUsersPagination');

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
                document.getElementById('edit_SEND_PASSKEY_SETUP_LINK').checked = false;
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

            window.maintainUsersPaginationState = window.maintainUsersPaginationState || { currentPage: 1, perPage: 10 };
            const maintainUsersWidthMeasurer = document.createElement('span');
            maintainUsersWidthMeasurer.style.position = 'absolute';
            maintainUsersWidthMeasurer.style.visibility = 'hidden';
            maintainUsersWidthMeasurer.style.pointerEvents = 'none';
            maintainUsersWidthMeasurer.style.whiteSpace = 'pre';
            maintainUsersWidthMeasurer.style.left = '-9999px';
            maintainUsersWidthMeasurer.style.top = '-9999px';
            document.body.appendChild(maintainUsersWidthMeasurer);

            function measureMaintainUsersTextWidth(text, sourceElement) {
                if (!sourceElement) return 0;
                const styles = window.getComputedStyle(sourceElement);
                maintainUsersWidthMeasurer.style.font = styles.font;
                maintainUsersWidthMeasurer.style.fontSize = styles.fontSize;
                maintainUsersWidthMeasurer.style.fontWeight = styles.fontWeight;
                maintainUsersWidthMeasurer.style.letterSpacing = styles.letterSpacing;
                maintainUsersWidthMeasurer.textContent = text || '';
                return Math.ceil(maintainUsersWidthMeasurer.getBoundingClientRect().width);
            }

            function syncMaintainUsersMeasuredFilterWidth(columnName, options) {
                if (!table) return;

                const header = table.querySelector('thead th[data-col="' + columnName + '"]');
                const filterWrap = header ? header.querySelector('.inquiries-filter-wrap') : null;
                const filterInput = header ? header.querySelector('.inquiries-grid-filter') : null;
                const headerLabel = header ? header.querySelector('.inquiries-header-label') : null;
                if (!header || !filterWrap || !filterInput) return;

                const visibleRows = Array.prototype.slice.call(table.querySelectorAll('tbody .maintain-users-row')).filter(function (row) {
                    return row.style.display !== 'none';
                });
                const dataCells = visibleRows.map(function (row) {
                    return row.querySelector('td[data-col="' + columnName + '"]');
                }).filter(Boolean);

                const labelWidth = headerLabel ? measureMaintainUsersTextWidth(headerLabel.textContent || '', headerLabel) : 0;
                const widestCell = dataCells.reduce(function (maxWidth, cell) {
                    return Math.max(maxWidth, measureMaintainUsersTextWidth(cell.textContent || '', cell));
                }, 0);

                const minWidth = options && options.minWidth ? options.minWidth : 72;
                const maxWidth = options && options.maxWidth ? options.maxWidth : 180;
                const labelPadding = options && options.labelPadding ? options.labelPadding : 18;
                const cellPadding = options && options.cellPadding ? options.cellPadding : 34;
                const targetWidth = Math.max(minWidth, Math.min(maxWidth, Math.max(labelWidth + labelPadding, widestCell + cellPadding)));

                filterWrap.style.width = targetWidth + 'px';
                filterWrap.style.minWidth = targetWidth + 'px';
                filterInput.style.width = '100%';
                filterInput.style.minWidth = '0';
            }

            function syncMaintainUsersMeasuredFilters() {
                syncMaintainUsersMeasuredFilterWidth('alias', {
                    minWidth: 72,
                    maxWidth: 180,
                    labelPadding: 18,
                    cellPadding: 34
                });
                syncMaintainUsersMeasuredFilterWidth('lastlogin', {
                    minWidth: 110,
                    maxWidth: 150,
                    labelPadding: 18,
                    cellPadding: 34
                });
            }

            function initMaintainUsersPagination() {
                if (!table || !pagination) return;

                const infoEl = pagination.querySelector('.inquiries-assigned-pagination-info');
                const pageNumbersEl = document.getElementById('maintainUsersPageNumbers');
                const controls = pagination.querySelectorAll('.inquiries-pagination-btn');
                const perPage = parseInt(pagination.getAttribute('data-per-page') || '10', 10);
                window.maintainUsersPaginationState.perPage = perPage;

                function getAllRows() {
                    return Array.prototype.slice.call(table.querySelectorAll('tbody .maintain-users-row'));
                }

                function getMatchingRows() {
                    return getAllRows().filter(function (row) {
                        if (!row.dataset.filterMatch) row.dataset.filterMatch = '1';
                        return row.dataset.filterMatch !== '0';
                    });
                }

                function clearPlaceholderRows() {
                    Array.prototype.slice.call(table.querySelectorAll('tbody tr.maintain-users-placeholder-row')).forEach(function (row) {
                        row.remove();
                    });
                }

                function ensureFixedHeight(visibleRows) {
                    const tbody = table.querySelector('tbody');
                    if (!tbody) return;
                    clearPlaceholderRows();

                    // Disabled placeholder rows as per user request to avoid empty space
                    return;
                }

                function buildPageNumbers(currentPage, lastPage) {
                    if (!pageNumbersEl) return;
                    pageNumbersEl.innerHTML = '';
                    
                    const maxVisible = 3;
                    if (lastPage <= maxVisible + 2) {
                        for (let p = 1; p <= lastPage; p++) {
                            addPageButton(p, p === currentPage);
                        }
                    } else {
                        // Always show page 1
                        addPageButton(1, 1 === currentPage);
                        
                        let start = Math.max(2, currentPage - 1);
                        let end = Math.min(lastPage - 1, currentPage + 1);
                        
                        if (currentPage <= 3) {
                            start = 2;
                            end = 4;
                        } else if (currentPage >= lastPage - 2) {
                            start = lastPage - 3;
                            end = lastPage - 1;
                        }

                        if (start > 2) {
                            addEllipsis();
                        }
                        
                        for (let p = start; p <= end; p++) {
                            addPageButton(p, p === currentPage);
                        }
                        
                        if (end < lastPage - 1) {
                            addEllipsis();
                        }

                        // Always show last page
                        addPageButton(lastPage, lastPage === currentPage);
                    }
                }

                function addPageButton(p, isActive) {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'inquiries-pagination-num' + (isActive ? ' inquiries-pagination-num-active' : '');
                    btn.setAttribute('data-page', String(p));
                    btn.textContent = String(p);
                    btn.style.position = 'relative';
                    btn.style.zIndex = '5';
                    btn.addEventListener('click', function (e) {
                        e.stopPropagation();
                        if (!isActive) window.maintainUsersGoToPage(p);
                    });
                    pageNumbersEl.appendChild(btn);
                }

                function addEllipsis() {
                    const span = document.createElement('span');
                    span.className = 'inquiries-pagination-num inquiries-pagination-dots';
                    span.textContent = '...';
                    span.style.cursor = 'pointer';
                    span.title = 'Click to jump to page';
                    span.setAttribute('data-page', 'dots');
                    span.style.position = 'relative';
                    span.style.zIndex = '5';
                    span.addEventListener('click', function(e) {
                        e.stopPropagation();
                        
                        const lastPage = parseInt(pagination.getAttribute('data-last-page') || '1', 10);
                        const input = document.createElement('input');
                        input.type = 'number';
                        input.className = 'inquiries-pagination-num inquiries-pagination-jump-input';
                        input.min = '1';
                        input.max = lastPage;
                        input.placeholder = '#';
                        
                        const doJump = function() {
                            const val = parseInt(input.value, 10);
                            if (!isNaN(val) && val >= 1 && val <= lastPage) {
                                window.maintainUsersGoToPage(val);
                            } else {
                                input.parentElement.replaceChild(span, input);
                            }
                        };
                        
                        input.addEventListener('blur', doJump);
                        input.addEventListener('keypress', function(ev) {
                            if (ev.key === 'Enter') {
                                ev.preventDefault();
                                doJump();
                            }
                        });
                        
                        span.parentElement.replaceChild(input, span);
                        input.focus();
                    });
                    pageNumbersEl.appendChild(span);
                }

                // Legacy popout removed in favor of inline jump input

                function renderPage(page) {
                    const matchingRows = getMatchingRows();
                    const total = matchingRows.length;
                    const lastPage = total > 0 ? Math.ceil(total / perPage) : 1;
                    const safePage = Math.min(Math.max(parseInt(page || 1, 10), 1), lastPage);
                    const start = total === 0 ? 0 : (safePage - 1) * perPage;
                    const end = total === 0 ? 0 : Math.min(start + perPage, total);

                    pagination.setAttribute('data-total', String(total));
                    pagination.setAttribute('data-current-page', String(safePage));
                    pagination.setAttribute('data-last-page', String(lastPage));
                    window.maintainUsersPaginationState.currentPage = safePage;

                    getAllRows().forEach(function (row) {
                        row.style.display = 'none';
                    });

                    const visibleRows = matchingRows.slice(start, end);
                    visibleRows.forEach(function (row) {
                        row.style.display = '';
                    });

                    ensureFixedHeight(visibleRows);
                    syncMaintainUsersMeasuredFilters();

                    if (infoEl) {
                        const fromText = total === 0 ? 0 : start + 1;
                        infoEl.textContent = 'Showing ' + fromText + ' to ' + end + ' of ' + total + ' entries (Page ' + safePage + ')';
                    }

                    buildPageNumbers(safePage, lastPage);

                    controls.forEach(function (btn) {
                        const action = btn.getAttribute('data-page');
                        let disabled = false;
                        if (total === 0) {
                            disabled = true;
                        } else if (action === 'first' || action === 'prev') {
                            disabled = safePage <= 1;
                        } else if (action === 'next' || action === 'last') {
                            disabled = safePage >= lastPage;
                        }
                        btn.disabled = disabled;
                    });
                }

                window.maintainUsersGoToPage = function (page) {
                    const action = String(page || '').toLowerCase();
                    const currentPage = window.maintainUsersPaginationState.currentPage || 1;
                    const lastPage = parseInt(pagination.getAttribute('data-last-page') || '1', 10);
                    let targetPage = currentPage;

                    if (action === 'first') targetPage = 1;
                    else if (action === 'prev') targetPage = currentPage - 1;
                    else if (action === 'next') targetPage = currentPage + 1;
                    else if (action === 'last') targetPage = lastPage;
                    else targetPage = parseInt(page || '1', 10);

                    renderPage(targetPage);
                };

                controls.forEach(function (btn) {
                    if (btn.getAttribute('data-pagination-bound') === '1') return;
                    btn.setAttribute('data-pagination-bound', '1');
                    btn.addEventListener('click', function () {
                        window.maintainUsersGoToPage(btn.getAttribute('data-page') || '1');
                    });
                });

                renderPage(window.maintainUsersPaginationState.currentPage || 1);
            }

            // Live search: top search box + per-column filters (all apply as you type)
            function applyTableFilter(resetPage) {
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
                    row.dataset.filterMatch = colMatch ? '1' : '0';
                });
                initMaintainUsersPagination();
                if (window.maintainUsersGoToPage) {
                    window.maintainUsersGoToPage(resetPage ? 1 : (window.maintainUsersPaginationState.currentPage || 1));
                }
            }
            if (table) {
                table.querySelectorAll('thead .inquiries-grid-filter').forEach(function (inp) {
                    inp.addEventListener('input', function () {
                        applyTableFilter(true);
                    });
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
                    applyTableFilter(true);
                });
            }

            initMaintainUsersPagination();
            applyTableFilter(false);
            window.addEventListener('resize', syncMaintainUsersMeasuredFilters);

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
