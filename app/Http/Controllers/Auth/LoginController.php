<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('login.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:8',
            'role' => 'required'
        ]);

        $throttleKey = Str::lower($request->input('email')) . '|' . $request->ip();

        // 1. Check if currently locked out
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $displaySeconds = $seconds > 60 ? 60 : $seconds;
            return redirect()->back()->with('error', "Too many failed attempts. Try again in $displaySeconds seconds.");
        }

        $credentials = $request->only('email', 'password', 'role');

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();

            if ($request->role == 'admin') {
                return redirect()->intended('/admin/dashboard');
            }
            return redirect()->intended('/dashboard');
        }

        // Increment failed attempts (1 hour memory)
        RateLimiter::hit($throttleKey, 3600);

        // 2. Check if the 5th attempt just happened
        if (RateLimiter::remaining($throttleKey, 5) === 0) {

            // Force the lockout to exactly 60 seconds
            RateLimiter::clear($throttleKey);
            RateLimiter::hit($throttleKey, 60);

            $userEmail = $request->input('email');
            $userIp = $request->ip();

            // 3. Send the DESIGNED Security Alert Email
            try {
                // We pass the IP to the blade template using an array
                $data = ['ip' => $userIp];

                Mail::send('emails.security_alert', $data, function ($message) use ($userEmail) {
                    $message->to($userEmail)
                            ->subject('⚠️ Security Alert: Multiple Failed Login Attempts');
                });
            } catch (\Exception $e) {
                \Log::error("Security Mail Error: " . $e->getMessage());
            }

            return redirect()->back()->with('error', "Too many failed attempts. Try again in 60 seconds.");
        }

        return redirect()->back()->with('error', 'Invalid email, password, or role.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
