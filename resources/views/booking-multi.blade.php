<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="theme-color" content="#7c3aed">
<meta name="apple-mobile-web-app-capable" content="yes">

<title>Multi-Destination Booking | Rem's Transport</title>
<link rel="manifest" href="/manifest.json">
<link rel="apple-touch-icon" href="/icons/icon-192.svg">
<link rel="stylesheet" href="{{ asset('css/booking.css') }}?v={{ filemtime(public_path('css/booking.css')) }}">
<link rel="stylesheet" href="{{ asset('css/responsive.css') }}?v={{ filemtime(public_path('css/responsive.css')) }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<style>
    .stops-wrapper { display: flex; flex-direction: column; gap: 10px; margin-bottom: 8px; }
    .stop-row { display: flex; align-items: center; gap: 8px; }
    .stop-row input { flex: 1; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; outline: none; transition: border-color .2s; }
    .stop-row input:focus { border-color: #7c3aed; }
    .stop-row .remove-stop { background: #fee2e2; border: none; color: #ef4444; width: 34px; height: 34px; border-radius: 8px; cursor: pointer; font-size: 16px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .stop-row .remove-stop:hover { background: #fecaca; }
    .stop-number { width: 26px; height: 26px; border-radius: 50%; background: #7c3aed; color: white; font-size: 11px; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .add-stop-btn { display: flex; align-items: center; gap: 6px; background: #f5f3ff; color: #7c3aed; border: 1.5px dashed #7c3aed; border-radius: 8px; padding: 9px 14px; font-size: 13px; font-weight: 600; cursor: pointer; transition: .2s; width: 100%; justify-content: center; }
    .add-stop-btn:hover { background: #ede9fe; }
    .multi-badge { display: inline-flex; align-items: center; gap: 6px; background: #f5f3ff; color: #7c3aed; border: 1px solid #ddd6fe; border-radius: 20px; padding: 4px 12px; font-size: 12px; font-weight: 700; margin-bottom: 14px; }
    .route-legs { margin-top: 8px; }
    .leg-row { display: flex; justify-content: space-between; font-size: 12px; color: #64748b; padding: 3px 0; border-bottom: 1px dashed #f1f5f9; }
    .leg-row:last-child { border: none; }
    .confirm-btn { background: #7c3aed !important; }
    .confirm-btn:hover { background: #6d28d9 !important; }
</style>
</head>

<body>
<div class="container">

<div class="topbar">
    <a href="/" class="logo">Rem's Transport</a>
</div>

<div class="booking-wrapper">

<!-- LEFT -->
<div class="form-box">
<form action="/book" method="POST" id="multiBookingForm">
@csrf

<input type="hidden" name="van_id" value="{{ $van->id }}">
<input type="hidden" id="van_id" name="van_id" value="{{ $van->id }}">
<input type="hidden" id="van" name="van" value="{{ $van->name }}">
<input type="hidden" id="destination" name="destination" value="">
<input type="hidden" id="stops_json" name="stops_json" value="">

@if(session('error'))
    <div style="background:#fee2e2;color:#b91c1c;padding:12px;border-radius:8px;margin-bottom:20px;border:1px solid #fecaca;font-weight:bold;">
        {{ session('error') }}
    </div>
@endif

<div class="multi-badge"><i class="fas fa-route"></i> Multi-Destination Booking</div>
<h3>Trip Details</h3>

<div class="row">
    <span>Selected Van</span>
    <span class="badge">{{ $van->name }}</span>
</div>

<!-- MAP -->
<div id="map" style="width:100%;height:300px;border-radius:10px;"></div>

<!-- PICKUP -->
<label>Pickup Location <small style="color:#6b7280;font-weight:400;">(NCR / Metro Manila only)</small></label>
<input type="text" id="pickup" name="pickup" required autocomplete="off" placeholder="Enter pickup location in Metro Manila">
<input type="hidden" id="pickup_lat" name="pickup_lat" value="">
<input type="hidden" id="pickup_lng" name="pickup_lng" value="">

<!-- DESTINATIONS -->
<label style="margin-top:14px;display:block;">Destinations / Stops <small style="color:#6b7280;font-weight:400;">(minimum 2)</small></label>
<div class="stops-wrapper" id="stopsWrapper">
    <div class="stop-row" data-index="0">
        <div class="stop-number">1</div>
        <input type="text" class="stop-input" placeholder="Stop 1 (e.g. Tagaytay City)" autocomplete="off">
    </div>
    <div class="stop-row" data-index="1">
        <div class="stop-number">2</div>
        <input type="text" class="stop-input" placeholder="Stop 2 (final destination)" autocomplete="off">
    </div>
</div>
<button type="button" class="add-stop-btn" id="addStopBtn" onclick="addStop()">
    <i class="fas fa-plus-circle"></i> Add Another Stop
</button>

<div id="maps-warning" style="display:none;margin-top:6px;padding:8px 12px;background:#fef9c3;border:1px solid #fde68a;border-radius:8px;font-size:12px;color:#92400e;">
    <i class="fas fa-exclamation-triangle"></i> Google Maps unavailable. Please enter the total distance manually below.
</div>

<label style="margin-top:14px;display:block;">Total Distance (KM)</label>
<input type="number" id="distance" name="distance" readonly step="0.01" min="0" placeholder="Auto-calculated from map">

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
<input type="text" name="pickup_time" id="pickup_time" placeholder="Select time" autocomplete="off" required>

<div class="grid">
<div>
<label>Passengers</label>
<input type="number" name="passengers" min="1" max="{{ $van->seats }}" value="1" required>
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

<!-- HIDDEN PRICING -->
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
<div class="row"><span>Total Distance</span><span id="travelDistance">0 km</span></div>
<div class="row"><span>Travel Time</span><span id="travelTime">0 mins</span></div>

<div class="route-legs" id="routeLegs" style="display:none;">
    <div style="font-size:11px;font-weight:700;color:#7c3aed;text-transform:uppercase;letter-spacing:.5px;margin:10px 0 6px;">Route Breakdown</div>
    <div id="legsList"></div>
</div>

<hr>

<h3>Booking Summary</h3>
<div class="row"><span>Days</span><span id="displayDays">0</span></div>
<div class="row"><span>Base Fare</span><span id="displayBaseFare">₱0</span></div>
<div class="row"><span>Distance Fee</span><span id="displayDistanceFee">₱0</span></div>
<div class="row"><span>Driver Fee</span><span id="displayDriverFee">₱0</span></div>
<hr>
<div class="total">
    <span><strong>Total</strong></span>
    <span id="displayTotal"><strong>₱0</strong></span>
</div>
</div>

</div>
</div>

<script>
const vanId = {{ $van->id }};
let map, directionsService, directionsRenderer;
let bookedDates = [];
let startPicker, endPicker;
let pickupNCRValid = false;
let stopAutocompletes = [];

const LUZON_BOUNDS = { north: 21.12, south: 12.45, west: 116.85, east: 126.60 };
const NCR_CITIES = new Set([
    'manila','quezon city','caloocan','las piñas','las pinas','makati','malabon',
    'mandaluyong','marikina','muntinlupa','navotas','parañaque','paranaque',
    'pasay','pasig','san juan','taguig','valenzuela','pateros','metro manila'
]);

function initMap() {
    map = new google.maps.Map(document.getElementById("map"), {
        center: { lat: 15.48, lng: 120.71 },
        zoom: 7,
        restriction: { latLngBounds: LUZON_BOUNDS, strictBounds: false }
    });
    directionsService = new google.maps.DirectionsService();
    directionsRenderer = new google.maps.DirectionsRenderer({ map: map });

    const ncrBounds = new google.maps.LatLngBounds(
        new google.maps.LatLng(14.35, 120.91),
        new google.maps.LatLng(14.77, 121.17)
    );

    const pickupAuto = new google.maps.places.Autocomplete(
        document.getElementById("pickup"),
        { componentRestrictions: { country: "ph" }, bounds: ncrBounds, strictBounds: true, fields: ["address_components","geometry","name"] }
    );
    pickupAuto.addListener('place_changed', () => {
        const place = pickupAuto.getPlace();
        clearPickupState();
        if (!place || !place.geometry) { alert("⚠️ Pumili ng specific na address sa NCR."); return; }
        const isNCR = (place.address_components || []).some(c => NCR_CITIES.has(c.long_name.toLowerCase()));
        if (!isNCR) { document.getElementById('pickup').value=''; alert("⚠️ Pickup location ay NCR (Metro Manila) lang."); return; }
        pickupNCRValid = true;
        document.getElementById('pickup_lat').value = place.geometry.location.lat();
        document.getElementById('pickup_lng').value = place.geometry.location.lng();
        document.getElementById('pickup').style.borderColor = '#10b981';
        calculateRoute();
    });

    // Wire autocomplete for existing stop inputs
    document.querySelectorAll('.stop-input').forEach((input, i) => attachStopAutocomplete(input, i));
}

function attachStopAutocomplete(input, index) {
    const auto = new google.maps.places.Autocomplete(input, {
        componentRestrictions: { country: "ph" },
        fields: ["geometry", "name", "formatted_address"]
    });
    auto.addListener('place_changed', () => {
        const place = auto.getPlace();
        if (!place || !place.geometry) return;
        const lat = place.geometry.location.lat();
        if (lat < 12.45) { alert("⚠️ Please select a destination within Luzon."); input.value = ''; return; }
        input.style.borderColor = '#7c3aed';
        calculateRoute();
    });
    stopAutocompletes[index] = auto;
}

function addStop() {
    const wrapper = document.getElementById('stopsWrapper');
    const count = wrapper.querySelectorAll('.stop-row').length;
    if (count >= 6) { alert("Maximum of 6 stops allowed."); return; }

    const row = document.createElement('div');
    row.className = 'stop-row';
    row.dataset.index = count;
    row.innerHTML = `
        <div class="stop-number">${count + 1}</div>
        <input type="text" class="stop-input" placeholder="Stop ${count + 1}" autocomplete="off">
        <button type="button" class="remove-stop" onclick="removeStop(this)" title="Remove stop">
            <i class="fas fa-times"></i>
        </button>
    `;
    wrapper.appendChild(row);
    attachStopAutocomplete(row.querySelector('.stop-input'), count);
    renumberStops();
}

function removeStop(btn) {
    const row = btn.closest('.stop-row');
    row.remove();
    renumberStops();
    calculateRoute();
}

function renumberStops() {
    document.querySelectorAll('#stopsWrapper .stop-row').forEach((row, i) => {
        row.querySelector('.stop-number').textContent = i + 1;
        row.querySelector('.stop-input').placeholder = i === 0 ? 'Stop 1 (first destination)' : `Stop ${i + 1}${i === document.querySelectorAll('#stopsWrapper .stop-row').length - 1 ? ' (final destination)' : ''}`;
    });
}

function clearPickupState() {
    pickupNCRValid = false;
    document.getElementById('pickup_lat').value = '';
    document.getElementById('pickup_lng').value = '';
    document.getElementById('distance').value = '';
    document.getElementById('travelDistance').innerText = '0 km';
    document.getElementById('travelTime').innerText = '0 mins';
    document.getElementById('routeLegs').style.display = 'none';
    if (directionsRenderer) directionsRenderer.setDirections({ routes: [] });
    updateSummary();
}

function calculateRoute() {
    if (!directionsService || !pickupNCRValid) return;

    const pickup = document.getElementById('pickup').value;
    const stopInputs = Array.from(document.querySelectorAll('.stop-input')).map(i => i.value.trim()).filter(v => v);

    if (!pickup || stopInputs.length < 1) return;

    const origin = pickup;
    const destination = stopInputs[stopInputs.length - 1];
    const waypoints = stopInputs.slice(0, -1).map(loc => ({ location: loc, stopover: true }));

    directionsService.route({
        origin, destination, waypoints,
        travelMode: google.maps.TravelMode.DRIVING,
        optimizeWaypoints: false
    }, (result, status) => {
        if (status !== "OK") return;

        directionsRenderer.setDirections(result);

        const legs = result.routes[0].legs;
        let totalKm = 0, totalSecs = 0;
        const legsList = document.getElementById('legsList');
        legsList.innerHTML = '';

        const allPoints = [pickup, ...stopInputs];
        legs.forEach((leg, i) => {
            totalKm += leg.distance.value / 1000;
            totalSecs += leg.duration.value;
            const from = allPoints[i].split(',')[0];
            const to   = allPoints[i + 1].split(',')[0];
            legsList.innerHTML += `<div class="leg-row"><span>${from} → ${to}</span><span>${(leg.distance.value/1000).toFixed(1)} km</span></div>`;
        });

        document.getElementById('routeLegs').style.display = 'block';
        document.getElementById('distance').value = totalKm.toFixed(2);
        document.getElementById('travelDistance').innerText = totalKm.toFixed(2) + ' km';

        const hrs = Math.floor(totalSecs / 3600);
        const mins = Math.round((totalSecs % 3600) / 60);
        document.getElementById('travelTime').innerText = hrs > 0 ? `${hrs}h ${mins}m` : `${mins} mins`;

        updateSummary();
    });
}

function updateSummary() {
    const start    = document.getElementById("start_date").value;
    const end      = document.getElementById("end_date").value;
    const driver   = document.getElementById("driver").value;
    const distance = parseFloat(document.getElementById("distance").value) || 0;
    const baseRate    = parseFloat(document.getElementById("baseFareValue").value) || 0;
    const perKm       = parseFloat(document.getElementById("pricePerKmValue").value) || 0;
    const driverRate  = parseFloat(document.getElementById("driverFeeValue").value) || 0;

    const days = calculateDays(start, end);
    const baseFareTotal    = baseRate * days;
    const distanceFeeTotal = perKm * distance;
    const driverFeeTotal   = (driver && driver !== "") ? driverRate * days : 0;
    const totalAmount      = baseFareTotal + distanceFeeTotal + driverFeeTotal;

    document.getElementById("displayDays").innerText = days;
    document.getElementById("displayBaseFare").innerText  = "₱" + baseFareTotal.toLocaleString(undefined, {minimumFractionDigits:2});
    document.getElementById("displayDistanceFee").innerText = "₱" + distanceFeeTotal.toLocaleString(undefined, {minimumFractionDigits:2});
    document.getElementById("displayDriverFee").innerText = "₱" + driverFeeTotal.toLocaleString(undefined, {minimumFractionDigits:2});
    document.getElementById("displayTotal").innerHTML = `<strong>₱${totalAmount.toLocaleString(undefined,{minimumFractionDigits:2})}</strong>`;

    document.getElementById("inputDays").value      = days;
    document.getElementById("inputBaseFare").value  = baseFareTotal;
    document.getElementById("inputDriverFee").value = driverFeeTotal;
    document.getElementById("inputTotal").value     = totalAmount.toFixed(2);
}

function calculateDays(start, end) {
    if (!start || !end) return 1;
    const s = new Date(start), e = new Date(end);
    s.setHours(0,0,0,0); e.setHours(0,0,0,0);
    const diff = Math.ceil(Math.abs(e - s) / (1000*60*60*24)) + 1;
    return diff > 0 ? diff : 1;
}

function validateAvailability() {
    const start = document.getElementById("start_date").value;
    const end   = document.getElementById("end_date").value;
    const btn   = document.querySelector(".confirm-btn");
    btn.disabled = false; btn.innerText = "Confirm Booking"; btn.style.backgroundColor = "";
    if (!start || !end) return;
    let temp = new Date(start);
    const stop = new Date(end);
    while (temp <= stop) {
        if (bookedDates.includes(temp.toISOString().split('T')[0])) {
            btn.disabled = true; btn.innerText = "❌ Dates Unavailable"; btn.style.backgroundColor = "#ef4444";
            return;
        }
        temp.setDate(temp.getDate() + 1);
    }
}

function validateDateTime() {
    const startVal = document.getElementById("start_date").value;
    const timeVal  = document.getElementById("pickup_time").value;
    const btn = document.querySelector(".confirm-btn");
    if (!startVal || !timeVal) return;
    if (new Date(startVal + 'T' + timeVal) < new Date()) {
        alert("⚠️ Invalid Time: You cannot book a time that has already passed.");
        document.getElementById("pickup_time").value = "";
        btn.disabled = true; btn.style.backgroundColor = "#ef4444"; btn.innerText = "❌ Invalid Time";
    } else { validateAvailability(); }
}

document.addEventListener("DOMContentLoaded", () => {
    const driverSelect = document.getElementById("driver");
    const pickupInput  = document.getElementById("pickup");

    pickupInput.addEventListener("input", () => {
        clearPickupState();
        pickupInput.style.borderColor = '';
    });

    // On form submit: build destination string and validate
    document.getElementById('multiBookingForm').addEventListener('submit', function(e) {
        if (!pickupNCRValid) {
            e.preventDefault();
            alert("⚠️ Please select a Pickup Location within NCR (Metro Manila).");
            pickupInput.focus(); pickupInput.style.borderColor = '#ef4444';
            return;
        }
        const stopValues = Array.from(document.querySelectorAll('.stop-input')).map(i => i.value.trim()).filter(v => v);
        if (stopValues.length < 2) {
            e.preventDefault();
            alert("⚠️ Please add at least 2 destination stops.");
            return;
        }
        // Build destination string for backend
        document.getElementById('destination').value = stopValues.join(' → ');
        document.getElementById('stops_json').value  = JSON.stringify(stopValues);
    });

    flatpickr("#pickup_time", {
        enableTime: true, noCalendar: true, dateFormat: "H:i",
        time_24hr: false, minuteIncrement: 30,
        onChange: () => validateDateTime()
    });

    document.getElementById("start_date").addEventListener("change", validateDateTime);
    driverSelect.addEventListener("change", updateSummary);

    // Refresh driver availability on date change
    function refreshDrivers() {
        const start = document.getElementById("start_date").value;
        const end   = document.getElementById("end_date").value || start;
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
                        if (opt.selected) driverSelect.value = '';
                    } else {
                        opt.disabled = false;
                        opt.textContent = opt.textContent.replace(' (Unavailable)', '');
                    }
                });
                document.getElementById('driver-hint').style.display = hasBusy ? 'block' : 'none';
            }).catch(() => {});
    }
    document.getElementById("start_date").addEventListener("change", refreshDrivers);
    document.getElementById("end_date").addEventListener("change", refreshDrivers);

    fetch(`/booked-dates/${vanId}`)
        .then(res => res.json())
        .then(data => {
            bookedDates = data;
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);

            startPicker = flatpickr("#start_date", {
                disable: bookedDates, minDate: tomorrow, dateFormat: "Y-m-d",
                onChange: (_, dateStr) => {
                    if (endPicker) endPicker.set('minDate', dateStr);
                    updateSummary(); validateDateTime();
                }
            });
            endPicker = flatpickr("#end_date", {
                disable: bookedDates, minDate: tomorrow, dateFormat: "Y-m-d",
                onChange: () => { updateSummary(); validateDateTime(); }
            });
        });

    updateSummary();

    // Maps fallback
    setTimeout(() => {
        if (typeof google === 'undefined' || typeof google.maps === 'undefined') {
            document.getElementById('maps-warning').style.display = 'block';
            document.getElementById('distance').removeAttribute('readonly');
            document.getElementById('distance').addEventListener('input', updateSummary);
        }
    }, 5000);
});
</script>

<script
    src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.key') }}&libraries=places&callback=initMap&onerror=mapsLoadError"
    async defer>
</script>
<script src="/js/pwa.js"></script>
</body>
</html>
