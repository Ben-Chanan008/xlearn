<?php

namespace App\Jobs;

use App\Models\CourseSectionsContent;
use App\Notifications\AssignmentDueSoon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Notification;

class SendAssignmentDueReminders implements ShouldQueue
{
    use Queueable;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Each due date lands in exactly one daily window (question 8)
        CourseSectionsContent::where('content_type', 'assignment')
            ->whereBetween('due_at', [now(), now()->addDay()])
            ->with('courseSection.course')
            ->each(function (CourseSectionsContent $assignment) {
                $course = $assignment->courseSection->course;

                if ($course->isDraft())
                    return;

                $submitted = $assignment->assignments()->pluck('student_id');
                Notification::send($course->students()->whereNotIn('users.id', $submitted)->get(), new AssignmentDueSoon($assignment));
            });
    }
}
