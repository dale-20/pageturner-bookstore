<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectBasedOnRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(auth()->user()) {
            $user = auth()->user();
        }

        if($user->isAdmin() && $request->routeIs("dashboard")) {
            return redirect()->route("admin.dashboard");
        }

        if(!$user->isAdmin() && $request->routeIs("admin.dashboard")) {
            return redirect()->route("dashboard");
        }

        return $next($request);
    }
}
