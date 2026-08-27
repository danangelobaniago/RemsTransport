<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Create Account | Rem's Transport</title>

<link rel="stylesheet" href="{{ asset('css/register.css') }}">

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

    <label>Full Name</label>
    <!-- Name Row using Flexbox -->
    <div style="display: flex; gap: 10px; margin-bottom: 15px;">
        <input type="text" name="first_name" placeholder="First Name" required maxlength="20" oninput="allowLettersOnly(this)" style="flex: 1;">
        <input type="text" name="middle_name" placeholder="Middle Name" maxlength="20" oninput="allowLettersOnly(this)" style="flex: 1;">
        <input type="text" name="last_name" placeholder="Last Name" required maxlength="20" oninput="allowLettersOnly(this)" style="flex: 1;">
    </div>

    <label>Email Address</label>
    <input type="email" name="email" placeholder="Enter email" required>

    <label>Birthday</label>
    <!-- max attribute blocks future dates -->
    <input type="date" name="birthday" max="{{ date('Y-m-d') }}" required>

    <label>Cellphone Number</label>
    <input type="text"
           name="phone_number"
           placeholder="09XXXXXXXXX"
           maxlength="11"
           pattern="^09\d{9}$"
           oninput="allowNumbersOnly(this)"
           title="Must start with 09 and be exactly 11 digits"
           required>

<label>Password</label>
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

<label>Confirm Password</label>
<div class="input-box">
<input type="password" id="confirmPassword" name="password_confirmation" placeholder="Confirm password" required>
<i class="fa fa-eye toggle-password" id="toggleConfirm"></i>
</div>

<div class="terms-text">
    <label class="terms-label">
        <input type="checkbox" name="terms" required>
        <span>
            I accept the
            <a href="#" onclick="openTerms(); return false;">Terms of Service</a>
            and Privacy Policy
        </span>
    </label>
</div>


<button type="submit">Create Account</button>

<a href="/login" class="back">Back to Login</a>

</form>

</div>

<div id="openTerms" class="modal">
    <div class="modal-content">

        <span class="close" onclick="closeModal()">&times;</span>

        <div class="modal-body">

            <h3>Terms & Conditions</h3>

<div class="modal-body">
    <p><strong>Privacy Policy & Terms</strong></p>

     <p>
    At Rem's Transport, we are committed to safeguarding your personal information and ensuring your privacy.
    The information we collect will be used solely for contacting the client who initiated the request and for generating an invoice.
    </p>

    <p>
    Your name, contact number, email, and address are securely stored in our customer database.
    We do not share, sell, or lease your data to third parties.
    </p>

    <p>
    Occasionally, we may send updates regarding special offers or compliance information.
    You can unsubscribe anytime.
    </p>

    <h4>Data Deletion Requests</h4>

    <p>
    If you wish to have your personal data deleted, please contact us at remstransport1.com
    We will process your request within 30 days.
    </p>

</div>

        <button onclick="agreeTerms()">I Understand</button>

    </div>
</div>

<script src="{{ asset('js/register.js') }}"></script>

</body>
</html>
