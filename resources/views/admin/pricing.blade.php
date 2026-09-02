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
                        value="{{ $pricing->base_fare }}" step="0.01" required>
                </div>
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
