<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TwoFactorEnabledNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
protected string $type;

    public function __construct(string $type)
    {
        $this->type = $type;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $methodLabel = $this->type === 'totp'
            ? 'Authenticator App (TOTP)'
            : 'Email One-Time Password';

        return (new MailMessage)
            ->subject('Two-Factor Authentication Enabled – PageTurner')
            ->greeting('Hi ' . $notifiable->name . '!')
            ->line('Two-factor authentication has been successfully enabled on your PageTurner account.')
            ->line('**Method:** ' . $methodLabel)
            ->line('**Enabled At:** ' . now()->format('F j, Y g:i A'))
            ->line('From now on, you will be asked to verify your identity each time you sign in.')
            ->action('Go to My Profile', url(route('profile.edit')))
            ->line('If you did not make this change, please contact us immediately or disable 2FA from your profile.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
