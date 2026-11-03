<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCourseSectionRequest;
use App\Http\Requests\UpdateCourseSectionRequest;
use App\Models\Course;
use App\Models\CourseSection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CourseSectionController extends Controller
{
    public function create(Course $course)
    {
        return view('instructor.section-create', compact('course'));
    }

    public function store(StoreCourseSectionRequest $request, Course $course)
    {
        $validated = $request->validated();
        // Uploaded so far, so they can be removed if the save fails part way
        $storedPaths = [];

        try {
            DB::transaction(function () use ($validated, $course, &$storedPaths) {
                // New sections go at the end of the course
                $section = $course->courseSections()->create([
                    'section_name' => $validated['section_name'],
                    'position' => $course->courseSections()->max('position') + 1,
                ]);

                foreach ($validated['contents'] as $content) {
                    $mode = $content['content_mode'];
                    $isAssignment = (bool) ($content['is_assignment'] ?? false);

                    $files = [];
                    if ($mode !== 'text') {
                        foreach ($content['files'] as $file) {
                            $path = $file->store("course-content/course-{$course->course_code}/section-{$section->id}", 'public');
                            $storedPaths[] = $path;
                            $files[] = ['path' => $path, 'name' => $file->getClientOriginalName(), 'mime' => $file->getMimeType()];
                        }
                    }

                    $section->sectionContents()->create([
                        'content_name' => $content['content_name'],
                        'content_mode' => $mode,
                        'content_information' => $mode === 'files' ? null : $content['content_information'],
                        'files' => $files ?: null,
                        'content_type' => $isAssignment ? 'assignment' : 'information',
                        'due_at' => $isAssignment ? ($content['due_at'] ?? null) : null,
                    ]);
                }
            });

            // Shown by the success banner on the page the browser goes to next
            session()->flash('success', 'Section created successfully');

            return response()->json([
                'message' => 'Section created successfully',
                'redirect' => route('instructor.courses.manage', $course),
            ], 201);
        } catch (\Exception $e) {
            Storage::disk('public')->delete($storedPaths);

            return response()->json(['message' => 'Something went wrong'], 500);
        }
    }

    public function update(UpdateCourseSectionRequest $request, Course $course, CourseSection $courseSection)
    {
        try {
            $courseSection->update(['section_name' => $request->validated()['section_name']]);

            return response()->json([
                'message' => 'Section renamed successfully',
                'section' => [
                    'id' => $courseSection->id,
                    'section_name' => $courseSection->section_name,
                    'position' => $courseSection->position,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Something went wrong'], 500);
        }
    }
}
