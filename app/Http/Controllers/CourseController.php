<?php

namespace App\Http\Controllers;

use App\Http\Requests\CourseRequest;
use App\Models\CardDetail;
use App\Models\Course;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
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

        $validated['instructor_id'] = auth()->id();
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
            $course_code = $this->generateCode('LARAVEL CODE');

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
        } catch (\Exception $e) {
            dd($e->getMessage());
            return back()->with('error', 'Something went wrong');
        }
    }
}
