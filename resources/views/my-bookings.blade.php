<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#2563eb">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <title>My Bookings | Rem's Transport</title>
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/icons/icon-192.svg">
    <link rel="stylesheet" href="{{ asset('css/my-bookings.css') }}">
    <link rel="stylesheet" href="{{ asset('css/responsive.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Custom styles for trip identification */
        .status { padding: 4px 10px; border-radius: 50px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .status-completed, .status-fully_paid, .status-approved { background: #dcfce7; color: #166534; }
        .status-paid, .status-downpayment_paid { background: #dbeafe; color: #1e40af; }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-cancelled { background: #fee2e2; color: #991b1b; }

        .empty-bookings-container {
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            min-height: 400px; text-align: center; padding: 50px 20px; background: #ffffff;
            border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }

        .empty-bookings-container i { font-size: 50px; color: #cbd5e1; margin-bottom: 20px; }
        .empty-bookings-container p { font-size: 1.2rem; color: #64748b; margin-bottom: 25px; font-weight: 500; }

        .btn-book-now {
            text-decoration: none; background: #2563eb; color: white; padding: 12px 30px;
            border-radius: 8px; font-weight: 600; transition: background 0.3s ease;
        }
        .btn-book-now:hover { background: #1d4ed8; }

        .star-rating { display: flex; flex-direction: row-reverse; justify-content: flex-end; gap: 5px; }
        .star-rating input { display: none; }
        .star-rating label { font-size: 30px; color: #ccc; cursor: pointer; transition: color 0.2s; }
        .star-rating input:checked ~ label, .star-rating label:hover, .star-rating label:hover ~ label { color: #f59e0b; }

        @keyframes slideIn { from { transform: translateY(-10px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    </style>
</head>

<body>

<div class="navbar">
    <div class="topbar">
        <a href="/" class="logo">Rem's Transport</a>
    </div>
    <nav>
        <a href="/">Home</a>
        <a href="/profile" class="active">Profile</a>
        <span class="user-name"><i class="fa fa-user-circle"></i> {{ auth()->user()->first_name }}</span>
        <form action="/logout" method="POST" class="logout-form">
            @csrf
            <button type="submit" class="logout-btn">Logout</button>
        </form>
    </nav>
</div>

<div class="booking-container" style="padding: 20px; max-width: 1200px; margin: auto;">
    <h1 class="page-title">My Bookings</h1>

    @if(session('success'))
        <div class="success-alert" style="background:#dcfce7; padding:15px; border-radius:10px; margin-bottom:20px; border: 1px solid #bbf7d0; color: #166534;">
            <i class="fa fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    @if($bookings->isEmpty())
        <div class="empty-bookings-container">
            <i class="fa fa-calendar-times"></i>
            <p>You don't have any bookings yet.</p>
            <a href="/" class="btn-book-now">
                <i class="fa fa-plus-circle"></i> Book Your First Trip
            </a>
        </div>
    @else
        <div class="booking-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 25px;">
            @foreach($bookings as $booking)
                @php
                    $rawType = strtolower(trim($booking->booking_type ?? 'joiner'));
                    $type = (str_contains($rawType, 'tour')) ? 'tour' : ((str_contains($rawType, 'private')) ? 'private' : 'joiner');
                    $urlType = ($type === 'joiner') ? 'joiner' : 'tour';
                    $statusClean = strtolower(str_replace([' ', '_'], '_', $booking->status));

                    $isFullyPaid = (isset($booking->payment_status) && $booking->payment_status === 'fully_paid');
                    $jsStatus = $isFullyPaid ? 'fully_paid' : $statusClean;
                @endphp

                <div class="booking-card" style="background: white; border-radius: 15px; padding: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #f1f5f9; display: flex; flex-direction: column; justify-content: space-between;">

                    {{-- HEADER --}}
                    <div class="card-header" style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px;">
                        <div>
                            <h3 style="font-size: 1.1rem; color: #1e293b; font-weight: 700;">{{ $booking->destination }}</h3>
                        </div>
                        <span class="status status-{{ $statusClean }}">
                            {{ ucwords(str_replace(['_', ' '], ' ', $booking->status)) }}
                        </span>
                    </div>

                    {{-- BODY --}}
                    <div class="card-body" style="color: #64748b; line-height: 1.6; margin-bottom: 15px;">
                        <p><strong>Booking ID:</strong> #{{ $booking->id }}</p>
                        <p><strong>Pickup:</strong> {{ $booking->pickup ?? 'Main Terminal' }}</p>
                        <p><strong>Date:</strong> {{ date('M d, Y', strtotime($booking->start_date ?? $booking->created_at)) }}</p>
                    </div>

                    {{-- FOOTER --}}
                    <div class="card-footer" style="display: flex; justify-content: space-between; align-items: center; padding-top: 15px; border-top: 1px solid #f1f5f9;">
                        <div class="footer-links">
                            @if($type == 'tour')
                                <a href="/receipt/{{ $booking->id }}" style="color: #7c3aed; font-weight: 700; text-decoration: none; font-size: 0.85rem;">Tour Receipt</a>
                            @elseif($type == 'joiner')
                                <a href="/joiner-receipt/{{ $booking->id }}" style="color: #2563eb; font-weight: 700; text-decoration: none; font-size: 0.85rem;">View Ticket</a>
                            @else
                                <a href="/receipt/{{ $booking->id }}" style="color: #16a34a; font-weight: 700; text-decoration: none; font-size: 0.85rem;">View Receipt</a>
                            @endif
                        </div>

                        <div class="footer-actions" style="display: flex; align-items: center; gap: 10px;">
                            {{-- CANCEL LOGIC --}}
                            @if($statusClean !== 'completed' && $statusClean !== 'cancelled')
                                <a href="javascript:void(0);"
                                   style="text-decoration: none; color: #ef4444; font-size: 0.85rem; font-weight: 600;"
                                   onclick="confirmCancellation('{{ $booking->id }}', '{{ $urlType }}', '{{ $jsStatus }}')">
                                    <i class="fa fa-times-circle"></i> Cancel
                                </a>
                            @endif

                            @if($statusClean === 'completed')
                                @php
                                    $alreadyReviewed = DB::table('feedbacks')->where('booking_id', $booking->id)->where('trip_type', $type)->exists();
                                @endphp

                                @if(!$alreadyReviewed)
                                    <button onclick="openFeedbackModal('{{ $booking->id }}', '{{ $booking->driver ?? 'Driver' }}', '{{ $type }}')"
                                            style="background: #16a34a; color: white; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 0.85rem; font-weight: 600;">
                                        <i class="fa fa-star"></i> Feedback
                                    </button>
                                @else
                                    <span style="color: #059669; font-weight: bold; font-size: 0.85rem;">Feedback Sent</span>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

{{-- MODAL --}}
<div id="feedbackModal" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.5); align-items:center; justify-content:center;">
    <div style="background:white; padding:30px; border-radius:15px; width:90%; max-width:450px;">
        <h2 style="margin-bottom:20px;">Rate Your Trip</h2>
        <form action="/submit-feedback" method="POST">
            @csrf
            <input type="hidden" name="booking_id" id="modal_booking_id">
            <input type="hidden" name="trip_type" id="modal_trip_type">
            <input type="hidden" name="driver_name" id="modal_driver_name">

            <div style="margin-bottom:15px;">
                <label>Service Rating:</label>
                <div class="star-rating">
                    <input type="radio" name="service_rating" value="5" id="s5"><label for="s5">★</label>
                    <input type="radio" name="service_rating" value="4" id="s4"><label for="s4">★</label>
                    <input type="radio" name="service_rating" value="3" id="s3"><label for="s3">★</label>
                    <input type="radio" name="service_rating" value="2" id="s2"><label for="s2">★</label>
                    <input type="radio" name="service_rating" value="1" id="s1"><label for="s1">★</label>
                </div>
            </div>

            <div style="margin-bottom:15px;">
                <label>Driver Rating:</label>
                <div class="star-rating">
                    <input type="radio" name="driver_rating" value="5" id="d5"><label for="d5">★</label>
                    <input type="radio" name="driver_rating" value="4" id="d4"><label for="d4">★</label>
                    <input type="radio" name="driver_rating" value="3" id="d3"><label for="d3">★</label>
                    <input type="radio" name="driver_rating" value="2" id="d2"><label for="d2">★</label>
                    <input type="radio" name="driver_rating" value="1" id="d1"><label for="d1">★</label>
                </div>
            </div>

            <div style="margin-bottom:20px;">
                <label>Comment:</label>
                <textarea name="comment" rows="3" style="width:100%; border:1px solid #ddd; border-radius:5px; padding:10px;"></textarea>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" onclick="closeFeedbackModal()" style="padding:10px; border-radius:5px; background:#ccc; border:none;">Cancel</button>
                <button type="submit" style="padding:10px; border-radius:5px; background:#16a34a; color:white; border:none;">Submit</button>
            </div>
        </form>
    </div>
</div>

<script>
function confirmCancellation(id, type, paymentStatus) {
    let message = "";
    if (paymentStatus === 'fully_paid') {
        message = "⚠️ WARNING: You have paid in FULL.\n\n" +
                  "According to our policy, the 20% downpayment is NON-REFUNDABLE.\n" +
                  "The remaining 80% will be returned to you. Our admin will contact you via Email or SMS " +
                  "to coordinate the refund process.\n\n" +
                  "Are you sure you want to proceed with the cancellation?";
    } else {
        message = "⚠️ WARNING: Are you sure you want to cancel?\n\n" +
                  "According to our policy, the 20% downpayment is NON-REFUNDABLE.\n" +
                  "If you pay full payment the remaining 80% will be returned to you. Our admin will contact you via Email or SMS " +
                  "to coordinate the refund process.\n\n" +
                  "Are you sure you want to proceed with the cancellation?";
    }

    if (confirm(message)) {
        // Dynamically create and submit a form to send a POST request
        let form = document.createElement('form');
        form.method = 'POST';
        form.action = `/bookings/cancel/${id}/${type}`;

        // Add CSRF Token
        let csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = '{{ csrf_token() }}';

        form.appendChild(csrf);
        document.body.appendChild(form);
        form.submit();
    }
}
function openFeedbackModal(id, driver, type) {
    document.getElementById('modal_booking_id').value = id;
    document.getElementById('modal_driver_name').value = driver;
    document.getElementById('modal_trip_type').value = type;
    document.getElementById('feedbackModal').style.display = 'flex';
}

function closeFeedbackModal() {
    document.getElementById('feedbackModal').style.display = 'none';
}

window.onclick = function(event) {
    if (event.target == document.getElementById('feedbackModal')) {
        closeFeedbackModal();
    }
}
</script>
<script src="/js/pwa.js"></script>
</body>
</html>
