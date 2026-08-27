<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $role
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {

        if (!Auth::check()) {
            return redirect('/login')->with('error', 'Please login to access this page.');
        }


        if (Auth::user()->role !== $role) {


            \Log::warning("Unauthorized access attempt by User ID " . Auth::id() . " to role-protected route: " . $request->fullUrl());


            if (Auth::user()->role === 'admin') {
                return redirect('/admin/dashboard')->with('error', 'Admins cannot access customer pages.');
            }

            return redirect('/')->with('error', 'Access Denied: You do not have the required permissions.');
        }

        return $next($request);
    }
}
