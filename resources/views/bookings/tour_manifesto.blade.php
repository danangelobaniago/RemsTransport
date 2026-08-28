<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Passenger Manifesto | {{ $tour->name }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Existing Styles */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background: #f8fafc; color: #1e293b; padding: 40px 20px; }
        .container { max-width: 1000px; margin: 0 auto; }
        .back-link { display: inline-flex; align-items: center; text-decoration: none; color: #64748b; font-weight: 600; margin-bottom: 20px; transition: 0.2s; }
        .back-link:hover { color: #2563eb; }
        .card { background: white; padding: 40px; border-radius: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.03); border: 1px solid #e2e8f0; }
        .header { border-bottom: 2px solid #f1f5f9; padding-bottom: 20px; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: flex-end; }
        .header h2 { font-size: 28px; color: #111827; }
        .tour-badge { display: inline-block; background: #eff6ff; color: #2563eb; padding: 6px 12px; border-radius: 6px; font-size: 14px; font-weight: 600; margin-top: 8px; }
        .limit-badge { font-size: 13px; color: #64748b; font-weight: 500; }

        /* Error Message Styling */
        .alert-error {
            background: #fee2e2;
            border: 1px solid #fecaca;
            color: #b91c1c;
            padding: 16px;
            border-radius: 12px;
            margin-bottom: 25px;
            font-size: 14px;
        }
        .alert-error ul { margin-left: 20px; margin-top: 8px; }

        .passenger-row {
            display: grid;
            grid-template-columns: 1.2fr 1fr 1.2fr 1fr 0.5fr 0.8fr 40px;
            gap: 12px;
            margin-bottom: 15px;
            padding: 20px;
            background: #fdfdfd;
            border: 1px solid #f1f5f9;
            border-radius: 12px;
            position: relative;
            align-items: end;
        }

        label { display: block; font-size: 11px; font-weight: 700; color: #64748b; margin-bottom: 6px; text-transform: uppercase; }
        input, select { width: 100%; padding: 11px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; transition: 0.2s; }
        input:focus { border-color: #2563eb; outline: none; box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1); }

        .add-btn { background: #f1f5f9; color: #475569; border: none; padding: 12px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; margin-top: 10px; transition: 0.2s; }
        .add-btn:hover:not(:disabled) { background: #e2e8f0; color: #1e293b; }
        .add-btn:disabled { opacity: 0.5; cursor: not-allowed; }

        .remove-btn { background: #fee2e2; color: #ef4444; border: none; width: 35px; height: 35px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.2s; }
        .remove-btn:hover { background: #fecaca; }

        .price-summary { margin-top: 30px; padding-top: 20px; border-top: 2px solid #f1f5f9; }

        /* Payment Option (matches the van booking passenger form) */
        .payment-title { margin-top: 0; margin-bottom: 15px; font-size: 20px; color: #1e293b; display: flex; align-items: center; gap: 10px; }
        .payment-selection { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 25px 25px 15px; margin-bottom: 6px; }
        .payment-selection .option-label { display: flex; align-items: center; cursor: pointer; text-transform: none; color: #1e293b; font-size: 14px; font-weight: 600; }
        .payment-selection .option-label input { width: 18px; height: 18px; margin-right: 8px; }

        .amount-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; background: #111827; color: white; padding: 20px; border-radius: 12px; margin-top: 20px; }
        .amount-item label { font-size: 12px; color: #9ca3af; display: block; text-transform: none; }
        .amount-item p { margin: 5px 0 0; font-size: 1.2rem; font-weight: 700; color: white; }
        .green { color: #4ade80; }
        .red { color: #f87171; }
        @media (max-width: 600px) { .amount-row { grid-template-columns: 1fr; text-align: center; } }

        .btn-submit {
            margin-top: 25px; width: 100%; padding: 16px; background: #2563eb; color: white; border: none;
            border-radius: 10px; font-size: 16px; font-weight: 600; cursor: pointer; transition: 0.2s;
            display: flex; align-items: center; justify-content: center;
        }
        .btn-submit:hover:not(:disabled) { background: #1e40af; }

        /* Terms and Conditions modal body */
        .terms-body p { margin: 0 0 16px; padding-bottom: 16px; border-bottom: 1px solid #f1f5f9; }
        .terms-body p:last-child { margin-bottom: 0; padding-bottom: 0; border-bottom: none; }
        .terms-body strong { display: block; color: #111827; font-size: 13.5px; font-weight: 700; margin-bottom: 4px; }

        @keyframes slideIn {
             from { transform: scale(0.9); opacity: 0; }
             to { transform: scale(1); opacity: 1; }
            }

    </style>
</head>
<body>

<div class="container">
    <a href="/tour/details/{{ $tour->id }}" class="back-link">
        <i class="fas fa-arrow-left" style="margin-right: 8px;"></i> Back to Tour Details
    </a>

    <div class="card">
        <div class="header">
            <div>
                <h2>Passenger Manifesto</h2>
                <div class="tour-badge">Tour: {{ $tour->name }}</div>
                <div style="margin-top:8px;display:inline-flex;align-items:center;gap:6px;background:#f0fdf4;color:#15803d;padding:5px 12px;border-radius:6px;font-size:13px;font-weight:700;border:1px solid #bbf7d0;">
                    <i class="fas fa-calendar-check"></i>
                    @if($preferred_date === $preferred_end)
                        Travel Date: {{ date('F d, Y', strtotime($preferred_date)) }}
                    @else
                        Travel Dates: {{ date('M d', strtotime($preferred_date)) }} – {{ date('M d, Y', strtotime($preferred_end)) }}
                        &nbsp;({{ $tripDays }} days)
                    @endif
                </div>
            </div>
            <div class="limit-badge">
                <i class="fas fa-info-circle"></i> Max Capacity: <strong>{{ $tour->max_passengers }} Pax</strong>
            </div>
        </div>

        {{-- Error Message Display --}}
        @if ($errors->any())
            <div class="alert-error">
                <strong><i class="fas fa-exclamation-triangle"></i> Please fix the following errors:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('bookings.tour_pay') }}" method="POST" id="manifesto-form" onsubmit="return validateTourForm();">
            @csrf
            <input type="hidden" name="tour_id" value="{{ $tour->id }}">
            <input type="hidden" name="preferred_date" value="{{ $preferred_date }}">
            <input type="hidden" name="preferred_end" value="{{ $preferred_end }}">

            <div id="passenger-list">
                <div class="passenger-row" data-index="0">
                    <div>
                        <label>First Name</label>
                        <input type="text" name="first_name[]" placeholder="First name" required>
                    </div>
                    <div>
                        <label>Middle Name</label>
                        <input type="text" name="middle_name[]" placeholder="Optional">
                    </div>
                    <div>
                        <label>Last Name</label>
                        <input type="text" name="last_name[]" placeholder="Last name" required>
                    </div>
                    <div>
                        <label>Birthday</label>
                        <input type="date" name="birthday[]" max="{{ date('Y-m-d') }}" required oninput="calcAge(this)">
                    </div>
                    <div>
                        <label>Age</label>
                        <input type="number" name="age[]" class="age-field" placeholder="—" readonly style="background:#f1f5f9;cursor:default;">
                    </div>
                    <div>
                        <label>Gender</label>
                        <select name="gender[]">
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                    <div style="width: 35px;"></div>
                </div>
            </div>

            <button type="button" class="add-btn" id="add-passenger-btn" onclick="addPassenger()">
                <i class="fas fa-plus-circle"></i> Add Another Passenger
            </button>

            <input type="hidden" name="amount_to_pay" id="amount_to_pay" value="{{ $tour->price * 0.20 }}">

            <div class="price-summary">
                <h2 class="payment-title"><i class="fas fa-credit-card"></i> Payment Option</h2>

                <div class="payment-selection">
                    <label style="display:block; font-size:13px; color:#374151; margin-bottom:10px; font-weight:600;">Choose Payment Option:</label>
                    <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                        <label class="option-label">
                            <input type="radio" name="payment_type" value="downpayment" checked onchange="updatePaymentSummary()">
                            Pay Downpayment (min. 20%)
                        </label>
                        <label class="option-label">
                            <input type="radio" name="payment_type" value="full" onchange="updatePaymentSummary()">
                            Pay Full Amount
                        </label>
                    </div>

                    <div id="customDownpaymentWrap" style="margin-top: 15px;">
                        <label for="downpaymentInput" style="display:block; font-size:13px; color:#374151; margin-bottom:6px;">
                            How much would you like to pay? (minimum ₱{{ number_format($tour->price * 0.20, 2) }} / 20%)
                        </label>
                        <input type="number" id="downpaymentInput" min="{{ $tour->price * 0.20 }}" max="{{ $tour->price }}" step="0.01"
                               value="{{ $tour->price * 0.20 }}" oninput="updatePaymentSummary()" onblur="updatePaymentSummary()"
                               style="padding:10px; border:1px solid #d1d5db; border-radius:8px; font-size:14px; width:100%; max-width:260px; text-align:left; display:block;">
                        <div id="downpaymentError" style="display:none; color:#b91c1c; font-size:12px; font-weight:600; margin-top:6px;">
                            Downpayment must be at least ₱{{ number_format($tour->price * 0.20, 2) }} (20% of the total amount).
                        </div>
                    </div>
                </div>

                <div class="amount-row">
                    <div class="amount-item">
                        <label id="payment-label">Required Downpayment</label>
                        <p class="green" id="display-payment-amount">₱{{ number_format($tour->price * 0.20, 2) }}</p>
                    </div>
                    <div class="amount-item">
                        <label>Remaining Balance</label>
                        <p class="red" id="display-balance">₱{{ number_format($tour->price * 0.80, 2) }}</p>
                    </div>
                    <div class="amount-item">
                        <label>Total Amount</label>
                        <p>₱{{ number_format($tour->price, 2) }}</p>
                    </div>
                </div>

                <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 12px; padding: 20px 24px; margin: 25px 0 0; display: flex; align-items: center; gap: 16px; flex-wrap: wrap;">
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
            </div>

            {{-- Terms Modal --}}
            <div id="termsModal" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; background:rgba(15,23,42,0.65); align-items:center; justify-content:center; padding:20px;">
                <div style="background:white; border-radius:16px; max-width:720px; width:100%; max-height:85vh; box-shadow:0 20px 50px rgba(0,0,0,0.25); display:flex; flex-direction:column; overflow:hidden;">

                    <div style="padding:22px 28px; border-bottom:1px solid #e5e7eb; display:flex; align-items:flex-start; justify-content:space-between; gap:16px; flex-shrink:0;">
                        <div>
                            <h2 style="margin:0 0 2px; color:#111827; font-size:19px; font-weight:700;">Terms and Conditions</h2>
                            <p style="margin:0; color:#6b7280; font-size:13px;">Rem's Transport Tour Booking Agreement &amp; Data Privacy Notice</p>
                        </div>
                        <button type="button" onclick="closeTermsModal()" aria-label="Close" style="background:#f3f4f6; border:none; width:32px; height:32px; border-radius:8px; color:#6b7280; cursor:pointer; font-size:14px; flex-shrink:0;">
                            <i class="fas fa-xmark"></i>
                        </button>
                    </div>

                    <div class="terms-body" style="padding:22px 28px; overflow-y:auto; font-size:14px; color:#475569; line-height:1.7;">
                        <p><strong>1. Reservation and Payment</strong> A non-refundable 20% downpayment is required to secure your slot. Full payment options are also available. Balance must be settled as specified.</p>
                        <p><strong>2. Cancellation Policy</strong> Cancellations result in forfeiture of the 20% downpayment. For full payments, the 80% balance is refundable through administrative coordination.</p>
                        <p><strong>3. Passenger Manifesto</strong> Only passengers registered in this manifesto are permitted.</p>
                        <p><strong>4. Data Privacy</strong> We collect and process personal information (including passenger names, birthdays, ages, and gender) in compliance with the Data Privacy Act of 2012 (RA 10173), used solely for booking confirmation, trip coordination, and safety purposes.</p>
                    </div>

                    <div style="padding:16px 28px; border-top:1px solid #e5e7eb; background:#f9fafb; flex-shrink:0;">
                        <button type="button" onclick="closeTermsModal()" style="width:100%; padding:13px; background:#2563eb; color:white; border:none; border-radius:10px; font-weight:700; font-size:14px; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px;">
                            <i class="fas fa-check"></i> I Have Read and Understood
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    const MAX_LIMIT = {{ $tour->max_passengers }};
    const passengerList = document.getElementById('passenger-list');
    const addBtn = document.getElementById('add-passenger-btn');
    const TODAY = new Date().toISOString().split('T')[0];

    // --- MODAL CONTROLS ---
    function openTermsModal() {
        document.getElementById('termsModal').style.display = 'flex';
        // Opening the terms counts as having read them — unlock the submit button right away.
        document.getElementById('termsStatus').style.display = 'block';
        const btn = document.getElementById('submitBtn');
        btn.disabled = false;
        btn.style.opacity = '1';
        btn.style.cursor = 'pointer';
    }

    function closeTermsModal() {
        document.getElementById('termsModal').style.display = 'none';
    }

    // --- PAYMENT SUMMARY ---
    const MIN_DOWNPAYMENT = {{ $tour->price * 0.20 }}; // 20% of total
    const TOTAL_AMOUNT = {{ $tour->price }};

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

    function validateTourForm() {
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

    // --- AGE CALCULATOR ---
    function calcAge(dateInput) {
        const dob = new Date(dateInput.value);
        const row = dateInput.closest('.passenger-row');
        const ageField = row.querySelector('.age-field');
        if (!dateInput.value || isNaN(dob)) { ageField.value = ''; return; }
        const today = new Date();
        let age = today.getFullYear() - dob.getFullYear();
        const m = today.getMonth() - dob.getMonth();
        if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) age--;
        ageField.value = age >= 0 ? age : '';
    }

    // --- PASSENGER MANAGEMENT ---
    function addPassenger() {
        const currentRows = document.querySelectorAll('.passenger-row').length;
        if (currentRows < MAX_LIMIT) {
            const firstRow = document.querySelector('.passenger-row');
            const newRow = firstRow.cloneNode(true);
            newRow.querySelectorAll('input').forEach(input => {
                input.value = '';
                if (input.type === 'date') input.max = TODAY;
            });
            const removeBtnCell = newRow.querySelector('div:last-child');
            removeBtnCell.innerHTML = `<button type="button" class="remove-btn" onclick="removeRow(this)"><i class="fas fa-times"></i></button>`;
            passengerList.appendChild(newRow);
            updateButtonState();
        }
    }

    function removeRow(btn) {
        btn.closest('.passenger-row').remove();
        updateButtonState();
    }

    function updateButtonState() {
        const currentRows = document.querySelectorAll('.passenger-row').length;
        if (currentRows >= MAX_LIMIT) {
            addBtn.disabled = true;
            addBtn.innerHTML = `<i class="fas fa-ban"></i> Limit Reached (${MAX_LIMIT})`;
        } else {
            addBtn.disabled = false;
            addBtn.innerHTML = `<i class="fas fa-plus-circle"></i> Add Another Passenger`;
        }
    }

    // --- GLOBAL MODAL CLICK LOGIC (Moved outside of event listener) ---
    window.onclick = function(event) {
        const modal = document.getElementById('termsModal');
        if (event.target == modal) {
            closeTermsModal();
        }
    }
</script>

</body>
</html>
