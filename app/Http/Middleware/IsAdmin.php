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
        // Check if user is logged in AND is at least a Department Admin
        if (Auth::check() && Auth::user()->is_admin >= User::ROLE_DEPT_ADMIN) {
            return $next($request);
        }

        // Redirect to dashboard with an error message
        return redirect()->route('dashboard')->with('error', 'Unauthorized action.');
    }
}