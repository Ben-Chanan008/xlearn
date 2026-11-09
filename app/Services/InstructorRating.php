<?php

namespace App\Services;

use App\Models\Message;
use App\Models\User;

/**
 * The rubric in plans/messaging.md
 */
class InstructorRating
{
    const WINDOW_DAYS = 90;
    const MIN_SAMPLES = 3;
    const STARS_WEIGHT = 0.7;
    // Below this share of replies within 24 hours, the rating can't reach the top band
    const ON_TIME_TARGET = 80;
    const LATE_CAP = 4.0;

    /**
     * Points for one message that started a clock; null while it's unanswered and under 24 hours old
     */
    public static function points(Message $message): ?int
    {
        $hours = $message->created_at->diffInHours($message->replied_at ?? now());

        if (! $message->replied_at && $hours < 24)
            return null;

        return match (true) {
            $hours <= 2 => 5,
            $hours <= 8 => 4,
            $hours <= 24 => 3,
            $hours <= 48 => 1,
            default => 0,
        };
    }

    /**
     * Writes rating, response_score and on_time_rate onto the instructor
     */
    public static function refresh(User $instructor): void
    {
        $points = Message::where('receiver_id', $instructor->id)
            ->where('awaits_reply', true)
            ->where('created_at', '>=', now()->subDays(self::WINDOW_DAYS))
            ->get()
            ->map(fn ($message) => self::points($message))
            ->reject(fn ($points) => $points === null);

        $enough = $points->count() >= self::MIN_SAMPLES;
        $response = $enough ? $points->avg() : null;
        // 3 or more points means it was answered within 24 hours
        $onTime = $enough ? (int) round($points->filter(fn ($p) => $p >= 3)->count() / $points->count() * 100) : null;

        // Null until the instructor has reviews
        $stars = $instructor->reviewsReceived()->avg('stars');

        $rating = match (true) {
            $stars !== null && $response !== null => $stars * self::STARS_WEIGHT + $response * (1 - self::STARS_WEIGHT),
            default => $stars ?? $response,
        };

        if ($rating !== null && $onTime !== null && $onTime < self::ON_TIME_TARGET)
            $rating = min($rating, self::LATE_CAP);

        $instructor->update([
            // Never below 1.0, the bottom of the star scale
            'rating' => $rating === null ? null : round(max($rating, 1), 1),
            'response_score' => $response === null ? null : round($response, 2),
            'on_time_rate' => $onTime,
            'rating_updated_at' => now(),
        ]);
    }
}
