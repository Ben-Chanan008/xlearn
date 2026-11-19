<?php

namespace App\Notifications;

use App\Models\Course;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StudentEnrolled extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Course $course, public User $student)
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

    /**
     * Tells the instructor who joined which course
     */
    public function toMail(User $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("New student in {$this->course->name}")
            ->greeting("Hi {$notifiable->first_name},")
            ->line("{$this->student->fullName()} enrolled in {$this->course->name}.")
            ->action('View students', route('instructor.courses.students', $this->course));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(User $notifiable): array
    {
        return [
            'title' => 'New student',
            'body' => "{$this->student->shortName()} enrolled in {$this->course->name}",
            'url' => route('instructor.courses.students', $this->course),
            'icon' => 'fa-user-plus',
            'course_id' => $this->course->id,
            'student_id' => $this->student->id,
        ];
    }
}