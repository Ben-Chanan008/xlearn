<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseSection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CourseSectionEditTest extends TestCase
{
    use RefreshDatabase;

    private Course $course;

    private CourseSection $section;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->course = Course::factory()->create();
        $this->section = $this->course->courseSections()->create(['section_name' => 'Week 1', 'position' => 1]);
    }

    private function storedFile(string $name): array
    {
        $path = "course-content/test/$name";
        Storage::disk('public')->put($path, 'x');

        return ['path' => $path, 'name' => $name, 'mime' => 'application/pdf'];
    }

    private function update(array $data, ?User $as = null)
    {
        return $this->actingAs($as ?? $this->course->owner)
            ->put(route('instructor.courses.sections.update', [$this->course, $this->section]), $data, ['Accept' => 'application/json']);
    }

    public function test_edit_page_shows_the_saved_items(): void
    {
        $this->section->sectionContents()->create([
            'content_name' => 'Opening lecture', 'content_mode' => 'files', 'content_type' => 'information',
            'files' => [$this->storedFile('slides.pdf')],
        ]);

        $this->actingAs($this->course->owner)
            ->get(route('instructor.courses.sections.edit', [$this->course, $this->section]))
            ->assertOk()
            ->assertSee('Edit Section')
            ->assertSee('value="Week 1"', false)
            ->assertSee('Opening lecture')
            ->assertSee('slides.pdf');
    }

    public function test_instructor_edits_items_files_and_modes(): void
    {
        $reading = $this->section->sectionContents()->create([
            'content_name' => 'Reading', 'content_mode' => 'text', 'content_type' => 'information', 'content_information' => '<p>Old</p>',
        ]);
        $slides = $this->section->sectionContents()->create([
            'content_name' => 'Slides', 'content_mode' => 'files', 'content_type' => 'information',
            'files' => [$this->storedFile('keep.pdf'), $this->storedFile('drop.pdf')],
        ]);
        $homework = $this->section->sectionContents()->create([
            'content_name' => 'Homework', 'content_mode' => 'files', 'content_type' => 'assignment',
            'files' => [$this->storedFile('homework.pdf')],
        ]);
        DB::table('assignment_submissions')->insert([
            'course_sections_content_id' => $homework->id, 'assignment_file' => 'submission.pdf',
            'student_id' => User::factory()->create()->id, 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->update([
            'section_name' => 'Week 1: Basics',
            'contents' => [
                // Switched from text to files
                ['id' => $reading->id, 'content_name' => 'Reading (video)', 'content_mode' => 'files', 'is_assignment' => '0',
                    'files' => [UploadedFile::fake()->create('reading.mp4', 100, 'video/mp4')]],
                // Keeps one file, drops the other
                ['id' => $slides->id, 'content_name' => 'Slides', 'content_mode' => 'files', 'is_assignment' => '0',
                    'keep_files' => ['course-content/test/keep.pdf']],
                // New item; Homework is left out, so it's removed
                ['content_name' => 'Summary', 'content_mode' => 'text', 'is_assignment' => '0', 'content_information' => '<p>Recap</p>'],
            ],
        ])->assertOk()->assertJsonPath('redirect', route('instructor.courses.manage', $this->course));

        $this->assertSame('Week 1: Basics', $this->section->fresh()->section_name);

        $reading->refresh();
        $this->assertSame('files', $reading->content_mode);
        $this->assertNull($reading->content_information);
        $this->assertSame('reading.mp4', $reading->files[0]['name']);

        $this->assertSame(['course-content/test/keep.pdf'], array_column($slides->fresh()->files, 'path'));
        Storage::disk('public')->assertExists('course-content/test/keep.pdf');
        Storage::disk('public')->assertMissing('course-content/test/drop.pdf');

        $this->assertSoftDeleted($homework);
        // A removed item keeps its files and its students' submissions
        Storage::disk('public')->assertExists('course-content/test/homework.pdf');
        $this->assertDatabaseHas('assignment_submissions', ['course_sections_content_id' => $homework->id]);

        $this->assertSame(['Reading (video)', 'Slides', 'Summary'], $this->section->sectionContents()->pluck('content_name')->all());
    }

    public function test_switching_to_text_removes_the_files(): void
    {
        $item = $this->section->sectionContents()->create([
            'content_name' => 'Notes', 'content_mode' => 'both', 'content_type' => 'information',
            'content_information' => '<p>Hi</p>', 'files' => [$this->storedFile('notes.pdf')],
        ]);

        $this->update([
            'section_name' => 'Week 1',
            'contents' => [['id' => $item->id, 'content_name' => 'Notes', 'content_mode' => 'text', 'content_information' => '<p>Hi</p>',
                'keep_files' => ['course-content/test/notes.pdf']]],
        ])->assertOk();

        $this->assertNull($item->fresh()->files);
        Storage::disk('public')->assertMissing('course-content/test/notes.pdf');
    }

    public function test_an_item_from_another_section_is_rejected(): void
    {
        $otherSection = Course::factory()->create()->courseSections()->create(['section_name' => 'Other', 'position' => 1]);
        $otherItem = $otherSection->sectionContents()->create([
            'content_name' => 'Not yours', 'content_mode' => 'text', 'content_type' => 'information', 'content_information' => '<p>x</p>',
        ]);

        $this->update([
            'section_name' => 'Week 1',
            'contents' => [['id' => $otherItem->id, 'content_name' => 'Taken', 'content_mode' => 'text', 'content_information' => '<p>y</p>']],
        ])->assertStatus(422)->assertJsonValidationErrors(['contents.0.id']);

        $this->assertSame('Not yours', $otherItem->fresh()->content_name);
    }

    public function test_a_forged_kept_path_is_ignored(): void
    {
        Storage::disk('public')->put('course-content/other/secret.pdf', 'x');
        $item = $this->section->sectionContents()->create([
            'content_name' => 'Slides', 'content_mode' => 'files', 'content_type' => 'information', 'files' => [$this->storedFile('mine.pdf')],
        ]);

        // A path the item never had doesn't count as a file
        $this->update([
            'section_name' => 'Week 1',
            'contents' => [['id' => $item->id, 'content_name' => 'Slides', 'content_mode' => 'files', 'keep_files' => ['course-content/other/secret.pdf']]],
        ])->assertStatus(422)->assertJsonValidationErrors(['contents.0.files']);

        $this->update([
            'section_name' => 'Week 1',
            'contents' => [['id' => $item->id, 'content_name' => 'Slides', 'content_mode' => 'files',
                'keep_files' => ['course-content/test/mine.pdf', 'course-content/other/secret.pdf']]],
        ])->assertOk();

        $this->assertSame(['course-content/test/mine.pdf'], array_column($item->fresh()->files, 'path'));
        Storage::disk('public')->assertExists('course-content/other/secret.pdf');
    }

    public function test_an_unchanged_past_due_date_is_allowed_but_a_new_one_is_not(): void
    {
        $item = $this->section->sectionContents()->create([
            'content_name' => 'Homework', 'content_mode' => 'text', 'content_type' => 'assignment',
            'content_information' => '<p>Do it</p>', 'due_at' => '2020-01-15 18:00:00',
        ]);
        $content = ['id' => $item->id, 'content_name' => 'Homework', 'content_mode' => 'text', 'content_information' => '<p>Do it</p>', 'is_assignment' => '1'];

        // What the page sends back for the saved date
        $this->update(['section_name' => 'Week 1', 'contents' => [[...$content, 'due_at' => '2020-01-15T18:00:00.000Z']]])
            ->assertOk();

        $this->update(['section_name' => 'Week 1', 'contents' => [[...$content, 'due_at' => '2020-02-01T18:00:00.000Z']]])
            ->assertStatus(422)->assertJsonValidationErrors(['contents.0.due_at']);
    }

    public function test_only_the_owner_can_edit(): void
    {
        $other = User::factory()->instructor()->create();

        $this->actingAs($other)->get(route('instructor.courses.sections.edit', [$this->course, $this->section]))->assertForbidden();
        $this->update(['section_name' => 'Hijacked'], $other)->assertForbidden();
    }

    public function test_a_section_from_another_course_is_not_found(): void
    {
        $otherSection = Course::factory()->create()->courseSections()->create(['section_name' => 'Other', 'position' => 1]);

        $this->actingAs($this->course->owner)
            ->get(route('instructor.courses.sections.edit', [$this->course, $otherSection]))
            ->assertNotFound();
    }

    public function test_rename_still_works_on_its_new_route(): void
    {
        $this->actingAs($this->course->owner)
            ->patchJson(route('instructor.courses.sections.rename', [$this->course, $this->section]), ['section_name' => 'Renamed'])
            ->assertOk();

        $this->assertSame('Renamed', $this->section->fresh()->section_name);
    }
}
