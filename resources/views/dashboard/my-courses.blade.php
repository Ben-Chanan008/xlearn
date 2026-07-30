<x-layout :footer="true">
    <div class="py-12 px-4 sm:px-6 lg:px-16">
        <div class="mb-10">
            <x-header>My Courses</x-header>
            <p class="text-gray-600">See your course status and complete your course. Get to the next level!</p>
        </div>

        <x-dashboard-tab/>

        @if($enrolledCourses->count() > 0)
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                {{-- Courses List (Left 2 columns) --}}
                <div class="lg:col-span-2 space-y-6">
                    @foreach($enrolledCourses as $course)
                        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 flex flex-col md:flex-row items-center group hover:shadow-md transition duration-300">
                            <div class="w-20 h-20 {{ $course['color'] }} rounded-2xl flex items-center justify-center text-3xl mb-4 md:mb-0 md:mr-6 shrink-0 group-hover:scale-105 transition duration-300">
                                <img class="rounded-2xl" src="{{ $course->thumbnail ? asset('storage/'. $course->thumbnail) : asset('images/logo.png') }}" />
                            </div>

                            <div class="grow text-center md:text-left">
                                <h3 class="text-xl font-bold text-gray-800 mb-1">{{ $course->name }}</h3>
                                <p class="text-sm text-gray-500 mb-3">By <span class="font-semibold">{{ $course->owner->fullName() }}</span> • Last accessed {{ $course->accessed_at }}</p>

                                <div class="flex items-center space-x-4">
                                    <div class="grow h-2 bg-gray-100 rounded-full overflow-hidden">
                                        <div class="h-full bg-primary transition-all duration-1000" style="width: {{ $course->pivot->course_progress }}%"></div>
                                    </div>
                                    <span class="text-sm font-bold text-gray-700">{{ $course->pivot->course_progress }} %</span>
                                </div>
                            </div>

                            <div class="mt-6 md:mt-0 md:ml-6 shrink-0">
                                <a href="{{ route('courses.learn', $course->slug) }}" class="inline-flex items-center justify-center px-6 py-3 bg-primary text-white font-bold rounded-2xl hover:opacity-90 transition duration-300">
                                    {{ $course->pivot->course_progress == 100 ? 'Review' : 'Continue' }}
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Statistics & Motivation (Right 1 column) --}}
                <div class="space-y-8">
                    {{-- Progress Stats --}}
                    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
                        <h3 class="text-lg font-bold text-gray-800 mb-6">Learning Statistics</h3>

                        <div class="flex justify-center mb-8 relative">
                            {{-- Simple SVG Pie/Donut Chart --}}
                            <svg class="w-40 h-40" viewBox="0 0 36 36">
                                <circle class="text-gray-100" stroke="currentColor" stroke-width="3" fill="transparent" r="16" cx="18" cy="18"/>
                                <circle class="text-primary" stroke="currentColor" stroke-width="3" stroke-dasharray="{{ $averageProgress }}, 100" stroke-linecap="round" fill="transparent" r="16" cx="18" cy="18" transform="rotate(-90 18 18)"/>
                            </svg>
                            <div class="absolute inset-0 flex flex-col items-center justify-center">
                                <span class="text-3xl font-extrabold text-gray-800">{{ round($averageProgress) }}%</span>
                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Overall</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-tea-green/30 p-4 rounded-2xl text-center">
                                <span class="block text-2xl font-bold text-primary">{{ $completedCount }}</span>
                                <span class="text-xs font-semibold text-gray-500 uppercase">Completed</span>
                            </div>
                            <div class="bg-primary/10 p-4 rounded-2xl text-center">
                                <span class="block text-2xl font-bold text-primary">{{ $inProgressCount }}</span>
                                <span class="text-xs font-semibold text-gray-500 uppercase">In Progress</span>
                            </div>
                        </div>
                    </div>

                    {{-- Motivation --}}
                    <div class="bg-primary rounded-3xl p-8 text-white relative overflow-hidden group">
                        <div class="absolute -right-6 -top-6 w-32 h-32 bg-white/10 rounded-full group-hover:scale-110 transition duration-500"></div>
                        <div class="relative">
                            <i class="fas fa-quote-left text-2xl opacity-50 mb-4"></i>
                            <p class="text-lg font-bold leading-relaxed mb-6">
                                "{{ $motivationalMessage }}"
                            </p>
                            <div class="flex items-center space-x-2">
                                <div class="w-8 h-1 bg-white/30 rounded-full"></div>
                                <span class="text-xs font-bold uppercase tracking-widest opacity-70">Daily Inspiration</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="flex flex-col items-center justify-center py-20 rounded-3xl">
                <div class="w-24 h-24 bg-gray-50 rounded-full flex items-center justify-center mb-6 text-gray-300">
                    <i class="fas fa-book-open text-4xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-800 mb-2">No enrolled courses</h3>
                <p class="text-gray-500 mb-8 max-w-sm text-center">You haven't started any learning journeys yet. Let's find something exciting for you!</p>
                <a href="{{ route('dashboard') }}" class="px-8 py-4 bg-primary text-white font-bold rounded-2xl hover:shadow-lg transition duration-300">
                    View courses
                </a>
            </div>
        @endif
    </div>
</x-layout>
