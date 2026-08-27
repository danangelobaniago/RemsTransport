// 1. GLOBAL VARIABLES
let map, directionsService, directionsRenderer;
let bookedDates = [];
let startPicker, endPicker;
let pickupNCRValid = false;


const LUZON_BOUNDS = {
    north: 21.12,
    south: 12.45,
    west: 116.85,
    east: 126.60,
};


const NCR_CITIES = new Set([
    'manila', 'quezon city', 'caloocan', 'las piñas', 'las pinas',
    'makati', 'malabon', 'mandaluyong', 'marikina', 'muntinlupa',
    'navotas', 'parañaque', 'paranaque', 'pasay', 'pasig',
    'san juan', 'taguig', 'valenzuela', 'pateros',
    'metro manila', // catch-all for region-level hits
]);


function initMap() {
    // Initialize Map with Luzon Restrictions
    map = new google.maps.Map(document.getElementById("map"), {
        center: { lat: 15.48, lng: 120.71 }, // Center of Luzon
        zoom: 7,
        restriction: {
            latLngBounds: LUZON_BOUNDS,
            strictBounds: false,
        },
    });

    directionsService = new google.maps.DirectionsService();
    directionsRenderer = new google.maps.DirectionsRenderer({ map: map });

    const pickupInput = document.getElementById("pickup");
    const destInput = document.getElementById("destination");

    // Strict NCR bounds — covers all 17 NCR cities/municipalities
    const ncrBounds = new google.maps.LatLngBounds(
        new google.maps.LatLng(14.35, 120.91),
        new google.maps.LatLng(14.77, 121.17)
    );

    // Pickup: strictly limited to NCR autocomplete results only
    const pickupOptions = {
        componentRestrictions: { country: "ph" },
        bounds: ncrBounds,
        strictBounds: true,
        fields: ["address_components", "geometry", "name"],
    };

    // Destination: Luzon-wide
    const destOptions = {
        componentRestrictions: { country: "ph" },
        fields: ["address_components", "geometry", "name"],
    };

    const pickupAuto = new google.maps.places.Autocomplete(pickupInput, pickupOptions);
    const destAuto   = new google.maps.places.Autocomplete(destInput,   destOptions);

    pickupAuto.addListener('place_changed', () => validateNCROnly(pickupAuto, "pickup"));
    destAuto.addListener('place_changed',   () => validateLuzonOnly(destAuto, "destination"));
}

function clearPickupState() {
    pickupNCRValid = false;
    document.getElementById('pickup_lat').value = '';
    document.getElementById('pickup_lng').value = '';
    document.getElementById('distance').value = '';
    document.getElementById('travelDistance').innerText = '0 km';
    document.getElementById('travelTime').innerText = '0 mins';
    if (directionsRenderer) directionsRenderer.setDirections({ routes: [] });
    updateSummary();
}

/**
 * Ensures pickup location is within NCR (Metro Manila) by checking address components.
 */
function validateNCROnly(autocomplete, inputId) {
    const place = autocomplete.getPlace();
    const input = document.getElementById(inputId);

    clearPickupState();
    input.style.borderColor = '';

    // No geometry means province/region-level selection — treat as invalid
    if (!place || !place.geometry || !place.geometry.location) {
        input.value = '';
        input.style.borderColor = '#ef4444';
        alert("⚠️ Pumili ng specific na address sa loob ng NCR (Metro Manila) mula sa mga mungkahi.");
        return;
    }

    const components = place.address_components || [];
    const isNCR = components.some(c => NCR_CITIES.has(c.long_name.toLowerCase()));

    if (!isNCR) {
        input.value = '';
        input.style.borderColor = '#ef4444';
        alert("⚠️ Ang pickup location ng Rem's Transport ay NCR (Metro Manila) lang. Pumili ng Metro Manila address mula sa mga mungkahi.");
        return;
    }

    pickupNCRValid = true;
    document.getElementById('pickup_lat').value = place.geometry.location.lat();
    document.getElementById('pickup_lng').value = place.geometry.location.lng();
    input.style.borderColor = '#10b981';
    calculateDistance();
}

