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

    public function myCourses(Request $request)
    {
        $enrolledCourses = $request->user()->studentCourses;

        // motivational messages
        $messages = [
            "Keep up the great work! You're making progress every day.",
            "Success is the sum of small efforts, repeated day in and day out.",
            "The more that you read, the more things you will know. The more that you learn, the more places you'll go.",
            "Your education is a dress rehearsal for a life that is yours to lead.",
            "Learning never exhausts the mind.",
        ];

        $motivationalMessage = $messages[array_rand($messages)];

        // Calculate statistics using the collection of enrolled courses
        $averageProgress = $enrolledCourses->avg('pivot.course_progress') ?? 0;
        $completedCount = $enrolledCourses->where('pivot.course_progress', 100)->count();
        $inProgressCount = $enrolledCourses->whereBetween('pivot.course_progress', [0, 99])->count();

        return view('dashboard.my-courses', compact('enrolledCourses', 'motivationalMessage', 'averageProgress', 'completedCount', 'inProgressCount'));
    }
}
