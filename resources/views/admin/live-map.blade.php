<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#111827">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <title>Live Driver Map | Rem's Transport</title>
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/icons/icon-192.svg">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    <link rel="stylesheet" href="{{ asset('css/responsive.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .map-layout { display: grid; grid-template-columns: 300px 1fr; gap: 16px; align-items: start; }
        @media (max-width: 900px) { .map-layout { grid-template-columns: 1fr; } }

        .driver-list { background: white; border-radius: 12px; box-shadow: 0 1px 6px rgba(0,0,0,0.08); overflow: hidden; }
        .driver-list-header { padding: 14px 16px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; font-weight: 700; font-size: 14px; }
        .driver-list-item {
            padding: 12px 16px; border-bottom: 1px solid #f1f5f9; cursor: pointer; transition: 0.15s;
        }
        .driver-list-item:hover { background: #f8fafc; }
        .driver-list-item:last-child { border-bottom: none; }
        .driver-list-name { font-size: 13px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 6px; }
        .driver-list-meta { font-size: 11px; color: #94a3b8; margin-top: 3px; }
        .dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
        .dot-live { background: #22c55e; }
        .dot-stale { background: #f59e0b; }
        .empty-list { padding: 30px 16px; text-align: center; color: #94a3b8; font-size: 13px; }

        #map { width: 100%; height: 75vh; min-height: 420px; border-radius: 12px; box-shadow: 0 1px 6px rgba(0,0,0,0.08); }
    </style>
</head>
<body>

<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>
<div class="container">

    <div class="sidebar" id="adminSidebar">
        <h2 class="logo">Rem's Transport</h2>
        <nav>
            <a href="/admin/dashboard">Dashboard</a>
            <a href="/admin/bookings">Bookings</a>
            <a href="/admin/drivers">Drivers</a>
            <a href="/admin/vans">Vans</a>
            <a href="/admin/tours">Tours</a>
            <a href="/admin/customers">Customers</a>
            <a href="/admin/joiner-trips">Joiner Trips</a>
            <a href="/admin/pricing">Pricing</a>
            <a href="/admin/live-map" class="active">Live Map</a>
            <a href="/admin/reports">Reports</a>
            <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
            <form id="logout-form" action="/logout" method="POST" style="display:none;">@csrf</form>
        </nav>
    </div>

    <div class="main">

        <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
            <button class="hamburger" onclick="toggleSidebar()" aria-label="Menu"><span></span><span></span><span></span></button>
            <h1 style="margin:0;">Live Driver Map</h1>
        </div>

        <p style="color:#64748b;font-size:13px;margin:-12px 0 20px;">
            Shows drivers who currently have their Driver Portal open with location sharing on. Positions refresh every 10 seconds.
        </p>

        <div class="map-layout">
            <div class="driver-list">
                <div class="driver-list-header"><i class="fas fa-van-shuttle"></i> Drivers Online</div>
                <div id="driverListBody">
                    <div class="empty-list">Loading...</div>
                </div>
            </div>

            <div id="map"></div>
        </div>

    </div>
</div>

<script>
function toggleSidebar() {
    document.getElementById('adminSidebar').classList.toggle('open');
    document.getElementById('sidebarOverlay').classList.toggle('open');
}

let map;
const markers = {}; // driver id -> google.maps.Marker
const STALE_AFTER_MS = 2 * 60 * 1000; // 2 minutes without an update = stale

function initMap() {
    map = new google.maps.Map(document.getElementById('map'), {
        center: { lat: 14.5995, lng: 120.9842 }, // Metro Manila
        zoom: 11,
    });
    fetchLocations();
    setInterval(fetchLocations, 10000);
}

function mapsLoadError() {
    document.getElementById('map').innerHTML =
        '<div style="display:flex;align-items:center;justify-content:center;height:100%;color:#94a3b8;font-size:13px;">Could not load Google Maps.</div>';
}

function timeAgo(dateStr) {
    if (!dateStr) return 'never';
    const diffSec = Math.floor((Date.now() - new Date(dateStr.replace(' ', 'T') + 'Z')) / 1000);
    if (diffSec < 60) return diffSec + 's ago';
    if (diffSec < 3600) return Math.floor(diffSec / 60) + 'm ago';
    return Math.floor(diffSec / 3600) + 'h ago';
}

function fetchLocations() {
    fetch("{{ route('admin.drivers.locations') }}")
        .then(r => r.json())
        .then(data => renderDrivers(data.drivers || []))
        .catch(() => {});
}

function tripSummary(trip) {
    if (!trip) return 'No active trip';
    const who = trip.customer ? ` &bull; ${trip.customer}` : '';
    return `${trip.type}: ${trip.destination}${who}`;
}

function renderDrivers(drivers) {
    const seenIds = new Set();
    const listBody = document.getElementById('driverListBody');
    driverById = {};

    if (drivers.length === 0) {
        listBody.innerHTML = '<div class="empty-list">No drivers are currently sharing their location.</div>';
    } else {
        listBody.innerHTML = drivers.map(d => {
            const isStale = (Date.now() - new Date(d.location_updated_at.replace(' ', 'T') + 'Z')) > STALE_AFTER_MS;
            return `<div class="driver-list-item" onclick="showDriver(${d.id})">
                <div class="driver-list-name"><span class="dot ${isStale ? 'dot-stale' : 'dot-live'}"></span>${d.name}</div>
                <div class="driver-list-meta">Updated ${timeAgo(d.location_updated_at)} &bull; ${d.status}</div>
                <div class="driver-list-meta">${tripSummary(d.current_trip)}</div>
            </div>`;
        }).join('');
    }

    drivers.forEach(d => {
        seenIds.add(d.id);
        driverById[d.id] = d;
        const pos = { lat: parseFloat(d.current_lat), lng: parseFloat(d.current_lng) };
        const isStale = (Date.now() - new Date(d.location_updated_at.replace(' ', 'T') + 'Z')) > STALE_AFTER_MS;

        if (markers[d.id]) {
            markers[d.id].setPosition(pos);
            markers[d.id].setOpacity(isStale ? 0.4 : 1);
        } else {
            markers[d.id] = new google.maps.Marker({
                position: pos,
                map: map,
                title: d.name,
                opacity: isStale ? 0.4 : 1,
                icon: {
                    path: google.maps.SymbolPath.CIRCLE,
                    scale: 8,
                    fillColor: '#2563eb',
                    fillOpacity: 1,
                    strokeColor: 'white',
                    strokeWeight: 2,
                },
            });
            markers[d.id].addListener('click', () => showDriver(d.id));
        }
    });

    // Remove markers for drivers no longer reporting a location.
    Object.keys(markers).forEach(id => {
        if (!seenIds.has(Number(id))) {
            markers[id].setMap(null);
            delete markers[id];
        }
    });
}

let driverById = {};
let infoWindow;

function showDriver(id) {
    const d = driverById[id];
    if (!d || !markers[id]) return;

    panTo(parseFloat(d.current_lat), parseFloat(d.current_lng));

    if (!infoWindow) infoWindow = new google.maps.InfoWindow();
    infoWindow.setContent(`
        <div style="font-size:13px;min-width:180px;">
            <div style="font-weight:700;margin-bottom:4px;">${d.name}</div>
            <div style="color:#64748b;margin-bottom:2px;">${tripSummary(d.current_trip)}</div>
            <div style="color:#94a3b8;font-size:11px;">Updated ${timeAgo(d.location_updated_at)}</div>
        </div>
    `);
    infoWindow.open(map, markers[id]);
}

function panTo(lat, lng) {
    map.panTo({ lat, lng });
    map.setZoom(15);
}
</script>
<script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.key') }}&callback=initMap&onerror=mapsLoadError" async defer></script>
<script src="/js/pwa.js"></script>
</body>
</html>
