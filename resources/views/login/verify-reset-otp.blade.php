<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Reset Code | Rem's Transport</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            height: 100vh;
            background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)),
            url('https://global.toyota/pages/models/images/20191018/kv/hiace_w1920_01.jpg') no-repeat center/cover;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
        }

        .card {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 24px;
            padding: 40px;
            width: 100%;
            max-width: 400px;
            text-align: center;
            color: #fff;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            position: relative;
        }

        .back-link {
            position: absolute;
            top: 20px;
            left: 20px;
            color: rgba(255, 255, 255, 0.5);
            text-decoration: none;
            font-size: 14px;
            transition: 0.3s;
        }

        .back-link:hover {
            color: #fff;
            transform: translateX(-3px);
        }

        h2 { font-size: 26px; font-weight: 700; margin-bottom: 8px; letter-spacing: -0.5px; }
        .subtitle { font-size: 13px; color: rgba(255,255,255,0.7); margin-bottom: 30px; line-height: 1.5; }

        /* OTP INPUT */
        .otp-input {
            width: 100%;
            padding: 15px;
            font-size: 24px;
            text-align: center;
            letter-spacing: 12px;
            font-weight: 800;
            border-radius: 12px;
            border: 2px solid rgba(255,255,255,0.1);
            background: rgba(255,255,255,0.9);
            color: #111827;
            outline: none;
            margin-bottom: 20px;
            transition: 0.3s;
        }

        .otp-input:focus {
            border-color: #2f6fed;
            box-shadow: 0 0 0 4px rgba(47, 111, 237, 0.3);
        }

        /* BUTTONS */
        .btn-verify {
            width: 100%;
            padding: 16px;
            border: none;
            border-radius: 12px;
            background: #2f6fed;
            color: white;
            font-weight: 700;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-verify:hover {
            background: #1e4fd8;
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.2);
        }

        /* RESEND SECTION */
        .resend-container {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid rgba(255,255,255,0.1);
        }

        .resend-text { font-size: 13px; color: rgba(255,255,255,0.6); margin-bottom: 10px; }

        .btn-resend {
            background: none;
            border: none;
            color: #00e6c3;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            text-decoration: underline;
            transition: 0.3s;
        }

        .btn-resend:disabled {
            color: #888 !important;
            cursor: not-allowed !important;
            text-decoration: none;
            opacity: 0.5;
            pointer-events: none;
        }

        .timer {
            margin-top: 8px;
            font-size: 12px;
            font-weight: 600;
            color: #facc15;
        }

        /* ALERTS */
        .alert {
            padding: 12px;
            border-radius: 10px;
            font-size: 13px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        .alert-error { background: rgba(255, 107, 107, 0.2); color: #ff8e8e; border: 1px solid rgba(255,107,107,0.3); }
        .alert-success { background: rgba(74, 222, 128, 0.2); color: #86efac; border: 1px solid rgba(74,222,128,0.3); }
    </style>
</head>
<body>

<div class="card">
    <a href="/login" class="back-link">
        <i class="fas fa-arrow-left"></i> Back
    </a>

    <i class="fas fa-lock-open" style="font-size: 40px; color: #2f6fed; margin-bottom: 20px; margin-top: 20px;"></i>
    <h2>Verify Reset Code</h2>
    <p class="subtitle">A 6-digit code has been sent to your email for your password reset. Please enter it below.</p>

    @if(session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="POST" action="/verify-reset-otp">
        @csrf
        <input
            type="text"
            name="otp"
            maxlength="6"
            class="otp-input"
            placeholder="000000"
            required
            autofocus
            autocomplete="off"
            inputmode="numeric"
            oninput="this.value = this.value.replace(/[^0-9]/g, '')"
        >
        <button type="submit" class="btn-verify">Verify Reset Code</button>
    </form>

    <div class="resend-container">
        <p class="resend-text">Didn’t receive the code?</p>

        <form method="POST" action="{{ route('resend.otp') }}">
            @csrf
            <button type="submit" id="resendBtn" class="btn-resend" disabled>
                Resend New Code
            </button>
        </form>

        <div class="timer" id="timer">Resend available in 01:00</div>
    </div>
</div>

<script>
    const resendBtn = document.getElementById("resendBtn");
    const timerText = document.getElementById("timer");
    let time = 60; // 60 Seconds

    function startTimer() {
        // Ensure button is visually and logically disabled
        resendBtn.disabled = true;

        const countdown = setInterval(() => {
            time--;

            let seconds = time % 60;
            timerText.innerText = `Resend available in 00:${seconds < 10 ? "0" : ""}${seconds}`;

            if (time <= 0) {
                clearInterval(countdown);
                resendBtn.disabled = false;
                timerText.innerText = "You can now resend the code";
                timerText.style.color = "#4ade80"; // Turn green when ready
            }
        }, 1000);
    }

    // Start the countdown on page load
    window.onload = startTimer;
</script>

</body>
</html>
