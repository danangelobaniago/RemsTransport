<?php

namespace App\Http\Middleware;

use App\Support\SingleSession;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureSingleSession
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && !SingleSession::isCurrent($user)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $message = 'Your account was signed in on another device, so you have been logged out here.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 401);
            }

            return redirect('/login')->with('error', $message);
        }

        return $next($request);
    }
}
