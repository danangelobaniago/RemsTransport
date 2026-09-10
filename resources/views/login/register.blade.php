<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Create Account | Rem's Transport</title>

<link rel="stylesheet" href="@vasset('css/register.css')">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

</head>

<body>

<div class="register-card">

<h1>Rem's Transport</h1>
<p>Create your account</p>

{{-- ERROR DISPLAY --}}
@if ($errors->any())
<div style="color:red;">
    @foreach ($errors->all() as $error)
        <p>{{ $error }}</p>
    @endforeach
</div>
@endif

<form method="POST" action="/register">
    @csrf

    <script>
        function allowLettersOnly(input) {
            input.value = input.value.replace(/[^A-Za-z\s]/g, '');
        }

        function allowNumbersOnly(input) {
            // Remove anything not a number
            input.value = input.value.replace(/[^0-9]/g, '');
            // Ensure it always starts with 09
            if (input.value.length >= 2 && !input.value.startsWith('09')) {
                input.value = '09' + input.value.replace(/^0+/, '').replace(/^9+/, '');
            }
        }
    </script>

    <label>Full Name <span class="req">*</span></label>
    <!-- Name Row using Flexbox -->
    <div class="name-row">
        <input type="text" name="first_name" value="{{ old('first_name') }}" placeholder="First" title="First name" required maxlength="20" oninput="allowLettersOnly(this)">
        <input type="text" name="middle_name" value="{{ old('middle_name') }}" placeholder="Middle" title="Middle name (optional)" maxlength="20" oninput="allowLettersOnly(this)">
        <input type="text" name="last_name" value="{{ old('last_name') }}" placeholder="Last" title="Last name" required maxlength="20" oninput="allowLettersOnly(this)">
        <input type="text" name="suffix" value="{{ old('suffix') }}" placeholder="Suffix" title="Suffix, e.g. Jr. (optional)" maxlength="10" list="suffix-options" class="suffix-input">
        <datalist id="suffix-options">
            <option value="Jr."></option>
            <option value="Sr."></option>
            <option value="II"></option>
            <option value="III"></option>
            <option value="IV"></option>
        </datalist>
    </div>

    <label>Email Address <span class="req">*</span></label>
    <input type="email" name="email" value="{{ old('email') }}" placeholder="Enter email" required>

    <label>Birthday <span class="req">*</span></label>
    <!-- max = latest birthday that still makes the user at least 18 -->
    <input type="date" name="birthday" value="{{ old('birthday') }}" max="{{ now()->subYears(18)->format('Y-m-d') }}" required>
    <small class="field-note">You must be at least 18 years old to create an account.</small>

    <label>Cellphone Number <span class="req">*</span></label>
    <input type="text"
           name="phone_number"
           value="{{ old('phone_number') }}"
           placeholder="09XXXXXXXXX"
           maxlength="11"
           pattern="^09\d{9}$"
           oninput="allowNumbersOnly(this)"
           title="Must start with 09 and be exactly 11 digits"
           required>

<label>Password <span class="req">*</span></label>
<div class="input-box">
<input type="password" id="password" name="password" placeholder="Create password" required>
<i class="fa fa-eye toggle-password" id="togglePassword"></i>
</div>



<!-- PASSWORD STRENGTH -->
<div class="strength-meter">
    <div id="strength-bar"></div>
</div>
<p id="strength-text"></p>

