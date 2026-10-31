@props(['course'])

@push('scripts')
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            const sectionList = document.querySelector('[data-section-list]');
            const sectionForm = document.querySelector('[data-section-form]');
            const sectionInput = sectionForm.querySelector('input[name="section_name"]');
            const sectionError = sectionForm.querySelector('[data-section-error]');
            const addButton = sectionForm.querySelector('button[type="submit"]');
            const msg = new Msg('.msg-alert');

            const showSectionError = (text) => {
                sectionError.textContent = text;
                sectionError.classList.toggle('hidden', !text);
            };

            // Builds the same row as the Blade loop below; textContent so the name is never read as HTML
            const addSectionRow = (section) => {
                const row = document.createElement('li');
                row.className = 'flex items-center gap-4 rounded-2xl border border-gray-100 bg-gray-50 px-5 py-3';

                const number = document.createElement('span');
                number.className = 'flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-primary text-sm font-bold text-white';
                number.textContent = sectionList.children.length + 1;

                const name = document.createElement('span');
                name.className = 'font-semibold text-gray-800';
                name.textContent = section.section_name;

                row.append(number, name);
                sectionList.append(row);
                sectionList.classList.remove('hidden');
            };

            sectionForm.addEventListener('submit', (e) => {
                e.preventDefault();
                addButton.disabled = true;
                showSectionError('');

                fetch("{{ route('instructor.courses.sections.store', $course) }}", {
                    method: 'POST',
                    body: JSON.stringify({ section_name: sectionInput.value }),
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                        // Without this, Laravel answers validation errors with an HTML redirect instead of JSON
                        'Accept': 'application/json',
                    },
                }).then(async res => ({ ok: res.ok, status: res.status, data: await res.json() }))
                .then(({ ok, status, data }) => {
                    // Validation errors go under the input, everything else is a popup
                    if (status === 422)
                        return showSectionError(data.errors?.section_name?.[0] ?? data.message);

                    msg.popup({
                        type: ok ? 'success' : 'error',
                        msg: data.message || 'An error occurred',
                        duration: ok ? 4000 : 3000,
                    });

                    if (ok) {
                        addSectionRow(data.section);
                        sectionInput.value = '';
                    }
                }).catch(() => {
                    msg.popup({
                        type: 'error',
                        msg: 'Something went wrong. Please try again.',
                        duration: 3000,
                    });
                }).finally(() => addButton.disabled = false);
            });
        });
    </script>
@endpush

