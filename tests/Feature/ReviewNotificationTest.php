<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use App\Notifications\ReviewReceived;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ReviewNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
    }

    private function enrolledStudent(Course $course): User
    {
        $student = User::factory()->create(['role' => 'student']);
        $course->students()->attach($student, ['course_progress' => 0]);

        return $student;
    }

    public function test_a_first_review_notifies_the_instructor(): void
    {
        $course = Course::factory()->create();
        $student = $this->enrolledStudent($course);

        $this->actingAs($student)->post(route('course.review', $course), ['stars' => 4])->assertSessionHas('success');

        Notification::assertSentTo($course->owner, ReviewReceived::class, fn ($notification) =>
            $notification->toArray($course->owner)['body'] === "{$student->shortName()} left a 4★ review on {$course->name}");
    }

    public function test_editing_a_review_does_not_notify_again(): void
    {
        $course = Course::factory()->create();
        $student = $this->enrolledStudent($course);

        $this->actingAs($student)->post(route('course.review', $course), ['stars' => 4]);
        $this->actingAs($student)->post(route('course.review', $course), ['stars' => 2, 'comment' => 'Changed my mind']);

        Notification::assertSentToTimes($course->owner, ReviewReceived::class, 1);
    }
}
