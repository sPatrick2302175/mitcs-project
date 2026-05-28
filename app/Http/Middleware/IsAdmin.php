<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        // Check if a user is logged in AND their is_admin column is true
        if (Auth::check() && Auth::user()->is_admin) {
            return $next($request); // Let them through!
        }

        // If they are not an admin, kick them out with a 403 error
        abort(403, 'Unauthorized action. You must be an Administrator to view this page.');
    }
}