<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseVisibilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    private function open(User $user, Course $course)
    {
        return $this->actingAs($user)->get(route('courses.show', $course->slug));
    }

    public function test_a_student_can_open_a_published_course(): void
    {
        $this->open(User::factory()->create(), Course::factory()->create())->assertOk();
    }

    public function test_a_student_cannot_open_an_unfinished_course(): void
    {
        $this->open(User::factory()->create(), Course::factory()->pending()->create())->assertForbidden();
    }

    public function test_the_instructor_can_open_their_own_unfinished_course(): void
    {
        $course = Course::factory()->pending()->create();

        $this->open($course->owner, $course)->assertOk();
    }

    public function test_another_instructor_cannot_open_an_unfinished_course(): void
    {
        $this->open(User::factory()->instructor()->create(), Course::factory()->pending()->create())->assertForbidden();
    }

    public function test_the_instructor_is_told_about_draft_courses(): void
    {
        $instructor = User::factory()->instructor()->create();
        $draft = Course::factory()->pending()->for($instructor, 'owner')->create();
        $draft->courseSections()->create(['section_name' => 'Intro', 'position' => 1]);
        Course::factory()->for($instructor, 'owner')->create();

        $this->actingAs($instructor)->get(route('instructor.dashboard'))
            ->assertOk()
            ->assertSee('You have 1 draft course')
            ->assertSee('Draft · 1/5 sections');

        $this->actingAs($instructor)->get(route('instructor.courses'))
            ->assertOk()
            ->assertSee('Draft · 1/5 sections');

        $this->open($instructor, $draft)->assertSee('This course is a draft');
    }

    public function test_no_draft_banner_when_everything_is_published(): void
    {
        $course = Course::factory()->create();

        $this->actingAs($course->owner)->get(route('instructor.dashboard'))
            ->assertOk()
            ->assertDontSee('data-draft-banner', false)
            ->assertDontSee('data-draft-badge', false);

        $this->open($course->owner, $course)->assertDontSee('This course is a draft');
    }
}
