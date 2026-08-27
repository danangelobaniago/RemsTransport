<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#1e293b">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <title>Staff Portal | Rem's Transport</title>
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/icons/icon-192.svg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }

        body {
            min-height: 100vh;
            display: flex;
            background: #0f172a;
        }

        /* ── LEFT PANEL ── */
        .left-panel {
            flex: 1;
            background: linear-gradient(160deg, #1e293b 0%, #0f172a 60%, #0c1525 100%);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 50px 60px;
            position: relative;
            overflow: hidden;
        }

        /* Decorative circles */
        .left-panel::before {
            content: '';
            position: absolute;
            width: 500px; height: 500px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(37,99,235,0.12) 0%, transparent 70%);
            top: -150px; right: -150px;
            pointer-events: none;
        }
        .left-panel::after {
            content: '';
            position: absolute;
            width: 350px; height: 350px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(30,58,138,0.15) 0%, transparent 70%);
            bottom: -100px; left: -80px;
            pointer-events: none;
        }

        .brand-logo {
            font-size: 20px;
            font-weight: 700;
            color: #f1f5f9;
            letter-spacing: -0.3px;
        }

        .left-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 40px 0;
        }

        .shield-icon {
            width: 64px; height: 64px;
            background: linear-gradient(135deg, #1d4ed8, #2563eb);
            border-radius: 18px;
            display: flex; align-items: center; justify-content: center;
            font-size: 26px; color: white;
            margin-bottom: 28px;
            box-shadow: 0 8px 24px rgba(37,99,235,0.4);
        }

        .left-tagline {
            font-size: 38px;
            font-weight: 800;
            color: #f1f5f9;
            line-height: 1.2;
            letter-spacing: -0.8px;
            margin-bottom: 16px;
        }
        .left-tagline span { color: #3b82f6; }

        .left-desc {
            font-size: 14px;
            color: rgba(241,245,249,0.45);
            line-height: 1.75;
            max-width: 340px;
            margin-bottom: 44px;
        }

        .staff-roles {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .role-card {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 18px;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.07);
            border-radius: 12px;
            transition: background 0.2s;
        }
        .role-card:hover { background: rgba(255,255,255,0.07); }

        .role-icon-wrap {
            width: 36px; height: 36px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 15px;
            flex-shrink: 0;
        }
        .role-icon-wrap.admin  { background: rgba(37,99,235,0.25); color: #60a5fa; }
        .role-icon-wrap.driver { background: rgba(16,185,129,0.2); color: #34d399; }

        .role-info .role-title { font-size: 13px; font-weight: 600; color: #e2e8f0; }
        .role-info .role-desc  { font-size: 12px; color: rgba(255,255,255,0.35); margin-top: 2px; }

        .left-footer {
            font-size: 12px;
            color: rgba(255,255,255,0.18);
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
        }

        .staff-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1d4ed8;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 5px 12px;
            border-radius: 99px;
            margin-bottom: 20px;
        }

        .form-header { margin-bottom: 32px; }
        .form-header h2 { font-size: 26px; font-weight: 800; color: #0f172a; letter-spacing: -0.5px; margin-bottom: 6px; }
        .form-header p { font-size: 14px; color: #64748b; }

        .form-group { margin-bottom: 18px; }
        .form-group label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #374151;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 7px;
        }

        .select-wrap select,
        .input-wrap input {
            width: 100%;
            padding: 12px 14px;
            border: 1.5px solid #e5e7eb;
            border-radius: 10px;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            color: #111827;
            background: #f9fafb;
            outline: none;
            transition: all 0.2s;
        }
        .select-wrap select:focus,
        .input-wrap input:focus {
            border-color: #2563eb;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(37,99,235,0.08);
        }
        .select-wrap select { cursor: pointer; }

        .input-wrap { position: relative; }
        .input-wrap input { padding: 12px 14px 12px 40px; }
        .input-wrap .icon {
            position: absolute;
            left: 14px; top: 50%;
            transform: translateY(-50%);
            color: #9ca3af; font-size: 14px;
            pointer-events: none;
        }
        .input-wrap .toggle-eye {
            position: absolute;
            right: 14px; top: 50%;
            transform: translateY(-50%);
            color: #9ca3af; cursor: pointer;
            font-size: 14px; transition: color 0.2s;
        }
        .input-wrap .toggle-eye:hover { color: #2563eb; }

        .form-footer-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }
        .remember-label {
            display: flex; align-items: center;
            gap: 7px; font-size: 13px;
            color: #4b5563; cursor: pointer;
        }
        .remember-label input[type="checkbox"] {
            width: 15px; height: 15px;
            accent-color: #2563eb; cursor: pointer;
        }
        .forgot-link {
            font-size: 13px; font-weight: 600;
            color: #2563eb; text-decoration: none;
        }
        .forgot-link:hover { text-decoration: underline; }

        .btn-login {
            width: 100%; padding: 13px;
            background: #1e3a8a;
            color: white; border: none;
            border-radius: 10px; font-size: 15px;
            font-weight: 700; cursor: pointer;
            font-family: 'Poppins', sans-serif;
            transition: all 0.2s; letter-spacing: 0.2px;
            display: flex; align-items: center;
            justify-content: center; gap: 8px;
        }
        .btn-login:hover {
            background: #1e40af;
            box-shadow: 0 4px 16px rgba(30,58,138,0.4);
            transform: translateY(-1px);
        }
        .btn-login:active { transform: translateY(0); }

        .back-row {
            text-align: center;
            margin-top: 22px;
            font-size: 13px;
            color: #9ca3af;
        }
        .back-row a {
            color: #6b7280; font-weight: 600;
            text-decoration: none;
        }
        .back-row a:hover { color: #2563eb; }

        /* ALERTS */
        .alert-box {
            padding: 11px 14px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 18px;
            display: flex; align-items: center; gap: 9px;
        }
        .alert-error   { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
        .alert-success { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }

        @media (max-width: 768px) {
            .left-panel { display: none; }
            .right-panel { width: 100%; padding: 40px 28px; }
        }
    </style>
</head>
<body>

    <!-- LEFT PANEL -->
    <div class="left-panel">
        <div class="brand-logo">Rem's Transport</div>

        <div class="left-content">
            <div class="shield-icon"><i class="fas fa-shield-halved"></i></div>
            <h1 class="left-tagline">Staff <span>Portal</span><br>Access</h1>
            <p class="left-desc">
                Secure login for authorized personnel only.
                Manage bookings, drivers, and operations from one central dashboard.
            </p>
            <div class="staff-roles">
                <div class="role-card">
                    <div class="role-icon-wrap admin"><i class="fas fa-user-shield"></i></div>
                    <div class="role-info">
                        <div class="role-title">Admin</div>
                        <div class="role-desc">Full system control — bookings, vans, drivers, reports</div>
                    </div>
                </div>
                <div class="role-card">
                    <div class="role-icon-wrap driver"><i class="fas fa-id-badge"></i></div>
                    <div class="role-info">
                        <div class="role-title">Driver</div>
                        <div class="role-desc">View assigned trips and update trip status</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="left-footer">&copy; {{ date('Y') }} Rem's Transport. All rights reserved.</div>
    </div>

    <!-- RIGHT PANEL -->
    <div class="right-panel">

        <span class="staff-badge"><i class="fas fa-shield-halved"></i> Staff Access Only</span>

        <div class="form-header">
            <h2>Sign in to Portal</h2>
            <p>Enter your staff credentials to continue</p>
        </div>

        @if(session('error'))
            <div class="alert-box alert-error">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            </div>
        @endif
        @if(session('success'))
            <div class="alert-box alert-success">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="/admin/login" id="loginForm">
            @csrf

            <div class="form-group">
                <label>Login As</label>
                <div class="select-wrap">
                    <select name="role" id="role">
                        <option value="admin">Admin</option>
                        <option value="driver">Driver</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Email Address</label>
                <div class="input-wrap">
                    <i class="fas fa-envelope icon"></i>
                    <input type="email" name="email" placeholder="staff@remstransport.com"
                           value="{{ old('email') }}" required autofocus>
                </div>
            </div>

            <div class="form-group">
                <label>Password</label>
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
                <i class="fas fa-sign-in-alt"></i> Sign In to Portal
            </button>
        </form>

        <div class="back-row">
            <a href="/login"><i class="fas fa-arrow-left" style="margin-right:5px;font-size:11px;"></i>Back to Customer Login</a>
        </div>

    </div>

    <script>
    const toggle = document.getElementById('togglePassword');
    const passInput = document.getElementById('password');
    if (toggle && passInput) {
        toggle.addEventListener('click', function () {
            const hidden = passInput.type === 'password';
            passInput.type = hidden ? 'text' : 'password';
            this.classList.toggle('fa-eye', !hidden);
            this.classList.toggle('fa-eye-slash', hidden);
        });
    }
    </script>
<script src="/js/pwa.js"></script>
</body>
</html>
