<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class TieredRateLimiter
{
    protected $limiter;

    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    public function handle(Request $request, Closure $next, $tier = 'default')
    {
        $limits = [
            'public' => ['max_requests' => 30, 'decay_minutes' => 1],
            'standard' => ['max_requests' => 60, 'decay_minutes' => 1],
            'premium' => ['max_requests' => 300, 'decay_minutes' => 1],
            'admin' => ['max_requests' => 1000, 'decay_minutes' => 1],
            'auth' => ['max_requests' => 10, 'decay_minutes' => 1], // Strict for auth endpoints
        ];

        $user = $request->user();
        
        // Determine user tier
        if ($user && $user->isAdmin()) {
            $currentTier = 'admin';
        } elseif ($user && $user->isPremium()) {
            $currentTier = 'premium';
        } elseif ($user) {
            $currentTier = 'standard';
        } elseif ($request->is('api/login') || $request->is('api/register') || $request->is('api/password/*')) {
            $currentTier = 'auth';
        } else {
            $currentTier = 'public';
        }

        $rateLimit = $limits[$currentTier];
        
        // Use different key for authenticated vs unauthenticated
        $key = $user ? 'user:' . $user->id . ':' . $tier : 'ip:' . $request->ip() . ':' . $tier;
        
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
        
        // Add rate limit headers to response
        $remainingAttempts = $rateLimit['max_requests'] - $this->limiter->attempts($key);
        
        $response->headers->set('X-RateLimit-Limit', $rateLimit['max_requests']);
        $response->headers->set('X-RateLimit-Remaining', max(0, $remainingAttempts));
        $response->headers->set('X-RateLimit-Tier', $currentTier);
        
        return $response;
    }
}