@props(['course', 'section', 'items'])

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" />
    <style>
        .ql-toolbar.ql-snow { border-radius: 1rem 1rem 0 0; border-color: #e5e7eb; background: #f9fafb; }
        .ql-container.ql-snow { border-radius: 0 0 1rem 1rem; border-color: #e5e7eb; min-height: 14rem; font-size: 0.95rem; }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            const builder = document.querySelector('[data-section-builder]');
            const sectionInput = builder.querySelector('input[name="section_name"]');
            const contentList = builder.querySelector('[data-content-list]');
            const contentTemplate = document.querySelector('[data-content-template]');
            const contentsError = builder.querySelector('[data-error="contents"]');
            const progress = builder.querySelector('[data-upload-progress]');
            const progressBar = progress.querySelector('[data-progress-bar]');
            const submitButton = builder.querySelector('button[type="submit"]');
            const msg = new Msg('.msg-alert');
            // The section's saved items when editing, empty when creating
            const existingItems = {{ Js::from($items) }};

            // Per content item: its saved id (none for new items), Quill editor, files already uploaded and files picked so far
            const itemState = new Map();
            let itemCount = 0;

            const fileIcon = (type, name) => {
                if (type?.startsWith('video/')) return 'fa-file-video';
                if (type?.startsWith('audio/')) return 'fa-file-audio';
                if (type?.startsWith('image/')) return 'fa-file-image';
                if (type === 'application/pdf') return 'fa-file-pdf';
                if (/\.(ppt|pptx|odp|key)$/i.test(name)) return 'fa-file-powerpoint';
                return 'fa-file-lines';
            };

            const formatSize = (bytes) => bytes >= 1048576 ? `${(bytes / 1048576).toFixed(1)} MB` : `${Math.ceil(bytes / 1024)} KB`;

            // The date picker wants local "YYYY-MM-DDTHH:mm"; due dates are saved as UTC
            const toLocalInput = (iso) => {
                const date = new Date(iso);
                date.setMinutes(date.getMinutes() - date.getTimezoneOffset());
                return date.toISOString().slice(0, 16);
            };

            // textContent throughout so file names are never read as HTML
            const fileRow = ({ type, name, detail, onRemove }) => {
                const row = document.createElement('li');
                row.className = 'flex items-center gap-3 rounded-xl bg-gray-50 px-4 py-2 text-sm';

                const icon = document.createElement('i');
                icon.className = `fas ${fileIcon(type, name)} text-golden`;

                const label = document.createElement('span');
                label.className = 'grow truncate font-semibold text-gray-700';
                label.textContent = name;

                const info = document.createElement('span');
                info.className = 'text-xs text-gray-400';
                info.textContent = detail;

                const remove = document.createElement('button');
                remove.type = 'button';
                remove.title = 'Remove file';
                remove.className = 'text-gray-400 hover:text-red-600 hover:cursor-pointer';
                remove.innerHTML = '<i class="fas fa-xmark"></i>';
                remove.addEventListener('click', onRemove);

                row.append(icon, label, info, remove);
                return row;
            };

            const renderFiles = (item) => {
                const state = itemState.get(item);

                item.querySelector('[data-file-list]').replaceChildren(
                    ...state.keptFiles.map((file, index) => fileRow({
                        type: file.mime,
                        name: file.name,
                        detail: 'Uploaded',
                        onRemove: () => {
                            state.keptFiles.splice(index, 1);
                            renderFiles(item);
                        },
                    })),
                    ...state.files.map((file, index) => fileRow({
                        type: file.type,
                        name: file.name,
                        detail: formatSize(file.size),
                        onRemove: () => {
                            state.files.splice(index, 1);
                            renderFiles(item);
                        },
                    })),
                );
            };

            // Editor for written content, file picker for uploads, both for "both"
            const applyMode = (item) => {
                const mode = item.querySelector('[data-mode]:checked').value;
                item.querySelector('[data-editor-wrap]').classList.toggle('hidden', mode === 'files');
                item.querySelector('[data-files-wrap]').classList.toggle('hidden', mode === 'text');
            };

            const renumberItems = () => {
                const items = contentList.querySelectorAll('[data-content-item]');
                items.forEach((item, index) => {
                    item.querySelector('[data-item-number]').textContent = index + 1;
                    // A section needs at least one content item, so the last one can't be removed
                    item.querySelector('[data-remove-item]').classList.toggle('hidden', items.length === 1);
                });
            };

            // data is one of existingItems when editing, null for a new blank item
            const addContentItem = (data = null) => {
                const item = contentTemplate.content.firstElementChild.cloneNode(true);
                const group = ++itemCount;

                // Each item's radio group needs its own name, or picking one item's type unchecks the others
                item.querySelectorAll('[data-mode]').forEach(radio => radio.name = `content_mode_${group}`);

                // On the page before Quill starts, since the editor measures and selects inside the live document
                contentList.append(item);

                const quill = new Quill(item.querySelector('[data-editor]'), {
                    theme: 'snow',
                    placeholder: 'Write the lesson...',
                    modules: {
                        // Only what the server's sanitizer keeps; images and video go through file uploads
                        toolbar: [
                            [{ header: [2, 3, false] }],
                            ['bold', 'italic', 'underline', 'strike'],
                            [{ list: 'ordered' }, { list: 'bullet' }],
                            ['blockquote', 'code-block', 'link'],
                            ['clean'],
                        ],
                    },
                });
                itemState.set(item, { id: data?.id ?? null, quill, keptFiles: [...(data?.files ?? [])], files: [] });

                if (data) {
                    item.querySelector('[data-field="content_name"]').value = data.content_name;
                    item.querySelector(`[data-mode][value="${data.content_mode}"]`).checked = true;
                    // Sanitized when it was saved; convert() rather than dangerouslyPasteHTML(), which would focus each editor and scroll the page
                    if (data.content_information) quill.setContents(quill.clipboard.convert({ html: data.content_information }));
                    item.querySelector('[data-assignment]').checked = data.is_assignment;
                    item.querySelector('[data-due-wrap]').classList.toggle('hidden', !data.is_assignment);
                    if (data.due_at) item.querySelector('[data-due]').value = toLocalInput(data.due_at);
                    renderFiles(item);
                }

                item.querySelectorAll('[data-mode]').forEach(radio => radio.addEventListener('change', () => applyMode(item)));

                const fileInput = item.querySelector('[data-files]');
                fileInput.addEventListener('change', () => {
                    itemState.get(item).files.push(...fileInput.files);
                    // Cleared so picking the same file again still fires change
                    fileInput.value = '';
                    renderFiles(item);
                });

                const assignmentToggle = item.querySelector('[data-assignment]');
                assignmentToggle.addEventListener('change', () => {
                    item.querySelector('[data-due-wrap]').classList.toggle('hidden', !assignmentToggle.checked);
                });

                item.querySelector('[data-remove-item]').addEventListener('click', () => {
                    // A saved item disappears for students on save; a new one was never seen
                    if (itemState.get(item).id && !confirm('Students will no longer see this item. Remove it?')) return;

                    itemState.delete(item);
                    item.remove();
                    renumberItems();
                });

                applyMode(item);
                renumberItems();
            };

            const clearErrors = () => {
                builder.querySelectorAll('[data-error]').forEach(error => {
                    error.textContent = '';
                    error.classList.add('hidden');
                });
            };

            const showError = (container, field, text) => {
                const error = container.querySelector(`[data-error="${field}"]`);
                if (!error) return false;
                // A field can have more than one message, e.g. several bad files
                error.textContent = error.textContent ? `${error.textContent} ${text}` : text;
                error.classList.remove('hidden');
                return true;
            };

            // Laravel keys item errors as contents.{index}.{field}, and file errors as contents.{index}.files.{n}
            const showErrors = (errors) => {
                const items = [...contentList.querySelectorAll('[data-content-item]')];

                Object.entries(errors).forEach(([key, messages]) => {
                    const match = key.match(/^contents\.(\d+)\.(\w+)/);
                    const shown = match && items[match[1]]
                        ? showError(items[match[1]], match[2], messages[0])
                        : showError(builder, key, messages[0]);

                    if (!shown) showError(builder, 'contents', messages[0]);
                });

                builder.querySelector('[data-error]:not(.hidden)')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            };

            const buildFormData = () => {
                const data = new FormData();
                // PHP only parses uploads on POST, so the edit's PUT rides along as _method
                @if($section)
                    data.append('_method', 'PUT');
                @endif
                data.append('section_name', sectionInput.value);

                contentList.querySelectorAll('[data-content-item]').forEach((item, index) => {
                    const { id, quill, keptFiles, files } = itemState.get(item);
                    const mode = item.querySelector('[data-mode]:checked').value;
                    const isAssignment = item.querySelector('[data-assignment]').checked;
                    const dueAt = item.querySelector('[data-due]').value;
                    const prefix = `contents[${index}]`;

                    if (id) data.append(`${prefix}[id]`, id);
                    data.append(`${prefix}[content_name]`, item.querySelector('[data-field="content_name"]').value);
                    data.append(`${prefix}[content_mode]`, mode);

                    // getSemanticHTML gives plain <ul>/<ol>/<pre> instead of Quill's own markup, but turns every space into &nbsp;
                    if (mode !== 'files')
                        data.append(`${prefix}[content_information]`, quill.getText().trim() ? quill.getSemanticHTML().replaceAll('&nbsp;', ' ') : '');

                    if (mode !== 'text') {
                        keptFiles.forEach(file => data.append(`${prefix}[keep_files][]`, file.path));
                        files.forEach(file => data.append(`${prefix}[files][]`, file));
                    }

                    data.append(`${prefix}[is_assignment]`, isAssignment ? '1' : '0');
                    // The picker gives local time; the app stores UTC
                    if (isAssignment && dueAt)
                        data.append(`${prefix}[due_at]`, new Date(dueAt).toISOString());
                });

                return data;
            };

            builder.querySelector('[data-add-item]').addEventListener('click', addContentItem);

            builder.addEventListener('submit', (e) => {
                e.preventDefault();
                clearErrors();
                submitButton.disabled = true;
                progressBar.style.width = '0%';
                progress.classList.remove('hidden');

                // axios instead of fetch for the upload progress, which matters for lecture videos
                window.axios.post("{{ $section ? route('instructor.courses.sections.update', [$course, $section]) : route('instructor.courses.sections.store', $course) }}", buildFormData(), {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    onUploadProgress: (e) => {
                        if (e.total) progressBar.style.width = `${Math.round(e.loaded / e.total * 100)}%`;
                    },
                }).then(({ data }) => {
                    window.location = data.redirect;
                }).catch((error) => {
                    const { status, data } = error.response ?? {};
                    progress.classList.add('hidden');
                    submitButton.disabled = false;

                    // Validation errors go under their fields, everything else is a popup
                    if (status === 422) return showErrors(data.errors);

                    msg.popup({
                        type: 'error',
                        msg: status === 413 ? 'The files are larger than the server allows.' : (data?.message || 'Something went wrong. Please try again.'),
                        duration: 3000,
                    });
                });
            });

            (existingItems.length ? existingItems : [null]).forEach(data => addContentItem(data));
        });
    </script>
