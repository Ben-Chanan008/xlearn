<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\InstructorReview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstructorReviewTest extends TestCase
{
    use RefreshDatabase;

    private Course $course;
    private User $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->course = Course::factory()->create();
        $this->student = User::factory()->create();
        $this->course->students()->attach($this->student, ['course_progress' => 0]);
    }

    private function review(User $student, array $data)
    {
        return $this->actingAs($student)->post(route('course.review', $this->course), $data);
    }

    public function test_an_enrolled_student_can_review(): void
    {
        $this->review($this->student, ['stars' => 4, 'comment' => 'Helpful'])->assertRedirect();

        $this->assertDatabaseHas('instructor_reviews', [
            'student_id' => $this->student->id,
            'instructor_id' => $this->course->instructor_id,
            'stars' => 4,
        ]);
        // The rating updates straight away, without waiting for the hourly job
        $this->assertSame(4.0, $this->course->owner->fresh()->rating);
    }

    public function test_a_second_review_replaces_the_first(): void
    {
        $this->review($this->student, ['stars' => 2]);
        $this->review($this->student, ['stars' => 5]);

        $this->assertSame(1, InstructorReview::count());
        $this->assertSame(5, InstructorReview::first()->stars);
    }

    public function test_a_student_who_is_not_enrolled_gets_403(): void
    {
        $this->review(User::factory()->create(), ['stars' => 5])->assertForbidden();
    }

    public function test_stars_outside_1_to_5_are_rejected(): void
    {
        $this->review($this->student, ['stars' => 0])->assertSessionHasErrors('stars');
        $this->review($this->student, ['stars' => 6])->assertSessionHasErrors('stars');

        $this->assertSame(0, InstructorReview::count());
    }
}
