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
        .dot-live { background: #22c55e; box-shadow: 0 0 0 3px #dcfce7; }
        .dot-offline { background: #cbd5e1; }
        .dot-idle { background: #f59e0b; box-shadow: 0 0 0 3px #fef3c7; }
        .empty-list { padding: 18px 16px; text-align: center; color: #94a3b8; font-size: 13px; }
        .list-group-title { padding: 8px 16px; font-size: 11px; font-weight: 700; letter-spacing: .5px; text-transform: uppercase; color: #64748b; background: #f8fafc; border-bottom: 1px solid #f1f5f9; }
        .driver-list-item.offline .driver-list-name { color: #64748b; }
        .driver-list-item.following { background: #eff6ff; }
        .src-badge { font-size: 10px; font-weight: 700; padding: 1px 6px; border-radius: 99px; background: #f1f5f9; color: #475569; }
        .src-badge.app { background: #ede9fe; color: #6d28d9; }
        .follow-btn { margin-left: auto; font-size: 11px; font-weight: 700; color: #2563eb; background: #eff6ff; border: none; border-radius: 6px; padding: 3px 8px; cursor: pointer; }
        .follow-btn.on { background: #2563eb; color: #fff; }
        @keyframes refreshPulse { 0% { transform: scale(1.8); opacity: .4; } 100% { transform: scale(1); opacity: 1; } }
        .dot.pulse { animation: refreshPulse .6s ease-out; }
        .follow-banner { display: none; align-items: center; gap: 10px; margin-bottom: 10px; padding: 8px 14px; background: #2563eb; color: #fff; border-radius: 10px; font-size: 13px; font-weight: 600; }
        .follow-banner button { margin-left: auto; background: rgba(255,255,255,.2); color: #fff; border: none; border-radius: 6px; padding: 4px 10px; cursor: pointer; font-weight: 700; }

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
            <a href="/admin/book-for-customer">New Booking</a>
            <a href="/admin/joiner-trips">Joiner Trips</a>
            <a href="/admin/pricing">Pricing</a>
            <a href="/admin/reports">Reports</a>
            <a href="/admin/live-map" class="active">Live Map</a>
            @include('admin.partials.messages-link')
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
            <strong>🟢 Live</strong> = location in the last 15 minutes &bull;
            <strong>🟡 Idle</strong> = 15–30 minutes (stopped, in traffic, or weak signal — last known spot shown) &bull;
            <strong>⚪ Offline</strong> = over 30 minutes. Locations come from the Driver Portal (while it's open) or the
            Traccar Client app (works in the background). Refreshes every 5 seconds; trails show the last hour.
        </p>

        <div class="map-layout">
            <div class="driver-list">
                <div class="driver-list-header" style="display:flex;align-items:center;gap:8px;">
                    <i class="fas fa-van-shuttle"></i> Drivers
                    <span id="refreshStatus" title="The map asks for new positions every 5 seconds"
                          style="margin-left:auto;font-size:11px;font-weight:600;color:#94a3b8;display:flex;align-items:center;gap:5px;">
                        <span id="refreshDot" class="dot dot-offline"></span> <span id="refreshText">Connecting…</span>
                    </span>
                </div>
                <div id="driverListBody">
                    <div class="empty-list">Loading...</div>
                </div>
            </div>

            <div>
                <div class="follow-banner" id="followBanner">
                    <i class="fas fa-location-crosshairs"></i> <span id="followText"></span>
                    <button type="button" onclick="stopFollowing()">Stop</button>
                </div>
                <div id="map"></div>
            </div>
        </div>

    </div>
</div>

<script>
function toggleSidebar() {
    document.getElementById('adminSidebar').classList.toggle('open');
    document.getElementById('sidebarOverlay').classList.toggle('open');
}

const REFRESH_MS = 5000;
const MOVE_ANIMATION_MS = 1200; // markers glide to their new position instead of jumping

let map, infoWindow;
let driverById = {};
let followId = null;          // driver the map keeps centered on
const markers = {};           // driver id -> google.maps.Marker
const accuracyCircles = {};   // driver id -> google.maps.Circle
const trails = {};            // driver id -> google.maps.Polyline

function initMap() {
    map = new google.maps.Map(document.getElementById('map'), {
        center: { lat: 14.5995, lng: 120.9842 }, // Metro Manila
        zoom: 11,
    });
    // Dragging the map yourself ends follow mode
    map.addListener('dragstart', () => { if (followId) stopFollowing(); });
    fetchLocations();
    setInterval(fetchLocations, REFRESH_MS);
}

function mapsLoadError() {
    document.getElementById('map').innerHTML =
        '<div style="display:flex;align-items:center;justify-content:center;height:100%;color:#94a3b8;font-size:13px;">Could not load Google Maps.</div>';
}

function escapeHtml(s) {
    return String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
}

// Server times are UTC 'Y-m-d H:i:s'
function timeAgo(dateStr) {
    if (!dateStr) return 'never';
    const diffSec = Math.max(0, Math.floor((Date.now() - new Date(dateStr.replace(' ', 'T') + 'Z')) / 1000));
    if (diffSec < 60) return diffSec + 's ago';
    if (diffSec < 3600) return Math.floor(diffSec / 60) + 'm ago';
    if (diffSec < 172800) return Math.floor(diffSec / 3600) + 'h ago';
    return Math.floor(diffSec / 86400) + 'd ago';
}

// Speed and heading only mean something for a recent fix; an older one just shows "Updated Xm ago"
const FRESH_MS = 2 * 60 * 1000;
function isFresh(d) {
    return d.location_updated_at && (Date.now() - new Date(d.location_updated_at.replace(' ', 'T') + 'Z')) < FRESH_MS;
}

function speedLabel(d) {
    if (d.location_speed === null || d.location_speed === undefined || !isFresh(d)) return '';
    const kmh = Math.round(parseFloat(d.location_speed) * 3.6);
    return kmh >= 3 ? ` &bull; ${kmh} km/h` : ' &bull; stopped';
}

function accuracyLabel(accuracy) {
    return accuracy ? ` &bull; &plusmn;${Math.round(accuracy)}m` : '';
}

function sourceBadge(d) {
    if (d.location_source === 'app') return '<span class="src-badge app" title="Traccar Client app — keeps working in the background">App</span>';
    if (d.location_source === 'portal') return '<span class="src-badge" title="Driver Portal — only while the page is open">Portal</span>';
    return '';
}

function tripSummary(trip) {
    if (!trip) return 'No active trip';
    const who = trip.customer ? ` &bull; ${escapeHtml(trip.customer)}` : '';
    return `${escapeHtml(trip.type)}: ${escapeHtml(trip.destination)}${who}`;
}

function fetchLocations() {
    fetch("{{ route('admin.drivers.locations') }}", { cache: 'no-store' })
        .then(r => { if (!r.ok) throw new Error(r.status); return r.json(); })
        .then(data => { renderDrivers(data.drivers || []); markRefreshed(true); })
        .catch(() => markRefreshed(false));
}

// Visible proof the map is polling: time of the last refresh + a pulse
function markRefreshed(ok) {
    const dot = document.getElementById('refreshDot');
    dot.className = 'dot ' + (ok ? 'dot-live' : 'dot-offline');
    void dot.offsetWidth; // restart the pulse animation
    if (ok) dot.classList.add('pulse');
    document.getElementById('refreshText').textContent = ok
        ? 'Refreshed ' + new Date().toLocaleTimeString('en-PH', { hour: 'numeric', minute: '2-digit', second: '2-digit' })
        : 'Connection problem — retrying';
}

// Keep the "Updated Xs ago" labels counting between refreshes
setInterval(() => {
    document.querySelectorAll('[data-ago]').forEach(el => { el.textContent = timeAgo(el.dataset.ago); });
}, 1000);

const DOT_CLASS = { live: 'dot-live', idle: 'dot-idle', offline: 'dot-offline' };
const MARKER_COLOR = { live: '#2563eb', idle: '#f59e0b', offline: '#94a3b8' };
const AGO_PREFIX = { live: 'Updated', idle: 'Idle — last update', offline: 'Last seen' };
const trackable = d => d.state !== 'offline'; // live or idle: still on the road, worth following

function listItem(d) {
    const following = followId === d.id;
    const ago = `<span data-ago="${escapeHtml(d.location_updated_at)}">${timeAgo(d.location_updated_at)}</span>`;
    const meta = d.state === 'live'
        ? `Updated ${ago}${speedLabel(d)}${accuracyLabel(d.location_accuracy)}`
        : d.state === 'idle'
            ? `Stopped or weak signal &bull; last update ${ago}`
            : `Last seen ${ago}`;
    return `<div class="driver-list-item ${d.state === 'offline' ? 'offline' : ''} ${following ? 'following' : ''}" onclick="showDriver(${d.id})">
        <div class="driver-list-name">
            <span class="dot ${DOT_CLASS[d.state]}"></span>${escapeHtml(d.name)} ${sourceBadge(d)}
            ${trackable(d) ? `<button type="button" class="follow-btn ${following ? 'on' : ''}" onclick="event.stopPropagation(); toggleFollow(${d.id})">${following ? 'Following' : 'Follow'}</button>` : ''}
        </div>
        <div class="driver-list-meta">${meta}</div>
        <div class="driver-list-meta">${tripSummary(d.current_trip)}</div>
    </div>`;
}

function renderList(drivers) {
    const group = (title, list, empty) =>
        `<div class="list-group-title">${title} (${list.length})</div>` +
        (list.length ? list.map(listItem).join('') : `<div class="empty-list">${empty}</div>`);
    document.getElementById('driverListBody').innerHTML =
        group('🟢 Live', drivers.filter(d => d.state === 'live'), 'No driver is sharing their location right now.') +
        group('🟡 Idle', drivers.filter(d => d.state === 'idle'), '—') +
        group('⚪ Offline', drivers.filter(d => d.state === 'offline'), '—');
}

function markerIcon(d) {
    const color = MARKER_COLOR[d.state];
    const moving = d.is_live && isFresh(d) && d.location_heading !== null && parseFloat(d.location_speed || 0) * 3.6 >= 3;
    // An arrow pointing where the van is heading while it moves; a dot when stopped or offline
    return moving
        ? { path: google.maps.SymbolPath.FORWARD_CLOSED_ARROW, scale: 6, rotation: parseFloat(d.location_heading),
            fillColor: color, fillOpacity: 1, strokeColor: 'white', strokeWeight: 2 }
        : { path: google.maps.SymbolPath.CIRCLE, scale: 8, fillColor: color, fillOpacity: 1, strokeColor: 'white', strokeWeight: 2 };
}

// Glide a marker from where it is to `to` over MOVE_ANIMATION_MS
function animateMarker(marker, to) {
    const from = marker.getPosition();
    if (!from || (from.lat() === to.lat && from.lng() === to.lng)) { marker.setPosition(to); return; }
    if (marker._anim) cancelAnimationFrame(marker._anim);
    const start = performance.now();
    const step = (now) => {
        const t = Math.min(1, (now - start) / MOVE_ANIMATION_MS);
        const e = t < 0.5 ? 2 * t * t : 1 - Math.pow(-2 * t + 2, 2) / 2; // ease in-out
        marker.setPosition({ lat: from.lat() + (to.lat - from.lat()) * e, lng: from.lng() + (to.lng - from.lng()) * e });
        if (t < 1) marker._anim = requestAnimationFrame(step);
    };
    marker._anim = requestAnimationFrame(step);
}

function renderDrivers(drivers) {
    const seenIds = new Set();
    driverById = {};
    drivers.forEach(d => { d.id = Number(d.id); driverById[d.id] = d; });

    // Following someone who went offline — stop
    if (followId && !(driverById[followId] && trackable(driverById[followId]))) stopFollowing(false);

    renderList(drivers);

    drivers.forEach(d => {
        seenIds.add(d.id);
        const pos = { lat: parseFloat(d.current_lat), lng: parseFloat(d.current_lng) };
        const accuracy = d.location_accuracy ? parseFloat(d.location_accuracy) : null;

        if (markers[d.id]) {
            animateMarker(markers[d.id], pos);
            markers[d.id].setIcon(markerIcon(d));
            markers[d.id].setZIndex(trackable(d) ? 10 : 1);
        } else {
            markers[d.id] = new google.maps.Marker({ position: pos, map, title: d.name, icon: markerIcon(d), zIndex: trackable(d) ? 10 : 1 });
            markers[d.id].addListener('click', () => showDriver(d.id));
        }

        // Faint circle = the GPS fix's own margin of error (live drivers only)
        if (accuracy && d.is_live) {
            if (accuracyCircles[d.id]) {
                accuracyCircles[d.id].setCenter(pos);
                accuracyCircles[d.id].setRadius(accuracy);
            } else {
                accuracyCircles[d.id] = new google.maps.Circle({
                    center: pos, radius: accuracy, map, clickable: false,
                    fillColor: '#2563eb', fillOpacity: 0.08, strokeColor: '#2563eb', strokeOpacity: 0.25, strokeWeight: 1,
                });
            }
        } else if (accuracyCircles[d.id]) {
            accuracyCircles[d.id].setMap(null);
            delete accuracyCircles[d.id];
        }

        // Trail of the last hour while live
        const path = (d.trail || []).map(([lat, lng]) => ({ lat, lng }));
        if (trackable(d) && path.length > 1) {
            if (trails[d.id]) {
                trails[d.id].setPath(path);
            } else {
                trails[d.id] = new google.maps.Polyline({ path, map, clickable: false, strokeColor: '#2563eb', strokeOpacity: 0.55, strokeWeight: 4 });
            }
        } else if (trails[d.id]) {
            trails[d.id].setMap(null);
            delete trails[d.id];
        }
    });

    // Drop anything for drivers no longer reporting
    Object.keys(markers).forEach(id => {
        if (seenIds.has(Number(id))) return;
        markers[id].setMap(null); delete markers[id];
        if (accuracyCircles[id]) { accuracyCircles[id].setMap(null); delete accuracyCircles[id]; }
        if (trails[id]) { trails[id].setMap(null); delete trails[id]; }
    });

    if (followId && driverById[followId]) {
        const d = driverById[followId];
        map.panTo({ lat: parseFloat(d.current_lat), lng: parseFloat(d.current_lng) });
    }
}

function showDriver(id) {
    const d = driverById[id];
    if (!d || !markers[id]) return;

    map.panTo({ lat: parseFloat(d.current_lat), lng: parseFloat(d.current_lng) });
    if (map.getZoom() < 15) map.setZoom(15);

    if (!infoWindow) infoWindow = new google.maps.InfoWindow();
    infoWindow.setContent(`
        <div style="font-size:13px;min-width:190px;">
            <div style="font-weight:700;margin-bottom:4px;">${escapeHtml(d.name)} ${sourceBadge(d)}</div>
            <div style="color:#64748b;margin-bottom:2px;">${tripSummary(d.current_trip)}</div>
            <div style="color:#94a3b8;font-size:11px;">${AGO_PREFIX[d.state]} ${timeAgo(d.location_updated_at)}${d.state === 'live' ? speedLabel(d) + accuracyLabel(d.location_accuracy) : ''}</div>
            ${d.phone ? `<div style="margin-top:6px;"><a href="tel:${escapeHtml(d.phone)}" style="color:#2563eb;font-weight:600;"><i class="fas fa-phone"></i> ${escapeHtml(d.phone)}</a></div>` : ''}
        </div>
    `);
    infoWindow.open(map, markers[id]);
}

function toggleFollow(id) {
    if (followId === id) { stopFollowing(); return; }
    followId = id;
    const d = driverById[id];
    document.getElementById('followText').textContent = 'Following ' + d.name;
    document.getElementById('followBanner').style.display = 'flex';
    showDriver(id);
    renderList(Object.values(driverById));
}

function stopFollowing(rerender = true) {
    followId = null;
    document.getElementById('followBanner').style.display = 'none';
    if (rerender) renderList(Object.values(driverById));
}
</script>
<script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.key') }}&callback=initMap&onerror=mapsLoadError" async defer></script>
<script src="/js/pwa.js"></script>
</body>
</html>
