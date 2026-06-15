<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emergency Admin Setup - SQL Sales Management System</title>
    <link rel="icon" type="image/png" href="{{ asset('sql-logo.png') }}?v=20260318">
    <link rel="shortcut icon" href="{{ asset('sql-logo.png') }}?v=20260318">
    <link rel="apple-touch-icon" href="{{ asset('sql-logo.png') }}?v=20260318">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v=20260417-01">
    <style>
        .login-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            margin-top: 0.25rem;
            margin-bottom: 1rem;
            font-family: inherit;
        }
        .login-btn {
            width: 100%;
            padding: 0.75rem;
            background-color: #8b5cf6; /* Purple to match theme */
            color: white;
            border: none;
            border-radius: 0.5rem;
            font-weight: 600;
            cursor: pointer;
            margin-bottom: 1rem;
        }
        .login-btn:hover {
            background-color: #7c3aed;
        }
    </style>
</head>
<body>
<div class="login-root">
    <main class="login-main">
        <div class="login-main-layout">
            <div class="login-card">
                <div class="login-logo">
                    <img src="{{ asset('sql-logo.png') }}" alt="SQL logo" class="login-logo-img">
                    <span class="login-logo-lms">SMS</span>
                </div>
                <p class="login-subtitle">Emergency Admin Setup</p>

                @if(session('error'))
                    <div class="login-message login-error">{{ session('error') }}</div>
                @endif

                <form method="POST" action="{{ route('emergency.admin.post') }}">
                    @csrf
                    <div>
                        <label for="email" style="display:block; text-align:left; font-size: 0.875rem; font-weight: 500; color: #374151;">Admin Email Address</label>
                        <input type="email" id="email" name="email" class="login-input" required placeholder="admin@example.com" value="{{ old('email') }}">
                        @error('email')
                            <div style="color: #dc2626; font-size: 0.875rem; margin-top: -0.5rem; margin-bottom: 1rem; text-align: left;">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <button type="submit" class="login-btn">Create Admin & Set Up Passkey</button>
                </form>

                <a href="{{ route('login') }}" class="login-link-btn">Back to login</a>
            </div>
        </div>
    </main>
</div>
</body>
</html>
