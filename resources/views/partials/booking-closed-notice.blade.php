{{--
    Shown on the receipt / ticket page once a booking is rejected or cancelled,
    in place of the "pay your balance" box.
    Expects: $closedStatus ('rejected'|'cancelled'), $closedReason (?string),
             $totalPaid (float), $paymentStatus (?string, e.g. 'fully_paid').
--}}
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
            @if($totalPaid > 0)
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
            @if($paymentStatus === 'fully_paid')
                20% is retained as a cancellation fee. Our admin will contact you regarding the 80% refund.
            @elseif($totalPaid > 0)
                The downpayment is non-refundable.
            @else
                No payment was made for this booking.
            @endif
        </div>
    @endif
    <div style="margin-top: 6px; font-size: 13px; opacity: .85;">Payments are no longer accepted for this booking.</div>
</div>
