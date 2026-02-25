<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectBookShow
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        if (auth()->user()) {
            $user = auth()->user();

            if ($user->isAdmin() && $request->routeIs("books.show")) {
                return redirect()->route("admin.books.show", $request->route('book'));
            }

            if (!$user->isAdmin() && $request->routeIs("admin.books.show")) {
                return redirect()->route("books.show", $request->route('book'));
            }
        }
        return $next($request);
    }
}