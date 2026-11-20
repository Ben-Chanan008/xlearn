<?php

namespace App\Notifications;

use App\Models\CourseSection;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

class NewCourseContent extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public CourseSection $section)
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
        return ['database', 'broadcast'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(User $notifiable): array
    {
        $course = $this->section->course;
        $body = "New section in {$course->name}: {$this->section->section_name}";

        // The soonest assignment due date in the new section, if it has one
        $due = $this->section->sectionContents()
            ->where('content_type', 'assignment')
            ->whereNotNull('due_at')
            ->min('due_at');

        if ($due)
            $body .= ', includes an assignment due ' . Carbon::parse($due)->format('M j');

        return [
            'title' => 'New content',
            'body' => $body,
            'url' => route('courses.learn', $course->slug),
            'icon' => 'fa-book-open',
            'course_id' => $course->id,
            'section_id' => $this->section->id,
        ];
    }
}
