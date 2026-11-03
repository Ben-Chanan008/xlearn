<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseSectionDeleteTest extends TestCase
{
    use RefreshDatabase;

    private Course $course;

    protected function setUp(): void
    {
        parent::setUp();

        $this->course = Course::factory()->create();
        foreach (['One', 'Two', 'Three', 'Four'] as $index => $name)
            $this->course->courseSections()->create(['section_name' => $name, 'position' => $index + 1]);
    }

    private function section(string $name)
    {
        return $this->course->courseSections()->where('section_name', $name)->first();
    }

    public function test_deleting_soft_deletes_and_renumbers_the_rest(): void
    {
        $two = $this->section('Two');
        $item = $two->sectionContents()->create([
            'content_name' => 'Lesson', 'content_mode' => 'text', 'content_type' => 'information', 'content_information' => '<p>x</p>',
        ]);

        $this->actingAs($this->course->owner)
            ->deleteJson(route('instructor.courses.sections.destroy', [$this->course, $two]))
            ->assertOk()->assertJsonPath('message', 'Section deleted successfully');

        $this->assertSoftDeleted($two);
        // The section's content stays, hidden along with it
        $this->assertNotSoftDeleted($item);
        $this->assertSame(
            ['One' => 1, 'Three' => 2, 'Four' => 3],
            $this->course->courseSections()->pluck('position', 'section_name')->all()
        );
    }

    public function test_a_deleted_section_disappears_for_students(): void
    {
        $student = User::factory()->create();
        $this->course->students()->attach($student, ['course_progress' => 0]);

        $this->actingAs($this->course->owner)->deleteJson(route('instructor.courses.sections.destroy', [$this->course, $this->section('Two')]));

        $this->actingAs($student)->get(route('courses.learn', $this->course->slug))
            ->assertOk()
            ->assertSee('Three')
            ->assertDontSee('>Two<', false);
    }

    public function test_the_management_page_counts_each_sections_content(): void
    {
        $this->section('One')->sectionContents()->create([
            'content_name' => 'Lesson', 'content_mode' => 'text', 'content_type' => 'information', 'content_information' => '<p>x</p>',
        ]);

        $this->actingAs($this->course->owner)->get(route('instructor.courses.manage', $this->course))
            ->assertOk()
            ->assertSee('data-content-count="1"', false)
            ->assertSee('data-delete-section', false);
    }

    public function test_a_section_from_another_course_is_not_found(): void
    {
        $otherSection = Course::factory()->create()->courseSections()->create(['section_name' => 'Other', 'position' => 1]);

        $this->actingAs($this->course->owner)
            ->deleteJson(route('instructor.courses.sections.destroy', [$this->course, $otherSection]))
            ->assertNotFound();

        $this->assertNotSoftDeleted($otherSection);
    }

    public function test_only_the_owner_can_delete(): void
    {
        $this->actingAs(User::factory()->instructor()->create())
            ->deleteJson(route('instructor.courses.sections.destroy', [$this->course, $this->section('One')]))
            ->assertForbidden();

        $this->assertSame(4, $this->course->courseSections()->count());
    }
}
