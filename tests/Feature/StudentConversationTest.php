<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentConversationTest extends TestCase
{
    use RefreshDatabase;

    private Course $course;
    private User $student;

    protected function setUp(): void
    {
        parent::setUp();

        // The page loads CSS/JS through Vite; skip that so the test doesn't need a frontend build
        $this->withoutVite();

        $this->course = Course::factory()->create();
        $this->student = User::factory()->create();
        $this->course->students()->attach($this->student, ['course_progress' => 0]);
    }

    private function message(User $from, User $to, string $content, array $extra = []): Message
    {
        return Message::create(['sender_id' => $from->id, 'receiver_id' => $to->id, 'course_id' => $this->course->id, 'content' => $content, ...$extra]);
    }

    private function openPage()
    {
        return $this->actingAs($this->student)->get(route('course.messages', $this->course));
    }

    public function test_it_shows_both_sides_of_the_conversation_in_order(): void
    {
        $this->message($this->course->owner, $this->student, 'Welcome to the course', ['created_at' => now()->subHour()]);
        $this->message($this->student, $this->course->owner, 'Thanks, I have a question');

        $this->openPage()->assertOk()->assertSeeInOrder(['Welcome to the course', 'Thanks, I have a question']);
    }

    public function test_it_shows_inactive_messages(): void
    {
        $this->message($this->course->owner, $this->student, 'An old announcement', ['status' => 'inactive']);

        $this->openPage()->assertOk()->assertSee('An old announcement');
    }

    public function test_it_does_not_show_another_students_messages(): void
    {
        $classmate = User::factory()->create();
        $this->course->students()->attach($classmate, ['course_progress' => 0]);
        $this->message($this->course->owner, $classmate, 'Private note for your classmate');

        $this->openPage()->assertOk()->assertDontSee('Private note for your classmate');
    }

    public function test_opening_it_marks_the_instructors_messages_read(): void
    {
        $message = $this->message($this->course->owner, $this->student, 'Read me');

        $this->openPage();

        $this->assertTrue((bool) $message->fresh()->is_read);
    }
}
