@push('scripts')
    <script>
        const url = new URL("{{ url()->full() }}");
        let activeStudentId = url.searchParams.get('student');
        // console.log(url.searchParams.get('student'));

        window.addEventListener('DOMContentLoaded', (e) => {
            const studentCards = [...document.querySelectorAll(`[data-student-card]`)];
            const formSelect = document.querySelector('#select-student select');
            const messageStudentInput = document.querySelector('#message-student');
            
            formSelect.addEventListener('change', (e) => {
                activeStudentId = e.target.value;
                toggleCardVisibility(activeStudentId);
                
                console.log(activeStudentId);   
            });

            const toggleCardVisibility = (activeStudentId) => {
                studentCards.forEach(card => {
                    const studentId = card.getAttribute('id').split('student-')[1];
                    if (studentId === activeStudentId)
                        card.classList.remove('hidden');
                    else
                        card.classList.add('hidden');
                });

                messageStudentInput.value = activeStudentId ? document.querySelector(`#student-${activeStudentId} h3`).textContent : '';
            }
           
            toggleCardVisibility(activeStudentId);

        });
    </script>
@endpush

<x-layout :footer="false">
    <div class="py-8 px-4 sm:px-6 lg:px-8">
        <div class="mb-8 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-12 h-12 rounded-2xl bg-primary/10 text-primary flex items-center justify-center text-xl">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div>
                        <h1 class="text-3xl font-extrabold text-gray-900">Student Management</h1>
                        <p class="text-gray-600">Track progress, communicate, and review student work!!</p>
                    </div>
                </div>
                <div class="flex font-bold gap-3">
                    {{-- <p class="bg-primary text-white py-1 rounded-2xl px-4">COURSE: <span class="text-golden">{{ $course->name }}</span></p> --}}
                    <p class="bg-primary text-white py-1 rounded-2xl px-4">CODE# <span class="text-golden">{{ $course->course_code }}</span></p>
                </div>
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('courses.show', $course->slug) }}" class="inline-flex items-center rounded-2xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to course
                </a>
                <button class="inline-flex items-center rounded-2xl bg-primary px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:opacity-90">
                    <i class="fas fa-paper-plane mr-2"></i>
                    Send update
                </button>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-[1.3fr_0.7fr]">
            <div class="space-y-6">
                <section class="flex gap-x-4 items-center">
                    <i class="fas fa-folder-open fa-2x text-xl text-primary"></i>
                    <form action="" id="select-student" class="w-full">
                        <p class="inline-block font-bold text-sm mx-2">Please select a student to view their details.</p>
                        <select class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-3 py-3 text-sm text-gray-700 focus:outline-none">
                            <option selected disabled class="focus:outline-none">Select Student</option>
                            @foreach($students as $student)
                                <option value="{{ $student->id }}">{{ $student->fullName() }}</option>
                            @endforeach
                        </select>
                    </form>
                </section>
                <div class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
                    <div class="mb-5 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <div>
                            <h2 class="text-xl font-extrabold text-gray-900">Learner overview</h2>
                            <p class="text-sm text-gray-500">Monitor each student's journey and keep communication close to the work.</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <button class="rounded-2xl border border-gray-200 px-3 py-2 text-sm font-semibold text-gray-700">All</button>
                            <button class="rounded-2xl border border-gray-200 px-3 py-2 text-sm font-semibold text-gray-700">Active</button>
                            <button class="rounded-2xl border border-gray-200 px-3 py-2 text-sm font-semibold text-gray-700">Completed</button>
                        </div>
                    </div>

                    <div class="space-y-4">
                        @forelse($students as $student)
                            @php
                                $activeCourses = $student->studentCourses()
                                    ->where('instructor_id', $course->instructor_id)
                                    ->wherePivotNull('completed_at')
                                    ->count();

                                $completedCourses = $student->studentCourses()
                                    ->where('instructor_id', $course->instructor_id)
                                    ->wherePivotNotNull('completed_at')
                                    ->count();
                            @endphp
                            {{-- Student Card --}}
                            <div 
                                class="rounded-2xl border border-gray-100 bg-gray-50/70 p-5 hidden"
                                id="student-{{ $student->id }}"
                                data-student-card
                            >
                                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                    <div class="flex items-center gap-4">
                                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-primary text-sm font-extrabold text-white">
                                            {{ $student->initials() }}
                                        </div>
                                        <div>   
                                            <h3 class="text-lg font-extrabold text-gray-900">{{ $student->fullName() }}</h3>
                                            <p class="text-sm text-gray-500">{{ $student->email }}</p>
                                            <div class="mt-2 flex flex-wrap gap-2">
                                                <span class="rounded-full bg-tea-green px-3 py-1 text-xs font-semibold text-primary">
                                                    {{ $activeCourses }} active course{{ $activeCourses == 1 ? '' : 's' }}
                                                </span>
                                                <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                                                    {{ $completedCourses }} completed course{{ $completedCourses == 1 ? '' : 's' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="text-left lg:text-right">
                                        <p class="text-sm font-semibold text-gray-500">Course progress</p>
                                        <p class="text-2xl font-extrabold text-gray-900">{{ $student->pivot->course_progress }}%</p>
                                    </div>
                                </div>

                                <div class="mt-4 h-2 overflow-hidden rounded-full bg-gray-200">
                                    <div class="h-full rounded-full bg-primary transition-all duration-500" style="width: {{ $student->pivot->course_progress }}%"></div>
                                </div>

                                <div class="mt-5 flex flex-wrap gap-3">
                                    <button class="inline-flex items-center rounded-2xl border border-gray-200 bg-white px-3.5 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                                        <i class="fas fa-comment-dots mr-2"></i>
                                        Message
                                    </button>
                                    <button class="inline-flex items-center rounded-2xl border border-gray-200 bg-white px-3.5 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                                        <i class="fas fa-file-alt mr-2"></i>
                                        View assignments
                                    </button>
                                    <span class="inline-flex items-center rounded-2xl bg-white px-3.5 py-2 text-sm font-semibold text-gray-600">
                                        <i class="fas fa-history mr-2"></i>
                                        Last access: {{ $student->pivot->accessed_at ? $student->pivot->accessed_at->diffForHumans() : 'No activity yet' }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-2xl border border-dashed border-gray-200 bg-gray-50 px-6 py-12 text-center">
                                <i class="fas fa-user-slash text-4xl text-gray-300"></i>
                                <h3 class="mt-4 text-lg font-semibold text-gray-700">No students yet</h3>
                                <p class="mt-1 text-sm text-gray-500">This course has not been enrolled by any students yet.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-primary/10 text-primary">
                            <i class="fas fa-paper-plane"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-extrabold text-gray-900">Quick message</h3>
                            <p class="text-sm text-gray-500">Send a note to a learner about progress or next steps.</p>
                        </div>
                    </div>

                    <form class="space-y-3">
                        <input readonly type="text" class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-3 py-3 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-primary/20" id="message-student"
                         />
                        <textarea rows="4" class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-3 py-3 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-primary/20" placeholder="Write a message about feedback, encouragement, or a reminder..."></textarea>
                        <button type="button" class="w-full rounded-2xl bg-primary px-4 py-3 text-sm font-semibold text-white hover:opacity-90">
                            Send message
                        </button>
                    </form>
                </div>

                <div class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-tea-green text-primary">
                            <i class="fas fa-file-check"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-extrabold text-gray-900">Assignments</h3>
                            <p class="text-sm text-gray-500">A quick overview of pending submissions and feedback.</p>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4">
                            <div class="flex items-center justify-between">
                                <p class="font-semibold text-gray-800">Module 2 reflection</p>
                                <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-700">Pending</span>
                            </div>
                            <p class="mt-2 text-sm text-gray-500">Submitted 2 hours ago by Amina Yusuf</p>
                        </div>

                        <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4">
                            <div class="flex items-center justify-between">
                                <p class="font-semibold text-gray-800">Capstone draft</p>
                                <span class="rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-700">Reviewed</span>
                            </div>
                            <p class="mt-2 text-sm text-gray-500">Feedback sent yesterday</p>
                        </div>

                        <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4">
                            <div class="flex items-center justify-between">
                                <p class="font-semibold text-gray-800">Quiz recap</p>
                                <span class="rounded-full bg-blue-100 px-2.5 py-1 text-xs font-semibold text-blue-700">In review</span>
                            </div>
                            <p class="mt-2 text-sm text-gray-500">Waiting for instructor feedback</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-extrabold text-gray-900">Enrollment snapshot</h3>
                    <div class="mt-4 space-y-3 text-sm text-gray-600">
                        <div class="flex items-center justify-between rounded-2xl bg-gray-50 px-3 py-3">
                            <span>Current enrollments</span>
                            <span class="font-semibold text-gray-900">{{ $students->sum(fn ($student) => $student->studentCourses()->where('instructor_id', $course->instructor_id)->wherePivotNull('completed_at')->count()) }}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-2xl bg-gray-50 px-3 py-3">
                            <span>Completed enrollments</span>
                            <span class="font-semibold text-gray-900">{{ $students->sum(fn ($student) => $student->studentCourses()->where('instructor_id', $course->instructor_id)->wherePivotNotNull('completed_at')->count()) }}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-2xl bg-gray-50 px-3 py-3">
                            <span>Courses taught</span>
                            <span class="font-semibold text-gray-900">{{ $course->owner->courses()->count() }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layout>
