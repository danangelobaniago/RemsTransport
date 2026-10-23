{{--
    Shown on the receipt / ticket page once a booking is rejected or cancelled,
    in place of the "pay your balance" box.
    Expects: $closedStatus ('rejected'|'cancelled'), $closedReason (?string),
             $totalPaid (float), $paymentStatus (?string, e.g. 'fully_paid'),
             optional $refund (booking_refunds row once the admin has sent it).
--}}
@php
    $refund   = $refund ?? null;
    $rejected = $closedStatus === 'rejected';
    $peso     = fn ($v) => '₱' . number_format((float) $v, 2);

    // What the customer is owed when nothing has been sent yet
    $owed = \App\Support\BookingRefund::eligibleAmount($closedStatus, $paymentStatus, $totalPaid);
@endphp

<style>
    .bc-card { margin: 0 0 24px; border-radius: 14px; overflow: hidden; border: 1px solid var(--bc-border); background: var(--bc-bg); }
    .bc-rejected { --bc-border: #fecaca; --bc-bg: #fff5f5; --bc-accent: #dc2626; --bc-text: #7f1d1d; }
    .bc-cancelled { --bc-border: #e2e8f0; --bc-bg: #f8fafc; --bc-accent: #64748b; --bc-text: #334155; }
    .bc-head { display: flex; align-items: center; gap: 14px; padding: 18px 20px; }
    .bc-icon { width: 42px; height: 42px; border-radius: 50%; background: var(--bc-accent); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0; }
    .bc-title { margin: 0; font-size: 16px; font-weight: 700; color: var(--bc-text); }
    .bc-sub { margin: 2px 0 0; font-size: 13px; color: var(--bc-text); opacity: .8; }
    .bc-body { padding: 0 20px 18px; display: grid; gap: 12px; }
    .bc-reason { background: #fff; border-left: 3px solid var(--bc-accent); border-radius: 0 8px 8px 0; padding: 10px 14px; font-size: 14px; color: #1f2937; }
    .bc-label { display: block; font-size: 11px; font-weight: 700; letter-spacing: .5px; text-transform: uppercase; color: #6b7280; margin-bottom: 3px; }
    .bc-refund { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 14px 16px; display: flex; gap: 16px; align-items: flex-start; }
    .bc-refund-info { flex: 1; min-width: 0; }
    .bc-chip { display: inline-flex; align-items: center; gap: 6px; padding: 3px 10px; border-radius: 99px; font-size: 12px; font-weight: 700; }
    .bc-chip-sent { background: #dcfce7; color: #15803d; }
    .bc-chip-pending { background: #fef3c7; color: #b45309; }
    .bc-amount { font-size: 24px; font-weight: 800; color: #0f172a; margin: 8px 0 2px; }
    .bc-meta { font-size: 13px; color: #6b7280; }
    .bc-message { margin-top: 10px; padding: 10px 12px; background: #f8fafc; border-radius: 8px; font-size: 13px; color: #334155; }
    .bc-proof { flex-shrink: 0; width: 120px; text-decoration: none; text-align: center; }
    .bc-proof img { width: 120px; height: 150px; object-fit: cover; border-radius: 8px; border: 1px solid #e5e7eb; display: block; }
    .bc-proof span { display: block; margin-top: 6px; font-size: 12px; font-weight: 600; color: #2563eb; }
    .bc-foot { padding: 10px 20px; border-top: 1px solid var(--bc-border); font-size: 12px; color: #6b7280; display: flex; align-items: center; gap: 6px; }
    @media (max-width: 560px) {
        .bc-refund { flex-direction: column; }
        .bc-proof, .bc-proof img { width: 100%; }
        .bc-proof img { height: 200px; }
    }
</style>

<div class="bc-card {{ $rejected ? 'bc-rejected' : 'bc-cancelled' }} no-print">
    <div class="bc-head">
        <div class="bc-icon"><i class="fas {{ $rejected ? 'fa-xmark' : 'fa-ban' }}"></i></div>
        <div>
            <h3 class="bc-title">{{ $rejected ? 'Booking Rejected' : 'Booking Cancelled' }}</h3>
            <p class="bc-sub">{{ $rejected ? 'This booking was rejected by the admin.' : 'This booking has been cancelled.' }}</p>
        </div>
    </div>

    <div class="bc-body">
        @if($rejected && $closedReason)
            <div class="bc-reason">
                <span class="bc-label">Reason</span>
                {{ $closedReason }}
            </div>
        @endif

        @if($refund)
            <div class="bc-refund">
                <div class="bc-refund-info">
                    <span class="bc-chip bc-chip-sent"><i class="fas fa-circle-check"></i> Refund Sent</span>
                    <div class="bc-amount">{{ $peso($refund->amount) }}</div>
                    <div class="bc-meta">Sent on {{ \App\Support\BookingRefund::refundedOn($refund) }}</div>
                    @if($refund->notes)
                        <div class="bc-message">
                            <span class="bc-label">Message from Rem's Transport</span>
                            {{ $refund->notes }}
                        </div>
                    @endif
                </div>
                <a class="bc-proof" href="{{ \App\Support\BookingRefund::proofUrl($refund) }}" target="_blank" rel="noopener">
                    <img src="{{ \App\Support\BookingRefund::proofUrl($refund) }}" alt="Refund receipt">
                    <span><i class="fas fa-up-right-from-square"></i> View receipt</span>
                </a>
            </div>
        @elseif($owed > 0)
            <div class="bc-refund">
                <div class="bc-refund-info">
                    <span class="bc-chip bc-chip-pending"><i class="fas fa-clock"></i> Refund Pending</span>
                    <div class="bc-amount">{{ $peso($owed) }}</div>
                    <div class="bc-meta">
                        @if($rejected)
                            You are eligible for a refund of the amount you paid.
                        @else
                            80% of your payment — 20% is retained as a cancellation fee.
                        @endif
                        Our admin will contact you to get your payment details for the refund.
                    </div>
                </div>
            </div>
        @else
            <div class="bc-meta" style="color: var(--bc-text);">
                {{ $totalPaid > 0 ? 'The downpayment is non-refundable.' : 'No payment was made for this booking.' }}
            </div>
        @endif
    </div>

    <div class="bc-foot"><i class="fas fa-lock"></i> Payments are no longer accepted for this booking.</div>
</div>
