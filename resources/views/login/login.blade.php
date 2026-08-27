<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#2563eb">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="Rem's Transport">
    <title>Login | Rem's Transport</title>
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/icons/icon-192.svg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', -apple-system, sans-serif; }

        body {
            height: 100vh;
            display: flex;
            background: #111827;
        }

        /* ── LEFT PANEL ── */
        .left-panel {
            flex: 1;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 50px 52px;
        }

        .left-panel .bg-image {
            position: absolute;
            inset: 0;
            background-size: cover;
            background-position: center;
            z-index: 0;
        }

        .left-panel .overlay {
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.52);
            z-index: 1;
        }

        .left-panel > * { position: relative; z-index: 2; }

        .brand-mark {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .brand-icon {
            width: 42px; height: 42px;
            background: #2563eb;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px; color: white;
        }
        .brand-name { font-size: 18px; font-weight: 700; color: white; letter-spacing: -0.3px; }

        .left-content { flex: 1; display: flex; flex-direction: column; justify-content: center; padding: 40px 0; }

        .left-tagline {
            font-size: 42px;
            font-weight: 800;
            color: white;
            line-height: 1.15;
            letter-spacing: -1px;
            margin-bottom: 20px;
        }
        .left-tagline span { color: #60a5fa; }

        .left-desc {
            font-size: 15px;
            color: rgba(255,255,255,0.55);
            line-height: 1.7;
            max-width: 360px;
            margin-bottom: 40px;
        }

        .features {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }
        .feature-item {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 14px;
            color: rgba(255,255,255,0.7);
        }
        .feature-icon {
            width: 32px; height: 32px;
            background: rgba(37,99,235,0.3);
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            color: #93c5fd; font-size: 13px;
            flex-shrink: 0;
        }

        .left-footer {
            font-size: 12px;
            color: rgba(255,255,255,0.25);
        }

        /* ── RIGHT PANEL ── */
        .right-panel {
            width: 460px;
            flex-shrink: 0;
            background: #ffffff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 52px 48px;
            position: relative;
        }

        .form-header { margin-bottom: 36px; }
        .form-header .welcome { font-size: 13px; font-weight: 600; color: #2563eb; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; }
        .form-header h2 { font-size: 28px; font-weight: 800; color: #0f172a; letter-spacing: -0.5px; margin-bottom: 8px; }
        .form-header p { font-size: 14px; color: #64748b; }

        .form-group { margin-bottom: 20px; }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 7px;
        }

        .input-wrap {
            position: relative;
        }
        .input-wrap .icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 14px;
            pointer-events: none;
        }
        .input-wrap input {
            width: 100%;
            padding: 12px 14px 12px 40px;
            border: 1.5px solid #e5e7eb;
            border-radius: 10px;
            font-size: 14px;
            color: #111827;
            background: #f9fafb;
            outline: none;
            transition: all 0.2s;
        }
        .input-wrap input:focus {
            border-color: #2563eb;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(37,99,235,0.08);
        }
        .input-wrap .toggle-eye {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            cursor: pointer;
            font-size: 14px;
            transition: color 0.2s;
        }
        .input-wrap .toggle-eye:hover { color: #2563eb; }

        .form-footer-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }
        .remember-label {
            display: flex;
            align-items: center;
            gap: 7px;
            font-size: 13px;
            color: #4b5563;
            cursor: pointer;
        }
        .remember-label input[type="checkbox"] {
            width: 15px; height: 15px;
            accent-color: #2563eb;
            cursor: pointer;
        }
        .forgot-link {
            font-size: 13px;
            font-weight: 600;
            color: #2563eb;
            text-decoration: none;
        }
        .forgot-link:hover { text-decoration: underline; }

        .btn-login {
            width: 100%;
            padding: 13px;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            letter-spacing: 0.2px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .btn-login:hover {
            background: #1d4ed8;
            box-shadow: 0 4px 16px rgba(37,99,235,0.35);
            transform: translateY(-1px);
        }
        .btn-login:active { transform: translateY(0); }

        .divider {
            display: flex;
            align-items: center;
            gap: 14px;
            margin: 24px 0;
            font-size: 12px;
            color: #d1d5db;
        }
        .divider::before, .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #f3f4f6;
        }

        .register-row {
            text-align: center;
            font-size: 14px;
            color: #6b7280;
        }
        .register-row a {
            color: #2563eb;
            font-weight: 700;
            text-decoration: none;
        }
        .register-row a:hover { text-decoration: underline; }

        .staff-link {
            text-align: center;
            margin-top: 14px;
            font-size: 12px;
            color: #d1d5db;
        }
        .staff-link a {
            color: #9ca3af;
            text-decoration: none;
            font-weight: 600;
        }
        .staff-link a:hover { color: #2563eb; }

        /* ERROR / SUCCESS */
        .alert-box {
            padding: 12px 14px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 9px;
        }
        .alert-error   { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
        .alert-success { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            .left-panel { display: none; }
            .right-panel { width: 100%; padding: 40px 28px; }
        }
    </style>
</head>
<body>

    <!-- LEFT BRAND PANEL -->
    <div class="left-panel">
        <div class="bg-image" style="background-image: url('{{ asset('image/hero-1.jpg') }}')"></div>
        <div class="overlay"></div>

        <div class="brand-mark">
            <span class="brand-name">Rem's Transport</span>
        </div>

        <div class="left-content">
            <h1 class="left-tagline">Travel <span>safe,</span><br>travel <span>smart.</span></h1>
            <p class="left-desc">
                Book your private van rental or join a shared trip with ease.
                Reliable service, professional drivers, and a seamless booking experience.
            </p>
            <div class="features">
                <div class="feature-item">
                    <div class="feature-icon"><i class="fas fa-shield-halved"></i></div>
                    <span>Secure online booking & payments</span>
                </div>
                <div class="feature-item">
                    <div class="feature-icon"><i class="fas fa-route"></i></div>
                    <span>Private charter & joiner trip options</span>
                </div>
                <div class="feature-item">
                    <div class="feature-icon"><i class="fas fa-clock"></i></div>
                    <span>Real-time trip status tracking</span>
                </div>
            </div>
        </div>

        <div class="left-footer">
            &copy; {{ date('Y') }} Rem's Transport. All rights reserved.
        </div>
    </div>

    <!-- RIGHT FORM PANEL -->
    <div class="right-panel">

        <div style="margin-bottom: 20px;">
            <a href="/" style="display:inline-flex; align-items:center; gap:6px; font-size:13px; font-weight:600; color:#64748b; text-decoration:none; padding:7px 14px; border:1px solid #e2e8f0; border-radius:8px; transition:0.2s;"
               onmouseover="this.style.background='#f1f5f9';this.style.color='#1e293b';"
               onmouseout="this.style.background='';this.style.color='#64748b';">
                <i class="fas fa-chevron-left" style="font-size:11px;"></i> Back to Home
            </a>
        </div>

        <div class="form-header">
            <div class="welcome">Welcome back</div>
            <h2>Sign in to your account</h2>
            <p>Enter your credentials to continue</p>
        </div>

        @if(session('error'))
            <div class="alert-box alert-error" id="lockout-timer">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            </div>
        @endif

        @if(session('success'))
            <div class="alert-box alert-success">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="/login" id="loginForm">
            @csrf
            <input type="hidden" name="role" value="customer">

            <div class="form-group">
                <label for="email">Email Address</label>
                <div class="input-wrap">
                    <i class="fas fa-envelope icon"></i>
                    <input type="email" id="email" name="email"
                           placeholder="you@example.com"
                           value="{{ old('email') }}" required autofocus>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrap">
                    <i class="fas fa-lock icon"></i>
                    <input type="password" id="password" name="password"
                           placeholder="••••••••" required minlength="8">
                    <i class="fas fa-eye toggle-eye" id="togglePassword"></i>
                </div>
            </div>

            <div class="form-footer-row">
                <label class="remember-label">
                    <input type="checkbox" name="remember"> Remember me
                </label>
                <a href="/forgot-password" class="forgot-link">Forgot password?</a>
            </div>

            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt"></i> Sign In
            </button>
        </form>

        <div class="divider">or</div>

        <div class="register-row">
            Don't have an account? <a href="/register">Create Account</a>
        </div>

        <div class="staff-link">
            <a href="/admin/login"><i class="fas fa-shield-halved" style="margin-right:4px;"></i>Admin / Driver Login</a>
        </div>

    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {

        // Toggle password visibility
        const toggle   = document.getElementById('togglePassword');
        const passInput = document.getElementById('password');
        if (toggle && passInput) {
            toggle.addEventListener('click', function () {
                const hidden = passInput.type === 'password';
                passInput.type = hidden ? 'text' : 'password';
                this.classList.toggle('fa-eye', !hidden);
                this.classList.toggle('fa-eye-slash', hidden);
            });
        }

        // Lockout countdown timer
        const timerEl = document.getElementById('lockout-timer');
        if (timerEl) {
            const match = timerEl.innerText.match(/(\d+)\s*second/);
            if (match) {
                let secs = parseInt(match[1]);
                const countdown = setInterval(() => {
                    secs--;
                    if (secs <= 0) {
                        clearInterval(countdown);
                        timerEl.className = 'alert-box alert-success';
                        timerEl.innerHTML = '<i class="fas fa-check-circle"></i> You can try logging in again.';
                    } else {
                        timerEl.innerHTML = `<i class="fas fa-exclamation-circle"></i> Too many failed attempts. Try again in ${secs} second${secs !== 1 ? 's' : ''}.`;
                    }
                }, 1000);
            }
        }

        // Input focus ring
        document.querySelectorAll('.input-wrap input').forEach(input => {
            input.addEventListener('focus',  () => input.parentElement.style.borderRadius = '10px');
            input.addEventListener('blur',   () => input.parentElement.style.boxShadow   = '');
        });

    });
    </script>
<script src="/js/pwa.js"></script>
</body>
</html>
