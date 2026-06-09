<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Company - SQL Sales Management System</title>
    <link rel="icon" type="image/png" href="{{ asset('sql-logo.png') }}?v=20260318">
    <script>
        (function () {
            try {
                if (localStorage.getItem('sqlsms-theme') === 'dark') {
                    document.documentElement.classList.add('theme-dark');
                }
            } catch (e) {}
        })();
    </script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v=20260417-01">
    <style>
        .company-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: 24px;
        }
        .company-select-btn {
            display: flex;
            align-items: center;
            width: 100%;
            padding: 16px 20px;
            background: #f0edff;
            border: 1px solid #e2d9ff;
            border-radius: 18px;
            cursor: pointer;
            text-align: left;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            outline: none;
            position: relative;
            gap: 16px;
        }
        .company-select-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(109, 61, 245, 0.08);
        }
        .company-select-btn:active {
            transform: translateY(0);
            box-shadow: none;
        }
        
        .company-icon-box {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
            background: #ffffff;
            border-radius: 12px;
            color: #6d3df5;
            font-size: 22px;
            flex-shrink: 0;
        }

        .company-info {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            text-align: left;
        }
        .company-name {
            font-weight: 700;
            color: #1f2933;
            font-size: 16px;
            margin-bottom: 2px;
        }
        .company-alias {
            font-size: 13px;
            color: #7b8794;
            font-weight: 500;
        }
        .company-role-dot {
            color: #6d3df5;
            margin: 0 4px;
            font-weight: bold;
        }

        .company-chevron-circle {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            background: #ffffff;
            border-radius: 50%;
            color: #6d3df5;
            font-size: 14px;
            flex-shrink: 0;
        }

        /* Dark theme support */
        html.theme-dark .company-select-btn {
            background: #251e4a;
            border-color: #372d6e;
        }
        html.theme-dark .company-icon-box {
            background: #1a153b;
            color: #a389ff;
        }
        html.theme-dark .company-name {
            color: #ffffff;
        }
        html.theme-dark .company-alias {
            color: #b2c0d4;
        }
        html.theme-dark .company-role-dot {
            color: #a389ff;
        }
        html.theme-dark .company-chevron-circle {
            background: #1a153b;
            color: #a389ff;
        }
    </style>
</head>
<body>
<div class="login-root">
    <header class="login-header">
        <div class="login-header-left">
            <img src="{{ asset('sql-logo.png') }}" alt="SQL company logo" class="login-header-logo-img">
            <div class="login-logo-text">SQL Sales Management System</div>
        </div>
    </header>

    <main class="login-main">
        <div class="login-main-layout">
            <div class="login-card login-card-passkey-login">
                <div class="login-logo">
                    <img src="{{ asset('sql-logo.png') }}" alt="SQL logo" class="login-logo-img">
                    <span class="login-logo-lms">SMS</span>
                </div>
                <p class="login-subtitle">Sales Management System</p>

                <div class="login-form login-form-passkey-login" style="align-items: stretch;">
                    <h2 style="font-size: 20px; font-weight: 600; text-align: center; color: var(--text-main); margin-bottom: 8px;">Select Company</h2>


                    @if (session('error'))
                        <div class="login-message login-error">{{ session('error') }}</div>
                    @endif

                    <form method="POST" action="{{ url('/login/select-company') }}" style="width: 100%;">
                        @csrf
                        <div class="company-list" style="width: 100%;">
                            @foreach($companies as $company)
                                @php
                                    $role = strtolower(trim($company->SYSTEMROLE ?? ''));
                                    $isDealer = ($role === 'dealer');
                                @endphp
                                <button type="submit" name="user_id" value="{{ $company->USERID ?? '' }}" class="company-select-btn">
                                    <div class="company-icon-box">
                                        @if($isDealer)
                                            <i class="bi bi-buildings"></i>
                                        @else
                                            <i class="bi bi-person-fill"></i>
                                        @endif
                                    </div>
                                    <div class="company-info">
                                        <span class="company-name">{{ $company->COMPANY ?? 'Unnamed Company' }}</span>
                                        <span class="company-alias">
                                            {{ $company->ALIAS ?? $email }}
                                            @if(!empty($company->SYSTEMROLE))
                                                <span class="company-role-dot">&bull;</span> {{ ucfirst(strtolower(trim($company->SYSTEMROLE))) }}
                                            @endif
                                        </span>
                                    </div>
                                    <div class="company-chevron-circle">
                                        <i class="bi bi-chevron-right"></i>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    </form>

                    <div style="margin-top: 24px; text-align: center;">
                        <a href="{{ route('login') }}" class="login-link-btn" style="color: var(--text-muted); font-size: 14px; text-decoration: underline;">Cancel and go back</a>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
</body>
</html>
