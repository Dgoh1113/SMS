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

<section class="dashboard-panel dashboard-table-panel inquiry-create-panel">
    <div class="dashboard-panel-body inquiry-create-body">
        <div class="inquiry-create-layout">
            <div class="inquiry-create-main">
                @if (session('error'))
                    <div class="login-message login-error" style="margin-bottom: 1rem;">{{ session('error') }}</div>
                @endif
                @if (session('duplicate_warning'))
                    <div class="login-message login-warning" style="margin-bottom: 1rem;">
                        {{ session('duplicate_warning') }}
                    </div>
                @endif

                {{-- Duplicate company confirmation modal --}}
                @if (session('duplicate_warning'))
                    <div class="inquiry-dup-modal" id="dupModal" role="dialog" aria-modal="true" aria-labelledby="dupModalTitle" hidden>
                        <div class="inquiry-dup-backdrop" data-dup-close="1"></div>
                        <div class="inquiry-dup-window">
                            <div class="inquiry-dup-header">
                                <div class="inquiry-dup-title" id="dupModalTitle">Company already exists</div>
                                <button type="button" class="inquiry-dup-close" aria-label="Close" data-dup-close="1">&times;</button>
                            </div>
                            <div class="inquiry-dup-body">
                                <p class="inquiry-dup-text">{{ session('duplicate_warning') }}</p>
                                <p class="inquiry-dup-subtext">Would you like to create another inquiry for the same company?</p>
                                <div class="inquiry-dup-actions">
                                    <button type="button" class="inquiries-btn inquiries-btn-secondary" data-dup-close="1">Cancel</button>
                                    <button type="button" class="inquiries-btn inquiries-btn-primary" id="dupConfirmBtn">Confirm & Add</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.inquiries.store') }}" class="inquiry-form" id="inquiryForm">
                    @csrf
                    @if (old('duplicate_ok'))
                        <input type="hidden" name="duplicate_ok" value="1">
                    @endif
            <div class="inquiry-form-grid">
                {{-- Row 1: Company name + Email --}}
                <label class="inquiry-form-label">
                    <span class="inquiry-form-label-title">Company name <span class="required">*</span></span>
                    <div class="inquiry-company-wrapper">
                        <input type="text" name="COMPANYNAME" id="companyInput" value="{{ old('COMPANYNAME') }}" required maxlength="255" class="inquiry-form-input">
                        <button type="button" class="inquiry-company-copy-btn" id="copyCompanyBtn" title="Copy from existing lead" hidden>
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </button>
                    </div>
                </label>
                <label class="inquiry-form-label">
                    <span class="inquiry-form-label-title">Email <span class="required">*</span></span>
                    <input type="email" name="EMAIL" value="{{ old('EMAIL') }}" required maxlength="255" class="inquiry-form-input">
                </label>

                {{-- Row 2: Contact name + Contact no --}}
                <label class="inquiry-form-label">
                    <span class="inquiry-form-label-title">Contact name <span class="required">*</span></span>
                    <input type="text" name="CONTACTNAME" value="{{ old('CONTACTNAME') }}" required maxlength="255" class="inquiry-form-input">
                </label>
                <label class="inquiry-form-label">
                    <span class="inquiry-form-label-title">Contact no <span class="required">*</span></span>
                    <input type="text" name="CONTACTNO" value="{{ old('CONTACTNO') }}" required maxlength="15" class="inquiry-form-input @error('CONTACTNO') inquiry-input-error @enderror">
                    @error('CONTACTNO')
                        <div class="inquiry-field-error">{{ $message }}</div>
                    @enderror
                </label>

                {{-- Row 3: Business nature full --}}
                <label class="inquiry-form-label inquiry-form-full">
                    <span class="inquiry-form-label-title">Business nature <span class="required">*</span></span>
                    <input type="text" name="BUSINESSNATURE" value="{{ old('BUSINESSNATURE') }}" required maxlength="255" class="inquiry-form-input">
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
                    <span class="inquiry-form-label-title">City <span class="required">*</span></span>
                    <input type="text" name="CITY" value="{{ old('CITY') }}" required maxlength="100" class="inquiry-form-input">
                </label>
                <label class="inquiry-form-label">
                    <span class="inquiry-form-label-title">Post code <span class="required">*</span></span>
                    <input type="text" name="POSTCODE" value="{{ old('POSTCODE') }}" required maxlength="5" class="inquiry-form-input @error('POSTCODE') inquiry-input-error @enderror">
                    @error('POSTCODE')
                        <div class="inquiry-field-error">{{ $message }}</div>
                    @enderror
                </label>
                <div class="inquiry-form-full inquiry-form-row3">
                    <label class="inquiry-form-label">
                        <span class="inquiry-form-label-title">User count</span>
                        @php $uc = (int) old('USERCOUNT', 1); if ($uc < 1) $uc = 1; @endphp
                        <input type="number" name="USERCOUNT" value="{{ $uc }}" min="1" class="inquiry-form-input">
                    </label>
                    <label class="inquiry-form-label">
                        <span class="inquiry-form-label-title">Existing software <span class="required">*</span></span>
                        <input type="text" name="EXISTINGSOFTWARE" value="{{ old('EXISTINGSOFTWARE') }}" required maxlength="255" class="inquiry-form-input">
                    </label>
                    <label class="inquiry-form-label">
                        <span class="inquiry-form-label-title">Demo mode</span>
                        @php $demoOld = old('DEMOMODE', 'Zoom'); @endphp
                        <div class="inquiry-toggle" data-toggle="demomode">
                            <button type="button" class="inquiry-toggle-option {{ $demoOld === 'Zoom' ? 'is-active' : '' }}" data-value="Zoom">Zoom</button>
                            <button type="button" class="inquiry-toggle-option {{ $demoOld === 'On-site' ? 'is-active' : '' }}" data-value="On-site">On-site</button>
                        </div>
                        <input type="hidden" name="DEMOMODE" id="demoModeInput" value="{{ $demoOld }}">
                    </label>
                </div>
                <label class="inquiry-form-label inquiry-form-full inquiry-form-products">
                    <span class="inquiry-form-label-title">Product interested <span class="required">*</span></span>
                    <div class="inquiry-form-checkboxes @error('product_interested') inquiry-input-error @enderror" role="group" aria-required="true">
                        @foreach($productInterestedList ?? [] as $num => $label)
                            @php $oldProducts = old('product_interested', []); @endphp
                            <label class="inquiry-form-checkbox-label">
                                <input type="checkbox" name="product_interested[]" value="{{ $num }}" {{ in_array($num, $oldProducts) ? 'checked' : '' }} class="inquiry-form-checkbox">
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                    @error('product_interested')
                        <div class="inquiry-field-error">Please select at least one product.</div>
                    @enderror
                </label>
                <label class="inquiry-form-label inquiry-form-full">
                    Referral code
                    <input type="text" name="REFERRALCODE" value="{{ old('REFERRALCODE') }}" maxlength="100" class="inquiry-form-input">
                </label>
                <label class="inquiry-form-label inquiry-form-full">
                    Message
                    <textarea name="DESCRIPTION" rows="6" maxlength="4000" class="inquiry-form-input" placeholder="Type the customer message / notes...">{{ old('DESCRIPTION') }}</textarea>
                </label>
            </div>
            <div class="inquiry-form-actions">
                <button type="submit" class="login-primary-btn">Save inquiry</button>
                <a href="{{ route('admin.inquiries') }}" class="inquiry-form-cancel">Cancel</a>
            </div>
        </form>
            </div>

            <div class="inquiry-create-fox">
                <img src="{{ asset('NewInquiries-FoxIcon.png') }}" alt="New inquiry" class="inquiry-create-fox-img">
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Demo mode toggle (Zoom / On-site)
    var toggle = document.querySelector('.inquiry-toggle[data-toggle="demomode"]');
    var hidden = document.getElementById('demoModeInput');
    if (toggle && hidden) {
        toggle.addEventListener('click', function (e) {
            var btn = e.target.closest('.inquiry-toggle-option');
            if (!btn) return;
            var val = btn.getAttribute('data-value') || '';
            if (!val) return;
            toggle.querySelectorAll('.inquiry-toggle-option').forEach(function (b) {
                b.classList.toggle('is-active', b === btn);
            });
            hidden.value = val;
        });
    }

    // Custom duplicate confirmation modal
    var hasDupWarning = {!! session('duplicate_warning') ? 'true' : 'false' !!};
    if (hasDupWarning) {
        var modal = document.getElementById('dupModal');
        var form = document.getElementById('inquiryForm');
        var confirmBtn = document.getElementById('dupConfirmBtn');
        if (modal) modal.hidden = false;

        function closeDup() { if (modal) modal.hidden = true; }

        document.addEventListener('click', function (e) {
            if (e.target && e.target.getAttribute && e.target.getAttribute('data-dup-close') === '1') {
                closeDup();
            }
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && modal && !modal.hidden) closeDup();
        });
        if (confirmBtn && form) {
            confirmBtn.addEventListener('click', function () {
                var existing = form.querySelector('input[name="duplicate_ok"]');
                if (!existing) {
                    existing = document.createElement('input');
                    existing.type = 'hidden';
                    existing.name = 'duplicate_ok';
                    existing.value = '1';
                    form.appendChild(existing);
                } else {
                    existing.value = '1';
                }
                form.submit();
            });
        }
    }

    // Focus first invalid field if any
    var firstInvalid = document.querySelector('.inquiry-form-input.inquiry-input-error');
    if (firstInvalid) {
        firstInvalid.focus();
        try {
            firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
        } catch (e) {}
    }

    // Company duplicate lookup + copy existing data
    var companyInput = document.getElementById('companyInput');
    var copyBtn = document.getElementById('copyCompanyBtn');
    var lastCompanyData = null;
    var lookupTimer = null;
    var lookupUrl = "{{ route('admin.inquiries.company-lookup') }}";

    function scheduleCompanyLookup() {
        if (!companyInput || !copyBtn || !lookupUrl) return;
        var val = (companyInput.value || '').trim();
        if (lookupTimer) clearTimeout(lookupTimer);
        if (val.length < 3) {
            lastCompanyData = null;
            copyBtn.hidden = true;
            return;
        }
        lookupTimer = setTimeout(function () {
            fetch(lookupUrl + '?q=' + encodeURIComponent(val), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            }).then(function (res) {
                return res.ok ? res.json() : Promise.reject();
            }).then(function (data) {
                if (data && data.found) {
                    lastCompanyData = data;
                    copyBtn.hidden = false;
                } else {
                    lastCompanyData = null;
                    copyBtn.hidden = true;
                }
            }).catch(function () {
                lastCompanyData = null;
                copyBtn.hidden = true;
            });
        }, 500);
    }

    if (companyInput && copyBtn) {
        companyInput.addEventListener('input', scheduleCompanyLookup);
        copyBtn.addEventListener('click', function () {
            if (!lastCompanyData) return;
            var map = {
                EMAIL: 'email',
                CONTACTNAME: 'contactname',
                CONTACTNO: 'contactno',
                CITY: 'city',
                POSTCODE: 'postcode',
                BUSINESSNATURE: 'businessnature',
                ADDRESS1: 'address1',
                ADDRESS2: 'address2',
                EXISTINGSOFTWARE: 'existingsoftware',
                USERCOUNT: 'usercount'
            };
            Object.keys(map).forEach(function (field) {
                var key = map[field];
                var value = lastCompanyData[key];
                var input = document.querySelector('[name="' + field + '"]');
                if (!input) return;
                if (field === 'USERCOUNT') {
                    var num = parseInt(value, 10);
                    if (!isNaN(num) && num > 0) {
                        // Always prefer the existing lead's user count over the default (1)
                        input.value = String(num);
                    }
                    return;
                }
                if (typeof value === 'string' && value !== '' && (!input.value || input.value.trim() === '')) {
                    input.value = value;
                }
            });

            // Demo mode toggle (Zoom / On-site) from existing lead
            if (lastCompanyData.demomode) {
                var dm = String(lastCompanyData.demomode);
                var demoInput = document.getElementById('demoModeInput');
                var toggle = document.querySelector('.inquiry-toggle[data-toggle="demomode"]');
                if (demoInput && toggle && (dm === 'Zoom' || dm === 'On-site')) {
                    demoInput.value = dm;
                    toggle.querySelectorAll('.inquiry-toggle-option').forEach(function (b) {
                        b.classList.toggle('is-active', b.getAttribute('data-value') === dm);
                    });
                }
            }
        });
    }
});
</script>
@endpush
