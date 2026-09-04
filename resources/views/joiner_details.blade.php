<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Joiner Trip Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<style>
    :root {
        --primary-blue: #2563eb;
        --border-gray: #e5e7eb;
    }

    .trip-container {
        max-width: 800px;
        margin: 40px auto;
        background: #fff;
        padding: 40px;
        border-radius: 16px;
        border: 1px solid var(--border-gray);
        box-shadow: 0 10px 25px rgba(0,0,0,0.05);
    }

    .trip-header {
        border-bottom: 1px solid var(--border-gray);
        margin-bottom: 30px;
        padding-bottom: 20px;
    }

    .feature-icon {
        width: 40px;
        height: 40px;
        background: #f3f4f6;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--primary-blue);
        margin-right: 1rem;
    }

    .meetup-highlight {
        background-color: #f0f7ff;
        border: 1px solid #cfe2ff;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 30px;
    }

    .booking-action-zone {
        background-color: #f9fafb;
        border-radius: 12px;
        padding: 25px;
        margin-top: 40px;
        border: 1px solid var(--border-gray);
    }

    @media (max-width: 576px) {
        .trip-container { margin: 16px auto; padding: 20px; border-radius: 12px; }
        .meetup-highlight, .booking-action-zone { padding: 16px; }
        h1.display-6 { font-size: 1.5rem; }
    }
</style>

<div class="container">
    <div class="trip-container">

        <div class="trip-header">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb sm">
                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                    <li class="breadcrumb-item"><a href="/#joiners">Joiner Trips</a></li>
                    <li class="breadcrumb-item active text-muted">{{ $trip->destination }}</li>
                </ol>
            </nav>

            @if(!empty($trip->image))
                <img src="{{ \Storage::disk('public')->url($trip->image) }}" alt="{{ $trip->name ?? $trip->destination }}"
                     style="width:100%; max-height:320px; object-fit:cover; border-radius:14px; margin-top:15px;">
            @endif

            <h1 class="fw-bold text-dark display-6 mt-2">{{ $trip->name ?? $trip->destination }}</h1>

            <div class="d-flex align-items-center gap-3 text-muted mt-3">
                <span><i class="fas fa-map-marker-alt text-danger"></i> Philippines</span>
                <span class="badge bg-light text-primary border">Van Type: {{ $trip->van }}</span>
            </div>
        </div>

        <h4 class="fw-bold mb-4">Trip Overview</h4>
        <div class="row g-4 mb-5">
            <div class="col-md-6">
                <div class="d-flex align-items-center">
                    <div class="feature-icon"><i class="fas fa-clock"></i></div>
                    <div>
                        <small class="text-muted d-block">Duration</small>
                        <strong>Day Tour Adventure</strong>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex align-items-center">
                    <div class="feature-icon"><i class="fas fa-calendar-alt"></i></div>
                    <div>
                        <small class="text-muted d-block">Travel Date</small>
                        <strong>{{ \Carbon\Carbon::parse($trip->trip_date)->format('F d, Y') }}</strong>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex align-items-center">
                    <div class="feature-icon"><i class="fas fa-user-friends"></i></div>
                    <div>
                        <small class="text-muted d-block">Group Size</small>
                        <strong>Up to {{ $trip->total_seats }} slots</strong>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex align-items-center">
                    <div class="feature-icon"><i class="fas fa-chair"></i></div>
                    <div>
                        <small class="text-muted d-block">Availability</small>
                        <strong class="text-success">{{ $trip->available_seats }} seats remaining</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="meetup-highlight">
            <div class="d-flex align-items-start">
                <div class="feature-icon" style="background: #fff; border: 1px solid #cfe2ff;">
                    <i class="fas fa-map-marked-alt"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-1 text-primary">Meetup Point</h5>
                    <p class="mb-0 text-dark fw-semibold fs-5">{{ $trip->meetup_point ?? 'To Be Announced' }}</p>
                    @if(!empty($trip->meetup_time))
                        <p class="mb-0 mt-1" style="color:#2563eb; font-size:14px; font-weight:600;">
                            <i class="fas fa-clock"></i> {{ date('g:i A', strtotime($trip->meetup_time)) }}
                        </p>
                    @endif
                </div>
            </div>
        </div>

        <h4 class="fw-bold mb-3">About this trip</h4>
        @if(!empty($trip->description))
            <p class="text-muted lead fs-6" style="white-space:pre-line;">{{ $trip->description }}</p>
        @else
            <p class="text-muted lead fs-6">Experience the best of {{ $trip->destination }}. This joiner trip guarantees a comfortable seat in a {{ $trip->van }} van, professional service, and an unforgettable sightseeing experience.</p>
        @endif

        <hr class="my-5">

