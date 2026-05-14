<?php

namespace App\Listeners;

use App\Models\Audit;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Notifications\Events\NotificationSent;

class AuthAuditListener
{
    public function __construct(protected Request $request) {}

    // -------------------------------------------------------------------------
    // Login
    // -------------------------------------------------------------------------
    public function handleLogin(Login $event): void
    {
        $this->writeAuthAudit('login', $event->user);
    }

    // -------------------------------------------------------------------------
    // Logout
    // -------------------------------------------------------------------------
    public function handleLogout(Logout $event): void
    {
        $this->writeAuthAudit('logout', $event->user);
    }

    // -------------------------------------------------------------------------
    // Failed login attempt
    // -------------------------------------------------------------------------
    public function handleFailed(Failed $event): void
    {
        // $event->user may be null if the user was not found at all
        $this->writeAuthAudit('failed_login', $event->user, [
            'attempted_email' => $event->credentials['email'] ?? null,
        ]);
    }

    // -------------------------------------------------------------------------
    // Password reset
    // -------------------------------------------------------------------------
    public function handlePasswordReset(PasswordReset $event): void
    {
        $this->writeAuthAudit('password_reset', $event->user);
    }

    // -------------------------------------------------------------------------
    // Email verification
    // -------------------------------------------------------------------------
    public function handleVerified(Verified $event): void
    {
        $this->writeAuthAudit('email_verified', $event->user);
    }

    // -------------------------------------------------------------------------
    // 2FA — fires when your 2FA package dispatches these events.
    // If you use Laravel Fortify, swap these for the Fortify equivalents.
    // -------------------------------------------------------------------------
    public function handleTwoFactorEnabled($event): void
    {
        $this->writeAuthAudit('two_factor_enabled', $event->user ?? null);
    }

    public function handleTwoFactorDisabled($event): void
    {
        $this->writeAuthAudit('two_factor_disabled', $event->user ?? null);
    }

    // -------------------------------------------------------------------------
    // Shared writer
    // -------------------------------------------------------------------------
    protected function writeAuthAudit(string $event, $user = null, array $extra = []): void
    {
        Audit::create([
            'user_type'      => $user ? get_class($user) : null,
            'user_id'        => $user?->id,
            'event'          => $event,
            'auditable_type' => $user ? get_class($user) : 'Auth',
            'auditable_id'   => $user?->id,
            'old_values'     => null,
            'new_values'     => empty($extra) ? null : $extra,
            'url'            => $this->request->fullUrl(),
            'ip_address'     => $this->request->ip(),
            'user_agent'     => $this->request->userAgent(),
            'method'         => $this->request->method(),
        ]);
    }
}