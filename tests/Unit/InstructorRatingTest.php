<?php

namespace Tests\Unit;

use App\Jobs\RecalculateInstructorRatings;
use App\Models\Course;
use App\Models\InstructorReview;
use App\Models\Message;
use App\Models\User;
use App\Services\InstructorRating;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstructorRatingTest extends TestCase
{
    use RefreshDatabase;

    private Course $course;
    private User $student;

    protected function setUp(): void
    {
        parent::setUp();

        // Stop the clock so "now" doesn't move while a test runs
        $this->freezeTime();

        $this->course = Course::factory()->create();
        $this->student = User::factory()->create();
        $this->course->students()->attach($this->student, ['course_progress' => 0]);
    }

    /**
     * A student message that started a clock $hoursAgo hours ago, answered $repliedAfter hours later (null = no reply yet)
     */
    private function wait(float $hoursAgo, ?float $repliedAfter = null): Message
    {
        $sentAt = now()->subMinutes((int) ($hoursAgo * 60));

        return Message::create([
            'sender_id' => $this->student->id,
            'receiver_id' => $this->course->instructor_id,
            'course_id' => $this->course->id,
            'content' => 'Question',
            'awaits_reply' => true,
            'created_at' => $sentAt,
            'replied_at' => $repliedAfter === null ? null : $sentAt->copy()->addMinutes((int) ($repliedAfter * 60)),
        ]);
    }

    public function test_each_band_boundary(): void
    {
        $this->assertSame(5, InstructorRating::points($this->wait(100, 2)));
        $this->assertSame(4, InstructorRating::points($this->wait(100, 2 + 1 / 60)));
        $this->assertSame(3, InstructorRating::points($this->wait(100, 24)));
        $this->assertSame(1, InstructorRating::points($this->wait(100, 47)));
        $this->assertSame(0, InstructorRating::points($this->wait(100, 49)));
    }

    public function test_unanswered_messages_are_scored_only_after_24_hours(): void
    {
        $this->assertNull(InstructorRating::points($this->wait(23)));
        $this->assertSame(1, InstructorRating::points($this->wait(30)));
    }

    public function test_fewer_than_3_samples_means_no_response_score(): void
    {
        $this->wait(100, 1);
        $this->wait(100, 1);

        InstructorRating::refresh($this->course->owner);

        $this->assertNull($this->course->owner->fresh()->response_score);
    }

    public function test_high_stars_are_capped_at_4_when_under_80_percent_are_on_time(): void
    {
        // 3 fast replies (5 points) and 2 late ones (0 points): responsiveness 3.0, on time 60%
        foreach (range(1, 3) as $i)
            $this->wait(100, 1);
        foreach (range(1, 2) as $i)
            $this->wait(100, 49);

        InstructorReview::create(['course_id' => $this->course->id, 'student_id' => $this->student->id, 'instructor_id' => $this->course->instructor_id, 'stars' => 5]);

        InstructorRating::refresh($this->course->owner);

        // Uncapped it would be 5 × 0.7 + 3 × 0.3 = 4.4
        $instructor = $this->course->owner->fresh();
        $this->assertSame(4.0, $instructor->rating);
        $this->assertSame(60, $instructor->on_time_rate);
    }

    public function test_the_hourly_job_refreshes_every_instructor(): void
    {
        foreach (range(1, 3) as $i)
            $this->wait(100, 1);

        (new RecalculateInstructorRatings())->handle();

        $instructor = $this->course->owner->fresh();
        $this->assertSame(5.0, $instructor->response_score);
        $this->assertSame(5.0, $instructor->rating);
        $this->assertSame(100, $instructor->on_time_rate);
    }
}
