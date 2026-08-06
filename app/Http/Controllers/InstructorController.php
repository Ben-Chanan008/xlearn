<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;

class InstructorController extends Controller
{
    //
    public function showCourseManagementPage(Request $request, Course $course)
    {
        $students = $course->students()->withPivot(['course_progress', 'completed_at', 'accessed_at'])->get();
        return view('instructor.course-management', compact('course', 'students'));
    }

    public function manageStudents(Request $request, Course $course)
    {
        // dd($request->all());
        $students = $course->students()->withPivot(['course_progress', 'completed_at', 'accessed_at'])->get();

        return view('instructor.manage-students', compact('course', 'students'));
    }
}