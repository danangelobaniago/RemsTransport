<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="theme-color" content="#111827">
<meta name="apple-mobile-web-app-capable" content="yes">
<title>Customers | Admin</title>
<link rel="manifest" href="/manifest.json">
<link rel="apple-touch-icon" href="/icons/icon-192.svg">
<link rel="stylesheet" href="{{ asset('css/admin.css') }}">
<link rel="stylesheet" href="{{ asset('css/responsive.css') }}">
<style>
.page-header { margin-bottom: 24px; }
.page-header h1 { margin: 0; }
.page-header p { color: #6b7280; margin: 4px 0 0; }

.table-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    overflow: hidden;
}

table { width: 100%; border-collapse: collapse; }
thead { background: #f9fafb; }
th {
    text-align: left;
    padding: 12px 16px;
    font-size: 12px;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
td { padding: 14px 16px; border-top: 1px solid #f3f4f6; vertical-align: middle; }
tbody tr:hover { background: #f9fafb; }

.customer-name {
    font-weight: 600;
    color: #2563eb;
    cursor: pointer;
    text-decoration: none;
}
.customer-name:hover { text-decoration: underline; }

.badge-count {
    background: #2563eb;
    color: #fff;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

/* MODAL */
.modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.45);
    z-index: 1000;
    justify-content: center;
    align-items: flex-start;
    padding: 40px 20px;
    overflow-y: auto;
}
.modal-overlay.open { display: flex; }

.modal-box {
    background: #fff;
    border-radius: 16px;
    width: 100%;
    max-width: 680px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.15);
    overflow: hidden;
    margin: auto;
}

.modal-header {
    padding: 20px 24px 16px;
    border-bottom: 1px solid #f3f4f6;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.modal-header h2 { margin: 0; font-size: 20px; }
.modal-close {
    background: none;
    border: none;
    font-size: 22px;
    cursor: pointer;
    color: #6b7280;
    line-height: 1;
    padding: 4px 8px;
}
.modal-close:hover { color: #111; }

.modal-body { padding: 24px; }

.info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-bottom: 28px;
}
.info-item label {
    font-size: 11px;
    color: #9ca3af;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: block;
    margin-bottom: 4px;
}
.info-item span { font-weight: 600; font-size: 14px; color: #111827; }

.bookings-title {
    font-size: 14px;
    font-weight: 700;
    color: #374151;
    margin-bottom: 12px;
    padding-bottom: 8px;
    border-bottom: 1px solid #f3f4f6;
}

.booking-item {
    padding: 12px 14px;
    border-radius: 10px;
    border: 1px solid #e5e7eb;
    margin-bottom: 10px;
}
.booking-item-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 6px;
}
.booking-destination { font-weight: 600; font-size: 14px; }
.booking-date { font-size: 12px; color: #6b7280; margin-top: 2px; }
.booking-total { font-size: 13px; color: #16a34a; font-weight: 600; margin-top: 4px; }

.status-pill {
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
}
.status-completed   { background: #dcfce7; color: #16a34a; }
.status-pending     { background: #fef9c3; color: #a16207; }
.status-approved    { background: #dbeafe; color: #1d4ed8; }
.status-rejected    { background: #fee2e2; color: #dc2626; }
.status-cancelled   { background: #fee2e2; color: #dc2626; }
.status-downpayment_paid { background: #ede9fe; color: #6d28d9; }
.status-fully_paid  { background: #dcfce7; color: #16a34a; }

.no-bookings { color: #9ca3af; font-size: 13px; text-align: center; padding: 20px 0; }

/* BOOKING CLICKABLE */
.booking-item { cursor: pointer; transition: border-color 0.15s; }
.booking-item:hover { border-color: #2563eb; background: #f0f7ff; }

/* MANIFESTO PANEL */
.manifesto-panel { display: none; }
.manifesto-panel.open { display: block; }
.back-btn {
    background: none;
    border: none;
    color: #2563eb;
    font-size: 13px;
    cursor: pointer;
    padding: 0 0 16px;
    display: flex;
    align-items: center;
    gap: 6px;
    font-weight: 600;
}
.back-btn:hover { text-decoration: underline; }
.manifesto-booking-title {
    font-size: 15px;
    font-weight: 700;
    margin-bottom: 4px;
}
.manifesto-booking-sub {
    font-size: 12px;
    color: #6b7280;
    margin-bottom: 20px;
}
.manifesto-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.manifesto-table th {
    background: #f9fafb;
    padding: 9px 12px;
    text-align: left;
    font-size: 11px;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 1px solid #e5e7eb;
}
.manifesto-table td {
    padding: 10px 12px;
    border-bottom: 1px solid #f3f4f6;
    vertical-align: middle;
}
.manifesto-table tr:last-child td { border-bottom: none; }
.no-passengers { color: #9ca3af; font-size: 13px; padding: 16px 0; text-align: center; }
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
            <a href="/admin/customers" class="active">Customers</a>
            <a href="/admin/joiner-trips">Joiner Trips</a>
            <a href="/admin/pricing">Pricing</a>
            <a href="/admin/reports">Reports</a>
            <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
            <form id="logout-form" action="/logout" method="POST" style="display:none;">@csrf</form>
        </nav>
    </div>

    <div class="main">

        <div class="page-header" style="display:flex; justify-content:space-between; align-items:flex-end;">
            <div style="display:flex;align-items:center;gap:10px;">
                <button class="hamburger" onclick="toggleSidebar()" aria-label="Menu"><span></span><span></span><span></span></button>
                <h1>Customers</h1>
                <p id="customer-count">{{ $customers->count() }} registered customer(s)</p>
            </div>
            <div style="display:flex; gap:8px;">
                <input type="text" id="search-input" placeholder="Search name, email, or contact..."
                    oninput="searchCustomers()"
                    style="padding:8px 14px; border:1px solid #d1d5db; border-radius:8px; font-size:13px; width:260px; outline:none;">
                <button onclick="searchCustomers()"
                    style="padding:8px 18px; background:#2563eb; color:#fff; border:none; border-radius:8px; font-size:13px; cursor:pointer; font-weight:600;">
                    Search
                </button>
            </div>
        </div>

        <div class="table-card">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Contact Number</th>
                        <th>Total Bookings</th>
                    </tr>
                </thead>
                <tbody id="customer-tbody">
                    @forelse($customers as $customer)
                    <tr class="customer-row"
                        data-name="{{ strtolower($customer->first_name . ' ' . $customer->last_name) }}"
                        data-email="{{ strtolower($customer->email) }}"
                        data-phone="{{ $customer->phone_number ?? '' }}">
                        <td>
                            <span class="customer-name" onclick="openModal({{ $customer->id }})">
                                {{ $customer->first_name }} {{ $customer->last_name }}
                            </span>
                        </td>
                        <td>{{ $customer->email }}</td>
                        <td>{{ $customer->phone_number ?? '—' }}</td>
                        <td><span class="badge-count">{{ $customer->total_bookings }}</span></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" style="text-align:center; color:#9ca3af; padding:30px;">No customers found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</div>

<!-- MODAL -->
<div class="modal-overlay" id="modal" onclick="closeModal(event)">
    <div class="modal-box">
        <div class="modal-header">
            <h2 id="modal-name"></h2>
            <button class="modal-close" onclick="closeModalBtn()">&#x2715;</button>
        </div>
        <div class="modal-body">

            <!-- CUSTOMER INFO + BOOKING LIST -->
            <div id="panel-customer">
                <div class="info-grid">
                    <div class="info-item">
                        <label>Email</label>
                        <span id="modal-email"></span>
                    </div>
                    <div class="info-item">
                        <label>Contact Number</label>
                        <span id="modal-phone"></span>
                    </div>
                    <div class="info-item">
                        <label>Birthday</label>
                        <span id="modal-birthday"></span>
                    </div>
                    <div class="info-item">
                        <label>Total Bookings</label>
                        <span id="modal-total"></span>
                    </div>
                </div>
                <div class="bookings-title">Booking History <span style="font-size:11px;color:#9ca3af;font-weight:400;">(click a booking to see manifesto)</span></div>
                <div id="modal-bookings"></div>
            </div>

            <!-- MANIFESTO PANEL -->
            <div id="panel-manifesto" class="manifesto-panel">
                <button class="back-btn" onclick="showCustomerPanel()">&#8592; Back to bookings</button>
                <div id="manifesto-booking-title" class="manifesto-booking-title"></div>
                <div id="manifesto-booking-sub" class="manifesto-booking-sub"></div>
                <table class="manifesto-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Birthday</th>
                            <th>Gender</th>
                        </tr>
                    </thead>
                    <tbody id="manifesto-body"></tbody>
                </table>
            </div>

        </div>
    </div>
</div>

<script>
const customers  = @json($customers);
const bookings   = @json($allBookings);
const passengers = @json($passengers);

function openModal(userId) {
    const c = customers.find(x => x.id == userId);
    if (!c) return;

    document.getElementById('modal-name').textContent     = c.first_name + ' ' + c.last_name;
    document.getElementById('modal-email').textContent    = c.email;
    document.getElementById('modal-phone').textContent    = c.phone_number || '—';
    document.getElementById('modal-birthday').textContent = c.birthday || '—';
    document.getElementById('modal-total').textContent    = c.total_bookings + ' booking(s)';

    const userBookings = bookings[userId] || [];
    const container    = document.getElementById('modal-bookings');

    if (userBookings.length === 0) {
        container.innerHTML = '<p class="no-bookings">No bookings yet.</p>';
    } else {
        container.innerHTML = userBookings.map(b => {
            const statusClass = 'status-' + b.status;
            const label = b.status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
            return `
                <div class="booking-item" onclick="showManifesto(${b.id}, '${b.destination.replace(/'/g,"\\'")}', '${b.start_date}', '${b.end_date || b.start_date}')">
                    <div class="booking-item-top">
                        <span class="booking-destination">${b.destination}</span>
                        <span class="status-pill ${statusClass}">${label}</span>
                    </div>
                    <div class="booking-date">${b.start_date}${b.end_date && b.end_date !== b.start_date ? ' → ' + b.end_date : ''} &nbsp;|&nbsp; ${b.van || 'N/A'}</div>
                    <div class="booking-total">₱${parseFloat(b.total).toLocaleString('en-PH', {minimumFractionDigits:2})}</div>
                </div>`;
        }).join('');
    }

    showCustomerPanel();
    document.getElementById('modal').classList.add('open');
}

function showManifesto(bookingId, destination, startDate, endDate) {
    document.getElementById('manifesto-booking-title').textContent = destination;
    document.getElementById('manifesto-booking-sub').textContent   =
        startDate + (endDate && endDate !== startDate ? ' → ' + endDate : '');

    const pax    = passengers[bookingId] || [];
    const tbody  = document.getElementById('manifesto-body');

    if (pax.length === 0) {
        tbody.innerHTML = `<tr><td colspan="4" class="no-passengers">No passenger manifesto recorded.</td></tr>`;
    } else {
        tbody.innerHTML = pax.map((p, i) => {
            const name = [p.first_name, p.middle_name, p.last_name].filter(Boolean).join(' ');
            return `<tr>
                <td>${i + 1}</td>
                <td>${name}</td>
                <td>${p.birthday || '—'}</td>
                <td>${p.gender ? p.gender.charAt(0).toUpperCase() + p.gender.slice(1) : '—'}</td>
            </tr>`;
        }).join('');
    }

    document.getElementById('panel-customer').style.display  = 'none';
    document.getElementById('panel-manifesto').classList.add('open');
}

function showCustomerPanel() {
    document.getElementById('panel-manifesto').classList.remove('open');
    document.getElementById('panel-customer').style.display = 'block';
}

function closeModalBtn() {
    document.getElementById('modal').classList.remove('open');
}

function closeModal(e) {
    if (e.target === document.getElementById('modal')) closeModalBtn();
}

function searchCustomers() {
    const q     = document.getElementById('search-input').value.toLowerCase().trim();
    const rows  = document.querySelectorAll('.customer-row');
    let visible = 0;

    rows.forEach(row => {
        const match = row.dataset.name.includes(q)
                   || row.dataset.email.includes(q)
                   || row.dataset.phone.includes(q);
        row.style.display = match ? '' : 'none';
        if (match) visible++;
    });

    document.getElementById('customer-count').textContent =
        q ? `${visible} result(s) for "${q}"` : `${rows.length} registered customer(s)`;
}
</script>
<script>
function toggleSidebar() {
    document.getElementById('adminSidebar').classList.toggle('open');
    document.getElementById('sidebarOverlay').classList.toggle('open');
}
</script>
<script src="/js/pwa.js"></script>
</body>
</html>
