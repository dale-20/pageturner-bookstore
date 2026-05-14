<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CriticalEventNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string  $event,
        public readonly string  $modelType,
        public readonly mixed   $modelId,
        public readonly ?int    $triggeredById,
        public readonly string  $triggeredByEmail,
        public readonly array   $extra = [],
    ) {}

    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        $modelShort = class_basename($this->modelType);
        $eventLabel = $this->humanReadableEvent();
        $subject    = "[Security Alert] {$eventLabel} — {$modelShort} #{$this->modelId}";

        $mail = (new MailMessage)
            ->subject($subject)
            ->greeting('Security Alert')
            ->line("A critical event was recorded in your audit log.")
            ->line('')
            ->line("**Event:** {$eventLabel}")
            ->line("**Affected:** {$modelShort} #{$this->modelId}")
            ->line("**Performed by:** {$this->triggeredByEmail} (ID: {$this->triggeredById})")
            ->line("**Time:** " . now()->toDateTimeString());

        // Append any extra context (e.g. order status transitions)
        if (!empty($this->extra)) {
            $mail->line('');
            $mail->line('**Additional context:**');
            foreach ($this->extra as $key => $value) {
                $label = ucwords(str_replace('_', ' ', $key));
                $mail->line("- {$label}: {$value}");
            }
        }

        $mail->action('View Audit Log', url('/admin/audit-logs'));
        $mail->line('This is an automated security notification. Do not reply to this email.');

        return $mail;
    }

    protected function humanReadableEvent(): string
    {
        $map = [
            'role_assigned'          => 'Role Assigned',
            'role_revoked'           => 'Role Revoked',
            'permission_granted'     => 'Permission Granted',
            'permission_revoked'     => 'Permission Revoked',
            'deleted'                => 'Record Deleted',
            'order_cancelled'        => 'Order Cancelled',
            'order_refunded'         => 'Order Refunded',
            'two_factor_disabled'    => '2FA Disabled',
            'password_reset'         => 'Password Reset',
        ];

        return $map[$this->event] ?? ucwords(str_replace('_', ' ', $this->event));
    }
}