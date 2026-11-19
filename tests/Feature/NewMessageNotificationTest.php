<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Message;
use App\Models\User;
use App\Notifications\NewMessage;
use Illuminate\Broadcasting\Broadcasters\PusherBroadcaster;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Broadcast;
use Mockery;
use Pusher\Pusher;
use Tests\TestCase;

class NewMessageNotificationTest extends TestCase
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
        $this->student = User::factory()->create(['role' => 'student']);
        $this->course->students()->attach($this->student, ['course_progress' => 0]);
    }

    private function send(User $sender, User $receiver)
    {
        return $this->actingAs($sender)->postJson(route('send-message'), [
            'course_id' => $this->course->id,
            'receiver_id' => $receiver->id,
            'content' => 'Hello',
        ])->assertOk();
    }

    // Stands in for Reverb: $user is in the presence channel of $student's conversation, and nobody is in any other
    private function viewing(User $user, User $student): void
    {
        $viewed = "presence-conversation.{$this->course->id}.{$student->id}.{$this->instructor->id}";

        $pusher = Mockery::mock(Pusher::class);
        $pusher->shouldReceive('trigger');
        $pusher->shouldReceive('getPresenceUsers')->andReturnUsing(fn ($channel) => (object) [
            'users' => $channel === $viewed ? [(object) ['id' => $user->id]] : [],
        ]);

        Broadcast::extend('fake-reverb', fn () => new PusherBroadcaster($pusher));
        config([
            'broadcasting.connections.fake-reverb' => ['driver' => 'fake-reverb'],
            'broadcasting.default' => 'fake-reverb',
        ]);
    }

    private function unread(User $user)
    {
        return $user->unreadNotifications()->where('type', NewMessage::class)->get();
    }

    public function test_a_student_message_notifies_the_instructor(): void
    {
        $this->send($this->student, $this->instructor);

        $notifications = $this->unread($this->instructor);
        $this->assertCount(1, $notifications);
        $this->assertSame(
            route('instructor.courses.students', [$this->course, 'conversation' => $this->student->id]),
            $notifications->first()->data['url'],
        );
        $this->assertCount(0, $this->unread($this->student));
    }

    public function test_an_instructor_message_notifies_the_student(): void
    {
        $this->send($this->instructor, $this->student);

        $notifications = $this->unread($this->student);
        $this->assertCount(1, $notifications);
        $this->assertSame(route('course.messages', $this->course), $notifications->first()->data['url']);
        $this->assertCount(0, $this->unread($this->instructor));
    }

    public function test_more_messages_before_reading_do_not_add_notifications(): void
    {
        $this->send($this->student, $this->instructor);
        $this->send($this->student, $this->instructor);
        $this->send($this->student, $this->instructor);

        $this->assertCount(1, $this->unread($this->instructor));
    }

    public function test_an_unread_notification_holds_back_new_ones_however_old(): void
    {
        $this->send($this->student, $this->instructor);

        $this->travel(5)->hours();
        $this->send($this->student, $this->instructor);

        $this->assertCount(1, $this->unread($this->instructor));
    }

    public function test_opening_the_conversation_marks_every_unread_notification_read(): void
    {
        $this->send($this->student, $this->instructor);
        $this->travel(4)->hours();
        $this->send($this->student, $this->instructor);

        $this->actingAs($this->instructor)
            ->getJson(route('instructor.courses.conversation', [$this->course, $this->student]))
            ->assertOk();

        $this->assertCount(0, $this->unread($this->instructor));
    }

    public function test_each_conversation_gets_its_own_notification(): void
    {
        $classmate = User::factory()->create(['role' => 'student']);
        $this->course->students()->attach($classmate, ['course_progress' => 0]);

        $this->send($this->student, $this->instructor);
        $this->send($classmate, $this->instructor);

        $this->assertCount(2, $this->unread($this->instructor));
    }

    public function test_the_instructor_opening_the_conversation_marks_it_read(): void
    {
        $this->send($this->student, $this->instructor);

        $this->actingAs($this->instructor)
            ->getJson(route('instructor.courses.conversation', [$this->course, $this->student]))
            ->assertOk();

        $this->assertCount(0, $this->unread($this->instructor));

        // Not viewing it any more, so the next message notifies again
        $this->send($this->student, $this->instructor);
        $this->assertCount(1, $this->unread($this->instructor));
    }

    public function test_the_instructor_viewing_the_conversation_gets_no_notification(): void
    {
        $this->viewing($this->instructor, $this->student);

        $this->send($this->student, $this->instructor);

        $this->assertCount(0, $this->unread($this->instructor));
    }

    public function test_the_student_viewing_the_conversation_gets_no_notification(): void
    {
        $this->viewing($this->student, $this->student);

        $this->send($this->instructor, $this->student);

        $this->assertCount(0, $this->unread($this->student));
    }

    public function test_viewing_another_students_conversation_still_notifies(): void
    {
        $classmate = User::factory()->create(['role' => 'student']);
        $this->course->students()->attach($classmate, ['course_progress' => 0]);
        $this->viewing($this->instructor, $classmate);

        $this->send($this->student, $this->instructor);

        $this->assertCount(1, $this->unread($this->instructor));
    }

    public function test_the_student_opening_messages_marks_it_read(): void
    {
        $this->send($this->instructor, $this->student);

        $this->actingAs($this->student)->get(route('course.messages', $this->course))->assertOk();

        $this->assertCount(0, $this->unread($this->student));
    }

    public function test_messages_read_while_the_dialog_was_open_clear_the_notification(): void
    {
        $this->send($this->student, $this->instructor);
        $message = Message::where('sender_id', $this->student->id)->first();

        $this->actingAs($this->instructor)
            ->patchJson(route('instructor.courses.messages.read', $this->course), ['ids' => [$message->id]])
            ->assertOk();

        $this->assertCount(0, $this->unread($this->instructor));
    }
}
