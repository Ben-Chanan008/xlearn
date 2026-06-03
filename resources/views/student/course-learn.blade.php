@php
    $inActiveClass = 'border-transparent rounded-t-lg hover:text-gray-600 hover:border-gray-300 transition duration-200';
    $activeClass = 'border-primary rounded-t-lg active group text-primary border-primary';
@endphp

@push('scripts')
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            const assignmentForm = document.getElementById('assignment-submit');

            assignmentForm.addEventListener('change', (e) => {
                const fileInput = e.target.files;
                const preview = document.getElementById('preview');
                const reader = new FileReader();

                [...fileInput].forEach(file => {
                    const fileURL = URL.createObjectURL(file);
                    if(file.type.startsWith('image/')){
                        const img = document.createElement('img');
                        img.src = fileURL;
                        img.alt = 'Assignment Preview';
                        img.id = 'preview';
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
                     console.log(fileURL);

                });
                

            });


            const sectionButtons = document.querySelectorAll('.js-section-toggle');
            const sectionDetails = document.querySelectorAll('.js-section-detail');

            function activateSection(sectionId) {
                const sectionHeader = document.getElementById('section-header');
                sectionHeader.textContent = document.querySelector(`.js-section-toggle[data-section="${sectionId}"] p`).textContent;

                sectionButtons.forEach(button => {
                    const isActive = button.dataset.section === sectionId;
                    // button.classList.toggle('bg-primary/10', isActive);
                    // button.classList.toggle('border-primary/20', isActive);
                    // button.classList.toggle('border-transparent', !isActive);
                    // button.classList.toggle('text-primary', isActive);
                    // button.classList.toggle('text-gray-700', !isActive);
                    // button.querySelector('.chevron-icon')?.classList.toggle('rotate-180', isActive);
                });

                sectionDetails.forEach(detail => {
                    detail.classList.toggle('hidden', detail.dataset.section !== sectionId);
                });
            }

            if (sectionButtons.length > 0) {
                activateSection(sectionButtons[0].dataset.section);
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
    <div class="py-8 px-4 sm:px-6 lg:px-8">
        <!-- Back to Courses -->
        <a href="{{ route('my-courses') }}" class="inline-flex items-center text-sm font-bold text-primary mb-6 hover:-translate-x-1 transition-transform">
            <i class="fas fa-arrow-left mr-2"></i> Back to My Courses
        </a>

        <x-header>{{ $course->name }}</x-header>

        <div class="mb-8 border-b border-gray-200">
            <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" id="dashboard-tabs">
                <li class="mr-2">
                    <a href="{{ auth()->user()->isInstructor() ? route('instructor.dashboard') : route('dashboard') }}"
                       class="inline-block p-4 border-b-2 {{ (request()->routeIs('dashboard') || request()->routeIs('instructor.dashboard')) ? $activeClass : $inActiveClass }}"
                       aria-current="page">
                        <i class="fas fa-book-open mr-2"></i>Course
                    </a>
                </li>
                @if(auth()->user()->isStudent())
                    <li class="mr-2">
                        <a href="{{ route('my-courses') }}"
                           class="inline-block p-4 border-b-2 {{ request()->routeIs('my-courses') ? $activeClass : $inActiveClass }}">
                            <i class="fas fa-user-group mr-2"></i>Participants
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
                    <a href="#" class="inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:text-gray-600 hover:border-gray-300 transition duration-200">
                        <i class="fas fa-certificate mr-2"></i>Certificates
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <div class="grid grid-cols-[380px_1fr] gap-8 mb-8">
        <div class="p-8 bg-white rounded-3xl border border-gray-200 shadow-sm">
            <div class="flex items-center gap-x-4 mb-6">
                    <i class="fas fa-bars"></i>
                    <p class="text-lg font-bold">Course Sections</p>
            </div>

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
                                            @if($content->content_type === 'assignment')
                                            <p class="mt-1 bg-gray-50 rounded-2xl py-2 px-4 text-sm font-semibold text-gray-700"><i class="fas fa-check text-tea-green"></i> Has Assignment</p>
                                            @endif
                                        </div> 
                                    </div>

                                    <p class="mt-4 text-sm leading-7 text-gray-600">{{ $content->content_information }} Lorem ipsum dolor sit amet consectetur, adipisicing elit. Ipsam sequi laboriosam architecto molestiae aliquam ullam repellendus cumque, necessitatibus quis cum nulla dolorum nostrum dolor dicta laborum vel consequatur est modi beatae quos possimus esse suscipit a. Quasi, animi. Eos optio ullam dolor. Aut, accusamus itaque adipisci quia quibusdam natus odio perspiciatis dolorum aperiam at facere nisi, ipsum expedita velit autem, possimus vel. Mollitia, nobis iusto quis quae quam obcaecati minima quaerat beatae accusantium consequuntur enim. Reiciendis impedit dolores quae corporis animi commodi alias. Incidunt, eveniet? Voluptatibus, impedit eum iure eaque magni magnam, dolorem tempora quaerat quibusdam architecto recusandae. Veritatis quae cumque obcaecati, voluptas magni optio harum cum explicabo eos, vitae sed sequi, animi ipsum ab ut illum quis esse placeat repellendus? Nobis dignissimos, vel voluptates enim tempora quasi! Debitis quo exercitationem explicabo soluta rerum a minus. Est id veritatis quia sed exercitationem a necessitatibus eius obcaecati, pariatur aliquid officiis maxime qui dolorem harum nulla iure. Minus maiores at amet, odio doloribus eius rem quia expedita eaque asperiores laboriosam quae modi! Ratione blanditiis perspiciatis aliquid sapiente eaque dolor itaque. Fuga at quae eligendi perspiciatis beatae pariatur, perferendis modi consequuntur cumque asperiores neque ipsum possimus, porro blanditiis soluta magnam deleniti ea, veritatis maiores quo assumenda! Ducimus dicta ut mollitia quia hic blanditiis repellat quasi aut voluptatibus eum architecto sapiente quibusdam non voluptatum a eaque, dolor magnam sit quod voluptates perspiciatis ad, maiores est corporis? In magni accusamus exercitationem molestias sunt doloribus neque. Dolore alias ab natus rerum asperiores vel explicabo. Veniam perferendis a, ab ipsam dolor pariatur dolores quia possimus expedita temporibus dolorum saepe iure quibusdam quo fugiat ex. Neque esse in optio autem. Consectetur in laboriosam, nulla nam placeat quod mollitia obcaecati quaerat commodi perferendis enim culpa, eligendi incidunt autem distinctio id adipisci ex tempora ipsum sequi quidem animi. Eligendi eaque, aliquam ipsum inventore cumque praesentium nobis quae illum natus!</p>

                                    @if($content->files)
                                        <div class="mt-4 bg-gray-50 p-4 text-gray-700">
                                            <p class="font-bold uppercase my-3 text-sm">Attached Files</p>  
                                            <div class="bg-tea-green w-1/4 p-4 rounded-lg text-left flex gap-x-2 items-center">
                                                <i class="fas fa-2x fa-file-lines text-golden"></i>
                                                {{-- <p class="mt-2 break-all">{{ $content->files }}</p> --}}
                                                <a href="{{ $content->files }}" download="{{ $content->files }}" class="text-golden font-bold underline">Content 1</a>
                                            </div>
                                        </div>
                                    @endif

                                    @if($content->content_type === 'assignment')
                                        <div class="mt-4 gap-3">
                                            <p class="font-bold uppercase my-3 text-sm">Assignments</p>
                                            <form action="" id="assignment-submit" enctype="multipart/form-data">
                                                <label for="assignment" class="hover:cursor-pointer my-4">
                                                    <div class="p-8 border border-dashed rounded-xl text-center ">
                                                        <div id="preview"></div>
                                                        <div>
                                                            <i class="fas fa-2x fa-cloud-arrow-up text-golden"></i>
                                                            <span class="block mt-2 text-xs opacity-75">Upload assignment</span>
                                                        </div>
                                                    </div>
                                                </label>
                                                <input type="file" id="assignment" name="assignment" class="hidden" multiple>
                                                <button type="submit" class="mt-3 inline-flex items-center justify-center rounded-2xl bg-primary px-5 py-3 text-sm font-bold hover:cursor-pointer text-white transition hover:bg-primary/90">
                                                    Submit Assignment
                                                </button>
                                            </form>
                                            <span class="inline-block mt-3 text-xs font-semibold text-slate-600">
                                                Deadline: To be confirmed
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
