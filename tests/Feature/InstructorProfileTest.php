<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\InstructorProfile;
use App\Models\InstructorReview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InstructorProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    private function validProfile(array $overrides = []): array
    {
        return array_merge([
            'headline' => 'Laravel mentor',
            'bio' => 'I have built web apps for ten years.',
            'expertise' => ['Laravel', 'Vue'],
            'years_experience' => 10,
            'website' => 'https://example.com',
            'linkedin' => 'https://www.linkedin.com/in/someone',
        ], $overrides);
    }

    // Viewing

    public function test_a_student_can_view_an_instructors_profile(): void
    {
        $profile = InstructorProfile::factory()->create(['headline' => 'Data science mentor', 'bio' => 'Twenty years of SQL.']);
        $published = Course::factory()->for($profile->user, 'owner')->create(['name' => 'Published SQL']);
        Course::factory()->pending()->for($profile->user, 'owner')->create(['name' => 'Unfinished draft']);

        $this->actingAs(User::factory()->create())
            ->get(route('instructors.show', $profile->user))
            ->assertOk()
            ->assertSee('Data science mentor')
            ->assertSee('Twenty years of SQL.')
            ->assertSee($published->name)
            ->assertDontSee('Unfinished draft')
            ->assertDontSee('Edit profile');
    }

    public function test_a_students_id_is_not_a_profile(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('instructors.show', User::factory()->create()))
            ->assertNotFound();
    }

    public function test_guests_are_sent_to_sign_in(): void
    {
        $this->get(route('instructors.show', User::factory()->instructor()->create()))
            ->assertRedirect(route('login'));
    }

    public function test_the_owner_sees_the_edit_controls(): void
    {
        $instructor = User::factory()->instructor()->create();

        $this->actingAs($instructor)
            ->get(route('instructors.show', $instructor))
            ->assertOk()
            ->assertSee('Edit profile')
            ->assertSee('Add a bio');
    }

    // Reviews

    public function test_reviews_from_every_course_show_with_short_names(): void
    {
        $instructor = User::factory()->instructor()->create();
        $first = Course::factory()->for($instructor, 'owner')->create(['name' => 'First course']);
        $second = Course::factory()->for($instructor, 'owner')->create(['name' => 'Second course']);
        $ada = User::factory()->create(['first_name' => 'Ada', 'last_name' => 'Lovelace']);

        InstructorReview::factory()->for($first)->for($ada, 'student')->create(['stars' => 5, 'comment' => 'Brilliant teacher']);
        InstructorReview::factory()->for($second)->create(['stars' => 3, 'comment' => 'Solid course']);
        // Another instructor's review must not show up
        InstructorReview::factory()->create(['comment' => 'Someone else entirely']);

        $this->actingAs(User::factory()->create())
            ->get(route('instructors.show', $instructor))
            ->assertOk()
            ->assertSee('Brilliant teacher')
            ->assertSee('Solid course')
            ->assertDontSee('Someone else entirely')
            ->assertSee('Ada L.')
            ->assertDontSee('Lovelace')
            ->assertSee('4.0')
            ->assertSee('2 reviews');
    }

    public function test_reviews_are_paginated_for_show_more(): void
    {
        $instructor = User::factory()->instructor()->create();
        $course = Course::factory()->for($instructor, 'owner')->create();
        InstructorReview::factory(7)->for($course)->create();

        $page = $this->actingAs(User::factory()->create())->get(route('instructors.show', $instructor));
        $page->assertOk();

        // 5 on the first page, and the link to the next one is where "Show more" reads it from
        $this->assertSame(5, substr_count($page->getContent(), '<article'));
        $page->assertSee('reviews=2', false);

        $next = $this->get(route('instructors.show', [$instructor, 'reviews' => 2]));
        $this->assertSame(2, substr_count($next->getContent(), '<article'));
    }

    // Updating

    public function test_the_owner_can_update_their_profile(): void
    {
        $instructor = User::factory()->instructor()->create();

        $this->actingAs($instructor)
            ->putJson(route('instructors.update', $instructor), $this->validProfile())
            ->assertOk()
            ->assertJsonPath('profile.headline', 'Laravel mentor')
            ->assertJsonPath('profile.expertise', ['Laravel', 'Vue']);

        $this->assertSame('Laravel mentor', $instructor->instructorProfile()->first()->headline);

        // Saving again updates the same row rather than adding one
        $this->putJson(route('instructors.update', $instructor), $this->validProfile(['headline' => 'Changed']))->assertOk();
        $this->assertSame(1, InstructorProfile::count());
        $this->assertSame('Changed', $instructor->instructorProfile()->first()->headline);
    }

    public function test_clearing_a_field_saves_it_as_empty(): void
    {
        $profile = InstructorProfile::factory()->create();

        $this->actingAs($profile->user)
            ->putJson(route('instructors.update', $profile->user), ['headline' => 'Only this'])
            ->assertOk();

        $profile->refresh();
        $this->assertSame('Only this', $profile->headline);
        $this->assertNull($profile->bio);
        $this->assertNull($profile->expertise);
    }

    public function test_another_instructor_cannot_update_the_profile(): void
    {
        $instructor = User::factory()->instructor()->create();

        $this->actingAs(User::factory()->instructor()->create())
            ->putJson(route('instructors.update', $instructor), $this->validProfile())
            ->assertForbidden();
    }

    public function test_a_student_cannot_update_a_profile(): void
    {
        $student = User::factory()->create();

        $this->actingAs($student)
            ->putJson(route('instructors.update', $student), $this->validProfile())
            ->assertForbidden();
    }

    public function test_invalid_input_is_rejected(): void
    {
        $instructor = User::factory()->instructor()->create();

        $this->actingAs($instructor)
            ->putJson(route('instructors.update', $instructor), $this->validProfile([
                'bio' => str_repeat('a', 2001),
                'expertise' => ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I'],
                'website' => 'not a url',
                'avatar' => UploadedFile::fake()->create('notes.pdf', 10, 'application/pdf'),
            ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['bio', 'expertise', 'website', 'avatar']);
    }

    public function test_a_new_avatar_replaces_and_deletes_the_old_one(): void
    {
        Storage::fake('public');
        $instructor = User::factory()->instructor()->create();

        $this->actingAs($instructor)
            ->putJson(route('instructors.update', $instructor), ['avatar' => UploadedFile::fake()->image('first.jpg')])
            ->assertOk();
        $first = $instructor->fresh()->avatar;
        Storage::disk('public')->assertExists($first);

        $this->putJson(route('instructors.update', $instructor), ['avatar' => UploadedFile::fake()->image('second.jpg')])
            ->assertOk()
            ->assertJsonPath('avatar_url', $instructor->fresh()->avatarUrl());

        Storage::disk('public')->assertMissing($first);
        Storage::disk('public')->assertExists($instructor->fresh()->avatar);
    }

    // Deleting

    public function test_the_owner_can_delete_their_account(): void
    {
        $instructor = User::factory()->instructor()->create();

        $this->actingAs($instructor)
            ->deleteJson(route('instructors.delete', $instructor))
            ->assertOk()
            ->assertJsonPath('redirect_url', route('home'));

        // Soft deleted: the row stays, with deleted_at set
        $this->assertSoftDeleted($instructor);
    }

    public function test_nobody_else_can_delete_the_account(): void
    {
        $instructor = User::factory()->instructor()->create();

        $this->actingAs(User::factory()->instructor()->create())
            ->deleteJson(route('instructors.delete', $instructor))
            ->assertForbidden();

        $this->assertNotSoftDeleted($instructor);
    }

    public function test_only_the_owner_gets_the_delete_button(): void
    {
        $instructor = User::factory()->instructor()->create();

        $this->actingAs($instructor)->get(route('instructors.show', $instructor))->assertSee('Delete account');
        $this->actingAs(User::factory()->create())->get(route('instructors.show', $instructor))->assertDontSee('Delete account');
    }

    // Completion

    public function test_profile_completion_counts_the_five_parts(): void
    {
        $instructor = User::factory()->instructor()->create();
        $this->assertSame(0, $instructor->profileCompletion());

        InstructorProfile::factory()->blank()->for($instructor)->create(['headline' => 'Hi', 'bio' => 'Bio']);
        $this->assertSame(40, $instructor->fresh()->profileCompletion());

        // instructorProfile() (a query), not ->instructorProfile: the first call above cached "no profile" on $instructor
        $instructor->instructorProfile()->update(['expertise' => ['PHP'], 'linkedin' => 'https://linkedin.com/in/x']);
        $instructor->update(['avatar' => 'avatars/me.jpg']);
        $this->assertSame(100, $instructor->fresh()->profileCompletion());
    }

    public function test_the_dashboard_banner_shows_until_the_profile_is_complete(): void
    {
        $instructor = User::factory()->instructor()->create();

        $this->actingAs($instructor)->get(route('instructor.dashboard'))
            ->assertOk()
            ->assertSee('Complete your profile · 0%');

        InstructorProfile::factory()->for($instructor)->create();
        $instructor->update(['avatar' => 'avatars/me.jpg']);

        $this->actingAs($instructor->fresh())->get(route('instructor.dashboard'))
            ->assertOk()
            ->assertDontSee('data-profile-banner', false);
    }

    // Linking

    public function test_the_course_page_links_to_the_instructor(): void
    {
        $course = Course::factory()->create();

        $this->actingAs(User::factory()->create())
            ->get(route('courses.show', $course->slug))
            ->assertOk()
            ->assertSee(route('instructors.show', $course->owner), false);
    }
}
