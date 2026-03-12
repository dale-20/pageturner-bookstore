<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TwoFactorDisabledNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Two-Factor Authentication Disabled – PageTurner')
            ->greeting('Hi ' . $notifiable->name . '!')
            ->line('Two-factor authentication has been **disabled** on your PageTurner account.')
            ->line('**Disabled At:** ' . now()->format('F j, Y g:i A'))
            ->line('Your account is now less secure. You can re-enable 2FA at any time from your profile.')
            ->action('Re-enable 2FA', url(route('profile.edit')))
            ->line('If you did not make this change, please reset your password immediately as your account may be compromised.');
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
