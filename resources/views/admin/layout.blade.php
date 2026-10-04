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
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
    /* Notification bell — this layout doesn't load style.css (that's the
       customer-facing stylesheet the .notif-* classes normally come from),
       so it needs its own copy, themed to match admin.css's light UI. */
    .notification-wrapper { position: relative; }
    .notif-dropdown {
        display: none; position: absolute; right: 0; top: 36px; width: 280px;
        max-height: 340px; overflow-y: auto; background: #fff; border: 1px solid #e2e8f0;
        border-radius: 10px; box-shadow: 0 8px 24px rgba(0,0,0,0.15); z-index: 1200;
    }
    .notif-dropdown.show { display: block; }
    .notif-item {
        display: block; padding: 10px 14px; color: #374151; text-decoration: none;
        font-size: 12.5px; border-bottom: 1px solid #f1f5f9; line-height: 1.4;
    }
    .notif-item:hover { background: #f8fafc; }
    .notif-item.unread { background: #eff6ff; color: #1e293b; font-weight: 600; }
    .no-notif { padding: 14px; color: #94a3b8; font-size: 12.5px; text-align: center; }
    .notif-count {
        position: absolute; top: 0; right: 0; background: #ef4444; color: white;
        font-size: 10px; font-weight: 700; border-radius: 50%; width: 16px; height: 16px;
        display: flex; align-items: center; justify-content: center;
    }
</style>

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
<a href="/admin/book-for-customer"> New Booking</a>
<a href="/admin/joiner-trips"> Joiner Trips</a>
<a href="/admin/pricing" class="active"> Pricing</a>
<a href="/admin/reports"> Reports</a>
<a href="/admin/live-map"> Live Map</a>
@include('admin.partials.messages-link')
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
<div class="topbar" style="display:flex;align-items:center;">
    <button class="hamburger" onclick="toggleSidebar()" aria-label="Menu">
        <span></span><span></span><span></span>
    </button>
    <h2>@yield('title')</h2>

    <div class="notification-wrapper" style="margin-left:auto;">
        <button class="notif-btn" onclick="toggleNotif(event)" style="position:relative;background:none;border:none;cursor:pointer;font-size:18px;color:#334155;">
            <i class="fa fa-bell"></i>
            @if(auth()->user()->unreadNotifications->count())
                <span class="notif-count">{{ auth()->user()->unreadNotifications->count() }}</span>
            @endif
        </button>
        <div class="notif-dropdown" id="notifDropdown">
            @forelse(auth()->user()->notifications as $notif)
                <a href="/notifications/read" class="notif-item {{ $notif->read_at ? '' : 'unread' }}">
                    {{ $notif->data['message'] ?? 'Notification' }}
                </a>
            @empty
                <p class="no-notif">No notifications</p>
            @endforelse
        </div>
    </div>
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

function toggleNotif(event) {
    event.stopPropagation();
    const dropdown = document.getElementById('notifDropdown');
    if (dropdown) dropdown.classList.toggle('show');
}

document.addEventListener('click', function () {
    const dropdown = document.getElementById('notifDropdown');
    if (dropdown) dropdown.classList.remove('show');
});

document.querySelectorAll('.notif-dropdown').forEach(function (dd) {
    dd.addEventListener('click', function (e) { e.stopPropagation(); });
});
</script>
<script src="/js/pwa.js"></script>
</body>
</html>
