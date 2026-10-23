<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Payment Receipt {{ $receiptNo }}</title>
{{-- Rendered by dompdf: tables only (no flexbox/grid), DejaVu Sans so the ₱ sign renders. --}}
<style>
    @page { margin: 32px 36px; }
    * { font-family: 'DejaVu Sans', sans-serif; }
    body { font-size: 10.5px; color: #0f172a; margin: 0; }
    table { width: 100%; border-collapse: collapse; }
    .muted { color: #64748b; }
    .right { text-align: right; }
    .brand { font-size: 20px; font-weight: bold; color: #1d4ed8; letter-spacing: 1px; }
    .doc-title { font-size: 15px; font-weight: bold; letter-spacing: 2px; color: #0f172a; }
    .chip { display: inline-block; padding: 3px 10px; border-radius: 10px; font-size: 9px; font-weight: bold; letter-spacing: .5px; }
    .chip-paid { background: #dcfce7; color: #15803d; }
    .chip-partial { background: #fef3c7; color: #b45309; }
    .rule { border-top: 2px solid #1d4ed8; margin: 14px 0 16px; }
    .section-title { font-size: 9px; font-weight: bold; letter-spacing: 1px; color: #64748b; text-transform: uppercase; margin: 0 0 6px; }
    .kv td { padding: 3px 0; vertical-align: top; }
    .kv td.k { color: #64748b; width: 95px; }
    .kv td.v { font-weight: bold; }
    .box { border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px 14px; }
    .grid th { background: #1e293b; color: #fff; font-size: 9px; letter-spacing: .5px; text-transform: uppercase; padding: 7px 10px; text-align: left; }
    .grid td { padding: 8px 10px; border-bottom: 1px solid #e2e8f0; }
    .grid tr:nth-child(even) td { background: #f8fafc; }
    .ref { font-family: 'DejaVu Sans Mono', monospace; font-size: 8.5px; color: #475569; }
    .totals td { padding: 6px 12px; }
    .totals .grand td { border-top: 2px solid #0f172a; font-size: 13px; font-weight: bold; padding-top: 9px; }
    .note { margin-top: 18px; padding: 10px 12px; border-radius: 6px; font-size: 9.5px; line-height: 1.5; }
    .note-due { background: #fffbeb; border: 1px solid #fde68a; color: #92400e; }
    .note-paid { background: #f0fdf4; border: 1px solid #bbf7d0; color: #15803d; }
    .footer { position: fixed; bottom: -10px; left: 0; right: 0; text-align: center; font-size: 8.5px; color: #94a3b8; }
</style>
</head>
<body>
@php
    $peso = fn ($v) => '₱' . number_format((float) $v, 2);
    $fullyPaid = $balance <= 0;
@endphp

{{-- HEADER --}}
<table>
    <tr>
        <td style="vertical-align: top;">
            <div class="brand">REM'S TRANSPORT</div>
            <div class="muted" style="margin-top: 3px;">Van Rental &amp; Tour Services</div>
            <div class="muted">remstransport1@gmail.com</div>
        </td>
        <td class="right" style="vertical-align: top;">
            <div class="doc-title">PAYMENT RECEIPT</div>
            <table class="kv" style="width: auto; margin-left: auto; margin-top: 6px;">
                <tr><td class="k right" style="padding-right: 8px;">Receipt No.</td><td class="v right">{{ $receiptNo }}</td></tr>
                <tr><td class="k right" style="padding-right: 8px;">Date Issued</td><td class="v right">{{ $issuedAt }}</td></tr>
            </table>
            <div style="margin-top: 6px;">
                <span class="chip {{ $fullyPaid ? 'chip-paid' : 'chip-partial' }}">{{ $fullyPaid ? 'FULLY PAID' : 'PARTIALLY PAID' }}</span>
            </div>
        </td>
    </tr>
</table>

<div class="rule"></div>

{{-- BILLED TO / BOOKING --}}
<table>
    <tr>
        <td style="width: 42%; vertical-align: top; padding-right: 12px;">
            <div class="section-title">Billed To</div>
            <div class="box">
                <div style="font-size: 12px; font-weight: bold;">{{ $customer ?: 'Customer' }}</div>
                @if($email)<div class="muted" style="margin-top: 2px;">{{ $email }}</div>@endif
                <div style="margin-top: 8px;"><span class="muted">Booking Type:</span> <strong>{{ $bookingType }}</strong></div>
            </div>
        </td>
        <td style="vertical-align: top;">
            <div class="section-title">Booking Details</div>
            <div class="box">
                <table class="kv">
                    @foreach($details as $label => $value)
                        <tr><td class="k">{{ $label }}</td><td class="v">{{ $value }}</td></tr>
                    @endforeach
                </table>
            </div>
        </td>
    </tr>
</table>

{{-- PASSENGERS --}}
@if($passengers->isNotEmpty())
    <div class="section-title" style="margin-top: 16px;">Passengers ({{ $passengers->count() }})</div>
    <div class="box" style="padding: 8px 14px;">
        @foreach($passengers as $name)
            <span style="display: inline-block; margin: 2px 14px 2px 0;">{{ $loop->iteration }}. {{ $name }}</span>
        @endforeach
    </div>
@endif

{{-- PAYMENTS --}}
<div class="section-title" style="margin-top: 16px;">Payment History</div>
<table class="grid">
    <thead>
        <tr>
            <th style="width: 22px;">#</th>
            <th>Date &amp; Time</th>
            <th>Payment Method</th>
            <th>Reference</th>
            <th class="right">Amount</th>
        </tr>
    </thead>
    <tbody>
        @forelse($payments as $p)
            @php
                $paidAt = \Carbon\Carbon::parse($p->paid_at ?? $p->created_at, 'UTC')->setTimezone(\App\Support\LocalDate::TIMEZONE);
                $byDriver = $p->collected_by === 'driver';
            @endphp
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $paidAt->format('M d, Y g:i A') }}</td>
                <td>
                    <strong>{{ $byDriver ? 'Cash' : \App\Support\PaymentReceipt::methodLabel($p->method) }}</strong>
                    <span class="muted"> · {{ $byDriver ? 'Collected by driver' : 'Paid online' }}</span>
                </td>
                <td class="ref">{{ $p->reference ?: '—' }}</td>
                <td class="right"><strong>{{ $peso($p->amount) }}</strong></td>
            </tr>
        @empty
            <tr><td colspan="5" class="muted" style="text-align: center;">{{ $totalPaid > 0 ? 'An itemized payment history is not available for this booking.' : 'No payments recorded yet.' }}</td></tr>
        @endforelse
    </tbody>
</table>

{{-- TOTALS --}}
<table style="margin-top: 14px;">
    <tr>
        <td style="width: 55%;"></td>
        <td>
            <table class="totals">
                <tr><td class="muted">Total Amount</td><td class="right">{{ $peso($total) }}</td></tr>
                <tr><td class="muted">Total Paid</td><td class="right" style="color: #15803d;">{{ $peso($totalPaid) }}</td></tr>
                <tr class="grand">
                    <td>Balance Due</td>
                    <td class="right" style="color: {{ $fullyPaid ? '#15803d' : '#b91c1c' }};">{{ $peso($balance) }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

@if($fullyPaid)
    <div class="note note-paid"><strong>This booking is fully paid.</strong> No further payment is needed. Thank you for choosing Rem's Transport!</div>
@else
    <div class="note note-due">
        <strong>Remaining balance: {{ $peso($balance) }}.</strong>
        This can be paid online from My Bookings before the trip, or in cash to the driver on the day of the trip.
    </div>
@endif

<div class="footer">
    This is a system-generated payment receipt from Rem's Transport. · {{ $receiptNo }}
</div>
</body>
</html>
