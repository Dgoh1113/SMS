<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Passkey Setup Link</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; line-height: 1.5; color: #333; margin: 0; padding: 0; background: #f5f5f5; }
        .wrapper { max-width: 560px; margin: 0 auto; padding: 24px; }
        .card { background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.08); padding: 24px; margin-bottom: 16px; }
        p { margin: 0 0 12px; }
        .btn { display: inline-block; padding: 12px 22px; background: #4f46e5; color: #fff !important; text-decoration: none; border-radius: 8px; font-weight: 700; margin: 8px 0 12px; }
        .btn:hover { background: #4338ca; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="card">
            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 12px;">
                <tr>
                    <td align="left" valign="middle">
                        <p style="margin: 0;">Hi,</p>
                    </td>
                    <td align="right" valign="middle">
                        <img src="{{ $message->embed(public_path('sql-logo.png')) }}" alt="SQL Logo" style="height: 32px; width: auto; display: block; opacity: 0.25;">
                    </td>
                </tr>
            </table>
            <p>{{ $introLine }}</p>
            <p>{{ $instructionLine }}</p>
            <p style="margin: 16px 0;">
                <a href="{{ $setupUrl }}" style="color: #4f46e5; word-break: break-all; text-decoration: underline;">{{ $setupUrl }}</a>
            </p>
            <p>{{ $expiryLine }}</p>
            @if (!empty($ignoreLine))
                <p>{{ $ignoreLine }}</p>
            @endif
            <p>Thank you.<br>{{ $systemName }}</p>
        </div>
    </div>
</body>
</html>
