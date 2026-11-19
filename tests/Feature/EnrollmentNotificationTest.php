<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use App\Notifications\EnrollmentConfirmed;
use App\Notifications\StudentEnrolled;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class EnrollmentNotificationTest extends TestCase
{
    use RefreshDatabase;

    private Course $course;
    private User $student;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();

        $this->course = Course::factory()->create();
        $this->student = User::factory()->create(['role' => 'student']);
    }

    private function checkout(array $card = [])
    {
        return $this->actingAs($this->student)->post(route('courses.checkout', $this->course), $card + [
            'card_number' => '4242424242424242',
            'expiry_date' => '12/30',
            'cvv' => '123',
            'name_on_card' => 'Jane Doe',
        ]);
    }

    public function test_a_successful_checkout_notifies_the_student_and_the_instructor(): void
    {
        $this->checkout()->assertRedirect(route('my-courses'));

        Notification::assertSentTo($this->student, EnrollmentConfirmed::class, function ($notification) {
            $mail = $notification->toMail($this->student)->render();

            // Price and course link are in, card details are not
            return str_contains($mail, number_format((float) $this->course->price, 2))
                && str_contains($mail, route('courses.learn', $this->course->slug))
                && ! str_contains($mail, '4242');
        });

        Notification::assertSentTo($this->course->owner, StudentEnrolled::class, fn ($notification) =>
            $notification->toArray($this->course->owner)['body'] === "{$this->student->shortName()} enrolled in {$this->course->name}");
    }

    public function test_a_full_course_sends_nothing(): void
    {
        $this->course->update(['max_students' => 1]);
        $this->course->students()->attach(User::factory()->create(['role' => 'student']), ['course_progress' => 0]);

        $this->checkout()->assertSessionHas('error');

        Notification::assertNothingSent();
    }

    public function test_a_bad_card_sends_nothing(): void
    {
        $this->checkout(['card_number' => ''])->assertSessionHasErrors('card_number');

        Notification::assertNothingSent();
    }
}
