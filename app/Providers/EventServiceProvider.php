<?php

namespace App\Providers;

use App\Listeners\AuthAuditListener;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Auth event → listener mappings.
     *
     * Spatie role/permission assignment events are NOT listed here because
     * Spatie does not fire dedicated domain events for sync operations.
     * Call SpatieAuditListener manually from your controllers instead.
     * See SpatieAuditListener.php for usage examples.
     */
    protected $listen = [
        // Authentication events
        Login::class         => [AuthAuditListener::class . '@handleLogin'],
        Logout::class        => [AuthAuditListener::class . '@handleLogout'],
        Failed::class        => [AuthAuditListener::class . '@handleFailed'],
        PasswordReset::class => [AuthAuditListener::class . '@handlePasswordReset'],
        Verified::class      => [AuthAuditListener::class . '@handleVerified'],

        // 2FA events — adjust class names to match your 2FA package
        // Laravel Fortify:
        //   \Laravel\Fortify\Events\TwoFactorAuthenticationEnabled::class  => [AuthAuditListener::class . '@handleTwoFactorEnabled'],
        //   \Laravel\Fortify\Events\TwoFactorAuthenticationDisabled::class => [AuthAuditListener::class . '@handleTwoFactorDisabled'],
        //
        // Custom events (if you dispatch them yourself):
        //   \App\Events\TwoFactorEnabled::class  => [AuthAuditListener::class . '@handleTwoFactorEnabled'],
        //   \App\Events\TwoFactorDisabled::class => [AuthAuditListener::class . '@handleTwoFactorDisabled'],
    ];

    public function boot(): void
    {
        //
    }

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
