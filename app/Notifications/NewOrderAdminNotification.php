<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Order;

class NewOrderAdminNotification extends Notification
{
    use Queueable;
    protected Order $order;

    /**
     * Create a new notification instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
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

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Order Received – PageTurner #' . $this->order->id)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('A new order has been placed on PageTurner.')
            ->line('**Order ID:** #' . $this->order->id)
            ->line('**Customer:** ' . $this->order->user->name . ' (' . $this->order->user->email . ')')
            ->line('**Total Amount:** ₱' . number_format($this->order->total_amount, 2))
            ->line('**Items:** ' . $this->order->orderItems->count() . ' item(s)')
            ->line('**Placed At:** ' . $this->order->created_at->format('F j, Y g:i A'))
            ->action('View Order', url(route('admin.orderShow', $this->order->id)))
            ->line('Please review and process this order.');
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
