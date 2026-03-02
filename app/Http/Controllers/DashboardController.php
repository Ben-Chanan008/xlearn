<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Dashboard;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $courses = Course::with('instructor')->latest()->get();
        return view('dashboard.index', compact('courses'));
    }

    public function show(Course $course)
    {
        return view('dashboard.show', ['course' => $course->with('instructor')->first()]);
    }

    public function myCourses()
    {
        // Currently, we don't have an enrollment system, so we'll show an empty list
        // and allow the user to see how it looks.
        $enrolledCourses = [];

        // motivational messages
        $messages = [
            "Keep up the great work! You're making progress every day.",
            "Success is the sum of small efforts, repeated day in and day out.",
            "The more that you read, the more things you will know. The more that you learn, the more places you'll go.",
            "Your education is a dress rehearsal for a life that is yours to lead.",
            "Learning never exhausts the mind.",
        ];

        $motivationalMessage = $messages[array_rand($messages)];

        // Calculate average progress for the statistics section
        $averageProgress = 0;
        $completedCount = 0;
        $inProgressCount = 0;

        return view('dashboard.my-courses', compact('enrolledCourses', 'motivationalMessage', 'averageProgress', 'completedCount', 'inProgressCount'));
    }
}
