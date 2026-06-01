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
        // Check if user is logged in AND is a Super Admin (== 2)
        if (Auth::check() && Auth::user()->is_admin === User::ROLE_SUPER_ADMIN) {
            return $next($request);
        }

        abort(403, 'Unauthorized action. This area is restricted to Super Administrators.');
    }
}