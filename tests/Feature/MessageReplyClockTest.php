<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessageReplyClockTest extends TestCase
{
    use RefreshDatabase;

    private Course $course;
    private User $instructor;
    private User $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->course = Course::factory()->create();
        $this->instructor = $this->course->owner;
        $this->student = User::factory()->create();
        $this->course->students()->attach($this->student, ['course_progress' => 0]);
    }

    private function send(User $sender, User $receiver, string $content): Message
    {
        $this->actingAs($sender)->postJson(route('send-message'), [
            'course_id' => $this->course->id,
            'receiver_id' => $receiver->id,
            'content' => $content,
        ])->assertOk();

        return Message::where('content', $content)->sole();
    }

    public function test_the_first_student_message_starts_the_clock(): void
    {
        $this->assertTrue($this->send($this->student, $this->instructor, 'First')->awaits_reply);
    }

    public function test_a_second_message_before_a_reply_does_not_start_another_clock(): void
    {
        $this->send($this->student, $this->instructor, 'First');

        $this->assertFalse($this->send($this->student, $this->instructor, 'Second')->awaits_reply);
    }

    public function test_an_instructor_reply_stops_the_clock_on_the_first_message_only(): void
    {
        $first = $this->send($this->student, $this->instructor, 'First');
        $second = $this->send($this->student, $this->instructor, 'Second');

        $this->send($this->instructor, $this->student, 'Reply');

        $this->assertNotNull($first->fresh()->replied_at);
        $this->assertNull($second->fresh()->replied_at);
    }

    public function test_the_next_student_message_after_a_reply_starts_a_new_clock(): void
    {
        $this->send($this->student, $this->instructor, 'First');
        $this->send($this->instructor, $this->student, 'Reply');

        $this->assertTrue($this->send($this->student, $this->instructor, 'Follow-up')->awaits_reply);
    }

    public function test_instructor_first_messages_never_start_a_clock(): void
    {
        $this->assertFalse($this->send($this->instructor, $this->student, 'Welcome')->awaits_reply);
    }
}
