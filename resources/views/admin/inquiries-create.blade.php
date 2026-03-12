@extends('layouts.app')
@section('title', 'Add new inquiry – Admin')
@section('content')
<header class="dashboard-header">
    <div>
        <h1 class="dashboard-title">Add new inquiry</h1>
        <p class="dashboard-subtitle">Create a new lead</p>
    </div>
    <a href="{{ route('admin.inquiries') }}" class="dashboard-panel-link">← Back to leads</a>
</header>

<section class="dashboard-panel dashboard-table-panel">
    <div class="dashboard-panel-body">
        @if (session('error'))
            <div class="login-message login-error" style="margin-bottom: 1rem;">{{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('admin.inquiries.store') }}" class="inquiry-form">
            @csrf
            <div class="inquiry-form-grid">
                <label class="inquiry-form-label">
                    Company name <span class="required">*</span>
                    <input type="text" name="COMPANYNAME" value="{{ old('COMPANYNAME') }}" required maxlength="255" class="inquiry-form-input">
                </label>
                <label class="inquiry-form-label">
                    Contact name <span class="required">*</span>
                    <input type="text" name="CONTACTNAME" value="{{ old('CONTACTNAME') }}" required maxlength="255" class="inquiry-form-input">
                </label>
                <label class="inquiry-form-label">
                    Contact no <span class="required">*</span>
                    <input type="text" name="CONTACTNO" value="{{ old('CONTACTNO') }}" required maxlength="100" class="inquiry-form-input">
                </label>
                <label class="inquiry-form-label">
                    Email <span class="required">*</span>
                    <input type="email" name="EMAIL" value="{{ old('EMAIL') }}" required maxlength="255" class="inquiry-form-input">
                </label>
                <label class="inquiry-form-label inquiry-form-full">
                    Address 1
                    <input type="text" name="ADDRESS1" value="{{ old('ADDRESS1') }}" maxlength="255" class="inquiry-form-input">
                </label>
                <label class="inquiry-form-label inquiry-form-full">
                    Address 2
                    <input type="text" name="ADDRESS2" value="{{ old('ADDRESS2') }}" maxlength="255" class="inquiry-form-input">
                </label>
                <label class="inquiry-form-label">
                    City <span class="required">*</span>
                    <input type="text" name="CITY" value="{{ old('CITY') }}" required maxlength="100" class="inquiry-form-input">
                </label>
                <label class="inquiry-form-label">
                    Post code <span class="required">*</span>
                    <input type="text" name="POSTCODE" value="{{ old('POSTCODE') }}" required maxlength="20" class="inquiry-form-input">
                </label>
                <label class="inquiry-form-label inquiry-form-full">
                    Business nature <span class="required">*</span>
                    <input type="text" name="BUSINESSNATURE" value="{{ old('BUSINESSNATURE') }}" required maxlength="255" class="inquiry-form-input">
                </label>
                <label class="inquiry-form-label">
                    User count
                    <input type="text" name="USERCOUNT" value="{{ old('USERCOUNT') }}" maxlength="50" class="inquiry-form-input">
                </label>
                <label class="inquiry-form-label">
                    Existing software <span class="required">*</span>
                    <input type="text" name="EXISTINGSOFTWARE" value="{{ old('EXISTINGSOFTWARE') }}" required maxlength="255" class="inquiry-form-input">
                </label>
                <label class="inquiry-form-label">
                    Demo mode <span class="required">*</span>
                    <select name="DEMOMODE" required class="inquiry-form-input">
                        <option value="">— Select —</option>
                        <option value="Zoom" {{ old('DEMOMODE') === 'Zoom' ? 'selected' : '' }}>Zoom</option>
                        <option value="On-site" {{ old('DEMOMODE') === 'On-site' ? 'selected' : '' }}>On-site</option>
                    </select>
                </label>
                <div class="inquiry-form-full inquiry-form-products">
                    <span class="inquiry-form-label">Product interested <span class="required">*</span></span>
                    <div class="inquiry-form-checkboxes" role="group" aria-required="true">
                        @foreach($productInterestedList ?? [] as $num => $label)
                            @php $oldProducts = old('product_interested', []); @endphp
                            <label class="inquiry-form-checkbox-label">
                                <input type="checkbox" name="product_interested[]" value="{{ $num }}" {{ in_array($num, $oldProducts) ? 'checked' : '' }} class="inquiry-form-checkbox">
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                    <p class="inquiry-form-hint">Select at least one product.</p>
                </div>
                <label class="inquiry-form-label inquiry-form-full">
                    Referral code
                    <input type="text" name="REFERRALCODE" value="{{ old('REFERRALCODE') }}" maxlength="100" class="inquiry-form-input">
                </label>
                <label class="inquiry-form-label inquiry-form-full">
                    Assigned to (dealer)
                    <select name="ASSIGNED_TO" class="inquiry-form-input">
                        <option value="">— Not assigned —</option>
                        @foreach($dealers as $dealer)
                            @php
                                $uid = $dealer->USERID ?? $dealer->userid ?? '';
                                $label = trim($dealer->COMPANY ?? $dealer->company ?? '') ?: ($dealer->EMAIL ?? $dealer->email ?? $uid);
                            @endphp
                            <option value="{{ $uid }}" {{ old('ASSIGNED_TO') == $uid ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="inquiry-form-label inquiry-form-full">
                    Description
                    <textarea name="DESCRIPTION" rows="4" maxlength="4000" class="inquiry-form-input">{{ old('DESCRIPTION') }}</textarea>
                </label>
            </div>
            <div class="inquiry-form-actions">
                <button type="submit" class="login-primary-btn">Save inquiry</button>
                <a href="{{ route('admin.inquiries') }}" class="inquiry-form-cancel">Cancel</a>
            </div>
        </form>
    </div>
</section>
@endsection
