<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderPlacedNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    protected Order $order;

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
            ->subject('Order Confirmed – PageTurner #' . $this->order->id)
            ->greeting('Hi ' . $notifiable->name . '!')
            ->line("Your order has been placed successfully. Here's a summary:")
            ->line('**Order ID:** #' . $this->order->id)
            ->line('**Total Amount:** ₱' . number_format($this->order->total_amount, 2))
            ->line('**Status:** ' . ucfirst($this->order->status))
            ->action('View Order', url(route('orders.show', $this->order->id)))
            ->line('Thank you for shopping with PageTurner!');
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
