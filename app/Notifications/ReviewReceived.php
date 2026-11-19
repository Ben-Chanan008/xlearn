<?php

namespace App\Notifications;

use App\Models\InstructorReview;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ReviewReceived extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public InstructorReview $review)
    {
        $this->afterCommit();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(User $notifiable): array
    {
        return [
            'title' => 'New review',
            'body' => "{$this->review->student->shortName()} left a {$this->review->stars}★ review on {$this->review->course->name}",
            'url' => route('instructors.show', $notifiable),
            'icon' => 'fa-star',
            'course_id' => $this->review->course_id,
            'review_id' => $this->review->id,
        ];
    }
}
