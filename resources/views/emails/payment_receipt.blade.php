<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt</title>
    <style>
        body { margin: 0; padding: 0; background: #f1f5f9; font-family: 'Segoe UI', Arial, sans-serif; }
        .wrapper { max-width: 600px; margin: 30px auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .header { background: #16a34a; padding: 36px 30px; text-align: center; }
        .header h1 { margin: 0; color: #ffffff; font-size: 22px; letter-spacing: 1.5px; font-weight: 700; }
        .header p { margin: 6px 0 0; color: rgba(255,255,255,0.8); font-size: 13px; }
        .check-icon { width: 60px; height: 60px; background: rgba(255,255,255,0.2); border-radius: 50%; margin: 0 auto 16px; display: flex; align-items: center; justify-content: center; font-size: 28px; }
        .body { padding: 36px 30px; }
        .greeting { font-size: 16px; color: #1e293b; margin-bottom: 20px; }
        .receipt-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; margin-bottom: 24px; }
        .receipt-title { background: #1e293b; color: #ffffff; padding: 12px 20px; font-size: 12px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; }
        .receipt-row { display: flex; justify-content: space-between; padding: 13px 20px; border-bottom: 1px solid #e2e8f0; font-size: 14px; }
        .receipt-row:last-child { border-bottom: none; }
        .receipt-row .label { color: #64748b; font-weight: 500; }
        .receipt-row .value { color: #0f172a; font-weight: 700; text-align: right; max-width: 60%; word-break: break-all; }
        .amount-box { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px; padding: 20px; text-align: center; margin-bottom: 24px; }
        .amount-box .amount-label { font-size: 12px; color: #16a34a; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 6px; }
        .amount-box .amount-value { font-size: 34px; font-weight: 800; color: #16a34a; }
        .status-badge { display: inline-block; padding: 5px 14px; border-radius: 50px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
        .status-fully { background: #dcfce7; color: #15803d; }
        .status-down { background: #dbeafe; color: #1d4ed8; }
        .note { background: #fffbeb; border: 1px solid #fde68a; border-radius: 10px; padding: 14px 18px; font-size: 13px; color: #92400e; margin-bottom: 24px; }
        .footer { background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 20px 30px; text-align: center; font-size: 12px; color: #94a3b8; }
        .footer a { color: #2563eb; text-decoration: none; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <div class="check-icon">✅</div>
        <h1>REM'S TRANSPORT</h1>
        <p>Payment Receipt — Thank you for your booking!</p>
    </div>

    <div class="body">
        <p class="greeting">Hi <strong>{{ $recipientName }}</strong>,</p>
        <p style="font-size:14px; color:#475569; margin-bottom:24px;">
            Your payment has been successfully processed. Below is your official receipt. Please keep this for your records.
        </p>

        <div class="amount-box">
            <div class="amount-label">Amount Paid</div>
            <div class="amount-value">₱{{ number_format($amountPaid, 2) }}</div>
            <div style="margin-top:10px;">
                @if($status === 'fully_paid')
                    <span class="status-badge status-fully">Fully Paid</span>
                @else
                    <span class="status-badge status-down">Downpayment Paid</span>
                @endif
            </div>
        </div>

        <div class="receipt-box">
            <div class="receipt-title">Transaction Details</div>
            <div class="receipt-row">
                <span class="label">Reference ID</span>
                <span class="value" style="font-family: monospace; font-size:12px;">{{ $referenceId }}</span>
            </div>
            <div class="receipt-row">
                <span class="label">Booking Type</span>
                <span class="value">{{ $bookingType }}</span>
            </div>
            <div class="receipt-row">
                <span class="label">Details</span>
                <span class="value">{{ $description }}</span>
            </div>
            <div class="receipt-row">
                <span class="label">Payment Method</span>
                <span class="value">{{ $paymentMethod }}</span>
            </div>
            <div class="receipt-row">
                <span class="label">Date &amp; Time Paid</span>
                <span class="value">{{ $datePaid }}</span>
            </div>
        </div>

        @if($status === 'downpayment_paid')
        <div class="note">
            ⚠️ <strong>Note:</strong> This is a 20% downpayment. Your remaining balance is payable in cash upon boarding. Please bring the exact amount on the day of your trip.
        </div>
        @endif

        <p style="font-size:13px; color:#64748b; text-align:center; margin:0;">
            Questions? Contact us at <a href="mailto:remstransport1@gmail.com" style="color:#2563eb;">remstransport1@gmail.com</a>
        </p>
    </div>

    <div class="footer">
        <p style="margin:0 0 4px;">© {{ date('Y') }} Rem's Transport. All rights reserved.</p>
        <p style="margin:0;">This is an automated email. Please do not reply directly to this message.</p>
    </div>
</div>
</body>
</html>
