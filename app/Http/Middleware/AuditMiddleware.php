<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;

class AuditMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        
        // Log sensitive operations
        if ($request->isMethod('PUT') || $request->isMethod('POST') || $request->isMethod('DELETE')) {
            if (auth()->check() && $request->route()) {
                $routeName = $request->route()->getName() ?? '';
                
                // Skip logging for import/export routes to avoid noise
                if (!in_array($routeName, ['import', 'export'])) {
                    
                    // Additional audit logging for security events
                    if (str_contains($routeName, 'permission') ||
                        str_contains($routeName, 'role') ||
                        str_contains($routeName, 'admin')) {
                        
                        // Use Laravel's Log facade instead of activity()
                        Log::channel('audit')->info('Security Event', [
                            'event' => 'security_event',
                            'user_id' => auth()->id(),
                            'user_email' => auth()->user()->email,
                            'performed_on' => auth()->user()->id,
                            'caused_by' => auth()->user()->id,
                            'properties' => [
                                'ip' => $request->ip(),
                                'user_agent' => $request->userAgent(),
                                'url' => $request->fullUrl(),
                                'method' => $request->method(),
                                'input' => Crypt::encryptString(json_encode($request->except(['password', 'token', 'password_confirmation']))),
                            ],
                            'created_at' => now(),
                        ]);
                    }
                }
            }
        }
        
        return $response;
    }
}