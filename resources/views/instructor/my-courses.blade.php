<x-layout :footer="true">
    <div class="py-12 px-4 sm:px-6 lg:px-16">
        <div class="mb-10">
            <x-header>My Managed Courses</x-header>
            <p class="text-gray-600">Overview of courses you are teaching and student progress.</p>
        </div>

        <x-dashboard-tab/>

        @if($courses->count() > 0)
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                {{-- Course Management List (Left 2 columns) --}}
                <div class="lg:col-span-2 space-y-12">
                       <p class="text-3xl">My Courses</p>
                    @foreach($courses as $course)
                        <div class="space-y-4">
                            {{-- Course ard --}}
                            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 flex flex-col md:flex-row items-center group hover:shadow-md transition duration-300">
                                <div class="flex grow text-center md:text-left items-center gap-8">
                                    <div class="h-full">
                                        <img src="{{ asset('storage/' . $course->thumbnail)  }}" alt="" class="rounded-2xl w-50 h-full" />
                                    </div>
                                    <div>
                                        <h3 class="text-xl font-bold text-gray-800 mb-1">{{ $course->name }}</h3>
                                        <p class="font-bold text-sm text-primary"><i class="fas fa-newspaper mr-2"></i>Course Code: {{ $course->course_code }}</p>
                                        <p class="text-sm text-gray-500 mb-3">
                                            <i class="fas fa-user-group"></i>
                                            Enrolled Students: <span class="font-semibold">{{ $course->students()->count() }}</span>
                                        </p>
                                        <p class="text-gray-700 text-sm">
                                            <i class="fas fa-calendar-altda"></i>
                                            Created on: {{ ($course->created_at->format('d M Y'))  }}
                                        </p>
                                    </div>
                                </div>

                                <div class="mt-6 md:mt-0 md:ml-6 space-x-3 shrink-0">
                                    <a href="{{ route('courses.edit', $course) }}" class="inline-flex items-center justify-center px-6 py-3 bg-primary text-white font-bold rounded-2xl hover:opacity-90 transition duration-300">
                                        <i class="fas mr-2 fa-pencil"></i> Edit
                                    </a>
                                    <form action="{{ route('courses.destroy', $course) }}" class="inline-block" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex hover:cursor-pointer items-center justify-center px-6 py-3 bg-gray-200 text-red-700 font-bold rounded-2xl hover:opacity-90 transition duration-300">
                                            <i class="fas mr-2 fa-times"></i> Close Course
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="p-8">
                {{ $courses->links()  }}
            </div>
        @else
            <div class="flex flex-col items-center justify-center py-20 rounded-3xl">
                <div class="w-24 h-24 bg-gray-50 rounded-full flex items-center justify-center mb-6 text-gray-300">
                    <i class="fas fa-chalkboard-teacher text-4xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-800 mb-2">No courses yet</h3>
                <p class="text-gray-500 mb-8 max-w-sm text-center">You haven't created any courses yet. Start sharing your knowledge today!</p>
                <a href="{{ route('courses.create') }}" class="px-8 py-4 bg-primary text-white font-bold rounded-2xl hover:shadow-lg transition duration-300">
                    Create your first course
                </a>
            </div>
        @endif
    </div>
</x-layout>
