<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Message;
use App\Models\User;
use App\Notifications\EnrollmentConfirmed;
use App\Notifications\ReplyOverdue;
use App\Notifications\StudentEnrolled;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\Markdown;
use Tests\TestCase;

class MailLayoutTest extends TestCase
{
    use RefreshDatabase;

    private Course $course;
    private User $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->course = Course::factory()->create(['price' => 92.41]);
        $this->student = User::factory()->create(['role' => 'student', 'first_name' => 'Jane']);
    }

    public function test_emails_use_the_xlearn_layout(): void
    {
        $html = (string) (new EnrollmentConfirmed($this->course))->toMail($this->student)->render();

        $this->assertStringContainsString(asset('images/logo.png'), $html);
        $this->assertStringContainsString('Enrollment', $html);
        $this->assertStringContainsString('Hi Jane,', $html);
        $this->assertStringContainsString('The xlearn team', $html);
        $this->assertStringContainsString('#996515', $html, 'the xlearn theme is inlined');
        $this->assertStringNotContainsString('Laravel', $html);
    }

    public function test_the_details_box_shows_only_when_passed(): void
    {
        $enrolled = (string) (new EnrollmentConfirmed($this->course))->toMail($this->student)->render();
        $newStudent = (string) (new StudentEnrolled($this->course, $this->student))->toMail($this->course->owner)->render();

        $this->assertStringContainsString('class="details"', $enrolled);
        $this->assertStringContainsString('$92.41', $enrolled);
        $this->assertStringContainsString($this->course->owner->fullName(), $enrolled);
        $this->assertStringNotContainsString('class="details"', $newStudent);
        $this->assertStringNotContainsString('class="callout"', $newStudent);
    }

    public function test_the_callout_and_accent_show_only_when_passed(): void
    {
        $message = Message::create([
            'course_id' => $this->course->id,
            'sender_id' => $this->student->id,
            'receiver_id' => $this->course->instructor_id,
            'content' => 'Hello?',
            'status' => 'active',
        ]);

        $overdue = (string) (new ReplyOverdue($message))->toMail($this->course->owner)->render();
        $enrolled = (string) (new EnrollmentConfirmed($this->course))->toMail($this->student)->render();

        $this->assertStringContainsString('class="callout"', $overdue);
        $this->assertStringContainsString('Slow replies lower your rating.', $overdue);
        $this->assertStringContainsString('accent-golden', $overdue);
        $this->assertStringNotContainsString('class="callout"', $enrolled);
        $this->assertStringContainsString('accent-green', $enrolled);
    }

    public function test_the_plain_text_version_has_no_html(): void
    {
        $mail = (new EnrollmentConfirmed($this->course))->toMail($this->student);
        $text = (string) app(Markdown::class)->renderText($mail->markdown, $mail->data());

        $this->assertStringContainsString('Price paid: $92.41', $text);
        $this->assertStringContainsString('Start learning: ' . route('courses.learn', $this->course->slug), $text);
        $this->assertDoesNotMatchRegularExpression('/<\/?[a-z][^>]*>/i', $text);
    }
}
