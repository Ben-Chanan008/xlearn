@php
$inActiveClass = 'border-transparent rounded-t-lg hover:text-gray-600 hover:border-gray-300 transition duration-200';
$activeClass = 'border-primary rounded-t-lg active group text-primary border-primary';
@endphp

<div class="mb-8 border-b border-gray-200">
    <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" id="dashboard-tabs">
        <li class="mr-2">
            <a href="{{ auth()->user()->isInstructor() ? route('instructor.dashboard') : route('dashboard') }}"
               class="inline-block p-4 border-b-2 {{ (request()->routeIs('dashboard') || request()->routeIs('instructor.dashboard')) ?  $activeClass : $inActiveClass }}" aria-current="page">
                <i class="fas fa-home mr-2"></i>Home
            </a>
        </li>
        @if(auth()->user()->isStudent())
            <li class="mr-2">
                <a href="{{ route('my-courses') }}" class="inline-block p-4 border-b-2 {{ request()->routeIs('my-courses') ?  $activeClass : $inActiveClass }}">
                    <i class="fas fa-book-open mr-2"></i>My Courses
                </a>
            </li>
        @endif
        @if(auth()->user()->isInstructor())
            <li class="mr-2">
                <a href="{{ route('instructor.courses') }}" class="inline-block p-4 border-b-2 {{ request()->routeIs('instructor.courses') ?  $activeClass : $inActiveClass }}">
                    <i class="fas fa-chalkboard-teacher mr-2"></i>Managed Courses
                </a>
            </li>
        @endif
        <li class="mr-2">
            <a href="#" class="inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:text-gray-600 hover:border-gray-300 transition duration-200">
                <i class="fas fa-certificate mr-2"></i>Certificates
            </a>
        </li>
        <li class="mr-2">
            <a href="#" class="inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:text-gray-600 hover:border-gray-300 transition duration-200">
                <i class="fas fa-cog mr-2"></i>Settings
            </a>
        </li>
    </ul>
</div>
