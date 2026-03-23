<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTwoFactorVerified
{
    /**
     * Routes that bypass the 2FA gate (challenge itself + auth routes).
     */
    protected array $except = [
        'two-factor.challenge',
        'two-factor.verify',
        'two-factor.resend',
        'logout',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (
            $user &&
            $user->two_factor_enabled &&
            ! session()->get('two_factor_verified') &&
            ! $this->inExceptArray($request)
        ) {
            // Store intended destination before bouncing to challenge
            session(['url.intended' => $request->fullUrl()]);

            return redirect()->route('two-factor.challenge');
        }

        return $next($request);
    }

    protected function inExceptArray(Request $request): bool
    {
        foreach ($this->except as $routeName) {
            if ($request->routeIs($routeName)) {
                return true;
            }
        }
        return false;
    }
}