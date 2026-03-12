<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Order;

class OrderStatusChangedNotification extends Notification
{
    use Queueable;
    protected Order $order;
    protected string $oldStatus;
    /**
     * Create a new notification instance.
     */
    public function __construct(Order $order, string $oldStatus)
    {
        $this->order = $order;
        $this->oldStatus = $oldStatus;
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
        $statusMessages = [
            'processing' => 'Your order is now being processed. We\'ll have it ready soon!',
            'completed' => 'Great news! Your order has been completed. Enjoy your books!',
            'cancelled' => 'Your order has been cancelled. If you have questions, please contact us.',
        ];

        $message = $statusMessages[$this->order->status]
            ?? 'Your order status has been updated.';

        return (new MailMessage)
            ->subject('Order Update – PageTurner #' . $this->order->id)
            ->greeting('Hi ' . $notifiable->name . '!')
            ->line($message)
            ->line('**Order ID:** #' . $this->order->id)
            ->line('**Previous Status:** ' . ucfirst($this->oldStatus))
            ->line('**New Status:** ' . ucfirst($this->order->status))
            ->line('**Total Amount:** ₱' . number_format($this->order->total_amount, 2))
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
