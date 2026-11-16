<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Message;
use App\Models\User;
use Auth;
use Illuminate\Http\Request;

class InstructorController extends Controller
{
    //
    public function showCourseManagementPage(Request $request, Course $course)
    {
        $students = $course->students()->withPivot(['course_progress', 'completed_at', 'accessed_at'])->get();
        // The count goes in the delete confirmation
        $course->load(['courseSections' => fn ($query) => $query->withCount('sectionContents')]);
        return view('instructor.course-management', compact('course', 'students'));
    }

    public function manageStudents(Request $request, Course $course)
    {
        // dd($request->all());
        $students = $course->students()->withPivot(['course_progress', 'completed_at', 'accessed_at'])->get();

        // Unread replies to the instructor in this course, keyed by student id: [41 => 2, ...]
        $unreadCounts = Message::where('course_id', $course->id)
            ->where('receiver_id', Auth::id())
            ->where('status', 'active')
            ->where('is_read', false)
            ->selectRaw('sender_id, count(*) as total')
            ->groupBy('sender_id')
            ->pluck('total', 'sender_id');

        // Students who've waited over 24 hours for a reply in this course, for the "Waiting 1d+" badge
        $overdueStudentIds = Message::overdue()
            ->where('course_id', $course->id)
            ->where('receiver_id', Auth::id())
            ->pluck('sender_id')
            ->unique();

        return view('instructor.manage-students', compact('course', 'students', 'unreadCounts', 'overdueStudentIds'));
    }
}