<div class="booking-action-zone">
    <div class="row align-items-center">
        <div class="col-md-5 mb-3 mb-md-0">
            @php
                $pricePerSeat = $trip->price_per_seat;
                $downpaymentRate = 0.20;
            @endphp

            <small class="text-muted d-block">Reservation Fee (20% per seat)</small>
            {{-- This ID "display-downpayment" will be updated by JS --}}
            <h2 class="mb-0 fw-bold text-primary" id="display-downpayment">₱{{ number_format($pricePerSeat * $downpaymentRate, 2) }}</h2>
            <small class="text-muted">Full price: <span id="display-fullprice">₱{{ number_format($pricePerSeat, 2) }}</span></small>
        </div>

        <div class="col-md-7">
            @auth
                @php
                    $alreadyJoined = \DB::table('joiner_bookings')
                        ->where('user_id', auth()->id())
                        ->where('joiner_trip_id', $trip->id)
                        ->exists();

                    // Determine the max seats allowed (The lesser of 5 or actual availability)
                    $maxAllowed = min(5, $trip->available_seats);
                @endphp

                @if($alreadyJoined)
                    <button class="btn btn-success w-100 btn-lg fw-bold" disabled>
                        <i class="fas fa-check me-2"></i> Seat Reserved
                    </button>
                @elseif($trip->available_seats > 0)
                    <form action="{{ route('joiner.passengerForm', $trip->id) }}" method="GET">
                        <div class="d-flex gap-2">
                            <div style="width: 120px;">
                                <small class="text-muted d-block mb-1">Seats</small>
                                <input type="number" name="seats" id="seat-qty"
                                    class="form-control form-control-lg fw-bold text-center"
                                    value="1" min="1" max="{{ $maxAllowed }}"
                                    oninput="calculateJoinerPrice(this.value)">
                            </div>
                            <button type="submit" class="btn btn-primary flex-grow-1 btn-lg fw-bold">
                                Reserve Now
                            </button>
                        </div>
                        <small class="text-muted d-block mt-2">
                            <i class="fas fa-info-circle"></i> Max 5 seats per account.
                        </small>
                    </form>
                @else
                    <button class="btn btn-secondary w-100 btn-lg fw-bold" disabled>Trip Fully Booked</button>
                @endif
            @else
                <a href="/login" class="btn btn-outline-primary w-100 btn-lg fw-bold">Login to Reserve Seat</a>
            @endauth
        </div>
    </div>
</div>

    </div>
</div>
<script>
    function calculateJoinerPrice(qty) {
        const pricePerSeat = {{ $trip->price_per_seat }};
        const downpaymentRate = 0.20;
        const maxSeats = {{ min(5, $trip->available_seats) }};

        // Clamp values between 1 and the allowed maximum
        if (qty > maxSeats) {
            qty = maxSeats;
            document.getElementById('seat-qty').value = maxSeats;
        }
        if (qty < 1) {
            qty = 1;
            document.getElementById('seat-qty').value = 1;
        }

        const totalFullPrice = pricePerSeat * qty;
        const totalDownpayment = totalFullPrice * downpaymentRate;

        // Update displays
        document.getElementById('display-downpayment').innerText = '₱' + totalDownpayment.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('display-fullprice').innerText = '₱' + totalFullPrice.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }
</script>

</body>
</html>
