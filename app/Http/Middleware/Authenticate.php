<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Authenticate extends Middleware
{
    /**
     * Handle an incoming request.
     */
    public function handle($request, Closure $next, ...$guards)
    {
        // If no guards specified, use default
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                // User is authenticated, proceed
                Auth::shouldUse($guard);
                return $next($request);
            }
        }

        // Not authenticated - redirect or return JSON response
        return $this->unauthenticated($request, $guards);
    }

    /**
     * Handle unauthenticated users.
     */
    protected function unauthenticated($request, array $guards)
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // Redirect to login page
        return redirect()->guest(route('login'));
    }

    /**
     * Get the path the user should be redirected to when they are not authenticated.
     * (Kept for compatibility)
     */
    protected function redirectTo(Request $request): ?string
    {
        if (!$request->expectsJson()) {
            return route('login');
        }
        return null;
    }
}