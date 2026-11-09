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

        {{-- Rating and replies --}}
        @php($me = auth()->user())
        <div class="mb-10 grid gap-6 lg:grid-cols-[320px_minmax(0,1fr)]">
            <div class="rounded-3xl bg-primary p-6 text-white shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wider text-golden">Your rating</p>
                <p class="mt-2 text-4xl font-extrabold">
                    @if($me->rating !== null)
                        <i class="fas fa-star text-golden text-3xl"></i> {{ number_format($me->rating, 1) }}
                    @else
                        <span class="text-2xl">New instructor</span>
                    @endif
                </p>
                <dl class="mt-6 grid grid-cols-2 gap-4 border-t border-white/15 pt-5 text-sm">
                    <div>
                        <dt class="text-white/60">Response score</dt>
                        <dd class="mt-1 text-lg font-bold">{{ $me->response_score !== null ? number_format($me->response_score, 1) . ' / 5' : 'Not enough data' }}</dd>
                    </div>
                    <div>
                        <dt class="text-white/60">Replied within 24h</dt>
                        <dd class="mt-1 text-lg font-bold">{{ $me->on_time_rate !== null ? $me->on_time_rate . '%' : 'Not enough data' }}</dd>
                    </div>
                </dl>
            </div>

            <div class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-extrabold text-gray-900">Waiting over 24 hours</h2>
                <p class="mt-1 text-sm text-gray-500">These students are still waiting on a reply, and it's lowering your rating.</p>

                <ul class="mt-4 divide-y divide-gray-100" data-overdue-list>
                    @forelse($overdueMessages as $waiting)
                        <li class="flex items-center justify-between gap-4 py-3">
                            <div class="min-w-0">
                                <p class="font-bold text-gray-900">{{ $waiting->sender->fullName() }} <span class="font-normal text-gray-500">in {{ $waiting->course->name }}</span></p>
                                <p class="truncate text-sm text-gray-500">{{ $waiting->content }}</p>
                            </div>
                            <a href="{{ route('instructor.courses.students', $waiting->course) }}" class="shrink-0 text-sm font-bold text-primary hover:opacity-75">
                                Waiting {{ $waiting->created_at->diffForHumans(null, true) }} <i class="fas fa-arrow-right ml-1 text-xs"></i>
                            </a>
                        </li>
                    @empty
                        <li class="py-6 text-center text-sm text-gray-400">Nobody is waiting. Nice work.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        {{-- Content Area - Home Tab (Instructor's Courses) --}}
        <div id="home-content">
            @if($draftCount)
                <div data-draft-banner class="mb-8 flex flex-col gap-3 rounded-3xl border border-orange-200 bg-orange-50 p-5 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-pen-ruler mt-1 text-orange-600"></i>
                        <div>
                            <p class="font-bold text-gray-900">You have {{ $draftCount }} draft {{ Str::plural('course', $draftCount) }}</p>
                            <p class="text-sm text-gray-600">Students can't see a course until it has {{ \App\Models\Course::SECTIONS_TO_PUBLISH }} sections. It publishes automatically once it does.</p>
                        </div>
                    </div>
                    <a href="{{ route('instructor.courses') }}" class="shrink-0 text-sm font-bold text-orange-700 hover:opacity-75">
                        View my courses <i class="fas fa-arrow-right ml-1 text-xs"></i>
                    </a>
                </div>
            @endif

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

            <x-live-search placeholder="Search your courses..." />

            <div data-search-results>
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
                                <div class="flex flex-col items-end gap-2">
                                    @if($course->created_at->gt(now()->subDays(7)))
                                        <span class="bg-tea-green text-primary text-xs font-bold px-3 py-1 rounded-full">New</span>
                                    @endif
                                    <x-draft-badge :course="$course" />
                                </div>
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
                    @if(request('search'))
                    <div class="col-span-full py-12 text-center bg-white rounded-3xl border border-dashed border-gray-300">
                        <i class="fas fa-search text-4xl text-gray-300 mb-4"></i>
                        <h3 class="text-lg font-medium text-gray-900">No courses match "{{ request('search') }}"</h3>
                        <a href="{{ route('instructor.dashboard') }}" class="mt-4 inline-block text-sm font-bold text-primary hover:opacity-75">Clear search</a>
                    </div>
                    @else
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
                    @endif
                @endforelse
            </div>
            <div class="">
                {{ $courses->links()  }}
            </div>
            </div>
        </div>
    </div>
</x-layout>
