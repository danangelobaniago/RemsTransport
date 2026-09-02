<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#111827">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <title>New Booking | Rem's Transport</title>
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/icons/icon-192.svg">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    <link rel="stylesheet" href="{{ asset('css/responsive.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .acting-banner {
            background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46;
            border-radius: 12px; padding: 18px 20px; margin-bottom: 24px;
            display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;
        }
        .acting-banner strong { display: block; font-size: 15px; margin-bottom: 2px; }
        .acting-banner p { margin: 0; font-size: 13px; color: #047857; }
        .acting-banner-actions { display: flex; gap: 10px; }
        .search-row { display: flex; gap: 10px; margin-bottom: 18px; }
        .search-row input { flex: 1; padding: 10px 14px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; }
        .customer-row-actions { text-align: right; }
        .empty-note { text-align: center; padding: 30px; color: #9ca3af; }
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
            <a href="/admin/book-for-customer" class="active">New Booking</a>
            <a href="/admin/joiner-trips">Joiner Trips</a>
            <a href="/admin/pricing">Pricing</a>
            <a href="/admin/reports">Reports</a>
            <a href="/admin/live-map">Live Map</a>
            <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
            <form id="logout-form" action="/logout" method="POST" style="display:none;">@csrf</form>
        </nav>
    </div>

    <div class="main">

        <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
            <button class="hamburger" onclick="toggleSidebar()" aria-label="Menu"><span></span><span></span><span></span></button>
            <h1 style="margin:0;">New Booking for a Customer</h1>
        </div>
        <p style="color:#6b7280;font-size:13px;margin:-12px 0 20px;">
            For walk-in customers. Pick or create the customer below, then you'll be sent to the normal booking pages
            (van, tour, or joiner trip) to complete it — payment still goes through the usual PayMongo checkout.
        </p>

        @if(session('success'))
            <div class="alert" style="background:#dcfce7;color:#166534;border:1px solid #bbf7d0;padding:12px 18px;border-radius:8px;margin-bottom:16px;">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif
        @if($errors->any())
            <div class="alert" style="background:#fee2e2;color:#991b1b;border:1px solid #fecaca;padding:12px 18px;border-radius:8px;margin-bottom:16px;">
                <i class="fas fa-times-circle"></i> {{ $errors->first() }}
            </div>
        @endif

        @if($actingAs)
            <div class="acting-banner">
                <div>
                    <strong><i class="fas fa-user-check"></i> Currently booking as: {{ $actingAs->first_name }} {{ $actingAs->last_name }}</strong>
                    <p>{{ $actingAs->email }} &bull; Any van, tour, or joiner trip booking you complete now will belong to this customer.</p>
                </div>
                <div class="acting-banner-actions">
                    <a href="/" class="btn" style="background:#059669;color:white;">
                        <i class="fas fa-arrow-right"></i> Go Book Now
                    </a>
                    <form method="POST" action="{{ route('admin.book_for_customer.stop') }}">
                        @csrf
                        <button type="submit" class="btn gray">Exit Walk-in Mode</button>
                    </form>
                </div>
            </div>
        @endif

        {{-- QUICK-CREATE NEW CUSTOMER --}}
        <div class="card" style="margin-bottom:24px;">
            <h3 style="margin-bottom:16px;font-size:16px;">New Customer (not yet in the system)</h3>
            <form method="POST" action="{{ route('admin.book_for_customer.create') }}">
                @csrf
                <div class="form-grid">
                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" name="first_name" required value="{{ old('first_name') }}">
                    </div>
                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text" name="last_name" required value="{{ old('last_name') }}">
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" required value="{{ old('email') }}">
                    </div>
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="text" name="phone_number" placeholder="09XXXXXXXXX" maxlength="11" required value="{{ old('phone_number') }}">
                    </div>
                    <div class="full-width">
                        <button type="submit" class="btn btn-approve" style="padding:10px 24px;">
                            <i class="fas fa-user-plus"></i> Create &amp; Start Booking
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- EXISTING CUSTOMERS --}}
        <div class="table-section table-responsive">
            <form method="GET" action="{{ route('admin.book_for_customer') }}" class="search-row">
                <input type="text" name="search" placeholder="Search by name, email, or phone..." value="{{ $search }}">
                <button type="submit" class="btn btn-approve">Search</button>
            </form>

            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th class="customer-row-actions">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                    <tr>
                        <td style="font-weight:600;">{{ $customer->first_name }} {{ $customer->last_name }}</td>
                        <td>{{ $customer->email }}</td>
                        <td>{{ $customer->phone_number ?? '—' }}</td>
                        <td class="customer-row-actions">
                            <form method="POST" action="{{ route('admin.book_for_customer.select') }}">
                                @csrf
                                <input type="hidden" name="customer_id" value="{{ $customer->id }}">
                                <button type="submit" class="btn small blue">
                                    <i class="fas fa-cart-plus"></i> Book for this Customer
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="empty-note">No customers found.</td></tr>
                    @endforelse
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
