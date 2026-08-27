<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="theme-color" content="#2563eb">
<meta name="apple-mobile-web-app-capable" content="yes">

<title>Van Booking | Rem's Transport</title>
<link rel="manifest" href="/manifest.json">
<link rel="apple-touch-icon" href="/icons/icon-192.svg">
<link rel="stylesheet" href="{{ asset('css/booking.css') }}">
<link rel="stylesheet" href="{{ asset('css/responsive.css') }}">

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

</head>

<body>

<div class="container">

<div class="topbar">
    <a href="/" class="logo">Rem's Transport</a>
</div>

<div class="booking-wrapper">

<!-- LEFT -->
<div class="form-box">

<form action="/book" method="POST">
@csrf


<input type="hidden" name="van_id" value="{{ $van->id }}">

<input type="hidden" id="van_id" name="van_id" value="{{ $van->id }}">

@if(session('error'))
    <div style="background: #fee2e2; color: #b91c1c; padding: 12px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #fecaca; font-weight: bold;">
        {{ session('error') }}
    </div>
@endif

<h3>Trip Details</h3>

<div class="row">
<span>Selected Van</span>
<span class="badge">{{ $van->name }}</span>
<input type="hidden" name="van_id" value="{{ $van->id }}">
</div>

<input type="hidden" id="van" name="van" value="{{ $van->name }}">

<!-- MAP -->
<div id="map" style="width:100%; height:300px; border-radius:10px;"></div>

<!-- INPUTS -->
<label>Pickup Location</label>
<input type="text" id="pickup" name="pickup" required autocomplete="off">
<input type="hidden" id="pickup_lat" name="pickup_lat" value="">
<input type="hidden" id="pickup_lng" name="pickup_lng" value="">

<label>Destination</label>
<input type="text" id="destination" name="destination" required>

<label>Distance (KM)</label>
<input type="number" id="distance" name="distance" readonly step="0.01" min="0">
<div id="maps-warning" style="display:none; margin-top:6px; padding:8px 12px; background:#fef9c3; border:1px solid #fde68a; border-radius:8px; font-size:12px; color:#92400e;">
    <i class="fas fa-exclamation-triangle"></i> Google Maps unavailable. Please enter the distance manually.
</div>

<div class="grid">
<div>
<label>Start Date</label>
<input type="text" id="start_date" name="start_date" placeholder="Select Date" required>
</div>

<div>
<label>End Date</label>
<input type="date" id="end_date" name="end_date" min="{{ date('Y-m-d', strtotime('+1 day')) }}" required>
</div>
</div>

<label>Pickup Time</label>
<input
    type="text"
    name="pickup_time"
    id="pickup_time"
    placeholder="Select time"
    autocomplete="off"
    required
>

<div class="grid">
<div>
<label>Passengers</label>
<input
    type="number"
    name="passengers"
    min="1"
    max="{{ $van->seats }}"
    value="1"
    required
>

</div>

<div>
<label>Preferred Driver</label>
<select id="driver" name="driver" required>
    <option value="" disabled selected>Select a Driver</option>
    @foreach($drivers as $d)
        <option value="{{ $d->id }}" data-driver-id="{{ $d->id }}">{{ $d->name }}</option>
    @endforeach
</select>
<small id="driver-hint" style="color:#6b7280;font-size:12px;margin-top:4px;display:none;">
    Drivers marked as <em>Unavailable</em> are already booked on your selected dates.
</small>
</div>
</div>

<!-- HIDDEN -->
<input type="hidden" id="inputDays" name="days">
<input type="hidden" id="inputBaseFare" name="baseFare">
<input type="hidden" id="inputDriverFee" name="driverFee">
<input type="hidden" id="inputTotal" name="total">


<input type="hidden" id="baseFareValue" value="{{ $pricing->base_fare ?? 0 }}">
<input type="hidden" id="pricePerKmValue" value="{{ $pricing->price_per_km ?? 0 }}">
<input type="hidden" id="driverFeeValue" value="{{ $pricing->driver_fee ?? 0 }}">


