<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowRatingsTest extends TestCase
{
    use RefreshDatabase;

    private Course $course;
    private User $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $this->course = Course::factory()->create();
        $this->student = $this->enrol(User::factory()->create(), $this->course);
    }

    private function enrol(User $student, Course $course): User
    {
        $course->students()->attach($student, ['course_progress' => 0]);

        return $student;
    }

    private function wait(User $student, Course $course, string $content, int $hoursAgo): void
    {
        Message::create([
            'sender_id' => $student->id,
            'receiver_id' => $course->instructor_id,
            'course_id' => $course->id,
            'content' => $content,
            'awaits_reply' => true,
            'created_at' => now()->subHours($hoursAgo),
        ]);
    }

    public function test_the_badge_and_waiting_list_show_only_this_instructors_overdue_conversations(): void
    {
        $recent = $this->enrol(User::factory()->create(), $this->course);
        $this->wait($this->student, $this->course, 'Overdue question', 30);
        $this->wait($recent, $this->course, 'Fresh question', 2);

        // Overdue too, but for a different instructor
        $otherCourse = Course::factory()->create();
        $this->wait($this->enrol(User::factory()->create(), $otherCourse), $otherCourse, 'Someone else’s student', 30);

        $instructor = $this->course->owner;

        $this->actingAs($instructor)->get(route('instructor.dashboard'))
            ->assertOk()
            ->assertSee('Overdue question')
            ->assertDontSee('Fresh question')
            ->assertDontSee('Someone else’s student');

        $page = $this->actingAs($instructor)->get(route('instructor.courses.students', $this->course))->assertOk();
        // One badge: the student waiting 30 hours, not the one waiting 2
        $this->assertSame(1, substr_count($page->getContent(), 'data-overdue-badge'));
    }

    public function test_the_course_page_shows_new_instructor_when_there_is_no_rating(): void
    {
        $this->actingAs($this->student)->get(route('courses.show', $this->course->slug))
            ->assertOk()
            ->assertSee('New instructor');
    }
}
