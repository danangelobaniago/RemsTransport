<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#2563eb">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <title>Profile | Rem's Transport</title>
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/icons/icon-192.svg">
    <link rel="stylesheet" href="@vasset('css/profile.css')">
    <link rel="stylesheet" href="@vasset('css/responsive.css')">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>

<!-- NAVBAR -->
<div class="navbar">
    <div class="topbar">
        <a href="/" class="logo">Rem's Transport</a>
    </div>
    <nav>
        <a href="/">Home</a>
        <a href="/my-bookings">My Bookings</a>
        <span class="user-name">
            <i class="fa fa-user-circle"></i>
            {{ auth()->user()->first_name }}
        </span>
        <form action="/logout" method="POST" class="logout-form">
            @csrf
            <button type="submit" class="logout-btn">Logout</button>
        </form>
    </nav>
</div>

<!-- MAIN -->
<div class="container">
    <div class="profile-stack">

        @if(session('success'))
            <div class="alert-banner success"><i class="fa fa-check-circle"></i> {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert-banner error"><i class="fa fa-exclamation-circle"></i> {{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="alert-banner error">
                <i class="fa fa-exclamation-circle"></i>
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- UPDATE PROFILE -->
        <div class="card">
            <h2><i class="fa fa-user-circle section-icon"></i> My Profile</h2>

            <form method="POST" action="/profile/update">
                @csrf

                <div class="field-row">
                    <div class="field">
                        <label>First Name</label>
                        <input type="text" name="first_name" value="{{ auth()->user()->first_name }}" maxlength="30" oninput="allowLettersOnly(this)">
                    </div>
                    <div class="field">
                        <label>Middle Name</label>
                        <input type="text" name="middle_name" value="{{ auth()->user()->middle_name }}" maxlength="30" oninput="allowLettersOnly(this)">
                    </div>
                </div>

                <label>Last Name</label>
                <input type="text" name="last_name" value="{{ auth()->user()->last_name }}" maxlength="30" oninput="allowLettersOnly(this)">

                <label>Email</label>
                <input type="email" name="email" value="{{ auth()->user()->email }}" required>

                <div class="field-row">
                    <div class="field">
                        <label>Contact Number</label>
                        <input type="text" name="phone_number" value="{{ auth()->user()->phone_number }}"
                               maxlength="11" oninput="allowNumbersOnly(this)" pattern="^09\d{9}$"
                               title="Must start with 09 and be 11 digits" required>
                    </div>
                    <div class="field">
                        <label>Birthday</label>
                        <input type="date" name="birthday" value="{{ auth()->user()->birthday }}"
                               max="{{ date('Y-m-d') }}" required>
                    </div>
                </div>

                <button type="submit"><i class="fa fa-check"></i> Update Profile</button>
            </form>
        </div>

        <!-- CHANGE PASSWORD -->
        <div class="card">
            <h2><i class="fa fa-lock section-icon"></i> Change Password</h2>
            <form method="POST" action="/profile/password">
                @csrf
                <label>Current Password</label>
                <div class="input-box">
                    <input type="password" id="currentPassword" name="current_password" required>
                    <i class="fa fa-eye toggle-password" data-target="currentPassword"></i>
                </div>

                <label>New Password</label>
                <div class="input-box">
                    <input type="password" id="newPassword" name="password" required>
                    <i class="fa fa-eye toggle-password" data-target="newPassword"></i>
                </div>

                <div class="strength-meter">
                    <div id="strength-bar"></div>
                </div>
                <p id="strength-text"></p>

                <label>Confirm Password</label>
                <div class="input-box">
                    <input type="password" id="confirmPassword" name="password_confirmation" required>
                    <i class="fa fa-eye toggle-password" data-target="confirmPassword"></i>
                </div>

                <button type="submit" class="btn-secondary"><i class="fa fa-key"></i> Change Password</button>
            </form>
        </div>

        <!-- DELETE ACCOUNT -->
        <div class="card danger-zone">
            <h2><i class="fa fa-triangle-exclamation section-icon"></i> Delete Account</h2>
            <p class="danger-text">
                This permanently anonymizes your profile (name, email, and phone number) and signs you out for good —
                this cannot be undone. You can't delete your account while you have an active or upcoming booking;
                wait until it's completed or cancel it first.
            </p>
            <button type="button" class="btn-danger" onclick="openDeleteModal()"><i class="fa fa-trash"></i> Delete My Account</button>
        </div>

    </div>
</div>

<!-- DELETE ACCOUNT MODAL -->
<div class="modal-overlay" id="deleteAccountModal">
    <div class="modal-box">
        <button type="button" class="modal-close" onclick="closeDeleteModal()">&times;</button>
        <h3><i class="fa fa-triangle-exclamation" style="color:#ef4444;"></i> Confirm Account Deletion</h3>
        <p>This cannot be undone. Enter your password to confirm.</p>
        <form method="POST" action="/profile/delete">
            @csrf
            <div class="input-box">
                <input type="password" id="deletePassword" name="password" placeholder="Your password" required>
                <i class="fa fa-eye toggle-password" data-target="deletePassword"></i>
            </div>
            <div class="modal-btn-row">
                <button type="button" class="btn-secondary" onclick="closeDeleteModal()">Cancel</button>
                <button type="submit" class="btn-danger">Yes, Delete My Account</button>
            </div>
        </form>
    </div>
</div>

<!-- CONTACT SECTION -->
<section class="contact">
    <h2>Contact Us</h2>
    <p class="contact-sub">We’re here to help you anytime</p>
    <div class="contact-container">
        <div class="contact-card">
            <i class="fa fa-phone"></i>
            <h4>Phone</h4>
            <p>+63 912 345 6789</p>
        </div>
        <div class="contact-card">
            <i class="fa fa-envelope"></i>
            <h4>Email</h4>
            <p>remstransport1@gmail.com</p>
        </div>
        <div class="contact-card">
            <i class="fa fa-map-marker-alt"></i>
            <h4>Location</h4>
            <p>Quezon City, Philippines</p>
        </div>
    </div>
</section>

<!-- FOOTER -->
<div class="footer">
    © 2026 Rem's Transport | All Rights Reserved
</div>

<script>
function allowLettersOnly(input) {
    input.value = input.value.replace(/[^A-Za-z\s]/g, '').replace(/^\s+/, '').slice(0, 30);
}
function allowNumbersOnly(input) {
    input.value = input.value.replace(/[^0-9]/g, '');
}
function openDeleteModal() { document.getElementById('deleteAccountModal').classList.add('open'); }
function closeDeleteModal() { document.getElementById('deleteAccountModal').classList.remove('open'); }

document.addEventListener("DOMContentLoaded", function () {
    const newPass = document.getElementById("newPassword");
    const confirmPass = document.getElementById("confirmPassword");

    confirmPass.addEventListener("input", function () {
        confirmPass.style.borderColor = (newPass.value !== confirmPass.value) ? "#ef4444" : "#22c55e";
    });

    document.querySelectorAll(".toggle-password").forEach(icon => {
        icon.addEventListener("click", function () {
            const input = document.getElementById(this.dataset.target);
            input.type = (input.type === "password") ? "text" : "password";
            this.classList.toggle("fa-eye");
            this.classList.toggle("fa-eye-slash");
        });
    });

    const bar = document.getElementById("strength-bar");
    const text = document.getElementById("strength-text");

    newPass.addEventListener("input", function () {
        let val = this.value;
        let strength = 0;
        if (val.length >= 8) strength++;
        if (/[A-Z]/.test(val)) strength++;
        if (/[0-9]/.test(val)) strength++;
        if (/[^A-Za-z0-9]/.test(val)) strength++;

        const colors = ["", "#ef4444", "#f59e0b", "#3b82f6", "#22c55e"];
        const labels = ["", "Weak", "Fair", "Good", "Strong"];

        bar.style.width = (strength * 25) + "%";
        bar.style.background = colors[strength];
        text.innerText = labels[strength];
    });

    document.getElementById('deleteAccountModal').addEventListener('click', function (e) {
        if (e.target === this) closeDeleteModal();
    });
});
</script>
<script src="/js/pwa.js"></script>
</body>
</html>
