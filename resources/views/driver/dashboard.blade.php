<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#1e293b">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="Rem's Driver">
    <title>Driver Portal | Rem's Transport</title>
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/icons/icon-192.svg">
    <link rel="stylesheet" href="{{ asset('css/responsive.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { background: #f1f5f9; color: #1e293b; min-height: 100vh; }

        /* ── NAVBAR ── */
        .navbar {
            background: #1e293b; color: white; padding: 0 28px;
            display: flex; justify-content: space-between; align-items: center;
            height: 58px; box-shadow: 0 2px 8px rgba(0,0,0,0.25); position: sticky; top: 0; z-index: 100;
        }
        .brand { font-size: 17px; font-weight: 700; color: #60a5fa; display: flex; align-items: center; gap: 8px; }
        .nav-right { display: flex; align-items: center; gap: 14px; font-size: 13px; }
        .driver-badge { color: #94a3b8; display: flex; align-items: center; gap: 7px; }
        .dot-green { width: 8px; height: 8px; border-radius: 50%; background: #4ade80; }
        .logout-btn {
            background: #ef4444; color: white; border: none; padding: 7px 14px;
            border-radius: 6px; cursor: pointer; font-size: 13px; font-weight: 600;
        }

        /* ── MAIN ── */
        .main { max-width: 1200px; margin: 24px auto; padding: 0 20px; }

        /* ── ALERTS ── */
        .alert { padding: 12px 18px; border-radius: 8px; margin-bottom: 18px; font-size: 14px; display: flex; align-items: center; gap: 8px; }
        .alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .alert-error   { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

        /* ── PROFILE CARD ── */
        .profile-card {
            background: linear-gradient(135deg, #1e40af 0%, #2563eb 100%);
            color: white; padding: 20px 28px; border-radius: 14px;
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 22px; box-shadow: 0 4px 18px rgba(37,99,235,0.3);
        }
        .profile-name { font-size: 20px; font-weight: 700; margin-bottom: 5px; }
        .profile-meta { font-size: 13px; opacity: 0.85; display: flex; gap: 18px; flex-wrap: wrap; }
        .profile-meta span { display: flex; align-items: center; gap: 6px; }
        .stat-box { text-align: right; }
        .stat-num { font-size: 30px; font-weight: 800; line-height: 1; }
        .stat-lbl { font-size: 12px; opacity: 0.75; margin-top: 3px; }

        /* ── NEXT UP CARD ── */
        .next-up-card {
            background: white; border-radius: 14px; border-left: 5px solid #f59e0b;
            box-shadow: 0 1px 6px rgba(0,0,0,0.08); padding: 18px 22px; margin-bottom: 22px;
            display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap;
        }
        .next-up-label {
            display: inline-flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 800;
            text-transform: uppercase; letter-spacing: 0.5px; color: #b45309; margin-bottom: 6px;
        }
        .next-up-dest { font-size: 17px; font-weight: 800; color: #1e293b; margin-bottom: 4px; }
        .next-up-meta { display: flex; flex-wrap: wrap; gap: 14px; font-size: 12px; color: #64748b; }
        .next-up-meta span { display: flex; align-items: center; gap: 5px; }
        .next-up-actions { display: flex; gap: 10px; flex-wrap: wrap; }
        .next-up-actions a {
            display: inline-flex; align-items: center; gap: 6px; padding: 10px 16px; border-radius: 8px;
            font-size: 13px; font-weight: 700; text-decoration: none; white-space: nowrap;
        }
        .next-up-btn-navigate { background: #fef3c7; color: #92400e; }
        .next-up-btn-manifest { background: #1e293b; color: white; }

        /* ── TWO-COLUMN LAYOUT ── */
        .dashboard-grid {
            display: grid;
            grid-template-columns: 380px 1fr;
            gap: 20px;
            align-items: start;
        }
        @media (max-width: 900px) { .dashboard-grid { grid-template-columns: 1fr; } }

        /* ── CALENDAR PANEL ── */
        .calendar-panel {
            background: white; border-radius: 14px;
            box-shadow: 0 1px 6px rgba(0,0,0,0.08); overflow: hidden; position: sticky; top: 78px;
        }
        .cal-header {
            background: #1e293b; padding: 16px 18px;
            display: flex; align-items: center; justify-content: space-between;
        }
        .cal-title { color: white; font-size: 15px; font-weight: 700; }
        .cal-nav-btn {
            background: rgba(255,255,255,0.1); border: none; color: white;
            width: 30px; height: 30px; border-radius: 8px; cursor: pointer;
            font-size: 14px; transition: 0.2s; display: flex; align-items: center; justify-content: center;
        }
        .cal-nav-btn:hover { background: rgba(255,255,255,0.2); }

        .cal-dow {
            display: grid; grid-template-columns: repeat(7, 1fr);
            background: #f8fafc; padding: 8px 10px 4px;
        }
        .cal-dow span {
            text-align: center; font-size: 10px; font-weight: 700;
            color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px;
        }

        .cal-grid {
            display: grid; grid-template-columns: repeat(7, 1fr);
            gap: 2px; padding: 6px 10px 14px;
        }
        .cal-day {
            aspect-ratio: 1; border-radius: 8px; display: flex; flex-direction: column;
            align-items: center; justify-content: center; cursor: pointer;
            font-size: 13px; font-weight: 500; color: #475569; position: relative;
            transition: 0.15s; padding: 2px; gap: 2px;
        }
        .cal-day:hover { background: #f1f5f9; }
        .cal-day.other-month { color: #cbd5e1; }
        .cal-day.today { background: #eff6ff; color: #2563eb; font-weight: 700; }
        .cal-day.today::before {
            content: ''; position: absolute; inset: 0; border-radius: 8px;
            border: 2px solid #2563eb;
        }
        .cal-day.selected { background: #2563eb !important; color: white !important; }
        .cal-day.selected .dot { background: white !important; }
        .cal-day.has-booking { font-weight: 700; }

        .dots { display: flex; gap: 2px; justify-content: center; flex-wrap: wrap; max-width: 28px; }
        .dot {
            width: 5px; height: 5px; border-radius: 50%;
        }
        .dot-pending      { background: #f59e0b; }
        .dot-approved     { background: #22c55e; }
        .dot-downpayment  { background: #3b82f6; }
        .dot-fully_paid   { background: #8b5cf6; }
        .dot-completed    { background: #94a3b8; }
        .dot-in_progress  { background: #f97316; }
        .dot-arrived      { background: #06b6d4; }

        /* ── LEGEND ── */
        .cal-legend {
            padding: 10px 14px 14px;
            display: flex; flex-wrap: wrap; gap: 8px; border-top: 1px solid #f1f5f9;
        }
        .legend-item { display: flex; align-items: center; gap: 5px; font-size: 10px; color: #64748b; font-weight: 600; }

        /* ── RIGHT PANEL ── */
        .right-panel { display: flex; flex-direction: column; gap: 16px; }

        /* ── SELECTED DATE PANEL ── */
        .date-detail {
            background: white; border-radius: 14px;
            box-shadow: 0 1px 6px rgba(0,0,0,0.08); overflow: hidden;
        }
        .date-detail-header {
            padding: 14px 20px; background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            display: flex; align-items: center; gap: 10px;
        }
        .date-detail-header .date-str { font-size: 15px; font-weight: 700; color: #1e293b; }
        .date-detail-header .booking-count {
            background: #2563eb; color: white; font-size: 11px; font-weight: 700;
            padding: 2px 9px; border-radius: 99px;
        }
        #dateDetailContent { padding: 16px 20px; }

        .no-date-selected {
            padding: 40px 20px; text-align: center; color: #94a3b8;
        }
        .no-date-selected i { font-size: 36px; margin-bottom: 10px; display: block; }

        /* ── BOOKING CARD (compact) ── */
        .bcard {
            border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; margin-bottom: 12px;
        }
        .bcard:last-child { margin-bottom: 0; }
        .bcard-head {
            padding: 12px 16px; display: flex; justify-content: space-between; align-items: center;
            border-bottom: 1px solid #f1f5f9;
        }
        .bcard-ref { font-size: 13px; font-weight: 700; color: #1e293b; }
        .bcard-body { padding: 14px 16px; }

        .info-row { display: flex; gap: 20px; flex-wrap: wrap; margin-bottom: 12px; }
        .info-cell .lbl { font-size: 10px; text-transform: uppercase; font-weight: 700; color: #94a3b8; margin-bottom: 2px; }
        .info-cell .val { font-size: 13px; font-weight: 600; color: #1e293b; }
        .info-cell .sub { font-size: 11px; color: #64748b; }

        /* COMPACT BOOKING LINES (replaces boxed info-row for rental cards) */
        .bcard-line { font-size: 13px; color: #334155; margin-bottom: 6px; display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
        .bcard-line:last-of-type { margin-bottom: 0; }
        .bcard-line i { color: #94a3b8; width: 14px; text-align: center; font-size: 11px; flex-shrink: 0; }
        .bcard-line strong { font-weight: 700; color: #1e293b; }
        .bcard-line .bcard-sub { color: #64748b; font-size: 12px; }
        .bcard-line .arrow { color: #cbd5e1; font-size: 10px; }

        .balance-line {
            display: flex; align-items: baseline; gap: 8px; flex-wrap: wrap;
            background: #f8fafc; border-radius: 8px; padding: 10px 12px; margin: 12px 0;
        }
        .balance-line .balance-lbl { font-size: 10px; text-transform: uppercase; font-weight: 700; color: #94a3b8; }
        .balance-line .balance-val { font-size: 15px; font-weight: 800; }
        .balance-line .balance-sub { font-size: 11px; color: #94a3b8; margin-left: auto; }

        .manifest-link-sm {
            display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 700;
            color: #1e40af; text-decoration: none; margin-bottom: 12px;
        }
        .manifest-link-sm i { font-size: 11px; }

        .link-row { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; margin-bottom: 12px; }
        .navigate-link-sm {
            display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 700;
            color: #0e7490; text-decoration: none;
        }
        .navigate-link-sm i { font-size: 11px; }

        /* PAYMENT ROW */
        .pay-row {
            display: flex; gap: 0; background: #f8fafc; border-radius: 8px;
            overflow: hidden; margin-bottom: 14px;
        }
        .pay-cell {
            flex: 1; padding: 10px 12px; border-right: 1px solid #e2e8f0; text-align: center;
        }
        .pay-cell:last-child { border-right: none; }
        .pay-cell .pl { font-size: 9px; text-transform: uppercase; font-weight: 700; color: #94a3b8; margin-bottom: 3px; }
        .pay-cell .pv { font-size: 14px; font-weight: 800; }
        .c-blue   { color: #2563eb; }
        .c-green  { color: #16a34a; }
        .c-red    { color: #dc2626; }
        .c-purple { color: #7c3aed; }

        /* PROGRESS STEPS */
        .steps-row { display: flex; margin-bottom: 12px; }
        .step-item { flex: 1; text-align: center; position: relative; }
        .step-item::after {
            content: ''; position: absolute; top: 12px; left: 50%;
            width: 100%; height: 2px; background: #e2e8f0; z-index: 0;
        }
        .step-item:last-child::after { display: none; }
        .step-circle {
            width: 24px; height: 24px; border-radius: 50%; background: #e2e8f0;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 5px; position: relative; z-index: 1; font-size: 10px;
        }
        .step-circle.s-done   { background: #22c55e; color: white; }
        .step-circle.s-active { background: #2563eb; color: white; box-shadow: 0 0 0 3px #bfdbfe; }
        .step-txt { font-size: 9px; text-transform: uppercase; font-weight: 700; color: #94a3b8; }
        .step-txt.s-done   { color: #16a34a; }
        .step-txt.s-active { color: #2563eb; }

        /* ACTION BTNS */
        .trip-btn {
            width: 100%; padding: 11px; border: none; border-radius: 9px;
            font-size: 13px; font-weight: 700; cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 7px; transition: 0.2s;
        }
        .btn-arrived    { background: #fef3c7; color: #92400e; }
        .btn-arrived:hover    { background: #fde68a; }
        .btn-inprogress { background: #dbeafe; color: #1e40af; }
        .btn-inprogress:hover { background: #bfdbfe; }
        .btn-complete   { background: #dcfce7; color: #166534; }
        .btn-complete:hover   { background: #bbf7d0; }
        .btn-done-grey  { background: #f1f5f9; color: #94a3b8; cursor: default; }
        .btn-locked     { background: #fee2e2; color: #991b1b; cursor: not-allowed; }
        .btn-collect    { background: linear-gradient(135deg, #f59e0b, #d97706); color: white; }
        .btn-collect:hover { background: linear-gradient(135deg, #d97706, #b45309); box-shadow: 0 4px 12px rgba(217,119,6,0.4); }

        /* STATUS PILLS */
        .pill {
            padding: 3px 10px; border-radius: 99px; font-size: 10px; font-weight: 700; text-transform: uppercase;
        }
        .pill-pending          { background: #fef3c7; color: #92400e; }
        .pill-approved         { background: #dcfce7; color: #166534; }
        .pill-downpayment_paid { background: #dbeafe; color: #1e40af; }
        .pill-fully_paid       { background: #ede9fe; color: #5b21b6; }
        .pill-completed        { background: #f1f5f9; color: #64748b; }
        .pill-not_started      { background: #f1f5f9; color: #64748b; }
        .pill-arrived          { background: #cffafe; color: #0e7490; }
        .pill-in_progress      { background: #ffedd5; color: #9a3412; }

        /* ALL BOOKINGS SECTION */
        .section-hdr { display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; margin-top: 4px; }
        .section-hdr h2 { font-size: 16px; font-weight: 700; }
        .badge-num { background: #2563eb; color: white; font-size: 11px; font-weight: 700; padding: 2px 9px; border-radius: 99px; }

        .empty-state { text-align: center; padding: 50px 20px; color: #94a3b8; }
        .empty-state i { font-size: 40px; margin-bottom: 12px; display: block; }

        /* hidden form */
        #tripUpdateForm { display: none; }
    </style>
</head>
<body>

{{-- Hidden form for trip status updates --}}
<form id="tripUpdateForm" method="POST" action="{{ route('driver.trip_status') }}">
    @csrf
    <input type="hidden" name="booking_id" id="formBookingId">
    <input type="hidden" name="trip_status" id="formTripStatus">
</form>

{{-- Hidden form for cash collection --}}
<form id="collectForm" method="POST" action="{{ route('driver.collect_payment') }}">
    @csrf
    <input type="hidden" name="booking_id" id="collectBookingId">
</form>

<nav class="navbar">
    <div class="brand"><i class="fas fa-van-shuttle"></i> Rem's Transport — Driver Portal</div>
    <div class="nav-right">
        <div class="driver-badge"><span class="dot-green"></span> <strong style="color:#f1f5f9;">{{ $driver->name }}</strong></div>
        <div class="driver-badge" id="locationStatus"><i class="fas fa-location-crosshairs" style="color:#94a3b8;"></i> Location: Off</div>
        <form id="logout-form" action="/logout" method="POST" style="display:none;">@csrf</form>
        <button class="logout-btn" onclick="document.getElementById('logout-form').submit();">
            <i class="fas fa-sign-out-alt"></i> Logout
        </button>
    </div>
</nav>

<div class="main">

    @if(session('success'))
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
    @endif

    {{-- PROFILE CARD --}}
    <div class="profile-card">
        <div>
            <div class="profile-name"><i class="fas fa-id-badge" style="margin-right:8px;"></i>{{ $driver->name }}</div>
            <div class="profile-meta">
                <span><i class="fas fa-phone"></i> {{ $driver->phone }}</span>
                <span><i class="fas fa-id-card"></i> {{ $driver->license_number }}</span>
                <span><span class="dot-green"></span> {{ strtoupper($driver->status) }}</span>
            </div>
        </div>
        <div class="stat-box">
            <div class="stat-num">{{ $bookings->whereNotIn('status', ['completed', 'cancelled', 'rejected'])->count() + $joinerTrips->where('status', 'active')->count() + $tourPackages->count() }}</div>
            <div class="stat-lbl">Active Trips</div>
        </div>
    </div>

    {{-- NEXT UP CARD --}}
    @if($nextTrip)
        @php
            $nuManifestHref = match($nextTrip['type']) {
                'joiner' => route('driver.joiner.manifest', str_replace('j', '', $nextTrip['id'])),
                'tour'   => route('driver.tour.manifest', $nextTrip['tour_package_id']),
                default  => route('driver.rental.manifest', $nextTrip['id']),
            };
            $nuInMotion = in_array($nextTrip['trip_status'] ?? '', ['arrived', 'in_progress']);
        @endphp
        <div class="next-up-card">
            <div>
                <div class="next-up-label">
                    <i class="fas fa-bolt"></i> {{ $nuInMotion ? 'Trip In Progress' : 'Next Up' }}
                </div>
                <div class="next-up-dest">{{ $nextTrip['destination'] }}</div>
                <div class="next-up-meta">
                    <span><i class="fas fa-calendar"></i> {{ date('M d', strtotime($nextTrip['start_date'])) }}</span>
                    <span><i class="fas fa-user"></i> {{ $nextTrip['customer'] }}</span>
                    <span><i class="fas fa-map-pin"></i> {{ $nextTrip['pickup'] }}</span>
                </div>
            </div>
            <div class="next-up-actions">
                <a href="https://www.google.com/maps/dir/?api=1&destination={{ urlencode($nextTrip['pickup']) }}" target="_blank" rel="noopener" class="next-up-btn-navigate">
                    <i class="fas fa-diamond-turn-right"></i> Navigate
                </a>
                <a href="{{ $nuManifestHref }}" class="next-up-btn-manifest">
                    <i class="fas fa-list-check"></i> Open Manifest
                </a>
            </div>
        </div>
    @endif

    {{-- DASHBOARD GRID --}}
    <div class="dashboard-grid">

        {{-- ── CALENDAR ── --}}
        <div class="calendar-panel">
            <div class="cal-header">
                <button class="cal-nav-btn" onclick="changeMonth(-1)"><i class="fas fa-chevron-left"></i></button>
                <span class="cal-title" id="calTitle"></span>
                <button class="cal-nav-btn" onclick="changeMonth(1)"><i class="fas fa-chevron-right"></i></button>
            </div>
            <div class="cal-dow">
                @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $d)
                    <span>{{ $d }}</span>
                @endforeach
            </div>
            <div class="cal-grid" id="calGrid"></div>
            <div class="cal-legend">
                <div class="legend-item"><span class="dot dot-pending"></span> Pending</div>
                <div class="legend-item"><span class="dot dot-approved"></span> Approved</div>
                <div class="legend-item"><span class="dot dot-arrived"></span> Arrived</div>
                <div class="legend-item"><span class="dot dot-in_progress"></span> In Progress</div>
                <div class="legend-item"><span class="dot dot-fully_paid"></span> Fully Paid</div>
                <div class="legend-item"><span class="dot dot-completed"></span> Completed</div>
            </div>
        </div>

        {{-- ── RIGHT PANEL ── --}}
        <div class="right-panel">

            {{-- Selected Date Bookings --}}
            <div class="date-detail">
                <div class="date-detail-header">
                    <i class="fas fa-calendar-day" style="color:#2563eb;"></i>
                    <span class="date-str" id="detailDateStr">Select a date</span>
                    <span class="booking-count" id="detailCount" style="display:none;"></span>
                </div>
                <div id="dateDetailContent">
                    <div class="no-date-selected">
                        <i class="fas fa-hand-pointer"></i>
                        <div style="font-weight:600; margin-bottom:4px;">Click any date on the calendar</div>
                        <div style="font-size:13px;">Booking details will appear here</div>
                    </div>
                </div>
            </div>

            {{-- Tour Packages Section --}}
            @if($tourPackages->count() > 0)
            <div style="margin-bottom:20px;">
                <div class="section-hdr" onclick="toggleSection('tours')" style="cursor:pointer; user-select:none;">
                    <div style="display:flex; align-items:center; gap:8px;">
                        <h2><i class="fas fa-suitcase" style="margin-right:8px; color:#0891b2;"></i>My Tour Packages</h2>
                        <span class="badge-num" style="background:#0891b2;">{{ $tourPackages->count() }}</span>
                    </div>
                    <i class="fas fa-chevron-up" id="tours-icon" style="color:#94a3b8; font-size:13px; transition:0.2s;"></i>
                </div>
                <div id="tours-body">

                @foreach($tourPackages as $tp)
                <div class="bcard" style="border-left:4px solid #0891b2;">
                    <div class="bcard-head">
                        <span class="bcard-ref" style="color:#0891b2;">{{ strtoupper($tp->name) }}</span>
                        <span class="pill" style="background:#cffafe;color:#0e7490;font-size:11px;padding:3px 10px;border-radius:50px;font-weight:700;">
                            TOUR PACKAGE
                        </span>
                    </div>
                    <div class="bcard-body">
                        <div class="info-row">
                            <div class="info-cell">
                                <div class="lbl">Destination</div>
                                <div class="val">{{ $tp->destination }}</div>
                                <div class="sub"><i class="fas fa-map-pin" style="font-size:10px;margin-right:3px;color:#0891b2;"></i>Meet: {{ $tp->pickup_point }}</div>
                            </div>
                            <div class="info-cell">
                                <div class="lbl">Dates</div>
                                <div class="val">{{ date('M d', strtotime($tp->tour_date)) }} – {{ $tp->end_date ? date('M d', strtotime($tp->end_date)) : 'TBD' }}</div>
                                <div class="sub"><i class="fas fa-clock" style="font-size:10px;margin-right:3px;"></i>{{ date('g:i A', strtotime($tp->pickup_time)) }}</div>
                            </div>
                            <div class="info-cell">
                                <div class="lbl">Van</div>
                                <div class="val">{{ $tp->van }}</div>
                                <div class="sub">{{ $tp->plate_number }}</div>
                            </div>
                        </div>
                        <div class="pay-row" style="margin-top:10px;">
                            <div class="pay-cell">
                                <div class="pl">Booked</div>
                                <div class="pv c-blue">{{ $tp->passenger_count }} pax</div>
                            </div>
                            <div class="pay-cell">
                                <div class="pl">Price / Seat</div>
                                <div class="pv">₱{{ number_format($tp->price) }}</div>
                            </div>
                            <div class="pay-cell">
                                <div class="pl">Revenue</div>
                                <div class="pv c-green">₱{{ number_format($tp->total_revenue ?? 0, 2) }}</div>
                            </div>
                        </div>
                        <div style="display:flex;gap:8px;margin-top:12px;">
                            <a href="https://www.google.com/maps/dir/?api=1&destination={{ urlencode($tp->pickup_point) }}" target="_blank" rel="noopener"
                               style="display:flex;align-items:center;justify-content:center;gap:6px;padding:11px 14px;background:#ecfeff;color:#0e7490;border-radius:8px;font-size:13px;font-weight:700;text-decoration:none;white-space:nowrap;">
                                <i class="fas fa-diamond-turn-right"></i> Navigate
                            </a>
                            <a href="{{ route('driver.tour.manifest', $tp->id) }}"
                               style="flex:1;display:flex;align-items:center;justify-content:center;padding:11px;background:#0891b2;color:white;border-radius:8px;text-align:center;font-size:13px;font-weight:700;text-decoration:none;">
                                <i class="fas fa-list-check" style="margin-right:6px;"></i>
                                Open Manifest & Manage Trip
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
                </div>{{-- end tours-body --}}
            </div>
            @endif

            {{-- Joiner Trips Section --}}
            @if($joinerTrips->count() > 0)
            <div style="margin-bottom:20px;">
                <div class="section-hdr" onclick="toggleSection('joiners')" style="cursor:pointer; user-select:none;">
                    <div style="display:flex; align-items:center; gap:8px;">
                        <h2><i class="fas fa-users" style="margin-right:8px; color:#7c3aed;"></i>My Joiner Trips</h2>
                        <span class="badge-num" style="background:#7c3aed;">{{ $joinerTrips->count() }}</span>
                    </div>
                    <i class="fas fa-chevron-up" id="joiners-icon" style="color:#94a3b8; font-size:13px; transition:0.2s;"></i>
                </div>
                <div id="joiners-body">

                @foreach($joinerTrips as $jt)
                <div class="bcard" style="border-left:4px solid #7c3aed;">
                    <div class="bcard-head">
                        <span class="bcard-ref" style="color:#7c3aed;">JOINER-{{ str_pad($jt->id, 4, '0', STR_PAD_LEFT) }}</span>
                        <span class="pill" style="background:#ede9fe;color:#7c3aed;font-size:11px;padding:3px 10px;border-radius:50px;font-weight:700;text-transform:uppercase;">
                            {{ $jt->status }}
                        </span>
                    </div>
                    <div class="bcard-body">
                        <div class="info-row">
                            <div class="info-cell">
                                <div class="lbl">Destination</div>
                                <div class="val">{{ $jt->destination }}</div>
                                <div class="sub"><i class="fas fa-map-pin" style="font-size:10px;margin-right:3px;color:#7c3aed;"></i>Meet: {{ $jt->meetup_point }}</div>
                            </div>
                            <div class="info-cell">
                                <div class="lbl">Dates</div>
                                <div class="val">{{ date('M d', strtotime($jt->trip_date)) }} – {{ $jt->end_date ? date('M d', strtotime($jt->end_date)) : 'TBD' }}</div>
                            </div>
                            <div class="info-cell">
                                <div class="lbl">Van</div>
                                <div class="val">{{ $jt->van }}</div>
                                <div class="sub">{{ $jt->plate_number }}</div>
                            </div>
                        </div>
                        <div class="pay-row" style="margin-top:10px;">
                            <div class="pay-cell">
                                <div class="pl">Passengers</div>
                                <div class="pv c-blue">{{ $jt->passenger_count }} / {{ $jt->total_seats }}</div>
                            </div>
                            <div class="pay-cell">
                                <div class="pl">Price / Seat</div>
                                <div class="pv">₱{{ number_format($jt->price_per_seat) }}</div>
                            </div>
                            <div class="pay-cell">
                                <div class="pl">Revenue</div>
                                <div class="pv c-green">₱{{ number_format($jt->total_revenue ?? 0, 2) }}</div>
                            </div>
                        </div>
                        <div style="display:flex;gap:8px;margin-top:12px;">
                            <a href="https://www.google.com/maps/dir/?api=1&destination={{ urlencode($jt->meetup_point) }}" target="_blank" rel="noopener"
                               style="display:flex;align-items:center;justify-content:center;gap:6px;padding:11px 14px;background:#f5f3ff;color:#6d28d9;border-radius:8px;font-size:13px;font-weight:700;text-decoration:none;white-space:nowrap;">
                                <i class="fas fa-diamond-turn-right"></i> Navigate
                            </a>
                            <a href="{{ route('driver.joiner.manifest', $jt->id) }}"
                               style="flex:1;display:flex;align-items:center;justify-content:center;padding:11px;background:#7c3aed;color:white;border-radius:8px;text-align:center;font-size:13px;font-weight:700;text-decoration:none;">
                                <i class="fas fa-users" style="margin-right:6px;"></i>
                                {{ $jt->status === 'pending' ? 'View Trip (Pending Approval)' : 'Open Manifest & Collect Payments' }}
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
                </div>{{-- end joiners-body --}}
            </div>
            @endif

            {{-- All Bookings List --}}
            <div>
                <div class="section-hdr" onclick="toggleSection('rentals')" style="cursor:pointer; user-select:none;">
                    <div style="display:flex; align-items:center; gap:8px;">
                        <h2><i class="fas fa-van-shuttle" style="margin-right:8px; color:#2563eb;"></i>Regular Van Booking</h2>
                        <span class="badge-num" style="background:#2563eb;">{{ $bookings->count() }}</span>
                    </div>
                    <i class="fas fa-chevron-up" id="rentals-icon" style="color:#94a3b8; font-size:13px; transition:0.2s;"></i>
                </div>
                <div id="rentals-body">

                @forelse($bookings as $b)
                    @php
                        $sc  = strtolower($b->status);
                        $ts  = $b->trip_status ?? 'not_started';
                        $bal = $b->remaining_balance ?? ($b->total - ($b->amount_paid ?? 0));
                    @endphp
                    <div class="bcard" style="border-left:4px solid #2563eb;">
                        <div class="bcard-head">
                            <span class="bcard-ref">#REM-{{ str_pad($b->id, 5, '0', STR_PAD_LEFT) }}</span>
                            <div style="display:flex; gap:6px;">
                                <span class="pill pill-{{ $sc }}">{{ ucwords(str_replace('_',' ',$b->status)) }}</span>
                                <span class="pill pill-{{ $ts }}">{{ ucwords(str_replace('_',' ',$ts)) }}</span>
                            </div>
                        </div>
                        <div class="bcard-body">
                            <div class="bcard-line">
                                <i class="fas fa-user"></i>
                                <strong>{{ $b->first_name ?? 'N/A' }} {{ $b->last_name ?? '' }}</strong>
                                @if($b->contact_number)
                                    <span class="bcard-sub">{{ $b->contact_number }}</span>
                                @endif
                            </div>
                            <div class="bcard-line">
                                <i class="fas fa-route"></i>
                                <span class="bcard-sub">{{ $b->pickup }}</span>
                                <i class="fas fa-arrow-right arrow"></i>
                                {{ $b->destination }}
                            </div>
                            <div class="bcard-line">
                                <i class="fas fa-calendar"></i>
                                {{ date('M d', strtotime($b->start_date)) }} – {{ date('M d, Y', strtotime($b->end_date)) }}
                            </div>

                            <div class="balance-line">
                                <span class="balance-lbl">Balance</span>
                                <span class="balance-val {{ $bal > 0 ? 'c-red' : 'c-green' }}">
                                    {{ $bal <= 0 ? '✓ Paid' : '₱'.number_format($bal,2) }}
                                </span>
                                <span class="balance-sub">Total ₱{{ number_format($b->total, 2) }} · Paid ₱{{ number_format($b->amount_paid ?? 0, 2) }}</span>
                            </div>

                            <div class="link-row">
                                <a href="{{ route('driver.rental.manifest', $b->id) }}" class="manifest-link-sm" style="margin-bottom:0;">
                                    <i class="fas fa-list-check"></i> View Passenger Manifest
                                </a>
                                <a href="https://www.google.com/maps/dir/?api=1&destination={{ urlencode($b->pickup) }}" target="_blank" rel="noopener" class="navigate-link-sm">
                                    <i class="fas fa-diamond-turn-right"></i> Navigate
                                </a>
                            </div>

                            @if(!in_array($sc, ['pending','rejected','cancelled']))
                                @if($sc === 'completed' || $ts === 'completed')
                                    <button class="trip-btn btn-done-grey" disabled><i class="fas fa-check-double"></i> Trip Completed</button>
                                @elseif($ts === 'not_started')
                                    @if(date('Y-m-d') === date('Y-m-d', strtotime($b->start_date)))
                                        <button class="trip-btn btn-arrived"
                                            onclick="submitTripStatus({{ $b->id }}, 'arrived', 'Confirm: You have arrived at the pickup point?')">
                                            <i class="fas fa-map-marker-alt"></i> Arrived at Pickup Point
                                        </button>
                                    @else
                                        <button class="trip-btn btn-locked" disabled
                                            title="Only available on the booking date: {{ date('M d, Y', strtotime($b->start_date)) }}">
                                            <i class="fas fa-lock"></i> Available on {{ date('M d, Y', strtotime($b->start_date)) }}
                                        </button>
                                    @endif
                                @elseif($ts === 'arrived')
                                    <button class="trip-btn btn-inprogress"
                                        onclick="submitTripStatus({{ $b->id }}, 'in_progress', 'Confirm: All passengers are on board and trip is starting?')">
                                        <i class="fas fa-route"></i> Passengers Boarded — Start Trip
                                    </button>
                                @elseif($ts === 'in_progress')
                                    @if($bal <= 0)
                                        <button class="trip-btn btn-complete"
                                            onclick="submitTripStatus({{ $b->id }}, 'completed', 'Confirm: Trip is done and full payment has been collected?')">
                                            <i class="fas fa-flag-checkered"></i> Complete Trip
                                        </button>
                                    @else
                                        <button class="trip-btn btn-collect"
                                            onclick="collectPayment({{ $b->id }}, '{{ number_format($bal,2) }}')">
                                            <i class="fas fa-hand-holding-dollar"></i> Collect ₱{{ number_format($bal,2) }} from Customer
                                        </button>
                                    @endif
                                @endif
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <i class="fas fa-calendar-xmark"></i>
                        <div style="font-weight:600; margin-bottom:4px;">No bookings assigned yet</div>
                        <div style="font-size:13px;">Trips assigned to you will appear here.</div>
                    </div>
                @endforelse
                </div>{{-- end rentals-body --}}
            </div>

        </div>{{-- end right panel --}}
    </div>{{-- end dashboard grid --}}

</div>

<script>
// ── BOOKINGS DATA FROM PHP ──
const bookings = @json($bookingsForJs);

// ── CALENDAR STATE ──
let currentYear, currentMonth, selectedDate = null;

function init() {
    const now = new Date();
    currentYear  = now.getFullYear();
    currentMonth = now.getMonth();
    renderCalendar();
}

function changeMonth(dir) {
    currentMonth += dir;
    if (currentMonth > 11) { currentMonth = 0; currentYear++; }
    if (currentMonth < 0)  { currentMonth = 11; currentYear--; }
    renderCalendar();
}

function dateStr(y, m, d) {
    return y + '-' + String(m+1).padStart(2,'0') + '-' + String(d).padStart(2,'0');
}

// Returns bookings active on a given date string
function bookingsOnDate(ds) {
    return bookings.filter(b => b.start_date <= ds && b.end_date >= ds);
}

function dotClass(b) {
    if (b.trip_status === 'in_progress') return 'dot-in_progress';
    if (b.trip_status === 'arrived')     return 'dot-arrived';
    if (b.trip_status === 'completed')   return 'dot-completed';
    if (b.status === 'fully_paid')       return 'dot-fully_paid';
    if (b.status === 'downpayment_paid') return 'dot-downpayment';
    if (b.status === 'approved')         return 'dot-approved';
    return 'dot-pending';
}

function renderCalendar() {
    const months = ['January','February','March','April','May','June',
                    'July','August','September','October','November','December'];
    document.getElementById('calTitle').textContent = months[currentMonth] + ' ' + currentYear;

    const grid   = document.getElementById('calGrid');
    const today  = new Date(); today.setHours(0,0,0,0);
    const firstDay = new Date(currentYear, currentMonth, 1).getDay();
    const daysInMonth = new Date(currentYear, currentMonth+1, 0).getDate();
    const prevDays    = new Date(currentYear, currentMonth, 0).getDate();

    let html = '';

    // Prev month tail
    for (let i = firstDay - 1; i >= 0; i--) {
        const d = prevDays - i;
        const m = currentMonth - 1;
        const y = m < 0 ? currentYear - 1 : currentYear;
        const mm = ((m % 12) + 12) % 12;
        const ds = dateStr(y, mm, d);
        const bList = bookingsOnDate(ds);
        html += dayCell(d, ds, true, false, bList);
    }

    // Current month
    for (let d = 1; d <= daysInMonth; d++) {
        const ds   = dateStr(currentYear, currentMonth, d);
        const cell = new Date(currentYear, currentMonth, d);
        const isToday = cell.getTime() === today.getTime();
        const bList = bookingsOnDate(ds);
        html += dayCell(d, ds, false, isToday, bList);
    }

    // Next month head
    const total = firstDay + daysInMonth;
    const remaining = total % 7 === 0 ? 0 : 7 - (total % 7);
    for (let d = 1; d <= remaining; d++) {
        const m = currentMonth + 1;
        const y = m > 11 ? currentYear + 1 : currentYear;
        const mm = m % 12;
        const ds = dateStr(y, mm, d);
        const bList = bookingsOnDate(ds);
        html += dayCell(d, ds, true, false, bList);
    }

    grid.innerHTML = html;

    // Re-highlight selected
    if (selectedDate) {
        const el = document.querySelector(`[data-date="${selectedDate}"]`);
        if (el) el.classList.add('selected');
    }
}

function dayCell(d, ds, otherMonth, isToday, bList) {
    const classes = ['cal-day',
        otherMonth ? 'other-month' : '',
        isToday    ? 'today'       : '',
        bList.length ? 'has-booking' : '',
        ds === selectedDate ? 'selected' : '',
    ].filter(Boolean).join(' ');

    const dots = bList.slice(0,4).map(b =>
        `<span class="dot ${dotClass(b)}"></span>`
    ).join('');

    return `<div class="${classes}" data-date="${ds}" onclick="selectDate('${ds}')">
                <span>${d}</span>
                ${dots ? `<div class="dots">${dots}</div>` : ''}
            </div>`;
}

function selectDate(ds) {
    selectedDate = ds;
    renderCalendar();
    showDateDetail(ds);
}

function showDateDetail(ds) {
    const bList = bookingsOnDate(ds);
    const dateObj = new Date(ds + 'T00:00:00');
    const opts = { weekday:'long', year:'numeric', month:'long', day:'numeric' };
    document.getElementById('detailDateStr').textContent = dateObj.toLocaleDateString('en-PH', opts);

    const countEl = document.getElementById('detailCount');
    if (bList.length > 0) {
        countEl.style.display = 'inline-block';
        countEl.textContent = bList.length + (bList.length === 1 ? ' booking' : ' bookings');
    } else {
        countEl.style.display = 'none';
    }

    const content = document.getElementById('dateDetailContent');

    if (bList.length === 0) {
        content.innerHTML = `
            <div class="no-date-selected">
                <i class="fas fa-calendar-check" style="color:#cbd5e1;"></i>
                <div style="font-weight:600; margin-bottom:4px;">No bookings on this date</div>
                <div style="font-size:13px;">You're free on ${dateObj.toLocaleDateString('en-PH', {month:'short', day:'numeric'})}.</div>
            </div>`;
        return;
    }

    content.innerHTML = bList.map(b => buildBookingCard(b)).join('');
}

function buildBookingCard(b) {
    const statusPill  = `<span class="pill pill-${b.status.toLowerCase()}">${b.status.replace('_',' ')}</span>`;
    const tripPill    = `<span class="pill pill-${b.trip_status}">${b.trip_status.replace('_',' ')}</span>`;

    const balColor = b.balance > 0 ? 'c-red' : 'c-green';
    const balText  = b.balance <= 0 ? '✓ Paid' : '₱' + b.balance.toLocaleString('en-PH', {minimumFractionDigits:2});

    let actionBtn = '';
    // Tour trip status is tracked per tour package (via its own manifest page), not per
    // individual customer booking, so the calendar card just links there instead of
    // showing arrived/in-progress/complete controls that don't apply at this level.
    const isActive = b.type !== 'tour' && !['pending','rejected','cancelled'].includes(b.status.toLowerCase());

    if (isActive) {
        const todayStr = new Date().toLocaleDateString('en-CA'); // YYYY-MM-DD in local time
        if (b.status === 'completed' || b.trip_status === 'completed') {
            actionBtn = `<button class="trip-btn btn-done-grey" disabled><i class="fas fa-check-double"></i> Trip Completed</button>`;
        } else if (b.trip_status === 'not_started') {
            if (b.start_date === todayStr) {
                actionBtn = `<button class="trip-btn btn-arrived" onclick="submitTripStatus(${b.id}, 'arrived', 'Confirm: You have arrived at the pickup point?')">
                    <i class="fas fa-map-marker-alt"></i> Arrived at Pickup Point</button>`;
            } else {
                actionBtn = `<button class="trip-btn btn-locked" disabled title="Only available on the booking date: ${formatDate(b.start_date)}">
                    <i class="fas fa-lock"></i> Available on ${formatDate(b.start_date)}</button>`;
            }
        } else if (b.trip_status === 'arrived') {
            actionBtn = `<button class="trip-btn btn-inprogress" onclick="submitTripStatus(${b.id}, 'in_progress', 'Confirm: All passengers are on board and trip is starting?')">
                <i class="fas fa-route"></i> Passengers Boarded — Start Trip</button>`;
        } else if (b.trip_status === 'in_progress') {
            if (b.balance <= 0) {
                actionBtn = `<button class="trip-btn btn-complete" onclick="submitTripStatus(${b.id}, 'completed', 'Confirm: Trip is done and full payment has been collected?')">
                    <i class="fas fa-flag-checkered"></i> Complete Trip</button>`;
            } else {
                const balFmt = Number(b.balance).toLocaleString('en-PH', {minimumFractionDigits:2});
                actionBtn = `<button class="trip-btn btn-collect" onclick="collectPayment(${b.id}, '${balFmt}')">
                    <i class="fas fa-hand-holding-dollar"></i> Collect ₱${balFmt} from Customer</button>`;
            }
        }
    }

    const cardColor = b.type === 'joiner' ? '#7c3aed' : b.type === 'tour' ? '#0891b2' : '#2563eb';
    const manifestHref = b.type === 'rental' ? `/driver/bookings/${b.id}/manifest`
        : b.type === 'joiner' ? `/driver/joiner-trips/${String(b.id).replace('j','')}`
        : `/driver/tour-packages/${b.tour_package_id}`;

    return `
    <div class="bcard" style="border-left:4px solid ${cardColor};">
        <div class="bcard-head">
            <span class="bcard-ref" style="color:${cardColor};">${b.ref}</span>
            <div style="display:flex;gap:5px;">${statusPill}${tripPill}</div>
        </div>
        <div class="bcard-body">
            <div class="bcard-line">
                <i class="fas fa-user"></i>
                <strong>${b.customer}</strong>
                ${b.contact ? `<span class="bcard-sub">${b.contact}</span>` : ''}
            </div>
            <div class="bcard-line">
                <i class="fas fa-route"></i>
                <span class="bcard-sub">${b.pickup}</span>
                <i class="fas fa-arrow-right arrow"></i>
                ${b.destination}
            </div>
            <div class="bcard-line">
                <i class="fas fa-calendar"></i>
                ${formatDate(b.start_date)} – ${formatDate(b.end_date)}
                <span class="bcard-sub">· ${b.van}${b.plate ? ' ('+b.plate+')' : ''}</span>
            </div>

            <div class="balance-line">
                <span class="balance-lbl">Balance</span>
                <span class="pv ${balColor}" style="font-size:15px;font-weight:800;">${balText}</span>
                <span class="balance-sub">Total ₱${Number(b.total).toLocaleString('en-PH',{minimumFractionDigits:2})} · Paid ₱${Number(b.amount_paid).toLocaleString('en-PH',{minimumFractionDigits:2})}</span>
            </div>

            <div class="link-row">
                <a href="${manifestHref}" class="manifest-link-sm" style="margin-bottom:0;">
                    <i class="fas fa-list-check"></i> View Passenger Manifest
                </a>
                <a href="https://www.google.com/maps/dir/?api=1&destination=${encodeURIComponent(b.pickup)}" target="_blank" rel="noopener" class="navigate-link-sm">
                    <i class="fas fa-diamond-turn-right"></i> Navigate
                </a>
            </div>

            ${isActive ? actionBtn : ''}
        </div>
    </div>`;
}

function formatDate(ds) {
    const d = new Date(ds + 'T00:00:00');
    return d.toLocaleDateString('en-PH', {month:'short', day:'numeric', year:'numeric'});
}

// ── SUBMIT TRIP STATUS ──
function submitTripStatus(bookingId, status, confirmMsg) {
    if (!confirm(confirmMsg)) return;
    document.getElementById('formBookingId').value  = bookingId;
    document.getElementById('formTripStatus').value = status;
    document.getElementById('tripUpdateForm').submit();
}

// ── COLLECT CASH PAYMENT ──
function collectPayment(bookingId, balFmt) {
    if (!confirm(`Confirm: You have collected ₱${balFmt} cash from the customer?`)) return;
    document.getElementById('collectBookingId').value = bookingId;
    document.getElementById('collectForm').submit();
}

init();

// ── LIVE LOCATION SHARING ──
// Shares this driver's position with the admin dashboard while this tab stays open.
// Sends at most once every 15 seconds, even if the browser reports position more often.
const LOCATION_SEND_INTERVAL_MS = 15000;
let lastLocationSendAt = 0;

function setLocationStatus(text, color) {
    const el = document.getElementById('locationStatus');
    el.innerHTML = `<i class="fas fa-location-crosshairs" style="color:${color};"></i> Location: ${text}`;
}

function sendLocation(position) {
    const now = Date.now();
    if (now - lastLocationSendAt < LOCATION_SEND_INTERVAL_MS) return;
    lastLocationSendAt = now;

    fetch("{{ route('driver.update_location') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                || document.querySelector('input[name="_token"]').value,
        },
        body: JSON.stringify({
            lat: position.coords.latitude,
            lng: position.coords.longitude,
        }),
    }).then(r => {
        if (r.ok) {
            setLocationStatus('Sharing', '#4ade80');
        } else {
            setLocationStatus('Error', '#ef4444');
        }
    }).catch(() => setLocationStatus('Error', '#ef4444'));
}

if ('geolocation' in navigator) {
    navigator.geolocation.watchPosition(
        sendLocation,
        (err) => setLocationStatus('Denied', '#ef4444'),
        { enableHighAccuracy: false, maximumAge: 10000, timeout: 20000 }
    );
} else {
    setLocationStatus('Unsupported', '#ef4444');
}

// ── SECTION COLLAPSE TOGGLE ──
const sectionState = {};

// Start every section collapsed so the page opens on the Next Up card +
// calendar, not a long scroll of cards — the driver expands what they need.
function collapseSectionsByDefault() {
    ['tours', 'joiners', 'rentals'].forEach(key => {
        const body = document.getElementById(key + '-body');
        const icon = document.getElementById(key + '-icon');
        if (!body) return;
        body.style.maxHeight = '0';
        body.style.opacity   = '0';
        body.style.overflow  = 'hidden';
        if (icon) icon.style.transform = 'rotate(180deg)';
        sectionState[key] = false;
    });
}
collapseSectionsByDefault();

function toggleSection(key) {
    const body = document.getElementById(key + '-body');
    const icon = document.getElementById(key + '-icon');
    if (!body) return;

    const isOpen = sectionState[key] !== false; // default open
    if (isOpen) {
        body.style.maxHeight = body.scrollHeight + 'px'; // set explicit before animating
        requestAnimationFrame(() => {
            body.style.transition = 'max-height 0.3s ease, opacity 0.3s ease';
            body.style.maxHeight  = '0';
            body.style.opacity    = '0';
            body.style.overflow   = 'hidden';
        });
        icon.style.transform = 'rotate(180deg)';
        sectionState[key] = false;
    } else {
        body.style.maxHeight = body.scrollHeight + 'px';
        body.style.opacity   = '1';
        setTimeout(() => { body.style.maxHeight = 'none'; }, 310);
        icon.style.transform = 'rotate(0deg)';
        sectionState[key] = true;
    }
}
</script>
<script src="/js/pwa.js"></script>
</body>
</html>
