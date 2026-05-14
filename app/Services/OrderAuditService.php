<?php

namespace App\Services;

use App\Models\Audit;
use App\Notifications\CriticalEventNotification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

/**
 * Handles custom audit logging for Order status transitions.
 *
 * Usage — call this from your OrderController or Order service layer:
 *
 *   app(OrderAuditService::class)->transition($order, 'pending', 'processing');
 *
 * Valid status flow:
 *   pending → processing → shipped → delivered
 *   any     → cancelled
 *   any     → refunded
 */
class OrderAuditService
{
    /** Status transitions that are flagged as critical and trigger admin alerts. */
    protected array $criticalTransitions = [
        'cancelled',
        'refunded',
    ];

    public function __construct(protected Request $request) {}

    /**
     * Record an order status transition as an audit event.
     *
     * @param  mixed   $order      The Order model instance
     * @param  string  $fromStatus Previous status
     * @param  string  $toStatus   New status
     * @param  array   $meta       Optional extra context (reason, refund amount, etc.)
     */
    public function transition(mixed $order, string $fromStatus, string $toStatus, array $meta = []): void
    {
        Audit::create([
            'user_type'      => Auth::check() ? get_class(Auth::user()) : null,
            'user_id'        => Auth::id(),
            'event'          => 'order_status_transition',
            'auditable_type' => get_class($order),
            'auditable_id'   => $order->getKey(),
            'old_values'     => ['status' => $fromStatus],
            'new_values'     => array_merge(['status' => $toStatus], $meta),
            'url'            => $this->request->fullUrl(),
            'ip_address'     => $this->request->ip(),
            'user_agent'     => $this->request->userAgent(),
            'method'         => $this->request->method(),
        ]);

        if (in_array($toStatus, $this->criticalTransitions, true)) {
            $this->alertAdmins($order, $fromStatus, $toStatus);
        }
    }

    protected function alertAdmins(mixed $order, string $fromStatus, string $toStatus): void
    {
        $admins = User::where('is_admin', true)->get();

        if ($admins->isEmpty()) {
            return;
        }

        Notification::send($admins, new CriticalEventNotification(
            event: "order_{$toStatus}",
            modelType: get_class($order),
            modelId: $order->getKey(),
            triggeredById: Auth::id(),
            triggeredByEmail: Auth::user()?->email ?? 'system',
            extra: [
                'from_status' => $fromStatus,
                'to_status'   => $toStatus,
                'order_ref'   => $order->reference ?? $order->getKey(),
            ],
        ));
    }
}