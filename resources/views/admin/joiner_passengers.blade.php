<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#111827">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <title>Passenger Manifest | {{ $trip->destination }}</title>
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/icons/icon-192.svg">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    <link rel="stylesheet" href="{{ asset('css/responsive.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @media print {
            .no-print { display: none; }
            body { background: white; padding: 0; margin: 0; font-size: 11px; } /* Reduced font size for print */
            .manifest-container { max-width: 100%; width: 100%; margin: 0; padding: 0; }
            .card { box-shadow: none; border: none; padding: 10px; width: 100%; }

            /* Prevents the table from overlapping or collapsing */
            table { width: 100%; border-collapse: collapse; table-layout: auto; }
            th, td { padding: 8px 4px !important; word-wrap: break-word; vertical-align: top; }

            /* Forces columns to maintain distance */
            .balance-text { color: #000 !important; } /* Print in black to save ink/clarity */
        }
        .manifest-container { max-width: 1200px; margin: 40px auto; padding: 0 20px; }
        .balance-text { color: #dc2626; font-weight: 700; }
    </style>
</head>
<body style="background: #f4f7fe;">

<div class="manifest-container">
    <div class="header-flex" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <div>
            <a href="/admin/joiner-trips" class="no-print" style="text-decoration: none; color: #2563eb; font-weight: 600;">
                <i class="fas fa-arrow-left"></i> Back to Trips
            </a>
            <h1 style="margin-top: 10px;">Passenger Manifest</h1>
        </div>
        <button onclick="window.print()" class="no-print" style="background: #1e293b; color: white; padding: 12px 25px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">
            <i class="fas fa-print"></i> Print Manifest
        </button>
    </div>

    <div class="card" style="background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
        <div style="margin-bottom: 30px; border-left: 5px solid #2563eb; padding-left: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <h2 style="font-size: 24px; margin: 0 0 5px 0;">{{ $trip->destination }}</h2>
                    <p style="color: #64748b; font-size: 16px; margin: 0;">
                        <i class="fas fa-calendar"></i> {{ date('M d, Y', strtotime($trip->trip_date)) }} &nbsp; | &nbsp;
                        <i class="fas fa-bus"></i> {{ $trip->van }} ({{ $trip->plate_number }}) &nbsp; | &nbsp;
                        <i class="fas fa-user-tie"></i> Driver: {{ $trip->driver_name }}
                    </p>
                </div>
                <div style="text-align: right;">
                    <span style="color: #64748b; font-size: 0.8rem; text-transform: uppercase; font-weight: 700;">Rate per Seat</span>
                    <h3 style="color: #1e293b; margin: 0;">₱{{ number_format($trip->price_per_seat, 2) }}</h3>
                </div>
            </div>
        </div>

        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="text-align: left; background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                    <th style="padding: 15px;">Account</th>
                    <th style="padding: 15px;">Passengers</th>
                    <th style="padding: 15px;">Contact(s)</th>
                    <th style="padding: 15px;">Birthday</th>
                    <th style="padding: 15px;">Age</th>
                    <th style="padding: 15px;">Payment Status</th>
                    <th style="padding: 15px;">Paid / Balance</th>
                    <th style="padding: 15px;">Booked At</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bookings as $b)
                @php
                    $paxList = $b->passengers->count() > 0 ? $b->passengers : collect([
                        (object)['name' => $b->passenger_name, 'contact' => $b->passenger_contact]
                    ]);
                    $status = $b->status;
                    $statusText = match($status) {
                        'fully_paid'      => 'FULLY PAID',
                        'downpayment_paid' => 'DOWNPAYMENT PAID',
                        'paid'            => 'DOWNPAYMENT PAID',
                        'completed'       => 'COMPLETED',
                        'pending'         => 'PENDING',
                        default           => strtoupper(str_replace('_', ' ', $status)),
                    };
                    $statusColor = in_array($status, ['fully_paid','completed']) ? '#16a34a'
                        : ($status === 'downpayment_paid' || $status === 'paid' ? '#2563eb'
                        : ($status === 'pending' ? '#b45309' : '#ef4444'));
                    $bgColor = in_array($status, ['fully_paid','completed']) ? '#dcfce7'
                        : ($status === 'downpayment_paid' || $status === 'paid' ? '#dbeafe'
                        : ($status === 'pending' ? '#fef3c7' : '#fee2e2'));
                @endphp
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 15px; vertical-align: top;">
                        <strong>{{ $b->first_name }} {{ $b->last_name }}</strong><br>
                        <small style="color: #64748b; word-break: break-all;">{{ $b->email }}</small>
                        @if($paxList->count() > 1)
                            <br><small style="color:#7c3aed;font-weight:700;">{{ $paxList->count() }} seats</small>
                        @endif
                    </td>
                    <td style="padding: 15px; vertical-align: top;">
                        @foreach($paxList as $pax)
                            <div>{{ $pax->name }}</div>
                        @endforeach
                    </td>
                    <td style="padding: 15px; vertical-align: top;">
                        @foreach($paxList as $pax)
                            <div>{{ $pax->contact }}</div>
                        @endforeach
                    </td>
                    <td style="padding: 15px; vertical-align: top;">
                        @foreach($paxList as $pax)
                            <div>
                                @if(!empty($pax->birthday))
                                    {{ date('M d, Y', strtotime($pax->birthday)) }}
                                @else
                                    <span style="color:#94a3b8;">—</span>
                                @endif
                            </div>
                        @endforeach
                    </td>
                    <td style="padding: 15px; vertical-align: top;">
                        @foreach($paxList as $pax)
                            <div>
                                @if(!empty($pax->birthday))
                                    @php $age = (new DateTime($pax->birthday))->diff(new DateTime())->y; @endphp
                                    {{ $age }} years old
                                @else
                                    <span style="color:#94a3b8;">—</span>
                                @endif
                            </div>
                        @endforeach
                    </td>
                    <td style="padding: 15px; vertical-align: top;">
                        <span style="padding: 6px 12px; border-radius: 50px; font-size: 9px; font-weight: 800; background: {{ $bgColor }}; color: {{ $statusColor }}; white-space: nowrap;">
                            {{ $statusText }}
                        </span>
                    </td>
                    <td style="padding: 15px; vertical-align: top;">
                        @if($b->actual_paid > 0)
                            <div style="color:#16a34a;font-weight:700;white-space:nowrap;">Paid: ₱{{ number_format($b->actual_paid, 2) }}</div>
                        @endif
                        @if($b->balance > 0)
                            <div class="balance-text" style="white-space:nowrap;">Balance: ₱{{ number_format($b->balance, 2) }}</div>
                        @else
                            <div style="color:#16a34a;font-weight:700;">✓ Fully Paid</div>
                        @endif
                        <div style="color:#94a3b8;font-size:11px;margin-top:2px;">Total: ₱{{ number_format($b->total_price, 2) }}</div>
                    </td>
                    <td style="padding: 15px; color: #64748b; font-size: 0.85rem; vertical-align: top; white-space: nowrap;">
                        {{ date('M d, Y', strtotime($b->created_at)) }}<br>
                        {{ date('g:i A', strtotime($b->created_at)) }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align: center; padding: 50px; color: #94a3b8;">
                        No passengers have reserved seats for this trip yet.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
