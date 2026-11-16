<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CoursePolicy
{
    /**
     * Determine whether the user can manage the model (instructor/owner).
     */
    public function manage(User $user, Course $course): bool
    {
        return $user->is($course->owner);
    }

    /**
     * Anyone can view a published course; an unfinished one only its instructor
     */
    public function view(User $user, Course $course): bool
    {
        return $course->status === 'published' || $this->manage($user, $course);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
//        dd($user->role === 'instructor');
        return $user->role === 'instructor' || $user->role === 'admin';
    }

    public function enroll(User $user, Course $course): bool
    {
        return $user->role === 'student';
    }

    public function enrolled(User $user, Course $course): bool
    {
        return $course->students()->where(['student_id' => $user->id])->exists();
    }
}