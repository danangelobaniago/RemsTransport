<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Booking | Rem's Transport</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        body { background: linear-gradient(135px, #f8fafc 0%, #e2e8f0 100%); min-height: 100vh; font-family: 'Inter', sans-serif; }
        .form-container { max-width: 1200px; margin: 40px auto; }
        .booking-card { background: #ffffff; border: none; border-radius: 24px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); overflow: hidden; }
        .card-header-custom { background: #2563eb; padding: 30px; text-align: center; color: white; }
        .trip-badge { background: rgba(255, 255, 255, 0.2); padding: 6px 16px; border-radius: 50px; font-size: 0.85rem; backdrop-filter: blur(4px); }
        .section-label { text-transform: uppercase; color: #64748b; font-weight: 700; font-size: 0.75rem; letter-spacing: 0.05em; margin-bottom: 1.5rem; display: block; }
        .passenger-entry { background: #ffffff; border-radius: 15px; border: 1px solid #e2e8f0; transition: all 0.3s ease; height: 100%; }
        .passenger-entry:hover { border-color: #2563eb; transform: translateY(-3px); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>

<div class="container">
    <div class="form-container">
        {{-- IMPORTANT: Error visibility --}}
        @if ($errors->any() || session('error'))
            <div class="alert alert-danger shadow-sm mb-4" style="border-radius: 12px;">
                <h6 class="fw-bold"><i class="fas fa-exclamation-circle me-2"></i> Booking Error:</h6>
                <ul class="mb-0 small">
                    @if(session('error')) <li>{{ session('error') }}</li> @endif
                    @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                </ul>
            </div>
        @endif

        <a href="{{ url()->previous() }}" class="text-decoration-none text-muted mb-3 d-inline-block small">
            <i class="fas fa-chevron-left me-1"></i> Return to Details
        </a>

        <form action="/joiner/book/{{ $trip->id }}" method="POST">
            @csrf
            <input type="hidden" name="seats_count" value="{{ $seats }}">

            <div class="booking-card">
                <div class="card-header-custom">
                    <h3 class="fw-bold mb-2">Final Step</h3>
                    <div class="trip-badge">
                        <i class="fas fa-map-marker-alt me-2"></i>{{ $trip->destination }} &bull; {{ \Carbon\Carbon::parse($trip->trip_date)->format('M d, Y') }}
                        @if(!empty($trip->meetup_time))
                            &bull; <i class="fas fa-clock me-1"></i>{{ date('g:i A', strtotime($trip->meetup_time)) }}
                        @endif
                    </div>
                </div>

                <div class="p-4 p-md-5">
                    <span class="section-label">Passenger Information ({{ $seats }} Seats)</span>

                    <div class="row g-4 mb-5 justify-content-center">
                        @for ($i = 0; $i < $seats; $i++)
                            <div class="col-12 col-md-6 col-lg-4" style="max-width: 400px;">
                                <div class="passenger-entry p-4 shadow-sm border h-100">
                                    <h6 class="fw-bold text-primary mb-3"><i class="fas fa-user-circle me-2"></i>Passenger #{{ $i + 1 }}</h6>
                                    <div class="mb-3">
                                        <label class="small fw-bold text-muted">Full Name</label>
                                        <input type="text" name="passenger_name[]" class="form-control bg-light" placeholder="Enter name" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="small fw-bold text-muted">Contact Number</label>
                                        <input type="tel" name="passenger_contact[]" class="form-control bg-light" placeholder="09XXXXXXXXX" maxlength="11" oninput="validatePHNumber(this)" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="small fw-bold text-muted">Birthday</label>
                                        <input type="date" name="passenger_birthday[]" class="form-control bg-light" max="{{ date('Y-m-d') }}" required oninput="calcAge(this)">
                                    </div>
                                    <div class="mb-3">
                                        <label class="small fw-bold text-muted">Age</label>
                                        <input type="number" class="age-field form-control" placeholder="—" readonly style="background:#f3f4f6;cursor:default;">
                                    </div>
                                    <div class="mb-0">
                                        <label class="small fw-bold text-muted">Gender</label>
                                        <select name="passenger_gender[]" class="form-select bg-light" required>
                                            <option value="Male">Male</option>
                                            <option value="Female">Female</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        @endfor
                    </div>

                    <div class="row align-items-stretch g-4 mt-2">
                        <div class="col-lg-7">
                            <div class="p-4 rounded-4 h-100 d-flex flex-column justify-content-center" style="background: #fff7ed; border: 1px solid #ffedd5;">
                                <h6 class="fw-bold text-warning-emphasis mb-2"><i class="fas fa-shield-alt me-1"></i> Terms & Data Privacy</h6>
                                <p class="small text-muted mb-3">Please review our terms regarding cancellations and data handling.</p>
                                <div class="form-check small text-start">
                                    <input class="form-check-input" type="checkbox" id="agreeCheck" required>
                                    <label class="form-check-label text-muted fw-bold" for="agreeCheck">
                                        I agree to the <a href="javascript:void(0)" onclick="openTermsModal()" style="color: #2563eb; text-decoration: underline;">Terms and Data Privacy Act</a>.
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-5">
                            @php
                                $pricePerSeat = $trip->price_per_seat;
                                $totalPrice = $pricePerSeat * $seats;
                                $downpaymentAmount = $totalPrice * 0.20;
                            @endphp
                            <div class="mb-3 p-3 rounded-4 border shadow-sm" style="background: #f8fafc;">
                                <label class="small fw-bold text-muted mb-2 d-block">CHOOSE PAYMENT:</label>
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_option" id="payDown" value="downpayment" checked onchange="updateJoinerPaymentDisplay()">
                                        <label class="form-check-label small fw-bold" for="payDown">Downpayment (20%)</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_option" id="payFull" value="full" onchange="updateJoinerPaymentDisplay()">
                                        <label class="form-check-label small fw-bold" for="payFull">Full Payment</label>
                                    </div>
                                </div>
                            </div>
                            <div class="p-4 bg-light rounded-4 mb-3 border">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted small" id="payment-label">Reservation Fee (20% x {{ $seats }}):</span>
                                    <span class="fw-bold text-dark" id="payment-amount">₱{{ number_format($downpaymentAmount, 2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted small">Total Package Price:</span>
                                    <span class="fs-4 fw-bold text-primary">₱{{ number_format($totalPrice, 2) }}</span>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 shadow py-3 fw-bold" id="confirm-btn" style="border-radius: 12px;">
                                PAY DOWNPAYMENT <i class="fas fa-lock ms-2"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- MODAL AND SCRIPTS SAME AS BEFORE --}}
<div id="termsModal" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.6); align-items:center; justify-content:center; padding: 20px;">
    <div style="background:white; padding:30px; border-radius:15px; max-width:700px; width:100%; max-height:80vh; overflow-y:auto; box-shadow: 0 10px 25px rgba(0,0,0,0.2); animation: fadeIn 0.3s ease;">
        <h2 style="margin-bottom:20px; color: #1e293b; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px;">Terms and Conditions</h2>
        <div style="font-size: 14px; color: #475569; line-height: 1.6;">
            <p>1. Reservation Policy: A non-refundable 20% downpayment is required.</p>
            <p>2. Data Privacy: We collect data per Data Privacy Act of 2012.</p>
        </div>
        <button type="button" onclick="closeTermsModal()" class="btn btn-primary w-100 mt-4 py-2 fw-bold">Close</button>
    </div>
</div>

<script>
    function calcAge(dateInput) {
        const dob = new Date(dateInput.value);
        const box = dateInput.closest('.passenger-entry');
        const ageField = box.querySelector('.age-field');
        if (!dateInput.value || isNaN(dob)) { ageField.value = ''; return; }
        const today = new Date();
        let age = today.getFullYear() - dob.getFullYear();
        const m = today.getMonth() - dob.getMonth();
        if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) age--;
        ageField.value = age >= 0 ? age : '';
    }
    function openTermsModal() { document.getElementById('termsModal').style.display = 'flex'; }
    function closeTermsModal() { document.getElementById('termsModal').style.display = 'none'; }
    function validatePHNumber(input) {
        let val = input.value.replace(/[^0-9]/g, '');
        if (val.length > 0 && val[0] !== '0') val = '';
        if (val.length > 1 && val[1] !== '9') val = '0';
        input.value = val;
    }
    function updateJoinerPaymentDisplay() {
        const isFull = document.getElementById('payFull').checked;
        const total = {{ $totalPrice }};
        const down = total * 0.20;
        document.getElementById('payment-label').innerText = isFull ? "Full Payment Amount:" : "Reservation Fee (20% x {{ $seats }}):";
        document.getElementById('payment-amount').innerText = "₱" + (isFull ? total : down).toLocaleString(undefined, {minimumFractionDigits: 2});
        document.getElementById('confirm-btn').innerHTML = isFull ? 'PAY FULL AMOUNT <i class="fas fa-lock ms-2"></i>' : 'PAY DOWNPAYMENT <i class="fas fa-lock ms-2"></i>';
    }
</script>
</body>
</html>
