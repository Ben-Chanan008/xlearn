<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CourseRemoved extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Plain values, not the course: it's deleted, so it can't be loaded or linked to by the time this sends
     */
    public function __construct(
        public string $courseName,
        public string $instructorName,
        public int $instructorId,
    ) {
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
     * Skipped for a deleted account, i.e. an instructor who deleted their own profile
     */
    public function shouldSend(User $notifiable, string $channel): bool
    {
        return ! $notifiable->trashed();
    }

    public function toMail(User $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("{$this->courseName} has been removed")
            ->greeting("Hi {$notifiable->first_name},")
            ->line($this->body($notifiable))
            ->action('My courses', $this->url($notifiable))
            ->markdown('notifications::email', ['eyebrow' => 'Your courses', 'accent' => 'grey']);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(User $notifiable): array
    {
        return [
            'title' => 'Course removed',
            'body' => $this->body($notifiable),
            'url' => $this->url($notifiable),
            'icon' => 'fa-trash-can',
        ];
    }

    // The owner is told it was their course; students are told who removed it
    private function body(User $notifiable): string
    {
        return $notifiable->id === $this->instructorId
            ? "Your course {$this->courseName} has been removed."
            : "{$this->courseName} by {$this->instructorName} has been removed, so it's no longer in your courses.";
    }

    private function url(User $notifiable): string
    {
        return $notifiable->id === $this->instructorId ? route('instructor.courses') : route('my-courses');
    }
}
