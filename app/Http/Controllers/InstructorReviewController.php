<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInstructorReviewRequest;
use App\Models\Course;
use App\Models\InstructorReview;
use App\Notifications\ReviewReceived;
use App\Services\InstructorRating;

class InstructorReviewController extends Controller
{
    /**
     * Saves the student's review of this course's instructor, replacing any earlier one
     */
    public function store(StoreInstructorReviewRequest $request, Course $course)
    {
        $review = InstructorReview::updateOrCreate(
            ['course_id' => $course->id, 'student_id' => $request->user()->id],
            ['instructor_id' => $course->instructor_id, ...$request->validated()],
        );

        // Edits to an existing review don't notify again
        if ($review->wasRecentlyCreated)
            $course->owner->notify(new ReviewReceived($review));

        // Recalculate now so the new stars show without waiting for the hourly job
        InstructorRating::refresh($course->owner);

        return back()->with('success', 'Thanks! Your review has been saved.');
    }
}
