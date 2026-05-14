<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // 1. Check if user is logged in
        if (!$request->user()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // 2. Check if the user's role name matches the required role
        if (strtolower($request->user()->role->name) !== strtolower($role)) {
            return response()->json(['message' => 'Forbidden: You do not have the required role.'], 403);
        }

        return $next($request);
    }
}
