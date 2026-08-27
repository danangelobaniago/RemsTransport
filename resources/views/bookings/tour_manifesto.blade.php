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

        .policy-box { background: #fff9eb; border: 1px solid #ffeeba; padding: 20px; border-radius: 12px; margin-top: 40px; }
        .policy-box li { font-size: 13px; color: #856404; margin-bottom: 5px; display: flex; gap: 8px; align-items: center; }

        .price-summary { margin-top: 30px; padding-top: 20px; border-top: 2px solid #f1f5f9; }
        .row-total { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }

        .pay-btn {
            width: 100%; padding: 18px; border: none; border-radius: 12px; font-size: 16px; font-weight: 700;
            cursor: not-allowed; background: #94a3b8; color: white; margin-top: 20px; transition: 0.3s;

        }
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

        <form action="{{ route('bookings.tour_pay') }}" method="POST" id="manifesto-form">
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

            <!-- Updated Policy Box -->
<div class="policy-box">
    <h4 style="color: #856404; font-size: 15px; margin-bottom: 10px;"><i class="fas fa-shield-alt"></i> Booking Policy</h4>
    <p style="font-size: 13px; color: #856404; margin-bottom: 10px;">
        By proceeding, you agree to our
        <a href="javascript:void(0)" onclick="openTermsModal()" style="color: #2563eb; font-weight: 700; text-decoration: underline;">  Terms and Conditions</a>.
    </p>
    <label style="display: flex; align-items: center; cursor: pointer; margin-top: 15px; text-transform: none; color: #1e293b;">
        <input type="checkbox" name="terms_agree" id="terms_agree" required style="width: 18px; height: 18px; margin-right: 12px;">
        <span style="font-size: 14px; font-weight: 600;">I understand and agree to the terms above.</span>
    </label>
</div>

<!-- NEW: Terms and Conditions Modal -->
<div id="termsModal" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.6); align-items:center; justify-content:center; padding: 20px;">
    <div style="background:white; padding:30px; border-radius:15px; max-width:600px; width:100%; max-height:80vh; overflow-y:auto; box-shadow: 0 10px 25px rgba(0,0,0,0.2); animation: slideIn 0.3s ease;">
        <h2 style="margin-bottom:20px; color: #1e293b; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px;">Terms and Conditions</h2>

        <div style="font-size: 14px; color: #475569; line-height: 1.6;">
            <p style="margin-bottom: 15px;"><strong>1. Reservation and Payment</strong><br>
            A non-refundable 20% downpayment is required to secure your slot. Full payment options are also available. Balance must be settled as specified.</p>

            <p style="margin-bottom: 15px;"><strong>2. Cancellation Policy</strong><br>
            Cancellations result in forfeiture of the 20% downpayment. For full payments, the 80% balance is refundable through administrative coordination.</p>

            <p style="margin-bottom: 15px;"><strong>3. Passenger Manifesto</strong><br>
            Only passengers registered in this manifesto are permitted.</p>

        </div>

        <button type="button" onclick="closeTermsModal()" style="margin-top:25px; width:100%; padding:12px; background:#2563eb; color:white; border:none; border-radius:8px; font-weight:700; cursor:pointer;">I Have Read the Terms</button>
    </div>
</div>

            <div class="price-summary">
    <!-- NEW: Payment Option Selector -->
    <div style="margin-bottom: 25px; padding: 15px; background: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0;">
        <label style="font-size: 13px; margin-bottom: 10px;">Choose Payment Option:</label>
        <div style="display: flex; gap: 20px;">
            <label style="display: flex; align-items: center; cursor: pointer; text-transform: none; color: #1e293b;">
                <input type="radio" name="payment_type" value="downpayment" checked onchange="togglePayment(this)" style="width: 18px; height: 18px; margin-right: 8px;">
                <span style="font-size: 14px; font-weight: 600;">Downpayment (20%)</span>
            </label>
            <label style="display: flex; align-items: center; cursor: pointer; text-transform: none; color: #1e293b;">
                <input type="radio" name="payment_type" value="full" onchange="togglePayment(this)" style="width: 18px; height: 18px; margin-right: 8px;">
                <span style="font-size: 14px; font-weight: 600;">Full Payment</span>
            </label>
        </div>
    </div>

    <div class="row-total">
        <span style="color: #64748b;">Package Total:</span>
        <span style="font-weight: 600;">₱{{ number_format($tour->price, 2) }}</span>
    </div>

    <div class="row-total">
        <!-- Dynamic Labels and Prices -->
        <span id="payment-label" style="font-size: 18px; font-weight: 700;">Downpayment (20%):</span>
        <span id="payment-amount" style="font-size: 26px; font-weight: 800; color: #10b981;">₱{{ number_format($tour->price * 0.20, 2) }}</span>
    </div>

    <button type="submit" id="pay_button" class="pay-btn" disabled>
        <i class="fas fa-lock" style="margin-right: 8px;"></i>
        <span id="btn-text">Pay Downpayment via PayMongo</span>
    </button>
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
    }

    function closeTermsModal() {
        document.getElementById('termsModal').style.display = 'none';
    }

    // --- PAYMENT TOGGLE ---
    function togglePayment(radio) {
        const tourPrice = {{ $tour->price }};
        const downpayment = tourPrice * 0.20;

        const label = document.getElementById('payment-label');
        const amount = document.getElementById('payment-amount');
        const btnText = document.getElementById('btn-text');

        if (radio.value === 'full') {
            label.innerText = 'Full Payment Total:';
            amount.innerText = '₱' + tourPrice.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
            btnText.innerText = 'Pay Full Amount via PayMongo';
        } else {
            label.innerText = 'Downpayment (20%):';
            amount.innerText = '₱' + downpayment.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
            btnText.innerText = 'Pay Downpayment via PayMongo';
        }
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

    // --- TERMS CHECKBOX LOGIC ---
    const checkbox = document.getElementById('terms_agree');
    const payBtn = document.getElementById('pay_button');

    checkbox.addEventListener('change', function() {
        if (this.checked) {
            payBtn.disabled = false;
            payBtn.style.background = '#2563eb';
            payBtn.style.cursor = 'pointer';
            payBtn.style.boxShadow = '0 4px 15px rgba(37, 99, 235, 0.3)';
        } else {
            payBtn.disabled = true;
            payBtn.style.background = '#94a3b8';
            payBtn.style.cursor = 'not-allowed';
            payBtn.style.boxShadow = 'none';
        }
    });

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
