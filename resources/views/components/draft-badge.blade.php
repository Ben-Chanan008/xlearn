@props(['course'])

{{-- Shown to the instructor on an unpublished course, with how many sections it still needs --}}
@if($course->isDraft())
    @php($sections = $course->course_sections_count ?? $course->courseSections()->count())
    <span data-draft-badge {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 rounded-full bg-orange-100 px-3 py-1 text-xs font-bold text-orange-700']) }} title="Students can't see this course until it has {{ \App\Models\Course::SECTIONS_TO_PUBLISH }} sections">
        <i class="fas fa-pen-ruler"></i>
        Draft · {{ $sections }}/{{ \App\Models\Course::SECTIONS_TO_PUBLISH }} sections
    </span>
@endif
