<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your inquiry has been assigned</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; line-height: 1.5; color: #333; margin: 0; padding: 0; background: #f5f5f5; }
        .wrapper { max-width: 560px; margin: 0 auto; padding: 24px; }
        .card { background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.08); padding: 24px; margin-bottom: 16px; }
        h1 { font-size: 20px; margin: 0 0 16px; color: #1a1a1a; }
        p { margin: 0 0 12px; }
        .detail-section { margin-top: 16px; margin-bottom: 16px; padding: 12px 16px; background-color: #f8f9fa; border-left: 4px solid #0d6efd; border-radius: 4px; }
        .detail-line { margin: 4px 0; }
        .footer { font-size: 12px; color: #6c757d; margin-top: 24px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="card">
            <p>Hi {{ $customerName }},</p>
            <p>Thank you for contacting us. Your inquiry (#{{ $inquiryId }}) has been assigned to one of our authorized dealers who will be following up on your case shortly.</p>
            
            <p>Assigned Dealer Details:<br>
            Dealer Name: {{ $dealerName }}</p>
            
            <p>Email: {{ $dealerEmail }}<br>
            Our dealer will get in touch with you soon to assist you with your request.</p>
        </div>
        <p class="footer">This is an automated message from SQL SMS.</p>
    </div>
</body>
</html>
