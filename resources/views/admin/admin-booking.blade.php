<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#111827">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <title>Manage Bookings | Rem's Transport</title>
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/icons/icon-192.svg">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    <link rel="stylesheet" href="{{ asset('css/responsive.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .status { padding: 4px 10px; border-radius: 50px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-approved, .status-completed { background: #dcfce7; color: #166534; }
        .status-cancelled, .status-rejected { background: #fee2e2; color: #991b1b; }
        .status-downpayment_paid { background: #dbeafe; color: #1e40af; }
        .status-fully_paid { background: #d1fae5; color: #065f46; }
        /* trip-status badges */
        .ts-badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 9px; border-radius: 50px; font-size: 10px; font-weight: 700; text-transform: uppercase; margin-top: 4px; }
        .ts-at-pickup  { background: #cffafe; color: #0e7490; }
        .ts-ongoing    { background: #ffedd5; color: #c2410c; }
        .ts-remitted   { background: #d1fae5; color: #065f46; }
        .btn-remitted  { background: #059669; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: 700; }
    </style>
</head>

<body>

<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>
<div class="container">
    <div class="sidebar" id="adminSidebar">
        <h2 class="logo">Rem's Transport</h2>
        <nav>
            <a href="/admin/dashboard">Dashboard</a>
            <a href="/admin/bookings" class="active">Bookings</a>
            <a href="/admin/drivers">Drivers</a>
            <a href="/admin/vans">Vans</a>
            <a href="/admin/tours">Tours</a>
            <a href="/admin/customers">Customers</a>
            <a href="/admin/joiner-trips">Joiner Trips</a>
            <a href="/admin/pricing">Pricing</a>
            <a href="/admin/reports">Reports</a>
            <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
            <form id="logout-form" action="/logout" method="POST" style="display: none;">@csrf</form>
        </nav>
    </div>

    <div class="main">
       <div class="card table-card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding: 0 10px;">
        <div style="display:flex;align-items:center;gap:10px;">
            <button class="hamburger" onclick="toggleSidebar()" aria-label="Menu"><span></span><span></span><span></span></button>
            <h2 style="font-size: 1.5rem; font-weight: 700; color: #1e293b;">Manage Bookings</h2>
        </div>

        <!-- SEARCH FORM -->
        <form action="/admin/bookings" method="GET" style="display: flex; gap: 10px;">
            <input type="text" name="search" placeholder="Search ID, Customer, or Destination..."
                   value="{{ request('search') }}"
                   style="padding: 10px 15px; border: 1px solid #e2e8f0; border-radius: 8px; width: 300px; outline: none;">

            <button type="submit" style="background: #2563eb; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 600;">
                <i class="fa fa-search"></i> Search
            </button>

            @if(request('search'))
                <a href="/admin/bookings" style="text-decoration: none; color: #ef4444; padding: 10px; font-weight: 600;">Clear</a>
            @endif
        </form>
    </div>

        {{-- FILTER BUTTONS --}}
        <div style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom:18px; padding: 0 4px;">
            @php
                $filters = [
                    'all'       => ['label' => 'All Bookings',     'color' => '#64748b', 'count' => null],
                    'pending'   => ['label' => 'Not Yet Approved', 'color' => '#d97706', 'count' => $counts->pending_count ?? 0],
                    'ongoing'   => ['label' => 'Ongoing',          'color' => '#2563eb', 'count' => $counts->ongoing_count ?? 0],
                    'completed' => ['label' => 'Completed',        'color' => '#059669', 'count' => $counts->completed_count ?? 0],
                    'rejected'  => ['label' => 'Rejected',         'color' => '#dc2626', 'count' => $counts->rejected_count ?? 0],
                ];
            @endphp
            @foreach($filters as $key => $f)
                @php
                    $isActive = $filter === $key;
                    $bg = $isActive ? $f['color'] : '#f1f5f9';
                    $textColor = $isActive ? '#fff' : '#374151';
                    $border = $isActive ? $f['color'] : '#e2e8f0';
                @endphp
                <a href="{{ request()->fullUrlWithQuery(['filter' => $key, 'search' => request('search')]) }}"
                   style="display:inline-flex; align-items:center; gap:6px; padding:8px 16px; border-radius:8px; font-size:13px; font-weight:600; text-decoration:none;
                          background:{{ $bg }}; color:{{ $textColor }}; border:1px solid {{ $border }}; transition:0.2s;">
                    {{ $f['label'] }}
                    @if($f['count'] !== null)
                        <span style="background:{{ $isActive ? 'rgba(255,255,255,0.25)' : $f['color'] }}; color:{{ $isActive ? '#fff' : '#fff' }};
                                     padding:1px 8px; border-radius:50px; font-size:11px; font-weight:700;">
                            {{ $f['count'] }}
                        </span>
                    @endif
                </a>
            @endforeach
        </div>

        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Payment ID</th>
                        <th>Customer</th>
                        <th>Van</th>
                        <th>Pickup</th>
                        <th>Destination</th>
                        <th>Dates</th>
                        <th>Driver</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th style="min-width:90px;">Status</th>
                        <th style="min-width:160px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bookings as $booking)
                        @php
                            $statusClean = strtolower($booking->status);
                            $tripStatus  = $booking->eff_trip_status ?? 'not_started';
                            $isTour      = !empty($booking->tour_id);
                            $statusLabel = match($statusClean) {
                                'downpayment_paid' => 'Down. Paid',
                                'fully_paid'       => 'Fully Paid',
                                'completed'        => 'Completed',
                                'approved'         => 'Approved',
                                'pending'          => 'Pending',
                                'rejected'         => 'Rejected',
                                'cancelled'        => 'Cancelled',
                                default            => ucwords(str_replace('_', ' ', $statusClean)),
                            };
                        @endphp
                        <tr>
                            <td>#{{ $booking->id }}</td>
                            <td style="max-width:130px; word-break:break-all; font-size:11px;">{{ $booking->payment_id ?? 'N/A' }}</td>
                            <td>
                                <div>{{ $booking->first_name ?? 'Guest' }} {{ $booking->last_name ?? '' }}</div>
                                @if($booking->contact_number)
                                    <div style="font-size:11px; color:#6b7280;">{{ $booking->contact_number }}</div>
                                @endif
                            </td>

                            {{-- Uses the display_van alias from Controller --}}
                            <td>{{ $booking->display_van ?? 'None' }}</td>

                            <td title="{{ $booking->pickup }}">{{ Str::limit($booking->pickup, 20) }}</td>
                            <td>{{ $booking->destination }}</td>
                            <td>
                                <small>{{ date('M d', strtotime($booking->start_date)) }}</small><br>
                                <small>to</small><br>
                                <small>{{ date('M d', strtotime($booking->end_date)) }}</small>
                            </td>

                            {{-- Uses the display_driver alias from Controller --}}
                            <td>{{ $booking->display_driver ?? 'None' }}</td>

                            <td>₱{{ number_format($booking->total, 2) }}</td>

                            <td>
                                <div style="font-size:12px; line-height:1.4;">
                                    <div><strong>Paid:</strong> <span style="color:green;">₱{{ number_format($booking->amount_paid ?? 0, 2) }}</span></div>
                                    <div><strong>Balance:</strong> <span style="color:{{ ($booking->computed_balance ?? 0) > 0 ? 'red' : 'green' }};">₱{{ number_format($booking->computed_balance ?? 0, 2) }}</span></div>
                                </div>
                            </td>

                            <td>
                                {{-- Booking status badge --}}
                                <span class="status status-{{ $statusClean }}">{{ $statusLabel }}</span>

                                {{-- Trip-status sub-badge --}}
                                @if($statusClean === 'approved')
                                    @if($tripStatus === 'arrived')
                                        <br><span class="ts-badge ts-at-pickup"><i class="fas fa-map-marker-alt"></i> At Pickup</span>
                                    @elseif($tripStatus === 'in_progress')
                                        <br><span class="ts-badge ts-ongoing"><i class="fas fa-route"></i> Ongoing</span>
                                    @elseif($tripStatus === 'completed')
                                        <br><span class="ts-badge" style="background:#f3e8ff;color:#6b21a8;font-size:10px;font-weight:700;padding:3px 9px;border-radius:50px;"><i class="fas fa-check-circle"></i> Balance Collected</span>
                                    @elseif($isTour)
                                        <br><span class="ts-badge" style="background:#f1f5f9;color:#64748b;font-size:10px;font-weight:700;padding:3px 9px;border-radius:50px;"><i class="fas fa-clock"></i> Awaiting Trip Start</span>
                                    @endif
                                @endif
                            </td>

                            <td>
                                <div class="actions">
                                    @if($statusClean == 'cancelled')
                                        <span style="color: #ef4444; font-weight: bold; font-size: 11px;">VOIDED</span>

                                    @elseif($statusClean == 'completed')
                                        <span style="color: #10b981; font-weight: bold; font-size: 11px;">✓ SETTLED</span>

                                    @elseif(in_array($statusClean, ['pending', 'downpayment_paid', 'fully_paid']))
                                        <div style="display:flex; gap:5px;">
                                            <form method="POST" action="{{ route('admin.updateStatus') }}">
                                                @csrf
                                                <input type="hidden" name="booking_id" value="{{ $booking->id }}">
                                                <input type="hidden" name="status" value="approved">
                                                <button class="btn btn-approve" style="background:#10b981; color:white; border:none; padding:4px 8px; border-radius:4px; cursor:pointer;">Approve</button>
                                            </form>
                                            <button type="button" class="btn btn-reject" style="background:#ef4444; color:white; border:none; padding:4px 8px; border-radius:4px; cursor:pointer;" onclick="openRejectModal({{ $booking->id }})">Reject</button>
                                        </div>

                                    @elseif($statusClean == 'approved')
                                        @if($tripStatus === 'completed')
                                            {{-- Driver finished trip + collected balance; admin confirms remittance --}}
                                            <form method="POST" action="{{ route('admin.updateStatus') }}">
                                                @csrf
                                                <input type="hidden" name="booking_id" value="{{ $booking->id }}">
                                                <input type="hidden" name="status" value="completed">
                                                <button type="submit" class="btn-remitted"
                                                    onclick="return confirm('Confirm: Driver has remitted payment successfully?')">
                                                    <i class="fas fa-check-circle"></i> Confirm Remittance
                                                </button>
                                            </form>
                                        @elseif($tripStatus === 'in_progress')
                                            <span style="color:#c2410c; font-size:11px; font-weight:700;"><i class="fas fa-route"></i> Trip Ongoing</span>
                                        @elseif($tripStatus === 'arrived')
                                            <span style="color:#0e7490; font-size:11px; font-weight:700;"><i class="fas fa-map-marker-alt"></i> Driver at Pickup</span>
                                        @elseif($isTour)
                                            {{-- Tour: driver controls trip start, admin cannot manually complete --}}
                                            <span style="color:#64748b; font-size:11px; font-weight:700;"><i class="fas fa-clock"></i> Awaiting Trip Start</span>
                                        @else
                                            <span style="color:#64748b; font-size:11px; font-weight:700;"><i class="fas fa-clock"></i> Awaiting Trip</span>
                                        @endif

                                    @elseif($statusClean == 'rejected')
                                        @if(!empty($booking->rejection_reason))
                                            <button type="button" onclick="showReasonModal(this)" data-booking-id="{{ $booking->id }}" data-reason="{{ $booking->rejection_reason }}"
                                                style="background:#fee2e2; color:#991b1b; border:1px solid #fecaca; padding:4px 10px; border-radius:6px; font-size:11px; font-weight:700; cursor:pointer;">
                                                <i class="fas fa-circle-info"></i> Reason
                                            </button>
                                        @else
                                            <span style="color:#9ca3af; font-size:11px;">No reason provided</span>
                                        @endif

                                    @else
                                        <span class="text-muted" style="font-size:11px;">Processing</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="12" style="text-align:center; padding: 20px;">No bookings found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Reject Reason Modal --}}
<div id="rejectModal" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; background:rgba(15,23,42,0.65); align-items:center; justify-content:center; padding:20px;">
    <div style="background:white; border-radius:14px; max-width:420px; width:100%; box-shadow:0 20px 50px rgba(0,0,0,0.25); overflow:hidden;">
        <form method="POST" action="{{ route('admin.updateStatus') }}" id="rejectForm">
            @csrf
            <input type="hidden" name="booking_id" id="rejectBookingId" value="">
            <input type="hidden" name="status" value="rejected">

            <div style="padding:20px 24px; border-bottom:1px solid #e5e7eb;">
                <h3 style="margin:0 0 2px; color:#111827; font-size:16px; font-weight:700;">Reject Booking</h3>
                <p style="margin:0; color:#6b7280; font-size:13px;">Please provide a reason. This will be shown to the customer.</p>
            </div>

            <div style="padding:20px 24px;">
                <textarea name="reason" id="rejectReason" required maxlength="1000" rows="4"
                    placeholder="e.g. Van/driver unavailable on the requested date..."
                    style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:8px; font-size:13px; font-family:inherit; resize:vertical;"></textarea>
            </div>

            <div style="padding:14px 24px; border-top:1px solid #e5e7eb; background:#f9fafb; display:flex; gap:10px; justify-content:flex-end;">
                <button type="button" onclick="closeRejectModal()" style="padding:9px 16px; background:#e5e7eb; color:#374151; border:none; border-radius:8px; font-weight:600; font-size:13px; cursor:pointer;">Cancel</button>
                <button type="submit" style="padding:9px 16px; background:#ef4444; color:white; border:none; border-radius:8px; font-weight:600; font-size:13px; cursor:pointer;">Reject Booking</button>
            </div>
        </form>
    </div>
</div>

{{-- View Rejection Reason Modal --}}
<div id="reasonModal" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; background:rgba(15,23,42,0.65); align-items:center; justify-content:center; padding:20px;">
    <div style="background:white; border-radius:14px; max-width:420px; width:100%; box-shadow:0 20px 50px rgba(0,0,0,0.25); overflow:hidden;">
        <div style="padding:20px 24px; border-bottom:1px solid #e5e7eb;">
            <h3 id="reasonModalTitle" style="margin:0; color:#111827; font-size:16px; font-weight:700;">Rejection Reason</h3>
        </div>
        <div style="padding:20px 24px;">
            <p id="reasonModalText" style="margin:0; color:#374151; font-size:13px; line-height:1.6; white-space:pre-wrap;"></p>
        </div>
        <div style="padding:14px 24px; border-top:1px solid #e5e7eb; background:#f9fafb; display:flex; justify-content:flex-end;">
            <button type="button" onclick="closeReasonModal()" style="padding:9px 16px; background:#2563eb; color:white; border:none; border-radius:8px; font-weight:600; font-size:13px; cursor:pointer;">Close</button>
        </div>
    </div>
</div>

<script>
function toggleSidebar() {
    document.getElementById('adminSidebar').classList.toggle('open');
    document.getElementById('sidebarOverlay').classList.toggle('open');
}
function openRejectModal(bookingId) {
    document.getElementById('rejectBookingId').value = bookingId;
    document.getElementById('rejectReason').value = '';
    document.getElementById('rejectModal').style.display = 'flex';
}
function closeRejectModal() {
    document.getElementById('rejectModal').style.display = 'none';
}
function showReasonModal(btn) {
    document.getElementById('reasonModalTitle').innerText = 'Rejection Reason — Booking #' + btn.dataset.bookingId;
    document.getElementById('reasonModalText').innerText = btn.dataset.reason;
    document.getElementById('reasonModal').style.display = 'flex';
}
function closeReasonModal() {
    document.getElementById('reasonModal').style.display = 'none';
}
</script>
<script src="/js/pwa.js"></script>
</body>
</html>
