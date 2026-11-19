<?php

namespace App\Notifications;

use App\Models\CourseSectionsContent;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AssignmentDueSoon extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public CourseSectionsContent $assignment)
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
            ->subject("{$this->assignment->content_name} is due soon")
            ->greeting("Hi {$notifiable->first_name},")
            ->line($this->body())
            ->action('Submit it', $this->url());
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(User $notifiable): array
    {
        return [
            'title' => 'Assignment due soon',
            'body' => $this->body(),
            'url' => $this->url(),
            'icon' => 'fa-clock',
            'course_id' => $this->assignment->courseSection->course_id,
            'content_id' => $this->assignment->id,
        ];
    }

    private function body(): string
    {
        $course = $this->assignment->courseSection->course;

        return "{$this->assignment->content_name} in {$course->name} is due {$this->assignment->due_at->format('M j \a\t g:i A')}, and you haven't submitted it yet.";
    }

    private function url(): string
    {
        return route('courses.learn', $this->assignment->courseSection->course->slug);
    }
}
