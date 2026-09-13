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
        .form-container { margin: 40px auto; transition: max-width 0.2s ease; }
        .booking-card { background: #ffffff; border: none; border-radius: 24px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); overflow: hidden; }
        .card-header-custom { background: #2563eb; padding: 30px; text-align: center; color: white; }
        .trip-badge { background: rgba(255, 255, 255, 0.2); padding: 6px 16px; border-radius: 50px; font-size: 0.85rem; backdrop-filter: blur(4px); }
        .section-label { text-transform: uppercase; color: #64748b; font-weight: 700; font-size: 0.75rem; letter-spacing: 0.05em; margin-bottom: 1.5rem; display: block; }
        .passenger-entry { background: #ffffff; border-radius: 15px; border: 1px solid #e2e8f0; transition: all 0.3s ease; height: 100%; }
        .passenger-entry:hover { border-color: #2563eb; transform: translateY(-3px); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }

        /* Payment Option (matches the van/tour booking passenger forms) */
        .payment-title { margin: 0 0 15px; font-size: 15px; font-weight: 700; color: #1e293b; text-transform: uppercase; letter-spacing: 0.05em; }
        .payment-selection { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px 20px 12px; margin-bottom: 14px; }
        .payment-selection .option-label { display: flex; align-items: center; cursor: pointer; color: #1e293b; font-size: 13.5px; font-weight: 600; margin-bottom: 10px; }
        .payment-selection .option-label input { width: 16px; height: 16px; margin-right: 8px; flex-shrink: 0; }
        .installment-note {
            margin-top: 12px; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px;
            padding: 12px 14px; font-size: 12px; color: #1e40af; line-height: 1.55;
        }
        .joiner-amount-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; background: #111827; color: white; padding: 18px; border-radius: 12px; margin-bottom: 16px; }
        .joiner-amount-row .amount-item label { font-size: 11px; color: #9ca3af; display: block; }
        .joiner-amount-row .amount-item p { margin: 4px 0 0; font-size: 1.05rem; font-weight: 700; color: white; }
        .joiner-amount-row .green { color: #4ade80; }
        .joiner-amount-row .red { color: #f87171; }
        @media (max-width: 480px) { .joiner-amount-row { grid-template-columns: 1fr; text-align: center; } }
    </style>
</head>
<body>

@php
    // Fewer passengers = a narrower card, so a 1-seat booking isn't a tiny
    // form lost in a huge blank page.
    $formMaxWidth = match (true) {
        $seats <= 1 => '640px',
        $seats == 2 => '880px',
        default     => '1200px',
    };
@endphp
<div class="container">
    <div class="form-container" style="max-width: {{ $formMaxWidth }};">
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

        <form action="/joiner/book/{{ $trip->id }}" method="POST" onsubmit="return validateJoinerForm();">
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
                        <div class="{{ $seats <= 2 ? 'col-12' : 'col-lg-7' }}">
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

                        <div class="{{ $seats <= 2 ? 'col-12' : 'col-lg-5' }}">
                            @php
                                $pricePerSeat = $trip->price_per_seat;
                                $totalPrice = $pricePerSeat * $seats;
                                $downpaymentAmount = $totalPrice * 0.20;
                            @endphp

                            <h2 class="payment-title"><i class="fas fa-credit-card me-2"></i>Payment Option</h2>

                            <div class="payment-selection">
                                <div>
                                    <label class="option-label">
                                        <input type="radio" name="payment_option" id="payDown" value="downpayment" checked onchange="updateJoinerPaymentDisplay()">
                                        Pay Downpayment (min. 20%)
                                    </label>
                                    <label class="option-label">
                                        <input type="radio" name="payment_option" id="payInstallment" value="installment" onchange="updateJoinerPaymentDisplay()">
                                        Pay in Installments (min. 20% now)
                                    </label>
                                    <label class="option-label">
                                        <input type="radio" name="payment_option" id="payFull" value="full" onchange="updateJoinerPaymentDisplay()">
                                        Pay Full Amount
                                    </label>
                                </div>

                                <div id="customDownpaymentWrap" style="margin-top: 6px;">
                                    <label for="downpaymentInput" style="display:block; font-size:12.5px; color:#374151; margin-bottom:6px; font-weight:600;">
                                        How much would you like to pay now? (minimum ₱{{ number_format($downpaymentAmount, 2) }} / 20%)
                                    </label>
                                    <input type="number" id="downpaymentInput" min="{{ $downpaymentAmount }}" max="{{ $totalPrice }}" step="0.01"
                                           value="{{ $downpaymentAmount }}" oninput="updateJoinerPaymentDisplay()" onblur="updateJoinerPaymentDisplay()"
                                           style="padding:10px; border:1px solid #d1d5db; border-radius:8px; font-size:14px; width:100%; max-width:260px; text-align:left; display:block;">
                                    <div id="downpaymentError" style="display:none; color:#b91c1c; font-size:12px; font-weight:600; margin-top:6px;">
                                        You must pay at least ₱{{ number_format($downpaymentAmount, 2) }} (20% of the total amount).
                                    </div>
                                </div>

                                <div id="installmentNote" class="installment-note" style="display:none;">
                                    <i class="fas fa-circle-info"></i>
                                    Pay at least 20% now to reserve your seat(s). You can then pay the rest in parts
                                    from <strong>My Bookings &rsaquo; Receipt</strong> any time until <strong>7 days before</strong> the trip.
                                    Whatever is left after that is collected by your driver on the trip.
                                </div>
                            </div>

                            <input type="hidden" name="amount_to_pay" id="amount_to_pay" value="{{ $downpaymentAmount }}">

                            <div class="joiner-amount-row">
                                <div class="amount-item">
                                    <label id="payment-label">Paying Now</label>
                                    <p class="green" id="payment-amount">₱{{ number_format($downpaymentAmount, 2) }}</p>
                                </div>
                                <div class="amount-item">
                                    <label>Remaining Balance</label>
                                    <p class="red" id="display-balance">₱{{ number_format($totalPrice - $downpaymentAmount, 2) }}</p>
                                </div>
                                <div class="amount-item">
                                    <label>Total Package Price</label>
                                    <p>₱{{ number_format($totalPrice, 2) }}</p>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 shadow py-3 fw-bold" id="confirm-btn" style="border-radius: 12px;">
                                Continue to Secure Payment <i class="fas fa-arrow-right ms-2"></i>
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
    const JOINER_TOTAL = {{ $totalPrice }};
    const JOINER_MIN_DOWNPAYMENT = {{ $downpaymentAmount }}; // 20% of total

    function updateJoinerPaymentDisplay() {
        const paymentType = document.querySelector('input[name="payment_option"]:checked').value;
        const total = JOINER_TOTAL;

        const label = document.getElementById('payment-label');
        const displayAmount = document.getElementById('payment-amount');
        const displayBalance = document.getElementById('display-balance');
        const hiddenAmount = document.getElementById('amount_to_pay');
        const customWrap = document.getElementById('customDownpaymentWrap');
        const downInput = document.getElementById('downpaymentInput');
        const downError = document.getElementById('downpaymentError');
        const installmentNote = document.getElementById('installmentNote');

        installmentNote.style.display = (paymentType === 'installment') ? 'block' : 'none';

        if (paymentType === 'full') {
            customWrap.style.display = 'none';
            downError.style.display = 'none';
            label.innerText = "Full Payment Amount";
            displayAmount.innerText = "₱" + total.toLocaleString(undefined, {minimumFractionDigits: 2});
            displayBalance.innerText = "₱0.00";
            hiddenAmount.value = total;
        } else {
            customWrap.style.display = 'block';
            let amount = parseFloat(downInput.value);
            if (isNaN(amount)) amount = JOINER_MIN_DOWNPAYMENT;

            // Never allow the amount to exceed the total — snap it back immediately.
            if (amount > total) {
                amount = total;
                downInput.value = total;
            }

            const isValid = amount >= JOINER_MIN_DOWNPAYMENT;
            downError.style.display = isValid ? 'none' : 'block';

            const safeAmount = Math.max(amount, JOINER_MIN_DOWNPAYMENT);

            label.innerText = (paymentType === 'installment') ? "Paying Now" : "Downpayment Amount";
            displayAmount.innerText = "₱" + safeAmount.toLocaleString(undefined, {minimumFractionDigits: 2});
            displayBalance.innerText = "₱" + (total - safeAmount).toLocaleString(undefined, {minimumFractionDigits: 2});
            hiddenAmount.value = amount;
        }
    }

    function validateJoinerForm() {
        const paymentType = document.querySelector('input[name="payment_option"]:checked').value;
        if (paymentType === 'downpayment' || paymentType === 'installment') {
            updateJoinerPaymentDisplay();
            const downInput = document.getElementById('downpaymentInput');
            const downError = document.getElementById('downpaymentError');
            const amount = parseFloat(downInput.value);
            if (isNaN(amount) || amount < JOINER_MIN_DOWNPAYMENT || amount > JOINER_TOTAL) {
                downError.style.display = 'block';
                downInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                downInput.focus();
                return false;
            }
        }
        return true;
    }
</script>
</body>
</html>
