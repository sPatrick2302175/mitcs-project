<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is logged in AND is a Super Admin
        if (Auth::check() && Auth::user()->is_admin === User::ROLE_SUPER_ADMIN) {
            return $next($request);
        }

        // Redirect to dashboard with an error message
        return redirect()->route('dashboard')->with('error', 'Unauthorized action.');
    }
}