<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\InstructorRating;

class RecalculateInstructorRatings
{
    /**
     * Refreshes every instructor's rating, so unanswered messages keep lowering it while the student waits
     */
    public function handle(): void
    {
        User::where('role', 'instructor')->each(fn ($instructor) => InstructorRating::refresh($instructor));
    }
}
