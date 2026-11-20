<?php

namespace App\Notifications;

use App\Models\Course;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EnrollmentConfirmed extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Course $course)
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
        return ['mail', 'database', 'broadcast'];
    }

    /**
     * The student's receipt: course and price only, never card details
     */
    public function toMail(User $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("You're enrolled in {$this->course->name}")
            ->greeting("Hi {$notifiable->first_name},")
            ->line("You're now enrolled in {$this->course->name}.")
            ->line('Price paid: $' . number_format((float) $this->course->price, 2))
            ->action('Start learning', route('courses.learn', $this->course->slug));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(User $notifiable): array
    {
        return [
            'title' => 'Enrollment confirmed',
            'body' => "You're enrolled in {$this->course->name}",
            'url' => route('courses.learn', $this->course->slug),
            'icon' => 'fa-graduation-cap',
            'course_id' => $this->course->id,
        ];
    }
}
