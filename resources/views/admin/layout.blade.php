<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="theme-color" content="#111827">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-title" content="Rem's Admin">

<title>@yield('title')</title>

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

<h2 class="logo"> Rem's Transport</h2>

<nav>
<a href="/admin/dashboard"> Dashboard</a>
<a href="/admin/bookings"> Bookings</a>
<a href="/admin/drivers"> Drivers</a>
<a href="/admin/vans"> Vans</a>
<a href="/admin/tours">Tours</a>
<a href="/admin/customers"> Customers</a>
<a href="/admin/joiner-trips"> Joiner Trips</a>
<a href="/admin/pricing" class="active"> Pricing</a>
<a href="/admin/reports"> Reports</a>
<a href="/admin/live-map"> Live Map</a>
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

<!-- TOP BAR -->
<div class="topbar">
    <button class="hamburger" onclick="toggleSidebar()" aria-label="Menu">
        <span></span><span></span><span></span>
    </button>
    <h2>@yield('title')</h2>
</div>

<!-- CONTENT -->
<div class="content">
    @yield('content')
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