/**
 * Ensures selected location is within Luzon based on Latitude.
 */
function validateLuzonOnly(autocomplete, inputId) {
    const place = autocomplete.getPlace();

    if (!place.geometry || !place.geometry.location) {
        return;
    }

    const lat = place.geometry.location.lat();

    if (lat < 12.45) {
        alert("⚠️ Rem's Transport currently only services the Luzon area. Please select a destination within Luzon.");
        document.getElementById(inputId).value = "";
        if (directionsRenderer) directionsRenderer.setDirections({routes: []});
        return;
    }

    calculateDistance();
}

// 3. LOGIC FUNCTIONS

function updateSummary() {
    const start = document.getElementById("start_date").value;
    const end = document.getElementById("end_date").value;
    const driver = document.getElementById("driver").value;
    const distance = parseFloat(document.getElementById("distance").value) || 0;

    const baseRate = parseFloat(document.getElementById("baseFareValue").value) || 0;
    const perKm = parseFloat(document.getElementById("pricePerKmValue").value) || 0;
    const driverRate = parseFloat(document.getElementById("driverFeeValue").value) || 0;

    const days = calculateDays(start, end);
    const baseFareTotal = baseRate * days;
    const distanceFeeTotal = perKm * distance;
    const driverFeeTotal = (driver && driver !== "") ? driverRate * days : 0;
    const totalAmount = baseFareTotal + distanceFeeTotal + driverFeeTotal;

    document.getElementById("displayDays").innerText = days;
    document.getElementById("displayBaseFare").innerText = "₱" + baseFareTotal.toLocaleString(undefined, {minimumFractionDigits: 2});
    document.getElementById("displayDistanceFee").innerText = "₱" + distanceFeeTotal.toLocaleString(undefined, {minimumFractionDigits: 2});
    document.getElementById("displayDriverFee").innerText = "₱" + driverFeeTotal.toLocaleString(undefined, {minimumFractionDigits: 2});
    document.getElementById("displayTotal").innerHTML = `<strong>₱${totalAmount.toLocaleString(undefined, {minimumFractionDigits: 2})}</strong>`;

    document.getElementById("inputDays").value = days;
    document.getElementById("inputBaseFare").value = baseFareTotal;
    document.getElementById("inputDriverFee").value = driverFeeTotal;
    document.getElementById("inputTotal").value = totalAmount.toFixed(2);
}

function calculateDays(start, end) {
    if (!start || !end) return 1;
    const s = new Date(start);
    const e = new Date(end);
    s.setHours(0,0,0,0);
    e.setHours(0,0,0,0);
    const diffTime = Math.abs(e - s);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
    return diffDays > 0 ? diffDays : 1;
}

function calculateDistance() {
    if (!directionsService) return; // Maps not loaded, user enters distance manually
    if (!pickupNCRValid) return; // Pickup not confirmed as NCR location

    let origin = document.getElementById("pickup").value;
    let destination = document.getElementById("destination").value;

    if (!origin || !destination) return;

    directionsService.route({
        origin: origin,
        destination: destination,
        travelMode: google.maps.TravelMode.DRIVING
    }, (result, status) => {
        if (status === "OK") {
            let route = result.routes[0].legs[0];
            let km = route.distance.value / 1000;

            document.getElementById("distance").value = km.toFixed(2);
            document.getElementById("travelDistance").innerText = km.toFixed(2) + " km";
            document.getElementById("travelTime").innerText = route.duration.text;

            directionsRenderer.setDirections(result);
            updateSummary();
        }
    });
}

