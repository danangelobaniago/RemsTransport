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
        .cal-card { margin-bottom: 24px; padding: 22px 24px; }
        .cal-top { display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; }
        .cal-top h2 { margin: 0; display: flex; align-items: center; gap: 10px; }
        .cal-top h2 .ico { width: 38px; height: 38px; border-radius: 11px; background: #eff6ff; color: #2563eb; display: inline-flex; align-items: center; justify-content: center; font-size: 17px; }
        .cal-top-right { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
        .cal-seg { display: inline-flex; background: #f1f5f9; border-radius: 10px; padding: 3px; }
        .cal-seg button { border: 0; background: transparent; padding: 7px 14px; border-radius: 8px; font: inherit; font-size: 13px; font-weight: 600; color: #64748b; cursor: pointer; display: inline-flex; gap: 6px; align-items: center; }
        .cal-seg button[aria-pressed="true"] { background: #fff; color: #0f172a; box-shadow: 0 1px 3px rgba(15,23,42,.12); }
        .cal-new { background: #2563eb; color: #fff; text-decoration: none; padding: 9px 14px; border-radius: 10px; font-size: 13px; font-weight: 700; display: inline-flex; gap: 6px; align-items: center; }
        .cal-new:hover { background: #1d4ed8; }

        .cal-bar-row { display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; margin: 18px 0 14px; }
        .cal-nav { display: flex; align-items: center; gap: 6px; }
        .cal-arrow { width: 34px; height: 34px; border-radius: 50%; border: 1px solid #e2e8f0; background: #fff; color: #334155; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; }
        .cal-arrow:hover { background: #f1f5f9; }
        .cal-title { min-width: 175px; text-align: center; font-size: 19px; font-weight: 800; color: #0f172a; }
        .cal-title small { font-weight: 600; color: #94a3b8; margin-left: 4px; }
        .cal-today-btn { border: 1px solid #e2e8f0; background: #fff; border-radius: 99px; padding: 7px 14px; font: inherit; font-size: 12px; font-weight: 700; color: #2563eb; cursor: pointer; margin-left: 4px; }
        .cal-today-btn:hover { background: #eff6ff; }
        .cal-filters { display: flex; gap: 6px; flex-wrap: wrap; align-items: center; }
        .cal-filter { display: inline-flex; align-items: center; gap: 7px; border: 1px solid #e2e8f0; background: #fff; border-radius: 99px; padding: 5px 6px 5px 11px; font: inherit; font-size: 12px; font-weight: 600; color: #334155; cursor: pointer; }
        .cal-filter .dot { width: 8px; height: 8px; border-radius: 50%; }
        .cal-filter .n { background: #f1f5f9; color: #0f172a; border-radius: 99px; padding: 1px 8px; font-weight: 800; }
        .cal-filter[aria-pressed="false"] { opacity: .45; }
        .cal-filter[aria-pressed="false"] .label { text-decoration: line-through; }
        .cal-note { font-size: 12px; font-weight: 700; padding: 6px 11px; border-radius: 99px; background: #fef3c7; color: #b45309; }
        .cal-note.red { background: #fee2e2; color: #b91c1c; }

        .cal-body { display: grid; grid-template-columns: minmax(0, 1fr) 330px; gap: 20px; align-items: start; }
        .cal-body.wide { grid-template-columns: minmax(0, 1fr); }

        /* MONTH */
        .cal-grid { background: #e9eef5; border: 1px solid #e9eef5; border-radius: 16px; overflow: hidden; display: grid; gap: 1px; }
        .cal-dows, .cal-week { display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); gap: 0 1px; }
        .cal-dow { background: #fff; font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .6px; padding: 10px 10px 8px; }
        .cal-week { grid-template-rows: 34px repeat(3, 26px) minmax(16px, 1fr); min-height: 128px; }
        .cal-day { grid-row: 1 / -1; border: 0; border-radius: 0; margin: 0; padding: 6px 8px; background: #fff; font: inherit; text-align: left; cursor: pointer; display: flex; flex-direction: column; justify-content: space-between; min-width: 0; transition: background .12s; }
        .cal-day.weekend { background: #fcfdfe; }
        .cal-day.past { background: #fafbfc; }
        .cal-day.past .cal-num { color: #b6bfcc; }
        .cal-day.other { background: #f8fafc; cursor: default; }
        .cal-day.other .cal-num { color: #d5dbe4; }
        .cal-day:not(.other):hover { background: #f5f8ff; }
        .cal-day.today { background: #f5f9ff; }
        .cal-day.selected { box-shadow: inset 0 0 0 2px #2563eb; }
        .cal-num { font-size: 13px; font-weight: 700; color: #1e293b; width: 26px; height: 26px; line-height: 26px; text-align: center; border-radius: 50%; }
        .cal-day.today .cal-num { background: #2563eb; color: #fff; box-shadow: 0 2px 6px rgba(37,99,235,.35); }
        .cal-more { font-size: 11px; font-weight: 700; color: #2563eb; padding-left: 4px; }

        .cal-ev { z-index: 1; height: 22px; line-height: 22px; margin: 2px 4px; padding: 0 8px; border: 0; border-radius: 99px; font: inherit; font-size: 11px; font-weight: 700; text-align: left; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; cursor: pointer; min-width: 0; display: flex; align-items: center; gap: 5px; }
        .cal-ev .dot { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }
        .cal-ev .txt { overflow: hidden; text-overflow: ellipsis; }
        .cal-ev i { font-size: 10px; }
        .cal-ev.van    { background: #dcfce7; color: #14532d; } .cal-ev.van .dot { background: #16a34a; }
        .cal-ev.tour   { background: #ede9fe; color: #4c1d95; } .cal-ev.tour .dot { background: #7c3aed; }
        .cal-ev.joiner { background: #e0f2fe; color: #0c4a6e; } .cal-ev.joiner .dot { background: #0284c7; }
        .cal-ev:hover { filter: brightness(.95); }
        .cal-ev.cont-left  { margin-left: 0; border-top-left-radius: 0; border-bottom-left-radius: 0; }
        .cal-ev.cont-right { margin-right: 0; border-top-right-radius: 0; border-bottom-right-radius: 0; }
        .cal-ev.completed { opacity: .45; }
        .cal-ev.approval { box-shadow: inset 0 0 0 1.5px #f59e0b; }
        .cal-ev.conflict { box-shadow: inset 0 0 0 2px #ef4444; }

        /* VANS TIMELINE */
        .vt { border: 1px solid #e9eef5; border-radius: 16px; overflow-x: auto; background: #fff; }
        .vt-row { display: grid; grid-template-columns: 180px repeat(var(--days), minmax(64px, 1fr)); min-width: calc(180px + var(--days) * 64px); border-bottom: 1px solid #eef2f7; }
        .vt-row:last-child { border-bottom: none; }
        .vt-row.body { grid-template-rows: repeat(var(--lanes), 30px); padding: 6px 0; min-height: 30px; }
        .vt-name { position: sticky; left: 0; z-index: 3; background: #fff; border-right: 1px solid #e9eef5; padding: 6px 12px; grid-row: 1 / -1; display: flex; flex-direction: column; justify-content: center; min-width: 0; }
        .vt-row.body .vt-name { margin: -6px 0; }
        .vt-name b { font-size: 13px; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .vt-name small { font-size: 11px; color: #94a3b8; display: flex; align-items: center; gap: 5px; white-space: nowrap; overflow: hidden; }
        .vt-name .warn { color: #b91c1c; font-weight: 700; }
        .vt-head .vt-name { font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .6px; }
        .vt-hd { text-align: center; padding: 8px 0 6px; font-size: 13px; font-weight: 700; color: #334155; cursor: pointer; }
        .vt-hd small { display: block; font-size: 10px; font-weight: 600; color: #94a3b8; }
        .vt-hd.today span { background: #2563eb; color: #fff; border-radius: 50%; display: inline-block; width: 24px; height: 24px; line-height: 24px; }
        .vt-hd.selected { box-shadow: inset 0 -3px 0 #2563eb; }
        .vt-hd.past { color: #b6bfcc; }
        .vt-cell { grid-row: 1 / -1; margin: -6px 0; border-left: 1px solid #f3f5f9; cursor: pointer; }
        .vt-cell.weekend { background: #fbfcfe; }
        .vt-cell.today { background: #f5f9ff; }
        .vt-cell.selected { background: #eef4ff; }
        .vt-cell:hover { background: #f5f8ff; }
        .vt .cal-ev { height: 24px; line-height: 24px; margin: 3px 2px; padding: 0 5px; }
        .vt .cal-ev .dot { display: none; }
        .vt-empty { color: #94a3b8; font-size: 13px; padding: 24px; text-align: center; }

        /* SIDE PANEL */
        .cal-panel { border: 1px solid #e9eef5; border-radius: 16px; padding: 16px; background: #fff; }
        .cal-body.wide .cal-panel { display: grid; grid-template-columns: 1fr 1fr; gap: 0 24px; }
        .cal-body.wide .cal-panel > .cal-pdate { grid-column: 1 / -1; }
        .cal-pdate { display: flex; gap: 12px; align-items: center; padding-bottom: 12px; border-bottom: 1px solid #f1f5f9; }
        .cal-pday { width: 52px; height: 56px; border-radius: 14px; background: linear-gradient(160deg, #3b82f6, #1d4ed8); color: #fff; display: flex; flex-direction: column; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 4px 10px rgba(37,99,235,.25); }
        .cal-pday b { font-size: 22px; line-height: 1; }
        .cal-pday small { font-size: 10px; font-weight: 700; text-transform: uppercase; opacity: .85; }
        .cal-pdate h3 { margin: 0; padding: 0; border: 0; font-size: 15px; color: #0f172a; }
        .cal-pdate .cnt { font-size: 12px; color: #64748b; margin-top: 2px; }
        .cal-sub { font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .6px; margin: 14px 0 2px; }
        .cal-trip { display: block; margin-top: 8px; padding: 10px 12px; border: 1px solid #eef2f7; border-radius: 12px; text-decoration: none; color: inherit; transition: background .15s, border-color .15s; position: relative; padding-left: 16px; }
        .cal-trip::before { content: ''; position: absolute; left: 6px; top: 10px; bottom: 10px; width: 4px; border-radius: 4px; background: var(--c); }
        .cal-trip:hover { background: #f8fafc; border-color: #dbe3ee; }
        .cal-trip-title { font-weight: 700; font-size: 13px; color: #0f172a; }
        .cal-trip-tags { display: flex; gap: 5px; flex-wrap: wrap; margin-top: 5px; align-items: center; }
        .cal-trip-when { font-size: 11px; color: #64748b; font-weight: 600; }
        .cal-trip-route { font-size: 12px; color: #475569; margin-top: 5px; }
        .cal-trip-meta { font-size: 11px; color: #64748b; margin-top: 3px; }
        .cal-state { font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 99px; }
        .cal-state.approval { background: #fef3c7; color: #b45309; }
        .cal-state.confirmed { background: #dbeafe; color: #1d4ed8; }
        .cal-state.ongoing { background: #ffedd5; color: #c2410c; }
        .cal-state.completed { background: #f1f5f9; color: #64748b; }
        .cal-free { display: flex; gap: 6px; flex-wrap: wrap; margin-top: 8px; }
        .cal-free span { font-size: 12px; font-weight: 600; background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; border-radius: 99px; padding: 4px 10px; }
        .cal-free span small { color: #4d7c0f; font-weight: 500; }
        .cal-toggle { width: 100%; display: flex; justify-content: space-between; align-items: center; border: 1px solid #e9eef5; background: #f8fafc; border-radius: 10px; padding: 9px 12px; font-family: inherit; cursor: pointer; margin-top: 12px; }
        .cal-toggle:hover { background: #f1f5f9; }
        .cal-toggle b { color: #15803d; margin-left: 4px; }
        .cal-toggle i { transition: transform .15s; }
        .cal-toggle[aria-expanded="true"] i { transform: rotate(180deg); }
        .cal-empty { color: #94a3b8; font-size: 13px; padding: 14px 0 4px; }

        /* HOVER PREVIEW */
        .cal-pop { position: fixed; z-index: 2000; width: 280px; background: #0f172a; color: #e2e8f0; border-radius: 12px; padding: 12px 14px; font-size: 12px; box-shadow: 0 12px 30px rgba(15,23,42,.3); pointer-events: none; display: none; }
        .cal-pop b { color: #fff; font-size: 13px; }
        .cal-pop .row { margin-top: 5px; display: flex; gap: 7px; }
        .cal-pop .row i { width: 12px; color: #94a3b8; margin-top: 2px; }
        .cal-pop .bal { color: #fca5a5; font-weight: 700; }
        .cal-pop .warn { color: #fca5a5; font-weight: 700; }

        @media (max-width: 1100px) { .cal-body { grid-template-columns: minmax(0, 1fr); } .cal-body.wide .cal-panel { grid-template-columns: 1fr; } }
        @media (max-width: 700px) {
            .cal-card { padding: 16px; }
            .cal-title { min-width: 0; font-size: 16px; }
            .cal-week { grid-template-rows: 30px repeat(3, 8px) minmax(14px, 1fr); min-height: 72px; }
            .cal-dow { padding: 8px 0; text-align: center; }
            .cal-day { padding: 4px; align-items: center; }
            .cal-month .cal-ev { height: 6px; font-size: 0; padding: 0; margin: 1px 2px; gap: 0; }
            .cal-month .cal-ev > * { display: none; }
            .cal-month .cal-ev.van { background: #16a34a; } .cal-month .cal-ev.tour { background: #7c3aed; } .cal-month .cal-ev.joiner { background: #0284c7; }
            .cal-more { font-size: 10px; padding: 0; }
            .cal-more-word { display: none; }
            .vt-row { grid-template-columns: 112px repeat(var(--days), 56px); min-width: calc(112px + var(--days) * 56px); }
            .cal-new span { display: none; }
        }
    </style>
    <div class="card cal-card">
        <div class="cal-top">
            <h2><span class="ico"><i class="fas fa-calendar-days"></i></span> Booking Calendar</h2>
            <div class="cal-top-right">
                <div class="cal-seg" role="group" aria-label="Calendar view">
                    <button type="button" id="calViewMonth" onclick="calSetView('month')"><i class="fas fa-calendar"></i> Month</button>
                    <button type="button" id="calViewVans" onclick="calSetView('vans')"><i class="fas fa-van-shuttle"></i> Vans</button>
                </div>
                <a href="/admin/book-for-customer" class="cal-new"><i class="fas fa-plus"></i><span>New Booking</span></a>
            </div>
        </div>
        <div class="cal-bar-row">
            <div class="cal-nav">
                <button type="button" class="cal-arrow" onclick="calMove(-1)" aria-label="Previous month"><i class="fas fa-chevron-left"></i></button>
                <div class="cal-title" id="calTitle">&nbsp;</div>
                <button type="button" class="cal-arrow" onclick="calMove(1)" aria-label="Next month"><i class="fas fa-chevron-right"></i></button>
                <button type="button" class="cal-today-btn" onclick="calToday()">Today</button>
            </div>
            <div class="cal-filters" id="calFilters"></div>
        </div>
        <div class="cal-body" id="calBody">
            <div id="calMain" aria-live="polite"></div>
            <div class="cal-panel" id="calPanel"></div>
        </div>
        <div class="cal-pop" id="calPop" role="tooltip"></div>
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
const CAL_KINDS = {
    'Van Rental':   { cls: 'van',    color: '#16a34a' },
    'Tour Package': { cls: 'tour',   color: '#7c3aed' },
    'Joiner Trip':  { cls: 'joiner', color: '#0284c7' },
};
const CAL_STATE = { approval: 'Needs approval', confirmed: 'Confirmed', ongoing: 'Ongoing', completed: 'Completed' };
const CAL_ICON = { approval: 'fa-hourglass-half', ongoing: 'fa-route', completed: 'fa-check' };
const CAL_LANES = 3; // bars shown per day in Month view before "+N more"
let calView = 'month';
let calFreeOpen = false; // "Free vans on this day" list starts collapsed
try { calFreeOpen = localStorage.getItem('calFreeOpen') === '1'; } catch (e) {}
try { if (localStorage.getItem('calView') === 'vans') calView = 'vans'; } catch (e) {}
let calMonth = CAL_TODAY.slice(0, 7);
let calSelected = CAL_TODAY;
let calTrips = [], calVans = [], calTips = [], calConflicts = new Set();
const calHidden = new Set();

const calEsc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
const calPad = n => String(n).padStart(2, '0');
const calEnd = t => t.end_date && t.end_date > t.date ? t.end_date : t.date;
const calPlate = p => String(p || '').replace(/\s+/g, '').toUpperCase();
const calFmt = (day, opts) => { const [y, m, d] = day.split('-').map(Number); return new Date(y, m - 1, d).toLocaleDateString('en-PH', opts); };
const calRange = (a, b) => b > a ? calFmt(a, { month: 'short', day: 'numeric' }) + ' – ' + calFmt(b, { month: 'short', day: 'numeric' }) : calFmt(a, { weekday: 'short', month: 'short', day: 'numeric' });
const calPeso = v => '₱' + Number(v).toLocaleString('en-PH', { minimumFractionDigits: 2 });
// Joiner trips have no single customer, so show where they're going
const calName = t => t.kind === 'Joiner Trip' ? (t.route || '').split('→').pop().trim() : t.who;
const calVisible = () => calTrips.filter(t => !calHidden.has(t.kind));
const calOn = (t, day) => t.date <= day && calEnd(t) >= day;

function calShift(month, by) {
    let [y, m] = month.split('-').map(Number);
    m += by;
    if (m < 1) { m = 12; y--; }
    if (m > 12) { m = 1; y++; }
    return y + '-' + calPad(m);
}

async function calLoad() {
    const month = calMonth;
    document.getElementById('calTitle').innerHTML = calFmt(month + '-01', { month: 'long' }) + `<small>${month.slice(0, 4)}</small>`;
    try {
        const res = await fetch('{{ route('admin.calendar') }}?month=' + month, { headers: { 'Accept': 'application/json' } });
        if (!res.ok) throw new Error(res.status);
        const data = await res.json();
        if (month !== calMonth) return; // a newer month was requested meanwhile
        calTrips = data.trips;
        calVans = data.vans || [];
    } catch (e) {
        calTrips = [];
    }
    calRender();
}

// Two different bookings on the same van on overlapping days (tour bookings share their van legitimately)
function calFindConflicts() {
    calConflicts = new Set();
    const live = calTrips.filter(t => calPlate(t.plate));
    live.forEach((a, i) => live.slice(i + 1).forEach(b => {
        if (calPlate(a.plate) === calPlate(b.plate) && a.group !== b.group && a.date <= calEnd(b) && b.date <= calEnd(a)) {
            calConflicts.add(a.ref);
            calConflicts.add(b.ref);
        }
    }));
}

function calRender() {
    calFindConflicts();
    document.getElementById('calViewMonth').setAttribute('aria-pressed', calView === 'month');
    document.getElementById('calViewVans').setAttribute('aria-pressed', calView === 'vans');
    document.getElementById('calBody').classList.toggle('wide', calView === 'vans');
    calFilters();
    calDraw();
}

function calDraw() {
    calTips = [];
    const main = document.getElementById('calMain');
    const keep = main.querySelector('.vt')?.scrollLeft; // stay put when picking a day
    main.innerHTML = calView === 'vans' ? calVansHtml() : calMonthHtml();
    const vt = main.querySelector('.vt');
    if (vt) {
        const mark = vt.querySelector('.vt-hd.today');
        // First visit: bring today near the left edge, one day of context before it
        vt.scrollLeft = keep ?? (mark ? mark.getBoundingClientRect().left - vt.getBoundingClientRect().left - vt.querySelector('.vt-name').offsetWidth - mark.offsetWidth : 0);
    }
    calPanel();
}

function calFilters() {
    const visible = calVisible();
    const approvals = visible.filter(t => t.state === 'approval').length;
    let html = Object.entries(CAL_KINDS).map(([kind, k]) =>
        `<button type="button" class="cal-filter" aria-pressed="${!calHidden.has(kind)}" onclick="calToggle('${kind}')">
            <span class="dot" style="background:${k.color}"></span><span class="label">${kind}</span>
            <span class="n">${calTrips.filter(t => t.kind === kind).length}</span></button>`).join('');
    if (approvals) html += `<span class="cal-note"><i class="fas fa-hourglass-half"></i> ${approvals} need${approvals === 1 ? 's' : ''} approval</span>`;
    if (calConflicts.size) html += `<span class="cal-note red"><i class="fas fa-triangle-exclamation"></i> Van double-booked</span>`;
    document.getElementById('calFilters').innerHTML = html;
}

// One bar; trips.length > 1 means bookings of one tour sharing a van
function calEv(trips, style, extra = '', short = false) {
    const t = trips[0];
    const state = trips.some(x => x.state === 'approval') ? 'approval' : trips.some(x => x.state === 'ongoing') ? 'ongoing'
        : trips.every(x => x.state === 'completed') ? 'completed' : 'confirmed';
    const conflict = trips.some(x => calConflicts.has(x.ref));
    const icon = conflict ? '<i class="fas fa-triangle-exclamation" style="color:#dc2626"></i>' : CAL_ICON[state] ? `<i class="fas ${CAL_ICON[state]}"></i>` : '';
    const label = trips.length > 1 ? `${t.route} · ${trips.length} bookings` : short ? t.ref : `${t.ref} ${calName(t)}`;
    calTips.push(trips);
    return `<button type="button" class="cal-ev ${CAL_KINDS[t.kind].cls} ${state} ${conflict ? 'conflict' : ''} ${extra}" style="${style}"
        data-tip="${calTips.length - 1}" aria-label="${calEsc(label)}" onclick="calPick('${t.date < calMonth + '-01' ? calMonth + '-01' : t.date}')">
        <span class="dot"></span>${icon}<span class="txt">${calEsc(label)}</span></button>`;
}

// Bookings of one tour package travel together, so they're drawn as one bar
function calGroups(trips) {
    const groups = {};
    for (const t of trips) (groups[t.group || t.ref] = groups[t.group || t.ref] || []).push(t);
    return Object.values(groups).map(list => ({
        trips: list,
        date: list.reduce((a, t) => t.date < a ? t.date : a, list[0].date),
        end: list.reduce((a, t) => calEnd(t) > a ? calEnd(t) : a, calEnd(list[0])),
    }));
}

function calMonthHtml() {
    const [y, m] = calMonth.split('-').map(Number);
    const first = new Date(y, m - 1, 1).getDay(); // 0 = Sunday
    const days = new Date(y, m, 0).getDate();
    const prevDays = new Date(y, m - 1, 0).getDate();
    const weeks = Math.ceil((first + days) / 7);
    const dayOf = d => calMonth + '-' + calPad(d);
    const trips = calVisible();

    let html = '<div class="cal-grid cal-month"><div class="cal-dows">' + ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'].map(d => `<div class="cal-dow">${d}</div>`).join('') + '</div>';

    for (let w = 0; w < weeks; w++) {
        const ws = dayOf(Math.max(1, w * 7 - first + 1));
        const we = dayOf(Math.min(days, w * 7 - first + 7));
        const colOf = day => (first + Number(day.slice(8)) - 1) % 7;

        // This week's piece of every trip (a tour's bookings as one), longest first so multi-day bars stay on top
        const segs = calGroups(trips).filter(g => g.date <= we && g.end >= ws)
            .map(g => { const s = g.date > ws ? g.date : ws, e = g.end < we ? g.end : we; return { t: g, s, e, c0: colOf(s), c1: colOf(e) }; })
            .sort((a, b) => a.s.localeCompare(b.s) || (b.c1 - b.c0) - (a.c1 - a.c0));

        const lanes = [], hidden = Array(7).fill(0);
        let bars = '';
        for (const g of segs) {
            let lane = 0;
            while (lanes[lane] && lanes[lane].some(c => c >= g.c0 && c <= g.c1)) lane++;
            if (lane >= CAL_LANES) { for (let c = g.c0; c <= g.c1; c++) hidden[c]++; continue; }
            for (let c = g.c0; c <= g.c1; c++) (lanes[lane] = lanes[lane] || []).push(c);
            const ext = (g.t.date < g.s ? 'cont-left ' : '') + (g.t.end > g.e ? 'cont-right' : '');
            bars += calEv(g.t.trips, `grid-column:${g.c0 + 1} / ${g.c1 + 2}; grid-row:${lane + 2};`, ext);
        }

        let cells = '';
        for (let c = 0; c < 7; c++) {
            const d = w * 7 + c - first + 1;
            if (d < 1 || d > days) {
                cells += `<div class="cal-day other" style="grid-column:${c + 1}"><span class="cal-num">${d < 1 ? prevDays + d : d - days}</span></div>`;
                continue;
            }
            const day = dayOf(d);
            const cls = ['cal-day', c === 0 || c === 6 ? 'weekend' : '', day < CAL_TODAY ? 'past' : '', day === CAL_TODAY ? 'today' : '', day === calSelected ? 'selected' : ''].join(' ');
            const n = trips.filter(t => calOn(t, day)).length;
            const more = hidden[c] ? `<span class="cal-more">+${hidden[c]}<span class="cal-more-word"> more</span></span>` : '';
            cells += `<button type="button" class="${cls}" style="grid-column:${c + 1}" onclick="calPick('${day}')"
                aria-label="${calFmt(day, { month: 'long', day: 'numeric' })}${n ? ', ' + n + ' trip' + (n > 1 ? 's' : '') : ''}"><span class="cal-num">${d}</span>${more}</button>`;
        }
        html += `<div class="cal-week">${cells}${bars}</div>`;
    }
    return html + '</div>';
}

function calVansHtml() {
    const [y, m] = calMonth.split('-').map(Number);
    const days = new Date(y, m, 0).getDate();
    const dayOf = d => calMonth + '-' + calPad(d);
    const last = dayOf(days);
    const idx = day => Number(day.slice(8)); // 1-based day of month

    // Group a tour's bookings into one bar per van
    const groups = {};
    for (const t of calVisible()) {
        const key = calPlate(t.plate) + '|' + t.group;
        (groups[key] = groups[key] || []).push(t);
    }
    const rows = calVans.map(v => ({ name: v.name, plate: v.plate, status: v.status, segs: [] }));
    const byPlate = Object.fromEntries(rows.map(r => [calPlate(r.plate), r]));
    const noVan = { name: 'No van assigned', plate: '', status: null, segs: [] };
    for (const trips of Object.values(groups)) {
        const s = trips.reduce((a, t) => t.date < a ? t.date : a, trips[0].date);
        const e = trips.reduce((a, t) => calEnd(t) > a ? calEnd(t) : a, calEnd(trips[0]));
        (byPlate[calPlate(trips[0].plate)] || noVan).segs.push({ trips, s, e });
    }
    if (noVan.segs.length) rows.push(noVan);
    if (!rows.length) return '<div class="vt"><div class="vt-empty">No vans yet.</div></div>';

    let head = '<div class="vt-row vt-head"><div class="vt-name">Van</div>';
    for (let d = 1; d <= days; d++) {
        const day = dayOf(d);
        const cls = ['vt-hd', day === CAL_TODAY ? 'today' : '', day < CAL_TODAY ? 'past' : '', day === calSelected ? 'selected' : ''].join(' ');
        head += `<div class="${cls}" onclick="calPick('${day}')"><small>${calFmt(day, { weekday: 'narrow' })}</small><span>${d}</span></div>`;
    }
    let html = head + '</div>';

    for (const r of rows) {
        const lanes = [];
        let bars = '';
        r.segs.sort((a, b) => a.s.localeCompare(b.s));
        for (const g of r.segs) {
            const s = g.s < calMonth + '-01' ? calMonth + '-01' : g.s, e = g.e > last ? last : g.e;
            const c0 = idx(s), c1 = idx(e);
            let lane = 0;
            while (lanes[lane] && lanes[lane].some(c => c >= c0 && c <= c1)) lane++;
            for (let c = c0; c <= c1; c++) (lanes[lane] = lanes[lane] || []).push(c);
            const ext = (g.s < s ? 'cont-left ' : '') + (g.e > e ? 'cont-right' : '');
            bars += calEv(g.trips, `grid-column:${c0 + 1} / ${c1 + 2}; grid-row:${lane + 1};`, ext, c0 === c1); // one-day bars: booking no. only
        }
        let cells = '';
        for (let d = 1; d <= days; d++) {
            const day = dayOf(d), dow = new Date(y, m - 1, d).getDay();
            const cls = ['vt-cell', dow === 0 || dow === 6 ? 'weekend' : '', day === CAL_TODAY ? 'today' : '', day === calSelected ? 'selected' : ''].join(' ');
            cells += `<div class="${cls}" style="grid-column:${d + 1}" onclick="calPick('${day}')"></div>`;
        }
        const clash = r.plate && r.segs.some(g => g.trips.some(t => calConflicts.has(t.ref)));
        const sub = r.plate
            ? `${calEsc(r.plate)} <span style="color:${r.status === 'available' ? '#16a34a' : '#94a3b8'}">&#9679;</span>${clash ? ' <span class="warn">Double-booked</span>' : ''}`
            : 'Assign a van to these';
        html += `<div class="vt-row body" style="--lanes:${Math.max(1, lanes.length)}">
            <div class="vt-name"><b title="${calEsc(r.name)}">${calEsc(r.name)}</b><small>${sub}</small></div>${cells}${bars}</div>`;
    }
    return `<div class="vt" style="--days:${days}">${html}</div>`;
}

function calTripCard(t) {
    const k = CAL_KINDS[t.kind];
    const balance = t.balance > 0 ? ` &bull; <span style="color:#b91c1c;font-weight:700;">Balance ${calPeso(t.balance)}</span>` : '';
    const clash = calConflicts.has(t.ref) ? '<span class="cal-state" style="background:#fee2e2;color:#b91c1c;"><i class="fas fa-triangle-exclamation"></i> Van double-booked</span>' : '';
    return `<a class="cal-trip" href="${calEsc(t.link)}" style="--c:${k.color}">
        <div class="cal-trip-title">${calEsc(t.ref)} &middot; ${calEsc(t.who)}</div>
        <div class="cal-trip-tags"><span class="up-kind ${k.cls}">${calEsc(t.kind)}</span><span class="cal-state ${t.state}">${CAL_STATE[t.state]}</span>${clash}<span class="cal-trip-when"><i class="far fa-calendar"></i> ${calRange(t.date, calEnd(t))}</span></div>
        <div class="cal-trip-route">${calEsc(t.route || '—')}</div>
        <div class="cal-trip-meta"><i class="fas fa-van-shuttle"></i> ${calEsc(t.van || 'No van yet')} &bull; <i class="fas fa-user"></i> ${calEsc(t.driver || 'No driver yet')}${balance}</div>
    </a>`;
}

function calPanel() {
    const visible = calVisible();
    const inMonth = calSelected.startsWith(calMonth);
    const day = inMonth ? calSelected : calMonth + '-01';
    const onDay = inMonth ? visible.filter(t => calOn(t, day)) : [];
    const next = visible.filter(t => (!inMonth || t.date > day) && !onDay.includes(t)).slice(0, calView === 'vans' ? 3 : 4);

    let head;
    if (inMonth) {
        head = `<div class="cal-pdate"><div class="cal-pday"><small>${calFmt(day, { month: 'short' })}</small><b>${Number(day.slice(8))}</b></div>
            <div><h3>${calFmt(day, { weekday: 'long' })}${day === CAL_TODAY ? ' &bull; Today' : ''}</h3><div class="cnt">${onDay.length ? onDay.length + ' trip' + (onDay.length > 1 ? 's' : '') : 'No trips on this day'}</div></div></div>`;
    } else {
        head = `<div class="cal-pdate"><div><h3>${calFmt(day, { month: 'long', year: 'numeric' })}</h3><div class="cnt">Pick a day to see its trips</div></div></div>`;
    }

    let trips = onDay.map(calTripCard).join('');
    if (next.length) trips += `<div class="cal-sub">Coming up this month</div>` + next.map(calTripCard).join('');
    else if (!onDay.length) trips += `<div class="cal-empty">Nothing else scheduled this month.</div>`;

    // Vans with nothing on this day (any trip type, even hidden by the filters)
    let free = '';
    if (inMonth && day >= CAL_TODAY && calVans.length) {
        const busy = new Set(calTrips.filter(t => calOn(t, day)).map(t => calPlate(t.plate)));
        const open = calVans.filter(v => v.status === 'available' && !busy.has(calPlate(v.plate)));
        free = `<button type="button" class="cal-sub cal-toggle" aria-expanded="${calFreeOpen}" onclick="calToggleFree()">
                <span>Free vans on this day <b>${open.length} of ${calVans.length}</b></span><i class="fas fa-chevron-down"></i></button>`
            + (!calFreeOpen ? '' : open.length
                ? `<div class="cal-free">${open.map(v => `<span>${calEsc(v.name)} <small>${calEsc(v.plate)}</small></span>`).join('')}</div>`
                : '<div class="cal-empty">Every van is booked or unavailable.</div>');
    }

    document.getElementById('calPanel').innerHTML = calView === 'vans'
        ? head + `<div>${trips}</div><div>${free}</div>`
        : head + free + trips;
}

// ---- hover preview (mouse only; taps just pick the day) ----
const calPop = document.getElementById('calPop');
function calShowPop(el) {
    const trips = calTips[el.dataset.tip];
    if (!trips) return;
    const t = trips[0];
    const s = trips.reduce((a, x) => x.date < a ? x.date : a, t.date), e = trips.reduce((a, x) => calEnd(x) > a ? calEnd(x) : a, calEnd(t));
    const bal = trips.reduce((a, x) => a + Number(x.balance || 0), 0);
    let html = trips.length > 1
        ? `<b>${calEsc(t.route)}</b><div class="row"><i class="fas fa-users"></i><span>${trips.map(x => calEsc(x.ref + ' ' + x.who)).join(', ')}</span></div>`
        : `<b>${calEsc(t.ref)} · ${calEsc(t.who)}</b><div class="row"><i class="fas fa-tag"></i><span>${calEsc(t.kind)} · ${CAL_STATE[t.state]}</span></div>
           <div class="row"><i class="fas fa-route"></i><span>${calEsc(t.route || '—')}</span></div>`;
    html += `<div class="row"><i class="far fa-calendar"></i><span>${calRange(s, e)}</span></div>
        <div class="row"><i class="fas fa-van-shuttle"></i><span>${calEsc(t.van || 'No van yet')}</span></div>
        <div class="row"><i class="fas fa-user"></i><span>${calEsc(t.driver || 'No driver yet')}</span></div>`;
    if (bal > 0) html += `<div class="row"><i class="fas fa-coins"></i><span class="bal">Balance ${calPeso(bal)}</span></div>`;
    if (trips.some(x => calConflicts.has(x.ref))) html += `<div class="row"><i class="fas fa-triangle-exclamation"></i><span class="warn">Another booking uses this van on these days</span></div>`;
    calPop.innerHTML = html;
    calPop.style.display = 'block';
    const r = el.getBoundingClientRect(), w = calPop.offsetWidth, h = calPop.offsetHeight;
    calPop.style.left = Math.max(8, Math.min(r.left, window.innerWidth - w - 8)) + 'px';
    calPop.style.top = (r.bottom + h + 12 > window.innerHeight ? r.top - h - 8 : r.bottom + 8) + 'px';
}
const calHidePop = () => { calPop.style.display = 'none'; };
if (window.matchMedia('(hover: hover)').matches) {
    const main = document.getElementById('calMain');
    main.addEventListener('mouseover', e => { const el = e.target.closest('[data-tip]'); el ? calShowPop(el) : calHidePop(); });
    main.addEventListener('mouseleave', calHidePop);
    main.addEventListener('scroll', calHidePop, true);
    window.addEventListener('scroll', calHidePop, { passive: true });
}

function calPick(day) { calSelected = day; calHidePop(); calDraw(); }
function calToggleFree() { calFreeOpen = !calFreeOpen; try { localStorage.setItem('calFreeOpen', calFreeOpen ? '1' : '0'); } catch (e) {} calPanel(); }
function calToggle(kind) { calHidden.has(kind) ? calHidden.delete(kind) : calHidden.add(kind); calRender(); }
function calSetView(view) { calView = view; try { localStorage.setItem('calView', view); } catch (e) {} calRender(); }
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