<x-layout :footer="false">
    <div class="msg-alert"></div>
    <x-header><i class="fas mr-1 fa-people-group"></i>My Students</x-header>

    <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden">
        {{-- Header / Summary Section --}}
        <div class="p-8 bg-gray-50/50">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                <div>
                    <h2 class="text-2xl font-extrabold text-gray-800 mb-1">{{ $course->name }}</h2>
                    <p class="text-gray-500">COURSE# <span class="text-golden px-1 font-bold">{{ $course->course_code }}</span></p>
                </div>

                <div class="flex flex-wrap gap-4">
                    {{-- Stats Cards --}}
                    <div class="bg-white px-6 py-3 rounded-2xl border border-gray-100 shadow-sm flex items-center space-x-3">
                        <div class="w-10 h-10 bg-tea-green rounded-xl flex items-center justify-center text-primary">
                            <i class="fas fa-users"></i>
                        </div>
                        <div>
                            <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider">Students</span>
                            <span class="text-lg font-bold text-gray-800">{{ $students->count() }}</span>
                        </div>
                    </div>

                    <div class="bg-white px-6 py-3 rounded-2xl border border-gray-100 shadow-sm flex items-center space-x-3">
                        <div class="w-10 h-10 bg-primary/10 rounded-xl flex items-center justify-center text-primary">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div>
                            <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider">Total Earnings</span>
                            <span class="text-lg font-bold text-primary">${{ number_format($course->students->count() * (float)$course->price, 2) }}</span>
                        </div>
                    </div>

                    {{-- Action: Close Course --}}
                    <form action="#" method="POST" onsubmit="return confirm('Are you sure you want to close this course listing? New students will not be able to enroll.')">
                        @csrf
                        <button type="submit" class="h-full px-6 py-3 bg-red-50 text-red-600 font-bold rounded-2xl hover:bg-red-100 transition duration-300 flex items-center space-x-2">
                            <i class="fas fa-times-circle"></i>
                            <span>Close Course</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="grid gap-4 p-4 md:grid-cols-3">
            <div class="rounded-3xl  border-t border-gray-50 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-gray-500">Active learners</p>
                        <p class="mt-2 text-2xl font-extrabold text-gray-900">{{ $students->count() }}</p>
                    </div>
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-tea-green text-primary">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>

            <div class="rounded-3xl bg-white border-t border-gray-50 p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-gray-500">Completed</p>
                        <p class="mt-2 text-2xl font-extrabold text-gray-900">{{ $students->filter(fn ($student) => $student->pivot->course_progress == 100)->count() }}</p>
                    </div>
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-green-100 text-green-700">
                        <i class="fas fa-award"></i>
                    </div>
                </div>
            </div>

            <div class="rounded-3xl border-t border-gray-50 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-gray-500">Average progress</p>
                        <p class="mt-2 text-2xl font-extrabold text-gray-900">
                            {{ $students->count() ? round($students->avg(fn ($student) => $student->pivot->course_progress), 0) : 0 }}%
                        </p>
                    </div>
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-primary/10 text-primary">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Course Sections --}}
        <div class="border-t border-gray-100 p-8">
            <h3 class="mb-4 text-lg font-extrabold text-gray-800"><i class="fas fa-layer-group mr-2 text-primary"></i>Course Sections</h3>

            <ol data-section-list class="mb-4 space-y-2 @if($course->courseSections->isEmpty()) hidden @endif">
                @foreach($course->courseSections as $section)
                    <li class="flex items-center gap-4 rounded-2xl border border-gray-100 bg-gray-50 px-5 py-3">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-primary text-sm font-bold text-white">{{ $loop->iteration }}</span>
                        <span class="font-semibold text-gray-800">{{ $section->section_name }}</span>
                    </li>
                @endforeach
            </ol>

            <form data-section-form class="flex flex-col gap-2 sm:flex-row sm:items-start">
                <div class="grow">
                    <input type="text" name="section_name" maxlength="255" class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-3 py-3 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-primary/20" placeholder="New section name, e.g. Introduction to Semiconductors" />
                    <p data-section-error class="hidden mt-1 text-sm font-semibold text-red-600"></p>
                </div>
                <button type="submit" class="rounded-2xl bg-primary px-6 py-3 text-sm font-semibold text-white hover:opacity-90 hover:cursor-pointer disabled:opacity-50">
                    <i class="fas fa-plus mr-2"></i>Add section
                </button>
            </form>
        </div>

        {{-- Students Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-gray-400 text-xs font-bold uppercase tracking-widest">
                        <th class="px-8 py-5 border-b border-gray-100">Student Name</th>
                        <th class="px-8 py-5 border-b border-gray-100">Progress</th>
                        <th class="px-8 py-5 border-b border-gray-100 text-center">Status</th>
                        <th class="px-8 py-5 border-b border-gray-100 text-right">Actions</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($course->students as $student)
                        <tr class="hover:bg-gray-50/50 transition duration-200">
                            {{-- Name & Email --}}
                            <td class="px-8 py-6">
                                <div class="flex items-center space-x-4">
                                    <div class="w-10 h-10 bg-primary rounded-full flex items-center justify-center text-white font-bold">
                                        {{ $student->initials() }}
                                    </div>
                                    <div>
                                        <span class="block font-bold text-gray-800">{{ $student->fullName() }}</span>
                                        <span class="text-sm text-gray-500">{{ $student->email }}</span>
                                    </div>
                                </div>
                            </td>

                            {{-- Progress Bar --}}
                            <td class="px-8 py-6 w-1/3">
                                <div class="flex items-center space-x-3">
                                    <div class="grow h-2 bg-gray-100 rounded-full overflow-hidden">
                                        <div class="h-full bg-primary transition-all duration-500" style="width: {{ $student->pivot->course_progress }}%"></div>
                                    </div>
                                    <span class="text-sm font-bold text-gray-700 min-w-12 text-right">
                                        {{ $student->pivot->course_progress }}%
                                    </span>
                                </div>
                            </td>

                            {{-- Status Badge --}}
                            <td class="px-8 py-6 text-center">
                                @if($student->pivot->course_progress == 100)
                                    <span class="px-3 py-1 bg-green-100 text-green-700 text-[10px] font-bold rounded-full uppercase tracking-wider">
                                        Completed
                                    </span>
                                @elseif($student->pivot->course_progress > 0)
                                    <span class="px-3 py-1 bg-blue-100 text-blue-700 text-[10px] font-bold rounded-full uppercase tracking-wider">
                                        In Progress
                                    </span>
                                @else
                                    <span class="px-3 py-1 bg-gray-100 text-gray-500 text-[10px] font-bold rounded-full uppercase tracking-wider">
                                        Not Started
                                    </span>
                                @endif
                            </td>

                            {{-- Actions: Certificate --}}
                            <td class="px-8 py-6 text-right">
                                @if($student->pivot->course_progress == 100)
                                    <button class="inline-flex items-center px-4 py-2 bg-tea-green text-primary font-bold text-sm rounded-xl hover:shadow-md transition duration-300">
                                        <i class="fas fa-award mr-2"></i>
                                        Issue Certificate
                                    </button>
                                @else
                                    <button disabled class="inline-flex items-center px-4 py-2 bg-gray-50 text-gray-300 font-bold text-sm rounded-xl cursor-not-allowed">
                                        <i class="fas fa-lock mr-2"></i>
                                        Issue Certificate
                                    </button>
                                @endif
                            </td>

                            <td>
                                <a href="{{ route('instructor.courses.students', ['course' => $course, 'student' => $student->id]) }}" class="inline-flex items-center cursor-pointer px-4 py-2 bg-tea-green text-primary font-bold text-sm rounded-xl hover:shadow-md transition duration-300">
                                    <i class="fas fa-user-circle mr-2"></i>
                                    View Student
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-8 py-20 text-center">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-user-slash text-4xl text-gray-200 mb-4"></i>
                                    <p class="text-gray-500 font-semibold">No students enrolled in this course yet.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layout>
