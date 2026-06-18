<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User; 
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->is_admin >= User::ROLE_ADMIN_OFFICER) {
            return $next($request);
        }

        // If the request is from JavaScript/Axios, return a proper 403 JSON response
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        return redirect()->route('dashboard')->with('error', 'Unauthorized action.');
    }
}