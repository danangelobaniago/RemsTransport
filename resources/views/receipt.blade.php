<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Official Receipt - Rem's Transport</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-blue: #2563eb;
            --success-green: #059669;
            --warning-amber: #d97706;
            --text-main: #1f2937;
            --text-muted: #6b7280;
            --border-light: #f3f4f6;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f8fafc;
            color: var(--text-main);
            line-height: 1.5;
            margin: 0;
            padding: 20px;
        }

        .receipt-container {
            max-width: 850px;
            margin: 40px auto;
            background: #ffffff;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
        }

        .receipt-container::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
            background: var(--primary-blue);
        }

        .receipt-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 40px;
        }

        .brand h2 { margin: 0; font-size: 24px; font-weight: 800; color: var(--primary-blue); text-transform: uppercase; }
        .brand p { margin: 4px 0 0; color: var(--text-muted); font-size: 14px; }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 30px;
            padding-bottom: 30px;
            border-bottom: 1px solid var(--border-light);
        }

        .info-group h4 { font-size: 12px; text-transform: uppercase; color: var(--text-muted); margin: 0 0 12px 0; }
        .info-item { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 14px; }
        .info-item span:first-child { color: var(--text-muted); }
        .info-item span:last-child { font-weight: 600; text-align: right; }

        .passenger-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .passenger-table th { text-align: left; font-size: 12px; color: var(--text-muted); padding: 12px 8px; border-bottom: 2px solid var(--border-light); text-transform: uppercase; }
        .passenger-table td { padding: 14px 8px; font-size: 14px; border-bottom: 1px solid var(--border-light); }

        .payment-recap { margin-top: 30px; display: flex; justify-content: flex-end; }
        .recap-card { width: 320px; background: #f8fafc; padding: 24px; border-radius: 12px; }
        .recap-row { display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 14px; }
        .recap-row.grand-total { margin-top: 15px; padding-top: 15px; border-top: 2px dashed #cbd5e1; font-size: 18px; font-weight: 800; color: var(--primary-blue); }

        .action-bar { display: flex; justify-content: space-between; margin-bottom: 30px; }
        .btn { cursor: pointer; padding: 10px 20px; border-radius: 8px; font-size: 14px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; border: none; }
        .btn-print { background: var(--primary-blue); color: white; }
        .btn-back { background: #f1f5f9; color: var(--text-main); }

        @media print { .action-bar { display: none; } .receipt-container { box-shadow: none; margin: 0; border: 1px solid #eee; } }
    </style>
</head>
<body>

<div class="receipt-container">
    <div class="action-bar">
        <a href="/my-bookings" class="btn btn-back"><i class="fas fa-chevron-left"></i> Back to Bookings</a>
        <button onclick="window.print()" class="btn btn-print"><i class="fas fa-print"></i> Print Receipt</button>
    </div>

    <div class="receipt-header">
        <div class="brand">
            <h2>Rem's Transport</h2>
            <p><i class="fas fa-receipt"></i> Official Trip Receipt</p>
        </div>

        <div class="status-badge-container">
            @if($totalPaid >= $booking->total)
                <div style="background: #ecfdf5; color: #059669; border: 1px solid #10b98133; padding: 8px 16px; border-radius: 99px; font-size: 13px; font-weight: 600;">
                    <i class="fas fa-check-double"></i> Fully Paid
                </div>
            @else
                <div style="background: #fffbeb; color: #d97706; border: 1px solid #f59e0b33; padding: 8px 16px; border-radius: 99px; font-size: 13px; font-weight: 600;">
                    <i class="fas fa-clock"></i> Partial Payment
                </div>
            @endif
        </div>
    </div>

    <div class="info-grid">
        <div class="info-group">
            <h4>Customer Information</h4>
            <div class="info-item"><span>Full Name</span> <span>{{ $booking->first_name ?? 'Customer' }} {{ $booking->last_name ?? '' }}</span></div>
            <div class="info-item"><span>Email Address</span> <span>{{ $booking->email }}</span></div>
            <div class="info-item"><span>Booking Reference</span> <span>#REM-{{ str_pad($booking->id, 5, '0', STR_PAD_LEFT) }}</span></div>
        </div>

        <div class="info-group">
            <h4>Trip Details</h4>
            <div class="info-item"><span>Vehicle Type</span> <span>{{ $booking->vehicle_type ?? 'N/A' }}</span></div>
            <div class="info-item"><span>Plate Number</span> <span>{{ $booking->plate_number ?? 'N/A' }}</span></div>
            <div class="info-item"><span>Assigned Driver</span> <span>{{ $booking->driver_name ?? 'TBA' }}</span></div>
            <div class="info-item"><span>Driver Contact</span> <span>{{ $booking->driver_phone ?? 'N/A' }}</span></div>
            <div class="info-item"><span>Travel Date</span> <span>{{ date('F d, Y', strtotime($booking->start_date)) }}</span></div>
            <div class="info-item"><span>Payment Reference</span> <span style="font-size:12px; word-break:break-all;">{{ $booking->payment_id ?? 'Pending / Not Captured' }}</span></div>
        </div>
    </div>

    <div style="margin-bottom: 30px;">
        <div style="background: #f8fafc; padding: 20px; border-radius: 12px; border-left: 4px solid var(--primary-blue);">
            <div style="margin-bottom: 10px;">
                <small style="color: var(--text-muted); text-transform: uppercase; font-size: 10px; font-weight: 700;">Pickup Point</small>
                <div style="font-size: 14px; font-weight: 500;">{{ $booking->pickup }}</div>
            </div>
            <div>
                <small style="color: var(--text-muted); text-transform: uppercase; font-size: 10px; font-weight: 700;">Destination</small>
                <div style="font-size: 14px; font-weight: 500;">{{ $booking->destination }}</div>
            </div>
        </div>
    </div>

    <div class="manifest-section">
        <h4 style="font-size: 16px; margin-bottom: 15px;">Passenger Manifesto</h4>
        <table class="passenger-table">
            <thead>
                <tr>
                    <th width="50">#</th>
                    <th>Full Name</th>
                    <th>Date of Birth</th>
                    <th width="100">Gender</th>
                </tr>
            </thead>
            <tbody>
                @foreach($passengers as $i => $p)
                <tr>
                    <td>{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</td>
                    <td style="font-weight: 600;">{{ trim($p->first_name . ' ' . $p->middle_name . ' ' . $p->last_name) }}</td>
                    <td>{{ $p->birthday ? date('M d, Y', strtotime($p->birthday)) : '-' }}</td>
                    <td>{{ $p->gender ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="payment-recap">
        <div class="recap-card">
            <div class="recap-row"><span>Total Fare</span> <span>₱{{ number_format($booking->total, 2) }}</span></div>
            <div class="recap-row"><span>Payment Received</span> <span style="color: var(--success-green);">₱{{ number_format($totalPaid, 2) }}</span></div>

            @if($balance <= 0)
                <div class="recap-row grand-total"><span>BALANCE</span> <span>₱0.00</span></div>
            @else
                <div class="recap-row grand-total" style="color: #ef4444;"><span>DUE UPON TRIP</span> <span>₱{{ number_format($balance, 2) }}</span></div>
            @endif
        </div>
    </div>

    <div style="margin-top: 40px; text-align: center; font-size: 12px; color: var(--text-muted); border-top: 1px solid var(--border-light); padding-top: 20px;">
        <p>Thank you for choosing Rem's Transport. For support, contact us at remstransport1@gmail.com</p>
    </div>
</div>

</body>
</html>
