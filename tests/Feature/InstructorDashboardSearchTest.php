<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstructorDashboardSearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_search_only_shows_matching_courses(): void
    {
        $instructor = User::factory()->instructor()->create();
        Course::factory()->for($instructor, 'owner')->create(['name' => 'Intro to Laravel']);
        Course::factory()->for($instructor, 'owner')->create(['name' => 'Watercolour Basics']);

        $this->actingAs($instructor)->get(route('instructor.dashboard', ['search' => 'laravel']))
            ->assertOk()
            ->assertSee('Intro to Laravel')
            ->assertDontSee('Watercolour Basics');
    }

    public function test_student_dashboard_search_only_shows_matching_courses(): void
    {
        Course::factory()->create(['name' => 'Intro to Laravel']);
        Course::factory()->create(['name' => 'Watercolour Basics']);

        $this->actingAs(User::factory()->create())->get(route('dashboard', ['search' => 'laravel']))
            ->assertOk()
            ->assertSee('data-search-results', false)
            ->assertSee('Intro to Laravel')
            ->assertDontSee('Watercolour Basics');
    }

    public function test_search_is_kept_on_the_page_links(): void
    {
        $instructor = User::factory()->instructor()->create();
        Course::factory(16)->for($instructor, 'owner')->create(['name' => 'Laravel part']);

        $this->actingAs($instructor)->get(route('instructor.dashboard', ['search' => 'laravel']))
            ->assertOk()
            ->assertSee('search=laravel&amp;page=2', false);
    }

    public function test_search_with_no_matches_says_so(): void
    {
        $instructor = User::factory()->instructor()->create();
        Course::factory()->for($instructor, 'owner')->create();

        $this->actingAs($instructor)->get(route('instructor.dashboard', ['search' => 'zzz-nothing']))
            ->assertOk()
            ->assertSee('No courses match')
            ->assertDontSee("You haven't created any courses yet", false);
    }
}
