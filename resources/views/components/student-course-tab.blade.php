@php
    $inActiveClass = 'border-transparent rounded-t-lg hover:text-gray-600 hover:border-gray-300 transition duration-200';
    $activeClass = 'border-primary rounded-t-lg active group text-primary border-primary';
@endphp
@props(['course'])
<div class="py-8 px-4 sm:px-6 lg:px-8">
    <!-- Back to Courses -->
    <a href="{{ url()->previous() }}" class="inline-flex items-center text-sm font-bold text-primary mb-6 hover:-translate-x-1 transition-transform">
        <i class="fas fa-arrow-left mr-2"></i> Go Back
    </a>

    <x-header>{{ $course->name }}</x-header>

    <div class="mb-8 border-b border-gray-200">
        <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" id="dashboard-tabs">
            <li class="mr-2">
                <a href="{{ auth()->user()->isInstructor() ? route('instructor.dashboard') : route('dashboard') }}"
                    class="inline-block p-4 border-b-2 {{ (request()->routeIs('dashboard') || request()->routeIs('instructor.dashboard')) ? $activeClass : $inActiveClass }}"
                    aria-current="page">
                    <i class="fas fa-home mr-2"></i>Courses
                </a>
            </li>
            @if(auth()->user()->isStudent())
                <li class="mr-2">
                    <a href="{{ route('my-courses') }}"
                        class="inline-block p-4 border-b-2 {{ request()->routeIs('my-courses') ? $activeClass : $inActiveClass }}">
                        <i class="fas fa-book-open mr-2"></i>My Courses
                    </a>
                </li>
            @endif
            @if(auth()->user()->isInstructor())
                <li class="mr-2">
                    <a href="{{ route('instructor.courses') }}"
                        class="inline-block p-4 border-b-2 {{ request()->routeIs('instructor.courses') ? $activeClass : $inActiveClass }}">
                        <i class="fas fa-chalkboard-teacher mr-2"></i>Grades
                    </a>
                </li>
            @endif
            <li class="mr-2">
                <a href="#" class="inline-block p-4 border-b-2 {{ request()->routeIs('#') ? $activeClass : $inActiveClass }}">
                    <i class="fas fa-certificate mr-2"></i>Certificates
                </a>
            </li>
            <li class="mr-2">
                <a href="{{ route('course.grades', $course) }}"
                    class="inline-block p-4 border-b-2 {{ request()->routeIs('course.grades') ? $activeClass : $inActiveClass }}">
                    <i class="fas fa-graduation-cap mr-2"></i>Grades
                </a>
            </li>
        </ul>
    </div>
</div>