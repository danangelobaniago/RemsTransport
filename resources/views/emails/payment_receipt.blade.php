<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt</title>
</head>
{{-- Tables + inline styles only: many email apps (Outlook, some phone apps) ignore flexbox and <style> blocks. --}}
<body style="margin:0; padding:0; background:#f1f5f9; font-family:'Segoe UI', Arial, sans-serif;">
@php
    $fullyPaid = $remainingBalance <= 0;
    $peso = fn ($v) => '₱' . number_format($v, 2);
    $rows = [
        ['Booking ID', $bookingRef],
        ['Booking Type', $bookingType],
        ['Details', $description],
        ['Payment For', $paymentLabel],
        ['Payment Method', $paymentMethod],
        ['Date & Time Paid', $datePaid],
    ];
    if ($referenceId) {
        $rows[] = ['Reference ID', $referenceId];
    }
@endphp
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f1f5f9;">
<tr><td align="center" style="padding:30px 12px;">

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px; background:#ffffff; border-radius:16px; overflow:hidden;">

        {{-- HEADER --}}
        <tr><td align="center" style="background:#16a34a; padding:32px 30px;">
            <div style="font-size:30px; line-height:1; margin-bottom:12px;">✅</div>
            <div style="color:#ffffff; font-size:22px; font-weight:700; letter-spacing:1.5px;">REM'S TRANSPORT</div>
            <div style="color:#dcfce7; font-size:13px; margin-top:6px;">Payment Receipt — Thank you for your payment!</div>
        </td></tr>

        <tr><td style="padding:32px 30px;">

            <p style="font-size:16px; color:#1e293b; margin:0 0 12px;">Hi <strong>{{ $recipientName }}</strong>,</p>
            <p style="font-size:14px; color:#475569; margin:0 0 24px; line-height:1.6;">
                We received your payment. Below is your official receipt — please keep it for your records.
            </p>

            {{-- AMOUNT --}}
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:12px; margin-bottom:24px;">
                <tr><td align="center" style="padding:20px;">
                    <div style="font-size:12px; color:#16a34a; font-weight:700; text-transform:uppercase; letter-spacing:1px; margin-bottom:6px;">Amount Paid</div>
                    <div style="font-size:34px; font-weight:800; color:#16a34a;">{{ $peso($amountPaid) }}</div>
                    <div style="margin-top:10px;">
                        @if($fullyPaid)
                            <span style="display:inline-block; padding:5px 14px; border-radius:50px; font-size:12px; font-weight:700; text-transform:uppercase; background:#dcfce7; color:#15803d;">Fully Paid</span>
                        @else
                            <span style="display:inline-block; padding:5px 14px; border-radius:50px; font-size:12px; font-weight:700; text-transform:uppercase; background:#dbeafe; color:#1d4ed8;">Partially Paid</span>
                        @endif
                    </div>
                </td></tr>
            </table>

            {{-- TRANSACTION DETAILS --}}
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #e2e8f0; border-radius:12px; border-collapse:separate; margin-bottom:24px;">
                <tr><td colspan="2" style="background:#1e293b; color:#ffffff; padding:12px 20px; font-size:12px; font-weight:700; letter-spacing:1px; text-transform:uppercase; border-radius:12px 12px 0 0;">Transaction Details</td></tr>
                @foreach($rows as [$label, $value])
                <tr>
                    <td style="padding:12px 20px; font-size:14px; color:#64748b; border-top:1px solid #e2e8f0; vertical-align:top; white-space:nowrap;">{{ $label }}</td>
                    <td align="right" style="padding:12px 20px; font-size:14px; color:#0f172a; font-weight:700; border-top:1px solid #e2e8f0; word-break:break-word;{{ $label === 'Reference ID' ? ' font-family:monospace; font-size:12px;' : '' }}">{{ $value }}</td>
                </tr>
                @endforeach
            </table>

            {{-- BALANCE SUMMARY --}}
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #e2e8f0; border-radius:12px; border-collapse:separate; margin-bottom:24px;">
                <tr><td colspan="2" style="background:#1e293b; color:#ffffff; padding:12px 20px; font-size:12px; font-weight:700; letter-spacing:1px; text-transform:uppercase; border-radius:12px 12px 0 0;">Booking Balance</td></tr>
                <tr>
                    <td style="padding:12px 20px; font-size:14px; color:#64748b; border-top:1px solid #e2e8f0;">Total Amount</td>
                    <td align="right" style="padding:12px 20px; font-size:14px; color:#0f172a; font-weight:700; border-top:1px solid #e2e8f0;">{{ $peso($total) }}</td>
                </tr>
                <tr>
                    <td style="padding:12px 20px; font-size:14px; color:#64748b; border-top:1px solid #e2e8f0;">Total Paid So Far</td>
                    <td align="right" style="padding:12px 20px; font-size:14px; color:#0f172a; font-weight:700; border-top:1px solid #e2e8f0;">{{ $peso(max(0, $total - $remainingBalance)) }}</td>
                </tr>
                <tr>
                    <td style="padding:12px 20px; font-size:14px; color:#64748b; border-top:1px solid #e2e8f0;">Remaining Balance</td>
                    <td align="right" style="padding:12px 20px; font-size:15px; font-weight:800; border-top:1px solid #e2e8f0; color:{{ $fullyPaid ? '#16a34a' : '#b45309' }};">{{ $peso($remainingBalance) }}</td>
                </tr>
            </table>

            @if($fullyPaid)
                <p style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:10px; padding:14px 18px; font-size:13px; color:#15803d; margin:0 0 24px; line-height:1.6;">
                    🎉 Your booking is now <strong>fully paid</strong>. No more payment is needed.
                </p>
            @else
                <p style="background:#fffbeb; border:1px solid #fde68a; border-radius:10px; padding:14px 18px; font-size:13px; color:#92400e; margin:0 0 24px; line-height:1.6;">
                    ⚠️ <strong>Remaining balance: {{ $peso($remainingBalance) }}.</strong>
                    You can pay it online from <strong>My Bookings</strong> before your trip, or in cash to your driver on the day of the trip.
                </p>
            @endif

            <p style="text-align:center; margin:0 0 24px;">
                <a href="{{ url('/my-bookings') }}" style="display:inline-block; background:#2563eb; color:#ffffff; text-decoration:none; font-weight:700; font-size:14px; padding:12px 26px; border-radius:10px;">View My Bookings</a>
            </p>

            <p style="font-size:13px; color:#64748b; text-align:center; margin:0;">
                Questions? Contact us at <a href="mailto:remstransport1@gmail.com" style="color:#2563eb; text-decoration:none;">remstransport1@gmail.com</a>
            </p>
        </td></tr>

        {{-- FOOTER --}}
        <tr><td align="center" style="background:#f8fafc; border-top:1px solid #e2e8f0; padding:20px 30px; font-size:12px; color:#94a3b8;">
            <p style="margin:0 0 4px;">© {{ date('Y') }} Rem's Transport. All rights reserved.</p>
            <p style="margin:0;">This is an automated email. Please do not reply directly to this message.</p>
        </td></tr>
    </table>

</td></tr>
</table>
</body>
</html>
