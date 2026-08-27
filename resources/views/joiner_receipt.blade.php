<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trip Ticket - #{{ $booking->id }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2563eb;
            --dark: #0f172a;
            --gray: #64748b;
            --success: #10b981;
            --danger: #dc2626;
            --bg: #f1f5f9;
        }

        body {
            background-color: var(--bg);
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }

        .back-btn {
            position: fixed;
            top: 25px;
            left: 25px;
            background: white;
            color: var(--dark);
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            text-decoration: none;
            transition: all 0.2s ease;
            z-index: 1000;
        }

        .back-btn:hover {
            background: var(--dark);
            color: white;
            transform: translateX(-3px);
        }

        .ticket-container {
            width: 100%;
            max-width: 420px;
            filter: drop-shadow(0 20px 25px rgba(0, 0, 0, 0.1));
        }

        .ticket-header {
            background-color: var(--primary);
            color: white;
            padding: 25px;
            border-radius: 24px 24px 0 0;
            text-align: center;
        }

        /* Change header color if fully paid */
        .ticket-header.fully-paid {
            background-color: var(--success);
        }

        .status-badge {
            background: rgba(255, 255, 255, 0.2);
            padding: 6px 16px;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            display: inline-block;
            margin-top: 10px;
            border: 1px solid rgba(255,255,255,0.3);
        }

        .ticket-divider {
            height: 30px;
            background-color: white;
            position: relative;
            display: flex;
            align-items: center;
            overflow: hidden;
        }

        .ticket-divider::before, .ticket-divider::after {
            content: '';
            width: 30px;
            height: 30px;
            background-color: var(--bg);
            border-radius: 50%;
            position: absolute;
            top: 0;
        }

        .ticket-divider::before { left: -15px; }
        .ticket-divider::after { right: -15px; }

        .ticket-divider .dashed-line {
            width: 100%;
            border-top: 2px dashed #e2e8f0;
            margin: 0 30px;
        }

        .ticket-body {
            background-color: white;
            padding: 30px;
            border-radius: 0 0 24px 24px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 25px;
        }

        .label {
            color: var(--gray);
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
            display: block;
        }

        .value {
            color: var(--dark);
            font-weight: 700;
            font-size: 0.95rem;
        }

        .driver-section {
            display: flex;
            align-items: center;
            background: #f8fafc;
            padding: 15px;
            border-radius: 12px;
            margin: 20px 0;
            border: 1px solid #f1f5f9;
        }

        .driver-icon {
            width: 40px;
            height: 40px;
            background: #e2e8f0;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: var(--gray);
        }

        .payment-box {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #f1f5f9;
        }

        .price-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }

        .balance-row {
            margin-top: 15px;
            padding: 15px;
            background: #fff5f5;
            border-radius: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .success-box {
            margin-top: 15px;
            padding: 15px;
            background: #f0fff4;
            border-radius: 12px;
            text-align: center;
            color: var(--success);
            font-weight: 700;
            border: 1px solid #c6f6d5;
        }

        .balance-amount {
            color: var(--danger);
            font-size: 1.4rem;
            font-weight: 800;
        }

        .btn-action {
            width: 100%;
            padding: 16px;
            background: var(--dark);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 700;
            cursor: pointer;
            margin-top: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: 0.2s;
        }

        @media print {
            .no-print { display: none; }
            body { background: white; padding: 0; }
            .ticket-container { filter: none; }
        }
    </style>
</head>
<body>

<a href="/my-bookings" class="back-btn no-print" title="Back to My Bookings">
    <i class="fas fa-chevron-left"></i>
</a>

<div class="ticket-container">
    {{-- Logic: Header color changes based on status --}}
    <div class="ticket-header {{ $booking->status === 'fully_paid' ? 'fully-paid' : '' }}">
        @if($booking->status === 'fully_paid')
            <h1 style="margin:0; font-size: 1.4rem;">TRIP <span style="color: #ffffff;">FULLY PAID</span></h1>
        @else
            <h1 style="margin:0; font-size: 1.4rem;">DOWNPAYMENT <span style="color: #60a5fa;">PAID</span></h1>
        @endif
        <div class="status-badge">Reservation Confirmed</div>
    </div>

    <div class="ticket-divider">
        <div class="dashed-line"></div>
    </div>

    <div class="ticket-body">
        <div class="info-grid">
            <div style="grid-column: span 1;">
                <span class="label">Passenger</span>
                <div class="value">{{ $booking->passenger_name }}</div>
            </div>
            <div style="grid-column: span 1;">
                <span class="label">Destination</span>
                <div class="value">{{ $booking->destination }}</div>
            </div>
            <div>
                <span class="label">Travel Date</span>
                <div class="value">{{ date('M d, Y', strtotime($booking->trip_date)) }}</div>
            </div>
            <div>
                <span class="label">Meetup Point</span>
                <div class="value">{{ $booking->meetup_point }}</div>
                @if(!empty($booking->meetup_time))
                    <div style="color:#2563eb; font-size:0.8rem; font-weight:700; margin-top:4px;">
                        <i class="fas fa-clock"></i> {{ date('g:i A', strtotime($booking->meetup_time)) }}
                    </div>
                @endif
            </div>
            <div>
                <span class="label">Van Model</span>
                <div class="value">{{ $booking->van }}</div>
            </div>
            <div>
                <span class="label">Plate Number</span>
                <div class="value">{{ $booking->plate_number }}</div>
            </div>
        </div>

        <div class="driver-section">
            <div class="driver-icon">
                <i class="fas fa-user-tie"></i>
            </div>
            <div>
                <span class="label" style="margin:0;">Driver Name</span>
                <div class="value" style="font-size: 1rem;">{{ $booking->driver_name ?? 'To be assigned' }}</div>
                @if(!empty($booking->driver_phone))
                    <div style="color:#64748b; font-size:0.8rem; margin-top:3px;"><i class="fas fa-phone"></i> {{ $booking->driver_phone }}</div>
                @endif
            </div>
        </div>

        <div class="payment-box">
            <div class="price-row">
                <span class="label">Total Trip Price</span>
                <span class="value">₱{{ number_format($booking->total_price, 2) }}</span>
            </div>
            <div class="price-row">
                <span class="label">Amount Paid Online</span>
                <span class="value" style="color: var(--success);">₱{{ number_format($totalPaid, 2) }}</span>
            </div>

            {{-- Logic: Show balance row only if balance exists --}}
            @if($balance > 0)
                <div class="balance-row">
                    <div>
                        <span class="label" style="color: var(--danger); margin:0;">Balance Owed (Cash)</span>
                        <div style="font-size: 0.7rem; color: var(--gray);">Pay upon boarding</div>
                    </div>
                    <div class="balance-amount">₱{{ number_format($balance, 2) }}</div>
                </div>
            @else
                <div class="success-box">
                    <i class="fas fa-check-circle me-1"></i> TRIP FULLY PAID - NO BALANCE
                </div>
            @endif
        </div>

        <button onclick="window.print()" class="btn-action no-print">
            <i class="fas fa-file-pdf"></i> DOWNLOAD TICKET (PDF)
        </button>
    </div>
</div>

</body>
</html>
