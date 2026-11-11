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
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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
        <a href="/admin/book-for-customer">New Booking</a>
        <a href="/admin/joiner-trips">Joiner Trips</a>
        <a href="/admin/pricing">Pricing</a>
        <a href="/admin/reports">Reports</a>
        <a href="/admin/live-map">Live Map</a>
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

    <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
        <button class="hamburger" onclick="toggleSidebar()" aria-label="Menu"><span></span><span></span><span></span></button>
        <h1 style="margin:0;">Dashboard</h1>
        @include('admin.partials.notification-bell')
    </div>

    <style>
        .dash-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 16px; }
        .dash-grid.todo { grid-template-columns: repeat(4, 1fr); margin-bottom: 24px; }
        .dash-card { background: #fff; border-radius: 12px; padding: 18px 20px; box-shadow: 0 8px 20px rgba(0,0,0,.05); text-decoration: none; color: inherit; display: block; border: 1px solid transparent; transition: border-color .15s; }
        a.dash-card:hover { border-color: #cbd5e1; }
        .dash-card h4 { margin: 0 0 6px; font-size: 13px; color: #6b7280; font-weight: 600; display: flex; align-items: center; gap: 6px; }
        .dash-card .num { font-size: 24px; font-weight: 800; color: #0f172a; line-height: 1.2; }
        .dash-card .sub { font-size: 12px; color: #64748b; margin-top: 4px; }
        .dash-card.is-alert { border-color: #fde68a; background: #fffbeb; }
        .dash-card.is-alert .num { color: #b45309; }
        .dash-card.is-danger { border-color: #fecaca; background: #fff5f5; }
        .dash-card.is-danger .num { color: #b91c1c; }
        .dash-card.is-good .num { color: #15803d; }
        .month-card { display: grid; grid-template-columns: 240px 1fr; gap: 24px; align-items: end; margin-bottom: 24px; }
        .month-card .num { font-size: 28px; }
        .trend-up { color: #15803d; font-weight: 700; }
        .trend-down { color: #b91c1c; font-weight: 700; }
        .bars { display: grid; grid-template-columns: repeat(6, 1fr); gap: 12px; align-items: end; height: 140px; }
        .bar-col { display: flex; flex-direction: column; align-items: center; justify-content: flex-end; height: 100%; gap: 6px; }
        .bar { width: 100%; max-width: 56px; background: #bfdbfe; border-radius: 6px 6px 0 0; min-height: 3px; }
        .bar.current { background: #2563eb; }
        .bar-val { font-size: 11px; color: #475569; font-weight: 600; white-space: nowrap; }
        .bar-label { font-size: 12px; color: #64748b; font-weight: 600; }
        @media (max-width: 1100px) { .dash-grid { grid-template-columns: repeat(3, 1fr); } .dash-grid.todo { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 700px) { .dash-grid, .dash-grid.todo { grid-template-columns: 1fr 1fr; } .month-card { grid-template-columns: 1fr; } }
    </style>
    @php
        $peso = fn ($v) => '₱' . number_format((float) $v, 2);
        $thisMonth = end($monthly)['amount'];
        $lastMonth = $monthly[count($monthly) - 2]['amount'] ?? 0;
        $change = $lastMonth > 0 ? round(($thisMonth - $lastMonth) / $lastMonth * 100) : null;
        $maxBar = max(1, max(array_column($monthly, 'amount')));
    @endphp

    <!-- STATS -->
    <div class="dash-grid">
        <div class="dash-card">
            <h4><i class="fas fa-ticket"></i> Total Bookings</h4>
            <div class="num">{{ $totalBookings }}</div>
            <div class="sub">Van, tour and joiner</div>
        </div>
        <div class="dash-card is-good">
            <h4><i class="fas fa-wallet"></i> Payments Collected</h4>
            <div class="num">{{ $peso($collected) }}</div>
            <div class="sub">All payments received, less refunds</div>
        </div>
        <a href="/admin/bookings?filter=pending" class="dash-card {{ $needsApproval > 0 ? 'is-alert' : '' }}">
            <h4><i class="fas fa-hourglass-half"></i> Needs Approval</h4>
            <div class="num">{{ $needsApproval }}</div>
            <div class="sub">Bookings waiting for you</div>
        </a>
        <div class="dash-card">
            <h4><i class="fas fa-flag-checkered"></i> Completed</h4>
            <div class="num">{{ $completed }}</div>
            <div class="sub">Finished trips</div>
        </div>
    </div>

    <!-- TO DO -->
    <div class="dash-grid todo">
        <a href="/admin/bookings?filter=refund" class="dash-card {{ $todo['refund_count'] > 0 ? 'is-danger' : '' }}">
            <h4><i class="fas fa-money-bill-transfer"></i> Refunds to Send</h4>
            <div class="num">{{ $todo['refund_count'] }}</div>
            <div class="sub">{{ $todo['refund_count'] > 0 ? $peso($todo['refund_amount']) . ' owed to customers' : 'Nothing to refund' }}</div>
        </a>
        <div class="dash-card {{ $todo['balance_count'] > 0 ? 'is-alert' : '' }}">
            <h4><i class="fas fa-coins"></i> Balances to Collect</h4>
            <div class="num">{{ $peso($todo['balance_amount']) }}</div>
            <div class="sub">{{ $todo['balance_count'] }} {{ \Illuminate\Support\Str::plural('trip', $todo['balance_count']) }} in the next 7 days</div>
        </div>
        <a href="/admin/live-map" class="dash-card {{ $todo['drivers_live'] > 0 ? 'is-good' : '' }}">
            <h4><i class="fas fa-location-dot"></i> Drivers on the Road</h4>
            <div class="num">{{ $todo['drivers_live'] }} <span style="font-size:14px;color:#64748b;font-weight:600;">/ {{ $todo['drivers_total'] }}</span></div>
            <div class="sub">🟢 Live{{ $todo['drivers_idle'] ? ' · 🟡 ' . $todo['drivers_idle'] . ' idle' : '' }} &bull; Open Live Map</div>
        </a>
        <a href="/admin/vans" class="dash-card" title="{{ $todo['vans_out']->isNotEmpty() ? 'Unavailable: ' . $todo['vans_out']->implode(', ') : 'All vans are available' }}">
            <h4><i class="fas fa-van-shuttle"></i> Vans Available</h4>
            <div class="num">{{ $todo['vans_available'] }} <span style="font-size:14px;color:#64748b;font-weight:600;">/ {{ $todo['vans_total'] }}</span></div>
            <div class="sub">{{ $todo['vans_out']->isNotEmpty() ? $todo['vans_out']->count() . ' unavailable or in maintenance' : 'All vans are available' }}</div>
        </a>
    </div>

    <!-- THIS MONTH -->
    <div class="dash-card month-card">
        <div>
            <h4><i class="fas fa-chart-column"></i> Collected This Month</h4>
            <div class="num">{{ $peso($thisMonth) }}</div>
            <div class="sub">
                @if($change === null)
                    No payments last month to compare
                @elseif($change >= 0)
                    <span class="trend-up"><i class="fas fa-arrow-trend-up"></i> {{ $change }}%</span> vs last month ({{ $peso($lastMonth) }})
                @else
                    <span class="trend-down"><i class="fas fa-arrow-trend-down"></i> {{ abs($change) }}%</span> vs last month ({{ $peso($lastMonth) }})
                @endif
            </div>
        </div>
        <div class="bars" aria-label="Payments collected per month">
            @foreach($monthly as $m)
                <div class="bar-col" title="{{ $m['label'] }}: {{ $peso($m['amount']) }}">
                    <div class="bar-val">₱{{ $m['amount'] >= 1000 ? number_format($m['amount'] / 1000, 1) . 'k' : number_format($m['amount']) }}</div>
                    <div class="bar {{ $loop->last ? 'current' : '' }}" style="height: {{ max(2, round(max(0, $m['amount']) / $maxBar * 100)) }}%;"></div>
                    <div class="bar-label">{{ $m['label'] }}</div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- BOOKING CALENDAR -->
    <style>
        .cal-card { margin-bottom: 24px; }
        .cal-head { display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; margin-bottom: 14px; }
        .cal-head h2 { margin: 0; }
        .cal-nav { display: flex; align-items: center; gap: 8px; }
        .cal-nav button { border: 1px solid #e2e8f0; background: #f8fafc; color: #1e293b; border-radius: 8px; padding: 7px 12px; font-size: 13px; font-weight: 600; cursor: pointer; }
        .cal-nav button:hover { background: #eef2f7; }
        .cal-title { min-width: 150px; text-align: center; font-weight: 800; color: #0f172a; font-size: 16px; }
        .cal-legend { display: flex; gap: 14px; flex-wrap: wrap; font-size: 12px; color: #64748b; margin-bottom: 12px; }
        .cal-legend span { display: inline-flex; align-items: center; gap: 6px; }
        .cal-legend i.sw { width: 10px; height: 10px; border-radius: 3px; display: inline-block; }
        .cal-body { display: grid; grid-template-columns: 1fr 320px; gap: 18px; align-items: start; }
        .cal-grid { display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; }
        .cal-dow { background: #f8fafc; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; text-align: center; padding: 8px 0; border-bottom: 1px solid #e2e8f0; }
        .cal-day { min-height: 96px; padding: 6px; border: 0; border-right: 1px solid #f1f5f9; border-bottom: 1px solid #f1f5f9; border-radius: 0; margin: 0; cursor: pointer; background: #fff; text-align: left; font: inherit; display: flex; flex-direction: column; gap: 3px; min-width: 0; }
        .cal-day:nth-child(7n) { border-right: none; }
        .cal-day:hover { background: #f8fafc; }
        .cal-day.blank { background: #fafbfc; cursor: default; }
        .cal-day.selected { box-shadow: inset 0 0 0 2px #2563eb; }
        .cal-num { font-size: 12px; font-weight: 700; color: #475569; width: 22px; height: 22px; line-height: 22px; text-align: center; border-radius: 50%; }
        .cal-day.today .cal-num { background: #2563eb; color: #fff; }
        .cal-day.past .cal-num { color: #94a3b8; }
        .cal-chip { font-size: 11px; font-weight: 600; padding: 2px 6px; border-radius: 5px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .cal-chip.van { background: #dcfce7; color: #166534; }
        .cal-chip.tour { background: #ede9fe; color: #6d28d9; }
        .cal-chip.joiner { background: #e0f2fe; color: #0369a1; }
        .cal-chip.completed { opacity: .5; }
        .cal-chip.approval { box-shadow: inset 3px 0 0 #f59e0b; }
        .cal-more { font-size: 11px; color: #64748b; font-weight: 600; padding-left: 4px; }
        .cal-panel { border: 1px solid #e2e8f0; border-radius: 12px; padding: 14px 16px; }
        .cal-panel h3 { margin: 0 0 2px; font-size: 15px; color: #0f172a; }
        .cal-panel .cal-count { font-size: 12px; color: #64748b; margin-bottom: 10px; }
        .cal-trip { display: block; padding: 10px 0; border-top: 1px solid #f1f5f9; text-decoration: none; color: inherit; }
        .cal-trip:hover .cal-trip-title { color: #2563eb; }
        .cal-trip-title { font-weight: 700; font-size: 13px; color: #0f172a; display: flex; gap: 6px; align-items: center; flex-wrap: wrap; }
        .cal-trip-route { font-size: 12px; color: #475569; margin-top: 2px; }
        .cal-trip-meta { font-size: 11px; color: #64748b; margin-top: 2px; }
        .cal-state { font-size: 10px; font-weight: 700; padding: 2px 7px; border-radius: 99px; }
        .cal-state.approval { background: #fef3c7; color: #b45309; }
        .cal-state.confirmed { background: #dbeafe; color: #1d4ed8; }
        .cal-state.ongoing { background: #ffedd5; color: #c2410c; }
        .cal-state.completed { background: #f1f5f9; color: #64748b; }
        .cal-empty { color: #94a3b8; font-size: 13px; padding: 12px 0; border-top: 1px solid #f1f5f9; }
        @media (max-width: 1100px) { .cal-body { grid-template-columns: 1fr; } }
        @media (max-width: 700px) {
            .cal-day { min-height: 54px; padding: 4px; align-items: center; }
            .cal-chip { font-size: 0; padding: 0; width: 8px; height: 8px; border-radius: 50%; display: inline-block; }
            .cal-chip.van { background: #16a34a; } .cal-chip.tour { background: #7c3aed; } .cal-chip.joiner { background: #0284c7; }
            .cal-chips { display: flex; gap: 3px; flex-wrap: wrap; justify-content: center; }
            .cal-more { font-size: 10px; padding: 0; }
        }
    </style>
    <div class="card cal-card">
        <div class="cal-head">
            <h2><i class="fas fa-calendar-days" style="color:#2563eb;"></i> Booking Calendar</h2>
            <div class="cal-nav">
                <button type="button" onclick="calMove(-1)" aria-label="Previous month"><i class="fas fa-chevron-left"></i></button>
                <div class="cal-title" id="calTitle">&nbsp;</div>
                <button type="button" onclick="calMove(1)" aria-label="Next month"><i class="fas fa-chevron-right"></i></button>
                <button type="button" onclick="calToday()">Today</button>
            </div>
        </div>
        <div class="cal-legend">
            <span><i class="sw" style="background:#16a34a;"></i> Van Rental</span>
            <span><i class="sw" style="background:#7c3aed;"></i> Tour Package</span>
            <span><i class="sw" style="background:#0284c7;"></i> Joiner Trip</span>
            <span><i class="sw" style="background:#f59e0b;"></i> Needs approval</span>
            <span><i class="sw" style="background:#cbd5e1;"></i> Completed (faded)</span>
        </div>
        <div class="cal-body">
            <div class="cal-grid" id="calGrid" aria-live="polite"></div>
            <div class="cal-panel" id="calPanel"></div>
        </div>
    </div>

    <!-- UPCOMING TRIPS -->
    <style>
        .upcoming-card { margin-bottom: 24px; }
        .upcoming-head { display: flex; justify-content: space-between; align-items: baseline; gap: 12px; margin-bottom: 14px; flex-wrap: wrap; }
        .upcoming-head h2 { margin: 0; }
        .upcoming-head small { color: #64748b; }
        .up-list { display: grid; gap: 10px; }
        .up-item { display: grid; grid-template-columns: 110px 1fr auto; gap: 16px; align-items: center; padding: 12px 16px; border: 1px solid #e2e8f0; border-radius: 12px; text-decoration: none; color: inherit; transition: background .15s, border-color .15s; }
        .up-item:hover { background: #f8fafc; border-color: #cbd5e1; }
        .up-item.today { border-left: 4px solid #2563eb; }
        .up-date { text-align: center; }
        .up-date .day { font-size: 22px; font-weight: 800; color: #0f172a; line-height: 1; }
        .up-date .mon { font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; }
        .up-when { display: inline-block; margin-top: 4px; font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 99px; background: #f1f5f9; color: #475569; }
        .up-when.today { background: #2563eb; color: #fff; }
        .up-when.tomorrow { background: #dbeafe; color: #1d4ed8; }
        .up-main { min-width: 0; }
        .up-title { font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
        .up-kind { font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 99px; }
        .up-kind.van { background: #dcfce7; color: #166534; }
        .up-kind.tour { background: #ede9fe; color: #6d28d9; }
        .up-kind.joiner { background: #e0f2fe; color: #0369a1; }
        .up-route { font-size: 13px; color: #475569; margin-top: 3px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .up-meta { font-size: 12px; color: #64748b; margin-top: 3px; }
        .up-flags { display: flex; flex-direction: column; align-items: flex-end; gap: 4px; }
        .up-flag { font-size: 11px; font-weight: 700; padding: 3px 9px; border-radius: 99px; white-space: nowrap; }
        .up-flag.approve { background: #fef3c7; color: #b45309; }
        .up-flag.balance { background: #fee2e2; color: #b91c1c; }
        .up-flag.ongoing { background: #ffedd5; color: #c2410c; }
        .up-flag.ok { background: #dcfce7; color: #15803d; }
        .up-empty { text-align: center; color: #94a3b8; padding: 24px; }
        @media (max-width: 700px) {
            .up-item { grid-template-columns: 70px 1fr; }
            .up-flags { grid-column: 1 / -1; flex-direction: row; flex-wrap: wrap; align-items: center; }
        }
    </style>
    @php
        $todayPh = \Illuminate\Support\Carbon::parse(\App\Support\LocalDate::today());
        $kindClass = ['Van Rental' => 'van', 'Tour Package' => 'tour', 'Joiner Trip' => 'joiner'];
    @endphp
    <div class="card upcoming-card">
        <div class="upcoming-head">
            <h2><i class="fas fa-calendar-check" style="color:#2563eb;"></i> Upcoming Trips</h2>
            <small>Next 14 days &bull; {{ $upcoming->count() }} {{ \Illuminate\Support\Str::plural('trip', $upcoming->count()) }}</small>
        </div>

        @if($upcoming->isEmpty())
            <div class="up-empty">No trips scheduled in the next 14 days.</div>
        @else
            <div class="up-list">
                @foreach($upcoming as $t)
                    @php
                        $date = \Illuminate\Support\Carbon::parse($t->date);
                        $daysAway = (int) $todayPh->diffInDays($date, false);
                        $when = $daysAway === 0 ? 'Today' : ($daysAway === 1 ? 'Tomorrow' : "In {$daysAway} days");
                    @endphp
                    <a href="{{ $t->link }}" class="up-item {{ $daysAway === 0 ? 'today' : '' }}">
                        <div class="up-date">
                            <div class="mon">{{ $date->format('M') }}</div>
                            <div class="day">{{ $date->format('d') }}</div>
                            <span class="up-when {{ $daysAway === 0 ? 'today' : ($daysAway === 1 ? 'tomorrow' : '') }}">{{ $when }}</span>
                        </div>
                        <div class="up-main">
                            <div class="up-title">
                                {{ $t->ref }} &middot; {{ $t->who }}
                                <span class="up-kind {{ $kindClass[$t->kind] }}">{{ $t->kind }}</span>
                            </div>
                            <div class="up-route" title="{{ $t->route }}">{{ $t->route ?: '—' }}</div>
                            <div class="up-meta">
                                <i class="fas fa-van-shuttle"></i> {{ $t->van ?: 'No van yet' }}
                                &nbsp;&bull;&nbsp; <i class="fas fa-user"></i> {{ $t->driver ?: 'No driver yet' }}
                                @if($t->pax) &nbsp;&bull;&nbsp; {{ $t->pax }} @endif
                                @if($t->end_date && $t->end_date > $t->date) &nbsp;&bull;&nbsp; until {{ \Illuminate\Support\Carbon::parse($t->end_date)->format('M d') }} @endif
                            </div>
                        </div>
                        <div class="up-flags">
                            @if(in_array($t->trip_status, ['arrived', 'in_progress'], true))
                                <span class="up-flag ongoing"><i class="fas fa-route"></i> Ongoing</span>
                            @endif
                            @if($t->needs_approval)
                                <span class="up-flag approve"><i class="fas fa-hourglass-half"></i> Needs approval</span>
                            @endif
                            @if($t->balance > 0)
                                <span class="up-flag balance">Balance ₱{{ number_format($t->balance, 2) }}</span>
                            @elseif(!$t->needs_approval && $t->kind !== 'Joiner Trip')
                                <span class="up-flag ok"><i class="fas fa-check"></i> Fully paid</span>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
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
// ---- BOOKING CALENDAR ----
const CAL_TODAY = @json(\App\Support\LocalDate::today()); // Philippine date, YYYY-MM-DD
const CAL_KIND = { 'Van Rental': 'van', 'Tour Package': 'tour', 'Joiner Trip': 'joiner' };
const CAL_STATE = { approval: 'Needs approval', confirmed: 'Confirmed', ongoing: 'Ongoing', completed: 'Completed' };
let calMonth = CAL_TODAY.slice(0, 7);
let calSelected = CAL_TODAY;
let calTrips = [];

const calEsc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
const calPad = n => String(n).padStart(2, '0');
const calOn = (t, day) => t.date <= day && (t.end_date || t.date) >= day;

function calShift(month, by) {
    let [y, m] = month.split('-').map(Number);
    m += by;
    if (m < 1) { m = 12; y--; }
    if (m > 12) { m = 1; y++; }
    return y + '-' + calPad(m);
}

async function calLoad() {
    const month = calMonth;
    const [y, m] = month.split('-').map(Number);
    document.getElementById('calTitle').textContent = new Date(y, m - 1, 1).toLocaleDateString('en-PH', { month: 'long', year: 'numeric' });
    try {
        const res = await fetch('{{ route('admin.calendar') }}?month=' + month, { headers: { 'Accept': 'application/json' } });
        if (!res.ok) throw new Error(res.status);
        const data = await res.json();
        if (month !== calMonth) return; // a newer month was requested meanwhile
        calTrips = data.trips;
    } catch (e) {
        calTrips = [];
    }
    calRender();
}

function calRender() {
    const [y, m] = calMonth.split('-').map(Number);
    const first = new Date(y, m - 1, 1).getDay();      // 0 = Sunday
    const days = new Date(y, m, 0).getDate();
    let html = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'].map(d => `<div class="cal-dow">${d}</div>`).join('');

    for (let i = 0; i < first; i++) html += '<div class="cal-day blank"></div>';
    for (let d = 1; d <= days; d++) {
        const day = calMonth + '-' + calPad(d);
        const trips = calTrips.filter(t => calOn(t, day));
        const cls = ['cal-day', day === CAL_TODAY ? 'today' : '', day < CAL_TODAY ? 'past' : '', day === calSelected ? 'selected' : ''].join(' ');
        // Joiner trips have no single customer, so show where they're going
        const chipName = t => t.kind === 'Joiner Trip' ? (t.route || '').split('→').pop().trim() : t.who;
        const chips = trips.slice(0, 3).map(t =>
            `<div class="cal-chip ${CAL_KIND[t.kind]} ${t.state}" title="${calEsc(t.ref + ' · ' + chipName(t))}">${calEsc(t.ref)} ${calEsc(chipName(t))}</div>`).join('');
        const more = trips.length > 3 ? `<div class="cal-more">+${trips.length - 3} more</div>` : '';
        const label = d + (trips.length ? `, ${trips.length} trip${trips.length > 1 ? 's' : ''}` : '');
        html += `<button type="button" class="${cls}" onclick="calPick('${day}')" aria-label="${label}"><span class="cal-num">${d}</span><div class="cal-chips">${chips}</div>${more}</button>`;
    }
    const cells = first + days;
    for (let i = cells; i < Math.ceil(cells / 7) * 7; i++) html += '<div class="cal-day blank"></div>';

    document.getElementById('calGrid').innerHTML = html;
    calPanel();
}

function calPanel() {
    const panel = document.getElementById('calPanel');
    if (!calSelected.startsWith(calMonth)) {
        const n = calTrips.length;
        panel.innerHTML = `<h3>${document.getElementById('calTitle').textContent}</h3><div class="cal-count">${n} trip${n === 1 ? '' : 's'} this month</div><div class="cal-empty">Pick a day to see its trips.</div>`;
        return;
    }
    const [y, m, d] = calSelected.split('-').map(Number);
    const trips = calTrips.filter(t => calOn(t, calSelected));
    const title = new Date(y, m - 1, d).toLocaleDateString('en-PH', { weekday: 'long', month: 'long', day: 'numeric' });
    const list = trips.length ? trips.map(t => {
        const until = t.end_date && t.end_date > t.date
            ? ` · ${new Date(t.date + 'T00:00').toLocaleDateString('en-PH', { month: 'short', day: 'numeric' })}–${new Date(t.end_date + 'T00:00').toLocaleDateString('en-PH', { month: 'short', day: 'numeric' })}` : '';
        const balance = t.balance > 0 ? ` · <span style="color:#b91c1c;font-weight:700;">Balance ₱${Number(t.balance).toLocaleString('en-PH', { minimumFractionDigits: 2 })}</span>` : '';
        return `<a class="cal-trip" href="${calEsc(t.link)}">
            <div class="cal-trip-title">${calEsc(t.ref)} · ${calEsc(t.who)}
                <span class="up-kind ${CAL_KIND[t.kind]}">${calEsc(t.kind)}</span>
                <span class="cal-state ${t.state}">${CAL_STATE[t.state]}</span></div>
            <div class="cal-trip-route">${calEsc(t.route || '—')}</div>
            <div class="cal-trip-meta"><i class="fas fa-van-shuttle"></i> ${calEsc(t.van || 'No van yet')} · <i class="fas fa-user"></i> ${calEsc(t.driver || 'No driver yet')}${until}${balance}</div>
        </a>`;
    }).join('') : '<div class="cal-empty">No trips on this day.</div>';
    panel.innerHTML = `<h3>${title}</h3><div class="cal-count">${trips.length} trip${trips.length === 1 ? '' : 's'}</div>${list}`;
}

function calPick(day) { calSelected = day; calRender(); }
function calMove(by) { calMonth = calShift(calMonth, by); calLoad(); }
function calToday() { calMonth = CAL_TODAY.slice(0, 7); calSelected = CAL_TODAY; calLoad(); }
calLoad();

function toggleSidebar() {
    document.getElementById('adminSidebar').classList.toggle('open');
    document.getElementById('sidebarOverlay').classList.toggle('open');
}
</script>
<script src="/js/pwa.js"></script>
</body>
</html>
