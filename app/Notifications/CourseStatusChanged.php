<?php

namespace App\Notifications;

use App\Models\Course;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CourseStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * $status is the course's new status: 'published' or 'pending' (back to draft)
     */
    public function __construct(public Course $course, public string $status)
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
        return ['mail', 'database'];
    }

    public function toMail(User $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->title())
            ->greeting("Hi {$notifiable->first_name},")
            ->line($this->body())
            ->action('Manage course', route('instructor.courses.manage', $this->course));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(User $notifiable): array
    {
        return [
            'title' => $this->title(),
            'body' => $this->body(),
            'url' => route('instructor.courses.manage', $this->course),
            'icon' => $this->status === 'published' ? 'fa-rocket' : 'fa-pen-ruler',
            'course_id' => $this->course->id,
            'status' => $this->status,
        ];
    }

    private function title(): string
    {
        return $this->status === 'published' ? 'Your course is live' : 'Your course went back to draft';
    }

    private function body(): string
    {
        if ($this->status === 'published')
            return "{$this->course->name} is published, so students can now find and enroll in it.";

        $minimum = Course::SECTIONS_TO_PUBLISH;

        return "{$this->course->name} went back to draft because it has fewer than {$minimum} sections. Students can't see it until you add more.";
    }
}
