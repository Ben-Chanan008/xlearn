@push('styles')
    <style>
        /* Lesson HTML from the instructor's editor; only these tags survive the sanitizer */
        .lesson-content h2 { margin: 1.5rem 0 0.5rem; font-size: 1.25rem; font-weight: 700; color: #111827; }
        .lesson-content h3 { margin: 1.25rem 0 0.5rem; font-size: 1.125rem; font-weight: 700; color: #111827; }
        .lesson-content p { margin-bottom: 0.75rem; }
        .lesson-content ul, .lesson-content ol { margin-bottom: 0.75rem; padding-left: 1.5rem; }
        .lesson-content ul { list-style: disc; }
        .lesson-content ol { list-style: decimal; }
        .lesson-content a { color: var(--color-golden); font-weight: 600; text-decoration: underline; }
        .lesson-content blockquote { margin-bottom: 0.75rem; border-left: 4px solid var(--color-tea-green); padding-left: 1rem; font-style: italic; }
        .lesson-content pre { margin-bottom: 0.75rem; overflow-x: auto; border-radius: 0.75rem; background: #1f2937; padding: 1rem; color: #f3f4f6; font-family: ui-monospace, monospace; line-height: 1.5; }
        .lesson-content code { font-family: ui-monospace, monospace; }
    </style>
@endpush

@push('scripts')
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            const lastViewedKey = 'lastViewedSection:{{ $course->id }}';

            // Due dates are stored in UTC; show them in the student's own time zone
            document.querySelectorAll('[data-local-time]').forEach(time => {
                time.textContent = new Date(time.dateTime).toLocaleString(undefined, { dateStyle: 'medium', timeStyle: 'short' });
            });

            // One form per assignment, each previewing into its own box
            document.querySelectorAll('[data-assignment-form]').forEach(assignmentForm => assignmentForm.addEventListener('change', (e) => {
                const fileInput = e.target.files;
                const preview = assignmentForm.querySelector('[data-preview]');

                [...fileInput].forEach(file => {
                    const fileURL = URL.createObjectURL(file);
                    if(file.type.startsWith('image/')){
                        const img = document.createElement('img');
                        img.src = fileURL;
                        img.alt = 'Assignment Preview';
                        img.className = 'mx-auto max-h-48 object-contain';
                        // preview.outerHTML = `<img src="${event.target.result}" alt="Assignment Preview" id="preview" class="mx-auto max-h-48 object-contain">`;
                        preview.appendChild(img)
                    }
                    else{
                        const i = document.createElement('i');
                        i.className = 'fas fa-2x fa-file-lines text-golden';
                        preview.appendChild(i);
                    }

                    const fileName = document.createElement('span');
                    fileName.className = 'block mt-2 text-xs opacity-75';
                    fileName.textContent = file.name;
                    preview.appendChild(fileName);
                });
            }));


            const sectionButtons = document.querySelectorAll('.js-section-toggle');
            const sectionDetails = document.querySelectorAll('.js-section-detail');

            /** Activate the section from the sidebar on the student learn page **/
            function activateSection(sectionId) {
                const sectionHeader = document.getElementById('section-header');
                sectionHeader.textContent = document.querySelector(`.js-section-toggle[data-section="${sectionId}"] p`).textContent;


                sectionButtons.forEach(button => {
                    const isActive = button.dataset.section === sectionId;
                });
                    
                localStorage.setItem(lastViewedKey, sectionId);

                sectionDetails.forEach(detail => {
                    detail.classList.toggle('hidden', detail.dataset.section !== sectionId);
                });
            }

            if (sectionButtons.length > 0) {
                const lastViewedSection = localStorage.getItem(lastViewedKey);
                const stillThere = [...sectionButtons].some(button => button.dataset.section === lastViewedSection);

                activateSection(stillThere ? lastViewedSection : sectionButtons[0].dataset.section);
                sectionButtons.forEach(button => {
                    button.addEventListener('click', () => {
                        activateSection(button.dataset.section);
                    });
                });
            }
        });
    </script>
@endpush

<x-layout :footer="false">
    <x-student-course-tab :course="$course" />

    <div class="grid grid-cols-[380px_1fr] gap-8 mb-8">
        <div class="p-8 bg-white rounded-3xl border border-gray-200 shadow-sm">
            <div class="flex items-center gap-x-4 mb-6">
                <i class="fas fa-bars"></i>
                <p class="text-lg font-bold">Course Sections</p>
            </div>

            <div class="space-y-4">
                @forelse($course->courseSections as $section)
                    <button type="button"
                            data-section="{{ $section->id }}"
                            class="js-section-toggle w-full text-left rounded-xl p-3 px-4 bg-primary text-white hover:cursor-pointer hover:scale-[.985] hover:transition-transform">
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex gap-x-2 items-center">
                                <span class="block h-2 w-2 rounded-full bg-white"></span>
                                <p class="font-semibold">{{ $section->section_name }}</p>
                            </div>
                        </div>
                    </button>
                @empty
                    <div class="rounded-3xl border border-gray-300 bg-gray-50 p-6 text-center">
                        <p class="font-semibold text-gray-900">No sections yet</p>
                        <p class="text-sm text-gray-500">Your instructor has not added any course sections yet.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <div class="p-8 bg-white rounded-3xl border border-gray-200 shadow-sm">
            <div class="mb-8">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-xs uppercase tracking-[0.2em] text-primary font-bold">Learning Progress</p>
                        <h2 class="mt-3 text-3xl font-extrabold text-gray-900" id="section-header"></h2>
                    </div>
                    <div class="rounded-3xl bg-primary/10 px-4 py-3 text-sm font-semibold text-primary">
                        {{ $progress }}% completed
                    </div>
                </div>

                <div class="mt-6 rounded-3xl bg-gray-100 overflow-hidden h-4">
                    <div class="h-full bg-primary transition-all duration-500" style="width: {{ $progress }}%"></div>
                </div>

                <div class="mt-4 flex gap-4 text-sm text-gray-600">
                    <div class="p-4">
                        <p class="font-extrabold uppercase text-xs"><i class="fas fa-people-group mr-2"></i>Students enrolled</p>
                        <p class="text-golden font-semibold">{{ $studentCount }}</p>
                    </div>
                    <div class="p-4">
                        <p class="font-extrabold uppercase text-xs"><i class="fas fa-person-chalkboard mr-2"></i>Instructor</p>
                        <p class="text-golden font-semibold">{{ $course->owner->fullName() }}</p>
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                @foreach($course->courseSections as $section)
                    <div data-section="{{ $section->id }}" class="js-section-detail {{ $loop->first ? '' : 'hidden' }} space-y-6">

                        @if($section->sectionContents->isEmpty())
                            <div class="rounded-3xl border border-dashed border-gray-300 bg-gray-50 p-6 text-center text-gray-500">
                                No content blocks have been added to this section yet.
                            </div>
                        @endif

                        <div class="space-y-3">
                            @foreach($section->sectionContents as $content)
                                <article class="p-6">
                                    <div class="">
                                        <div class="flex justify-between items-center">
                                            <h4 class="text-lg font-bold text-gray-900">{{ $content->content_name }}</h4>
                                            <div class="inline-flex gap-3">
                                                @if($content->content_type === 'assignment')
                                                <p class="mt-1 bg-gray-50 rounded-2xl py-2 px-4 text-sm font-semibold text-gray-700"><i class="fas fa-check text-tea-green"></i> Has Assignment</p>
                                                @endif
                                                <button class="mt-1 mark-as-done bg-gray-500 rounded-2xl py-2 px-4 text-sm font-semibold hover:bg-primary hover:cursor-pointer hover:text-white shadow-lg
                                                text-gray-300"><i class="fas fa-check text-tea-green"></i> Mark As Done</button>
                                            </div>
                                        </div>
                                    </div>

                                    @if($content->content_information)
                                        {{-- Unescaped on purpose: StoreCourseSectionRequest sanitizes it down to the editor's tags before it's saved --}}
                                        <div class="lesson-content mt-4 text-sm leading-7 text-gray-600">{!! $content->content_information !!}</div>
                                    @endif

                                    @if($content->files)
                                        @php
                                            // Lectures and images play inline; everything else is a download
                                            [$media, $downloads] = collect($content->files)->partition(fn ($file) => preg_match('#^(video|audio)/#', $file['mime'] ?? ''));
                                        @endphp

                                        @foreach($media as $file)
                                            <figure class="mt-4">
                                                @if(str_starts_with($file['mime'], 'video/'))
                                                    <video controls preload="metadata" class="max-h-[70vh] w-full rounded-2xl bg-black" src="{{ Storage::url($file['path']) }}"></video>
                                                @elseif(str_starts_with($file['mime'], 'audio/'))
                                                    <audio controls preload="metadata" class="w-full" src="{{ Storage::url($file['path']) }}"></audio>
                                                {{-- @else
                                                    <img src="{{ Storage::url($file['path']) }}" alt="{{ $file['name'] }}" class="max-h-[70vh] rounded-2xl" /> --}}
                                                @endif
                                                <figcaption class="mt-2 flex items-center gap-2 text-xs text-gray-500">
                                                    <span class="truncate">{{ $file['name'] }}</span>
                                                    <a href="{{ Storage::url($file['path']) }}" download="{{ $file['name'] }}" class="font-bold text-golden underline">Download</a>
                                                </figcaption>
                                            </figure>
                                        @endforeach

                                        @if($downloads->isNotEmpty())
                                            <div class="mt-4 bg-gray-50 p-4 text-gray-700">
                                                <p class="font-bold uppercase my-3 text-sm">Attached Files</p>
                                                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                                    @foreach($downloads as $file)
                                                        @php
                                                            $icon = match (strtolower(pathinfo($file['name'], PATHINFO_EXTENSION))) {
                                                                'pdf' => 'fa-file-pdf',
                                                                'ppt', 'pptx', 'odp', 'key' => 'fa-file-powerpoint',
                                                                'doc', 'docx' => 'fa-file-word',
                                                                'xls', 'xlsx', 'csv' => 'fa-file-excel',
                                                                'zip' => 'fa-file-zipper',
                                                                default => 'fa-file-lines',
                                                            };
                                                        @endphp
                                                        <div class="bg-tea-green p-4 rounded-lg text-left flex gap-x-2 items-center">
                                                            <i class="fas fa-2x {{ $icon }} text-golden"></i>
                                                            <a href="{{ Storage::url($file['path']) }}" download="{{ $file['name'] }}" class="truncate text-golden font-bold underline">{{ $file['name'] }}</a>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    @endif

                                    @if($content->content_type === 'assignment')
                                        <div class="mt-4 gap-3">
                                            <p class="font-bold uppercase my-3 text-sm">Assignments</p>
                                            <form action="{{ route('courses.submit-assignment', $content->id) }}" method="POST" data-assignment-form enctype="multipart/form-data">
                                                @csrf
                                                <label for="assignment-{{ $content->id }}" class="hover:cursor-pointer my-4">
                                                    <div class="p-8 border border-dashed rounded-xl text-center ">
                                                        <div data-preview></div>
                                                        <div>
                                                            <i class="fas fa-2x fa-cloud-arrow-up text-golden"></i>
                                                            <span class="block mt-2 text-xs opacity-75">Upload assignment</span>
                                                        </div>
                                                    </div>
                                                </label>
                                                {{-- fix up multiple file upload --}}
                                                <input type="file" id="assignment-{{ $content->id }}" name="assignment[]" class="hidden" multiple>
                                                <button type="submit" class="mt-3 inline-flex items-center justify-center rounded-2xl bg-primary px-5 py-3 text-sm font-bold hover:cursor-pointer text-white transition hover:bg-primary/90">
                                                    Submit Assignment
                                                </button>
                                                @error('assignment')
                                                    <p class="text-red-500 my-2 text-sm">{{ $message }}</p>
                                                @enderror
                                            </form>
                                            <span class="inline-block mt-3 text-xs font-semibold text-slate-600">
                                                @if($content->due_at)
                                                    {{-- UTC fallback until the script shows it in the student's time zone --}}
                                                    Due: <time datetime="{{ $content->due_at->toIso8601String() }}" data-local-time>{{ $content->due_at->format('M j, Y g:i A') }} UTC</time>
                                                    @if($content->due_at->isPast())
                                                        <span class="ml-2 rounded-full bg-red-100 px-2 py-0.5 text-red-700">Past due</span>
                                                    @endif
                                                @else
                                                    <span class="ml-2 px-2 py-0.5 text-red-700">No due date</span>
                                                @endif
                                            </span>
                                        </div>
                                    @endif
                                </article>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-layout>
