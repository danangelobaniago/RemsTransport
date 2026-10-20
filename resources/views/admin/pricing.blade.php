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
