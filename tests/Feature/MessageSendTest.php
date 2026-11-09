<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessageSendTest extends TestCase
{
    use RefreshDatabase;

    private Course $course;
    private User $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->course = Course::factory()->create();
        $this->student = $this->enrol(User::factory()->create(), $this->course);
    }

    private function enrol(User $student, Course $course): User
    {
        $course->students()->attach($student, ['course_progress' => 0]);

        return $student;
    }

    private function send(User $sender, User $receiver, Course $course, array $extra = [])
    {
        return $this->actingAs($sender)->postJson(route('send-message'), [
            'course_id' => $course->id,
            'receiver_id' => $receiver->id,
            'content' => 'Hello',
            ...$extra,
        ]);
    }

    public function test_a_student_can_message_their_instructor(): void
    {
        $this->send($this->student, $this->course->owner, $this->course)->assertOk();

        $this->assertDatabaseHas('messages', ['sender_id' => $this->student->id, 'receiver_id' => $this->course->instructor_id]);
    }

    public function test_an_instructor_can_message_an_enrolled_student(): void
    {
        $this->send($this->course->owner, $this->student, $this->course)->assertOk();
    }

    public function test_a_student_cannot_message_another_student(): void
    {
        $classmate = $this->enrol(User::factory()->create(), $this->course);

        $this->send($this->student, $classmate, $this->course)->assertForbidden();
    }

    public function test_a_student_cannot_message_the_instructor_of_a_course_they_are_not_in(): void
    {
        $other = Course::factory()->create();

        $this->send($this->student, $other->owner, $other)->assertForbidden();
    }

    public function test_a_forged_sender_id_is_ignored(): void
    {
        $victim = $this->enrol(User::factory()->create(), $this->course);

        $this->send($this->student, $this->course->owner, $this->course, ['sender_id' => $victim->id])->assertOk();

        $this->assertDatabaseHas('messages', ['sender_id' => $this->student->id]);
        $this->assertDatabaseMissing('messages', ['sender_id' => $victim->id]);
    }
}
