<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Passenger Manifest — #REM-{{ str_pad($booking->id, 5, '0', STR_PAD_LEFT) }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { background: #f1f5f9; color: #1e293b; min-height: 100vh; }

        .navbar {
            background: #1e293b; color: white; padding: 0 24px;
            display: flex; justify-content: space-between; align-items: center;
            height: 56px; box-shadow: 0 2px 8px rgba(0,0,0,0.25);
        }
        .brand { font-size: 15px; font-weight: 700; color: #60a5fa; display: flex; align-items: center; gap: 8px; }
        .back-link { color: #94a3b8; font-size: 13px; text-decoration: none; display: flex; align-items: center; gap: 6px; }
        .back-link:hover { color: white; }

        .main { max-width: 820px; margin: 24px auto; padding: 0 16px; }

        .trip-card {
            background: linear-gradient(135deg, #1e40af 0%, #2563eb 100%);
            color: white; border-radius: 14px; padding: 22px 26px;
            margin-bottom: 20px; box-shadow: 0 4px 18px rgba(37,99,235,0.3);
        }
        .trip-ref { font-size: 22px; font-weight: 800; margin-bottom: 12px; }
        .trip-meta { display: flex; flex-wrap: wrap; gap: 18px; font-size: 13px; opacity: 0.9; }
        .trip-meta span { display: flex; align-items: center; gap: 6px; }

        .stats-row {
            display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 20px;
        }
        .stat-card {
            background: white; border-radius: 12px; padding: 16px;
            text-align: center; box-shadow: 0 1px 4px rgba(0,0,0,0.07);
        }
        .stat-card .sn { font-size: 24px; font-weight: 800; margin-bottom: 4px; }
        .stat-card .sl { font-size: 11px; text-transform: uppercase; font-weight: 700; color: #64748b; }
        .c-blue { color: #2563eb; } .c-green { color: #16a34a; } .c-red { color: #dc2626; }

        .section { background: white; border-radius: 14px; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,0.07); margin-bottom: 16px; }
        .section-head { padding: 14px 20px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; gap: 10px; }
        .section-head h3 { font-size: 14px; font-weight: 700; color: #1e293b; }
        .section-body { padding: 20px; }

        .customer-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .info-cell .lbl { font-size: 10px; text-transform: uppercase; font-weight: 700; color: #94a3b8; margin-bottom: 3px; }
        .info-cell .val { font-size: 14px; font-weight: 600; color: #1e293b; }
        .info-cell .sub { font-size: 12px; color: #64748b; }

        table { width: 100%; border-collapse: collapse; }
        thead th { text-align: left; font-size: 11px; text-transform: uppercase; font-weight: 700; color: #94a3b8; padding: 10px 12px; border-bottom: 2px solid #f1f5f9; }
        tbody td { padding: 12px; font-size: 14px; border-bottom: 1px solid #f8fafc; }
        tbody tr:last-child td { border-bottom: none; }
        .pax-num { font-size: 12px; font-weight: 700; color: #94a3b8; }
        .pax-name { font-weight: 600; }
        .gender-badge { display: inline-block; padding: 2px 8px; border-radius: 99px; font-size: 11px; font-weight: 700; }
        .g-male { background: #dbeafe; color: #1e40af; }
        .g-female { background: #fce7f3; color: #9d174d; }
        .g-other { background: #f3f4f6; color: #374151; }

        .no-passengers { text-align: center; padding: 40px; color: #94a3b8; }
        .no-passengers i { font-size: 36px; margin-bottom: 10px; display: block; }

        .print-btn {
            display: flex; align-items: center; justify-content: center; gap: 8px;
            width: 100%; padding: 13px; background: #1e293b; color: white;
            border: none; border-radius: 10px; font-size: 14px; font-weight: 700;
            cursor: pointer; margin-top: 4px;
        }
        .print-btn:hover { background: #0f172a; }

        @media print {
            .navbar, .back-link, .print-btn { display: none; }
            body { background: white; }
            .section { box-shadow: none; border: 1px solid #e2e8f0; }
        }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="brand"><i class="fas fa-van-shuttle"></i> Driver Portal</div>
    <a href="{{ route('driver.dashboard') }}" class="back-link"><i class="fas fa-chevron-left"></i> Back to Dashboard</a>
</nav>

<div class="main">

    {{-- TRIP HEADER --}}
    <div class="trip-card">
        <div class="trip-ref">#REM-{{ str_pad($booking->id, 5, '0', STR_PAD_LEFT) }} — Passenger Manifest</div>
        <div class="trip-meta">
            <span><i class="fas fa-map-marker-alt"></i> {{ $booking->pickup }}</span>
            <span><i class="fas fa-flag"></i> {{ $booking->destination }}</span>
            <span><i class="fas fa-calendar"></i> {{ date('M d, Y', strtotime($booking->start_date)) }}@if($booking->start_date !== $booking->end_date) – {{ date('M d, Y', strtotime($booking->end_date)) }}@endif</span>
            <span><i class="fas fa-van-shuttle"></i> {{ $booking->van_name ?? 'N/A' }} ({{ $booking->plate_number ?? 'N/A' }})</span>
        </div>
    </div>

    {{-- STATS --}}
    <div class="stats-row">
        <div class="stat-card">
            <div class="sn c-blue">{{ $passengers->count() ?: $booking->passengers }}</div>
            <div class="sl">Passengers</div>
        </div>
        <div class="stat-card">
            <div class="sn c-green">₱{{ number_format($booking->amount_paid ?? 0, 2) }}</div>
            <div class="sl">Amount Paid</div>
        </div>
        <div class="stat-card">
            @php $bal = max(0, ($booking->total ?? 0) - ($booking->amount_paid ?? 0)); @endphp
            <div class="sn {{ $bal > 0 ? 'c-red' : 'c-green' }}">{{ $bal > 0 ? '₱'.number_format($bal,2) : '✓ Paid' }}</div>
            <div class="sl">Balance</div>
        </div>
    </div>

    {{-- CUSTOMER INFO --}}
    <div class="section">
        <div class="section-head">
            <i class="fas fa-user" style="color:#2563eb;"></i>
            <h3>Customer Information</h3>
        </div>
        <div class="section-body">
            <div class="customer-grid">
                <div class="info-cell">
                    <div class="lbl">Full Name</div>
                    <div class="val">{{ $booking->first_name }} {{ $booking->last_name }}</div>
                </div>
                <div class="info-cell">
                    <div class="lbl">Contact</div>
                    <div class="val">{{ $booking->contact_number ?? 'N/A' }}</div>
                </div>
                <div class="info-cell">
                    <div class="lbl">Email</div>
                    <div class="val" style="font-size:13px;">{{ $booking->email ?? 'N/A' }}</div>
                </div>
                <div class="info-cell">
                    <div class="lbl">Booking Status</div>
                    <div class="val">{{ ucwords(str_replace('_', ' ', $booking->status)) }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- PASSENGER LIST --}}
    <div class="section">
        <div class="section-head">
            <i class="fas fa-users" style="color:#2563eb;"></i>
            <h3>Passenger List</h3>
        </div>
        <div class="section-body" style="padding: 0;">
            @if($passengers->count() > 0)
                <table>
                    <thead>
                        <tr>
                            <th width="50">#</th>
                            <th>Full Name</th>
                            <th>Date of Birth</th>
                            <th width="60">Age</th>
                            <th width="90">Gender</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($passengers as $i => $p)
                        <tr>
                            <td class="pax-num">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</td>
                            <td class="pax-name">{{ trim($p->first_name . ' ' . ($p->middle_name ?? '') . ' ' . $p->last_name) }}</td>
                            <td>{{ $p->birthday ? date('M d, Y', strtotime($p->birthday)) : '—' }}</td>
                            <td>
                                @if($p->birthday)
                                    @php
                                        $dob = new DateTime($p->birthday);
                                        $age = $dob->diff(new DateTime())->y;
                                    @endphp
                                    {{ $age }}
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                @php $g = strtolower($p->gender ?? ''); @endphp
                                <span class="gender-badge {{ $g === 'male' ? 'g-male' : ($g === 'female' ? 'g-female' : 'g-other') }}">
                                    {{ ucfirst($p->gender ?? 'N/A') }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="no-passengers">
                    <i class="fas fa-user-slash"></i>
                    <div style="font-weight:600; margin-bottom:4px;">No passenger details recorded</div>
                    <div style="font-size:13px;">Passenger data was not submitted for this booking.</div>
                </div>
            @endif
        </div>
    </div>

    <button class="print-btn" onclick="window.print()">
        <i class="fas fa-print"></i> Print Manifest
    </button>

</div>
</body>
</html>
