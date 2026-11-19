<?php

namespace Tests\Feature;

use App\Jobs\SendAssignmentDueReminders;
use App\Jobs\SendReplyReminders;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\CourseSectionsContent;
use App\Models\Message;
use App\Models\User;
use App\Notifications\AssignmentDueSoon;
use App\Notifications\ReplyOverdue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ScheduledReminderTest extends TestCase
{
    use RefreshDatabase;

    private Course $course;
    private User $student;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        $this->travelTo('2026-11-24 09:00:00');

        $this->course = Course::factory()->create();
        $this->student = User::factory()->create(['role' => 'student']);
        $this->course->students()->attach($this->student, ['course_progress' => 0]);
    }

    private function waiting(int $hours): Message
    {
        return Message::create([
            'sender_id' => $this->student->id,
            'receiver_id' => $this->course->instructor_id,
            'course_id' => $this->course->id,
            'content' => 'Can you help?',
            'awaits_reply' => true,
            'created_at' => now()->subHours($hours),
        ]);
    }

    private function assignmentDueIn(int $hours): CourseSectionsContent
    {
        return CourseSectionsContent::factory()->assignment()->create([
            'course_section_id' => CourseSection::factory()->create(['course_id' => $this->course->id])->id,
            'content_name' => 'Week 3 Essay',
            'due_at' => now()->addHours($hours),
        ]);
    }

    public function test_a_message_waiting_25_hours_gets_one_reminder(): void
    {
        $message = $this->waiting(25);

        (new SendReplyReminders)->handle();

        Notification::assertSentTo($this->course->owner, ReplyOverdue::class, fn ($notification) =>
            $notification->toArray($this->course->owner)['body'] === "{$this->student->shortName()} has been waiting over a day for a reply in {$this->course->name}. Slow replies lower your rating.");
        $this->assertNotNull($message->fresh()->reminded_at);

        // The next hourly run doesn't remind again
        $this->travel(1)->hour();
        (new SendReplyReminders)->handle();

        Notification::assertSentToTimes($this->course->owner, ReplyOverdue::class, 1);
    }

    public function test_a_message_waiting_20_hours_gets_no_reminder(): void
    {
        $this->waiting(20);

        (new SendReplyReminders)->handle();

        Notification::assertNothingSent();
    }

    public function test_an_assignment_due_in_10_hours_reminds_only_students_who_have_not_submitted(): void
    {
        $submitter = User::factory()->create(['role' => 'student']);
        $this->course->students()->attach($submitter, ['course_progress' => 0]);
        $assignment = $this->assignmentDueIn(10);
        $assignment->assignments()->create(['student_id' => $submitter->id, 'assignment_file' => 'essay.pdf']);

        (new SendAssignmentDueReminders)->handle();

        Notification::assertSentTo($this->student, AssignmentDueSoon::class, fn ($notification) =>
            str_starts_with($notification->toArray($this->student)['body'], "Week 3 Essay in {$this->course->name} is due Nov 24 at 7:00 PM"));
        Notification::assertNotSentTo($submitter, AssignmentDueSoon::class);
    }

    public function test_an_assignment_due_in_30_hours_reminds_nobody(): void
    {
        $this->assignmentDueIn(30);

        (new SendAssignmentDueReminders)->handle();

        Notification::assertNothingSent();
    }
}
