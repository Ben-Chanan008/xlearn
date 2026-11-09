@push('scripts')
    <script>
        const url = new URL("{{ url()->full() }}");
        let activeStudentId = url.searchParams.get('student');
        // console.log(url.searchParams.get('student'));

        window.addEventListener('DOMContentLoaded', (e) => {
            const studentCards = [...document.querySelectorAll(`[data-student-card]`)];
            const formSelect = document.querySelector('#select-student select');
            const messageStudentInput = document.querySelector('#message-student');
            const messageForm = messageStudentInput.closest('form');

            const courseIdInput = messageForm.querySelector('input[name="course_id"]');

            // Sends a message to a student in this course and resolves to { ok, message }.
            // Used by both the quick message form and the conversation dialog.
            const postMessage = (receiverId, content) => fetch("{{ route('send-message') }}", {
                method:'POST',
                body: JSON.stringify({
                    receiver_id: parseInt(receiverId),
                    course_id: parseInt(courseIdInput.value),
                    content: content,
                }),
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                    // Without this, Laravel answers validation errors with an HTML redirect instead of JSON
                    'Accept': 'application/json',
                },
            }).then(async res => ({ ok: res.ok, message: (await res.json()).message }));

            messageForm.addEventListener('submit', (e) => {
                e.preventDefault();
                const messageInput = messageForm.querySelector('textarea[name="message"]');
                const msg = new Msg('.msg-alert');

                postMessage(activeStudentId, messageInput.value).then(({ ok, message }) => {
                    msg.popup({
                        type: ok ? 'success' : 'error',
                        msg: message || 'An error occurred',
                        duration: ok ? 4000 : 3000,
                    });

                    // Only clear the message once the server has saved it
                    if (ok)
                        messageInput.value = '';
                }).catch(() => {
                    msg.popup({
                        type: 'error',
                        msg: 'Something went wrong. Please try again.',
                        duration: 3000,
                    });
                });
            })

            // Conversation dialog
            const dialog = document.querySelector('#conversation-dialog');
            const dialogMessages = dialog.querySelector('[data-dialog-messages]');
            const dialogForm = dialog.querySelector('[data-dialog-form]');
            const dialogInput = dialogForm.querySelector('textarea');
            const dialogError = dialogForm.querySelector('[data-dialog-error]');
            const conversationUrl = "{{ route('instructor.courses.conversation', [$course, '__student__']) }}";
            let dialogStudentId = null;

            const showDialogError = (text) => {
                dialogError.textContent = text;
                dialogError.classList.toggle('hidden', !text);
            };

            const showDialogNotice = (text) => {
                const notice = document.createElement('p');
                notice.dataset.dialogNotice = '';
                notice.className = 'py-10 text-center text-sm text-gray-400';
                notice.textContent = text;
                dialogMessages.replaceChildren(notice);
            };

            const addBubble = ({ content, from_instructor, sent_at }) => {
                dialogMessages.querySelector('[data-dialog-notice]')?.remove();

                const bubble = document.createElement('div');
                bubble.dataset.bubble = from_instructor ? 'instructor' : 'student';
                bubble.className = `w-fit max-w-[80%] rounded-2xl px-4 py-2 text-sm ${from_instructor ? 'ml-auto bg-primary text-white' : 'bg-white text-gray-700 ring-1 ring-gray-100'}`;

                // textContent, never innerHTML: the message is shown exactly as typed and can't inject HTML
                const text = document.createElement('p');
                text.className = 'whitespace-pre-line';
                text.textContent = content;

                const time = document.createElement('p');
                time.className = `mt-1 text-[11px] ${from_instructor ? 'text-white/60' : 'text-gray-400'}`;
                time.textContent = sent_at;

                bubble.append(text, time);
                dialogMessages.append(bubble);
                dialogMessages.scrollTop = dialogMessages.scrollHeight;
            };

            const openConversation = (button) => {
                dialogStudentId = button.dataset.messageStudent;
                dialog.querySelector('[data-dialog-name]').textContent = document.querySelector(`#student-${dialogStudentId} h3`).textContent;
                dialog.querySelector('[data-dialog-initials]').textContent = '';
                dialogInput.value = '';
                showDialogError('');
                showDialogNotice('Loading conversation...');
                dialog.showModal();

                fetch(conversationUrl.replace('__student__', dialogStudentId), { headers: { 'Accept': 'application/json' } })
                    .then(async res => {
                        if (!res.ok)
                            throw new Error(res.status);

                        const { student, messages } = await res.json();
                        dialog.querySelector('[data-dialog-name]').textContent = student.name;
                        dialog.querySelector('[data-dialog-initials]').textContent = student.initials;

                        if (!messages.length)
                            showDialogNotice('No messages yet. Start the conversation below.');
                        else {
                            dialogMessages.replaceChildren();
                            messages.forEach(addBubble);
                        }

                        // The server marked this student's replies as read, so the red dot can go
                        button.querySelector('[data-unread-dot]')?.remove();
                    })
                    .catch(() => showDialogNotice('Could not load the conversation. Please try again.'));
            };

            document.querySelectorAll('[data-message-student]').forEach(button =>
                button.addEventListener('click', () => openConversation(button)));

            dialog.querySelector('[data-dialog-close]').addEventListener('click', () => dialog.close());

            // Clicking the dark backdrop around the dialog closes it
            dialog.addEventListener('click', (e) => {
                if (e.target === dialog)
                    dialog.close();
            });

            dialogForm.addEventListener('submit', (e) => {
                e.preventDefault();
                const content = dialogInput.value;
                const sendButton = dialogForm.querySelector('button[type="submit"]');

                sendButton.disabled = true;
                showDialogError('');

                postMessage(dialogStudentId, content)
                    .then(({ ok, message }) => {
                        if (!ok)
                            return showDialogError(message || 'An error occurred');

                        addBubble({ content, from_instructor: true, sent_at: 'just now' });
                        dialogInput.value = '';
                    })
                    .catch(() => showDialogError('Something went wrong. Please try again.'))
                    .finally(() => sendButton.disabled = false);
            });

            // console.log(messageForm);
            
            formSelect.addEventListener('change', (e) => {
                activeStudentId = e.target.value;
                toggleCardVisibility(activeStudentId);
                
                // console.log(activeStudentId);   
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
    <div class="msg-alert"></div>
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
                                    @php($unread = $unreadCounts[$student->id] ?? 0)
                                    <button type="button" data-message-student="{{ $student->id }}" class="relative inline-flex items-center rounded-2xl border border-gray-200 bg-white px-3.5 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 hover:cursor-pointer">
                                        <i class="fas fa-comment-dots mr-2"></i>
                                        Message
                                        @if($unread)
                                            <span data-unread-dot title="{{ $unread }} unread {{ Str::plural('reply', $unread) }}" class="absolute -top-2 -right-2 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-500 px-1 text-xs font-bold text-white">{{ $unread }}</span>
                                        @endif
                                    </button>
                                    @if($overdueStudentIds->contains($student->id))
                                        <span data-overdue-badge class="inline-flex items-center rounded-2xl bg-orange-100 px-3.5 py-2 text-sm font-semibold text-orange-700">
                                            <i class="fas fa-hourglass-half mr-2"></i>
                                            Waiting 1d+
                                        </span>
                                    @endif
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

                    <form class="space-y-3" method="POST" action="#">
                        <input type="hidden" name="course_id" value="{{ $course->id }}" />
                        <input readonly type="text" name="receiver" class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-3 py-3 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-primary/20" id="message-student"
                         />
                        <textarea name="message" rows="4" class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-3 py-3 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-primary/20" placeholder="Write a message about feedback, encouragement, or a reminder..."></textarea>
                        <button type="submit" class="w-full hover:cursor-pointer rounded-2xl bg-primary px-4 py-3 text-sm font-semibold text-white hover:opacity-90">
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

    {{-- Conversation dialog, opened by a student card's Message button --}}
    <dialog id="conversation-dialog" class="m-auto w-full max-w-lg rounded-3xl p-0 shadow-xl backdrop:bg-black/40">
        <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
            <div class="flex items-center gap-3">
                <div data-dialog-initials class="flex h-10 w-10 items-center justify-center rounded-full bg-primary text-sm font-extrabold text-white"></div>
                <div>
                    <h3 data-dialog-name class="font-extrabold text-gray-900"></h3>
                    <p class="text-xs text-gray-500">{{ $course->name }}</p>
                </div>
            </div>
            <button type="button" data-dialog-close aria-label="Close conversation" class="flex h-9 w-9 items-center justify-center rounded-full text-gray-500 hover:bg-gray-100 hover:cursor-pointer">
                <i class="fas fa-xmark"></i>
            </button>
        </div>

        <div data-dialog-messages class="h-96 space-y-3 overflow-y-auto bg-gray-50 px-5 py-4"></div>

        <form data-dialog-form class="space-y-2 border-t border-gray-100 px-5 py-4">
            <textarea rows="2" class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-3 py-3 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-primary/20" placeholder="Write a reply..."></textarea>
            <p data-dialog-error class="hidden text-sm font-semibold text-red-600"></p>
            <button type="submit" class="w-full rounded-2xl bg-primary px-4 py-3 text-sm font-semibold text-white hover:opacity-90 hover:cursor-pointer disabled:opacity-50">
                <i class="fas fa-paper-plane mr-2"></i>Send
            </button>
        </form>
    </dialog>
</x-layout>
