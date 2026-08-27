<form method="POST" action="/reset-password">
@csrf

<input type="hidden" name="token" value="{{ $token }}">

<input type="email" name="email" placeholder="Email" required>

<input type="password" name="password" placeholder="New Password" required>

<input type="password" name="password_confirmation" placeholder="Confirm Password" required>

<button type="submit">Reset Password</button>

</form>
