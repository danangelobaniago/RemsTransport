<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#1e293b">
    <title>Tour Manifest | Driver Portal</title>
    <link rel="stylesheet" href="{{ asset('css/responsive.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Inter',sans-serif; }
        body { background:#f1f5f9; color:#1e293b; min-height:100vh; }
        .navbar { background:#1e293b; color:white; padding:0 24px; display:flex; justify-content:space-between; align-items:center; height:58px; position:sticky; top:0; z-index:100; box-shadow:0 2px 8px rgba(0,0,0,0.25); }
        .brand { font-size:16px; font-weight:700; color:#22d3ee; display:flex; align-items:center; gap:8px; }
        .back-btn { color:#94a3b8; text-decoration:none; font-size:13px; display:flex; align-items:center; gap:6px; }
        .back-btn:hover { color:white; }
        .main { max-width:960px; margin:24px auto; padding:0 16px; }
        .alert { padding:12px 18px; border-radius:8px; margin-bottom:16px; font-size:14px; display:flex; align-items:center; gap:8px; }
        .alert-success { background:#dcfce7; color:#166534; border:1px solid #bbf7d0; }
        .alert-error   { background:#fee2e2; color:#991b1b; border:1px solid #fecaca; }

        .trip-card { background:linear-gradient(135deg,#0e7490,#0891b2); color:white; border-radius:14px; padding:22px 28px; margin-bottom:20px; box-shadow:0 4px 18px rgba(8,145,178,0.3); }
        .trip-title { font-size:22px; font-weight:800; margin-bottom:6px; }
        .trip-meta { font-size:13px; opacity:0.85; display:flex; flex-wrap:wrap; gap:16px; }
        .trip-meta span { display:flex; align-items:center; gap:6px; }

        .status-flow { background:white; border-radius:14px; padding:20px 24px; margin-bottom:20px; box-shadow:0 1px 6px rgba(0,0,0,0.07); }
        .flow-title { font-size:14px; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:.5px; margin-bottom:16px; }
        .steps { display:flex; align-items:center; }
        .step { display:flex; flex-direction:column; align-items:center; flex:1; }
        .step-line { flex:1; height:3px; background:#e2e8f0; margin-bottom:28px; }
        .step-line.done { background:#0891b2; }
        .step-circle { width:44px; height:44px; border-radius:50%; border:3px solid #e2e8f0; display:flex; align-items:center; justify-content:center; font-size:18px; margin-bottom:8px; background:white; color:#94a3b8; }
        .step-circle.active { border-color:#0891b2; color:#0891b2; background:#ecfeff; }
        .step-circle.done   { border-color:#0891b2; background:#0891b2; color:white; }
        .step-label { font-size:11px; font-weight:600; color:#94a3b8; text-align:center; }
        .step-label.active { color:#0891b2; }
        .step-label.done   { color:#0891b2; }

        .action-btn { width:100%; padding:14px; border:none; border-radius:10px; font-size:15px; font-weight:700; cursor:pointer; transition:0.2s; display:flex; align-items:center; justify-content:center; gap:10px; margin-top:16px; }
        .btn-arrived    { background:#0891b2; color:white; }
        .btn-inprogress { background:#7c3aed; color:white; }
        .btn-complete   { background:#16a34a; color:white; }
        .btn-done       { background:#e2e8f0; color:#94a3b8; cursor:not-allowed; }
        .action-btn:hover:not(.btn-done) { opacity:0.88; transform:translateY(-1px); }

        .summary-row { display:grid; grid-template-columns:repeat(3,1fr); gap:12px; margin-bottom:20px; }
        .sum-card { background:white; border-radius:12px; padding:16px 18px; box-shadow:0 1px 6px rgba(0,0,0,0.07); text-align:center; }
        .sum-label { font-size:11px; color:#64748b; font-weight:600; text-transform:uppercase; margin-bottom:4px; }
        .sum-val   { font-size:22px; font-weight:800; }
        .c-blue  { color:#0891b2; }
        .c-green { color:#16a34a; }
        .c-red   { color:#dc2626; }

        .section-title { font-size:15px; font-weight:700; color:#1e293b; margin-bottom:12px; display:flex; align-items:center; gap:8px; }
        .pax-card { background:white; border-radius:12px; padding:16px 20px; margin-bottom:12px; box-shadow:0 1px 6px rgba(0,0,0,0.06); border-left:4px solid #e2e8f0; }
        .pax-card.paid-full { border-left-color:#16a34a; }
        .pax-card.not-paid  { border-left-color:#f59e0b; }
        .pax-top { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:10px; }
        .pax-name { font-size:15px; font-weight:700; }
        .pax-contact { font-size:12px; color:#64748b; margin-top:2px; display:flex; align-items:center; gap:5px; }
        .pay-badge { padding:4px 12px; border-radius:50px; font-size:11px; font-weight:700; text-transform:uppercase; }
        .badge-paid    { background:#dcfce7; color:#166534; }
        .badge-partial { background:#fef3c7; color:#92400e; }
        .badge-unpaid  { background:#fee2e2; color:#991b1b; }
        .pax-amounts { display:grid; grid-template-columns:repeat(3,1fr); gap:8px; margin-bottom:12px; }
        .amt-cell { text-align:center; }
        .amt-lbl { font-size:10px; color:#94a3b8; font-weight:600; text-transform:uppercase; }
        .amt-val { font-size:14px; font-weight:700; }
        .collect-btn { width:100%; padding:10px; border:none; border-radius:8px; background:#f59e0b; color:white; font-size:13px; font-weight:700; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px; }
        .collect-btn:hover { background:#d97706; }
        .collected-msg { text-align:center; padding:10px; background:#dcfce7; color:#166534; border-radius:8px; font-size:13px; font-weight:700; }
        @media(max-width:600px) {
            .navbar { flex-direction: column; height: auto; padding: 10px 16px; gap: 4px; }
            .main { padding: 0 12px; margin: 16px auto; }
            .trip-card { padding: 18px 20px; }
            .trip-title { font-size: 18px; }
            .summary-row { grid-template-columns:1fr 1fr; }
            .pax-amounts { grid-template-columns: 1fr 1fr; }
            .step-circle { width: 36px; height: 36px; font-size: 15px; }
            .step-label { font-size: 10px; }
        }
    </style>
</head>
<body>

<nav class="navbar">
    <a href="{{ route('driver.dashboard') }}" class="back-btn">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>
    <span class="brand"><i class="fas fa-suitcase"></i> Tour Package Manifest</span>
    <span style="font-size:13px;color:#94a3b8;">{{ date('M d, Y') }}</span>
</nav>

<div class="main">

    @if(session('success'))
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-error"><i class="fas fa-times-circle"></i> {{ session('error') }}</div>
    @endif

    {{-- Tour Info --}}
    <div class="trip-card">
        <div class="trip-title"><i class="fas fa-suitcase" style="margin-right:8px;"></i>{{ $tour->name }}</div>
        <div class="trip-meta">
            <span><i class="fas fa-map-marker-alt"></i> {{ $tour->destination }}</span>
            <span><i class="fas fa-calendar"></i> {{ date('M d', strtotime($bookedStart)) }}{{ $bookedEnd && $bookedEnd !== $bookedStart ? ' – ' . date('M d', strtotime($bookedEnd)) : '' }}</span>
            <span><i class="fas fa-clock"></i> {{ date('g:i A', strtotime($tour->pickup_time)) }}</span>
            <span><i class="fas fa-map-pin"></i> Meet: {{ $tour->pickup_point }}</span>
            <span><i class="fas fa-bus"></i> {{ $tour->van }} ({{ $tour->plate_number }})</span>
            <span><i class="fas fa-users"></i> {{ $totalPassengers }} passengers</span>
        </div>
    </div>

    {{-- Summary --}}
    <div class="summary-row">
        <div class="sum-card">
            <div class="sum-label">Passengers</div>
            <div class="sum-val c-blue">{{ $totalPassengers }}</div>
        </div>
        <div class="sum-card">
            <div class="sum-label">Cash Collected</div>
            <div class="sum-val c-green">₱{{ number_format($totalCashCollected, 2) }}</div>
        </div>
        <div class="sum-card">
            <div class="sum-label">Outstanding</div>
            <div class="sum-val c-red">₱{{ number_format($totalOutstanding, 2) }}</div>
        </div>
    </div>

    {{-- Trip Progress --}}
    <div class="status-flow">
        <div class="flow-title"><i class="fas fa-route" style="margin-right:6px;"></i>Trip Progress</div>
        @php
            $ts    = $tour->trip_status ?? 'not_started';
            $order = ['not_started'=>0,'arrived'=>1,'in_progress'=>2,'completed'=>3];
            $cur   = $order[$ts] ?? 0;
        @endphp
        <div class="steps">
            <div class="step">
                <div class="step-circle {{ $cur >= 1 ? 'done' : ($cur===0?'active':'') }}">
                    <i class="fas {{ $cur >= 1 ? 'fa-check' : 'fa-map-marker-alt' }}"></i>
                </div>
                <div class="step-label {{ $cur >= 1 ? 'done' : ($cur===0?'active':'') }}">Arrived</div>
            </div>
            <div class="step-line {{ $cur >= 1 ? 'done' : '' }}"></div>
            <div class="step">
                <div class="step-circle {{ $cur >= 2 ? 'done' : ($cur===1?'active':'') }}">
                    <i class="fas {{ $cur >= 2 ? 'fa-check' : 'fa-users' }}"></i>
                </div>
                <div class="step-label {{ $cur >= 2 ? 'done' : ($cur===1?'active':'') }}">Boarded</div>
            </div>
            <div class="step-line {{ $cur >= 2 ? 'done' : '' }}"></div>
            <div class="step">
                <div class="step-circle {{ $cur >= 3 ? 'done' : ($cur===2?'active':'') }}">
                    <i class="fas {{ $cur >= 3 ? 'fa-check' : 'fa-flag-checkered' }}"></i>
                </div>
                <div class="step-label {{ $cur >= 3 ? 'done' : ($cur===2?'active':'') }}">Completed</div>
            </div>
        </div>

        @if($ts === 'not_started')
            @if($hasApprovedBookings)
            <form method="POST" action="{{ route('driver.tour.status', $tour->id) }}">
                @csrf <input type="hidden" name="trip_status" value="arrived">
                <button type="submit" class="action-btn btn-arrived" onclick="return confirm('Confirm: You have arrived at the meetup point?')">
                    <i class="fas fa-map-marker-alt"></i> Arrived at Meetup Point
                </button>
            </form>
            @else
            <div style="background:#fef3c7;border:1px solid #fde68a;border-radius:10px;padding:14px 18px;margin-top:16px;display:flex;align-items:center;gap:10px;font-size:14px;color:#92400e;font-weight:600;">
                <i class="fas fa-clock" style="font-size:18px;"></i>
                Waiting for admin approval — trip cannot be started yet.
            </div>
            @endif
        @elseif($ts === 'arrived')
            <form method="POST" action="{{ route('driver.tour.status', $tour->id) }}">
                @csrf <input type="hidden" name="trip_status" value="in_progress">
                <button type="submit" class="action-btn btn-inprogress" onclick="return confirm('Confirm: All passengers are on board?')">
                    <i class="fas fa-users"></i> All Passengers Boarded — Start Trip
                </button>
            </form>
        @elseif($ts === 'in_progress')
            @if($allPaid)
                <form method="POST" action="{{ route('driver.tour.status', $tour->id) }}">
                    @csrf <input type="hidden" name="trip_status" value="completed">
                    <button type="submit" class="action-btn btn-complete" onclick="return confirm('Confirm: Trip completed and all payments collected?')">
                        <i class="fas fa-flag-checkered"></i> Complete Trip
                    </button>
                </form>
            @else
                <button class="action-btn btn-done" disabled>
                    <i class="fas fa-exclamation-circle"></i> Collect all payments before completing
                </button>
            @endif
        @elseif($ts === 'completed')
            <button class="action-btn btn-done" disabled><i class="fas fa-check-double"></i> Trip Completed</button>
        @endif
    </div>

    {{-- Booking / Passenger List --}}
    <div class="section-title">
        <i class="fas fa-users" style="color:#0891b2;"></i>
        Passenger List ({{ $totalPassengers }} passengers, {{ $bookings->count() }} booking(s))
    </div>

    @forelse($bookings as $b)
        @php
            $isPaid     = $b->balance <= 0;
            $badgeClass = $isPaid ? 'badge-paid' : ($b->actual_paid > 0 ? 'badge-partial' : 'badge-unpaid');
            $badgeText  = $isPaid ? 'Fully Paid' : ($b->actual_paid > 0 ? 'Partial' : 'Unpaid');
            $paxList    = $b->passengers->count() > 0 ? $b->passengers : collect([
                (object)['first_name' => ($b->first_name ?? ''), 'last_name' => ($b->last_name ?? ''), 'birthday' => null, 'gender' => null]
            ]);
        @endphp
        <div class="pax-card {{ $isPaid ? 'paid-full' : 'not-paid' }}">
            <div class="pax-top">
                <div style="flex:1;">
                    <div style="font-size:11px;color:#64748b;font-weight:600;text-transform:uppercase;margin-bottom:4px;">
                        <i class="fas fa-user-circle" style="color:#0891b2;"></i>
                        Account: {{ $b->first_name ?? '' }} {{ $b->last_name ?? '' }}
                        @if($paxList->count() > 1)
                            &nbsp;·&nbsp; {{ $paxList->count() }} seats
                        @endif
                    </div>
                    @foreach($paxList as $pax)
                    <div style="display:flex;align-items:center;gap:10px;margin-bottom:4px;flex-wrap:wrap;">
                        <div class="pax-name" style="font-size:14px;min-width:120px;">{{ trim(($pax->first_name ?? '') . ' ' . ($pax->last_name ?? '')) }}</div>
                        @if(!empty($pax->birthday))
                        @php $age = (new DateTime($pax->birthday))->diff(new DateTime())->y; @endphp
                        <div class="pax-contact">
                            <i class="fas fa-birthday-cake" style="color:#a78bfa;font-size:10px;"></i>
                            {{ date('M d, Y', strtotime($pax->birthday)) }}
                        </div>
                        <div class="pax-contact">
                            <i class="fas fa-user-clock" style="color:#64748b;font-size:10px;"></i>
                            {{ $age }} years old
                        </div>
                        @endif
                        @if(!empty($pax->gender))
                        <div class="pax-contact">
                            <i class="fas fa-venus-mars" style="color:#64748b;font-size:10px;"></i>
                            {{ ucfirst($pax->gender) }}
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
                <span class="pay-badge {{ $badgeClass }}">{{ $badgeText }}</span>
            </div>

            <div class="pax-amounts">
                <div class="amt-cell">
                    <div class="amt-lbl">Total</div>
                    <div class="amt-val">₱{{ number_format($b->total, 2) }}</div>
                </div>
                <div class="amt-cell">
                    <div class="amt-lbl">Paid</div>
                    <div class="amt-val c-green">₱{{ number_format($b->actual_paid, 2) }}</div>
                </div>
                <div class="amt-cell">
                    <div class="amt-lbl">Balance</div>
                    <div class="amt-val {{ $b->balance > 0 ? 'c-red' : 'c-green' }}">
                        {{ $b->balance <= 0 ? '✓ Paid' : '₱'.number_format($b->balance, 2) }}
                    </div>
                </div>
            </div>

            @if(!$isPaid && $ts === 'in_progress')
                <form method="POST" action="{{ route('driver.tour.collect', [$tour->id, $b->id]) }}">
                    @csrf
                    <button type="submit" class="collect-btn"
                            onclick="return confirm('Collect ₱{{ number_format($b->balance,2) }} balance?')">
                        <i class="fas fa-hand-holding-dollar"></i>
                        Collect ₱{{ number_format($b->balance, 2) }} Balance
                    </button>
                </form>
            @elseif($isPaid && $b->driver_collected > 0)
                <div class="collected-msg"><i class="fas fa-check-circle"></i> Payment Collected</div>
            @elseif($isPaid)
                <div style="text-align:center;padding:10px;background:#dbeafe;color:#1d4ed8;border-radius:8px;font-size:13px;font-weight:700;">
                    <i class="fas fa-globe"></i> Paid Online
                </div>
            @endif
        </div>
    @empty
        <div style="background:white;border-radius:12px;padding:40px;text-align:center;color:#94a3b8;">
            <i class="fas fa-users" style="font-size:32px;margin-bottom:12px;display:block;"></i>
            No passengers have booked this tour yet.
        </div>
    @endforelse

    {{-- All Settled banner --}}
    @if($allPaid && $bookings->count() > 0)
        @php $onlinePaid = $bookings->sum(fn($b) => $b->online_paid); @endphp
        <div style="background:#dcfce7;border:2px solid #bbf7d0;border-radius:14px;padding:20px 24px;margin-top:16px;text-align:center;">
            <div style="font-size:13px;color:#166534;font-weight:600;margin-bottom:4px;"><i class="fas fa-check-double"></i> All Payments Settled</div>
            @if($totalCashCollected > 0)
                <div style="font-size:28px;font-weight:800;color:#15803d;">₱{{ number_format($totalCashCollected, 2) }}</div>
                <div style="font-size:12px;color:#166534;margin-top:4px;">Cash collected by driver</div>
            @else
                <div style="font-size:15px;font-weight:700;color:#1d4ed8;margin-top:4px;"><i class="fas fa-globe"></i> All paid online — no cash to collect</div>
            @endif
        </div>
    @endif

</div>
<script src="/js/pwa.js"></script>
</body>
</html>
