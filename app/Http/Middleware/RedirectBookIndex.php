<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectBookIndex
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


            if ($user->isAdmin() && $request->routeIs("books.index")) {
                return redirect()->route("admin.books.index");
            }

            if (!$user->isAdmin() && $request->routeIs("admin.books.index")) {
                return redirect()->route("books.index");
            }

            if ($user->isAdmin() && $request->routeIs("books.show")) {
                $book = $request->route('book');
                return redirect()->route("admin.books.show", ['book'=>$book]);
            }

            if (!$user->isAdmin() && $request->routeIs("admin.books.show")) {
                $book = $request->route('book');
                return redirect()->route("books.show", ['book'=>$book]);
            }
        }

        return $next($request);
    }
}
