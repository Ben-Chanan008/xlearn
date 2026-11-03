<?php

namespace App\Http\Controllers;

use App\Http\Requests\RenameCourseSectionRequest;
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
        return view('instructor.section-form', ['course' => $course, 'section' => null, 'items' => []]);
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
                    $files = $content['content_mode'] === 'text'
                        ? []
                        : $this->storeFiles($content['files'], $course, $section, $storedPaths);

                    $section->sectionContents()->create($this->contentAttributes($content, $files));
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

    public function edit(Course $course, CourseSection $courseSection)
    {
        // The shape the form's addContentItem() fills an item from
        $items = $courseSection->sectionContents->map(fn ($content) => [
            'id' => $content->id,
            'content_name' => $content->content_name,
            'content_mode' => $content->content_mode,
            'content_information' => $content->content_information,
            'files' => $content->files ?? [],
            'is_assignment' => $content->content_type === 'assignment',
            'due_at' => $content->due_at?->toIso8601String(),
        ]);

        return view('instructor.section-form', ['course' => $course, 'section' => $courseSection, 'items' => $items]);
    }

    public function update(UpdateCourseSectionRequest $request, Course $course, CourseSection $courseSection)
    {
        $validated = $request->validated();
        $storedPaths = [];
        // Files taken off items that are still there; deleted from disk once the save has gone through
        $removedPaths = [];

        try {
            DB::transaction(function () use ($validated, $course, $courseSection, &$storedPaths, &$removedPaths) {
                $courseSection->update(['section_name' => $validated['section_name']]);

                $existing = $courseSection->sectionContents()->get()->keyBy('id');
                $submittedIds = collect($validated['contents'])->pluck('id')->filter()->all();

                // Items the instructor removed are soft deleted, so their files and student submissions stay
                $existing->except($submittedIds)->each->delete();

                foreach ($validated['contents'] as $content) {
                    $current = isset($content['id']) ? $existing[$content['id']] : null;
                    $currentFiles = collect($current?->files ?? []);

                    $files = [];
                    if ($content['content_mode'] !== 'text') {
                        // Kept files come from what the item already has; a path from the request alone is never trusted
                        $keep = $content['keep_files'] ?? [];
                        $files = $currentFiles->filter(fn ($file) => in_array($file['path'], $keep, true))->values()->all();
                        $files = [...$files, ...$this->storeFiles($content['files'] ?? [], $course, $courseSection, $storedPaths)];
                    }

                    $removedPaths = [...$removedPaths, ...array_diff($currentFiles->pluck('path')->all(), array_column($files, 'path'))];

                    $current
                        ? $current->update($this->contentAttributes($content, $files))
                        : $courseSection->sectionContents()->create($this->contentAttributes($content, $files));
                }
            });

            Storage::disk('public')->delete($removedPaths);

            session()->flash('success', 'Section updated successfully');

            return response()->json([
                'message' => 'Section updated successfully',
                'redirect' => route('instructor.courses.manage', $course),
            ]);
        } catch (\Exception $e) {
            Storage::disk('public')->delete($storedPaths);

            return response()->json(['message' => 'Something went wrong'], 500);
        }
    }

    public function rename(RenameCourseSectionRequest $request, Course $course, CourseSection $courseSection)
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

    public function destroy(Course $course, CourseSection $courseSection)
    {
        try {
            DB::transaction(function () use ($course, $courseSection) {
                // Soft deleted: its content, files and student submissions stay, hidden with the section
                $courseSection->delete();

                // Close the gap so positions stay 1..n
                CourseSection::where('course_id', $course->id)
                    ->where('position', '>', $courseSection->position)
                    ->decrement('position');
            });

            return response()->json(['message' => 'Section deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Something went wrong'], 500);
        }
    }

    /**
     * Stores the uploads on the public disk and returns them in the shape the files column holds
     */
    private function storeFiles(array $uploads, Course $course, CourseSection $section, array &$storedPaths): array
    {
        return array_map(function ($file) use ($course, $section, &$storedPaths) {
            $path = $file->store("course-content/course-{$course->course_code}/section-{$section->id}", 'public');
            $storedPaths[] = $path;

            return ['path' => $path, 'name' => $file->getClientOriginalName(), 'mime' => $file->getMimeType()];
        }, $uploads);
    }

    private function contentAttributes(array $content, array $files): array
    {
        $mode = $content['content_mode'];
        $isAssignment = (bool) ($content['is_assignment'] ?? false);

        return [
            'content_name' => $content['content_name'],
            'content_mode' => $mode,
            'content_information' => $mode === 'files' ? null : $content['content_information'],
            'files' => $files ?: null,
            'content_type' => $isAssignment ? 'assignment' : 'information',
            'due_at' => $isAssignment ? ($content['due_at'] ?? null) : null,
        ];
    }
}