<button type="submit" class="confirm-btn">Confirm Booking</button>

</form>
</div>

<!-- RIGHT SUMMARY -->
<div class="summary-box">

<h3>Route Preview</h3>

<div class="row">
<span>Distance</span>
<span id="travelDistance">0 km</span>
</div>

<div class="row">
<span>Travel Time</span>
<span id="travelTime">0 mins</span>
</div>


<hr>

<h3>Booking Summary</h3>

<div class="row">
    <span>Days</span>
    <span id="displayDays">0</span> </div>

<div class="row">
    <span>Base Fare</span>
    <span id="displayBaseFare">₱0</span> </div>

<div class="row">
    <span>Distance Fee</span>
    <span id="displayDistanceFee">₱0</span>
</div>

<div class="row">
    <span>Driver Fee</span>
    <span id="displayDriverFee">₱0</span>
</div>

<hr>

<div class="total">
    <span><strong>Total</strong></span>
    <span id="displayTotal"><strong>₱0</strong></span>
</div>

</div>
</div>

<script>
const vanId = {{ $van->id }};
</script>

<script src="{{ asset('js/booking.js') }}?v=5"></script>

<script>
(function () {
    const startInput  = document.getElementById('start_date');
    const endInput    = document.getElementById('end_date');
    const driverSelect = document.getElementById('driver');
    const hint        = document.getElementById('driver-hint');

    function refreshDrivers() {
        const start = startInput.value;
        const end   = endInput.value || start;
        if (!start) return;

        fetch(`/available-drivers?start_date=${start}&end_date=${end}`)
            .then(r => r.json())
            .then(data => {
                const busy = data.busy || [];
                let hasBusy = false;

                Array.from(driverSelect.options).forEach(opt => {
                    if (!opt.value) return;
                    const id = parseInt(opt.getAttribute('data-driver-id'));
                    if (busy.includes(id)) {
                        opt.disabled = true;
                        opt.textContent = opt.textContent.replace(' (Unavailable)', '') + ' (Unavailable)';
                        hasBusy = true;
                        // Deselect if currently selected
                        if (opt.selected) {
                            driverSelect.value = '';
                        }
                    } else {
                        opt.disabled = false;
                        opt.textContent = opt.textContent.replace(' (Unavailable)', '');
                    }
                });

                hint.style.display = hasBusy ? 'block' : 'none';
            })
            .catch(() => {});
    }

    startInput.addEventListener('change', refreshDrivers);
    endInput.addEventListener('change',   refreshDrivers);

    // Also hook into flatpickr onChange if it's used on start_date
    document.addEventListener('DOMContentLoaded', function () {
        const fpEl = startInput._flatpickr;
        if (fpEl) {
            fpEl.config.onChange.push(function () { refreshDrivers(); });
        }

        // If Maps hasn't initialised within 5 seconds, enable fallback
        setTimeout(function () {
            if (typeof google === 'undefined' || typeof google.maps === 'undefined') {
                enableMapsFallback();
            }
        }, 5000);
    });
})();
</script>

<script>
// Called by Google Maps if API key auth fails (billing disabled, domain restricted, etc.)
function gm_authFailure() {
    enableMapsFallback();
}
// Called if the script itself fails to load (network error, etc.)
function mapsLoadError() {
    enableMapsFallback();
}
function enableMapsFallback() {
    const distanceInput = document.getElementById('distance');
    const warning = document.getElementById('maps-warning');
    const mapDiv = document.getElementById('map');
    if (distanceInput) {
        distanceInput.removeAttribute('readonly');
        distanceInput.placeholder = 'Enter distance in KM';
        distanceInput.addEventListener('input', function () {
            if (typeof updateSummary === 'function') updateSummary();
        });
    }
    if (warning) warning.style.display = 'block';
    if (mapDiv)  mapDiv.style.display  = 'none';
}
</script>
<script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.key') }}&libraries=places&callback=initMap&onerror=mapsLoadError" async defer></script>
<script src="/js/pwa.js"></script>
</body>
</html>
