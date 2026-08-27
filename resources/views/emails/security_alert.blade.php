<!DOCTYPE html>
<html>
<head>
    <style>
        .email-card {
            max-width: 500px;
            margin: 20px auto;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            overflow: hidden;
        }
        .header {
            background-color: #2563eb;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .body {
            padding: 30px;
            color: #475569;
            line-height: 1.6;
        }
        .alert-icon {
            font-size: 40px;
            margin-bottom: 10px;
            display: block;
        }
        .footer {
            background-color: #f8fafc;
            padding: 15px;
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
        }
        .ip-box {
            background: #f1f5f9;
            padding: 10px;
            border-radius: 6px;
            display: inline-block;
            font-family: monospace;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="email-card">
        <div class="header">
            <span class="alert-icon">⚠️</span>
            <h2 style="margin:0;">Security Alert</h2>
        </div>
        <div class="body">
            <p>Hi there,</p>
            <p>We noticed <strong>5 failed login attempts</strong> on your <strong>Rem's Transport</strong> account.</p>
            <p>For your protection, login access for this email has been locked for <strong>60 seconds</strong>.</p>

            <p><strong>Location Details:</strong><br>
            <span class="ip-box">IP Address: {{ $ip }}</span></p>

            <p style="margin-top:25px;">If this wasn't you, we recommend changing your password immediately to keep your account secure.</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} Rem's Transport Services &bull; Secure Login System
        </div>
    </div>
</body>
</html>
