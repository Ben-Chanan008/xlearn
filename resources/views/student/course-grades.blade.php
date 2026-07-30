<x-layout :footer="false">
    <x-student-course-tab :course="$course" />

    <div class="mx-8">

        <div class="bg-primary text-white my-8 p-3 px-8 rounded-lg shadow-md">
            <div class="flex justify-between items-center">
                <div>
                    <x-header class="text-lg">My Grades</x-header>
                    <p class="text-sm"><i class="fas fa-circle-info mr-2"></i>Grades will be available here once they are released by your instructor.</p>
                </div>
                <p class="text-sm font-semibold"><i class="fas fa-book"></i> COURSE / <span class="text-golden font-bold">{{ $course->course_code }}</span></p>
            </div>

            <div class="">
                @forelse ($course->courseSections as $idx => $section)
                    <section class="py-4 flex flex-col items-center">
                        <div class="flex w-[90%] flex-col items-center">
                            <p class="uppercase font-bold text-2xl self-start my-4">Section {{ $idx + 1 }}:  {{ $section->section_name }}</p>
                            <table class="w-full">
                                <thead class="mt-4 bg-black">
                                    <tr class="">
                                        <th class="text-left pl-4 py-8 rounded-tl-2xl" row-span="4">Assignment</th>
                                        <th class="text-center ml-4 py-8">Grade</th>
                                        <th class="text-center ml-4 py-8 rounded-tr-2xl">Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($section->sectionContents as $content)
                                        @if($content->content_type === 'assignment' /** change content type to hasAssignment**/)
                                            <tr class="border-b border-b-gray-400">
                                                <td class="p-4 text-xl">{{ $content->content_name }}</td>
                                                <td class="p-4 font-bold text-center">A+</td>
                                                <td class="p-4 font-bold text-center">100%</td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </section>
                @empty
                    <p>Instructor is still building the course.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-layout>