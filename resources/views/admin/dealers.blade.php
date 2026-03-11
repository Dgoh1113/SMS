@extends('layouts.app')
@section('title', 'Users – Admin')
@section('content')
<header class="dashboard-header">
    <div>
        <h1 class="dashboard-title">Users</h1>
        <p class="dashboard-subtitle">Manage admin, manager, and dealer accounts</p>
    </div>
    <div class="dashboard-header-actions">
        <button type="button" class="btn btn-outline" id="openAddUserModal">Add user</button>
    </div>
</header>

<section class="dashboard-panel" style="margin-top:16px;">
    <div class="dashboard-panel-header">
        <div>
            <div class="dashboard-panel-title">User Directory</div>
            <div class="dashboard-panel-subtitle">Filter by role or search email / ID</div>
        </div>
    </div>
    <div class="dashboard-panel-body">
        @if (session('error'))
            <div class="login-message login-error" style="margin-bottom:10px;">{{ session('error') }}</div>
        @endif
        @if (session('success'))
            <div class="login-message login-success" style="margin-bottom:10px;">{{ session('success') }}</div>
        @endif
        <form method="GET" action="{{ route('admin.dealers') }}" class="form-grid-2 form-grid-filters">
            <div class="form-field">
                <label class="form-label">Search</label>
                <input type="text" name="q" value="{{ $filterQuery ?? '' }}" class="form-control" placeholder="Email or ID">
            </div>
            <div class="form-field">
                <label class="form-label">Role</label>
                <select name="role" class="form-control">
                    <option value="">All roles</option>
                    <option value="Admin" {{ ($filterRole ?? '') === 'Admin' ? 'selected' : '' }}>Admin</option>
                    <option value="Manager" {{ ($filterRole ?? '') === 'Manager' ? 'selected' : '' }}>Manager</option>
                    <option value="Dealer" {{ ($filterRole ?? '') === 'Dealer' ? 'selected' : '' }}>Dealer</option>
                </select>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-outline">Apply filters</button>
            </div>
        </form>
    </div>
</section>

<div class="dashboard-row" style="margin-top: 16px;">
    <section class="dashboard-panel dashboard-table-panel" style="grid-column: 1 / -1;">
        <div class="dashboard-panel-header">
            <div>
                <div class="dashboard-panel-title">All Users</div>
                <div class="dashboard-panel-subtitle">Inline update, ban, or delete</div>
            </div>
        </div>
        <div class="dashboard-panel-body">
            <div class="table-responsive">
                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Active</th>
                            <th>Last login</th>
                            <th style="width:170px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $r)
                            <tr>
                                <td>{{ $r->USERID }}</td>
                                <td>
                                    <form method="POST" action="{{ route('admin.dealers.update', $r->USERID) }}">
                                        @csrf
                                        <input type="email" name="email" value="{{ $r->EMAIL }}" class="form-control form-control-sm" required>
                                </td>
                                <td>
                                        <select name="role" class="form-control form-control-sm" required>
                                            <option value="Admin" {{ ($r->SYSTEMROLE ?? '') === 'Admin' ? 'selected' : '' }}>Admin</option>
                                            <option value="Manager" {{ ($r->SYSTEMROLE ?? '') === 'Manager' ? 'selected' : '' }}>Manager</option>
                                            <option value="Dealer" {{ ($r->SYSTEMROLE ?? '') === 'Dealer' ? 'selected' : '' }}>Dealer</option>
                                        </select>
                                </td>
                                <td>
                                        <label class="switch">
                                            <input type="checkbox" name="is_active" value="1" {{ ($r->ISACTIVE ?? 0) ? 'checked' : '' }}>
                                            <span class="slider"></span>
                                        </label>
                                </td>
                                <td>{{ $r->LASTLOGIN ? date('Y-m-d H:i', strtotime($r->LASTLOGIN)) : '—' }}</td>
                                <td>
                                        <div class="dealer-actions">
                                            <button type="submit" name="action" value="save" class="btn btn-xs">Save</button>
                                            <button type="submit" name="action" value="ban" class="btn btn-xs btn-danger-light">Ban</button>
                                            <button type="submit" name="action" value="delete" class="btn btn-xs btn-danger" onclick="return confirm('Delete this user?');">Delete</button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6">No users yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
    </section>
</div>

{{-- Add User modal --}}
<div id="addUserModal" class="modal-backdrop" style="display:none;">
    <div class="modal-panel">
        <div class="modal-header">
            <div>
                <div class="dashboard-panel-title">Add New User</div>
                <div class="dashboard-panel-subtitle">Create a new admin, manager, or dealer</div>
            </div>
            <button type="button" class="modal-close" id="closeAddUserModal">&times;</button>
        </div>
        <div class="modal-body">
            <form method="POST" action="{{ route('admin.dealers.store') }}" class="form-grid-2">
                @csrf
                <div class="form-field">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="form-control" required>
                </div>
                <div class="form-field">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-control" required>
                        <option value="Dealer" {{ old('role') === 'Dealer' ? 'selected' : '' }}>Dealer</option>
                        <option value="Manager" {{ old('role') === 'Manager' ? 'selected' : '' }}>Manager</option>
                        <option value="Admin" {{ old('role') === 'Admin' ? 'selected' : '' }}>Admin</option>
                    </select>
                </div>
                <div class="form-field">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="form-field">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>
                <div class="form-field form-field-inline">
                    <label class="form-label">Active</label>
                    <label class="switch">
                        <input type="checkbox" name="is_active" value="1" checked>
                        <span class="slider"></span>
                    </label>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-outline">Create user</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const openBtn = document.getElementById('openAddUserModal');
            const closeBtn = document.getElementById('closeAddUserModal');
            const modal = document.getElementById('addUserModal');
            if (!modal || !openBtn || !closeBtn) return;
            openBtn.addEventListener('click', () => { modal.style.display = 'flex'; });
            closeBtn.addEventListener('click', () => { modal.style.display = 'none'; });
            modal.addEventListener('click', (e) => {
                if (e.target === modal) modal.style.display = 'none';
            });
        });
    </script>
@endpush
