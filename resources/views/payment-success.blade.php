<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Payment Success</title>

<style>
body {
    font-family: 'Segoe UI', sans-serif;
    background: linear-gradient(135deg, #f9fafb, #eef2ff);
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    margin: 0;
}

/* CARD */
.card {
    background: #fff;
    padding: 40px;
    border-radius: 16px;
    text-align: center;
    box-shadow: 0 20px 50px rgba(0,0,0,0.08);
    width: 420px;
    animation: fadeIn 0.4s ease;
}

/* ICON */
.icon-wrapper {
    width: 80px;
    height: 80px;
    background: #dcfce7;
    color: #16a34a;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    font-size: 36px;
    margin: 0 auto 15px;
}

/* TEXT */
h2 {
    margin: 10px 0;
    font-size: 22px;
}

.subtitle {
    color: #6b7280;
    font-size: 14px;
    margin-bottom: 20px;
}

/* INFO BOX */
.info-box {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    padding: 15px;
    border-radius: 10px;
    font-size: 13px;
    color: #374151;
    margin-bottom: 20px;
}

/* BUTTONS */
.actions {
    display: flex;
    gap: 10px;
    justify-content: center;
}

.btn {
    padding: 12px 18px;
    border-radius: 8px;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
}

/* Primary */
.btn-primary {
    background: #2563eb;
    color: white;
}

/* Secondary */
.btn-secondary {
    background: #f3f4f6;
    color: #374151;
}

/* HOVER */
.btn:hover {
    opacity: 0.9;
}

/* ANIMATION */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

</head>

<body>

<div class="card">

    <div class="icon-wrapper">✔</div>

    <h2>Payment Successful</h2>

    @if(isset($booking) && $booking->payment_status === 'fully_paid')
        <div class="info-box" style="background:#f0fdf4; border-color:#bbf7d0; color:#166534;">
            🎉 Your booking is <strong>fully paid</strong>. No remaining balance. See you on your trip!
        </div>
    @else
        <div class="info-box">
            Please prepare the remaining balance on the day of your trip.
            <br><br>
            <span style="font-size:12px; color:#9ca3af;">If you have already paid in full, please disregard this message.</span>
        </div>
    @endif

    <div class="actions">
        <a href="/my-bookings" class="btn btn-primary">View My Bookings</a>
        <a href="/" class="btn btn-secondary">Back to Home</a>
    </div>

</div>

</body>
</html>
