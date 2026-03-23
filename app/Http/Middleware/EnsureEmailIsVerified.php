<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailIsVerified
{
    /**
     * Routes the unverified user may still access.
     */
    protected array $except = [
        'verification.notice',
        'verification.verify',
        'verification.send',
        'profile.edit',
        'profile.update',
        'profile.two-factor',
        'two-factor.totp.setup',
        'two-factor.totp.confirm',
        'two-factor.email.enable',
        'two-factor.disable',
        'profile.two-factor.recovery-codes',
        'logout',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (
            $request->user() &&
            ! $request->user()->hasVerifiedEmail() &&
            ! $this->inExceptArray($request)
        ) {
            return $request->expectsJson()
                ? abort(403, 'Your email address is not verified.')
                : redirect()->route('verification.notice');
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