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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
    .section-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        padding: 24px;
        margin-bottom: 28px;
    }
    .section-title {
        font-size: 16px;
        font-weight: 700;
        color: #1e293b;
        margin: 0 0 18px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .section-title i { color: #2563eb; }

    /* REVENUE TABS */
    .rev-tabs { display: flex; gap: 8px; margin-bottom: 20px; flex-wrap: wrap; }
    .rev-tab {
        padding: 7px 18px;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        cursor: pointer;
        font-size: 13px;
        font-weight: 600;
        color: #64748b;
        transition: all 0.15s;
    }
    .rev-tab.active { background: #2563eb; color: #fff; border-color: #2563eb; }
    .rev-panel { display: none; }
    .rev-panel.active { display: block; }

    .rev-amount {
        font-size: 2.4rem;
        font-weight: 800;
        color: #16a34a;
        margin: 0 0 4px;
    }
    .rev-sub { font-size: 13px; color: #94a3b8; }
    .rev-print-btn {
        margin-top: 16px;
        padding: 8px 20px;
        background: #1e293b;
        color: #fff;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 13px;
        font-weight: 600;
    }
    .rev-print-btn:hover { background: #334155; }

    /* TRANSACTIONS TABLE */
    table { width: 100%; border-collapse: collapse; font-size: 13px; }
    thead { background: #f8fafc; }
    th { padding: 10px 12px; text-align: left; font-size: 11px; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; }
    td { padding: 12px; border-top: 1px solid #f1f5f9; vertical-align: middle; }

    /* VAN MAINTENANCE TABLE */
    .van-table th { background: #f8fafc; }
    .van-plate { font-size: 11px; color: #94a3b8; }
    .maint-date { font-size: 12px; color: #374151; }
    .maint-empty { color: #cbd5e1; font-size: 12px; }
    .maint-old { color: #ef4444; font-size: 12px; font-weight: 600; }
    .maint-ok  { color: #16a34a; font-size: 12px; }
    .edit-btn {
        padding: 5px 12px;
        font-size: 12px;
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
        color: #374151;
    }
    .edit-btn:hover { background: #e2e8f0; }

    /* MAINTENANCE MODAL */
    .maint-overlay {
        display: none;
        position: fixed; inset: 0;
        background: rgba(0,0,0,0.45);
        z-index: 1000;
        justify-content: center;
        align-items: center;
    }
    .maint-overlay.open { display: flex; }
    .maint-box {
        background: #fff;
        border-radius: 16px;
        width: 440px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.15);
        overflow: hidden;
    }
    .maint-box-header {
        padding: 18px 22px 14px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .maint-box-header h3 { margin: 0; font-size: 16px; }
    .maint-close { background: none; border: none; font-size: 20px; cursor: pointer; color: #94a3b8; }
    .maint-form { padding: 22px; }
    .maint-field { margin-bottom: 16px; }
    .maint-field label { display: block; font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 5px; }
    .maint-field input, .maint-field textarea {
        width: 100%; box-sizing: border-box;
        border: 1px solid #e2e8f0; border-radius: 8px;
        padding: 8px 12px; font-size: 13px; outline: none;
    }
    .maint-field input:focus, .maint-field textarea:focus { border-color: #2563eb; }
    .maint-field textarea { resize: vertical; min-height: 70px; }
    .maint-save {
        width: 100%; padding: 10px;
        background: #2563eb; color: #fff;
        border: none; border-radius: 8px;
        font-size: 14px; font-weight: 600;
        cursor: pointer; margin-top: 4px;
    }
    .maint-save:hover { background: #1d4ed8; }

    /* PRINT STYLES */
    @media print {
        .sidebar, .rev-tabs, .rev-print-btn, .edit-btn, .maint-overlay,
        .section-card:not(.print-target) { display: none !important; }
        .print-target { display: block !important; box-shadow: none; }
        body { background: #fff; }
    }
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
            <a href="/admin/joiner-trips">Joiner Trips</a>
            <a href="/admin/pricing">Pricing</a>
            <a href="/admin/reports" class="active">Reports</a>
            <a href="/admin/live-map">Live Map</a>
            <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
            <form id="logout-form" action="/logout" method="POST" style="display:none;">@csrf</form>
        </nav>
    </div>

    <div class="main">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:24px;">
            <button class="hamburger" onclick="toggleSidebar()" aria-label="Menu"><span></span><span></span><span></span></button>
            <h1 style="margin:0;">Financial Reports</h1>
        </div>

        <!-- ① REVENUE MODULE -->
        <div class="section-card print-target" id="revenue-section">
            <div class="section-title"><i class="fas fa-chart-line"></i> Total Revenue</div>

            <div class="rev-tabs">
                <button class="rev-tab active" onclick="switchTab('week', this)">This Week</button>
                <button class="rev-tab" onclick="switchTab('month', this)">This Month</button>
                <button class="rev-tab" onclick="switchTab('year', this)">This Year</button>
                <button class="rev-tab" onclick="switchTab('all', this)">All Time</button>
            </div>

            <div id="tab-week" class="rev-panel active">
                <p class="rev-amount">₱{{ number_format($weekRevenue, 2) }}</p>
                <p class="rev-sub">Completed revenue for the current week</p>
            </div>
            <div id="tab-month" class="rev-panel">
                <p class="rev-amount">₱{{ number_format($monthRevenue, 2) }}</p>
                <p class="rev-sub">Completed revenue for {{ now()->format('F Y') }}</p>
            </div>
            <div id="tab-year" class="rev-panel">
                <p class="rev-amount">₱{{ number_format($yearRevenue, 2) }}</p>
                <p class="rev-sub">Completed revenue for {{ now()->year }}</p>
            </div>
            <div id="tab-all" class="rev-panel">
                <p class="rev-amount">₱{{ number_format($totalRevenue, 2) }}</p>
                <p class="rev-sub">All-time completed revenue &nbsp;|&nbsp; {{ $privateCount }} Private &nbsp;|&nbsp; {{ $joinerCount }} Joiner</p>
            </div>

            <button class="rev-print-btn" onclick="printRevenue()">
                <i class="fas fa-print"></i> Print Revenue Report
            </button>
        </div>

        <!-- ② RECENT TRANSACTIONS -->
        <div class="section-card">
            <div class="section-title" style="justify-content:space-between; flex-wrap:wrap; gap:10px;">
                <span><i class="fas fa-receipt"></i> Completed Transactions</span>
                <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
                    <select id="filter-month" onchange="filterTransactions()"
                        style="padding:6px 10px; border:1px solid #e2e8f0; border-radius:8px; font-size:12px; color:#374151; outline:none;">
                        <option value="">All Months</option>
                        <option value="01">January</option>
                        <option value="02">February</option>
                        <option value="03">March</option>
                        <option value="04">April</option>
                        <option value="05">May</option>
                        <option value="06">June</option>
                        <option value="07">July</option>
                        <option value="08">August</option>
                        <option value="09">September</option>
                        <option value="10">October</option>
                        <option value="11">November</option>
                        <option value="12">December</option>
                    </select>
                    <input type="number" id="filter-year" onchange="filterTransactions()" placeholder="Year e.g. 2026"
                        style="padding:6px 10px; border:1px solid #e2e8f0; border-radius:8px; font-size:12px; width:120px; outline:none;">
                    <input type="date" id="filter-date" onchange="filterTransactions()"
                        style="padding:6px 10px; border:1px solid #e2e8f0; border-radius:8px; font-size:12px; outline:none;">
                    <button onclick="clearTxFilters()"
                        style="padding:6px 12px; border:1px solid #e2e8f0; border-radius:8px; font-size:12px; background:#f8fafc; cursor:pointer; color:#64748b; font-weight:600;">
                        Clear
                    </button>
                </div>
            </div>
            <div id="tx-count" style="font-size:12px; color:#94a3b8; margin-bottom:12px;"></div>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Customer Name</th>
                        <th>Type</th>
                        <th>Destination</th>
                        <th>Payment ID</th>
                        <th>Booking Status</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody id="tx-tbody">
                    @foreach($recentEarnings as $report)
                    <tr class="tx-row" data-date="{{ date('Y-m-d', strtotime($report->date)) }}">
                        <td>{{ date('M d, Y', strtotime($report->date)) }}</td>
                        <td>{{ $report->customer_name ?? '—' }}</td>
                        <td>
                            <span style="background: {{ $report->type == 'Private' ? '#e0f2fe' : '#fef3c7' }}; color: {{ $report->type == 'Private' ? '#0369a1' : '#92400e' }}; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight:600;">
                                {{ $report->type }}
                            </span>
                        </td>
                        <td>{{ $report->destination }}</td>
                        <td style="font-size:11px; color:#94a3b8;">{{ $report->payment_id ?? '—' }}</td>
                        <td>
                            <span style="padding:3px 8px; border-radius:4px; font-size:11px; font-weight:600;
                                background:{{ $report->status=='completed'?'#dcfce7':($report->status=='downpayment_paid'?'#fef9c3':'#f1f5f9') }};
                                color:{{ $report->status=='completed'?'#16a34a':($report->status=='downpayment_paid'?'#a16207':'#475569') }};">
                                {{ ucfirst(str_replace('_', ' ', $report->status)) }}
                            </span>
                        </td>
                        <td style="font-weight:700; color:#16a34a;">₱{{ number_format($report->amount, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div id="tx-empty" style="display:none; text-align:center; padding:24px; color:#94a3b8; font-size:13px;">
                No transactions found for the selected filter.
            </div>
        </div>

        <!-- ③ VAN MAINTENANCE MODULE -->
        <div class="section-card">
            <div class="section-title" style="justify-content:space-between;">
                <span><i class="fas fa-tools"></i> Van Maintenance Records</span>
                <button class="rev-print-btn" onclick="printVanMaintenance()" style="font-size:12px;padding:6px 14px;">
                    <i class="fas fa-print"></i> Print Maintenance Report
                </button>
            </div>
            <table class="van-table">
                <thead>
                    <tr>
                        <th>Van</th>
                        <th>Last Oil Change</th>
                        <th>Last Maintenance</th>
                        <th>Last Problem</th>
                        <th>Last Trip</th>
                        <th>Action</th>
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
                        <td>
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
// REVENUE TABS
function switchTab(period, btn) {
    document.querySelectorAll('.rev-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.rev-panel').forEach(p => p.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('tab-' + period).classList.add('active');
}

// Pass PHP data to JS
const transactions = @json($recentEarnings);
const vansData     = @json($vans);

function printRevenue() {
    const activePanel = document.querySelector('.rev-panel.active');
    const activeTab   = document.querySelector('.rev-tab.active').textContent.trim();
    const amount      = activePanel.querySelector('.rev-amount').textContent;
    const sub         = activePanel.querySelector('.rev-sub').textContent;

    // Build transactions rows
    const txRows = transactions.map(t => {
        const date   = new Date(t.date).toLocaleDateString('en-PH', {month:'short', day:'numeric', year:'numeric'});
        const status = t.status.replace(/_/g,' ').replace(/\b\w/g, l => l.toUpperCase());
        const amount = '₱' + parseFloat(t.amount).toLocaleString('en-PH', {minimumFractionDigits:2});
        return `<tr>
            <td>${date}</td>
            <td>${t.customer_name || '—'}</td>
            <td>${t.type}</td>
            <td>${t.destination}</td>
            <td style="font-size:11px;color:#64748b;">${t.payment_id || '—'}</td>
            <td>${status}</td>
            <td style="font-weight:700;color:#16a34a;">${amount}</td>
        </tr>`;
    }).join('');

    const w = window.open('', '_blank');
    w.document.write(`
        <html><head><title>Revenue Report</title>
        <style>
            * { box-sizing: border-box; }
            body { font-family: 'Segoe UI', sans-serif; padding: 36px; color: #1e293b; font-size: 13px; }
            h2 { margin: 0 0 2px; font-size: 20px; }
            .period { font-size: 13px; color: #64748b; margin-bottom: 24px; }
            .amount { font-size: 2.6rem; font-weight: 800; color: #16a34a; margin: 16px 0 4px; }
            .sub { font-size: 13px; color: #94a3b8; margin-bottom: 32px; }
            .section-title { font-size: 14px; font-weight: 700; margin: 32px 0 12px; padding-bottom: 8px; border-bottom: 2px solid #e2e8f0; color: #1e293b; }
            table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
            th { background: #f8fafc; padding: 9px 10px; text-align: left; font-size: 11px; color: #64748b; text-transform: uppercase; letter-spacing: 0.4px; border-bottom: 1px solid #e2e8f0; }
            td { padding: 9px 10px; border-bottom: 1px solid #f1f5f9; vertical-align: top; }
            tr:last-child td { border-bottom: none; }
            .footer { margin-top: 40px; font-size: 11px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 14px; display: flex; justify-content: space-between; }
        </style></head>
        <body>
            <h2>Rem's Transport — Revenue Report</h2>
            <div class="period">Period: <strong>${activeTab}</strong></div>
            <div class="amount">${amount}</div>
            <div class="sub">${sub}</div>

            <div class="section-title">Completed Transactions</div>
            <table>
                <thead><tr>
                    <th>Date</th><th>Customer</th><th>Type</th>
                    <th>Destination</th><th>Payment ID</th><th>Status</th><th>Amount</th>
                </tr></thead>
                <tbody>${txRows || '<tr><td colspan="7" style="text-align:center;color:#94a3b8;padding:16px;">No transactions</td></tr>'}</tbody>
            </table>

            <div class="footer">
                <span>Rem\'s Transport</span>
                <span>Printed: ${new Date().toLocaleString('en-PH')}</span>
            </div>
        </body></html>`);
    w.document.close();
    w.print();
}

// TRANSACTION FILTERS
function filterTransactions() {
    const month = document.getElementById('filter-month').value;
    const year  = document.getElementById('filter-year').value.trim();
    const exact = document.getElementById('filter-date').value;
    const rows  = document.querySelectorAll('.tx-row');
    let visible = 0;

    rows.forEach(row => {
        const d = row.dataset.date; // YYYY-MM-DD
        let show = true;
        if (exact)              show = (d === exact);
        else {
            if (month)          show = show && d.substring(5, 7) === month;
            if (year)           show = show && d.substring(0, 4) === year;
        }
        row.style.display = show ? '' : 'none';
        if (show) visible++;
    });

    const total = rows.length;
    const countEl = document.getElementById('tx-count');
    const emptyEl = document.getElementById('tx-empty');

    if (month || year || exact) {
        countEl.textContent = `Showing ${visible} of ${total} transaction(s)`;
        emptyEl.style.display = visible === 0 ? 'block' : 'none';
    } else {
        countEl.textContent = `${total} transaction(s) total`;
        emptyEl.style.display = 'none';
    }
}

function clearTxFilters() {
    document.getElementById('filter-month').value = '';
    document.getElementById('filter-year').value  = '';
    document.getElementById('filter-date').value  = '';
    filterTransactions();
}

// init count on load
document.addEventListener('DOMContentLoaded', filterTransactions);

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
                <thead><tr>
                    <th>Van</th>
                    <th>Last Oil Change</th>
                    <th>Last Maintenance</th>
                    <th>Last Problem</th>
                    <th>Last Trip</th>
                </tr></thead>
                <tbody>${vanRows || '<tr><td colspan="5" style="text-align:center;color:#94a3b8;padding:20px;">No vans recorded</td></tr>'}</tbody>
            </table>
            <div class="footer">
                <span>Rem\'s Transport</span>
                <span>Printed: ${new Date().toLocaleString('en-PH')}</span>
            </div>
        </body></html>`);
    w.document.close();
    w.print();
}

// VAN MAINTENANCE MODAL
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
