<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User; 
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Math magic: 1, 2, and 3 are all >= 1. 
        // This lets Admin Officers, Super Admins, and Dept Heads use all shared admin features seamlessly!
        if (Auth::check() && Auth::user()->is_admin >= User::ROLE_ADMIN_OFFICER) {
            return $next($request);
        }

        // Redirect to dashboard with an error message
        return redirect()->route('dashboard')->with('error', 'Unauthorized action.');
    }
}