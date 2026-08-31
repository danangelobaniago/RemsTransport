<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="theme-color" content="#111827">
<meta name="apple-mobile-web-app-capable" content="yes">
<title>Manage Trips | Rem's Transport</title>
<link rel="manifest" href="/manifest.json">
<link rel="apple-touch-icon" href="/icons/icon-192.svg">
<link rel="stylesheet" href="{{ asset('css/admin.css') }}">
<link rel="stylesheet" href="{{ asset('css/responsive.css') }}">
</head>

<body>

<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>
<div class="container">

<!-- SIDEBAR -->
<div class="sidebar" id="adminSidebar">

<h2 class="logo">Rem's Transport</h2>

<nav>
<a href="/admin/dashboard">Dashboard</a>
<a href="/admin/bookings">Bookings</a>
<a href="/admin/drivers">Drivers</a>
<a href="/admin/vans">Vans</a>
<a href="/admin/customers">Customers</a>
<a href="/admin/joiner-trips">Joiner Trips</a>
<a href="/admin/pricing">Pricing</a>
<a href="/admin/reports">Reports</a>
<a href="/admin/live-map">Live Map</a>
<a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
        Logout
    </a>

    <form id="logout-form" action="/logout" method="POST" style="display: none;">
        @csrf
    </form>
</nav>

</div>


<!-- MAIN -->
<div class="main">

<div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
    <button class="hamburger" onclick="toggleSidebar()" aria-label="Menu"><span></span><span></span><span></span></button>
    <h1 style="margin:0;">Manage Trips</h1>
</div>

<div class="table-section">

<table>

<thead>
<tr>
<th>Trip ID</th>
<th>Driver</th>
<th>Van</th>
<th>Destination</th>
<th>Date</th>
<th>Status</th>
<th>Action</th>
</tr>
</thead>

<tbody id="tripBody">
</tbody>

</table>

</div>

</div>

</div>

<script>
function toggleSidebar() {
    document.getElementById('adminSidebar').classList.toggle('open');
    document.getElementById('sidebarOverlay').classList.toggle('open');
}
</script>
<script src="/js/pwa.js"></script>
</body>
</html>
