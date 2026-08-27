<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Forgot Password | Rem's Transport</title>

<link rel="stylesheet" href="{{ asset('css/forgot.css') }}">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>

<div class="forgot-wrapper">
<div class="forgot-card">

<h1 class="logo">Rem's Transport</h1>
<p class="subtitle">Reset your password</p>

<form method="POST" action="/forgot-password">
@csrf

<label>Email Address</label>

<div class="input-box">
<i class="fa fa-envelope"></i>
<input type="email" name="email" placeholder="Enter your email" required>
@error('email')
<p class="message error">{{ $message }}</p>
@enderror
</div>

<button type="submit" class="reset-btn">
Send OTP
</button>

<a href="/login" class="back-login">
Back to Login
</a>

@if(session('success'))
<p class="message success">{{ session('success') }}</p>
@endif

@if(session('error'))
<p class="message error">{{ session('error') }}</p>
@endif

@error('email')
<p class="message error">{{ $message }}</p>
@enderror

</form>

</div>
</div>

</body>
</html>
