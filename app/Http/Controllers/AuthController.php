<?php

namespace App\Http\Controllers;

use App\Mail\LoginOtpMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Returns an error message if a new OTP email may NOT be sent to this
     * address yet, or null if sending is allowed. Limits per email address:
     * 1 code per 60 seconds, and 5 codes per hour. This is the server-side
     * guard — the countdown on the verify pages is only cosmetic and can be
     * bypassed by refreshing or posting directly.
     *
     * Call recordOtpSend() right after an OTP email is actually sent.
     */
    private function otpThrottleError(string $email): ?string
    {
        $email = Str::lower($email);

        if (RateLimiter::tooManyAttempts('otp-send-min:' . $email, 1)) {
            $seconds = RateLimiter::availableIn('otp-send-min:' . $email);
            return "Please wait {$seconds} second" . ($seconds === 1 ? '' : 's')
                . " before requesting another verification code.";
        }

        if (RateLimiter::tooManyAttempts('otp-send-hour:' . $email, 5)) {
            $minutes = max(1, (int) ceil(RateLimiter::availableIn('otp-send-hour:' . $email) / 60));
            return "Too many verification codes requested. Please try again in about {$minutes} minute"
                . ($minutes === 1 ? '' : 's') . ".";
        }

        return null;
    }

    /**
     * Records that an OTP email was just sent to this address, for throttling.
     */
    private function recordOtpSend(string $email): void
    {
        $email = Str::lower($email);
        RateLimiter::hit('otp-send-min:' . $email, 60);
        RateLimiter::hit('otp-send-hour:' . $email, 3600);
    }

    public function showLogin()
    {
        return view('login.login');
    }

    /**
     * Single login for every role. Credentials are matched on email +
     * password only; the account's own `role` column then decides where
     * verifyLoginOtp() sends the user after 2FA.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Define Throttling Key
        $throttleKey = Str::lower($request->input('email')) . '|' . $request->ip();

        // 1. Check if they are already locked out
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $displaySeconds = $seconds > 60 ? 60 : $seconds;
            return back()->with('error', "Too many failed attempts. Try again in $displaySeconds seconds.");
        }

        // 2. Attempt Login
        if (Auth::attempt($credentials, $request->remember)) {
            // SUCCESS: Clear failures
            RateLimiter::clear($throttleKey);

            $user = Auth::user();

            // Only send a fresh OTP if we are allowed to. If the user is
            // throttled but still holds a valid code (e.g. they just came
            // back to re-login), reuse it instead of emailing another.
            $otpError = $this->otpThrottleError($user->email);
            $hasValidOtp = $user->otp_code
                && $user->otp_expires_at
                && now()->lt($user->otp_expires_at);

            $reusedOtp = false;

            if (!$otpError || !$hasValidOtp) {
                $otp = rand(100000, 999999);
                $user->otp_code = $otp;
                $user->otp_expires_at = now()->addMinutes(5);
                $user->save();

                Mail::to($user->email)->send(new LoginOtpMail($otp));
                $this->recordOtpSend($user->email);
            } else {
                $reusedOtp = true;
            }

            // Logout temporarily for 2FA
            Auth::logout();

            session(['login_2fa_user_id' => $user->id]);

            $redirect = redirect('/verify-login-otp');

            return $reusedOtp
                ? $redirect->with('success', 'We already sent a verification code to your email recently. Please enter it below.')
                : $redirect;
        }

        // 3. LOGIN FAILED: Increment hits
        RateLimiter::hit($throttleKey, 3600); // 1-hour window to track failures

        // 4. Check if this was the 5th failure to send Security Alert
        if (RateLimiter::remaining($throttleKey, 5) === 0) {

            // Force clean 60s lockout
            RateLimiter::clear($throttleKey);
            RateLimiter::hit($throttleKey, 60);

            $userEmail = $request->input('email');
            $userIp = $request->ip();

            // Send the DESIGNED Security Alert (The blue card we made)
            try {
                $data = ['ip' => $userIp];
                Mail::send('emails.security_alert', $data, function ($message) use ($userEmail) {
                    $message->to($userEmail)
                            ->subject('⚠️ Security Alert: Multiple Failed Login Attempts');
                });
            } catch (\Exception $e) {
                \Log::error("Security Alert Email Error: " . $e->getMessage());
            }

            return back()->with('error', "Too many failed attempts. Try again in 60 seconds.");
        }

        return back()->with('error', 'Invalid email or password.');
    }

    public function showOtpForm()
    {
        if (!session()->has('login_2fa_user_id')) {
            return redirect('/login')->with('error', 'Please login first.');
        }
        return view('login.verify-otp');
    }

public function verifyLoginOtp(Request $request)
    {
        $request->validate(['otp' => 'required|digits:6']);

        // 1. Find user from session
        $user = User::find(session('login_2fa_user_id'));

        // 2. Validate
        if (!$user || (string)$request->otp !== (string)$user->otp_code || now()->gt($user->otp_expires_at)) {
            return back()->with('error', 'Invalid or expired code.');
        }

        // 3. Success: Clear OTP
        $user->update([
            'otp_code' => null,
            'otp_expires_at' => null
        ]);

        // 4. Official Login
        Auth::login($user);

        // 5. Clear temp session
        session()->forget('login_2fa_user_id');

        // 6. DYNAMIC REDIRECT BASED ON ROLE
        if ($user->role === 'admin') {
            return redirect('/admin/dashboard')->with('success', 'Welcome Admin ' . $user->first_name . '!');
        }

        if ($user->role === 'driver') {
            return redirect('/driver/dashboard')->with('success', 'Welcome Driver ' . $user->first_name . '!');
        }

        return redirect('/')->with('success', 'Welcome! ' . $user->first_name . '!');
    }

    public function changePassword(Request $request)
{
    $request->validate([
        'current_password' => 'required',
        'password' => 'required|min:8|confirmed',
    ]);

    $user = Auth::user();

    // 1. Check if the "Current Password" entered is correct
    if (!Hash::check($request->current_password, $user->password)) {
        return back()->with('error', 'The current password you entered is incorrect.');
    }

    // 2. ALERT: Check if the "New Password" is the same as the "Current Password"
    if (Hash::check($request->password, $user->password)) {
        return back()->with('error', 'New password cannot be the same as your current password. Please choose a different one.');
    }

    // 3. Success: Update to the new password
    $user->password = Hash::make($request->password);
    $user->save();

    return back()->with('success', 'Password changed successfully!');
}

public function updateProfile(Request $request)
{
    $user = Auth::user();

    // 1. Validate the input
    $request->validate([
        'first_name' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z\s]+$/'],
        'middle_name' => ['nullable', 'string', 'max:255', 'regex:/^[A-Za-z\s]+$/'],
        'last_name' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z\s]+$/'],
        'email' => 'required|email|unique:users,email,' . $user->id,

        // New validation rules for birthday and contact number
        'birthday' => 'required|date|before_or_equal:today',
        'phone_number' => ['required', 'regex:/^09\d{9}$/'],
    ]);

    // 2. Update the user data
    $user->update([
        'first_name' => $request->first_name,
        'middle_name' => $request->middle_name,
        'last_name' => $request->last_name,
        'email' => $request->email,

        // Save the new fields
        'birthday' => $request->birthday,
        'phone_number' => $request->phone_number,
    ]);

    return back()->with('success', 'Profile updated successfully!');
}



 public function sendOtp(Request $request)
{
    $request->validate(['email' => 'required|email|exists:users,email']);

    if ($error = $this->otpThrottleError($request->email)) {
        return back()->with('error', $error);
    }

    $user = User::where('email', $request->email)->first();
    $otp = rand(100000, 999999);

    $user->otp_code = $otp;
    $user->otp_expires_at = now()->addMinutes(10);
    $user->save();

    Mail::to($user->email)->send(new \App\Mail\LoginOtpMail($otp));
    $this->recordOtpSend($user->email);

    // Store ID in a RESET-specific session key
    session(['password_reset_user_id' => $user->id]);

    return redirect('/verify-reset-otp')->with('success', 'Reset code sent to your email!');
}


public function showResetOtpVerifyForm()
{
    if (!session()->has('password_reset_user_id')) return redirect('/login');
    return view('login.verify-reset-otp');
}

public function verifyResetOtp(Request $request)
{
    $request->validate(['otp' => 'required|digits:6']);
    $user = User::find(session('password_reset_user_id'));

    if (!$user || (string)$request->otp !== (string)$user->otp_code || now()->gt($user->otp_expires_at)) {
        return back()->with('error', 'Invalid or expired code.');
    }


    session(['otp_verified' => true]);

    return redirect('/reset-otp');
}

    public function showResetOtpForm()
    {
        if (!session()->has('password_reset_user_id') || !session('otp_verified')) {
            return redirect('/login')->with('error', 'Please verify your OTP first.');
        }
        return view('login.reset-otp');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'password' => [
                'required',
                'confirmed',
                Password::min(8)->mixedCase()->numbers()->symbols(),
            ],
        ]);

        $user = User::find(session('password_reset_user_id'));

        if (!$user) {
            return redirect('/login')->with('error', 'Session expired.');
        }

        $user->password = Hash::make($request->password);
        $user->otp_code = null;
        $user->otp_expires_at = null;
        $user->save();

        session()->forget(['password_reset_user_id', 'otp_verified']);

        return redirect('/login')->with('success', 'Password reset successful! Please login.');
    }



    public function resendOtp(Request $request)
    {
        $userId = session('login_2fa_user_id') ?? session('password_reset_user_id');
        $user = User::find($userId);

        if (!$user) {
            return redirect('/login')->with('error', 'Session expired.');
        }

        if ($error = $this->otpThrottleError($user->email)) {
            return back()->with('error', $error);
        }

        $otp = rand(100000, 999999);
        $user->otp_code = $otp;
        $user->otp_expires_at = now()->addMinutes(5);
        $user->save();

        Mail::to($user->email)->send(new LoginOtpMail($otp));
        $this->recordOtpSend($user->email);

        return back()->with('success', 'OTP resent successfully!');
    }

    public function showRegister()
    {
        return view('login.register');
    }

public function register(Request $request)
{
    $request->validate([
        'first_name'   => ['required', 'max:20', 'regex:/^[A-Za-z\s]+$/'],
        'middle_name'  => ['nullable', 'max:20', 'regex:/^[A-Za-z\s]+$/'],
        'last_name'    => ['required', 'max:20', 'regex:/^[A-Za-z\s]+$/'],
        'suffix'       => ['nullable', 'max:10', 'regex:/^[A-Za-z. ]+$/'],
        'email'        => 'required|email|unique:users,email',
        'birthday'     => 'required|date|before_or_equal:today',
        'phone_number' => ['required', 'regex:/^09\d{9}$/', 'unique:users,phone_number'],
        'password'     => 'required|min:8|confirmed',
        'terms'        => 'required|in:accepted',
    ], [
        'terms.required' => 'Please review and accept the Terms of Service & Privacy Policy to continue.',
        'terms.in'       => 'Please review and accept the Terms of Service & Privacy Policy to continue.',
    ]);

    if ($error = $this->otpThrottleError($request->email)) {
        return back()->withInput()->with('error', $error);
    }

    $otp = rand(100000, 999999);

    session([
        'reg_pending_data' => [
            'first_name'   => $request->first_name,
            'middle_name'  => $request->middle_name,
            'last_name'    => $request->last_name,
            'suffix'       => $request->suffix,
            'email'        => $request->email,
            'birthday'     => $request->birthday,
            'phone_number' => $request->phone_number,
            'password'     => Hash::make($request->password),
        ],
        'reg_otp'            => (string) $otp,
        'reg_otp_expires_at' => now()->addMinutes(10)->toDateTimeString(),
    ]);

    Mail::to($request->email)->send(new LoginOtpMail($otp));
    $this->recordOtpSend($request->email);

    return redirect('/verify-register-otp');
}

public function showRegisterOtpForm()
{
    if (!session()->has('reg_pending_data')) {
        return redirect('/register')->with('error', 'Session expired. Please fill in the form again.');
    }
    return view('login.verify-register-otp');
}

public function verifyRegisterOtp(Request $request)
{
    $request->validate(['otp' => 'required|digits:6']);

    $pendingData = session('reg_pending_data');
    $storedOtp   = session('reg_otp');
    $expiresAt   = session('reg_otp_expires_at');

    if (!$pendingData || !$storedOtp) {
        return redirect('/register')->with('error', 'Session expired. Please fill in the form again.');
    }

    if ((string) $request->otp !== $storedOtp || now()->gt($expiresAt)) {
        return back()->with('error', 'Invalid or expired OTP code.');
    }

    // OTP verified — now create the account
    $user = User::create([
        'first_name'   => $pendingData['first_name'],
        'middle_name'  => $pendingData['middle_name'],
        'last_name'    => $pendingData['last_name'],
        'suffix'       => $pendingData['suffix'] ?? null,
        'email'        => $pendingData['email'],
        'birthday'     => $pendingData['birthday'],
        'phone_number' => $pendingData['phone_number'],
        'password'     => $pendingData['password'],
        'role'         => 'customer',
    ]);

    $user->notify(new \App\Notifications\WelcomeNotification());

    session()->forget(['reg_pending_data', 'reg_otp', 'reg_otp_expires_at']);

    Auth::login($user);
    return redirect('/')->with('success', 'Account created successfully! Welcome, ' . $user->first_name . '!');
}

public function resendRegisterOtp()
{
    $pendingData = session('reg_pending_data');

    if (!$pendingData) {
        return redirect('/register')->with('error', 'Session expired. Please fill in the form again.');
    }

    if ($error = $this->otpThrottleError($pendingData['email'])) {
        return back()->with('error', $error);
    }

    $otp = rand(100000, 999999);
    session([
        'reg_otp'            => (string) $otp,
        'reg_otp_expires_at' => now()->addMinutes(10)->toDateTimeString(),
    ]);

    Mail::to($pendingData['email'])->send(new LoginOtpMail($otp));
    $this->recordOtpSend($pendingData['email']);

    return back()->with('success', 'A new OTP has been sent to your email.');
}

    public function logout()
{
    Auth::logout();

    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect('/login')->with('success', 'You have been logged out successfully.');
}

}
