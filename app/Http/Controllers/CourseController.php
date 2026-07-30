<?php

namespace App\Http\Controllers;

use App\Http\Requests\CourseRequest;
use App\Models\CardDetail;
use App\Models\Course;
use App\Models\CourseSectionsContent;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CourseController extends Controller
{
    //
    public function generateCode($name): string
    {
        $num = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        $first_letter = Str::charAt($name, 0);
        $random_letters = Str::substr($name, 1, 3); // Take 3 letters from name

        $numbers = Arr::join(Arr::random($num, 3),'');
        return strtoupper($first_letter . $random_letters . $numbers);
    }

    public function store(CourseRequest $request)
    {
        $validated = $request->validated();

        $validated['instructor_id'] = Auth::id();
        $validated['slug'] = Str::slug($validated['name']);

        if ($request->hasFile('thumbnail')) {
            $validated['thumbnail'] = $request->file('thumbnail')->store('courses', 'public');
        }

        $course = Course::create($validated);

        if($course)
            return redirect()->route('dashboard')->with('success', 'Course created successfully');

        return back()->with('error', 'Something went wrong');
    }

    public function create()
    {
        $course_code = $this->generateCode('XLEARN');

        while(Course::where(['course_code' => $course_code])->exists())
            $course_code = $this->generateCode('XLEARN');

        return view('course.create', compact('course_code'));
    }

    public function enroll(Request $request, Course $course)
    {
        return view('course.enroll', compact('course'));
    }

    public function checkout(Request $request, Course $course)
    {
        Gate::authorize('enroll', $course);

        //check seating isn't full
        //check access is still valid
        //
        $request->validate([
            'card_number' => 'required',
            'expiry_date' => 'required|max:5',
            'cvv' => 'required',
            'name_on_card' => 'required'
        ]);

        $number_of_students = $course->max_students;
        $number_of_enrolled = $course->students()->get()->count();

        if($number_of_enrolled === $number_of_students){
            if($course->status !== 'active')
                return back()->with('error', 'Course is full, wait for the next available seats');
        }

        // charge the user. send notification of transaction

        try {
            CardDetail::create([
                'user_id' => $request->user()->id,
                'name_on_card' => $request->name_on_card,
                'card_number' => $request->card_number,
                'expiry_date' => $request->expiry_date,
                'cvv' => Hash::make($request->cvv),
            ]);

            $course->students()->syncWithoutDetaching($request->user()->id);
            return redirect()->route('my-courses')->with('success', 'Course enrolled successfully');
        } catch (\Exception $e) {
//            dd($e->getMessage());
            return back()->with('error', 'Something went wrong');
        }
    }

    public function edit(Course $course)
    {
        Gate::authorize('manage', $course);

        return view('course.edit', compact('course'));
    }

    public function update(CourseRequest $request, Course $course)
    {
        Gate::authorize('manage', $course);

        $validated = $request->validated();

        if ($request->hasFile('thumbnail')) {
            $validated['thumbnail'] = $request->file('thumbnail')->store('courses', 'public');
        }

        $validated['slug'] = Str::slug($validated['name']);

        $course->update($validated);

        return redirect()->route('dashboard')->with('success', 'Course updated successfully');
    }

    public function delete(Request $request, Course $course)
    {
        Gate::authorize('manage', $course);

        $course->delete($course->id);
//        Possibly add a notification to notify the instructor and the fellow students
        return back()->with('success', 'Course deleted successfully');
    }

    public function submitAssignment(Request $request, CourseSectionsContent $courseSectionContent)
    {
        // dd($request->all());
        $course = $courseSectionContent->courseSection->course;
        $request->validate([
            'assignment' => ['required', 'array'],
            'assignment.*' => 'file|mimes:pdf,doc,docx|max:4096', // Max size 4MB
        ]);

        // $response = Gate::inspect('enrolled', $course);
        // Check if the user is enrolled in the course
        if (Gate::denies('enrolled', $course)) {
            return redirect()->back()->with('error', 'You are not enrolled in this course.');
        }
        dd($request->file('assignment'));

        try{
            // Store the assignment file
            foreach ($request->file('assignment') as $file) {
                $path = $file->store("assignments/course-$course->course_code/section-#{$courseSectionContent->courseSection->id}", 'public');

                // Save the assignment submission to the database (assuming you have a model for it)
                $courseSectionContent->assignments()->create([
                    'students_id' => Auth::id(),
                    'assignment_file' => $path,
                ]);
            }
            // $path = $request->file('assignment')->store("assignments/course-$course->course_code/section-#{$courseSectionContent->courseSection->id}", 'public');

            // Save the assignment submission to the database (assuming you have a model for it)
            // $courseSectionContent->assignments()->create([
            //     'students_id' => Auth::id(),
            //     'assignment_file' => $path,
            // ]);

            //Notify the instructor of the submission (assuming you have a notification system in place)

            return back()->with('success', 'Assignment submitted successfully.');
        } catch (\Exception $e) {
            dd($e->getMessage());
        }
    } 

    public function showGrades(Request $request, Course $course)
    {
        return view('student.course-grades', compact('course'));
    }
}