<x-layout :footer="true">
    <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumbs -->
        <nav class="flex mb-8 text-sm font-medium text-gray-500" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2">
                <li><a href="{{ route('dashboard') }}" class="hover:text-primary transition-colors">Dashboard</a></li>
                <li class="flex items-center space-x-2">
                    <i class="fas fa-chevron-right text-[10px]"></i>
                    <span class="text-gray-900">Course Details</span>
                </li>
            </ol>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
            <!-- Left Column: Course Content -->
            <div class="lg:col-span-2 space-y-10">
                <!-- Course Header -->
                <div class="space-y-4">
                    <div class="flex flex-wrap gap-2">
                        @php
                            $tags = is_array($course->tags) ? $course->tags : explode(',', $course->tags ?? '');
                        @endphp
                        @foreach(array_filter($tags) as $tag)
                            <span class="px-3 py-1 bg-primary/10 text-primary text-xs font-bold rounded-full uppercase tracking-wider">
                                {{ trim($tag) }}
                            </span>
                        @endforeach
                    </div>
                    <h1 class="text-4xl md:text-5xl font-black text-gray-900 leading-tight">
                        {{ $course->name }}
                    </h1>
{{--                    @elsecan('manage', $course)--}}

                        <p class="text-xl text-gray-600 leading-relaxed">
                            {{ Str::limit($course->description, 150) }}
                        </p>

                        <!-- Instructor & Meta -->
                        <div class="flex flex-wrap items-center gap-6 pt-4 border-t border-gray-100">
                            <div class="flex items-center space-x-3">
                                <div class="w-12 h-12 rounded-full bg-primary/20 flex items-center justify-center text-primary">
                                    <i class="fas fa-chalkboard-teacher text-xl"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 uppercase font-bold tracking-tighter">Instructor</p>
                                    <p class="font-bold text-gray-900">{{ $course->owner->fullName() ?? 'Expert Instructor' }}</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-3">
                                <div class="w-12 h-12 rounded-full bg-tea-green flex items-center justify-center text-primary">
                                    <i class="fas fa-calendar-alt text-xl"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 uppercase font-bold tracking-tighter">Last Updated</p>
                                    <p class="font-bold text-gray-900">{{ $course->updated_at->format('M Y') }}</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-3">
                                <div class="w-12 h-12 rounded-full bg-yellow-100 flex items-center justify-center text-yellow-600">
                                    <i class="fas fa-star text-xl"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 uppercase font-bold tracking-tighter">Rating</p>
                                    <p class="font-bold text-gray-900">4.9 (2.4k reviews)</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Course Thumbnail -->
                    <div class="relative group rounded-3xl overflow-hidden shadow-2xl aspect-video bg-gray-100">
                        <img
                            class="w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-700"
                            src="{{ $course->thumbnail ? asset('storage/' . $course->thumbnail) : 'https://placeholder.co/800x400' }}"
                            alt="{{ $course->name }}"
                        >
                        <div class="absolute inset-0 bg-linear-to-t from-black/40 to-transparent"></div>
                        <button class="absolute inset-0 flex items-center justify-center group/play">
                            <div class="w-20 h-20 bg-white/90 rounded-full flex items-center justify-center shadow-xl group-hover/play:scale-110 group-hover/play:bg-primary group-hover/play:text-white transition-all duration-300">
                                <i class="fas fa-play text-2xl ml-1"></i>
                            </div>
                        </button>
                    </div>

                    <!-- Detailed Description -->
                    <div class="prose prose-lg max-w-none">
                        <h3 class="text-2xl font-bold text-gray-900 mb-6">Course Description</h3>
                        <div class="text-gray-600 leading-relaxed space-y-4">
                            {!! nl2br(e($course->description)) !!}
                        </div>
                    </div>

                    <!-- What you'll learn (Dynamic enhancement) -->
                    <div class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm space-y-6">
                        <h3 class="text-2xl font-bold text-gray-900">What you'll learn</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @php
                                $learnPoints = include base_path('course-learns.php');
                            @endphp
                            @foreach($learnPoints as $point)
                                <div class="flex items-start space-x-3">
                                    <div class="mt-1 shrink-0 w-5 h-5 bg-primary/10 text-primary rounded-full flex items-center justify-center">
                                        <i class="fas fa-check text-[10px]"></i>
                                    </div>
                                    <span class="text-gray-700 font-medium">{{ $point }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Right Column: Enrollment Card -->
                <div class="lg:col-span-1">
                    <div class="sticky top-8 space-y-6">
                        <div class="bg-white rounded-[2.5rem] shadow-xl border border-gray-100 overflow-hidden">
                            <div class="p-8 space-y-8">
                                <!-- Pricing -->
                                <div class="flex items-baseline space-x-3">
                                    <span class="text-5xl font-black text-gray-900">${{ number_format($course->price, 2) }}</span>
                                    @if($course->discount_code)
                                        <span class="text-xl text-gray-400 line-through">${{ number_format($course->price * 1.5, 2) }}</span>
                                    @endif
                                </div>

                                @if($course->discount_code)
                                    <div class="flex items-center justify-between p-4 bg-tea-green/30 border border-primary/20 rounded-2xl text-primary font-bold">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-tag"></i>
                                            <span>OFFER APPLIED</span>
                                        </div>
                                        <span class="bg-primary text-white px-3 py-1 rounded-lg text-xs">{{ $course->discount_code }}</span>
                                    </div>
                                @endif

                                <div class="space-y-4">
                                    @can('enroll', $course)
                                    <a href="{{ route('courses.enroll', $course) }}" class="w-full block text-center bg-primary hover:bg-primary/90 text-white font-black py-5 rounded-2xl shadow-lg shadow-primary/30 transition-all active:scale-95 text-lg">
                                        Enroll in Course
                                    </a>
                                    <button class="w-full bg-gray-50 hover:bg-gray-100 text-gray-900 font-bold py-5 rounded-2xl transition-all border border-gray-200">
                                        Try Free Preview
                                    </button>
                                    @else
                                        <a href="{{ route('courses.enroll', $course) }}" class="w-full block text-center bg-primary hover:bg-primary/90 text-white font-black py-5 rounded-2xl shadow-lg shadow-primary/30 transition-all active:scale-95 text-lg">
                                            View students
                                        </a>
                                    @endcan
                                </div>

                                <div class="space-y-4 pt-4">
                                    <h4 class="font-bold text-gray-900 uppercase tracking-widest text-xs">Course Features</h4>
                                    <ul class="space-y-4">
                                        <li class="flex justify-between items-center text-sm font-medium">
                                            <span class="text-gray-500"><i class="fas fa-id-badge w-6 text-primary"></i> Course ID</span>
                                            <span class="text-gray-900 font-bold">{{ $course->course_code }}</span>
                                        </li>
                                        <li class="flex justify-between items-center text-sm font-medium">
                                            <span class="text-gray-500"><i class="fas fa-users w-6 text-primary"></i> Capacity</span>
                                            <span class="text-gray-900 font-bold">{{ $course->max_students }} Students</span>
                                        </li>
                                        <li class="flex justify-between items-center text-sm font-medium">
                                            <span class="text-gray-500"><i class="fas fa-infinity w-6 text-primary"></i> Access</span>
                                            <span class="text-gray-900 font-bold">Full Lifetime</span>
                                        </li>
                                        <li class="flex justify-between items-center text-sm font-medium">
                                            <span class="text-gray-500"><i class="fas fa-certificate w-6 text-primary"></i> Certificate</span>
                                            <span class="text-gray-900 font-bold">Yes</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        @can('manage', $course)
                            <a href="{{ route('courses.edit', $course) }}" class="w-full block text-center bg-primary hover:bg-primary/90 text-white font-black py-5 rounded-2xl shadow-lg shadow-primary/30 transition-all active:scale-95 text-lg">
                                <i class="fas fa-pencil"></i>   Edit Course
                            </a>    
                        @endcan

                        <!-- Money Back Guarantee -->
                        <div class="p-6 bg-gray-50 rounded-3xl border border-gray-100 text-center">
                            <p class="text-sm text-gray-500 font-medium">
                                <i class="fas fa-shield-alt text-primary mr-2"></i> 30-Day Money-Back Guarantee
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
{{--        @endcan--}}

{{--    @can('manage', $course)--}}
{{--        <x-instructor.course-management :course="$course" />--}}
{{--    @endcan--}}
</x-layout>
