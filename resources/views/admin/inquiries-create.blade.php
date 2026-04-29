@extends('layouts.app')
@php $isEdit = isset($inquiry); $inquiry = $inquiry ?? null; @endphp
@section('title', $isEdit ? 'Edit inquiry - Admin' : 'Add new inquiry - Admin')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/admin-inquiries.css') }}?v=20260421-01">
    <style>
        /* ===== Panel Container ===== */
        .inquiry-create-panel {
            width: min(92vw, 1500px);
            max-width: 1500px;
            margin: 8px auto;
            position: relative;
            overflow: hidden;
            background: #f8f9fc;
            border: 1px solid #e9eaf2;
            border-radius: 16px;
            box-shadow: 0 6px 18px rgba(30, 41, 59, 0.05);
        }

        .inquiry-create-panel--new .dashboard-panel-body.inquiry-create-body {
            padding-left: 56px !important;
        }

        .vertical-title {
            position: absolute;
            top: 20px;
            left: 42px;
            z-index: 2;
            writing-mode: vertical-rl;
            transform: rotate(180deg);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 15px 7px;
            border-radius: 18px;
            background: linear-gradient(180deg, #f3edff, #ebe4ff);
            color: #7c5cff;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 0.18em;
            box-shadow: 0 4px 14px rgba(124, 92, 255, 0.12);
            pointer-events: none;
            white-space: nowrap;
        }

        .inquiry-create-panel .dashboard-panel-body.inquiry-create-body {
            padding: 14px 14px !important;
        }

        .dashboard-root.inquiry-create-scroll {
            min-height: 100vh !important;
            height: auto !important;
        }

        .dashboard-root.inquiry-create-scroll .dashboard-main {
            height: auto !important;
            min-height: 100vh !important;
            overflow: visible !important;
        }

        .dashboard-root.inquiry-create-scroll .dashboard-main-body {
            flex: 1 1 auto !important;
            min-height: 0 !important;
            overflow: visible !important;
            padding-bottom: 24px !important;
        }

        .dashboard-root.inquiry-create-scroll .dashboard-bottombar {
            margin-top: auto !important;
        }

        .dashboard-root.inquiry-create-scroll .dashboard-sidebar {
            align-self: stretch !important;
            min-height: 100vh !important;
            height: auto !important;
        }

        .inquiry-create-layout {
            align-items: flex-start !important;
            gap: 16px !important;
            position: relative;
        }

        .inquiry-create-main {
            flex: 1 1 auto;
            min-width: 0;
        }

        .inquiry-create-panel .inquiry-create-fox {
            display: none !important;
        }
        /* --- Section Styling --- */
        .inquiry-form-section {
            background: #fff;
            border-radius: 14px;
            padding: 14px 18px;
            margin-bottom: 0;
            border: 1px solid #f0f0f5;
        }

        .inquiry-form-section-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 10px;
        }

        .inquiry-form-section-icon {
            width: 34px;
            height: 34px;
            background: #f3f0ff;
            color: #6366f1;
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        .inquiry-form-section-title {
            font-size: 1rem;
            font-weight: 700;
            color: #1a1a1a;
            margin: 0;
        }

        .inquiry-form-section-subtitle {
            font-size: 0.8rem;
            color: #666;
            margin: 1px 0 0 0;
        }

        /* --- Input Wrapper with Icons --- */
        .inquiry-form-input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .inquiry-input-icon {
            position: absolute;
            left: 12px;
            color: #999;
            font-size: 1.1rem;
            pointer-events: none;
        }

        .inquiry-form-input.has-icon {
            padding-left: 40px !important;
        }

        /* --- Product Grid --- */
        .inquiry-form-checkboxes {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
            width: 100%;
        }

        .inquiry-form-checkbox-label {
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 10px 12px;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            font-weight: 600;
            font-size: 0.85rem;
            color: #444;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }

        .inquiry-form-checkbox-label::before {
            content: "";
            min-width: 20px;
            height: 20px;
            border: 2px solid #d1d5db;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            background: #fff;
        }

        .inquiry-form-checkbox-label:hover {
            border-color: #6366f1;
            background: #f8faff;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.08);
        }

        .inquiry-form-checkbox-label:has(input:checked) {
            background: #f3f0ff;
            border-color: #6366f1;
            color: #4f46e5;
        }

        .inquiry-form-checkbox-label:has(input:checked)::before {
            background: #6366f1;
            border-color: #6366f1;
            content: "\F26E"; /* bi-check */
            font-family: "bootstrap-icons" !important;
            color: #fff;
            font-size: 14px;
        }

        /* --- Demo Mode Toggle --- */
        .inquiry-toggle {
            display: flex;
            background: #f8f9fa;
            border-radius: 8px;
            padding: 4px;
            gap: 4px;
        }

        .inquiry-toggle-option {
            flex: 1;
            border: none;
            background: transparent;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            cursor: pointer;
            transition: all 0.2s;
            color: #666;
        }

        .inquiry-toggle-option.is-active {
            background: #6366f1;
            color: #fff;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.2);
        }

        /* REFINED MASCOT - 200px */
        img.inquiry-create-fox-img {
            position: absolute !important;
            top: 5px !important;
            right: 12px !important;
            width: 200px !important;
            height: auto !important;
            max-width: none !important;
            z-index: 5 !important;
            pointer-events: none !important;
            display: block !important;
        }

        .inquiry-form-section {
            width: 100% !important;
            box-sizing: border-box !important;
            position: relative !important;
            overflow: visible !important;
        }

        .inquiry-lookup-btn {
            position: absolute;
            right: 4px;
            top: 50%;
            transform: translateY(-50%);
            width: 26px;
            height: 26px;
            background: #f5f3ff;
            border: 1px solid #ddd6fe;
            border-radius: 6px;
            color: #6366f1;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            opacity: 0;
            visibility: hidden;
            box-shadow: 0 2px 4px rgba(99, 102, 241, 0.1);
        }

        .inquiry-lookup-btn:hover {
            background: #6366f1;
            color: #fff;
            border-color: #6366f1;
            box-shadow: 0 2px 6px rgba(99, 102, 241, 0.3);
        }

        .inquiry-lookup-btn.is-visible {
            display: flex;
            opacity: 1;
            visibility: visible;
        }

        .inquiry-form-section {
            width: 100% !important;
            box-sizing: border-box;
            position: relative;
        }

        /* --- Custom Grid Layout for Sections --- */
        .inquiry-form-section .inquiry-form-grid {
            display: grid;
            grid-template-columns: repeat(12, 1fr);
            gap: 14px;
        }

        .inquiry-form-section .full { grid-column: span 12; }
        .inquiry-form-section .half { grid-column: span 6; }
        .inquiry-form-section .third { grid-column: span 4; }
        .inquiry-form-section .quarter { grid-column: span 3; }

        /* City icon overlap adjustment */
        .inquiry-city-input-wrap .inquiry-input-icon {
            z-index: 6;
        }
        .inquiry-city-input-wrap .inquiry-form-input {
            padding-left: 40px !important;
            padding-right: 40px !important;
        }

        .inquiry-create-panel #inquiryFormGrid .google-maps-btn {
            right: 6px;
            bottom: 6px;
        }

        /* ===== Form Body ===== */
        /* Stack top sections, but split Inquiry Details */
        /* Force stack top sections, but keep split in Inquiry Details */
        .inquiry-create-panel .inquiry-form-body {
            display: flex !important;
            flex-direction: column !important;
            gap: 16px !important;
            width: 100% !important;
        }

        .inquiry-details-split {
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 24px;
            align-items: start;
        }

        .inquiry-details-right {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .inquiry-form-label {
            display: flex;
            flex-direction: column;
            gap: 4px;
            min-width: 0;
            color: #2f3654;
        }

        .inquiry-form-label-title {
            display: inline-flex;
            align-items: center;
            gap: 3px;
            font-size: 14px;
            font-weight: 600;
            color: #2f3654;
            white-space: nowrap;
            line-height: 1.2;
        }

        .required { color: #ef4444; }

        .inquiry-form-input {
            width: 100%;
            height: 36px;
            box-sizing: border-box;
            border: 1px solid #d8dce8;
            border-radius: 10px;
            background: #fff;
            font-size: 13px;
            color: #2f3654;
            padding: 5px 12px;
            transition: all 0.2s ease;
        }

        .inquiry-form-input:focus {
            outline: none;
            border-color: #7c5cff;
            box-shadow: 0 0 0 3px rgba(124, 92, 255, 0.10);
        }

        textarea.inquiry-form-input {
            min-height: 60px !important;
            height: 60px !important;
            padding: 8px 12px 6px !important;
            resize: vertical;
        }

        .inquiry-city-input-wrap {
            position: relative;
            display: flex;
            flex-direction: column;
            width: 100%;
        }

        #googleMapsWrap {
            position: absolute;
            right: 4px;
            bottom: 4px;
            z-index: 5;
        }

        .google-maps-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 30px !important;
            width: 30px !important;
            border-radius: 8px;
            border: none !important;
            background: transparent !important;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .google-maps-btn:hover {
            background: rgba(0,0,0,0.05) !important;
        }

        .inquiry-inline-toggle {
            display: inline-block;
            padding: 0;
            border: none;
            background: transparent;
            color: #7c5cff;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
        }

        .inquiry-inline-toggle:hover {
            color: #6847f5;
            text-decoration: underline;
        }

        .inquiry-address2-wrap {
            display: none;
            opacity: 0;
            transform: translateY(-10px);
            transition: opacity 0.2s ease, transform 0.2s ease;
        }

        .inquiry-address2-wrap.is-visible {
            display: block;
            opacity: 1;
            transform: translateY(0);
        }

        /* When inside grid, use grid display instead of block */
        .inquiry-form-grid .inquiry-address2-wrap.is-visible {
            display: grid;
        }

        .inquiry-form-actions .login-primary-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 150px;
            height: 42px;
            padding: 0 24px;
            border: none;
            border-radius: 10px;
            background: linear-gradient(135deg, #8b5cf6, #6d3df2);
            box-shadow: 0 4px 12px rgba(109, 61, 242, 0.16);
            color: #fff;
            font-size: 14px;
            font-weight: 600;
            white-space: nowrap;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .inquiry-form-actions .login-primary-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 14px rgba(109, 61, 242, 0.20);
        }

        .inquiry-form-actions .inquiry-form-cancel {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 110px;
            height: 42px;
            padding: 0 24px;
            border: 1px solid #d8dce8;
            border-radius: 10px;
            background: #fff;
            color: #4b5563;
            font-size: 14px;
            font-weight: 600;
            white-space: nowrap;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .inquiry-form-actions .inquiry-form-cancel:hover {
            border-color: #c9cfdf;
            background: #f8f9fc;
            text-decoration: none;
        }

        /* ===== Wide Desktop Compact ===== */
        @media (min-width: 1280px) {
            .inquiry-create-panel {
                margin: 14px 10px 4px -2px !important;
                width: calc(100% - 12px) !important;
                max-width: 1800px !important;
            }

            .inquiry-create-panel .dashboard-panel-body.inquiry-create-body {
                padding: 4px 14px 4px !important;
            }

            .inquiry-create-panel--new .dashboard-panel-body.inquiry-create-body {
                padding-left: 44px !important;
            }

            .inquiry-form-body {
                gap: 12px !important;
                display: flex !important;
                flex-direction: column !important;
                justify-content: space-between !important;
                width: 100% !important;
            }

            .inquiry-form-section {
                padding: 10px 18px !important;
                border: 1px solid #eef0f7 !important;
                box-shadow: 0 2px 8px rgba(0,0,0,0.02) !important;
            }

            .inquiry-form-section-header {
                margin-bottom: 6px !important;
                gap: 8px !important;
            }

            .inquiry-form-section-icon {
                width: 30px;
                height: 30px;
                font-size: 15px;
                border-radius: 8px;
            }

            .inquiry-form-section-title {
                font-size: 0.95rem;
            }

            .inquiry-form-section-subtitle {
                font-size: 0.75rem;
            }

            /* Balanced spacing when Address 2 is expanded */
            .inquiry-form-body.has-address2 {
                gap: 6px !important;
            }
            .inquiry-form-body.has-address2 .inquiry-form-section {
                padding-top: 8px !important;
                padding-bottom: 8px !important;
            }
            .inquiry-form-body.has-address2 .inquiry-form-grid {
                gap: 8px !important;
            }

            .inquiry-form-section .inquiry-form-grid {
                gap: 12px;
            }

            .inquiry-form-label-title {
                font-size: 12px;
            }

            .inquiry-form-input {
                height: 32px;
                font-size: 13px;
                padding: 4px 12px;
            }

            .inquiry-form-input.has-icon {
                padding-left: 36px !important;
            }

            .inquiry-input-icon {
                left: 10px;
                font-size: 1rem;
            }

            textarea.inquiry-form-input {
                min-height: 50px !important;
                height: 50px !important;
                padding: 6px 10px 4px !important;
            }

            .inquiry-form-checkboxes {
                gap: 6px;
            }

            .inquiry-form-checkbox-label {
                padding: 6px 8px;
                font-size: 0.78rem;
                gap: 6px;
                border-radius: 8px;
            }

            .inquiry-form-checkbox-label::before {
                min-width: 16px;
                height: 16px;
                border-radius: 5px;
            }

            .inquiry-toggle-option {
                padding: 6px 12px;
                font-size: 0.8rem;
            }

            .inquiry-form-actions .login-primary-btn,
            .inquiry-form-actions .inquiry-form-cancel {
                height: 38px !important;
                width: 150px !important;
                min-width: 150px !important;
                padding: 0 24px !important;
                font-size: 13.5px !important;
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                text-align: center !important;
                border-radius: 12px !important;
                font-weight: 700 !important;
                line-height: 1 !important;
                gap: 8px !important;
            }

            .inquiry-form-actions .inquiry-form-cancel {
                border: 1px solid #e0e0e0 !important;
                color: #666 !important;
                background: #fff !important;
                text-decoration: none !important;
            }

            img.inquiry-create-fox-img {
                width: 140px !important;
                top: 2px !important;
                right: 10px !important;
            }

            .vertical-title {
                padding: 16px 5px;
                font-size: 14px;
                border-radius: 14px;
                top: 19px;
                left: 54px;
                z-index: 10;
            }
        }

        /* ===== Responsive: Tablet ===== */
        @media (max-width: 1024px) {
            .inquiry-details-split {
                grid-template-columns: 1fr !important;
                gap: 16px !important;
            }
            .inquiry-form-section .inquiry-form-grid {
                grid-template-columns: repeat(6, 1fr) !important;
            }
            .inquiry-form-section .third { grid-column: span 3; }
            .inquiry-form-section .quarter { grid-column: span 3; }
            .inquiry-form-checkboxes {
                grid-template-columns: repeat(3, 1fr) !important;
            }
        }

        /* ===== Responsive: Mobile ===== */
        @media (max-width: 768px) {
            .inquiry-create-panel { margin: 12px; max-width: none; width: auto; }
            .inquiry-create-panel--new .dashboard-panel-body.inquiry-create-body { padding-left: 18px !important; }
            .vertical-title { display: none; }
            .inquiry-create-fox-img { display: none !important; }
            .inquiry-details-split {
                grid-template-columns: 1fr !important;
            }
            .inquiry-form-section { padding: 16px; }
            .inquiry-form-section .inquiry-form-grid {
                grid-template-columns: 1fr !important;
                gap: 14px !important;
            }
            .inquiry-form-section .full,
            .inquiry-form-section .half,
            .inquiry-form-section .third,
            .inquiry-form-section .quarter { grid-column: span 1 !important; }
            .inquiry-form-checkboxes { grid-template-columns: repeat(2, 1fr) !important; }
            .inquiry-form-actions { flex-direction: column !important; }
            .inquiry-form-actions .login-primary-btn,
            .inquiry-form-actions .inquiry-form-cancel { width: 100%; justify-content: center; }
        }

        @media (max-width: 480px) {
            .inquiry-form-checkboxes { grid-template-columns: 1fr !important; }
        }








        .inquiry-form-input:focus {
            background-color: #fff !important;
        }

        .inquiry-form-input:not(:placeholder-shown):not(:focus) {
            background-color: #f6faff !important;
            border-color: #d1e4ff !important;
        }

    </style>
@endpush
@section('content')
<section class="dashboard-panel dashboard-table-panel inquiry-create-panel{{ $isEdit ? ' inquiry-create-panel--edit' : ' inquiry-create-panel--new' }}">
    <div class="vertical-title" aria-hidden="true">{{ $isEdit ? 'EDIT INQUIRY - #SQL-' . ($inquiry->LEADID ?? '') : 'ADD INQUIRY' }}</div>
    <div class="dashboard-panel-body inquiry-create-body">
        <div class="inquiry-create-layout">
            <div class="inquiry-create-main">
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
                                <p class="inquiry-dup-subtext">{{ $isEdit ? 'Would you like to update anyway?' : 'Would you like to create another inquiry for the same company?' }}</p>
                                <div class="inquiry-dup-actions">
                                    <button type="button" class="inquiries-btn inquiries-btn-secondary" data-dup-close="1">Cancel</button>
                                    <button type="button" class="inquiries-btn inquiries-btn-primary" id="dupConfirmBtn">{{ $isEdit ? 'Confirm & Update' : 'Confirm & Add' }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ $isEdit ? route('admin.inquiries.update', $inquiry->LEADID) : route('admin.inquiries.store') }}" class="inquiry-form" id="inquiryForm">
                    @csrf
                    @if ($isEdit)
                        @method('PUT')
                        <input type="hidden" name="INQUIRY_SNAPSHOT_AT" value="{{ $inquiry->SNAPSHOT_MODIFIED_AT ?? $inquiry->snapshot_modified_at ?? '' }}">
                        @if(request()->query('tab'))
                            <input type="hidden" name="return_tab" value="{{ request()->query('tab') }}">
                        @endif
                    @endif
            <div class="inquiry-form-body" id="inquiryFormGrid">
                <!-- Section 1: Company Information -->
                <div class="inquiry-form-section" style="position: relative;">
                    <img src="{{ asset('NewInquiries-FoxIcon.png') }}" class="inquiry-create-fox-img" alt="Mascot">
                    <div class="inquiry-form-section-header">
                        <div>
                            <h3 class="inquiry-form-section-title">Company Information</h3>
                        </div>
                    </div>
                    <div class="inquiry-form-grid">
                        <div class="inquiry-form-label inquiry-company-field" style="grid-column: span 10 !important;">
                            <label for="companyInput" class="inquiry-form-label-title">Company name <span class="required">*</span></label>
                            <div class="inquiry-form-input-wrapper">
                                <i class="bi bi-building inquiry-input-icon"></i>
                                <input type="text" name="COMPANYNAME" id="companyInput" value="{{ old('COMPANYNAME', $inquiry->COMPANYNAME ?? '') }}" maxlength="200" required class="inquiry-form-input has-icon" placeholder="Enter company name" autocomplete="off">
                                <button type="button" class="inquiry-lookup-btn" id="lookupCompanyBtn" title="Load existing company data">
                                    <i class="bi bi-search" style="font-size: 11px; font-weight: 800;"></i>
                                </button>
                            </div>
                        </div>
                        <div style="grid-column: span 2 !important;"></div>
                        <label class="inquiry-form-label" style="grid-column: span 4 !important;">
                            <span class="inquiry-form-label-title">Business nature <span class="required">*</span></span>
                            <div class="inquiry-form-input-wrapper">
                                <i class="bi bi-briefcase inquiry-input-icon"></i>
                                <input type="text" name="BUSINESSNATURE" value="{{ old('BUSINESSNATURE', $inquiry->BUSINESSNATURE ?? '') }}" required maxlength="255" class="inquiry-form-input has-icon" placeholder="Enter business nature">
                            </div>
                        </label>

                        <label class="inquiry-form-label" style="grid-column: span 3 !important;">
                            <span class="inquiry-form-label-title">Existing software <span class="required">*</span></span>
                            <div class="inquiry-form-input-wrapper">
                                <i class="bi bi-window-stack inquiry-input-icon"></i>
                                <input type="text" name="EXISTINGSOFTWARE" value="{{ old('EXISTINGSOFTWARE', $inquiry->EXISTINGSOFTWARE ?? '') }}" required maxlength="255" class="inquiry-form-input has-icon" placeholder="Enter existing software">
                            </div>
                        </label>

                        <label class="inquiry-form-label" style="grid-column: span 3 !important;">
                            <span class="inquiry-form-label-title">User count <span class="required">*</span></span>
                            <div class="inquiry-form-input-wrapper">
                                <i class="bi bi-people inquiry-input-icon"></i>
                                <input type="number" name="USERCOUNT" value="{{ old('USERCOUNT', $inquiry->USERCOUNT ?? '1') }}" required min="1" class="inquiry-form-input has-icon" placeholder="1">
                            </div>
                        </label>
                        <div style="grid-column: span 2 !important;"></div>
                    </div>
                </div>

                <!-- Section 2: Contact Details -->
                <div class="inquiry-form-section">
                    <div class="inquiry-form-section-header">
                        <div>
                            <h3 class="inquiry-form-section-title">Contact Details</h3>
                        </div>
                    </div>
                    <div class="inquiry-form-grid">
                        <label class="inquiry-form-label third">
                            <span class="inquiry-form-label-title">Email <span class="required">*</span></span>
                            <div class="inquiry-form-input-wrapper">
                                <i class="bi bi-envelope inquiry-input-icon"></i>
                                <input type="email" name="EMAIL" value="{{ old('EMAIL', $inquiry->EMAIL ?? '') }}" required maxlength="255" class="inquiry-form-input has-icon" placeholder="example@company.com">
                            </div>
                        </label>
                        <label class="inquiry-form-label third">
                            <span class="inquiry-form-label-title">Contact name <span class="required">*</span></span>
                            <div class="inquiry-form-input-wrapper">
                                <i class="bi bi-person inquiry-input-icon"></i>
                                <input type="text" name="CONTACTNAME" value="{{ old('CONTACTNAME', $inquiry->CONTACTNAME ?? '') }}" required maxlength="255" class="inquiry-form-input has-icon" placeholder="Enter contact name">
                            </div>
                        </label>
                        <label class="inquiry-form-label third">
                            <span class="inquiry-form-label-title">Contact no <span class="required">*</span></span>
                            <div class="inquiry-form-input-wrapper">
                                <i class="bi bi-telephone inquiry-input-icon"></i>
                                <input type="text" name="CONTACTNO" value="{{ old('CONTACTNO', $inquiry->CONTACTNO ?? '') }}" required maxlength="15" class="inquiry-form-input has-icon @error('CONTACTNO') inquiry-input-error @enderror" placeholder="012-3456789">
                            </div>
                            @error('CONTACTNO')
                                <div class="inquiry-field-error">{{ $message }}</div>
                            @enderror
                        </label>
                    </div>
                </div>

                <!-- Section 3: Address -->
                <div class="inquiry-form-section">
                    <div class="inquiry-form-section-header">
                        <div>
                            <h3 class="inquiry-form-section-title">Address</h3>
                        </div>
                    </div>
                    <div class="inquiry-form-grid">
                        <div class="inquiry-form-label full">
                            <label for="address1Input" class="inquiry-form-label-title">Address 1 <span class="required">*</span></label>
                            <div class="inquiry-form-input-wrapper">
                                <i class="bi bi-building inquiry-input-icon"></i>
                                <input type="text" id="address1Input" name="ADDRESS1" value="{{ old('ADDRESS1', $inquiry->ADDRESS1 ?? '') }}" maxlength="255" class="inquiry-form-input has-icon" placeholder="Enter address line 1">
                                @php
                                    $address2Value = old('ADDRESS2', $inquiry->ADDRESS2 ?? '');
                                    $hasAddress2Value = trim((string) $address2Value) !== '';
                                @endphp
                                <button type="button" class="inquiry-inline-toggle" id="address2ToggleBtn" aria-controls="address2FieldWrap" aria-expanded="{{ $hasAddress2Value ? 'true' : 'false' }}" style="right: 12px; top: 50%; transform: translateY(-50%); position: absolute; font-size: 0.8rem; font-weight: 600;">
                                    {{ $hasAddress2Value ? '- Remove Address 2' : '+ Add Address 2' }}
                                </button>
                            </div>
                        </div>

                        <div class="inquiry-address2-wrap full{{ $hasAddress2Value ? ' is-visible' : '' }}" id="address2FieldWrap" {{ $hasAddress2Value ? '' : 'hidden' }}>
                            <div class="inquiry-form-label" style="width: 100%;">
                                <label for="address2Input" class="inquiry-form-label-title">Address 2</label>
                                <div class="inquiry-form-input-wrapper">
                                    <i class="bi bi-building inquiry-input-icon"></i>
                                    <input type="text" name="ADDRESS2" id="address2Input" value="{{ $address2Value }}" maxlength="255" class="inquiry-form-input has-icon" placeholder="Enter address line 2">
                                </div>
                            </div>
                        </div>

                        <label class="inquiry-form-label quarter" for="postcodeInput">
                            <span class="inquiry-form-label-title">Post code <span class="required">*</span></span>
                            <div class="inquiry-form-input-wrapper">
                                <i class="bi bi-mailbox inquiry-input-icon"></i>
                                <input type="text" id="postcodeInput" name="POSTCODE" value="{{ old('POSTCODE', $inquiry->POSTCODE ?? '') }}" required maxlength="5" inputmode="numeric" class="inquiry-form-input has-icon" placeholder="e.g. 53300">
                            </div>
                        </label>

                        <div class="inquiry-form-label quarter">
                            <span class="inquiry-form-label-title">City <span class="required">*</span></span>
                            <div class="inquiry-city-input-wrap">
                                <div class="inquiry-form-input-wrapper">
                                    <i class="bi bi-geo inquiry-input-icon"></i>
                                    <input type="text" id="cityInput" name="CITY" value="{{ old('CITY', $inquiry->CITY ?? '') }}" required maxlength="100" class="inquiry-form-input has-icon" placeholder="Enter city">
                                </div>
                                <div id="googleMapsWrap" style="display: none;">
                                    <a href="#" id="googleMapsBtn" target="_blank" title="View on Google Maps" class="google-maps-btn">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 92.3 132.3" style="height: 18px; width: auto;"><path fill="#1a73e8" d="M60.2 2.2C55.8.8 51 0 46.1 0 32 0 19.3 6.4 10.8 16.5l21.8 18.3L60.2 2.2z"/><path fill="#ea4335" d="M10.8 16.5C4.1 24.5 0 34.9 0 46.1c0 8.7 1.7 15.7 4.6 22l28-33.3-21.8-18.3z"/><path fill="#4285f4" d="M46.2 28.5c9.8 0 17.7 7.9 17.7 17.7 0 4.3-1.6 8.3-4.2 11.4 0 0 13.9-16.6 27.5-32.7-5.6-10.8-15.3-19-27-22.7L32.6 34.8c3.3-3.8 8.1-6.3 13.6-6.3"/><path fill="#fbbc04" d="M46.2 63.8c-9.8 0-17.7-7.9-17.7-17.7 0-4.3 1.5-8.3 4.1-11.3l-28 33.3c4.8 10.6 12.8 19.2 21 29.9l34.1-40.5c-3.3 3.9-8.1 6.3-13.5 6.3"/><path fill="#34a853" d="M59.1 109.2c15.4-24.1 33.3-35 33.3-63 0-7.7-1.9-14.9-5.2-21.3L25.6 98c2.6 3.4 5.3 7.3 7.9 11.3 9.4 14.5 6.8 23.1 12.8 23.1s3.4-8.7 12.8-23.2"/></svg>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <label class="inquiry-form-label quarter">
                            <span class="inquiry-form-label-title">State</span>
                            <div class="inquiry-form-input-wrapper">
                                <i class="bi bi-map inquiry-input-icon"></i>
                                <input type="text" id="stateInput" name="STATE" value="{{ old('STATE', $inquiry->STATE ?? '') }}" maxlength="100" class="inquiry-form-input has-icon" placeholder="Enter state">
                            </div>
                        </label>

                        <label class="inquiry-form-label quarter">
                            <span class="inquiry-form-label-title">Country <span class="required">*</span></span>
                            <div class="inquiry-form-input-wrapper">
                                <i class="bi bi-globe inquiry-input-icon"></i>
                                <select id="countryInput" name="COUNTRY" class="inquiry-form-input has-icon">
                                    @php
                                        $currentCountry = old('COUNTRY', $inquiry->COUNTRY ?? '');
                                        if ($currentCountry === '' && !$isEdit) $currentCountry = 'Malaysia';
                                    @endphp
                                    <option value="Malaysia" {{ $currentCountry === 'Malaysia' ? 'selected' : '' }}>Malaysia</option>
                                    <option value="Singapore" {{ $currentCountry === 'Singapore' ? 'selected' : '' }}>Singapore</option>
                                </select>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Section 4: Inquiry Details -->
                <div class="inquiry-form-section" style="z-index: 20; position: relative;">
                    <div class="inquiry-form-section-header">
                        <div>
                            <h3 class="inquiry-form-section-title">Inquiry Details</h3>
                        </div>
                    </div>
                    <div class="inquiry-form-grid">
                        <!-- Left Side: Products (9 columns) -->
                        <div class="inquiry-form-label" style="grid-column: span 9;">
                            <span class="inquiry-form-label-title">Product interested <span class="required">*</span></span>
                            <div class="inquiry-form-checkboxes @error('product_interested') inquiry-input-error @enderror">
                                @php
                                    $defaultProducts = [];
                                    if ($isEdit && $inquiry && !empty($inquiry->PRODUCTID)) {
                                        $defaultProducts = array_map('intval', array_filter(explode(',', (string) $inquiry->PRODUCTID)));
                                    }
                                    $selectedProducts = old('product_interested', $defaultProducts);
                                @endphp
                                @foreach($productInterestedList ?? [] as $num => $label)
                                    <label class="inquiry-form-checkbox-label">
                                        <input type="checkbox" name="product_interested[]" value="{{ $num }}" {{ in_array($num, $selectedProducts) ? 'checked' : '' }} class="inquiry-form-checkbox" style="position:absolute;opacity:0;width:0;height:0;pointer-events:none;">
                                        {{ $label }}
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Right Side: Demo & Referral (3 columns) -->
                        <div style="grid-column: span 3; display: flex; flex-direction: column; gap: 16px;">
                            <div class="inquiry-form-label">
                                <span class="inquiry-form-label-title">Demo mode <span class="required">*</span></span>
                                @php
                                    $demoDefault = $isEdit && isset($inquiry->DEMOMODE) ? trim((string) $inquiry->DEMOMODE) : 'Zoom';
                                    if ($demoDefault !== 'Zoom' && $demoDefault !== 'On-site') $demoDefault = 'Zoom';
                                    $demoOld = old('DEMOMODE', $demoDefault);
                                @endphp
                                <div class="inquiry-toggle" data-toggle="demomode">
                                    <button type="button" class="inquiry-toggle-option {{ $demoOld === 'Zoom' ? 'is-active' : '' }}" data-value="Zoom">
                                        <i class="bi bi-camera-video"></i> Zoom
                                    </button>
                                    <button type="button" class="inquiry-toggle-option {{ $demoOld === 'On-site' ? 'is-active' : '' }}" data-value="On-site">
                                        <i class="bi bi-geo-alt"></i> On-site
                                    </button>
                                </div>
                                <input type="hidden" name="DEMOMODE" id="demoModeInput" value="{{ $demoOld }}">
                            </div>

                            <label class="inquiry-form-label">
                                <span class="inquiry-form-label-title">Referral code</span>
                                <div class="inquiry-form-input-wrapper">
                                    <i class="bi bi-ticket-perforated inquiry-input-icon"></i>
                                    <input type="text" name="REFERRALCODE" value="{{ old('REFERRALCODE', $inquiry->REFERRALCODE ?? '') }}" maxlength="100" class="inquiry-form-input has-icon" placeholder="Enter referral code">
                                </div>
                            </label>
                        </div>

                        <!-- Bottom: Message (12 columns) -->
                        <label class="inquiry-form-label full" style="margin-top: 8px;">
                            <span class="inquiry-form-label-title">Message</span>
                            <div class="inquiry-form-input-wrapper">
                                <i class="bi bi-chat-left-text inquiry-input-icon" style="top: 14px;"></i>
                                <textarea name="DESCRIPTION" rows="4" maxlength="4000" class="inquiry-form-input has-icon" placeholder="Type the customer message / notes..." style="padding-top: 10px;">{{ old('DESCRIPTION', $inquiry->DESCRIPTION ?? '') }}</textarea>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="inquiry-form-actions" style="margin-top: auto; padding-top: 10px; display: flex; justify-content: flex-end; gap: 12px; align-items: center; width: 100%;">
                    <a href="{{ route('admin.inquiries') }}" class="inquiry-form-cancel" style="padding: 10px 24px; border-radius: 12px; font-weight: 700; color: #666; text-decoration: none; transition: all 0.2s; min-width: 150px; text-align: center; border: 1px solid #e0e0e0; display: flex; align-items: center; justify-content: center; gap: 8px;">
                        <i class="bi bi-x-lg" style="font-size: 14px;"></i> Cancel
                    </a>
                    <button type="submit" class="login-primary-btn" style="padding: 10px 24px; border-radius: 12px; display: flex; align-items: center; justify-content: center; gap: 8px; font-weight: 700; border: none; transition: all 0.2s; min-width: 150px; box-shadow: 0 4px 12px rgba(99, 102, 241, 0.2);">
                        <i class="bi bi-send"></i> {{ $isEdit ? 'Update inquiry' : 'Save inquiry' }}
                    </button>
                </div>
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
    var inquiryPanel = document.querySelector('.inquiry-create-panel');
    var dashboardRoot = document.getElementById('dashboardRoot');

    if (dashboardRoot && inquiryPanel) {
        dashboardRoot.classList.add('inquiry-create-scroll');
    }

    var postcodeInput = document.getElementById('postcodeInput');
    var cityInput = document.getElementById('cityInput');
    var stateInput = document.getElementById('stateInput');
    var countryInput = document.getElementById('countryInput');
    var postcodeCityLookup = @json($postcodeCityLookup ?? []);
    var lastAutoFilledCity = '';
    var lastAutoFilledState = '';
    var lastAutoFilledCountry = '';

    function normalizePostcodeValue(value) {
        return String(value || '').replace(/\D+/g, '').slice(0, 5);
    }

    var googleMapsWrap = document.getElementById('googleMapsWrap');
    var googleMapsBtn = document.getElementById('googleMapsBtn');

    function updateGoogleMapsLink() {
        if (!cityInput || !postcodeInput || !googleMapsWrap || !googleMapsBtn) return;
        
        var city = cityInput.value.trim();
        var postcode = postcodeInput.value.trim();
        
        if (city) {
            var query = encodeURIComponent(postcode + ' ' + city);
            googleMapsBtn.href = 'https://www.google.com/maps/search/?api=1&query=' + query;
            googleMapsWrap.style.display = 'flex';
            googleMapsBtn.style.display = 'flex';
        } else {
            googleMapsWrap.style.display = 'none';
        }
    }

    function syncLocationFromPostcode() {
        if (!postcodeInput || !cityInput) return;

        var normalizedPostcode = normalizePostcodeValue(postcodeInput.value);
        if (postcodeInput.value !== normalizedPostcode) {
            postcodeInput.value = normalizedPostcode;
        }

        if (normalizedPostcode.length !== 5) {
            if (lastAutoFilledCity && cityInput.value === lastAutoFilledCity) { cityInput.value = ''; }
            if (lastAutoFilledState && stateInput && stateInput.value === lastAutoFilledState) { stateInput.value = ''; }
            if (lastAutoFilledCountry && countryInput && countryInput.value === lastAutoFilledCountry) { countryInput.value = ''; }
            lastAutoFilledCity = '';
            lastAutoFilledState = '';
            lastAutoFilledCountry = '';
        } else {
            var matched = postcodeCityLookup[normalizedPostcode] || null;
            if (!matched) {
                if (lastAutoFilledCity && cityInput.value === lastAutoFilledCity) { cityInput.value = ''; }
                if (lastAutoFilledState && stateInput && stateInput.value === lastAutoFilledState) { stateInput.value = ''; }
                if (lastAutoFilledCountry && countryInput && countryInput.value === lastAutoFilledCountry) { countryInput.value = ''; }
                lastAutoFilledCity = '';
                lastAutoFilledState = '';
                lastAutoFilledCountry = '';
            } else {
                var matchedCity = matched.city || '';
                var matchedState = matched.state || '';
                var matchedCountry = 'Malaysia';

                // City
                if (cityInput.value.trim() === '' || cityInput.value === lastAutoFilledCity) {
                    cityInput.value = matchedCity;
                    lastAutoFilledCity = matchedCity;
                } else if (cityInput.value.trim().toLowerCase() === matchedCity.toLowerCase()) {
                    lastAutoFilledCity = cityInput.value;
                }

                // State
                if (stateInput) {
                    if (stateInput.value.trim() === '' || stateInput.value === lastAutoFilledState) {
                        stateInput.value = matchedState;
                        lastAutoFilledState = matchedState;
                    } else if (stateInput.value.trim().toLowerCase() === matchedState.toLowerCase()) {
                        lastAutoFilledState = stateInput.value;
                    }
                }

                // Country
                if (countryInput) {
                    countryInput.value = matchedCountry;
                    lastAutoFilledCountry = matchedCountry;
                }
            }
        }
        updateGoogleMapsLink();
    }

    if (cityInput) {
        cityInput.addEventListener('input', updateGoogleMapsLink);
        cityInput.addEventListener('change', updateGoogleMapsLink);
    }

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

        function closeDup() {
            if (modal) modal.hidden = true;
            if (form) {
                var existing = form.querySelector('input[name="duplicate_ok"]');
                if (existing) existing.remove();
            }
        }

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
    var lookupBtn = document.getElementById('lookupCompanyBtn');
    var lastCompanyData = null;
    var lookupTimer = null;
    var lookupUrl = "{{ route('admin.inquiries.company-lookup') }}";

    function performLookup() {
        var val = (companyInput.value || '').trim();
        if (val.length < 2) {
            if (lookupBtn) lookupBtn.classList.remove('is-visible');
            return;
        }

        if (lookupTimer) clearTimeout(lookupTimer);
        lookupTimer = setTimeout(function() {
            fetch(lookupUrl + '?q=' + encodeURIComponent(val))
                .then(response => response.json())
                .then(data => {
                    if (data && data.found) {
                        lastCompanyData = data;
                        if (lookupBtn) lookupBtn.classList.add('is-visible');
                    } else {
                        lastCompanyData = null;
                        if (lookupBtn) lookupBtn.classList.remove('is-visible');
                    }
                })
                .catch(err => {
                    console.error('Lookup error:', err);
                    if (lookupBtn) lookupBtn.classList.remove('is-visible');
                });
        }, 500);
    }

    if (companyInput) {
        companyInput.addEventListener('input', performLookup);
    }

    if (lookupBtn) {
        lookupBtn.addEventListener('click', function() {
            if (!lastCompanyData) return;
            
            // Auto-fill fields if they are empty
            var fields = {
                'BUSINESSNATURE': 'businessnature',
                'EXISTINGSOFTWARE': 'existingsoftware',
                'USERCOUNT': 'usercount',
                'ADDRESS1': 'address1',
                'ADDRESS2': 'address2',
                'POSTCODE': 'postcode',
                'CITY': 'city',
                'STATE': 'state',
                'COUNTRY': 'country',
                'CONTACTNAME': 'contactname',
                'CONTACTNO': 'contactno',
                'EMAIL': 'email'
            };

            for (var key in fields) {
                var input = document.querySelector('[name="' + key + '"]');
                if (input && lastCompanyData[fields[key]]) {
                    // Fill only if empty
                    if (!input.value || input.value.trim() === '') {
                        input.value = lastCompanyData[fields[key]];
                        // Trigger events for postcode lookup etc.
                        input.dispatchEvent(new Event('input'));
                        input.dispatchEvent(new Event('change'));
                    }
                }
            }

            // Handle Address 2 visibility specifically
            if (lastCompanyData.address2 && lastCompanyData.address2.trim() !== '') {
                setAddress2Expanded(true);
            }

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
            
            // Hide button after loading
            lookupBtn.classList.remove('is-visible');
        });
    }

    var inquiryPanelBody = document.querySelector('.inquiry-create-panel .dashboard-panel-body.inquiry-create-body');
    var inquiryFormGrid = document.getElementById('inquiryFormGrid');
    var address2ToggleBtn = document.getElementById('address2ToggleBtn');
    var address2FieldWrap = document.getElementById('address2FieldWrap');
    var address2Input = document.getElementById('address2Input');

    function syncPhoneStackMode() {
        if (!inquiryPanel || !inquiryPanelBody || !inquiryFormGrid) return;

        var viewportWidth = window.innerWidth || document.documentElement.clientWidth || 0;
        var panelWidth = inquiryPanelBody.getBoundingClientRect().width || inquiryPanelBody.clientWidth || 0;
        var shouldPhoneStack = panelWidth > 0 && panelWidth <= 560 && viewportWidth <= 1024;

        inquiryPanel.classList.toggle('inquiry-create-panel--phone-stack', shouldPhoneStack);
        inquiryFormGrid.classList.toggle('is-phone-stack', shouldPhoneStack);
    }

    syncPhoneStackMode();
    window.addEventListener('resize', syncPhoneStackMode);
    window.addEventListener('orientationchange', syncPhoneStackMode);

    function setAddress2Expanded(expanded) {
        if (!address2ToggleBtn || !address2FieldWrap) return;

        address2ToggleBtn.textContent = expanded ? 'â€“ Remove Address 2' : '+ Add Address 2';
        address2ToggleBtn.setAttribute('aria-expanded', expanded ? 'true' : 'false');
        address2ToggleBtn.textContent = expanded ? '- Remove Address 2' : '+ Add Address 2';

        if (expanded) {
            if (inquiryFormGrid) {
                inquiryFormGrid.classList.add('has-address2');
            }
            address2FieldWrap.hidden = false;
            requestAnimationFrame(function () {
                address2FieldWrap.classList.add('is-visible');
            });
            if (address2Input) {
                setTimeout(function () {
                    address2Input.focus();
                }, 140);
            }
            return;
        }

        address2FieldWrap.classList.remove('is-visible');
        if (inquiryFormGrid) {
            inquiryFormGrid.classList.remove('has-address2');
        }
        if (address2Input) {
            address2Input.value = '';
        }
        window.setTimeout(function () {
            if (!address2FieldWrap.classList.contains('is-visible')) {
                address2FieldWrap.hidden = true;
            }
        }, 220);
    }

    if (address2ToggleBtn && address2FieldWrap) {
        address2ToggleBtn.textContent = address2ToggleBtn.getAttribute('aria-expanded') === 'true'
            ? '- Remove Address 2'
            : '+ Add Address 2';
        if (inquiryFormGrid && address2ToggleBtn.getAttribute('aria-expanded') === 'true') {
            inquiryFormGrid.classList.add('has-address2');
        }
        address2ToggleBtn.addEventListener('click', function () {
            var expanded = address2ToggleBtn.getAttribute('aria-expanded') === 'true';
            setAddress2Expanded(!expanded);
        });
    }

    if (postcodeInput) {
        postcodeInput.addEventListener('input', syncLocationFromPostcode);
        postcodeInput.addEventListener('change', syncLocationFromPostcode);
        syncLocationFromPostcode();
    }




});
</script>
@endpush
