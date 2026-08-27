<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Important!
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {

        if (Auth::check() && Auth::user()->role === $role) {
            return $next($request);
        }


        return redirect('/login')->with('error', 'Unauthorized Access.');
    }
}
