<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Passenger Details | Rem's Transport</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f3f4f6; margin: 0; padding: 20px; }

        .wrapper { max-width: 1700px; margin: 20px auto; background: #fff; border-radius: 14px; padding: 30px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
        h2 { color: #1f2937; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }

        /* Summary Section */
        .summary-card { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 12px; padding: 20px; margin-bottom: 30px; }
        .summary-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; }
        .summary-item label { font-size: 11px; text-transform: uppercase; color: #6b7280; letter-spacing: 0.5px; display: block; }
        .summary-item p { margin: 5px 0 0; font-weight: 600; color: #111827; }

        /* Passenger Forms */
        .passenger-grid { display: grid; gap: 15px; margin-bottom: 20px; } /* column count is set inline per booking, based on passenger count */
        .passenger-box { border: 1px solid #e5e7eb; padding: 20px; border-radius: 12px; margin-bottom: 0; background: #ffffff; transition: 0.3s; }
        .passenger-box:hover { border-color: #2563eb; }
        .passenger-box h4 { margin-top: 0; color: #2563eb; border-bottom: 1px solid #f3f4f6; padding-bottom: 10px; font-size: 15px; }

        /* When there's more than one passenger per row, stack each passenger's fields in a single column so they stay readable */
        .passenger-grid-narrow .form-row, .passenger-grid-narrow .form-row-4 { grid-template-columns: 1fr !important; gap: 10px; margin-bottom: 10px; }

        @media (max-width: 700px) { .passenger-grid { grid-template-columns: repeat(2, 1fr) !important; } }
        @media (max-width: 500px) { .passenger-grid { grid-template-columns: 1fr !important; } }

        .form-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 15px; }
        .form-group { display: flex; flex-direction: column; min-width: 0; }
        .form-group label { font-size: 13px; color: #374151; margin-bottom: 6px; }
        .form-group input, .form-group select { width: 100%; min-width: 0; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; outline: none; }
        .form-group input:focus { border-color: #2563eb; ring: 2px #bfdbfe; }

        /* Pricing Section */
        .amount-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; background: #111827; color: white; padding: 20px; border-radius: 12px; margin-top: 30px; }
        .amount-item label { font-size: 12px; color: #9ca3af; display: block; }
        .amount-item p { margin: 5px 0 0; font-size: 1.2rem; font-weight: 700; }
        .green { color: #4ade80; }
        .red { color: #f87171; }

        .warning-box { background: #fffbeb; border: 1px solid #fef3c7; color: #92400e; padding: 15px; border-radius: 8px; margin: 20px 0; text-align: center; font-size: 14px; }

        /* Terms and Conditions modal body */
        .terms-body p { margin: 0 0 16px; padding-bottom: 16px; border-bottom: 1px solid #f1f5f9; }
        .terms-body p:last-child { margin-bottom: 0; padding-bottom: 0; border-bottom: none; }
        .terms-body strong { display: block; color: #111827; font-size: 13.5px; font-weight: 700; margin-bottom: 4px; }
        .btn-submit { width: 100%; padding: 16px; background: #2563eb; color: white; border: none; border-radius: 10px; font-size: 16px; font-weight: 600; cursor: pointer; transition: 0.2s; }
        .btn-submit:hover { background: #1e40af; transform: translateY(-1px); }
        /* Add space above the Payment Option title */
.payment-title {
    margin-top: 40px !important;
    margin-bottom: 15px;
}

/* Add space between the selection box and the black pricing row */
.payment-selection {
    margin-bottom: 6px !important;
    padding: 25px 25px 15px !important; /* Makes the box feel less tight */
}

.warning-box {
    background: #fffbeb;
    border: 1px solid #fef3c7;
    color: #92400e;
    padding: 15px;
    border-radius: 8px;
    margin: 25px 0 !important; /* This ensures 25px space top AND bottom */
    text-align: center;
    font-size: 14px;
}

/* Add space between the warning box/pricing and the submit button */
.btn-submit {
    margin-top: 25px !important;
    width: 100%;
    padding: 16px;
    background: #2563eb;
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Ensure the very bottom of the white card has space */
.wrapper {
    padding-bottom: 40px !important;
}
        .form-row-4 { grid-template-columns: repeat(4, 1fr); }
        @media (max-width: 700px) { .form-row-4 { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 600px) { .form-row, .form-row-4 { grid-template-columns: 1fr; } .amount-row { grid-template-columns: 1fr; text-align: center; } }
    </style>
</head>
<body>

@php
    // Size the whole card to how many passengers there are, so a single passenger
    // doesn't leave the page looking huge and mostly empty.
    $gridCols = min(3, max(1, (int) $passengers));
    $perBoxWidth = 320;
    $gridGap = 15;
    $wrapperPadding = 60; // 30px left + 30px right
    $wrapperMaxWidth = max(900, min(1050, ($gridCols * $perBoxWidth) + (($gridCols - 1) * $gridGap) + $wrapperPadding));
@endphp

<div class="wrapper" style="max-width: {{ $wrapperMaxWidth }}px;">
    <form method="POST" action="/paymongo/checkout" onsubmit="return validatePassengers();">
        @csrf

        {{-- CRITICAL: Hidden Data passing to Controller --}}
        <input type="hidden" name="formData" value='@json($formData)'>

        @php
            $total = floatval($formData['total'] ?? 0);
            $downpayment = $total * 0.2;
            $remaining = $total - $downpayment;
        @endphp

        <input type="hidden" name="total" value="{{ $total }}">
        <input type="hidden" name="days" value="{{ $formData['days'] ?? 1 }}">
        <input type="hidden" name="baseFare" value="{{ $formData['baseFare'] ?? 0 }}">
        <input type="hidden" name="driver_fee" value="{{ $formData['driverFee'] ?? 0 }}">

        <div style="margin-bottom: 20px;">
            <a href="javascript:history.back()" style="display:inline-flex; align-items:center; gap:8px; color:#2563eb; font-weight:600; font-size:14px; text-decoration:none;">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>

        <h2><i class="fas fa-file-invoice"></i> Booking Summary</h2>
        <div class="summary-card">
            <div class="summary-grid">
                <div class="summary-item">
                    <label>Pickup</label>
                    <p>{{ $formData['pickup'] }}</p>
                </div>
                <div class="summary-item">
                    <label>Destination</label>
                    <p>{{ $formData['destination'] }}</p>
                </div>
                <div class="summary-item">
                    <label>Schedule</label>
                    <p>{{ date('M d', strtotime($formData['start_date'])) }} - {{ date('M d, Y', strtotime($formData['end_date'])) }}</p>
                </div>
                <div class="summary-item">
                    <label>Pickup Time</label>
                    <p>{{ !empty($formData['pickup_time']) ? date('g:i A', strtotime($formData['pickup_time'])) : 'Not specified' }}</p>
                </div>
                <div class="summary-item">
                    <label>Vehicle</label>
                    <p>{{ $formData['van'] ?? 'Selected Van' }}</p>
                </div>
            </div>
        </div>

        <h2><i class="fas fa-users"></i> Passenger Details</h2>
        <div class="passenger-grid {{ $gridCols > 1 ? 'passenger-grid-narrow' : '' }}" style="grid-template-columns: repeat({{ $gridCols }}, 1fr);">
        @for ($i = 0; $i < $passengers; $i++)
            <div class="passenger-box">
                <h4><i class="fas fa-user-circle"></i> Passenger {{ $i + 1 }}</h4>
                <div class="form-row form-row-4">
                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" name="passengers_data[{{ $i }}][first_name]" placeholder="Enter First Name" maxlength="20" pattern="[A-Za-zÀ-ÿ .'\-]+" title="Letters only, no numbers" oninput="letterOnlyInput(this)" required>
                    </div>
                    <div class="form-group">
                        <label>Middle Name</label>
                        <input type="text" name="passengers_data[{{ $i }}][middle_name]" placeholder="Optional" maxlength="20" pattern="[A-Za-zÀ-ÿ .'\-]+" title="Letters only, no numbers" oninput="letterOnlyInput(this)">
                    </div>
                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text" name="passengers_data[{{ $i }}][last_name]" placeholder="Enter Last Name" maxlength="20" pattern="[A-Za-zÀ-ÿ .'\-]+" title="Letters only, no numbers" oninput="letterOnlyInput(this)" required>
                    </div>
                    <div class="form-group">
                        <label>Suffix</label>
                        <input type="text" name="passengers_data[{{ $i }}][suffix]" placeholder="Jr., Sr., III" maxlength="20" pattern="[A-Za-zÀ-ÿ .'\-]+" title="Letters only, no numbers" oninput="letterOnlyInput(this)">
                    </div>
                </div>
                <div class="form-row" style="grid-template-columns: 1fr 1fr 1fr;">
                    <div class="form-group">
                        <label>Birthday</label>
                        <input type="date" name="passengers_data[{{ $i }}][birthday]" max="{{ date('Y-m-d', strtotime('-3 months')) }}" required oninput="calcAge(this)">
                    </div>
                    <div class="form-group">
                        <label>Age</label>
                        <input type="text" class="age-field" placeholder="—" readonly style="background:#f3f4f6;cursor:default;">
                    </div>
                    <div class="form-group">
                        <label>Gender</label>
                        <select name="passengers_data[{{ $i }}][gender]" required>
                            <option value="">Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                </div>
            </div>
        @endfor
        </div>

        <div id="duplicateWarning" style="display:none; background:#fef2f2; border:1px solid #fecaca; color:#b91c1c; padding:12px 15px; border-radius:8px; margin:-5px 0 20px; font-size:14px; font-weight:600; align-items:center; gap:8px;">
            <i class="fas fa-triangle-exclamation"></i>
            <span>Two passengers cannot have the same First Name, Middle Name, and Last Name. Please fix the highlighted boxes.</span>
        </div>

        <div id="ageWarning" style="display:none; background:#fef2f2; border:1px solid #fecaca; color:#b91c1c; padding:12px 15px; border-radius:8px; margin:-5px 0 20px; font-size:14px; font-weight:600; align-items:center; gap:8px;">
            <i class="fas fa-triangle-exclamation"></i>
            <span>Passengers must be at least 3 months old. Please fix the highlighted birthday field(s).</span>
        </div>

        <!-- Add this before the .amount-row div -->
<h2 class="payment-title"><i class="fas fa-credit-card"></i> Payment Option</h2>

        <div class="payment-selection">
            <label>
                <input type="radio" name="payment_type" value="downpayment" checked onchange="updatePaymentSummary()">
                Pay Downpayment (min. 20%)
            </label>
            <label>
                <input type="radio" name="payment_type" value="full" onchange="updatePaymentSummary()">
                Pay Full Amount
            </label>

            <div id="customDownpaymentWrap" style="margin-top: 15px;">
                <label for="downpaymentInput" style="display:block; font-size:13px; color:#374151; margin-bottom:6px;">
                    How much would you like to pay? (minimum ₱{{ number_format($downpayment, 2) }} / 20%)
                </label>
                <input type="number" id="downpaymentInput" min="{{ $downpayment }}" max="{{ $total }}" step="0.01"
                       value="{{ $downpayment }}" oninput="updatePaymentSummary()" onblur="updatePaymentSummary()"
                       style="padding:10px; border:1px solid #d1d5db; border-radius:8px; font-size:14px; width:100%; max-width:260px; text-align:left; display:block;">
                <div id="downpaymentError" style="display:none; color:#b91c1c; font-size:12px; font-weight:600; margin-top:6px;">
                    Downpayment must be at least ₱{{ number_format($downpayment, 2) }} (20% of the total amount).
                </div>
            </div>
        </div>

        <!-- Hidden input to tell the controller exactly how much to charge -->
        <input type="hidden" name="amount_to_pay" id="amount_to_pay" value="{{ $downpayment }}">

        <div class="amount-row" style="margin-top: 0;">
            <div class="amount-item">
                <label id="payment-label">Required Downpayment</label>
                <p class="green" id="display-payment-amount">₱{{ number_format($downpayment, 2) }}</p>
            </div>

            <div class="amount-item">
                <label>Remaining Balance</label>
                <p class="red" id="display-balance">₱{{ number_format($remaining, 2) }}</p>
            </div>

            <div class="amount-item">
                <label>Total Amount</label>
                <p>₱{{ number_format($total, 2) }}</p>
            </div>
        </div>


        <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 12px; padding: 20px 24px; margin: 20px 0; display: flex; align-items: center; gap: 16px; flex-wrap: wrap;">
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

        <button type="submit" class="btn-submit" id="submitBtn" disabled style="opacity: 0.5; cursor: not-allowed;">
            Continue to Secure Payment <i class="fas fa-arrow-right" style="margin-left: 10px;"></i>
        </button>
    </form>
</div>

{{-- Terms Modal --}}
<div id="termsModal" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; background:rgba(15,23,42,0.65); align-items:center; justify-content:center; padding:20px;">
    <div style="background:white; border-radius:16px; max-width:720px; width:100%; max-height:85vh; box-shadow:0 20px 50px rgba(0,0,0,0.25); display:flex; flex-direction:column; overflow:hidden;">

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
function letterOnlyInput(input) {
    // Only letters, spaces, and common name punctuation ( . ' - ); no numbers/symbols. Max 20 chars.
    const cleaned = input.value.replace(/[^A-Za-zÀ-ÿ .'\-]/g, '').slice(0, 20);
    if (cleaned !== input.value) input.value = cleaned;
}
function calcAge(dateInput) {
    const dob = new Date(dateInput.value);
    const box = dateInput.closest('.passenger-box');
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
const MIN_BIRTHDAY = "{{ date('Y-m-d', strtotime('-3 months')) }}"; // birthday must be on/before this date

function validatePassengers() {
    const boxes = document.querySelectorAll('.passenger-box');
    const dupWarning = document.getElementById('duplicateWarning');
    const ageWarning = document.getElementById('ageWarning');
    const seen = new Map();
    let duplicateFound = false;
    let underageFound = false;
    let firstBadBox = null;

    boxes.forEach(box => {
        box.style.borderColor = '';
        const birthdayInput = box.querySelector('input[name*="[birthday]"]');
        if (birthdayInput) birthdayInput.style.borderColor = '';
    });

    boxes.forEach((box, i) => {
        const first = (box.querySelector('input[name*="[first_name]"]')?.value || '').trim().toLowerCase();
        const middle = (box.querySelector('input[name*="[middle_name]"]')?.value || '').trim().toLowerCase();
        const last = (box.querySelector('input[name*="[last_name]"]')?.value || '').trim().toLowerCase();
        const key = `${first}|${middle}|${last}`;

        if (seen.has(key)) {
            duplicateFound = true;
            box.style.borderColor = '#dc2626';
            boxes[seen.get(key)].style.borderColor = '#dc2626';
            firstBadBox = firstBadBox || box;
        } else {
            seen.set(key, i);
        }

        const birthdayInput = box.querySelector('input[name*="[birthday]"]');
        if (birthdayInput && birthdayInput.value && birthdayInput.value > MIN_BIRTHDAY) {
            underageFound = true;
            birthdayInput.style.borderColor = '#dc2626';
            firstBadBox = firstBadBox || box;
        }
    });

    dupWarning.style.display = duplicateFound ? 'flex' : 'none';
    ageWarning.style.display = underageFound ? 'flex' : 'none';

    if (duplicateFound || underageFound) {
        firstBadBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return false;
    }

    // Custom downpayment amount must be at least 20% of the total, and never exceed it.
    const paymentType = document.querySelector('input[name="payment_type"]:checked').value;
    if (paymentType === 'downpayment') {
        updatePaymentSummary(); // re-sync/clamp in case submit happened without a blur event
        const downInput = document.getElementById('downpaymentInput');
        const downError = document.getElementById('downpaymentError');
        const amount = parseFloat(downInput.value);
        if (isNaN(amount) || amount < MIN_DOWNPAYMENT || amount > TOTAL_AMOUNT) {
            downError.style.display = 'block';
            downInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
            downInput.focus();
            return false;
        }
    }

    return true;
}
function openTermsModal() {
    document.getElementById('termsModal').style.display = 'flex';
    // Opening the terms counts as having read them — unlock the submit button right away.
    document.getElementById('termsStatus').style.display = 'block';
    const btn = document.getElementById('submitBtn');
    btn.disabled = false;
    btn.style.opacity = '1';
    btn.style.cursor = 'pointer';
}
function closeTermsModal() { document.getElementById('termsModal').style.display = 'none'; }
const MIN_DOWNPAYMENT = {{ $downpayment }}; // 20% of total
const TOTAL_AMOUNT = {{ $total }};

function updatePaymentSummary() {
    const paymentType = document.querySelector('input[name="payment_type"]:checked').value;
    const total = TOTAL_AMOUNT;

    const label = document.getElementById('payment-label');
    const displayAmount = document.getElementById('display-payment-amount');
    const displayBalance = document.getElementById('display-balance');
    const hiddenAmount = document.getElementById('amount_to_pay');
    const customWrap = document.getElementById('customDownpaymentWrap');
    const downInput = document.getElementById('downpaymentInput');
    const downError = document.getElementById('downpaymentError');

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
        if (isNaN(amount)) amount = MIN_DOWNPAYMENT;

        // Never allow the downpayment to exceed the total amount — snap it back immediately.
        if (amount > total) {
            amount = total;
            downInput.value = total;
        }

        const isValid = amount >= MIN_DOWNPAYMENT;
        downError.style.display = isValid ? 'none' : 'block';

        const safeAmount = Math.max(amount, MIN_DOWNPAYMENT);

        label.innerText = "Downpayment Amount";
        displayAmount.innerText = "₱" + safeAmount.toLocaleString(undefined, {minimumFractionDigits: 2});
        displayBalance.innerText = "₱" + (total - safeAmount).toLocaleString(undefined, {minimumFractionDigits: 2});
        hiddenAmount.value = amount;
    }
}
</script>
</body>
</html>
