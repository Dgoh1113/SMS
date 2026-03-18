<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Temporary Password</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; line-height: 1.5; color: #333; margin: 0; padding: 0; background: #f5f5f5; }
        .wrapper { max-width: 560px; margin: 0 auto; padding: 24px; }
        .card { background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.08); padding: 24px; margin-bottom: 16px; }
        p { margin: 0 0 12px; }
        .password-box { display: inline-block; padding: 12px 16px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 18px; font-weight: 700; letter-spacing: 0.08em; color: #4f46e5; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="card">
            <p>Hi {{ $recipientName }},</p>
            <p>Your SQL SMS account is ready.</p>
            <p>Please use this temporary password for your first login:</p>
            <p><span class="password-box">{{ $temporaryPassword }}</span></p>
            <p>If you have already signed in before, you can ignore this email.</p>
            <p>Thank you.<br>{{ $systemName }}</p>
        </div>
    </div>
</body>
</html>
