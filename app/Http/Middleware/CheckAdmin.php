<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Get the authenticated user
        $user = Auth::user();
        
        // Check if user has admin privileges
        // Choose ONE of these options based on your User model structure:
        
        // Option 1: If you have an 'is_admin' column (boolean)
        // if ($user->is_admin === true) {
        //     return $next($request);
        // }
        
        // Option 2: If you have a 'role' column (string)
        // if ($user->role === 'admin') {
        //     return $next($request);
        // }
        
        // Option 3: If you have an isAdmin() method in your User model
        // This is the cleanest approach
        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return $next($request);
        }
        
        // If none of the above conditions are met, deny access
        abort(403, 'Administrator access required.');
    }
}