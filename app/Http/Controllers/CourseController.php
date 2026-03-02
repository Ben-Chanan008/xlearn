<?php

namespace App\Http\Controllers;

use App\Http\Requests\CourseRequest;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
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
}
