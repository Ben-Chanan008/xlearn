<?php

namespace App\Notifications;

use App\Models\CourseSectionsContent;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class AssignmentSubmitted extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public CourseSectionsContent $assignment,
        public User $student,
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
        return ['database', 'broadcast'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(User $notifiable): array
    {
        $course = $this->assignment->courseSection->course;

        return [
            'title' => 'Assignment submitted',
            'body' => "{$this->student->shortName()} submitted assignment for {$this->assignment->content_name}",
            // No grading page yet: swap this for it once there is one
            'url' => route('instructor.courses.manage', $course),
            'icon' => 'fa-file-upload',
            'course_id' => $course->id,
            'student_id' => $this->student->id,
        ];
    }
}