<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCourseSectionRequest;
use App\Models\Course;

class CourseSectionController extends Controller
{
    public function store(StoreCourseSectionRequest $request, Course $course)
    {
        $validated = $request->validated();

        try {
            // New sections go at the end of the course
            $section = $course->courseSections()->create([
                'section_name' => $validated['section_name'],
                'position' => $course->courseSections()->max('position') + 1,
            ]);

            return response()->json([
                'message' => 'Section created successfully',
                'section' => [
                    'id' => $section->id,
                    'section_name' => $section->section_name,
                    'position' => $section->position,
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Something went wrong'], 500);
        }
    }
}
