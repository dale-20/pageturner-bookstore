<?php

namespace App\Notifications;

use App\Models\Review;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewReviewAdminNotification extends Notification
{
    use Queueable;
    protected Review $review;

    /**
     * Create a new notification instance.
     */
    public function __construct(Review $review)
    {
        //
        $this->review = $review;
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
        $stars = str_repeat('★', $this->review->rating) . str_repeat('☆', 5 - $this->review->rating);

        return (new MailMessage)
            ->subject('New Review Submitted – ' . $this->review->book->title)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('A new review has been submitted on PageTurner.')
            ->line('**Book:** ' . $this->review->book->title)
            ->line('**Customer:** ' . $this->review->user->name . ' (' . $this->review->user->email . ')')
            ->line('**Rating:** ' . $stars . ' (' . $this->review->rating . '/5)')
            ->line('**Comment:** ' . ($this->review->comment ?? '*(no comment)*'))
            ->line('**Submitted At:** ' . $this->review->created_at->format('F j, Y g:i A'))
            ->action('View Book', url(route('admin.books.show', $this->review->book->id)))
            ->line('Please review this submission for any policy violations.');
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
