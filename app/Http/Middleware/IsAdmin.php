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
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is logged in AND is at least a Department Admin (>= 1)
        if (Auth::check() && Auth::user()->is_admin >= User::ROLE_DEPT_ADMIN) {
            return $next($request);
        }

        abort(403, 'Unauthorized action. You must be an Administrator to view this page.');
    }
}