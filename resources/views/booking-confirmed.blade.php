<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmed | Rem's Transport</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background: #f8fafc; display: flex; align-items: center; justify-content: center; min-height: 100vh; }

        .success-card {
            background: white;
            padding: 50px;
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.05);
            text-align: center;
            max-width: 500px;
            width: 90%;
        }

        .icon-circle {
            width: 80px;
            height: 80px;
            background: #dcfce7;
            color: #22c55e;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            margin: 0 auto 24px;
        }

        h1 { color: #1e293b; font-size: 28px; margin-bottom: 12px; }
        p { color: #64748b; line-height: 1.6; margin-bottom: 32px; }

        .btn-home {
            background: #2563eb;
            color: white;
            text-decoration: none;
            padding: 14px 32px;
            border-radius: 12px;
            font-weight: 600;
            transition: 0.3s;
            display: inline-block;
        }

        .btn-home:hover {
            background: #1d4ed8;
            transform: translateY(-2px);
            box-shadow: 0 10px 15px rgba(37, 99, 235, 0.2);
        }
    </style>
</head>
<body>

<div class="success-card">
    <div class="icon-circle">
        <i class="fas fa-check"></i>
    </div>
    <h1>Payment Successful!</h1>
    <p>Thank you for choosing Rem's Transport. Your downpayment has been received and your tour is now officially reserved.</p>

    <a href="/" class="btn-home">Return to Home</a>
</div>

</body>
</html>
