<x-layout :footer="true">
    <div class="py-12 px-4 sm:px-6 lg:px-16">
        <div class="mb-10">
            <h1 class="text-4xl font-extrabold text-gray-900 mb-2">
                Instructor Dashboard
            </h1>
            <p class="text-gray-600">Welcome back, {{ auth()->user()->first_name }}! Here's what's happening with your courses.</p>
        </div>

        {{-- Tabs Section --}}
        <x-dashboard-tab />

        {{-- Content Area - Home Tab (Instructor's Courses) --}}
        <div id="home-content">
            <div class="flex items-center justify-between mb-8">
                <x-header>My Courses</x-header>
                <div class="flex space-x-2">
                    @can('create', \App\Models\Course::class)
                        <a href="{{route('courses.create')}}" class="bg-primary text-white font-bold p-3 rounded-xl hover:cursor-pointer">
                            <i class="fas fa-book"></i> Create Course
                        </a>
                    @endcan
                    <button class="p-2 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition duration-200">
                        <i class="fas fa-th-large text-gray-500"></i>
                    </button>
                    <button class="p-2 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 transition duration-200">
                        <i class="fas fa-list text-gray-500"></i>
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-16">
                @forelse($courses as $course)
                    <a href="{{ route('courses.show', $course->slug) }}" class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition duration-300 flex flex-col group">
                        <div class="p-8 grow">
                            <div class="flex items-start justify-between mb-6">
                                <div class="w-20 flex items-center justify-center text-2xl group-hover:scale-110 transition duration-300">
                                    <img
                                        src="{{$course->thumbnail ? asset('storage/'. $course->thumbnail) : asset('images/logo.png')}}"
                                        alt="{{$course->slug}}"
                                        class="rounded-lg"
                                    >
                                </div>
                                @if($course->created_at->gt(now()->subDays(7)))
                                    <span class="bg-tea-green text-primary text-xs font-bold px-3 py-1 rounded-full">New</span>
                                @endif
                            </div>

                            <h3 class="text-xl font-bold text-gray-800 mb-2 leading-tight group-hover:text-primary transition duration-300">{{ $course->name }}</h3>

                            <div class="space-y-3 mb-6">
                                <div class="flex items-center text-sm text-gray-500">
                                    <i class="fas fa-users w-5 text-gray-400"></i>
                                    <span class="ml-2"><span class="font-semibold text-gray-700">{{ $course->students()->get()->count() }}</span> students enrolled</span>
                                </div>
                                <div class="flex items-center text-sm text-gray-500">
                                    <i class="fas fa-calendar-alt w-5 text-gray-400"></i>
                                    <span class="ml-2 italic text-gray-400">Created {{ $course->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="px-8 pb-8 pt-0 mt-auto">
                            <div class="w-full bg-primary text-white font-bold py-3.5 rounded-2xl hover:opacity-90 transition duration-300 shadow-lg flex items-center justify-center space-x-2">
                                <span>Manage Course</span>
                                <i class="fas fa-arrow-right text-xs"></i>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="col-span-full py-12 text-center bg-white rounded-3xl border border-dashed border-gray-300">
                        <i class="fas fa-book-open text-4xl text-gray-300 mb-4"></i>
                        <h3 class="text-lg font-medium text-gray-900">You haven't created any courses yet</h3>
                        <p class="text-gray-500">Start by creating your first course to share your knowledge.</p>
                        <div class="mt-6">
                            <a href="{{route('courses.create')}}" class="bg-primary text-white font-bold px-6 py-3 rounded-xl hover:cursor-pointer inline-block">
                                <i class="fas fa-plus mr-2"></i> Create Course
                            </a>
                        </div>
                    </div>
                @endforelse
            </div>
            <div class="">
                {{ $courses->links()  }}
            </div>
        </div>
    </div>
</x-layout>
