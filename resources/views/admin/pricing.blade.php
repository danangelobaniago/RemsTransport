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

    <form action="/admin/pricing/update" method="POST">
        @csrf

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

        <div class="form-group">
            <label>Reason for change</label>
            <textarea name="reason" rows="2" placeholder="Bakit nagbabago ang pricing na ito?" required>{{ old('reason') }}</textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-save">
                 Save Changes
            </button>
        </div>

    </form>

</div>

@endsection