@endpush

<x-layout :footer="false">
    <div class="msg-alert"></div>
    <x-header><i class="fas mr-1 fa-layer-group"></i>{{ $section ? 'Edit Section' : 'New Section' }}</x-header>

    <a href="{{ route('instructor.courses.manage', $course) }}" class="mb-6 inline-flex items-center text-sm font-semibold text-primary hover:underline">
        <i class="fas fa-arrow-left mr-2"></i>Back to {{ $course->name }}
    </a>

    <form data-section-builder class="mb-8 space-y-6">
        <div class="rounded-3xl border border-gray-100 bg-white p-8 shadow-xl">
            <label for="section_name" class="mb-2 block text-sm font-bold uppercase tracking-wider text-gray-500">Section name</label>
            <input type="text" id="section_name" name="section_name" value="{{ $section?->section_name }}" maxlength="255" class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-3 py-3 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-primary/20" placeholder="e.g. Introduction to Semiconductors" />
            <p data-error="section_name" class="hidden mt-1 text-sm font-semibold text-red-600"></p>
        </div>

        <div data-content-list class="space-y-6"></div>

        <div>
            <button type="button" data-add-item class="w-full rounded-3xl border-2 border-dashed border-primary/30 bg-white/60 px-6 py-4 text-sm font-bold text-primary hover:bg-white hover:cursor-pointer">
                <i class="fas fa-plus mr-2"></i>Add another content item
            </button>
            <p data-error="contents" class="hidden mt-2 text-sm font-semibold text-red-600"></p>
        </div>

        <div class="flex flex-col items-stretch gap-4 rounded-3xl border border-gray-100 bg-white p-6 shadow-xl sm:flex-row sm:items-center sm:justify-end">
            <div data-upload-progress class="hidden grow">
                <p class="mb-1 text-xs font-bold uppercase tracking-wider text-gray-400">Uploading</p>
                <div class="h-2 overflow-hidden rounded-full bg-gray-100">
                    <div data-progress-bar class="h-full bg-primary transition-all duration-300" style="width: 0%"></div>
                </div>
            </div>
            <a href="{{ route('instructor.courses.manage', $course) }}" class="rounded-2xl bg-gray-100 px-6 py-3 text-center text-sm font-semibold text-gray-600 hover:bg-gray-200">Cancel</a>
            <button type="submit" class="rounded-2xl bg-primary px-6 py-3 text-sm font-semibold text-white hover:opacity-90 hover:cursor-pointer disabled:opacity-50">
                <i class="fas fa-check mr-2"></i>{{ $section ? 'Save changes' : 'Create section' }}
            </button>
        </div>
    </form>

    {{-- One content item; cloned and, when editing, filled by addContentItem() --}}
    <template data-content-template>
        <article data-content-item class="space-y-6 rounded-3xl border border-gray-100 bg-white p-8 shadow-xl">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-extrabold text-gray-800">
                    <span class="mr-2 inline-flex h-8 w-8 items-center justify-center rounded-xl bg-primary text-sm font-bold text-white" data-item-number>1</span>Content item
                </h3>
                <button type="button" data-remove-item class="text-sm font-semibold text-gray-400 hover:text-red-600 hover:cursor-pointer">
                    <i class="fas fa-trash mr-1"></i>Remove
                </button>
            </div>

            <div>
                <label class="mb-2 block text-sm font-bold uppercase tracking-wider text-gray-500">Title</label>
                <input type="text" data-field="content_name" maxlength="255" class="w-full rounded-2xl border border-gray-200 bg-gray-50 px-3 py-3 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-primary/20" placeholder="e.g. What is a semiconductor?" />
                <p data-error="content_name" class="hidden mt-1 text-sm font-semibold text-red-600"></p>
            </div>

            <div>
                <p class="mb-2 text-sm font-bold uppercase tracking-wider text-gray-500">Content type</p>
                <div class="grid gap-3 sm:grid-cols-3">
                    <label class="flex cursor-pointer items-start gap-3 rounded-2xl border-2 border-gray-100 p-4 has-checked:border-primary has-checked:bg-primary/5">
                        <input type="radio" data-mode value="text" class="mt-1 accent-primary" checked />
                        <span>
                            <span class="block font-bold text-gray-800"><i class="fas fa-pen-nib mr-1 text-primary"></i>Write it</span>
                            <span class="text-xs text-gray-500">Type the lesson with headings, lists, links and code.</span>
                        </span>
                    </label>
                    <label class="flex cursor-pointer items-start gap-3 rounded-2xl border-2 border-gray-100 p-4 has-checked:border-primary has-checked:bg-primary/5">
                        <input type="radio" data-mode value="files" class="mt-1 accent-primary" />
                        <span>
                            <span class="block font-bold text-gray-800"><i class="fas fa-cloud-arrow-up mr-1 text-primary"></i>Upload files</span>
                            <span class="text-xs text-gray-500">Lecture videos, audio, slides, PDFs or documents.</span>
                        </span>
                    </label>
                    <label class="flex cursor-pointer items-start gap-3 rounded-2xl border-2 border-gray-100 p-4 has-checked:border-primary has-checked:bg-primary/5">
                        <input type="radio" data-mode value="both" class="mt-1 accent-primary" />
                        <span>
                            <span class="block font-bold text-gray-800"><i class="fas fa-layer-group mr-1 text-primary"></i>Both</span>
                            <span class="text-xs text-gray-500">Written lesson with files attached underneath.</span>
                        </span>
                    </label>
                </div>
                <p data-error="content_mode" class="hidden mt-1 text-sm font-semibold text-red-600"></p>
            </div>

            <div data-editor-wrap>
                <div data-editor></div>
                <p data-error="content_information" class="hidden mt-1 text-sm font-semibold text-red-600"></p>
            </div>

            <div data-files-wrap class="hidden">
                <label class="block cursor-pointer rounded-2xl border border-dashed border-gray-300 p-8 text-center hover:bg-gray-50">
                    <i class="fas fa-2x fa-cloud-arrow-up text-golden"></i>
                    <span class="mt-2 block text-sm font-semibold text-gray-600">Choose files</span>
                    <span class="block text-xs text-gray-400">Video, audio, slides, PDF, Word, Excel, images or zip &middot; up to 512 MB each</span>
                    <input type="file" data-files multiple class="hidden" accept=".pdf,.doc,.docx,.ppt,.pptx,.odp,.key,.xls,.xlsx,.csv,.txt,.zip,image/*,audio/*,video/*" />
                </label>
                <ul data-file-list class="mt-3 space-y-2"></ul>
                <p data-error="files" class="hidden mt-1 text-sm font-semibold text-red-600"></p>
            </div>

            <div class="rounded-2xl bg-gray-50 p-4">
                <label class="flex cursor-pointer items-center gap-3">
                    <input type="checkbox" data-assignment class="h-4 w-4 accent-primary" />
                    <span class="font-semibold text-gray-800"><i class="fas fa-clipboard-check mr-1 text-primary"></i>This is an assignment</span>
                    <span class="text-xs text-gray-500">Students can upload a submission for it.</span>
                </label>
                <div data-due-wrap class="hidden mt-3">
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-gray-500">Due date (optional)</label>
                    <input type="datetime-local" data-due class="rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-primary/20" />
                    <p data-error="due_at" class="hidden mt-1 text-sm font-semibold text-red-600"></p>
                </div>
            </div>
        </article>
    </template>
</x-layout>
