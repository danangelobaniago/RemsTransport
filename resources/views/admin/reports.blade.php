<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#111827">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <title>Reports | Admin</title>
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/icons/icon-192.svg">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    <link rel="stylesheet" href="{{ asset('css/responsive.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
    <style>
    .section-card { background: #fff; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); padding: 24px; margin-bottom: 24px; }
    .section-title { font-size: 16px; font-weight: 700; color: #1e293b; margin: 0 0 4px; display: flex; align-items: center; gap: 8px; }
    .section-title i { color: #2563eb; }
    .section-sub { font-size: 12px; color: #94a3b8; margin: 0 0 18px; }

    /* PERIOD BAR */
    .period-bar { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
    .period-tabs { display: flex; gap: 6px; flex-wrap: wrap; }
    .period-tab { padding: 7px 16px; border-radius: 8px; border: 1px solid #e2e8f0; background: #f8fafc; font-size: 13px; font-weight: 600; color: #64748b; text-decoration: none; }
    .period-tab.active { background: #2563eb; color: #fff; border-color: #2563eb; }
    .period-custom { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
    .period-custom input { padding: 6px 10px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 13px; }
    .period-custom button, .tool-btn { padding: 7px 14px; border-radius: 8px; border: none; font-size: 13px; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }
    .btn-apply { background: #1e293b; color: #fff; }
    .tool-btn.csv { background: #16a34a; color: #fff; }
    .tool-btn.print { background: #f1f5f9; color: #1e293b; border: 1px solid #e2e8f0; }
    .period-label { font-size: 13px; color: #475569; margin-top: 12px; }
    .period-label strong { color: #0f172a; }

    /* KPI TILES */
    .kpi-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; }
    .kpi-tile { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 14px 16px; }
    .kpi-label { font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 6px; }
    .kpi-value { font-size: 1.5rem; font-weight: 800; color: #1e293b; line-height: 1.2; }
    .kpi-value.is-green { color: #16a34a; }
    .kpi-value.is-red { color: #b91c1c; }
    .kpi-value.is-amber { color: #b45309; }
    .kpi-note { font-size: 11px; color: #94a3b8; margin-top: 4px; }

    /* CHARTS */
    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .grid-3-2 { display: grid; grid-template-columns: 1.5fr 1fr; gap: 20px; }
    .chart-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px 18px; }
    .chart-box h4 { margin: 0 0 2px; font-size: 13px; font-weight: 700; color: #1e293b; }
    .chart-box p { margin: 0 0 12px; font-size: 12px; color: #94a3b8; }
    .chart-box canvas { max-height: 260px; }
    .chart-empty { text-align: center; padding: 36px 0; color: #94a3b8; font-size: 13px; }

    /* OUTCOME BARS */
    .outcome { display: grid; grid-template-columns: 150px 1fr 60px; gap: 10px; align-items: center; margin-bottom: 10px; font-size: 13px; }
    .outcome-track { background: #e2e8f0; border-radius: 99px; height: 10px; overflow: hidden; }
    .outcome-fill { height: 100%; border-radius: 99px; }
    .outcome-count { text-align: right; font-weight: 700; color: #1e293b; }
    .rate-row { display: flex; gap: 12px; margin-top: 14px; flex-wrap: wrap; }
    .rate-pill { padding: 6px 12px; border-radius: 99px; font-size: 12px; font-weight: 700; }

    /* TABLES */
    table { width: 100%; border-collapse: collapse; font-size: 13px; }
    thead { background: #f8fafc; }
    th { padding: 10px 12px; text-align: left; font-size: 11px; color: #94a3b8; text-transform: uppercase; letter-spacing: .5px; }
    td { padding: 11px 12px; border-top: 1px solid #f1f5f9; vertical-align: middle; }
    .num-cell { text-align: right; white-space: nowrap; }
    .util-track { background: #e2e8f0; border-radius: 99px; height: 8px; width: 100%; min-width: 80px; overflow: hidden; }
    .util-fill { background: #2563eb; height: 100%; border-radius: 99px; }
    .stars { color: #f59e0b; letter-spacing: 1px; }
    .type-pill { padding: 3px 8px; border-radius: 6px; font-size: 11px; font-weight: 700; white-space: nowrap; }
    .type-pill.van { background: #dcfce7; color: #166534; }
    .type-pill.tour { background: #ede9fe; color: #6d28d9; }
    .type-pill.joiner { background: #e0f2fe; color: #0369a1; }
    .empty-row { text-align: center; color: #94a3b8; padding: 22px; }
    .rank-list { margin: 0; padding: 0; list-style: none; }
    .rank-list li { display: flex; justify-content: space-between; gap: 10px; padding: 8px 0; border-top: 1px solid #eef2f7; font-size: 13px; }
    .rank-list li:first-child { border-top: none; }
    .rank-list .muted { color: #94a3b8; font-size: 12px; }

    /* VAN MAINTENANCE */
    .van-plate { font-size: 11px; color: #94a3b8; }
    .maint-date { font-size: 12px; color: #374151; }
    .maint-empty { color: #cbd5e1; font-size: 12px; }
    .maint-old { color: #ef4444; font-size: 12px; font-weight: 600; }
    .maint-ok  { color: #16a34a; font-size: 12px; }
    .edit-btn { padding: 5px 12px; font-size: 12px; background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 6px; cursor: pointer; font-weight: 600; color: #374151; }
    .edit-btn:hover { background: #e2e8f0; }
    .maint-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.45); z-index: 1000; justify-content: center; align-items: center; }
    .maint-overlay.open { display: flex; }
    .maint-box { background: #fff; border-radius: 16px; width: 440px; max-width: calc(100vw - 32px); box-shadow: 0 20px 60px rgba(0,0,0,0.15); overflow: hidden; }
    .maint-box-header { padding: 18px 22px 14px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; }
    .maint-box-header h3 { margin: 0; font-size: 16px; }
    .maint-close { background: none; border: none; font-size: 20px; cursor: pointer; color: #94a3b8; }
    .maint-form { padding: 22px; }
    .maint-field { margin-bottom: 16px; }
    .maint-field label { display: block; font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 5px; }
    .maint-field input, .maint-field textarea { width: 100%; box-sizing: border-box; border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px 12px; font-size: 13px; outline: none; }
    .maint-field textarea { resize: vertical; min-height: 70px; }
    .maint-save { width: 100%; padding: 10px; background: #2563eb; color: #fff; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; margin-top: 4px; }

    @media (max-width: 1000px) { .kpi-row { grid-template-columns: repeat(2, 1fr); } .grid-2, .grid-3-2 { grid-template-columns: 1fr; } }

    /* PRINT / SAVE AS PDF: the whole report, without the app chrome */
    @media print {
        .sidebar, .sidebar-overlay, .hamburger, .no-print, .maint-overlay { display: none !important; }
        .main { margin: 0 !important; padding: 0 !important; width: 100% !important; }
        body { background: #fff; }
        .section-card { box-shadow: none; border: 1px solid #e2e8f0; break-inside: avoid; }
        .print-header { display: block !important; }
    }
    .print-header { display: none; margin-bottom: 16px; }
    </style>
</head>
<body>
@php
    $peso = fn ($v) => '₱' . number_format((float) $v, 2);
    $rangeLabel = \Illuminate\Support\Carbon::parse($from)->format('M d, Y') . ' – ' . \Illuminate\Support\Carbon::parse($to)->format('M d, Y');
    $presets = ['week' => 'This Week', 'month' => 'This Month', 'year' => 'This Year', 'all' => 'All Time'];
    $typeClass = ['Van Rental' => 'van', 'Tour Package' => 'tour', 'Joiner Trip' => 'joiner'];
    $outcomeColor = ['completed' => '#16a34a', 'active' => '#2563eb', 'awaiting' => '#f59e0b', 'cancelled' => '#94a3b8', 'rejected' => '#ef4444'];
    $exportQuery = http_build_query(['range' => $range, 'from' => $from, 'to' => $to]);
@endphp

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
            <a href="/admin/book-for-customer">New Booking</a>
            <a href="/admin/joiner-trips">Joiner Trips</a>
            <a href="/admin/pricing">Pricing</a>
            <a href="/admin/reports" class="active">Reports</a>
            <a href="/admin/live-map">Live Map</a>
            @include('admin.partials.messages-link')
            <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
            <form id="logout-form" action="/logout" method="POST" style="display:none;">@csrf</form>
        </nav>
    </div>

    <div class="main">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
            <button class="hamburger" onclick="toggleSidebar()" aria-label="Menu"><span></span><span></span><span></span></button>
            <h1 style="margin:0;">Reports &amp; Analytics</h1>
        </div>

        <div class="print-header">
            <h2 style="margin:0;">Rem's Transport — Reports &amp; Analytics</h2>
            <div style="color:#64748b;font-size:13px;">Period: {{ $rangeLabel }} &bull; Printed {{ now(\App\Support\LocalDate::TIMEZONE)->format('M d, Y g:i A') }}</div>
        </div>

        <!-- PERIOD -->
        <div class="section-card no-print">
            <div class="period-bar">
                <div class="period-tabs">
                    @foreach($presets as $key => $label)
                        <a href="?range={{ $key }}" class="period-tab {{ $range === $key ? 'active' : '' }}">{{ $label }}</a>
                    @endforeach
                </div>
                <form class="period-custom" method="GET">
                    <input type="hidden" name="range" value="custom">
                    <input type="date" name="from" value="{{ $from }}" required aria-label="From">
                    <span style="color:#94a3b8;">to</span>
                    <input type="date" name="to" value="{{ $to }}" required aria-label="To">
                    <button type="submit" class="btn-apply {{ $range === 'custom' ? '' : '' }}">Apply</button>
                </form>
                <div style="margin-left:auto;display:flex;gap:8px;">
                    <a href="{{ route('admin.reports.export') }}?{{ $exportQuery }}" class="tool-btn csv"><i class="fas fa-file-csv"></i> Export CSV</a>
                    <button type="button" class="tool-btn print" onclick="window.print()"><i class="fas fa-print"></i> Print / PDF</button>
                </div>
            </div>
            <div class="period-label">Showing trips dated <strong>{{ $rangeLabel }}</strong> (Philippine time). Bookings count toward the period of their trip date.</div>
        </div>

        <!-- KPIs -->
        <div class="section-card">
            <div class="section-title"><i class="fas fa-gauge-high"></i> Overview</div>
            <p class="section-sub">{{ $rangeLabel }}</p>
            <div class="kpi-row">
                <div class="kpi-tile">
                    <div class="kpi-label">Revenue (completed trips)</div>
                    <div class="kpi-value is-green">{{ $peso($kpi['revenue']) }}</div>
                    <div class="kpi-note">{{ $kpi['completed'] }} completed {{ \Illuminate\Support\Str::plural('trip', $kpi['completed']) }}</div>
                </div>
                <div class="kpi-tile">
                    <div class="kpi-label">Payments received</div>
                    <div class="kpi-value">{{ $peso($payments['net']) }}</div>
                    <div class="kpi-note">{{ $peso($payments['received']) }} in, {{ $peso($payments['refunds']) }} refunded</div>
                </div>
                <div class="kpi-tile">
                    <div class="kpi-label">Bookings</div>
                    <div class="kpi-value">{{ $kpi['bookings'] }}</div>
                    <div class="kpi-note">Excludes cancelled &amp; rejected</div>
                </div>
                <div class="kpi-tile">
                    <div class="kpi-label">Avg. completed booking</div>
                    <div class="kpi-value">{{ $peso($kpi['avg_value']) }}</div>
                    <div class="kpi-note">Revenue ÷ completed trips</div>
                </div>
                <div class="kpi-tile">
                    <div class="kpi-label">Outstanding balances</div>
                    <div class="kpi-value is-amber">{{ $peso($outstanding['amount']) }}</div>
                    <div class="kpi-note">Still to collect on upcoming trips</div>
                </div>
                <div class="kpi-tile">
                    <div class="kpi-label">Refunds owed</div>
                    <div class="kpi-value {{ $refundsOwed > 0 ? 'is-red' : '' }}">{{ $peso($refundsOwed) }}</div>
                    <div class="kpi-note">Rejected / cancelled, not yet sent</div>
                </div>
                <div class="kpi-tile">
                    <div class="kpi-label">Cancellation rate</div>
                    <div class="kpi-value">{{ $kpi['cancel_rate'] }}%</div>
                    <div class="kpi-note">Rejection rate {{ $kpi['reject_rate'] }}%</div>
                </div>
                <div class="kpi-tile">
                    <div class="kpi-label">Booking lead time</div>
                    <div class="kpi-value">{{ $leadTime['average'] !== null ? $leadTime['average'] . ' days' : '—' }}</div>
                    <div class="kpi-note">Average days booked before the trip</div>
                </div>
            </div>
        </div>

        <!-- REVENUE TREND + BY TYPE -->
        <div class="section-card">
            <div class="section-title"><i class="fas fa-chart-line"></i> Revenue</div>
            <p class="section-sub">Completed trips only, by trip date</p>
            <div class="grid-3-2">
                <div class="chart-box">
                    <h4>Revenue &amp; bookings — last 12 months</h4>
                    <p>Up to {{ \Illuminate\Support\Carbon::parse(min($to, \App\Support\LocalDate::today()))->format('M Y') }}</p>
                    <canvas id="trendChart" role="img" aria-label="Monthly completed revenue and number of bookings for the last 12 months"></canvas>
                </div>
                <div class="chart-box">
                    <h4>Revenue by booking type</h4>
                    <p>{{ $rangeLabel }}</p>
                    @if($revenueByType->sum() > 0)
                        <canvas id="typeChart" role="img" aria-label="Share of completed revenue by booking type"></canvas>
                    @else
                        <div class="chart-empty">No completed trips in this period.</div>
                    @endif
                </div>
            </div>
        </div>

        <!-- OUTCOMES + PAYMENTS -->
        <div class="grid-2">
            <div class="section-card">
                <div class="section-title"><i class="fas fa-list-check"></i> Booking Outcomes</div>
                <p class="section-sub">{{ $trips->count() }} {{ \Illuminate\Support\Str::plural('booking', $trips->count()) }} with trips in this period</p>
                @foreach(\App\Support\ReportData::OUTCOMES as $key => $label)
                    @php $pct = $trips->count() ? round($outcomes[$key] / $trips->count() * 100) : 0; @endphp
                    <div class="outcome">
                        <span>{{ $label }}</span>
                        <div class="outcome-track"><div class="outcome-fill" style="width: {{ $pct }}%; background: {{ $outcomeColor[$key] }};"></div></div>
                        <span class="outcome-count">{{ $outcomes[$key] }} <span style="color:#94a3b8;font-weight:500;">({{ $pct }}%)</span></span>
                    </div>
                @endforeach
                <div class="rate-row">
                    <span class="rate-pill" style="background:#f1f5f9;color:#475569;">Cancellation rate {{ $kpi['cancel_rate'] }}%</span>
                    <span class="rate-pill" style="background:#fee2e2;color:#b91c1c;">Rejection rate {{ $kpi['reject_rate'] }}%</span>
                </div>
            </div>

            <div class="section-card">
                <div class="section-title"><i class="fas fa-wallet"></i> Payments</div>
                <p class="section-sub">Received {{ $rangeLabel }}, by payment method</p>
                @if($payments['by_method']->isEmpty())
                    <div class="chart-empty">No payments recorded in this period.</div>
                @else
                    <canvas id="methodChart" role="img" aria-label="Payments received by method" style="max-height:200px;"></canvas>
                @endif
                <ul class="rank-list" style="margin-top:12px;">
                    <li><span>Total received</span><strong>{{ $peso($payments['received']) }}</strong></li>
                    <li><span>Refunds sent</span><strong style="color:#b91c1c;">− {{ $peso($payments['refunds']) }}</strong></li>
                    <li><span>Net</span><strong style="color:#16a34a;">{{ $peso($payments['net']) }}</strong></li>
                </ul>
            </div>
        </div>

        <!-- VAN UTILIZATION -->
        <div class="section-card">
            <div class="section-title"><i class="fas fa-van-shuttle"></i> Van Utilization</div>
            <p class="section-sub">Days each van was booked out of {{ $report->days() }} {{ \Illuminate\Support\Str::plural('day', $report->days()) }} — van rentals, tours and joiner trips (excludes cancelled &amp; rejected)</p>
            <div style="overflow-x:auto;">
                <table style="min-width:640px;">
                    <thead><tr><th>Van</th><th class="num-cell">Trips</th><th class="num-cell">Days used</th><th style="width:30%;">Utilization</th><th class="num-cell">Revenue</th></tr></thead>
                    <tbody>
                        @forelse($vanUse as $v)
                            <tr>
                                <td><strong>{{ $v->name }}</strong> <span class="van-plate">{{ $v->plate }}</span></td>
                                <td class="num-cell">{{ $v->trips }}</td>
                                <td class="num-cell">{{ $v->days_used }}</td>
                                <td>
                                    <div style="display:flex;align-items:center;gap:8px;">
                                        <div class="util-track"><div class="util-fill" style="width: {{ min(100, $v->utilization) }}%;"></div></div>
                                        <span style="font-weight:700;min-width:40px;text-align:right;">{{ $v->utilization }}%</span>
                                    </div>
                                </td>
                                <td class="num-cell">{{ $peso($v->revenue) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="empty-row">No vans yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- DRIVERS -->
        <div class="section-card">
            <div class="section-title"><i class="fas fa-id-card"></i> Driver Performance</div>
            <p class="section-sub">Trips in {{ $rangeLabel }}; rating is the all-time average from customer feedback</p>
            <div style="overflow-x:auto;">
                <table style="min-width:620px;">
                    <thead><tr><th>Driver</th><th class="num-cell">Completed trips</th><th class="num-cell">Assigned trips</th><th class="num-cell">Revenue</th><th>Rating</th></tr></thead>
                    <tbody>
                        @forelse($drivers as $d)
                            <tr>
                                <td><strong>{{ $d->name }}</strong></td>
                                <td class="num-cell">{{ $d->trips }}</td>
                                <td class="num-cell">{{ $d->assigned }}</td>
                                <td class="num-cell">{{ $peso($d->revenue) }}</td>
                                <td>
                                    @if($d->rating)
                                        <span class="stars">{{ str_repeat('★', (int) round($d->rating)) }}{{ str_repeat('☆', 5 - (int) round($d->rating)) }}</span>
                                        <strong>{{ number_format($d->rating, 1) }}</strong> <span style="color:#94a3b8;font-size:12px;">({{ $d->rating_count }})</span>
                                    @else
                                        <span style="color:#cbd5e1;">No ratings yet</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="empty-row">No drivers yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- DEMAND -->
        <div class="section-card">
            <div class="section-title"><i class="fas fa-fire"></i> Demand</div>
            <p class="section-sub">{{ $rangeLabel }} — excludes cancelled &amp; rejected</p>
            <div class="grid-2">
                <div class="chart-box">
                    <h4>Busiest days of the week</h4>
                    <p>Bookings by trip day</p>
                    <canvas id="weekdayChart" role="img" aria-label="Number of bookings for each day of the week"></canvas>
                </div>
                <div class="chart-box">
                    <h4>How early customers book</h4>
                    <p>Days between booking and the trip{{ $leadTime['average'] !== null ? ' — average ' . $leadTime['average'] . ' days' : '' }}</p>
                    <canvas id="leadChart" role="img" aria-label="Number of bookings by how many days ahead they were made"></canvas>
                </div>
            </div>
            <div class="grid-2" style="margin-top:20px;">
                <div class="chart-box">
                    <h4>Top destinations</h4>
                    <p>Van rentals and joiner trips, by city</p>
                    <ul class="rank-list">
                        @forelse($destinations['places'] as $i => $p)
                            <li><span>{{ $i + 1 }}. {{ $p->name }}</span><span><strong>{{ $p->count }}</strong> <span class="muted">{{ \Illuminate\Support\Str::plural('booking', $p->count) }}</span></span></li>
                        @empty
                            <li class="muted">No bookings in this period.</li>
                        @endforelse
                    </ul>
                </div>
                <div class="chart-box">
                    <h4>Top tour packages</h4>
                    <p>By number of bookings</p>
                    <ul class="rank-list">
                        @forelse($destinations['tours'] as $i => $p)
                            <li><span>{{ $i + 1 }}. {{ $p->name }}</span><span><strong>{{ $p->count }}</strong> <span class="muted">· {{ $peso($p->revenue) }}</span></span></li>
                        @empty
                            <li class="muted">No tour bookings in this period.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        <!-- CUSTOMERS -->
        <div class="section-card">
            <div class="section-title"><i class="fas fa-users"></i> Customers</div>
            <p class="section-sub">Repeat customers have 2 or more bookings overall (not cancelled or rejected)</p>
            <div class="kpi-row" style="margin-bottom:18px;">
                <div class="kpi-tile"><div class="kpi-label">Registered customers</div><div class="kpi-value">{{ $customers['total'] }}</div></div>
                <div class="kpi-tile"><div class="kpi-label">New in this period</div><div class="kpi-value">{{ $customers['new'] }}</div></div>
                <div class="kpi-tile"><div class="kpi-label">Repeat customers</div><div class="kpi-value">{{ $customers['repeat'] }}</div><div class="kpi-note">of {{ $customers['booked'] }} who have booked</div></div>
                <div class="kpi-tile"><div class="kpi-label">Repeat rate</div><div class="kpi-value">{{ $customers['repeat_rate'] }}%</div></div>
            </div>
            <h4 style="margin:0 0 6px;font-size:13px;color:#1e293b;">Top customers ({{ $rangeLabel }})</h4>
            <ul class="rank-list">
                @forelse($customers['top'] as $i => $c)
                    <li><span>{{ $i + 1 }}. {{ $c->name }} <span class="muted">· {{ $c->trips }} completed {{ \Illuminate\Support\Str::plural('trip', $c->trips) }}</span></span><strong>{{ $peso($c->spent) }}</strong></li>
                @empty
                    <li class="muted">No completed trips in this period.</li>
                @endforelse
            </ul>
        </div>

        <!-- COMPLETED TRANSACTIONS -->
        <div class="section-card">
            <div class="section-title"><i class="fas fa-receipt"></i> Completed Trips</div>
            <p class="section-sub">{{ $completedTrips->count() }} in {{ $rangeLabel }} &bull; all bookings for the period are in the CSV export</p>
            <div style="overflow-x:auto;">
                <table style="min-width:720px;">
                    <thead><tr><th>Trip date</th><th>Booking</th><th>Customer</th><th>Type</th><th>Destination</th><th>Driver</th><th class="num-cell">Amount</th></tr></thead>
                    <tbody>
                        @forelse($completedTrips->sortByDesc('date') as $t)
                            <tr>
                                <td style="white-space:nowrap;">{{ \Illuminate\Support\Carbon::parse($t->date)->format('M d, Y') }}</td>
                                <td style="font-weight:700;color:#2563eb;">{{ $t->ref }}</td>
                                <td>{{ $t->customer }}</td>
                                <td><span class="type-pill {{ $typeClass[$t->type] }}">{{ $t->type }}</span></td>
                                <td>{{ \Illuminate\Support\Str::limit($t->destination, 50) }}</td>
                                <td>{{ $t->driver_name ?: '—' }}</td>
                                <td class="num-cell" style="font-weight:700;color:#16a34a;">{{ $peso($t->total) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="empty-row">No completed trips in this period.</td></tr>
                        @endforelse
                    </tbody>
                    @if($completedTrips->isNotEmpty())
                        <tfoot><tr><td colspan="6" style="font-weight:700;">Total</td><td class="num-cell" style="font-weight:800;color:#16a34a;">{{ $peso($completedTrips->sum('total')) }}</td></tr></tfoot>
                    @endif
                </table>
            </div>
        </div>

        <!-- VAN MAINTENANCE MODULE -->
        <div class="section-card">
            <div class="section-title" style="justify-content:space-between;">
                <span><i class="fas fa-tools"></i> Van Maintenance Records</span>
                <button class="tool-btn print no-print" onclick="printVanMaintenance()" style="font-size:12px;padding:6px 14px;">
                    <i class="fas fa-print"></i> Print Maintenance Report
                </button>
            </div>
            <p class="section-sub">Current records (not limited to the period above)</p>
            <div style="overflow-x:auto;">
            <table style="min-width:600px;">
                <thead>
                    <tr>
                        <th>Van</th>
                        <th>Last Oil Change</th>
                        <th>Last Maintenance</th>
                        <th>Last Problem</th>
                        <th>Last Trip</th>
                        <th class="no-print">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($vans as $van)
                    <tr>
                        <td>
                            <div style="font-weight:600;">{{ $van->name }}</div>
                            <div class="van-plate">{{ $van->plate_number }}</div>
                        </td>
                        <td>
                            @if($van->last_oil_change)
                                @php $days = now()->diffInDays($van->last_oil_change, false); @endphp
                                <span class="{{ $days < -90 ? 'maint-old' : 'maint-ok' }}">
                                    {{ \Carbon\Carbon::parse($van->last_oil_change)->format('M d, Y') }}
                                </span>
                                @if($days < -90)
                                    <div style="font-size:10px;color:#ef4444;">⚠ {{ abs((int)$days) }} days ago</div>
                                @endif
                            @else
                                <span class="maint-empty">Not recorded</span>
                            @endif
                        </td>
                        <td>
                            @if($van->last_maintenance)
                                {{ \Carbon\Carbon::parse($van->last_maintenance)->format('M d, Y') }}
                            @else
                                <span class="maint-empty">Not recorded</span>
                            @endif
                        </td>
                        <td>
                            @if($van->last_problem_date)
                                <div class="maint-date">{{ \Carbon\Carbon::parse($van->last_problem_date)->format('M d, Y') }}</div>
                                @if($van->last_problem_notes)
                                    <div style="font-size:11px;color:#64748b;margin-top:2px;">{{ $van->last_problem_notes }}</div>
                                @endif
                            @else
                                <span class="maint-empty">None recorded</span>
                            @endif
                        </td>
                        <td>
                            @if($van->last_trip_date)
                                <span class="maint-date">{{ \Carbon\Carbon::parse($van->last_trip_date)->format('M d, Y') }}</span>
                            @else
                                <span class="maint-empty">No trips yet</span>
                            @endif
                        </td>
                        <td class="no-print">
                            <button class="edit-btn" onclick="openMaintModal(
                                {{ $van->id }},
                                '{{ addslashes($van->name) }} - {{ $van->plate_number }}',
                                '{{ $van->last_oil_change ?? '' }}',
                                '{{ $van->last_maintenance ?? '' }}',
                                '{{ $van->last_problem_date ?? '' }}',
                                '{{ addslashes($van->last_problem_notes ?? '') }}'
                            )">
                                <i class="fas fa-edit"></i> Update
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        </div>

    </div>
</div>

<!-- VAN MAINTENANCE MODAL -->
<div class="maint-overlay" id="maint-modal" onclick="closeMaintModal(event)">
    <div class="maint-box">
        <div class="maint-box-header">
            <h3 id="maint-van-name"></h3>
            <button class="maint-close" onclick="closeMaintModal()">&#x2715;</button>
        </div>
        <form class="maint-form" method="POST" id="maint-form">
            @csrf
            <div class="maint-field">
                <label>Last Oil Change</label>
                <input type="date" name="last_oil_change" id="maint-oil">
            </div>
            <div class="maint-field">
                <label>Last Maintenance</label>
                <input type="date" name="last_maintenance" id="maint-maint">
            </div>
            <div class="maint-field">
                <label>Last Problem Date</label>
                <input type="date" name="last_problem_date" id="maint-prob-date">
            </div>
            <div class="maint-field">
                <label>Problem Description</label>
                <textarea name="last_problem_notes" id="maint-prob-notes" placeholder="e.g. Engine overheating, flat tire..."></textarea>
            </div>
            <button type="submit" class="maint-save">Save Changes</button>
        </form>
    </div>
</div>

<script>
const vansData = @json($vans);

// ---- CHARTS ----
const BLUE = '#2563eb', BLUE_FILL = 'rgba(37, 99, 235, 0.12)', GRID = '#eef1f5', INK = '#64748b';
const TYPE_COLORS = ['#16a34a', '#7c3aed', '#0284c7'];
const peso = v => '₱' + Number(v).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
Chart.defaults.font.family = "'Segoe UI', system-ui, -apple-system, sans-serif";
Chart.defaults.color = INK;

const trend = @json($trend);
new Chart(document.getElementById('trendChart'), {
    data: {
        labels: trend.map(m => m.label),
        datasets: [
            { type: 'bar', label: 'Revenue', data: trend.map(m => m.revenue), backgroundColor: BLUE, borderRadius: 4, maxBarThickness: 28, yAxisID: 'y' },
            { type: 'line', label: 'Bookings', data: trend.map(m => m.bookings), borderColor: '#f59e0b', backgroundColor: '#f59e0b', cubicInterpolationMode: 'monotone', pointRadius: 3, yAxisID: 'y1' },
        ],
    },
    options: {
        responsive: true,
        interaction: { mode: 'index', intersect: false },
        plugins: { tooltip: { callbacks: { label: c => c.dataset.label === 'Revenue' ? 'Revenue: ' + peso(c.parsed.y) : 'Bookings: ' + c.parsed.y } } },
        scales: {
            x: { grid: { display: false }, ticks: { font: { size: 10 } } },
            y: { beginAtZero: true, grid: { color: GRID }, ticks: { callback: v => '₱' + Number(v).toLocaleString('en-PH') } },
            y1: { beginAtZero: true, position: 'right', grid: { display: false }, ticks: { precision: 0 } },
        },
    },
});

@if($revenueByType->sum() > 0)
const byType = @json($revenueByType);
new Chart(document.getElementById('typeChart'), {
    type: 'doughnut',
    data: { labels: Object.keys(byType), datasets: [{ data: Object.values(byType), backgroundColor: TYPE_COLORS, borderWidth: 2, borderColor: '#fff' }] },
    options: {
        responsive: true, cutout: '62%',
        plugins: {
            legend: { position: 'bottom' },
            tooltip: { callbacks: { label: c => {
                const total = c.dataset.data.reduce((a, b) => a + b, 0);
                return `${c.label}: ${peso(c.parsed)} (${total ? Math.round(c.parsed / total * 100) : 0}%)`;
            } } },
        },
    },
});
@endif

@if($payments['by_method']->isNotEmpty())
const byMethod = @json($payments['by_method']);
new Chart(document.getElementById('methodChart'), {
    type: 'bar',
    data: { labels: Object.keys(byMethod), datasets: [{ data: Object.values(byMethod), backgroundColor: BLUE, borderRadius: 4, maxBarThickness: 26 }] },
    options: {
        indexAxis: 'y', responsive: true,
        plugins: { legend: { display: false }, tooltip: { callbacks: { label: c => peso(c.parsed.x) } } },
        scales: { x: { beginAtZero: true, grid: { color: GRID }, ticks: { callback: v => '₱' + Number(v).toLocaleString('en-PH') } }, y: { grid: { display: false } } },
    },
});
@endif

const weekdays = @json($weekdays);
new Chart(document.getElementById('weekdayChart'), {
    type: 'bar',
    data: { labels: Object.keys(weekdays), datasets: [{ data: Object.values(weekdays), backgroundColor: BLUE, borderRadius: 4, maxBarThickness: 32 }] },
    options: { responsive: true, plugins: { legend: { display: false } }, scales: { x: { grid: { display: false } }, y: { beginAtZero: true, grid: { color: GRID }, ticks: { precision: 0 } } } },
});

const lead = @json($leadTime['buckets']);
new Chart(document.getElementById('leadChart'), {
    type: 'bar',
    data: { labels: Object.keys(lead), datasets: [{ data: Object.values(lead), backgroundColor: '#0ea5e9', borderRadius: 4, maxBarThickness: 40 }] },
    options: { responsive: true, plugins: { legend: { display: false } }, scales: { x: { grid: { display: false } }, y: { beginAtZero: true, grid: { color: GRID }, ticks: { precision: 0 } } } },
});

// ---- VAN MAINTENANCE ----
function printVanMaintenance() {
    const vanRows = vansData.map(v => {
        const fmt = d => d ? new Date(d).toLocaleDateString('en-PH', {month:'short', day:'numeric', year:'numeric'}) : '—';
        const oilDate  = v.last_oil_change ? new Date(v.last_oil_change) : null;
        const daysAgo  = oilDate ? Math.floor((new Date() - oilDate) / 86400000) : null;
        const oilWarn  = daysAgo && daysAgo > 90 ? ` <span style="color:#ef4444;font-size:10px;">⚠ ${daysAgo} days ago</span>` : '';
        return `<tr>
            <td><strong>${v.name}</strong><br><span style="font-size:11px;color:#64748b;">${v.plate_number}</span></td>
            <td>${fmt(v.last_oil_change)}${oilWarn}</td>
            <td>${fmt(v.last_maintenance)}</td>
            <td>${fmt(v.last_problem_date)}${v.last_problem_notes ? '<br><span style="font-size:11px;color:#64748b;">'+v.last_problem_notes+'</span>' : ''}</td>
            <td>${fmt(v.last_trip_date)}</td>
        </tr>`;
    }).join('');

    const w = window.open('', '_blank');
    w.document.write(`
        <html><head><title>Van Maintenance Report</title>
        <style>
            * { box-sizing: border-box; }
            body { font-family: 'Segoe UI', sans-serif; padding: 36px; color: #1e293b; font-size: 13px; }
            h2 { margin: 0 0 2px; font-size: 20px; }
            .sub { font-size: 13px; color: #94a3b8; margin-bottom: 28px; }
            table { width: 100%; border-collapse: collapse; }
            th { background: #f8fafc; padding: 10px 12px; text-align: left; font-size: 11px; color: #64748b; text-transform: uppercase; letter-spacing: 0.4px; border-bottom: 2px solid #e2e8f0; }
            td { padding: 10px 12px; border-bottom: 1px solid #f1f5f9; vertical-align: top; }
            tr:last-child td { border-bottom: none; }
            .footer { margin-top: 40px; font-size: 11px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 14px; display: flex; justify-content: space-between; }
        </style></head>
        <body>
            <h2>Rem's Transport — Van Maintenance Report</h2>
            <div class="sub">As of ${new Date().toLocaleDateString('en-PH', {month:'long', day:'numeric', year:'numeric'})}</div>
            <table>
                <thead><tr><th>Van</th><th>Last Oil Change</th><th>Last Maintenance</th><th>Last Problem</th><th>Last Trip</th></tr></thead>
                <tbody>${vanRows || '<tr><td colspan="5" style="text-align:center;color:#94a3b8;padding:20px;">No vans recorded</td></tr>'}</tbody>
            </table>
            <div class="footer">
                <span>Rem's Transport</span>
                <span>Printed: ${new Date().toLocaleString('en-PH')}</span>
            </div>
        </body></html>`);
    w.document.close();
    w.print();
}

function openMaintModal(id, name, oil, maint, probDate, probNotes) {
    document.getElementById('maint-van-name').textContent = name;
    document.getElementById('maint-form').action          = '/admin/vans/maintenance/' + id;
    document.getElementById('maint-oil').value            = oil;
    document.getElementById('maint-maint').value          = maint;
    document.getElementById('maint-prob-date').value      = probDate;
    document.getElementById('maint-prob-notes').value     = probNotes;
    document.getElementById('maint-modal').classList.add('open');
}

function closeMaintModal(e) {
    if (!e || e.target === document.getElementById('maint-modal')) {
        document.getElementById('maint-modal').classList.remove('open');
    }
}

function toggleSidebar() {
    document.getElementById('adminSidebar').classList.toggle('open');
    document.getElementById('sidebarOverlay').classList.toggle('open');
}
</script>
<script src="/js/pwa.js"></script>
</body>
</html>
