<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;

class TieredRateLimiter
{
    protected array $limits = [
        'public' => ['max_requests' => 30, 'decay_minutes' => 1],
        'standard' => ['max_requests' => 60, 'decay_minutes' => 1],
        'premium' => ['max_requests' => 300, 'decay_minutes' => 1],
        'admin' => ['max_requests' => 1000, 'decay_minutes' => 1],
        'auth' => ['max_requests' => 10, 'decay_minutes' => 1],
    ];

    public function __construct(protected RateLimiter $limiter) {}

    public function handle(Request $request, Closure $next, string $tier = 'public')
    {
        $user = $request->user();

        if ($user?->isAdmin()) {
            $currentTier = 'admin';
        } elseif ($user && $this->isPremiumUser($user)) {
            $currentTier = 'premium';
        } elseif ($user) {
            $currentTier = 'standard';
        } elseif ($request->is('api/login', 'api/register', 'api/password/*')) {
            $currentTier = 'auth';
        } else {
            $currentTier = array_key_exists($tier, $this->limits) ? $tier : 'public';
        }

        $rateLimit = $this->limits[$currentTier];
        $key = $this->resolveKey($request, $currentTier);

        if ($this->limiter->tooManyAttempts($key, $rateLimit['max_requests'])) {
            $retryAfter = $this->limiter->availableIn($key);

            return response()->json([
                'error' => 'Too many requests',
                'message' => "You have exceeded the rate limit for {$currentTier} tier. Please try again in {$retryAfter} seconds.",
                'limit' => $rateLimit['max_requests'],
                'remaining' => 0,
                'retry_after' => $retryAfter,
                'tier' => $currentTier,
            ], 429)->withHeaders([
                'X-RateLimit-Limit' => $rateLimit['max_requests'],
                'X-RateLimit-Remaining' => 0,
                'Retry-After' => $retryAfter,
            ]);
        }

        $this->limiter->hit($key, $rateLimit['decay_minutes'] * 60);

        $response = $next($request);
        $remainingAttempts = $rateLimit['max_requests'] - $this->limiter->attempts($key);

        $response->headers->set('X-RateLimit-Limit', $rateLimit['max_requests']);
        $response->headers->set('X-RateLimit-Remaining', max(0, $remainingAttempts));
        $response->headers->set('X-RateLimit-Tier', $currentTier);

        return $response;
    }

    protected function resolveKey(Request $request, string $tier): string
    {
        if ($request->user()) {
            return 'api-rate:user:' . $request->user()->getAuthIdentifier() . ':' . $tier;
        }

        return 'api-rate:ip:' . $request->ip() . ':' . $tier;
    }

    protected function isPremiumUser(object $user): bool
    {
        if (method_exists($user, 'isPremium')) {
            return (bool) $user->isPremium();
        }

        return ($user->role ?? null) === 'premium';
    }
}
