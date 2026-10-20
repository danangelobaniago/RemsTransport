@extends('admin.layout')

@section('content')

<div class="page-header">
    <h1> Pricing Settings</h1>
    <p>Manage your transport pricing system</p>
</div>

@if(session('success'))
    <div class="alert">
        ✔ {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div style="background:#fee2e2; color:#991b1b; padding:12px 16px; border-radius:8px; margin-bottom:16px; font-size:14px; line-height:1.5;">
        <i class="fa fa-times-circle"></i> {{ session('error') }}
    </div>
@endif

@if($outOfRangeVans->isNotEmpty())
    <div style="background:#fffbeb; border:1px solid #fde68a; color:#92400e; padding:14px 16px; border-radius:10px; margin-bottom:16px; font-size:13.5px; line-height:1.55;">
        <strong><i class="fa fa-triangle-exclamation"></i> The current base fare (₱{{ number_format($pricing->base_fare) }}) is outside the price range of {{ $outOfRangeVans->count() === 1 ? 'this van' : 'these vans' }}:</strong>
        <ul style="margin:6px 0 6px 20px;">
            @foreach($outOfRangeVans as $v)
                <li>{{ $v->name }} ({{ $v->plate_number }}): ₱{{ number_format($v->price_min) }} – ₱{{ number_format($v->price_max) }}</li>
            @endforeach
        </ul>
        Fix their price ranges on the <a href="/admin/vans" style="color:#92400e; font-weight:700;">Vans page</a>, or set a base fare inside every van's range.
    </div>
@endif

<div class="card">

    <form action="/admin/pricing/update" method="POST" id="pricing-form">
        @csrf
        <input type="hidden" name="reason" id="pricing-reason">

        <div class="form-grid">

            <!-- BASE FARE -->
            <div class="form-group">
                <label>Base Fare (₱ / day)</label>
                <div class="input-group">
                    <span>₱</span>
                    <input type="number" name="base_fare"
                        value="{{ old('base_fare', $pricing->base_fare) }}" step="0.01" required
                        @if(!is_null($allowedMin) && $allowedMin <= $allowedMax) min="{{ $allowedMin }}" max="{{ $allowedMax }}" @endif>
                </div>
                @if(!is_null($allowedMin))
                    @if($allowedMin <= $allowedMax)
                        <small style="display:block; margin-top:6px; color:#6b7280; font-size:12px;">
                            Allowed: ₱{{ number_format($allowedMin) }} – ₱{{ number_format($allowedMax) }} (must be inside every van's price range)
                        </small>
                    @else
                        <small style="display:block; margin-top:6px; color:#b91c1c; font-size:12px;">
                            No base fare fits every van right now — the van price ranges don't overlap. Adjust them on the Vans page first.
                        </small>
                    @endif
                @endif
            </div>

            <!-- PRICE PER KM -->
            <div class="form-group">
                <label>Price per KM (₱)</label>
                <div class="input-group">
                    <span>₱</span>
                    <input type="number" name="price_per_km"
                        value="{{ $pricing->price_per_km }}" step="0.01" required>
                </div>
            </div>

            <!-- DRIVER FEE -->
            <div class="form-group">
                <label>Driver Fee (₱ / day)</label>
                <div class="input-group">
                    <span>₱</span>
                    <input type="number" name="driver_fee"
                        value="{{ $pricing->driver_fee ?? 500 }}" step="0.01" required>
                </div>
            </div>

        </div>

        <div class="form-actions">
            <button type="button" class="btn-save" onclick="openReasonModal()">
                 Save Changes
            </button>
        </div>

    </form>

</div>

{{-- Price Change History --}}
@php
    $fieldLabels = [
        'base_fare'    => 'Base Fare',
        'price_per_km' => 'Price per KM',
        'driver_fee'   => 'Driver Fee',
        'price_min'    => 'Min Price',
        'price_max'    => 'Max Price',
    ];
@endphp
<div class="card" style="margin-top:20px;">
    <h3 style="margin:0 0 4px; font-size:17px; color:#111827;"><i class="fa fa-clock-rotate-left"></i> Price Change History</h3>
    <p style="margin:0 0 14px; color:#6b7280; font-size:13px;">Every change to the base fare, price per KM, driver fee, and van price ranges.</p>

    @if($history->isEmpty())
        <p style="padding:24px; text-align:center; color:#9ca3af; font-size:14px; margin:0;">No price changes recorded yet.</p>
    @else
        <div style="overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse; font-size:13.5px;">
                <thead>
                    <tr style="background:#f9fafb; text-align:left; color:#6b7280; font-size:12px; text-transform:uppercase; letter-spacing:.03em;">
                        <th style="padding:10px 12px;">Date</th>
                        <th style="padding:10px 12px;">Changed by</th>
                        <th style="padding:10px 12px;">Item</th>
                        <th style="padding:10px 12px;">Old → New</th>
                        <th style="padding:10px 12px;">Reason</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($history as $h)
                        @php
                            $up = $h->old_value !== null && (float) $h->new_value > (float) $h->old_value;
                            $down = $h->old_value !== null && (float) $h->new_value < (float) $h->old_value;
                        @endphp
                        <tr style="border-top:1px solid #f1f5f9;">
                            <td style="padding:10px 12px; white-space:nowrap; color:#374151;">
                                {{ \Carbon\Carbon::parse($h->created_at)->timezone('Asia/Manila')->format('M j, Y') }}<br>
                                <small style="color:#9ca3af;">{{ \Carbon\Carbon::parse($h->created_at)->timezone('Asia/Manila')->format('g:i A') }}</small>
                            </td>
                            <td style="padding:10px 12px;">{{ trim(($h->first_name ?? '') . ' ' . ($h->last_name ?? '')) ?: 'Admin' }}</td>
                            <td style="padding:10px 12px;">
                                <strong>{{ $fieldLabels[$h->field] ?? $h->field }}</strong>
                                @if($h->van_label)
                                    <br><small style="color:#6b7280;">{{ $h->van_label }}</small>
                                @endif
                            </td>
                            <td style="padding:10px 12px; white-space:nowrap;">
                                <span style="color:#6b7280;">{{ $h->old_value !== null ? '₱' . number_format($h->old_value, 2) : '—' }}</span>
                                →
                                <strong style="color:{{ $up ? '#b91c1c' : ($down ? '#15803d' : '#111827') }};">₱{{ number_format($h->new_value, 2) }}</strong>
                                @if($up) <i class="fa fa-arrow-up" style="color:#b91c1c; font-size:11px;"></i> @endif
                                @if($down) <i class="fa fa-arrow-down" style="color:#15803d; font-size:11px;"></i> @endif
                            </td>
                            <td style="padding:10px 12px; color:#374151; max-width:320px;">{{ $h->reason ?: '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($history->hasPages())
            <div style="display:flex; justify-content:space-between; align-items:center; margin-top:14px; font-size:13px; color:#6b7280;">
                @if($history->onFirstPage())
                    <span style="color:#cbd5e1;">&larr; Newer</span>
                @else
                    <a href="{{ $history->previousPageUrl() }}" style="color:#2563eb; font-weight:600; text-decoration:none;">&larr; Newer</a>
                @endif
                <span>Page {{ $history->currentPage() }} of {{ $history->lastPage() }}</span>
                @if($history->hasMorePages())
                    <a href="{{ $history->nextPageUrl() }}" style="color:#2563eb; font-weight:600; text-decoration:none;">Older &rarr;</a>
                @else
                    <span style="color:#cbd5e1;">Older &rarr;</span>
                @endif
            </div>
        @endif
    @endif
</div>

{{-- Price Change Reason Modal --}}
<div id="reasonModal" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; background:rgba(15,23,42,0.65); align-items:center; justify-content:center; padding:20px;">
    <div style="background:white; border-radius:14px; max-width:420px; width:100%; box-shadow:0 20px 50px rgba(0,0,0,0.25); overflow:hidden;">
        <div style="padding:20px 24px; border-bottom:1px solid #e5e7eb;">
            <h3 style="margin:0 0 2px; color:#111827; font-size:16px; font-weight:700;">Confirm Price Change</h3>
            <p style="margin:0; color:#6b7280; font-size:13px;">Please provide a reason for this price change.</p>
        </div>

        <div style="padding:20px 24px;">
            <textarea id="reasonModalInput" required maxlength="1000" rows="4"
                placeholder="e.g. Adjusting for fuel cost increase..."
                style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:8px; font-size:13px; font-family:inherit; resize:vertical;"></textarea>
            <p id="reasonModalError" style="display:none; margin:8px 0 0; color:#dc2626; font-size:12px;">A reason is required to save this price change.</p>
        </div>

        <div style="padding:14px 24px; border-top:1px solid #e5e7eb; background:#f9fafb; display:flex; gap:10px; justify-content:flex-end;">
            <button type="button" onclick="closeReasonModal()" style="padding:9px 16px; background:#e5e7eb; color:#374151; border:none; border-radius:8px; font-weight:600; font-size:13px; cursor:pointer;">Cancel</button>
            <button type="button" onclick="confirmReasonModal()" style="padding:9px 16px; background:#2563eb; color:white; border:none; border-radius:8px; font-weight:600; font-size:13px; cursor:pointer;">Save Changes</button>
        </div>
    </div>
</div>

<script>
function openReasonModal() {
    // Show the browser's message (e.g. base fare outside the allowed range) before asking for a reason.
    if (!document.getElementById('pricing-form').reportValidity()) return;
    document.getElementById('reasonModalInput').value = '';
    document.getElementById('reasonModalError').style.display = 'none';
    document.getElementById('reasonModal').style.display = 'flex';
}
function closeReasonModal() {
    document.getElementById('reasonModal').style.display = 'none';
}
function confirmReasonModal() {
    const reason = document.getElementById('reasonModalInput').value.trim();

    if (reason === '') {
        document.getElementById('reasonModalError').style.display = 'block';
        return;
    }

    document.getElementById('pricing-reason').value = reason;
    document.getElementById('pricing-form').submit();
}
document.getElementById('reasonModal').addEventListener('click', function (e) {
    if (e.target === this) closeReasonModal();
});
</script>

@endsection
