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

        /* Terms modal body (matches the van/tour booking passenger forms) */
        .terms-body p { margin: 0 0 16px; padding-bottom: 16px; border-bottom: 1px solid #f1f5f9; }
        .terms-body p:last-child { margin-bottom: 0; padding-bottom: 0; border-bottom: none; }
        .terms-body strong { display: block; color: #111827; font-size: 13.5px; font-weight: 700; margin-bottom: 4px; }
    </style>
</head>
<body>

@php
    // Fewer passengers = a narrower card, so a 1-seat booking isn't a tiny
    // form lost in a huge blank page.
    $formMaxWidth = match (true) {
        $seats <= 1 => '720px',
        $seats == 2 => '980px',
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
                            <div class="{{ match (true) { $seats <= 1 => 'col-12', $seats == 2 => 'col-12 col-md-6', default => 'col-12 col-md-6 col-lg-4' } }}" style="max-width: 400px;">
                                <div class="passenger-entry p-4 shadow-sm border h-100">
                                    <h6 class="fw-bold text-primary mb-3"><i class="fas fa-user-circle me-2"></i>Passenger #{{ $i + 1 }}</h6>
                                    <div class="mb-3">
                                        <label class="small fw-bold text-muted">Full Name</label>
                                        <input type="text" name="passenger_name[]" class="form-control bg-light" placeholder="Enter name" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="small fw-bold text-muted">Suffix</label>
                                        <select name="passenger_suffix[]" class="form-select bg-light">
                                            <option value="">None</option>
                                            <option value="Jr.">Jr.</option>
                                            <option value="Sr.">Sr.</option>
                                            <option value="II">II</option>
                                            <option value="III">III</option>
                                            <option value="IV">IV</option>
                                            <option value="V">V</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="small fw-bold text-muted">Contact Number</label>
                                        <input type="tel" name="passenger_contact[]" class="form-control bg-light" placeholder="09XXXXXXXXX" maxlength="11" oninput="validatePHNumber(this)" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="small fw-bold text-muted">Birthday</label>
                                        <input type="date" name="passenger_birthday[]" class="form-control bg-light" max="{{ date('Y-m-d', strtotime('-1 month')) }}" required oninput="calcAge(this)">
                                    </div>
                                    <div class="mb-3">
                                        <label class="small fw-bold text-muted">Age</label>
                                        <input type="text" class="age-field form-control" placeholder="—" readonly style="background:#f3f4f6;cursor:default;">
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

                    <div id="duplicateWarning" style="display:none; background:#fef2f2; border:1px solid #fecaca; color:#b91c1c; padding:12px 15px; border-radius:8px; margin-bottom:20px; font-size:14px; font-weight:600; align-items:center; gap:8px;">
                        <i class="fas fa-triangle-exclamation"></i>
                        <span>Two passengers cannot have the same Full Name and Suffix. Please fix the highlighted boxes.</span>
                    </div>

                    <div id="ageWarning" style="display:none; background:#fef2f2; border:1px solid #fecaca; color:#b91c1c; padding:12px 15px; border-radius:8px; margin-bottom:20px; font-size:14px; font-weight:600; align-items:center; gap:8px;">
                        <i class="fas fa-triangle-exclamation"></i>
                        <span>Passengers must be at least 1 month old. Please fix the highlighted birthday field(s).</span>
                    </div>

                    <div id="guardianWarning" style="display:none; background:#fef2f2; border:1px solid #fecaca; color:#b91c1c; padding:12px 15px; border-radius:8px; margin-bottom:20px; font-size:14px; font-weight:600; align-items:center; gap:8px;">
                        <i class="fas fa-triangle-exclamation"></i>
                        <span>A passenger under 18 cannot travel alone — add at least one passenger who is 18 or older to this booking.</span>
                    </div>

                    <div class="row align-items-stretch g-4 mt-2">
                        <div class="{{ $seats <= 2 ? 'col-12' : 'col-lg-7' }}">
                            <div class="h-100 d-flex align-items-center" style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 12px; padding: 20px 24px; gap: 16px; flex-wrap: wrap;">
                                <div style="flex: 1; min-width: 220px;">
                                    <h6 style="font-weight: 700; color: #111827; margin: 0 0 4px; font-size: 15px;">Terms &amp; Data Privacy</h6>
                                    <p style="font-size: 13px; color: #6b7280; margin: 0;">Please open and read the full Terms and Data Privacy Act before proceeding with your booking.</p>
                                    <p id="termsStatus" style="display:none; margin: 8px 0 0; font-size: 13px; font-weight: 600; color: #16a34a;">
                                        <i class="fas fa-circle-check"></i> You've read the Terms and Data Privacy Act.
                                    </p>
                                </div>
                                <button type="button" onclick="openTermsModal()" style="padding:10px 18px; background:#2563eb; color:white; border:none; border-radius:8px; font-weight:600; font-size:13px; cursor:pointer; white-space:nowrap; display:inline-flex; align-items:center; gap:8px;">
                                    <i class="fas fa-file-lines"></i> Read Terms
                                </button>
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

                            <button type="submit" class="btn btn-primary w-100 shadow py-3 fw-bold" id="confirm-btn" disabled style="border-radius: 12px; opacity: 0.5; cursor: not-allowed;">
                                Continue to Secure Payment <i class="fas fa-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Terms Modal (matches the van/tour booking passenger forms) --}}
<div id="termsModal" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; background:rgba(15,23,42,0.65); align-items:center; justify-content:center; padding:20px;">
    <div style="background:white; border-radius:16px; max-width:720px; width:100%; max-height:85vh; box-shadow:0 20px 50px rgba(0,0,0,0.25); display:flex; flex-direction:column; overflow:hidden; animation: fadeIn 0.3s ease;">

        <div style="padding:22px 28px; border-bottom:1px solid #e5e7eb; display:flex; align-items:flex-start; justify-content:space-between; gap:16px; flex-shrink:0;">
            <div>
                <h2 style="margin:0 0 2px; color:#111827; font-size:19px; font-weight:700;">Terms and Conditions</h2>
                <p style="margin:0; color:#6b7280; font-size:13px;">Rem's Transport Booking Agreement &amp; Data Privacy Notice</p>
            </div>
            <button type="button" onclick="closeTermsModal()" aria-label="Close" style="background:#f3f4f6; border:none; width:32px; height:32px; border-radius:8px; color:#6b7280; cursor:pointer; font-size:14px; flex-shrink:0;">
                <i class="fas fa-xmark"></i>
            </button>
        </div>

        <div class="terms-body" style="padding:22px 28px; overflow-y:auto; font-size:14px; color:#475569; line-height:1.7;">
            <p><strong>1. Reservation Policy</strong> A non-refundable downpayment of at least 20% of the total fare is required to confirm your booking. The remaining balance must be settled in cash upon boarding, before the vehicle departs from the pickup point.</p>
            <p><strong>2. Passenger Responsibility</strong> All passengers must be present at the designated pickup point at the agreed date and time. Rem's Transport is not liable for missed trips, delays, or additional charges arising from passenger tardiness or incomplete/incorrect booking information.</p>
            <p><strong>3. Passenger Information Accuracy</strong> Passengers are responsible for providing true, accurate, and complete details (name, birthday, age, and gender) during booking. Rem's Transport may refuse boarding if a passenger's identity cannot be reasonably verified against the submitted booking details.</p>
            <p><strong>4. Cancellation Policy</strong> Cancellations made at least 3 days before the scheduled trip may be rebooked to another available date, subject to vehicle availability. Cancellations made within 72 hours of the trip, or no-shows on the day of the trip, will forfeit the downpayment.</p>
            <p><strong>5. Rescheduling</strong> Trip rescheduling requests must be made at least 48 hours before the original schedule and are subject to driver and vehicle availability. Repeated rescheduling of the same booking may incur additional fees.</p>
            <p><strong>6. Vehicle, Route, and Driver Changes</strong> Rem's Transport reserves the right to assign a different but comparable vehicle, driver, or route in case of mechanical issues, road conditions, weather disturbances, or other circumstances beyond our control, without reducing the agreed service inclusions.</p>
            <p><strong>7. Passenger Conduct</strong> Passengers must not bring illegal, hazardous, or prohibited items on board. Rem's Transport reserves the right to deny or discontinue service to passengers who engage in unruly, abusive, or unsafe behavior, without refund.</p>
            <p><strong>8. Luggage and Belongings</strong> Passengers are responsible for their own personal belongings at all times. Rem's Transport shall not be held liable for any loss, theft, or damage to items left unattended inside or outside the vehicle.</p>
            <p><strong>9. Liability and Insurance</strong> While reasonable safety measures are observed, Rem's Transport's liability for any injury, loss, or damage arising from the trip shall be limited to what is covered by the vehicle's applicable insurance policy, except where caused by our proven gross negligence.</p>
            <p><strong>10. Force Majeure</strong> Rem's Transport shall not be held liable for delays, cancellations, or service interruptions caused by events beyond its reasonable control, including but not limited to natural disasters, government-imposed restrictions, road closures, and civil disturbances. Affected bookings will be rescheduled or credited whenever possible.</p>
            <p><strong>11. Data Privacy</strong> We collect and process personal information (including passenger names, birthdays, ages, gender, and contact details) in compliance with the Data Privacy Act of 2012 (RA 10173). Your data is used solely for booking confirmation, trip coordination, safety, and legal compliance purposes, and will not be sold or shared with third parties except as required by law or to complete the booked service.</p>
            <p><strong>12. Data Retention</strong> Booking and passenger information will be retained only for as long as necessary to fulfill the purposes stated above and to comply with legal, accounting, or reporting requirements, after which it will be securely disposed of.</p>
            <p><strong>13. Amendments</strong> Rem's Transport may update these Terms and the Data Privacy Notice from time to time. Continued use of our booking system after changes are posted constitutes acceptance of the revised terms.</p>
            <p><strong>14. Governing Law</strong> These Terms shall be governed by and interpreted in accordance with the laws of the Republic of the Philippines. Any disputes arising from this agreement shall be resolved through good-faith negotiation before resorting to formal legal action.</p>
            <p><strong>15. Contact Information</strong> For questions, concerns, or data privacy requests regarding your booking, you may reach Rem's Transport through the contact details provided on our booking confirmation or official channels.</p>
        </div>

        <div style="padding:16px 28px; border-top:1px solid #e5e7eb; background:#f9fafb; flex-shrink:0;">
            <button type="button" onclick="closeTermsModal()" style="width:100%; padding:13px; background:#2563eb; color:white; border:none; border-radius:10px; font-weight:700; font-size:14px; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px;">
                <i class="fas fa-check"></i> I Have Read and Understood
            </button>
        </div>
    </div>
</div>

<script>
    const MIN_BIRTHDAY = "{{ date('Y-m-d', strtotime('-1 month')) }}"; // birthday must be on/before this date

    function calcAge(dateInput) {
        const dob = new Date(dateInput.value);
        const box = dateInput.closest('.passenger-entry');
        const ageField = box.querySelector('.age-field');
        if (!dateInput.value || isNaN(dob)) { ageField.value = ''; return; }
        const today = new Date();

        let totalMonths = (today.getFullYear() - dob.getFullYear()) * 12 + (today.getMonth() - dob.getMonth());
        if (today.getDate() < dob.getDate()) totalMonths--;
        if (totalMonths < 0) { ageField.value = ''; return; }

        if (totalMonths < 12) {
            ageField.value = totalMonths + (totalMonths === 1 ? ' month' : ' months');
        } else {
            ageField.value = Math.floor(totalMonths / 12);
        }
    }

    // Whole years old as of today, or null if the date is invalid/empty.
    function getAgeYears(dateStr) {
        if (!dateStr) return null;
        const dob = new Date(dateStr);
        if (isNaN(dob)) return null;
        const today = new Date();
        let age = today.getFullYear() - dob.getFullYear();
        const m = today.getMonth() - dob.getMonth();
        if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) age--;
        return age;
    }
    function openTermsModal() {
        document.getElementById('termsModal').style.display = 'flex';
        // Opening the terms counts as having read them — unlock the submit button right away.
        document.getElementById('termsStatus').style.display = 'block';
        const btn = document.getElementById('confirm-btn');
        btn.disabled = false;
        btn.style.opacity = '1';
        btn.style.cursor = 'pointer';
    }
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
        const duplicateWarning = document.getElementById('duplicateWarning');
        const ageWarning = document.getElementById('ageWarning');
        const guardianWarning = document.getElementById('guardianWarning');
        const entries = document.querySelectorAll('.passenger-entry');

        entries.forEach(entry => {
            entry.style.borderColor = '';
            const birthdayInput = entry.querySelector('input[name="passenger_birthday[]"]');
            if (birthdayInput) birthdayInput.style.borderColor = '';
        });

        // Two passengers cannot be the same person (same full name + suffix).
        let duplicateFound = false;
        let firstBadEntry = null;
        const seenNames = new Map();
        entries.forEach((entry, i) => {
            const name = (entry.querySelector('input[name="passenger_name[]"]')?.value || '').trim().toLowerCase();
            const suffix = (entry.querySelector('select[name="passenger_suffix[]"]')?.value || '').trim().toLowerCase();
            const key = `${name}|${suffix}`;

            if (seenNames.has(key)) {
                duplicateFound = true;
                entry.style.borderColor = '#dc2626';
                entries[seenNames.get(key)].style.borderColor = '#dc2626';
                firstBadEntry = firstBadEntry || entry;
            } else {
                seenNames.set(key, i);
            }
        });

        let underageFound = false;

        entries.forEach(entry => {
            const birthdayInput = entry.querySelector('input[name="passenger_birthday[]"]');
            if (birthdayInput && birthdayInput.value && birthdayInput.value > MIN_BIRTHDAY) {
                underageFound = true;
                birthdayInput.style.borderColor = '#dc2626';
                firstBadEntry = firstBadEntry || entry;
            }
        });

        // A minor (under 18) may not book/travel without at least one adult (18+)
        // passenger on the same booking.
        const ages = Array.from(entries).map(entry => {
            const birthdayInput = entry.querySelector('input[name="passenger_birthday[]"]');
            return { entry, years: birthdayInput ? getAgeYears(birthdayInput.value) : null };
        });
        const hasAdult = ages.some(a => a.years !== null && a.years >= 18);
        let minorWithoutGuardian = false;
        ages.forEach(({ entry, years }) => {
            if (years !== null && years < 18 && !hasAdult) {
                minorWithoutGuardian = true;
                entry.style.borderColor = '#dc2626';
                firstBadEntry = firstBadEntry || entry;
            }
        });

        duplicateWarning.style.display = duplicateFound ? 'flex' : 'none';
        ageWarning.style.display = underageFound ? 'flex' : 'none';
        guardianWarning.style.display = minorWithoutGuardian ? 'flex' : 'none';

        if (duplicateFound || underageFound || minorWithoutGuardian) {
            firstBadEntry.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return false;
        }

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
