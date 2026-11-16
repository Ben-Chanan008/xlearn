@php
    $inActiveClass = 'border-transparent rounded-t-lg hover:text-gray-600 hover:border-gray-300 transition duration-200';
    $activeClass = 'border-primary rounded-t-lg active group text-primary border-primary';
@endphp
@props(['course'])
@php
    // Instructor messages this student hasn't read yet in this course
    $unreadMessages = \App\Models\Message::where('course_id', $course->id)
        ->where('sender_id', $course->instructor_id)
        ->where('receiver_id', auth()->id())
        ->where('is_read', false)
        ->count();
@endphp

{{-- The messages page shows new messages itself, so only the other tabs bump the dot --}}
{{-- @unless(request()->routeIs('course.messages'))
    @push('scripts')
        <script>
            // Echo loads as a module, so wait for it before subscribing
            window.addEventListener('DOMContentLoaded', () => {
                Echo.private('message.{{ $course->id }}')
                    .listen('MessageSent', ({ message }) => {
                        if (message.receiver_id != {{ auth()->id() }})
                            return;

                        const tab = document.querySelector('[data-messages-tab]');
                        let dot = tab.querySelector('[data-unread-dot]');
                        if (!dot) {
                            dot = document.createElement('span');
                            dot.dataset.unreadDot = '';
                            dot.className = 'absolute top-1 -right-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-500 px-1 text-xs font-bold text-white';
                            tab.append(dot);
                        }

                        const count = (parseInt(dot.textContent) || 0) + 1;
                        dot.textContent = count;
                        dot.title = `${count} unread ${count === 1 ? 'message' : 'messages'}`;
                    });
            });
        </script>
    @endpush
@endunless --}}
<div class="py-8 px-4 sm:px-6 lg:px-8">
    <x-back-link>Go Back</x-back-link>

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
                <a href="{{ route('course.messages', $course) }}" data-messages-tab class="relative inline-block p-4 border-b-2 {{ request()->routeIs('course.messages') ? $activeClass : $inActiveClass }}">
                    <i class="fas fa-comment mr-2"></i>Messages
                    @if($unreadMessages)
                        <span data-unread-dot title="{{ $unreadMessages }} unread {{ Str::plural('message', $unreadMessages) }}" class="absolute top-1 -right-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-500 px-1 text-xs font-bold text-white">{{ $unreadMessages }}</span>
                    @endif
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