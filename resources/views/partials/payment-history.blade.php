{{--
    Payment history for the customer's receipt pages (van/tour receipt and joiner ticket).
    Expects $payments: booking_payments rows (method, reference, collected_by, amount, paid_at).
--}}
@php
    // [icon, color, background] per method; joiner rows store labels like "credit/debit card"
    $methodStyle = function ($code) {
        $code = strtolower((string) $code);
        return match (true) {
            $code === 'gcash'           => ['fa-mobile-screen-button', '#2563eb', '#eff6ff'],
            str_contains($code, 'card') => ['fa-credit-card', '#7c3aed', '#f5f3ff'],
            $code === 'cash'            => ['fa-money-bill-wave', '#16a34a', '#f0fdf4'],
            default                     => ['fa-wallet', '#475569', '#f1f5f9'],
        };
    };
@endphp

<style>
    .ph-card { margin-top: 24px; border: 1px solid #e2e8f0; border-radius: 14px; overflow: hidden; background: #fff; }
    .ph-head { display: flex; justify-content: space-between; align-items: center; padding: 14px 18px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; }
    .ph-head h4 { margin: 0; font-size: 14px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px; }
    .ph-head h4 i { color: #2563eb; }
    .ph-count { font-size: 12px; color: #64748b; font-weight: 600; }
    .ph-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .ph-table th { text-align: left; padding: 10px 18px; font-size: 11px; font-weight: 700; letter-spacing: .5px; text-transform: uppercase; color: #94a3b8; border-bottom: 1px solid #e2e8f0; }
    .ph-table td { padding: 14px 18px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; color: #0f172a; }
    .ph-table tbody tr:last-child td { border-bottom: none; }
    .ph-num { color: #94a3b8; font-weight: 700; width: 36px; }
    .ph-date { font-weight: 600; white-space: nowrap; }
    .ph-time { display: block; font-size: 12px; color: #64748b; font-weight: 400; margin-top: 2px; }
    .ph-method { display: flex; align-items: center; gap: 10px; }
    .ph-icon { width: 32px; height: 32px; border-radius: 9px; display: inline-flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0; }
    .ph-method strong { display: block; font-weight: 600; }
    .ph-via { display: block; font-size: 12px; color: #64748b; margin-top: 2px; }
    .ph-ref { font-family: ui-monospace, Consolas, monospace; font-size: 11.5px; color: #475569; word-break: break-all; }
    .ph-amt { text-align: right !important; font-weight: 700; color: #16a34a; white-space: nowrap; }
    .ph-total td { background: #f8fafc; border-top: 1px solid #e2e8f0; font-weight: 700; }
    .ph-total .ph-amt { font-size: 15px; }
    @media (max-width: 640px) {
        .ph-table th, .ph-table td { padding: 12px 12px; }
        .ph-col-ref, .ph-num { display: none; }
    }
</style>

<div class="ph-card">
    <div class="ph-head">
        <h4><i class="fas fa-clock-rotate-left"></i> Payment History</h4>
        <span class="ph-count">{{ $payments->count() }} {{ \Illuminate\Support\Str::plural('payment', $payments->count()) }}</span>
    </div>
    <table class="ph-table">
        <thead>
            <tr>
                <th class="ph-num">#</th>
                <th>Date &amp; Time</th>
                <th>Payment Method</th>
                <th class="ph-col-ref">Reference</th>
                <th class="ph-amt">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payments as $p)
                @php
                    // Stored in UTC; customers read Philippine time.
                    $paidAt = \Carbon\Carbon::parse($p->paid_at ?? $p->created_at, 'UTC')->setTimezone(\App\Support\LocalDate::TIMEZONE);
                    $byDriver = $p->collected_by === 'driver';
                    $code = $byDriver ? 'cash' : $p->method;
                    [$icon, $fg, $bg] = $methodStyle($code);
                @endphp
                <tr>
                    <td class="ph-num">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</td>
                    <td>
                        <span class="ph-date">{{ $paidAt->format('M d, Y') }}</span>
                        <span class="ph-time">{{ $paidAt->format('g:i A') }}</span>
                    </td>
                    <td>
                        <div class="ph-method">
                            <span class="ph-icon" style="color: {{ $fg }}; background: {{ $bg }};"><i class="fas {{ $icon }}"></i></span>
                            <span>
                                <strong>{{ $byDriver ? 'Cash' : \App\Support\PaymentReceipt::methodLabel($p->method) }}</strong>
                                <span class="ph-via">{{ $byDriver ? 'Collected by driver' : 'Paid online' }}</span>
                            </span>
                        </div>
                    </td>
                    <td class="ph-col-ref"><span class="ph-ref">{{ $p->reference ?: '—' }}</span></td>
                    <td class="ph-amt">₱{{ number_format($p->amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="ph-total">
                <td class="ph-num"></td>
                <td colspan="2">Total Paid</td>
                <td class="ph-col-ref"></td>
                <td class="ph-amt">₱{{ number_format($payments->sum('amount'), 2) }}</td>
            </tr>
        </tfoot>
    </table>
</div>
