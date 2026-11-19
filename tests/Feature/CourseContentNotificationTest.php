<?php

namespace Tests\Feature;

use App\Jobs\VerifyCourseCanBePublished;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\User;
use App\Notifications\CourseStatusChanged;
use App\Notifications\NewCourseContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CourseContentNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
    }

    // A course with $sections sections and two enrolled students
    private function course(int $sections, string $status = 'published'): Course
    {
        $course = Course::factory()->create(['status' => $status]);
        CourseSection::factory()->count($sections)->sequence(fn ($s) => ['position' => $s->index + 1])->create(['course_id' => $course->id]);
        $course->students()->attach(User::factory()->count(2)->create(['role' => 'student']), ['course_progress' => 0]);

        return $course;
    }

    private function addSection(Course $course)
    {
        return $this->actingAs($course->owner)->postJson(route('instructor.courses.sections.store', $course), [
            'section_name' => 'Week 6',
            'contents' => [[
                'content_name' => 'Final essay',
                'content_mode' => 'text',
                'content_information' => '<p>Write it</p>',
                'is_assignment' => true,
                'due_at' => now()->addDays(5)->toDateTimeString(),
            ]],
        ])->assertCreated();
    }

    public function test_a_new_section_in_a_published_course_notifies_each_enrolled_student(): void
    {
        // 5 sections already, so it stays published
        $course = $this->course(5);
        $outsider = User::factory()->create(['role' => 'student']);

        $this->addSection($course);

        $due = now()->addDays(5)->format('M j');
        Notification::assertSentTo($course->students, NewCourseContent::class, fn ($notification, $channels, $student) =>
            $notification->toArray($student)['body'] === "New section in {$course->name}: Week 6, includes an assignment due {$due}");
        Notification::assertNotSentTo($outsider, NewCourseContent::class);
    }

    public function test_a_new_section_in_a_draft_course_notifies_nobody(): void
    {
        $course = $this->course(1, 'pending');

        $this->addSection($course);

        Notification::assertNothingSent();
    }

    public function test_the_owner_hears_when_the_course_is_published(): void
    {
        $course = $this->course(5, 'pending');

        (new VerifyCourseCanBePublished($course))->handle();

        Notification::assertSentTo($course->owner, CourseStatusChanged::class, fn ($notification) =>
            $notification->status === 'published'
            && $notification->toArray($course->owner)['title'] === 'Your course is live');
    }

    public function test_the_owner_hears_when_the_course_goes_back_to_draft(): void
    {
        $course = $this->course(4);

        (new VerifyCourseCanBePublished($course))->handle();

        Notification::assertSentTo($course->owner, CourseStatusChanged::class, fn ($notification) =>
            $notification->status === 'pending'
            && str_contains($notification->toArray($course->owner)['body'], 'fewer than 5 sections'));
    }

    public function test_no_status_change_sends_nothing(): void
    {
        $course = $this->course(5);

        (new VerifyCourseCanBePublished($course))->handle();

        Notification::assertNothingSent();
    }
}
