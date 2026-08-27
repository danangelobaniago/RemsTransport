<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="theme-color" content="#111827">
<meta name="apple-mobile-web-app-capable" content="yes">
<title>Dashboard | Rem's Transport</title>
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
        <a href="/admin/dashboard" class="active">Dashboard</a>
        <a href="/admin/bookings">Bookings</a>
        <a href="/admin/drivers">Drivers</a>
        <a href="/admin/vans">Vans</a>
        <a href="/admin/tours">Tours</a>
        <a href="/admin/customers">Customers</a>
        <a href="/admin/joiner-trips">Joiner Trips</a>
        <a href="/admin/pricing">Pricing</a>
        <a href="/admin/reports">Reports</a>
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
        <h1 style="margin:0;">Dashboard</h1>
    </div>

    <!-- STATS -->
    <div class="stats-grid">

        <div class="stat-card">
            <h4>Total Bookings</h4>
            <p>{{ $totalBookings }}</p>
        </div>

        <div class="stat-card">
            <h4>Total Revenue</h4>
            <p>₱{{ number_format($totalRevenue,2) }}</p>
        </div>

        <div class="stat-card">
            <h4>Pending</h4>
            <p>{{ $pending }}</p>
        </div>

        <div class="stat-card">
            <h4>Completed</h4>
            <p>{{ $completed }}</p>
        </div>

    </div>

    <!-- RECENT BOOKINGS -->
<div class="card table-card">

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2>Recent Bookings</h2>

    <!-- SEARCH & TRIP TYPE FILTER FORM -->
    <form action="/admin/dashboard" method="GET" style="display: flex; gap: 10px;">
        <input type="text" name="search" placeholder="Search ID or Destination..."
               value="{{ request('search') }}"
               style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 6px; outline: none;">

        <!-- Updated Filter for Trip Type -->
        <select name="trip_type" style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 6px; cursor: pointer;" onchange="this.form.submit()">
            <option value="">All Trip Types</option>
            <option value="private" {{ request('trip_type') == 'private' ? 'selected' : '' }}>Private Van</option>
            <option value="joiner" {{ request('trip_type') == 'joiner' ? 'selected' : '' }}>Joiner Trip</option>
        </select>

        <button type="submit" style="background: #2563eb; color: white; border: none; padding: 8px 15px; border-radius: 6px; cursor: pointer; font-weight: 600;">
            <i class="fa fa-search"></i> Search
        </button>

        @if(request('search') || request('trip_type'))
            <a href="/admin/dashboard" style="text-decoration: none; color: #ef4444; padding: 8px; font-size: 14px; font-weight: 600;">Clear</a>
        @endif
    </form>
</div>

    <table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Date</th> <!-- Added Date Header -->
            <th>Pickup</th>
            <th>Destination</th>
            <th>Total</th>
            <th>Status</th>
        </tr>
    </thead>

    <tbody>
    @foreach($recent as $b)
    <tr>
        {{-- REMOVE THE OLD ID LINE THAT WAS CAUSING THE ERROR --}}

        {{-- Use ONLY display_id here --}}
        <td style="font-weight: bold; color: #2563eb;">{{ $b->display_id }}</td>

        <td style="font-weight: 600; color: #475569; white-space: nowrap;">
            {{ date('M d, Y', strtotime($b->date)) }}
        </td>

        <td>{{ Str::limit($b->pickup, 40) }}</td>
        <td>{{ $b->destination }}</td>
        <td>₱{{ number_format($b->total, 2) }}</td>

        <td>
            <span class="status {{ strtolower($b->status) }}">
                {{ ucfirst(str_replace('_', ' ', $b->status)) }}
            </span>
        </td>
    </tr>
    @endforeach
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
