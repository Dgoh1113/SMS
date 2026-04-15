@extends('layouts.app')
@php $isEdit = isset($inquiry); $inquiry = $inquiry ?? null; @endphp
@section('title', $isEdit ? 'Edit inquiry – Admin' : 'Add new inquiry – Admin')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/admin-inquiries.css') }}?v=20260327-41">
    <style>
        .inquiry-create-panel {
            max-width: 1280px;
            margin: 20px auto;
            position: relative;
            overflow: hidden;
            background: #ffffff;
            border: 1px solid #e9eaf2;
            border-radius: 16px;
            box-shadow: 0 6px 18px rgba(30, 41, 59, 0.05);
        }

        .inquiry-create-panel--new .dashboard-panel-body.inquiry-create-body {
            padding-left: 76px !important;
        }

        .vertical-title {
            position: absolute;
            top: 20px;
            left: 16px;
            z-index: 2;
            writing-mode: vertical-rl;
            transform: rotate(180deg);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 14px 8px;
            border-radius: 18px;
            background: linear-gradient(180deg, #f3edff, #ebe4ff);
            color: #7c5cff;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.18em;
            box-shadow: 0 4px 14px rgba(124, 92, 255, 0.12);
            pointer-events: none;
            white-space: nowrap;
        }

        .inquiry-create-panel .dashboard-panel-body.inquiry-create-body {
            padding: 18px 22px !important;
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
        }

        .inquiry-create-main {
            flex: 1 1 auto;
            min-width: 0;
        }

        .inquiry-create-panel .inquiry-create-fox {
            display: flex !important;
            flex: 0 0 136px !important;
            align-items: flex-start !important;
            justify-content: center !important;
            padding-top: 2px;
        }

        .inquiry-create-panel .inquiry-create-fox-img {
            display: block !important;
            max-width: 136px !important;
            height: auto !important;
        }

        .inquiry-create-panel #inquiryFormGrid {
            display: grid;
            grid-template-columns: repeat(12, minmax(0, 1fr));
            gap: 10px 14px !important;
            max-width: 100%;
        }

        .inquiry-create-panel #inquiryFormGrid > .company-name { grid-column: 1 / span 10 !important; }
        .inquiry-create-panel #inquiryFormGrid > .email { grid-column: 1 / span 4 !important; }
        .inquiry-create-panel #inquiryFormGrid > .contact-name { grid-column: 5 / span 4 !important; }
        .inquiry-create-panel #inquiryFormGrid > .contact-no { grid-column: 9 / span 4 !important; }
        .inquiry-create-panel #inquiryFormGrid > .business-nature { grid-column: 1 / span 4 !important; }
        .inquiry-create-panel #inquiryFormGrid > .existing-software { grid-column: 5 / span 3 !important; }
        .inquiry-create-panel #inquiryFormGrid > .user-count { grid-column: 9.5 / span 1 !important; }
        .inquiry-create-panel #inquiryFormGrid > .demo-mode { grid-column: 10 / span 3 !important; }
        .inquiry-create-panel #inquiryFormGrid > .address1 { grid-column: 1 / span 10 !important; }
        .inquiry-create-panel #inquiryFormGrid > .address2 { grid-column: 1 / span 10 !important; }
        .inquiry-create-panel #inquiryFormGrid > .post-code { grid-column: 1 / span 3 !important; }
        .inquiry-create-panel #inquiryFormGrid > .city { grid-column: 4 / span 5 !important; }
        .inquiry-create-panel #inquiryFormGrid > .product-interested { grid-column: 1 / span 12 !important; }
        .inquiry-create-panel #inquiryFormGrid > .referral-code { grid-column: 1 / span 12 !important; }
        .inquiry-create-panel #inquiryFormGrid > .message { grid-column: 1 / span 12 !important; }

        .inquiry-create-panel #inquiryFormGrid > .company-name,
        .inquiry-create-panel #inquiryFormGrid > .email,
        .inquiry-create-panel #inquiryFormGrid > .contact-name,
        .inquiry-create-panel #inquiryFormGrid > .contact-no,
        .inquiry-create-panel #inquiryFormGrid > .business-nature,
        .inquiry-create-panel #inquiryFormGrid > .existing-software,
        .inquiry-create-panel #inquiryFormGrid > .user-count,
        .inquiry-create-panel #inquiryFormGrid > .demo-mode,
        .inquiry-create-panel #inquiryFormGrid > .address1,
        .inquiry-create-panel #inquiryFormGrid > .address2,
        .inquiry-create-panel #inquiryFormGrid > .post-code,
        .inquiry-create-panel #inquiryFormGrid > .city,
        .inquiry-create-panel #inquiryFormGrid > .product-interested,
        .inquiry-create-panel #inquiryFormGrid > .referral-code,
        .inquiry-create-panel #inquiryFormGrid > .message,
        .inquiry-create-panel #inquiryFormGrid > .inquiry-actions-field {
            order: initial !important;
        }

        .inquiry-create-panel #inquiryFormGrid .inquiry-form-section-title {
            display: none !important;
        }

        .inquiry-create-panel #inquiryFormGrid .inquiry-form-label,
        .inquiry-create-panel #inquiryFormGrid .inquiry-address2-inner,
        .inquiry-create-panel #inquiryFormGrid .inquiry-postcode-mini-field,
        .inquiry-create-panel #inquiryFormGrid .inquiry-city-mini-field {
            display: flex;
            flex-direction: column;
            gap: 4px;
            min-width: 0;
            color: #2f3654;
        }

        .inquiry-create-panel #inquiryFormGrid > .post-code,
        .inquiry-create-panel #inquiryFormGrid > .city {
            min-width: 0;
        }

        .inquiry-create-panel #inquiryFormGrid .inquiry-form-label-title {
            display: inline-flex;
            align-items: center;
            gap: 3px;
            font-size: 14px;
            font-weight: 600;
            color: #2f3654;
            white-space: nowrap;
            line-height: 1.2;
        }

        .inquiry-create-panel #inquiryFormGrid .required {
            color: #ef4444;
        }

        .inquiry-create-panel #inquiryFormGrid .inquiry-form-input {
            width: 100%;
            height: 38px;
            box-sizing: border-box;
            border: 1px solid #d8dce8;
            border-radius: 10px;
            background: #fff;
            font-size: 14px;
            color: #2f3654;
            padding: 6px 12px;
            transition: all 0.2s ease;
        }

        .inquiry-create-panel #inquiryFormGrid .inquiry-form-input:focus {
            outline: none;
            border-color: #7c5cff;
            box-shadow: 0 0 0 3px rgba(124, 92, 255, 0.10);
        }

        .inquiry-create-panel #inquiryFormGrid textarea.inquiry-form-input {
            min-height: 72px !important;
            height: 72px !important;
            padding: 10px 12px 8px !important;
            resize: none;
        }

        .inquiry-create-panel #inquiryFormGrid.has-address2 {
            gap: 8px 14px !important;
        }

        .inquiry-create-panel #inquiryFormGrid.has-address2 .inquiry-form-label,
        .inquiry-create-panel #inquiryFormGrid.has-address2 .inquiry-address2-inner,
        .inquiry-create-panel #inquiryFormGrid.has-address2 .inquiry-postcode-mini-field,
        .inquiry-create-panel #inquiryFormGrid.has-address2 .inquiry-city-mini-field {
            gap: 3px;
        }

        .inquiry-create-panel #inquiryFormGrid.has-address2 textarea.inquiry-form-input {
            min-height: 60px !important;
            height: 60px !important;
            padding-top: 8px !important;
        }

        .inquiry-create-panel #inquiryFormGrid .inquiry-inline-toggle {
            display: inline-block;
            align-self: flex-start;
            margin-top: 2px;
            padding: 0;
            border: none;
            background: transparent;
            color: #7c5cff;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
        }

        .inquiry-create-panel #inquiryFormGrid .inquiry-inline-toggle:hover,
        .inquiry-create-panel #inquiryFormGrid .inquiry-inline-toggle:focus-visible {
            color: #6847f5;
            text-decoration: underline;
        }

        .inquiry-create-panel #inquiryFormGrid .inquiry-toggle {
            display: inline-flex;
            align-items: center;
            width: fit-content;
            height: 38px;
            padding: 3px;
            gap: 4px;
            border: 1px solid #d8dce8;
            border-radius: 10px;
            background: #f3f4f8;
            max-width: none;
        }

        .inquiry-create-panel #inquiryFormGrid .inquiry-toggle-option {
            border: none;
            background: transparent;
            color: #4b5563;
            font-size: 12px;
            font-weight: 600;
            padding: 5px 12px;
            border-radius: 8px;
            cursor: pointer;
            box-shadow: none;
            transition: all 0.2s ease;
        }

        .inquiry-create-panel #inquiryFormGrid .inquiry-toggle-option.is-active {
            background: linear-gradient(135deg, #8b5cf6, #6d3df2);
            color: #ffffff;
            box-shadow: none;
        }

        .inquiry-create-panel #inquiryFormGrid .inquiry-form-checkboxes {
            display: grid;
            grid-template-columns: repeat(6, minmax(0, 1fr));
            gap: 8px 16px;
            margin-top: 4px;
        }

        .inquiry-create-panel #inquiryFormGrid.has-address2 .inquiry-form-checkboxes {
            gap: 6px 14px;
            margin-top: 2px;
        }

        .inquiry-create-panel #inquiryFormGrid .inquiry-form-checkbox-label {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 0;
            font-size: 14px;
            font-weight: 500;
            color: #374151;
            line-height: 1.2;
            cursor: pointer;
        }

        .inquiry-create-panel #inquiryFormGrid .inquiry-form-checkbox {
            width: 15px;
            height: 15px;
            accent-color: #7c5cff;
            margin: 0;
            flex-shrink: 0;
            cursor: pointer;
        }

        .inquiry-create-panel #inquiryFormGrid > .inquiry-actions-field {
            grid-column: 1 / -1;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 10px;
            flex-wrap: nowrap;
            margin-top: 14px;
        }

        .inquiry-create-panel #inquiryFormGrid.has-address2 > .inquiry-actions-field {
            margin-top: 8px;
        }

        .inquiry-create-panel.inquiry-create-panel--phone-stack .inquiry-create-layout {
            flex-direction: column !important;
            gap: 14px !important;
        }

        .inquiry-create-panel.inquiry-create-panel--phone-stack .inquiry-create-main,
        .inquiry-create-panel.inquiry-create-panel--phone-stack .inquiry-create-fox {
            width: 100% !important;
            min-width: 0 !important;
        }

        .inquiry-create-panel.inquiry-create-panel--phone-stack .inquiry-create-fox {
            display: none !important;
        }

        .inquiry-create-panel #inquiryFormGrid.is-phone-stack {
            display: grid !important;
            grid-template-columns: minmax(0, 1fr) !important;
            gap: 12px !important;
            width: 100% !important;
        }

        .inquiry-create-panel #inquiryFormGrid.is-phone-stack > * {
            grid-column: 1 / -1 !important;
            width: 100% !important;
            max-width: 100% !important;
            min-width: 0 !important;
        }

        .inquiry-create-panel #inquiryFormGrid.is-phone-stack .inquiry-form-label-title {
            font-size: 13px;
            white-space: normal !important;
        }

        .inquiry-create-panel #inquiryFormGrid.is-phone-stack .inquiry-company-wrapper {
            display: grid !important;
            grid-template-columns: minmax(0, 1fr) !important;
            gap: 8px;
            width: 100% !important;
        }

        .inquiry-create-panel #inquiryFormGrid.is-phone-stack .inquiry-company-copy-btn:not([hidden]) {
            width: 100%;
        }

        .inquiry-create-panel #inquiryFormGrid.is-phone-stack .inquiry-toggle {
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            width: 100% !important;
            max-width: 100% !important;
            height: auto;
        }

        .inquiry-create-panel #inquiryFormGrid.is-phone-stack .inquiry-toggle-option {
            min-width: 0;
            padding: 8px 10px;
            white-space: normal;
        }

        .inquiry-create-panel #inquiryFormGrid.is-phone-stack .inquiry-form-checkboxes {
            grid-template-columns: 1fr !important;
            gap: 8px !important;
        }

        .inquiry-create-panel #inquiryFormGrid.is-phone-stack > .inquiry-actions-field {
            flex-direction: column;
            align-items: stretch;
        }

        .inquiry-create-panel #inquiryFormGrid.is-phone-stack > .inquiry-actions-field .login-primary-btn,
        .inquiry-create-panel #inquiryFormGrid.is-phone-stack > .inquiry-actions-field .inquiry-form-cancel {
            width: 100%;
            text-align: center;
        }

        @media (min-width: 860px) {
            .inquiry-create-panel #inquiryFormGrid > .inquiry-actions-field {
                width: calc(100% + 152px);
                margin-right: -152px;
                box-sizing: border-box;
                padding-right: 4px;
            }
        }

        @media (max-width: 768px) {
            .dashboard-root:has(.inquiry-create-panel) .dashboard-main {
                height: auto !important;
                min-height: 100vh !important;
                overflow-y: auto !important;
            }

            .dashboard-root:has(.inquiry-create-panel) .dashboard-main-body {
                flex: 0 0 auto !important;
                overflow: visible !important;
                padding-bottom: 28px !important;
            }

            .inquiry-create-panel {
                overflow: visible;
            }

            .inquiry-create-panel .inquiry-create-fox {
                display: none !important;
            }

            .vertical-title {
                display: none;
            }

            .inquiry-create-panel--new .dashboard-panel-body.inquiry-create-body {
                padding-left: 22px !important;
            }

            .inquiry-create-panel #inquiryFormGrid > .company-name,
            .inquiry-create-panel #inquiryFormGrid > .address1,
            .inquiry-create-panel #inquiryFormGrid > .address2 {
                grid-column: 1 / -1 !important;
            }

            .inquiry-create-panel #inquiryFormGrid > .inquiry-actions-field {
                width: 100% !important;
                margin-right: 0 !important;
                padding-right: 0 !important;
            }
        }

        .inquiry-create-panel #inquiryFormGrid > .inquiry-actions-field .login-primary-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 150px;
            height: 40px;
            padding: 0 18px;
            border: none;
            border-radius: 999px;
            background: linear-gradient(135deg, #8b5cf6, #6d3df2);
            box-shadow: 0 4px 12px rgba(109, 61, 242, 0.16);
            font-size: 13px;
            font-weight: 600;
            line-height: 1;
            white-space: nowrap;
            margin: 0;
            transition: all 0.2s ease;
        }

        .inquiry-create-panel #inquiryFormGrid > .inquiry-actions-field .login-primary-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 14px rgba(109, 61, 242, 0.20);
        }

        .inquiry-create-panel #inquiryFormGrid > .inquiry-actions-field .inquiry-form-cancel {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 110px;
            height: 40px;
            padding: 0 18px;
            border: 1px solid #d8dce8;
            border-radius: 999px;
            background: #ffffff;
            color: #4b5563;
            font-size: 13px;
            font-weight: 600;
            line-height: 1;
            white-space: nowrap;
            margin: 0;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .inquiry-create-panel #inquiryFormGrid > .inquiry-actions-field .inquiry-form-cancel:hover {
            border-color: #c9cfdf;
            background: #f8f9fc;
            color: #374151;
            text-decoration: none;
        }

        @media (min-width: 1024px) and (max-height: 920px) {
            .dashboard-root.inquiry-create-scroll .dashboard-main-body {
                padding-bottom: 8px !important;
            }

            .inquiry-create-panel {
                margin: 6px auto;
            }

            .inquiry-create-panel--new .dashboard-panel-body.inquiry-create-body {
                padding-left: 58px !important;
            }

            .vertical-title {
                top: 12px;
                left: 10px;
                padding: 10px 6px;
                font-size: 10px;
                letter-spacing: 0.14em;
            }

            .inquiry-create-panel .dashboard-panel-body.inquiry-create-body {
                padding: 12px 16px !important;
            }

            .inquiry-create-layout {
                gap: 10px !important;
            }

            .inquiry-create-panel .inquiry-create-fox {
                flex: 0 0 96px !important;
                padding-top: 0;
            }

            .inquiry-create-panel .inquiry-create-fox-img {
                max-width: 96px !important;
            }

            .inquiry-create-panel #inquiryFormGrid {
                gap: 6px 10px !important;
            }

            .inquiry-create-panel #inquiryFormGrid .inquiry-form-label,
            .inquiry-create-panel #inquiryFormGrid .inquiry-address2-inner,
            .inquiry-create-panel #inquiryFormGrid .inquiry-postcode-mini-field,
            .inquiry-create-panel #inquiryFormGrid .inquiry-city-mini-field {
                gap: 2px;
            }

            .inquiry-create-panel #inquiryFormGrid .inquiry-form-label-title {
                font-size: 12px;
            }

            .inquiry-create-panel #inquiryFormGrid .inquiry-form-input {
                height: 32px;
                padding: 4px 9px;
                font-size: 12px;
            }

            .inquiry-create-panel #inquiryFormGrid textarea.inquiry-form-input {
                min-height: 48px !important;
                height: 48px !important;
                padding: 7px 9px 5px !important;
            }

            .inquiry-create-panel #inquiryFormGrid.has-address2 {
                gap: 5px 10px !important;
            }

            .inquiry-create-panel #inquiryFormGrid.has-address2 textarea.inquiry-form-input {
                min-height: 42px !important;
                height: 42px !important;
                padding-top: 5px !important;
            }

            .inquiry-create-panel #inquiryFormGrid .inquiry-inline-toggle {
                margin-top: 0;
                font-size: 10px;
            }

            .inquiry-create-panel #inquiryFormGrid .inquiry-toggle {
                height: 32px;
                padding: 2px;
            }

            .inquiry-create-panel #inquiryFormGrid .inquiry-toggle-option {
                font-size: 10px;
                padding: 3px 9px;
            }

            .inquiry-create-panel #inquiryFormGrid .inquiry-form-checkboxes {
                gap: 4px 10px;
                margin-top: 1px;
            }

            .inquiry-create-panel #inquiryFormGrid .inquiry-form-checkbox-label {
                gap: 4px;
                font-size: 12px;
                line-height: 1.1;
            }

            .inquiry-create-panel #inquiryFormGrid .inquiry-form-checkbox {
                width: 13px;
                height: 13px;
            }

            .inquiry-create-panel #inquiryFormGrid > .inquiry-actions-field {
                width: calc(100% + 120px);
                margin-right: -120px;
                gap: 8px;
                margin-top: 4px;
                padding-right: 0;
            }

            .inquiry-create-panel #inquiryFormGrid > .inquiry-actions-field .login-primary-btn {
                min-width: 130px;
                height: 34px;
                padding: 0 15px;
                font-size: 12px;
            }

            .inquiry-create-panel #inquiryFormGrid > .inquiry-actions-field .inquiry-form-cancel {
                min-width: 96px;
                height: 34px;
                padding: 0 14px;
                font-size: 12px;
            }
        }

        @media (min-width: 1024px) and (max-height: 820px) {
            .dashboard-root.inquiry-create-scroll .dashboard-main-body {
                padding-bottom: 2px !important;
            }

            .inquiry-create-panel.inquiry-create-panel--edit {
                margin: 2px auto;
            }

            .inquiry-create-panel.inquiry-create-panel--edit .dashboard-panel-body.inquiry-create-body {
                padding: 10px 14px !important;
            }

            .inquiry-create-panel.inquiry-create-panel--edit .vertical-title {
                top: 10px;
                left: 8px;
                padding: 8px 5px;
                font-size: 9px;
                letter-spacing: 0.12em;
            }

            .inquiry-create-panel.inquiry-create-panel--edit .inquiry-create-layout {
                gap: 8px !important;
            }

            .inquiry-create-panel.inquiry-create-panel--edit .inquiry-create-fox {
                flex: 0 0 82px !important;
            }

            .inquiry-create-panel.inquiry-create-panel--edit .inquiry-create-fox-img {
                max-width: 82px !important;
            }

            .inquiry-create-panel.inquiry-create-panel--edit #inquiryFormGrid {
                gap: 4px 8px !important;
            }

            .inquiry-create-panel.inquiry-create-panel--edit #inquiryFormGrid.has-address2 {
                gap: 4px 8px !important;
            }

            .inquiry-create-panel.inquiry-create-panel--edit #inquiryFormGrid .inquiry-form-label,
            .inquiry-create-panel.inquiry-create-panel--edit #inquiryFormGrid .inquiry-address2-inner,
            .inquiry-create-panel.inquiry-create-panel--edit #inquiryFormGrid .inquiry-postcode-mini-field,
            .inquiry-create-panel.inquiry-create-panel--edit #inquiryFormGrid .inquiry-city-mini-field {
                gap: 1px;
            }

            .inquiry-create-panel.inquiry-create-panel--edit #inquiryFormGrid .inquiry-form-label-title {
                font-size: 11px;
            }

            .inquiry-create-panel.inquiry-create-panel--edit #inquiryFormGrid .inquiry-form-input {
                height: 30px;
                padding: 3px 8px;
                font-size: 11.5px;
            }

            .inquiry-create-panel.inquiry-create-panel--edit #inquiryFormGrid textarea.inquiry-form-input {
                min-height: 40px !important;
                height: 40px !important;
                padding: 6px 8px 4px !important;
            }

            .inquiry-create-panel.inquiry-create-panel--edit #inquiryFormGrid.has-address2 textarea.inquiry-form-input {
                min-height: 36px !important;
                height: 36px !important;
                padding-top: 4px !important;
            }

            .inquiry-create-panel.inquiry-create-panel--edit #inquiryFormGrid .inquiry-inline-toggle {
                font-size: 9px;
            }

            .inquiry-create-panel.inquiry-create-panel--edit #inquiryFormGrid .inquiry-toggle {
                height: 30px;
                padding: 2px;
                gap: 3px;
            }

            .inquiry-create-panel.inquiry-create-panel--edit #inquiryFormGrid .inquiry-toggle-option {
                font-size: 9.5px;
                padding: 3px 8px;
            }

            .inquiry-create-panel.inquiry-create-panel--edit #inquiryFormGrid .inquiry-form-checkboxes {
                gap: 3px 9px;
                margin-top: 0;
            }

            .inquiry-create-panel.inquiry-create-panel--edit #inquiryFormGrid .inquiry-form-checkbox-label {
                gap: 4px;
                font-size: 11px;
                line-height: 1.05;
            }

            .inquiry-create-panel.inquiry-create-panel--edit #inquiryFormGrid .inquiry-form-checkbox {
                width: 12px;
                height: 12px;
            }

            .inquiry-create-panel.inquiry-create-panel--edit #inquiryFormGrid > .inquiry-actions-field {
                width: calc(100% + 96px);
                margin-right: -96px;
                gap: 7px;
                margin-top: 2px;
            }

            .inquiry-create-panel.inquiry-create-panel--edit #inquiryFormGrid > .inquiry-actions-field .login-primary-btn {
                min-width: 122px;
                height: 32px;
                padding: 0 14px;
                font-size: 11.5px;
            }

            .inquiry-create-panel.inquiry-create-panel--edit #inquiryFormGrid > .inquiry-actions-field .inquiry-form-cancel {
                min-width: 90px;
                height: 32px;
                padding: 0 13px;
                font-size: 11.5px;
            }
        }

        @media (max-width: 768px) {
            .inquiry-create-panel {
                max-width: none;
                margin: 12px;
            }

            .inquiry-create-panel--new .dashboard-panel-body.inquiry-create-body {
                padding-left: 18px !important;
            }

            .vertical-title {
                display: none;
            }

            .inquiry-create-panel .dashboard-panel-body.inquiry-create-body {
                padding: 18px !important;
            }

            .inquiry-create-panel .inquiry-create-layout {
                flex-direction: column !important;
                gap: 14px !important;
            }

            .inquiry-create-panel .inquiry-create-main,
            .inquiry-create-panel .inquiry-create-fox {
                width: 100% !important;
                min-width: 0 !important;
            }

            .inquiry-create-panel #inquiryFormGrid {
                display: grid !important;
                grid-template-columns: minmax(0, 1fr) !important;
                gap: 12px !important;
                width: 100% !important;
            }

            .inquiry-create-panel #inquiryFormGrid > * {
                grid-column: 1 / -1 !important;
                width: 100% !important;
                max-width: 100% !important;
                min-width: 0 !important;
            }

            .inquiry-create-panel .inquiry-create-fox {
                display: none !important;
            }

            .inquiry-create-panel #inquiryFormGrid .inquiry-form-checkboxes {
                grid-template-columns: 1fr;
            }

            .inquiry-create-panel #inquiryFormGrid > .inquiry-actions-field {
                flex-direction: column;
                align-items: stretch;
            }

            .inquiry-create-panel #inquiryFormGrid > .inquiry-actions-field .login-primary-btn,
            .inquiry-create-panel #inquiryFormGrid > .inquiry-actions-field .inquiry-form-cancel {
                width: 100%;
                text-align: center;
            }
        }

        @media (max-width: 768px) {
            .inquiry-create-panel--new {
                margin: 8px 0 16px;
                border-radius: 14px;
            }

            .inquiry-create-panel--new .dashboard-panel-body.inquiry-create-body {
                padding: 16px 14px 18px !important;
            }

            .inquiry-create-panel--new #inquiryFormGrid {
                grid-template-columns: minmax(0, 1fr) !important;
                gap: 12px !important;
                width: 100%;
            }

            .inquiry-create-panel--new #inquiryFormGrid > * {
                grid-column: 1 / -1 !important;
                width: 100% !important;
                min-width: 0 !important;
                max-width: 100% !important;
            }

            .inquiry-create-panel--new #inquiryFormGrid .inquiry-form-label-title {
                font-size: 13px;
                white-space: normal !important;
            }

            .inquiry-create-panel--new #inquiryFormGrid .inquiry-company-wrapper {
                display: grid;
                grid-template-columns: minmax(0, 1fr);
                gap: 8px;
                width: 100%;
            }

            .inquiry-create-panel--new #inquiryFormGrid .inquiry-company-copy-btn:not([hidden]) {
                width: 100%;
            }

            .inquiry-create-panel--new #inquiryFormGrid .inquiry-toggle {
                display: grid !important;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                width: 100% !important;
                max-width: 100% !important;
                height: auto;
            }

            .inquiry-create-panel--new #inquiryFormGrid .inquiry-toggle-option {
                min-width: 0;
                padding: 8px 10px;
                white-space: normal;
            }

            .inquiry-create-panel--new #inquiryFormGrid .inquiry-form-checkboxes {
                grid-template-columns: 1fr !important;
                gap: 8px !important;
            }

            .inquiry-create-panel--new #inquiryFormGrid > .inquiry-actions-field {
                margin-top: 4px;
            }
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
                    @endif
            <div class="inquiry-form-grid form-grid" id="inquiryFormGrid">
                <label class="inquiry-form-label inquiry-company-field company-name">
                    <span class="inquiry-form-label-title">Company name <span class="required">*</span></span>
                    <div class="inquiry-company-wrapper">
                        <input type="text" name="COMPANYNAME" id="companyInput" value="{{ old('COMPANYNAME', $inquiry->COMPANYNAME ?? '') }}" required maxlength="255" class="inquiry-form-input">
                        <button type="button" class="inquiry-company-copy-btn" id="copyCompanyBtn" title="Copy from existing lead" hidden>
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </button>
                    </div>
                </label>
                <label class="inquiry-form-label inquiry-email-field email">
                    <span class="inquiry-form-label-title">Email <span class="required">*</span></span>
                    <input type="email" name="EMAIL" value="{{ old('EMAIL', $inquiry->EMAIL ?? '') }}" required maxlength="255" class="inquiry-form-input">
                </label>

                <label class="inquiry-form-label inquiry-contactname-field contact-name">
                    <span class="inquiry-form-label-title">Contact name <span class="required">*</span></span>
                    <input type="text" name="CONTACTNAME" value="{{ old('CONTACTNAME', $inquiry->CONTACTNAME ?? '') }}" required maxlength="255" class="inquiry-form-input">
                </label>
                <label class="inquiry-form-label inquiry-contactno-field contact-no">
                    <span class="inquiry-form-label-title">Contact no <span class="required">*</span></span>
                    <input type="text" name="CONTACTNO" value="{{ old('CONTACTNO', $inquiry->CONTACTNO ?? '') }}" required maxlength="15" class="inquiry-form-input @error('CONTACTNO') inquiry-input-error @enderror">
                    @error('CONTACTNO')
                        <div class="inquiry-field-error">{{ $message }}</div>
                    @enderror
                </label>

                <label class="inquiry-form-label inquiry-businessnature-field business-nature">
                    <span class="inquiry-form-label-title">Business nature <span class="required">*</span></span>
                    <input type="text" name="BUSINESSNATURE" value="{{ old('BUSINESSNATURE', $inquiry->BUSINESSNATURE ?? '') }}" required maxlength="255" class="inquiry-form-input">
                </label>
                <label class="inquiry-form-label inquiry-existingsoftware-field existing-software">
                    <span class="inquiry-form-label-title">Existing software <span class="required">*</span></span>
                    <input type="text" name="EXISTINGSOFTWARE" value="{{ old('EXISTINGSOFTWARE', $inquiry->EXISTINGSOFTWARE ?? '') }}" required maxlength="255" class="inquiry-form-input">
                </label>
                <label class="inquiry-form-label inquiry-usercount-field user-count">
                    <span class="inquiry-form-label-title">User count</span>
                    @php
                        $ucDefault = $isEdit && isset($inquiry->USERCOUNT) ? (int) $inquiry->USERCOUNT : 1;
                        if ($ucDefault < 1) $ucDefault = 1;
                        $uc = (int) old('USERCOUNT', $ucDefault);
                        if ($uc < 1) $uc = 1;
                    @endphp
                    <input type="number" name="USERCOUNT" value="{{ $uc }}" min="1" class="inquiry-form-input">
                </label>
                <label class="inquiry-form-label inquiry-demomode-field demo-mode">
                    <span class="inquiry-form-label-title">Demo mode</span>
                    @php
                        $demoDefault = $isEdit && isset($inquiry->DEMOMODE) ? trim((string) $inquiry->DEMOMODE) : 'Zoom';
                        if ($demoDefault !== 'Zoom' && $demoDefault !== 'On-site') $demoDefault = 'Zoom';
                        $demoOld = old('DEMOMODE', $demoDefault);
                    @endphp
                    <div class="inquiry-toggle" data-toggle="demomode">
                        <button type="button" class="inquiry-toggle-option {{ $demoOld === 'Zoom' ? 'is-active' : '' }}" data-value="Zoom">Zoom</button>
                        <button type="button" class="inquiry-toggle-option {{ $demoOld === 'On-site' ? 'is-active' : '' }}" data-value="On-site">On-site</button>
                    </div>
                    <input type="hidden" name="DEMOMODE" id="demoModeInput" value="{{ $demoOld }}">
                </label>
                @php
                    $address2Value = old('ADDRESS2', $inquiry->ADDRESS2 ?? '');
                    $hasAddress2Value = trim((string) $address2Value) !== '';
                @endphp
                <div class="inquiry-form-label inquiry-address-field inquiry-address1-field address1">
                    <label for="address1Input" class="inquiry-form-label-title">Address 1</label>
                    <input type="text" id="address1Input" name="ADDRESS1" value="{{ old('ADDRESS1', $inquiry->ADDRESS1 ?? '') }}" maxlength="255" class="inquiry-form-input">
                    <button type="button"
                            class="inquiry-inline-toggle"
                            id="address2ToggleBtn"
                            aria-controls="address2FieldWrap"
                            aria-expanded="{{ $hasAddress2Value ? 'true' : 'false' }}">
                        {{ $hasAddress2Value ? '– Remove Address 2' : '+ Add Address 2' }}
                    </button>
                </div>
                <div class="inquiry-address2-wrap inquiry-address2-field address2{{ $hasAddress2Value ? ' is-visible' : '' }}"
                     id="address2FieldWrap"
                     {{ $hasAddress2Value ? '' : 'hidden' }}>
                    <div class="inquiry-address2-inner inquiry-form-label">
                        <label for="address2Input" class="inquiry-form-label-title">Address 2</label>
                        <input type="text"
                               name="ADDRESS2"
                               id="address2Input"
                               value="{{ $address2Value }}"
                               maxlength="255"
                               class="inquiry-form-input">
                    </div>
                </div>
                <div class="inquiry-postcode-city-row post-code">
                    <label class="inquiry-postcode-mini-field" for="postcodeInput">
                        <span class="inquiry-form-label-title">Post code <span class="required">*</span></span>
                        <input type="text" id="postcodeInput" name="POSTCODE" value="{{ old('POSTCODE', $inquiry->POSTCODE ?? '') }}" required maxlength="5" inputmode="numeric" autocomplete="postal-code" class="inquiry-form-input @error('POSTCODE') inquiry-input-error @enderror">
                        @error('POSTCODE')
                            <div class="inquiry-field-error">{{ $message }}</div>
                        @enderror
                    </label>
                </div>
                <div class="inquiry-postcode-city-row city">
                    <label class="inquiry-city-mini-field" for="cityInput">
                        <span class="inquiry-form-label-title">City <span class="required">*</span></span>
                        <input type="text" id="cityInput" name="CITY" value="{{ old('CITY', $inquiry->CITY ?? '') }}" required maxlength="100" autocomplete="address-level2" class="inquiry-form-input">
                    </label>
                </div>
                <div class="inquiry-form-label inquiry-form-products inquiry-products-field product-interested">
                    <span class="inquiry-form-label-title">Product interested <span class="required">*</span></span>
                    <div class="inquiry-form-checkboxes @error('product_interested') inquiry-input-error @enderror" role="group" aria-required="true">
                        @php
                            $defaultProducts = [];
                            if ($isEdit && $inquiry && !empty($inquiry->PRODUCTID)) {
                                $defaultProducts = array_map('intval', array_filter(explode(',', (string) $inquiry->PRODUCTID)));
                            }
                            $selectedProducts = old('product_interested', $defaultProducts);
                        @endphp
                        @foreach($productInterestedList ?? [] as $num => $label)
                            <label class="inquiry-form-checkbox-label">
                                <input type="checkbox" name="product_interested[]" value="{{ $num }}" {{ in_array($num, $selectedProducts) ? 'checked' : '' }} class="inquiry-form-checkbox">
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                    @error('product_interested')
                        <div class="inquiry-field-error">Please select at least one product.</div>
                    @enderror
                </div>
                <label class="inquiry-form-label inquiry-referral-field referral-code">
                    <span class="inquiry-form-label-title">Referral code</span>
                    <input type="text" name="REFERRALCODE" value="{{ old('REFERRALCODE', $inquiry->REFERRALCODE ?? '') }}" maxlength="100" class="inquiry-form-input">
                </label>
                <label class="inquiry-form-label inquiry-message-field message">
                    <span class="inquiry-form-label-title">Message</span>
                    <textarea name="DESCRIPTION" rows="5" maxlength="4000" class="inquiry-form-input" placeholder="Type the customer message / notes...">{{ old('DESCRIPTION', $inquiry->DESCRIPTION ?? '') }}</textarea>
                </label>
                <div class="inquiry-form-actions inquiry-form-actions-grid inquiry-actions-field">
                <a href="{{ route('admin.inquiries') }}" class="inquiry-form-cancel">Cancel</a>
                <button type="submit" class="login-primary-btn">{{ $isEdit ? 'Update inquiry' : 'Save inquiry' }}</button>
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
    var postcodeCityLookup = @json($postcodeCityLookup ?? []);
    var lastAutoFilledCity = '';

    function normalizePostcodeValue(value) {
        return String(value || '').replace(/\D+/g, '').slice(0, 5);
    }

    function syncCityFromPostcode() {
        if (!postcodeInput || !cityInput) return;

        var normalizedPostcode = normalizePostcodeValue(postcodeInput.value);
        if (postcodeInput.value !== normalizedPostcode) {
            postcodeInput.value = normalizedPostcode;
        }

        if (normalizedPostcode.length !== 5) {
            if (lastAutoFilledCity && cityInput.value === lastAutoFilledCity) {
                cityInput.value = '';
            }
            lastAutoFilledCity = '';
            return;
        }

        var matchedCity = postcodeCityLookup[normalizedPostcode] || '';
        if (!matchedCity) {
            if (lastAutoFilledCity && cityInput.value === lastAutoFilledCity) {
                cityInput.value = '';
            }
            lastAutoFilledCity = '';
            return;
        }

        if (cityInput.value.trim() === '' || cityInput.value === lastAutoFilledCity) {
            cityInput.value = matchedCity;
            lastAutoFilledCity = matchedCity;
            return;
        }

        if (cityInput.value.trim().toLowerCase() === matchedCity.toLowerCase()) {
            lastAutoFilledCity = cityInput.value;
        }
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
    var copyBtn = document.getElementById('copyCompanyBtn');
    var lastCompanyData = null;
    var lookupTimer = null;
    var lookupUrl = "{{ route('admin.inquiries.company-lookup') }}";

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

        address2ToggleBtn.textContent = expanded ? '– Remove Address 2' : '+ Add Address 2';
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

    if (postcodeInput && cityInput) {
        postcodeInput.addEventListener('input', syncCityFromPostcode);
        postcodeInput.addEventListener('change', syncCityFromPostcode);
        postcodeInput.addEventListener('blur', syncCityFromPostcode);
        syncCityFromPostcode();
    }

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

            if (typeof lastCompanyData.address2 === 'string' && lastCompanyData.address2.trim() !== '') {
                setAddress2Expanded(true);
                if (address2Input && (!address2Input.value || address2Input.value.trim() === '')) {
                    address2Input.value = lastCompanyData.address2;
                }
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
        });
    }
});
</script>
@endpush