<!-- PASSWORD REQUIREMENTS -->
<div class="password-requirements">
    <p id="req-length"><i class="fa fa-circle-xmark"></i> At least 8 characters</p>
    <p id="req-upper"><i class="fa fa-circle-xmark"></i> At least one uppercase letter (A-Z)</p>
    <p id="req-number"><i class="fa fa-circle-xmark"></i> At least one number (0-9)</p>
    <p id="req-special"><i class="fa fa-circle-xmark"></i> At least one special character (!@#$...)</p>
</div>

<label>Confirm Password <span class="req">*</span></label>
<div class="input-box">
<input type="password" id="confirmPassword" name="password_confirmation" placeholder="Confirm password" required>
<i class="fa fa-eye toggle-password" id="toggleConfirm"></i>
</div>

<input type="hidden" name="terms" id="termsField" value="{{ old('terms') }}">

<div class="terms-text" id="termsStatus">
    <span id="termsPending">
        Please review our
        <a href="#" onclick="openTerms(); return false;">Terms of Service &amp; Privacy Policy</a>
        before creating an account.
    </span>
    <span id="termsAccepted" style="display:none;">
        <i class="fa fa-circle-check" style="color:#22c55e;"></i>
        You have accepted the
        <a href="#" onclick="openTerms(); return false;">Terms of Service &amp; Privacy Policy</a>.
    </span>
</div>


<button type="submit">Create Account</button>

<a href="/login" class="back">Back to Login</a>

</form>

</div>

<div id="openTerms" class="modal">
    <div class="modal-content">

        <span class="close" onclick="closeModal()">&times;</span>

        <h3>Terms of Service &amp; Privacy Policy</h3>

        <div class="modal-body">

            <p>
                Welcome to Rem's Transport. These Terms of Service ("Terms") govern your access to and
                use of the Rem's Transport website, booking platform, and related services (collectively,
                the "Service"). By creating an account or making a booking, you confirm that you have
                read, understood, and agree to be bound by these Terms and by the Privacy Policy below.
                If you do not agree, please do not use the Service.
            </p>

            <h4>1. Eligibility</h4>
            <p>
                You must be at least 18 years old, or the age of legal majority in your place of
                residence, to create an account and enter into bookings. By registering, you represent
                that the information you provide — including your name, birthday, email address, and
                mobile number — is accurate, current, and complete, and that you will keep it updated.
            </p>

            <h4>2. Accounts and Security</h4>
            <p>
                You are responsible for maintaining the confidentiality of your account credentials and
                one-time verification codes, and for all activity that occurs under your account. Do not
                share your password or OTP with anyone. Notify us immediately if you suspect any
                unauthorized use of your account. We may suspend or terminate accounts that show signs
                of fraud, abuse, or repeated policy violations.
            </p>

            <h4>3. Bookings and Trips</h4>
            <p>
                A booking is a request for a van rental, joiner trip seat, or tour package and is only
                confirmed once payment (or the required downpayment) is received and you receive a
                confirmation. Trip schedules, routes, pick-up points, vehicle assignments, and driver
                assignments may change due to weather, road conditions, mechanical issues, or
                operational needs. We will make reasonable efforts to notify you of material changes.
            </p>
            <p>
                Passenger counts must not exceed the seating capacity stated for the vehicle or package.
                You are responsible for the conduct of all passengers included in your booking, and for
                arriving at the agreed pick-up point on time. The driver may decline to transport
                passengers who are intoxicated, disruptive, or unsafe.
            </p>

            <h4>4. Payments</h4>
            <p>
                Prices are shown in Philippine Peso and are payable through our third-party payment
                provider or, where offered, in cash to the driver. Downpayments reserve your slot;
                the remaining balance is due as stated at checkout or before the trip begins. Fees for
                additional stops, waiting time, extended hours, or damage to the vehicle may be charged
                separately. Failure to settle outstanding balances may result in cancellation and may
                affect future bookings.
            </p>

            <h4>5. Cancellations, Rescheduling, and Refunds</h4>
            <p>
                Cancellation and rescheduling requests must be made through your account as early as
                possible. Refund eligibility depends on how far in advance you cancel and on the
                specific van, joiner trip, or tour policy shown at the time of booking. Downpayments
                may be non-refundable. Refunds, when approved, are returned through the original
                payment method and may take several business days to process. No-shows are generally
                not eligible for a refund.
            </p>

            <h4>6. Acceptable Use</h4>
            <p>
                You agree not to misuse the Service, including by submitting false information, making
                fraudulent bookings, attempting to access other users' accounts, interfering with the
                platform's security or availability, or using the Service for any unlawful purpose.
            </p>

            <h4>7. Liability</h4>
            <p>
                Rem's Transport provides the Service on a commercially reasonable basis. To the extent
                permitted by law, we are not liable for indirect or consequential losses, for delays or
                losses caused by events beyond our reasonable control, or for personal belongings left
                in a vehicle. Nothing in these Terms limits liability that cannot be limited under
                applicable law.
            </p>

            <h4>8. Changes to These Terms</h4>
            <p>
                We may update these Terms from time to time. Material changes will be posted on this
                page. Continued use of the Service after changes take effect means you accept the
                revised Terms.
            </p>

            <h3 style="margin-top:22px;">Privacy Policy</h3>

            <h4>Information We Collect</h4>
            <p>
                We collect the information you provide when you register and book — your name (and
                suffix, if any), birthday, email address, mobile number, and booking and payment
                details — as well as limited technical information such as your IP address for security
                and fraud prevention.
            </p>

            <h4>How We Use Your Information</h4>
            <p>
                Your information is used to create and secure your account, process and manage
                bookings, generate invoices and receipts, coordinate trips with drivers, send
                verification codes and booking updates, and comply with legal obligations. We may send
                occasional service or promotional messages; you can opt out of promotional messages at
                any time.
            </p>

            <h4>Sharing</h4>
            <p>
                We share information only as needed to operate the Service — for example, trip details
                with the assigned driver, and payment information with our payment provider. We do not
                sell, rent, or lease your personal data to third parties. We may disclose information
                if required by law or to protect the rights and safety of our users and staff.
            </p>

            <h4>Data Retention and Your Rights</h4>
            <p>
                We keep your information for as long as your account is active or as needed to provide
                the Service and meet legal and accounting requirements. You may request access to,
                correction of, or deletion of your personal data by contacting us. Verified deletion
                requests are processed within 30 days, except where we are required to retain certain
                records.
            </p>

            <h4>Contact</h4>
            <p>
                For questions about these Terms or the Privacy Policy, or to make a data request,
                contact Rem's Transport at <strong>remstransport1@gmail.com</strong>.
            </p>

        </div>

        <button onclick="agreeTerms()">I Understand and Accept</button>

    </div>
</div>

<script src="@vasset('js/register.js')"></script>

</body>
</html>
