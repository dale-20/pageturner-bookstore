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
        // Adjust this logic based on your User model structure
        
        // Option 1: If you have an 'is_admin' column
        if (isset($user->is_admin) && $user->is_admin == true) {
            return $next($request);
        }
        
        // Option 2: If you have a 'role' column
        if (isset($user->role) && $user->role === 'admin') {
            return $next($request);
        }
        
        // Option 3: If you have a roles relationship
        // if ($user->roles()->where('name', 'admin')->exists()) {
        //     return $next($request);
        // }
        
        // User is not an admin - deny access
        abort(403, 'Administrator access required.');
    }
}