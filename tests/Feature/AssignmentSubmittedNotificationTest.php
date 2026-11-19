<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseSection;
use App\Models\CourseSectionsContent;
use App\Models\User;
use App\Notifications\AssignmentSubmitted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AssignmentSubmittedNotificationTest extends TestCase
{
    use RefreshDatabase;

    private Course $course;
    private CourseSectionsContent $assignment;
    private User $student;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        Storage::fake('public');

        $this->course = Course::factory()->create();
        $this->assignment = CourseSectionsContent::factory()->assignment()->create([
            'course_section_id' => CourseSection::factory()->create(['course_id' => $this->course->id])->id,
            'content_name' => 'Week 3 Essay',
        ]);
        $this->student = User::factory()->create(['role' => 'student']);
    }

    private function submit(int $files)
    {
        return $this->actingAs($this->student)->post(route('courses.submit-assignment', $this->assignment), [
            'assignment' => array_map(fn ($i) => UploadedFile::fake()->create("essay-$i.pdf", 100, 'application/pdf'), range(1, $files)),
        ]);
    }

    public function test_a_3_file_submission_sends_one_notification(): void
    {
        $this->course->students()->attach($this->student, ['course_progress' => 0]);

        $this->submit(3)->assertSessionHas('success');

        Notification::assertSentToTimes($this->course->owner, AssignmentSubmitted::class, 1);
        Notification::assertSentTo($this->course->owner, AssignmentSubmitted::class, fn ($notification) =>
            $notification->toArray($this->course->owner)['body'] === "{$this->student->shortName()} submitted assignment for Week 3 Essay");
    }

    public function test_a_non_enrolled_students_submission_sends_nothing(): void
    {
        $this->submit(1)->assertSessionHas('error');

        Notification::assertNothingSent();
    }
}
