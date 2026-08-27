<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | Rem's Transport</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }

        body {
            height: 100vh;
            background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)),
            url('https://global.toyota/pages/models/images/20191018/kv/hiace_w1920_01.jpg') no-repeat center/cover;
            display: flex; justify-content: center; align-items: center;
        }

        .card {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 40px;
            border-radius: 20px;
            width: 380px;
            text-align: center;
            color: #fff;
            box-shadow: 0 15px 35px rgba(0,0,0,0.4);
        }

        /* BRANDING INSIDE BOX */
        .brand-header {
            display: block;
            text-decoration: none;
            color: #fff;
            font-size: 20px;
            font-weight: 700;
            letter-spacing: 1.5px;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            transition: 0.3s;
        }

        .brand-header i {
            color: #16a34a; /* Matches your green button */
            margin-right: 8px;
        }

        .brand-header:hover {
            color: #16a34a;
        }

        .card h2 { margin-bottom: 8px; font-size: 24px; }
        .card p { font-size: 13px; opacity: 0.7; margin-bottom: 25px; line-height: 1.4; }

        .input-box { position: relative; width: 100%; margin-bottom: 15px; text-align: left; }

        .input-box input {
            width: 100%; padding: 12px; padding-right: 40px;
            border-radius: 8px; border: none; outline: none; font-size: 14px;
            background: rgba(255,255,255,0.9);
            color: #111;
        }

        .toggle-password {
            position: absolute;
            right: 12px;
            top: 22px;
            transform: translateY(-50%);
            cursor: pointer;
            color: #666;
            z-index: 10;
        }

        /* STRENGTH METER */
        .strength-meter {
            height: 4px; width: 100%; background: rgba(255,255,255,0.2);
            margin-top: 8px; border-radius: 2px; overflow: hidden;
        }
        .strength-bar { height: 100%; width: 0; transition: 0.3s; }

        .requirements {
            text-align: left; font-size: 11px; margin-top: 10px; list-style: none;
            color: rgba(255,255,255,0.6);
        }
        .requirements li.valid { color: #4ade80; }
        .requirements li.valid::before { content: "✓ "; }

        .btn {
            width: 100%; padding: 13px; border: none; border-radius: 8px;
            background: #16a34a; color: white; font-weight: 600; cursor: pointer; transition: 0.3s; margin-top: 10px;
        }
        .btn:disabled { background: #334155; cursor: not-allowed; opacity: 0.7; }
        .btn:hover:not(:disabled) { background: #15803d; transform: translateY(-1px); }

        .error { color: #f87171; margin-top: 10px; font-size: 13px; }
    </style>
</head>

<body>
    <div class="card">
        <a href="/login" class="brand-header">
         REM'S TRANSPORT
        </a>

        <h2>Reset Password</h2>
        <p>Ensure your new password is secure and easy for you to remember.</p>

        <form method="POST" action="/reset-password-otp" id="resetForm">
            @csrf
            <input type="hidden" name="email" value="{{ session('email') }}">

            <div class="input-box">
                <input type="password" name="password" id="password" placeholder="New Password" required>
                <i class="fas fa-eye toggle-password" onclick="togglePass('password', this)"></i>

                <div class="strength-meter"><div class="strength-bar" id="strengthBar"></div></div>
                <ul class="requirements">
                    <li id="length">At least 8 characters</li>
                    <li id="upper">At least one uppercase letter</li>
                    <li id="number">At least one number (0–9)</li>
                    <li id="symbol">At least one special character (!@#$%^&*)</li>
                </ul>
            </div>

            <div class="input-box">
                <input type="password" name="password_confirmation" id="password_confirmation" placeholder="Confirm Password" required>
                <i class="fas fa-eye toggle-password" onclick="togglePass('password_confirmation', this)"></i>
            </div>

            <button type="submit" class="btn" id="submitBtn" disabled>Update Password</button>
        </form>

        @if(session('error')) <p class="error"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</p> @endif
    </div>

<script>
    const password = document.getElementById('password');
    const confirm = document.getElementById('password_confirmation');
    const strengthBar = document.getElementById('strengthBar');
    const submitBtn = document.getElementById('submitBtn');

    const reqList = {
        length: document.getElementById('length'),
        upper:  document.getElementById('upper'),
        number: document.getElementById('number'),
        symbol: document.getElementById('symbol'),
    };

    password.addEventListener('input', () => {
        const val = password.value;
        const hasLength = val.length >= 8;
        const hasUpper  = /[A-Z]/.test(val);
        const hasNumber = /[0-9]/.test(val);
        const hasSymbol = /[!@#$%^&*\-_=+[\]{};:'",.<>?/\\|`~]/.test(val);

        reqList.length.className = hasLength ? 'valid' : '';
        reqList.upper.className  = hasUpper  ? 'valid' : '';
        reqList.number.className = hasNumber ? 'valid' : '';
        reqList.symbol.className = hasSymbol ? 'valid' : '';

        const strength = [hasLength, hasUpper, hasNumber, hasSymbol].filter(Boolean).length * 25;
        strengthBar.style.width = strength + '%';
        if (strength <= 50) strengthBar.style.background = '#f87171';
        else if (strength <= 75) strengthBar.style.background = '#fbbf24';
        else strengthBar.style.background = '#4ade80';

        validateForm();
    });

    confirm.addEventListener('input', validateForm);

    function validateForm() {
        const val = password.value;
        const isStrong = val.length >= 8
            && /[A-Z]/.test(val)
            && /[0-9]/.test(val)
            && /[!@#$%^&*\-_=+[\]{};:'",.<>?/\\|`~]/.test(val);
        const matches = val === confirm.value && confirm.value !== "";
        submitBtn.disabled = !(isStrong && matches);
    }

    function togglePass(inputId, icon) {
        const inputField = document.getElementById(inputId);
        if (inputField.type === "password") {
            inputField.type = "text";
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            inputField.type = "password";
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
</script>
</body>
</html>
