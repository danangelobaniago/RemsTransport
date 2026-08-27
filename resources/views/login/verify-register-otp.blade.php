<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email | Rem's Transport</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }

        body {
            height: 100vh;
            background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)),
            url('https://global.toyota/pages/models/images/20191018/kv/hiace_w1920_01.jpg') no-repeat center/cover;
            display: flex;
            justify-content: center;
            align-items: center;
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
            position: relative;
        }

        .back-btn {
            position: absolute;
            top: 20px;
            left: 20px;
            color: rgba(255, 255, 255, 0.6);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: 0.3s;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .back-btn:hover {
            color: #fff;
            transform: translateX(-3px);
        }

        .card h2 { margin: 10px 0 5px; font-weight: 600; }
        .card p { font-size: 13px; opacity: 0.7; margin-bottom: 25px; line-height: 1.5; }

        .email-badge {
            background: rgba(37, 99, 235, 0.2);
            border: 1px solid rgba(37, 99, 235, 0.4);
            border-radius: 8px;
            padding: 8px 14px;
            font-size: 13px;
            color: #93c5fd;
            margin-bottom: 20px;
            word-break: break-all;
        }

        .otp-input {
            width: 100%;
            padding: 14px;
            border-radius: 10px;
            border: none;
            outline: none;
            margin-bottom: 15px;
            text-align: center;
            font-size: 20px;
            letter-spacing: 8px;
            font-weight: 700;
            background: rgba(255, 255, 255, 0.9);
            color: #111;
        }

        .btn {
            width: 100%;
            padding: 13px;
            border: none;
            border-radius: 10px;
            background: #2563eb;
            color: white;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
            font-size: 15px;
        }

        .btn:hover:not(:disabled) {
            background: #1d4ed8;
            box-shadow: 0 5px 15px rgba(37, 99, 235, 0.3);
        }

        .resend-container {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .resend-btn {
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: #fff;
            font-size: 13px;
        }

        .resend-btn:disabled {
            opacity: 0.4;
            cursor: not-allowed;
            pointer-events: none;
        }

        .timer {
            margin-top: 10px;
            font-size: 12px;
            color: #facc15;
            font-weight: 500;
        }

        .success { color: #4ade80; margin-top: 15px; font-size: 13px; }
        .error { color: #f87171; margin-top: 15px; font-size: 13px; }
    </style>
</head>

<body>
<div class="card">
    <a href="/register" class="back-btn">
        <i class="fas fa-chevron-left"></i> Back
    </a>

    <i class="fas fa-envelope-open-text" style="font-size: 30px; color: #2563eb; margin-top: 10px;"></i>
    <h2>Verify Your Email</h2>
    <p>We sent a 6-digit code to your email address. Enter it below to complete your registration.</p>

    @if(session('reg_pending_data'))
        <div class="email-badge">
            <i class="fas fa-envelope" style="margin-right: 6px;"></i>{{ session('reg_pending_data')['email'] }}
        </div>
    @endif

    <form method="POST" action="{{ route('register.otp.post') }}">
        @csrf
        <input
            type="text"
            name="otp"
            class="otp-input"
            pattern="[0-9]*"
            inputmode="numeric"
            maxlength="6"
            placeholder="••••••"
            required
            autofocus
            oninput="this.value = this.value.replace(/[^0-9]/g, '')"
        >
        <button type="submit" class="btn">Create My Account</button>
    </form>

    <div class="resend-container">
        <form method="POST" action="{{ route('register.otp.resend') }}">
            @csrf
            <button type="submit" class="btn resend-btn" id="resendBtn" disabled>
                <i class="fas fa-sync-alt" style="margin-right: 5px;"></i> Resend Code
            </button>
        </form>
        <div class="timer" id="timer">Initializing...</div>
    </div>

    @if(session('error')) <p class="error"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</p> @endif
    @if(session('success')) <p class="success"><i class="fas fa-check-circle"></i> {{ session('success') }}</p> @endif
</div>

<script>
    const resendBtn = document.getElementById("resendBtn");
    const timerText = document.getElementById("timer");
    let time = 60;

    function startTimer() {
        resendBtn.disabled = true;

        const countdown = setInterval(() => {
            time--;
            let seconds = time % 60;
            timerText.innerText = `Resend available in 00:${seconds < 10 ? "0" : ""}${seconds}`;

            if (time <= 0) {
                clearInterval(countdown);
                resendBtn.disabled = false;
                timerText.innerText = "You can resend the code now";
                timerText.style.color = "#4ade80";
            }
        }, 1000);
    }

    window.onload = startTimer;
</script>
</body>
</html>
