<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class DashboardRedirect
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            if (Auth::user()->isInstructor()) {
                return redirect()->route('instructor.dashboard');
            }

            if (auth()->user()->isAdmin()) {
                // If you have an admin dashboard, redirect there.
                // For now, let's just allow them or redirect somewhere safe.
                // return redirect()->route('admin.dashboard');
            }

            return $next($request);
        }

        return $next($request);
    }
}
