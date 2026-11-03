@props(['course'])

@push('scripts')
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            const sectionList = document.querySelector('[data-section-list]');
            const msg = new Msg('.msg-alert');

            const sectionUrl = (id) => "{{ route('instructor.courses.sections.update', [$course, '__section__']) }}".replace('__section__', id);

            // Swaps the row's name for an input; Enter saves, Esc or Cancel puts the name back
            const startRename = (row) => {
                if (row.querySelector('[data-rename-form]')) return;

                const name = row.querySelector('[data-section-name]');
                const editButton = row.querySelector('[data-edit-section]');

                const form = document.createElement('form');
                form.dataset.renameForm = '';
                form.className = 'flex grow flex-col gap-2 sm:flex-row sm:items-start';

                const field = document.createElement('div');
                field.className = 'grow';

                const input = document.createElement('input');
                input.type = 'text';
                input.name = 'section_name';
                input.maxLength = 255;
                input.value = name.textContent;
                input.className = 'w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-primary/20';

                const error = document.createElement('p');
                error.className = 'hidden mt-1 text-sm font-semibold text-red-600';
                field.append(input, error);

                const saveButton = document.createElement('button');
                saveButton.type = 'submit';
                saveButton.className = 'rounded-xl bg-primary px-4 py-2 text-sm font-semibold text-white hover:opacity-90 hover:cursor-pointer disabled:opacity-50';
                saveButton.textContent = 'Save';

                const cancelButton = document.createElement('button');
                cancelButton.type = 'button';
                cancelButton.className = 'rounded-xl bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-200 hover:cursor-pointer';
                cancelButton.textContent = 'Cancel';

                form.append(field, saveButton, cancelButton);

                const showError = (text) => {
                    error.textContent = text;
                    error.classList.toggle('hidden', !text);
                };

                const stopRename = () => {
                    form.remove();
                    name.classList.remove('hidden');
                    editButton.classList.remove('hidden');
                };

                cancelButton.addEventListener('click', stopRename);
                input.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape') stopRename();
                });

                form.addEventListener('submit', (e) => {
                    e.preventDefault();
                    saveButton.disabled = true;
                    showError('');

                    fetch(sectionUrl(row.dataset.sectionId), {
                        method: 'PATCH',
                        body: JSON.stringify({ section_name: input.value }),
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                        },
                    }).then(async res => ({ ok: res.ok, status: res.status, data: await res.json() }))
                    .then(({ ok, status, data }) => {
                        if (status === 422)
                            return showError(data.errors?.section_name?.[0] ?? data.message);

                        msg.popup({
                            type: ok ? 'success' : 'error',
                            msg: data.message || 'An error occurred',
                            duration: ok ? 4000 : 3000,
                        });

                        if (ok) {
                            name.textContent = data.section.section_name;
                            stopRename();
                        }
                    }).catch(() => {
                        msg.popup({
                            type: 'error',
                            msg: 'Something went wrong. Please try again.',
                            duration: 3000,
                        });
                    }).finally(() => saveButton.disabled = false);
                });

                name.classList.add('hidden');
                editButton.classList.add('hidden');
                name.after(form);
                input.focus();
                input.select();
            };

            sectionList.addEventListener('click', (e) => {
                const editButton = e.target.closest('[data-edit-section]');
                if (editButton) startRename(editButton.closest('[data-section-row]'));
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
                    <li data-section-row data-section-id="{{ $section->id }}" class="flex items-center gap-4 rounded-2xl border border-gray-100 bg-gray-50 px-5 py-3">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-primary text-sm font-bold text-white">{{ $loop->iteration }}</span>
                        <span data-section-name class="grow font-semibold text-gray-800">{{ $section->section_name }}</span>
                        <button type="button" data-edit-section title="Rename section" class="text-gray-400 hover:text-primary hover:cursor-pointer"><i class="fas fa-pen"></i></button>
                    </li>
                @endforeach
            </ol>

            {{-- A section is created together with its content, on its own page --}}
            <a href="{{ route('instructor.courses.sections.create', $course) }}" class="inline-flex items-center rounded-2xl bg-primary px-6 py-3 text-sm font-semibold text-white hover:opacity-90">
                <i class="fas fa-plus mr-2"></i>Add section
            </a>
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