function validateAvailability() {
    const start = document.getElementById("start_date").value;
    const end = document.getElementById("end_date").value;
    const submitBtn = document.querySelector(".confirm-btn");

    // Standard styling reset before checking
    submitBtn.disabled = false;
    submitBtn.innerText = "Confirm Booking";
    submitBtn.style.backgroundColor = "";

    if (!start || !end) return;

    let isConflict = false;
    let tempDate = new Date(start);
    const stopDate = new Date(end);

    while (tempDate <= stopDate) {
        let formatted = tempDate.toISOString().split('T')[0];
        if (bookedDates.includes(formatted)) {
            isConflict = true;
            break;
        }
        tempDate.setDate(tempDate.getDate() + 1);
    }

    if (isConflict) {
        submitBtn.disabled = true;
        submitBtn.innerText = "❌ Dates Unavailable";
        submitBtn.style.backgroundColor = "#ef4444";
    }
}

function validateDateTime() {
    const startDateInput = document.getElementById("start_date").value;
    const pickupTimeInput = document.getElementById("pickup_time").value;
    const submitBtn = document.querySelector(".confirm-btn");

    if (!startDateInput || !pickupTimeInput) return;

    const now = new Date();
    // Using a more robust date parsing format
    const selectedDateTime = new Date(startDateInput + 'T' + pickupTimeInput);

    if (selectedDateTime < now) {
        alert("⚠️ Invalid Time: You cannot book a time that has already passed today. Please select a future time.");
        document.getElementById("pickup_time").value = "";

        submitBtn.disabled = true;
        submitBtn.style.backgroundColor = "#ef4444";
        submitBtn.innerText = "❌ Invalid Time";
    } else {
        // If time is valid, check if the van is available on these dates
        validateAvailability();
    }
}

document.addEventListener("DOMContentLoaded", () => {
    const vanInput    = document.getElementById("van_id");
    const driverSelect = document.getElementById("driver");
    const pickupInput  = document.getElementById("pickup");
    const bookingForm  = document.querySelector("form[action='/book']");

    // Reset all pickup state whenever user edits the pickup field manually
    if (pickupInput) {
        pickupInput.addEventListener("input", function () {
            clearPickupState();
            pickupInput.style.borderColor = '';
        });
    }

    // Block form submission if pickup was not selected from NCR autocomplete
    if (bookingForm) {
        bookingForm.addEventListener("submit", function (e) {
            if (!pickupNCRValid) {
                e.preventDefault();
                alert("⚠️ Please select a Pickup Location within NCR (Metro Manila) from the dropdown suggestions.");
                if (pickupInput) {
                    pickupInput.focus();
                    pickupInput.style.borderColor = '#ef4444';
                }
            }
        });
    }

    flatpickr("#pickup_time", {
        enableTime: true,
        noCalendar: true,
        dateFormat: "H:i",
        time_24hr: false,
        minuteIncrement: 30,
        onChange: function() {
            validateDateTime();
        }
    });

    document.getElementById("start_date").addEventListener("change", validateDateTime);
    driverSelect.addEventListener("change", updateSummary);

    if (vanInput && vanInput.value) {
        fetch(`/booked-dates/${vanInput.value}`)
            .then(res => res.json())
            .then(data => {
                bookedDates = data;

                const tomorrow = new Date();
                tomorrow.setDate(tomorrow.getDate() + 1);

                startPicker = flatpickr("#start_date", {
                    disable: bookedDates,
                    minDate: tomorrow,
                    dateFormat: "Y-m-d",
                    onChange: function(selectedDates, dateStr) {
                        if(endPicker) endPicker.set('minDate', dateStr);
                        updateSummary();
                        validateDateTime();
                    }
                });

                endPicker = flatpickr("#end_date", {
                    disable: bookedDates,
                    minDate: tomorrow,
                    dateFormat: "Y-m-d",
                    onChange: function() {
                        updateSummary();
                        validateDateTime();
                    }
                });
            });
    }
    updateSummary();
});
