<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use App\Notifications\CourseRemoved;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CourseRemovedNotificationTest extends TestCase
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

    public function test_deleting_a_course_notifies_its_students_and_owner_only(): void
    {
        $course = Course::factory()->create();
        $student = $this->enrolledStudent($course);
        $otherStudent = $this->enrolledStudent(Course::factory()->create());

        $this->actingAs($course->owner)->delete(route('courses.destroy', $course))->assertSessionHas('success');

        Notification::assertSentTo($student, CourseRemoved::class, fn ($notification) =>
            $notification->toArray($student)['url'] === route('my-courses'));
        Notification::assertSentTo($course->owner, CourseRemoved::class, fn ($notification) =>
            $notification->toArray($course->owner)['body'] === "Your course {$course->name} has been removed.");
        Notification::assertNotSentTo($otherStudent, CourseRemoved::class);
    }

    public function test_deleting_an_instructors_profile_notifies_the_students_of_each_course(): void
    {
        $instructor = User::factory()->instructor()->create();
        [$first, $second] = Course::factory()->count(2)->create(['instructor_id' => $instructor->id]);
        $firstStudent = $this->enrolledStudent($first);
        $secondStudent = $this->enrolledStudent($second);

        $this->actingAs($instructor)->deleteJson(route('instructors.delete', $instructor))->assertOk();

        Notification::assertSentTo($firstStudent, CourseRemoved::class, fn ($notification) => $notification->courseName === $first->name);
        Notification::assertSentTo($secondStudent, CourseRemoved::class, fn ($notification) => $notification->courseName === $second->name);
    }

    public function test_force_deleting_a_removed_course_sends_nothing_more(): void
    {
        $instructor = User::factory()->instructor()->create();
        $course = Course::factory()->create(['instructor_id' => $instructor->id]);
        $this->enrolledStudent($course);
        $instructor->delete();
        Notification::fake(); // forget the notifications from the soft delete above

        $course->fresh()->forceDelete();

        Notification::assertNothingSent();
    }

    public function test_an_instructor_who_deleted_their_profile_is_not_told_about_their_own_courses(): void
    {
        $instructor = User::factory()->instructor()->create();
        $course = Course::factory()->create(['instructor_id' => $instructor->id]);
        $student = $this->enrolledStudent($course);

        $this->actingAs($instructor)->deleteJson(route('instructors.delete', $instructor))->assertOk();

        // shouldSend runs when the queue sends it, after the profile delete has committed
        $notification = new CourseRemoved($course->name, $instructor->fullName(), $instructor->id);
        $this->assertFalse($notification->shouldSend($instructor->fresh(), 'mail'));
        $this->assertTrue($notification->shouldSend($student, 'mail'));
    }
}
