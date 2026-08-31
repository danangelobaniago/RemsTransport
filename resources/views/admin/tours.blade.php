<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#111827">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Manage Tours | Rem's Transport</title>
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/icons/icon-192.svg">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    <link rel="stylesheet" href="{{ asset('css/responsive.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* --- CORE THEME --- */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background: #f5f7fb; color: #1f2937; }
        .container { display: flex; min-height: 100vh; }

        /* --- MAIN CONTENT --- */
        .main { margin-left: 240px; padding: 40px; width: calc(100% - 240px); }
        h1 { font-size: 26px; margin-bottom: 20px; }
        .card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); margin-bottom: 30px; }

        /* --- UPDATED 3-COLUMN GRID --- */
        .form-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; align-items: start; }
        .full-width { grid-column: span 3; }
        .form-group { display: flex; flex-direction: column; gap: 4px; }
        .form-group label { font-size: 12px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.4px; }

        input, select, textarea { padding: 12px; border: 1px solid #ddd; border-radius: 8px; width: 100%; font-size: 14px; background: #f9fafb; }
        input:focus { border-color: #2563eb; outline: none; background: white; }

        /* --- ALIGNED CHECKBOX --- */
        .checkbox-group { display: flex; align-items: center; gap: 10px; height: 45px; padding-bottom: 5px; }
        .checkbox-group input[type="checkbox"] { width: 18px; height: 18px; cursor: pointer; }
        .checkbox-group label { font-size: 14px; cursor: pointer; color: #4b5563; }

        .section-title { color: #2563eb; font-weight: 700; margin: 25px 0 15px 0; font-size: 16px; border-bottom: 1px solid #e5e7eb; padding-bottom: 8px; }

        /* --- MODAL STYLES --- */
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; }
        .modal-content { background: white; padding: 30px; border-radius: 12px; width: 90%; max-width: 800px; max-height: 80vh; overflow-y: auto; }
        .close-modal { float: right; font-size: 24px; cursor: pointer; color: #6b7280; }

        /* --- ACTION BUTTONS --- */
        .btn-action { padding: 8px 10px; border-radius: 6px; font-size: 12px; border: none; cursor: pointer; transition: 0.2s; color: white; display: inline-flex; align-items: center; justify-content: center; }
        .btn-manifest { background: #3b82f6; }
        .btn-complete { background: #10b981; }
        .btn-edit { background: #f59e0b; }
        .btn-approve { background: #10b981; }
        .btn-reject  { background: #ef4444; }
        .btn-action:hover { opacity: 0.8; transform: translateY(-1px); }

        /* --- TRIP STATUS BADGES --- */
        .ts-badge { display:inline-block; padding:3px 10px; border-radius:50px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.4px; }
        .ts-not-started { background:#f3f4f6; color:#6b7280; }
        .ts-arrived     { background:#dbeafe; color:#1d4ed8; }
        .ts-in-progress { background:#ede9fe; color:#6d28d9; }
        .ts-completed   { background:#dcfce7; color:#166534; }
        .pending-badge  { background:#fef3c7; color:#92400e; border-radius:50px; padding:2px 8px; font-size:11px; font-weight:700; }

        /* --- BOOKING CARD IN MODAL --- */
        .booking-card { border:1px solid #e5e7eb; border-radius:8px; padding:14px 16px; margin-bottom:12px; }
        .booking-card.status-pending { border-left:4px solid #f59e0b; }
        .booking-card.status-approved { border-left:4px solid #10b981; }
        .booking-card.status-rejected { border-left:4px solid #ef4444; opacity:.6; }

        /* --- TABLE --- */
        .table-card { background: white; padding: 20px; border-radius: 12px; overflow: hidden; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f9fafb; padding: 14px; text-align: left; font-size: 12px; color: #6b7280; border-bottom: 2px solid #eee; text-transform: uppercase; }
        td { padding: 14px; border-bottom: 1px solid #f1f1f1; vertical-align: middle; font-size: 13px; }

        .btn { padding: 8px 16px; border-radius: 6px; font-size: 13px; font-weight: 600; display: inline-flex; align-items: center; justify-content: center; gap: 8px; transition: 0.2s; border: none; cursor: pointer; }
        .primary { background: #2563eb; color: white; width: 100%; margin-top: 20px; padding: 14px; }
        .btn-delete { background: #fee2e2; color: #ef4444; }
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
            <a href="/admin/tours" class="active">Tours</a>
            <a href="/admin/customers">Customers</a>
            <a href="/admin/joiner-trips">Joiner Trips</a>
            <a href="/admin/pricing">Pricing</a>
            <a href="/admin/reports">Reports</a>
            <a href="/admin/live-map">Live Map</a>
            <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
            <form id="logout-form" action="/logout" method="POST" style="display: none;">@csrf</form>
        </nav>
    </div>

    <div class="main">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
            <button class="hamburger" onclick="toggleSidebar()" aria-label="Menu"><span></span><span></span><span></span></button>
            <h1 style="margin:0;">Manage Tour Packages</h1>
        </div>

        @if(session('success')) <div class="alert success" style="background:#dcfce7; color:#16a34a; padding:15px; border-radius:8px; margin-bottom:20px;"><i class="fas fa-check-circle"></i> {{ session('success') }}</div> @endif

        <div class="card">
            <h3>Add Fixed Tour Package</h3>
            <form method="POST" action="/admin/tours/add" enctype="multipart/form-data">
                @csrf
                {{-- hidden: auto-filled from selected van's seats --}}
                <input type="hidden" name="max_passengers" id="tour_max_passengers">

                {{-- LOGISTICS FIRST --}}
                <h4 class="section-title"><i class="fas fa-shuttle-van"></i> Logistics Assignment</h4>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;margin-bottom:20px;">
                    <div class="form-group">
                        <label>Select Van</label>
                        <select name="van_id" id="tourVanSelect" required onchange="onTourVanChange(this)">
                            <option value="">-- Choose Van --</option>
                            @foreach($vans as $van)
                                <option value="{{ $van->id }}"
                                        data-name="{{ $van->name }}"
                                        data-plate="{{ $van->plate_number }}"
                                        data-seats="{{ $van->seats }}">
                                    {{ $van->name }} — {{ $van->plate_number }} ({{ $van->seats }} pax)
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Select Driver</label>
                        <select name="driver_id" id="tourDriverSelect" required>
                            <option value="">-- Choose Driver --</option>
                            @foreach($drivers as $driver)
                                <option value="{{ $driver->id }}"
                                        data-name="{{ $driver->name }}"
                                        data-license="{{ $driver->license_number }}">
                                    {{ $driver->name }} — {{ $driver->license_number }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- PACKAGE DETAILS --}}
                <h4 class="section-title"><i class="fas fa-suitcase"></i> Package Details</h4>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Package Name</label>
                        <input type="text" name="name" placeholder="e.g. Tagaytay Day Tour" required>
                    </div>
                    <div class="form-group">
                        <label>Fixed Destination</label>
                        <input type="text" name="destination" placeholder="e.g. Tagaytay City, Cavite" required>
                    </div>
                    <div class="form-group">
                        <label>Duration</label>
                        <input type="text" name="duration" placeholder="e.g. 3D2N" required>
                    </div>

                    {{-- Row: Meet-up Point (2 cols) + Pick-up Time (1 col) --}}
                    <div class="form-group" style="grid-column: span 2;">
                        <label>Meet-up Point</label>
                        <input type="text" name="pickup_point" placeholder="e.g. SM Mall of Asia, Pasay City" required>
                    </div>
                    <div class="form-group">
                        <label>Pick-up Time</label>
                        <input type="time" name="pickup_time" required>
                    </div>

                    {{-- Row: Date Range (2 cols) + Price (1 col) --}}
                    <div class="form-group" style="grid-column: span 2;">
                        <label>Booking Date Range <span style="color:#94a3b8;font-weight:400;text-transform:none;font-size:11px;">(Customers can only book within this range)</span></label>
                        <div style="display:flex;align-items:stretch;gap:0;border:1px solid #ddd;border-radius:10px;overflow:hidden;background:#f9fafb;">
                            <div style="flex:1;padding:10px 14px;">
                                <div style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;">From</div>
                                <input type="date" name="tour_date" id="tour_date" required
                                       min="{{ date('Y-m-d') }}"
                                       style="border:none;background:transparent;padding:4px 0;font-size:14px;font-weight:700;color:#1e293b;width:100%;"
                                       onchange="onTourStartDateChange(this)">
                            </div>
                            <div style="display:flex;align-items:center;padding:0 10px;color:#cbd5e1;font-size:18px;background:#f1f5f9;border-left:1px solid #ddd;border-right:1px solid #ddd;">→</div>
                            <div style="flex:1;padding:10px 14px;">
                                <div style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;">To</div>
                                <input type="date" name="end_date" id="end_date" required
                                       min="{{ date('Y-m-d') }}"
                                       style="border:none;background:transparent;padding:4px 0;font-size:14px;font-weight:700;color:#1e293b;width:100%;"
                                       onchange="validateTourDate(this)">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Price (₱)</label>
                        <input type="number" name="price" placeholder="e.g. 3500" required>
                    </div>

                    {{-- Row: Checkbox (full width) --}}
                    <div class="form-group" style="grid-column: span 3; padding-bottom: 4px;">
                        <div class="checkbox-group">
                            <input type="checkbox" name="is_best_seller" id="best_seller">
                            <label for="best_seller">Mark as Best Seller</label>
                        </div>
                    </div>

                    <div class="form-group full-width">
                        <label>Description & Inclusions</label>
                        <textarea name="description" placeholder="List what's included in the package..." required style="min-height: 80px;"></textarea>
                    </div>
                </div>

                <div style="margin-top: 15px;">
                    <label style="font-size: 12px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.4px;">Thumbnail Image</label><br><br>
                    <input type="file" name="image" accept="image/*" required style="border: none; padding: 0;">
                </div>
                <button type="submit" class="btn primary">Create Tour Package</button>
            </form>
        </div>

        <div class="card table-card">
            <h3>Active Packages</h3>
            <table>
                <thead>
                    <tr>
                        <th style="width: 80px;">Image</th>
                        <th>Package</th>
                        <th>Logistics</th>
                        <th>Schedule</th>
                        <th>Status</th>
                        <th>Price</th>
                        <th style="text-align: center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tours as $tour)
                    @php
                        $ts = $tour->trip_status ?? 'not_started';
                        $tsBadgeClass = match($ts) {
                            'arrived'     => 'ts-arrived',
                            'in_progress' => 'ts-in-progress',
                            'completed'   => 'ts-completed',
                            default       => 'ts-not-started',
                        };
                        $tsLabel = match($ts) {
                            'arrived'     => 'Arrived at Pickup',
                            'in_progress' => 'Ongoing Trip',
                            'completed'   => 'Completed',
                            default       => 'Not Started',
                        };
                    @endphp
                    <tr id="tour-row-{{ $tour->id }}">
                        <td><img src="{{ \Storage::disk('public')->url($tour->image) }}" width="60" height="40" style="object-fit: cover; border-radius: 4px; border: 1px solid #eee;"></td>
                        <td>
                            <strong>{{ $tour->name }}</strong><br>
                            <small><i class="fas fa-map-marker-alt"></i> {{ $tour->destination }}</small>
                            @if($tour->pending_count > 0)
                                <br><span class="pending-badge"><i class="fas fa-clock"></i> {{ $tour->pending_count }} pending approval</span>
                            @endif
                        </td>
                        <td><small><strong>Van:</strong> {{ $tour->van }}</small><br><small><strong>Driver:</strong> {{ $tour->driver_name }}</small></td>
                        <td><small><i class="far fa-calendar-alt"></i> {{ date('M d', strtotime($tour->tour_date)) }}</small><br><small><i class="far fa-clock"></i> {{ date('g:i A', strtotime($tour->pickup_time)) }}</small></td>
                        <td><span class="ts-badge {{ $tsBadgeClass }}">{{ $tsLabel }}</span>
                            @if($tour->approved_count > 0)
                                <br><small style="color:#16a34a;font-size:11px;"><i class="fas fa-check"></i> {{ $tour->approved_count }} approved</small>
                            @endif
                        </td>
                        <td><strong>₱{{ number_format($tour->price) }}</strong></td>
                        <td style="text-align: center;">
                            <div style="display: flex; gap: 5px; justify-content: center;">
                                <button class="btn-action btn-manifest" onclick="openManifest({{ $tour->id }}, '{{ addslashes($tour->name) }}')" title="View Bookings & Manifest"><i class="fas fa-users"></i></button>
                                @if($tour->approved_count > 0)
                                    <button class="btn-action btn-edit" disabled style="opacity:.4; cursor:not-allowed;" title="Cannot edit — this package already has an approved booking"><i class="fas fa-edit"></i></button>
                                    <button class="btn-action btn-delete" disabled style="opacity:.4; cursor:not-allowed;" title="Cannot delete — this package already has an approved booking"><i class="fas fa-trash"></i></button>
                                @else
                                    <button class="btn-action btn-edit" onclick="fillEditForm({{ json_encode($tour) }})" title="Edit"><i class="fas fa-edit"></i></button>
                                    <form action="/admin/tours/delete/{{ $tour->id }}" method="POST" style="display:inline;">@csrf @method('DELETE')<button type="submit" class="btn-action btn-delete" onclick="return confirm('Delete this tour and all its bookings?')"><i class="fas fa-trash"></i></button></form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" style="text-align:center; padding: 30px; color: #9ca3af;">No active tour packages found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="manifestModal" class="modal">
    <div class="modal-content" style="max-width:900px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h2 id="manifestTitle" style="font-size:18px;">Bookings & Manifest</h2>
            <div style="display:flex; align-items:center; gap:10px;">
                <button onclick="printManifest()" style="background:#1f2937;color:white;border:none;padding:8px 14px;border-radius:6px;cursor:pointer;font-size:13px;"><i class="fas fa-print"></i> Print</button>
                <span onclick="closeManifest()" style="font-size:24px;cursor:pointer;color:#6b7280;line-height:1;">&times;</span>
            </div>
        </div>
        <hr style="margin-bottom:16px;border:0;border-top:1px solid #eee;">

        {{-- Bookings Section --}}
        <h4 style="font-size:13px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.5px;margin-bottom:12px;"><i class="fas fa-clipboard-list"></i> Customer Bookings</h4>
        <div id="bookingsData"><p style="text-align:center;color:#9ca3af;">Loading...</p></div>

        <hr style="margin:20px 0;border:0;border-top:1px solid #eee;">

        {{-- Passenger Manifest --}}
        <h4 style="font-size:13px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.5px;margin-bottom:12px;"><i class="fas fa-users"></i> Full Passenger Manifest</h4>
        <div id="manifestData"></div>
    </div>
</div>

<script>
    const CSRF = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').content : '{{ csrf_token() }}';

    // ── APPROVE / REJECT tour booking ──────────────────────────────────────
    let _currentManifestTourId = null;

    function tourBookingAction(bookingId, action) {
        const label = action === 'approve' ? 'Approve' : 'Reject';
        if (!confirm(label + ' this booking?')) return;
        fetch(`/admin/tour-bookings/${bookingId}/${action}`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json' },
        }).then(r => {
            if (r.ok || r.redirected) {
                // Reload bookings section in the modal
                loadBookings(_currentManifestTourId);
                // Reload page silently to refresh table badges
                setTimeout(() => location.reload(), 800);
            }
        });
    }

    function loadBookings(tourId) {
        document.getElementById('bookingsData').innerHTML = '<p style="text-align:center;color:#9ca3af;">Loading...</p>';
        fetch(`/admin/tours/${tourId}/bookings`)
            .then(r => r.json())
            .then(bookings => {
                if (!bookings.length) {
                    document.getElementById('bookingsData').innerHTML = '<p style="color:#9ca3af;text-align:center;padding:20px 0;">No bookings yet.</p>';
                    return;
                }
                let html = '';
                bookings.forEach(b => {
                    const stMap = { approved:'status-approved', rejected:'status-rejected' };
                    const cardCls = stMap[b.status] || 'status-pending';
                    const stLabel = {
                        approved: '<span style="background:#dcfce7;color:#166534;padding:2px 10px;border-radius:50px;font-size:11px;font-weight:700;">APPROVED</span>',
                        rejected: '<span style="background:#fee2e2;color:#991b1b;padding:2px 10px;border-radius:50px;font-size:11px;font-weight:700;">REJECTED</span>',
                        downpayment_paid: '<span style="background:#fef3c7;color:#92400e;padding:2px 10px;border-radius:50px;font-size:11px;font-weight:700;">DOWNPAYMENT PAID</span>',
                        fully_paid: '<span style="background:#dbeafe;color:#1d4ed8;padding:2px 10px;border-radius:50px;font-size:11px;font-weight:700;">FULLY PAID</span>',
                        pending: '<span style="background:#f3f4f6;color:#374151;padding:2px 10px;border-radius:50px;font-size:11px;font-weight:700;">PENDING</span>',
                    }[b.status] || `<span style="background:#f3f4f6;color:#374151;padding:2px 10px;border-radius:50px;font-size:11px;font-weight:700;">${b.status.toUpperCase()}</span>`;

                    const paid = parseFloat(b.amount_paid || b.downpayment || 0);
                    const total = parseFloat(b.total || 0);
                    const balance = Math.max(0, total - paid);

                    let paxRows = '';
                    if (b.passenger_list && b.passenger_list.length) {
                        b.passenger_list.forEach(p => {
                            paxRows += `<span style="display:inline-block;background:#f9fafb;border:1px solid #e5e7eb;border-radius:6px;padding:4px 10px;margin:2px;font-size:12px;">${p.first_name} ${p.last_name}</span>`;
                        });
                    }

                    const actionBtns = (b.status !== 'approved' && b.status !== 'rejected')
                        ? `<div style="display:flex;gap:6px;margin-top:10px;">
                               <button onclick="tourBookingAction(${b.id},'approve')" class="btn-action btn-approve" style="padding:6px 14px;font-size:12px;"><i class="fas fa-check"></i> Approve</button>
                               <button onclick="tourBookingAction(${b.id},'reject')"  class="btn-action btn-reject"  style="padding:6px 14px;font-size:12px;"><i class="fas fa-times"></i> Reject</button>
                           </div>`
                        : '';

                    html += `<div class="booking-card ${cardCls}">
                        <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:8px;">
                            <div>
                                <strong style="font-size:14px;">${b.first_name} ${b.last_name}</strong>
                                <span style="color:#6b7280;font-size:12px;margin-left:8px;">${b.email}</span>
                                <div style="font-size:12px;color:#6b7280;margin-top:2px;">${b.seat_count} seat(s) &middot; Total: ₱${total.toLocaleString('en-PH',{minimumFractionDigits:2})} &middot; Paid: ₱${paid.toLocaleString('en-PH',{minimumFractionDigits:2})} &middot; Balance: ₱${balance.toLocaleString('en-PH',{minimumFractionDigits:2})}</div>
                            </div>
                            ${stLabel}
                        </div>
                        <div style="margin-top:8px;">${paxRows}</div>
                        ${actionBtns}
                    </div>`;
                });
                document.getElementById('bookingsData').innerHTML = html;
            });
    }

    // ── MANIFEST (passengers only, for print) ─────────────────────────────
    function loadPassengerManifest(tourId) {
        fetch(`/admin/tours/manifest/${tourId}`)
            .then(res => res.json())
            .then(data => {
                const calcAge = dob => { if (!dob) return '—'; const b = new Date(dob), t = new Date(); let a = t.getFullYear()-b.getFullYear(); const m=t.getMonth()-b.getMonth(); if(m<0||(m===0&&t.getDate()<b.getDate())) a--; return a+''; };
                let html = '<table style="width:100%;border-collapse:collapse;font-size:13px;"><thead><tr style="background:#f9fafb;"><th style="padding:10px;border-bottom:2px solid #eee;text-align:left;">NAME</th><th style="padding:10px;border-bottom:2px solid #eee;text-align:left;">BIRTHDAY</th><th style="padding:10px;border-bottom:2px solid #eee;text-align:left;">AGE</th><th style="padding:10px;border-bottom:2px solid #eee;text-align:left;">GENDER</th></tr></thead><tbody>';
                if (!data.length) html += '<tr><td colspan="4" style="text-align:center;padding:20px;color:#9ca3af;">No passengers found.</td></tr>';
                else data.forEach(p => { html += `<tr><td style="padding:10px;border-bottom:1px solid #f1f1f1;">${p.first_name} ${p.last_name}</td><td style="padding:10px;border-bottom:1px solid #f1f1f1;">${p.birthday ?? '—'}</td><td style="padding:10px;border-bottom:1px solid #f1f1f1;">${calcAge(p.birthday)}</td><td style="padding:10px;border-bottom:1px solid #f1f1f1;">${p.gender ?? '—'}</td></tr>`; });
                html += '</tbody></table>';
                document.getElementById('manifestData').innerHTML = html;
            });
    }

    function openManifest(tourId, tourName) {
        _currentManifestTourId = tourId;
        document.getElementById('manifestTitle').innerText = tourName + ' — Bookings & Manifest';
        document.getElementById('manifestModal').style.display = 'flex';
        document.getElementById('manifestData').innerHTML = '<p style="text-align:center;color:#9ca3af;">Loading...</p>';
        loadBookings(tourId);
        loadPassengerManifest(tourId);
    }

    function closeManifest() { document.getElementById('manifestModal').style.display = 'none'; }
    function printManifest() {
        const title = document.getElementById('manifestTitle').innerText;
        const table = document.getElementById('manifestData').innerHTML;
        const pw = window.open('', '', 'height=600,width=800');
        pw.document.write(`<html><head><style>body{font-family:sans-serif;padding:20px;} table{width:100%;border-collapse:collapse;} th,td{border:1px solid #ddd;padding:10px;text-align:left;} th{background:#f2f2f2;}</style></head><body><h1>${title}</h1>${table}</body></html>`);
        pw.document.close(); pw.print();
    }
    window.onclick = e => { if (e.target == document.getElementById('manifestModal')) closeManifest(); }

    // ── VAN / DATE LOGIC ──────────────────────────────────────────────────
    // Blocked dates for the currently selected van
    window.tourBlockedDates = [];

    function onTourVanChange(select) {
        const opt = select.options[select.selectedIndex];
        if (!opt.value) { window.tourBlockedDates = []; return; }

        // Auto-fill hidden max_passengers from van seats
        document.getElementById('tour_max_passengers').value = opt.getAttribute('data-seats') || '';

        // Fetch blocked dates for this van (covers regular bookings + joiner trips + tours)
        fetch(`/booked-dates/${opt.value}`)
            .then(r => r.json())
            .then(dates => {
                window.tourBlockedDates = dates;
                // Re-validate currently entered dates
                const sd = document.getElementById('tour_date');
                const ed = document.getElementById('end_date');
                if (sd.value) validateTourDate(sd);
                if (ed.value) validateTourDate(ed);
            })
            .catch(() => { window.tourBlockedDates = []; });
    }

    function validateTourDate(input) {
        if (!input.value) return;
        if (window.tourBlockedDates.includes(input.value)) {
            alert('❌ The selected date (' + input.value + ') is already booked for this van. Please choose another date.');
            input.value = '';
        }
    }

    function onTourStartDateChange(input) {
        // Update end date minimum to match start date
        document.getElementById('end_date').min = input.value || '{{ date("Y-m-d") }}';
        validateTourDate(input);
    }

    function fillEditForm(tour) {
        const form = document.querySelector('form[action*="/admin/tours/add"]');
        form.action = `/admin/tours/update/${tour.id}`;
        form.querySelector('button[type="submit"]').innerText = "Update Tour Package";
        form.querySelector('button[type="submit"]').style.background = "#f59e0b";
        document.querySelector('input[name="name"]').value = tour.name;
        document.querySelector('input[name="destination"]').value = tour.destination;
        document.querySelector('input[name="duration"]').value = tour.duration;
        document.querySelector('input[name="pickup_point"]').value = tour.pickup_point;
        document.querySelector('input[name="tour_date"]').value = tour.tour_date;
        document.querySelector('input[name="end_date"]').value = tour.end_date;
        document.querySelector('input[name="pickup_time"]').value = tour.pickup_time;
        document.querySelector('input[name="price"]').value = tour.price;
        document.getElementById('tour_max_passengers').value = tour.max_passengers;
        document.querySelector('textarea[name="description"]').value = tour.description;
        document.getElementById('best_seller').checked = tour.is_best_seller == 1;
        document.querySelector('input[name="image"]').required = false;
        window.scrollTo({ top: 0, behavior: 'smooth' });
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
