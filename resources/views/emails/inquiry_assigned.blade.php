<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New inquiry assigned</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; line-height: 1.5; color: #333; margin: 0; padding: 0; background: #f5f5f5; }
        .wrapper { max-width: 560px; margin: 0 auto; padding: 24px; }
        .card { background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.08); padding: 24px; margin-bottom: 16px; }
        h1 { font-size: 20px; margin: 0 0 16px; color: #1a1a1a; }
        p { margin: 0 0 12px; }
        .inquiry-id { font-weight: 600; color: #0d6efd; }
        .detail { margin: 8px 0; padding: 8px 0; border-bottom: 1px solid #eee; }
        .detail:last-of-type { border-bottom: none; }
        .btn { display: inline-block; padding: 12px 24px; background: #0d6efd; color: #fff !important; text-decoration: none; border-radius: 6px; font-weight: 600; margin-top: 16px; }
        .btn:hover { background: #0b5ed7; }
        .footer { font-size: 12px; color: #6c757d; margin-top: 24px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="card">
            <h1>New inquiry assigned to you</h1>
            <p>Hi {{ $dealerName }},</p>
            <p>A new inquiry has been assigned to you.</p>
            <div class="detail"><strong>Inquiry ID:</strong> <span class="inquiry-id">#{{ $inquiryId }}</span></div>
            <div class="detail"><strong>Company:</strong> {{ $companyName }}</div>
            <div class="detail"><strong>Contact:</strong> {{ $contactName }}</div>
            <p>
                <a href="{{ $viewInquiryUrl }}" class="btn">View inquiry</a>
            </p>
            <p>This link will take you to your Dealer Console where you can view and manage this inquiry.</p>
        </div>
        <p class="footer">This is an automated message from SQL LMS.</p>
    </div>
</body>
</html>
