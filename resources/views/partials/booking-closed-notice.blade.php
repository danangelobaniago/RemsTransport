{{--
    Shown on the receipt / ticket page once a booking is rejected or cancelled,
    in place of the "pay your balance" box.
    Expects: $closedStatus ('rejected'|'cancelled'), $closedReason (?string),
             $totalPaid (float), $paymentStatus (?string, e.g. 'fully_paid'),
             optional $refund (booking_refunds row once the admin has sent it).
--}}
@php
    $refund = $refund ?? null;
    $refundLine = $refund
        ? 'Refund sent: ₱' . number_format($refund->amount, 2) . ' on ' . \App\Support\BookingRefund::refundedOn($refund) . '.'
        : null;
@endphp
<div class="no-print" style="margin: 0 0 24px; padding: 16px 18px; border-radius: 12px; font-size: 14px; line-height: 1.6;
     {{ $closedStatus === 'rejected' ? 'background:#fef2f2; border:1px solid #fecaca; color:#991b1b;' : 'background:#f8fafc; border:1px solid #e2e8f0; color:#334155;' }}">
    @if($closedStatus === 'rejected')
        <div style="font-weight: 700; font-size: 15px; margin-bottom: 4px;">
            <i class="fas fa-circle-xmark"></i> This booking was rejected by the admin.
        </div>
        @if($closedReason)
            <div><strong>Reason:</strong> {{ $closedReason }}</div>
        @endif
        <div style="margin-top: 6px;">
            @if($refundLine)
                <i class="fas fa-circle-check"></i> <strong>{{ $refundLine }}</strong>
            @elseif($totalPaid > 0)
                You are eligible for a refund of the <strong>₱{{ number_format($totalPaid, 2) }}</strong> you paid.
                Our admin will contact you to get your payment details for the refund.
            @else
                No payment was made for this booking, so nothing needs to be refunded.
            @endif
        </div>
    @else
        <div style="font-weight: 700; font-size: 15px; margin-bottom: 4px;">
            <i class="fas fa-ban"></i> This booking has been cancelled.
        </div>
        <div>
            @if($refundLine)
                <i class="fas fa-circle-check"></i> <strong>{{ $refundLine }}</strong>
            @elseif($paymentStatus === 'fully_paid')
                20% is retained as a cancellation fee. Our admin will contact you regarding the 80% refund.
            @elseif($totalPaid > 0)
                The downpayment is non-refundable.
            @else
                No payment was made for this booking.
            @endif
        </div>
    @endif
    @if($refund)
        <a href="{{ \App\Support\BookingRefund::proofUrl($refund) }}" target="_blank" rel="noopener"
           style="display: inline-block; margin-top: 10px; text-decoration: none; color: inherit;">
            <img src="{{ \App\Support\BookingRefund::proofUrl($refund) }}" alt="Refund receipt"
                 style="display: block; max-width: 220px; max-height: 220px; border-radius: 8px; border: 1px solid rgba(0,0,0,.12); background: #fff;">
            <span style="font-size: 12px; font-weight: 600;"><i class="fas fa-up-right-from-square"></i> View refund receipt</span>
        </a>
    @endif
    <div style="margin-top: 6px; font-size: 13px; opacity: .85;">Payments are no longer accepted for this booking.</div>
</div>
