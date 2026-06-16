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
        // Check if user is logged in AND is specifically a Super Admin
        if (Auth::check() && Auth::user()->is_admin === User::ROLE_SUPER_ADMIN) {
            return $next($request);
        }

        // Fix: Handle background API/JS requests gracefully
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Unauthorized action. Super Admin access required.'], 403);
        }

        return redirect()->route('dashboard')->with('error', 'Unauthorized action. Super Admin access required.');
    }
}