@php
    $profile = $user->instructorProfile;
    $input = 'w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-primary/20';
    $card = 'rounded-3xl bg-white p-6 shadow-sm sm:p-8';
    $label = 'text-xs font-extrabold uppercase tracking-[0.2em] text-golden';
    $placeholder = 'mt-4 w-full rounded-2xl border-2 border-dashed border-gray-200 px-5 py-6 text-left text-sm font-semibold text-gray-400 transition hover:border-golden hover:text-golden hover:cursor-pointer';
    $host = fn (?string $url) => $url ? preg_replace('/^www\./', '', parse_url($url, PHP_URL_HOST) ?? $url) : null;
@endphp

@push('styles')
    <style>[x-cloak] { display: none !important; }</style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            // Alpine wraps its data in a Proxy, which structuredClone can't copy; the profile is plain JSON, so this can
            const copy = (value) => JSON.parse(JSON.stringify(value));

            Alpine.data('profilePage', (config) => ({
                canEdit: config.canEdit,
                // What's on the page now, and the copy being edited; Cancel copies saved back over form
                saved: config.profile,
                form: copy(config.profile),
                avatarUrl: config.avatarUrl,
                completion: config.completion,
                nextReviews: config.nextReviews,

                editing: false,
                saving: false,
                confirmingDelete: false,
                deleteConfirmation: '',
                deleting: false,
                loadingReviews: false,
                errors: {},
                newSkill: '',
                photoFile: null,
                photoPreview: null,

                startEditing(field = null) {
                    this.editing = true;
                    if (field) this.$nextTick(() => this.$refs[field]?.focus());
                },

                cancel() {
                    this.form = copy(this.saved);
                    this.photoFile = null;
                    this.photoPreview = null;
                    this.errors = {};
                    this.newSkill = '';
                    this.editing = false;
                },

                pickPhoto(event) {
                    const file = event.target.files[0];
                    if (!file) return;

                    // Shown straight away; it's only uploaded when the instructor saves
                    this.photoFile = file;
                    this.photoPreview = URL.createObjectURL(file);
                },

                addSkill() {
                    const skill = this.newSkill.trim();
                    if (skill && this.form.expertise.length < 8 && !this.form.expertise.includes(skill))
                        this.form.expertise.push(skill);

                    this.newSkill = '';
                },

                // The first message for a field, including per-item ones like expertise.2
                error(field) {
                    const key = Object.keys(this.errors).find(key => key === field || key.startsWith(field + '.'));
                    return key ? this.errors[key][0] : null;
                },

                host(url) {
                    try { return new URL(url).hostname.replace(/^www\./, ''); } catch { return url; }
                },

                async save() {
                    const msg = new Msg('.msg-alert');
                    this.saving = true;
                    this.errors = {};

                    // POST + _method=PUT: PHP only reads uploaded files on a real POST
                    const data = new FormData();
                    data.append('_method', 'PUT');
                    ['headline', 'bio', 'years_experience', 'website', 'linkedin']
                        .forEach(field => data.append(field, this.form[field] ?? ''));
                    this.form.expertise.forEach(skill => data.append('expertise[]', skill));
                    if (this.photoFile) data.append('avatar', this.photoFile);

                    try {
                        const res = await fetch(config.updateUrl, {
                            method: 'POST',
                            body: data,
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json',
                            },
                        });
                        const response = await res.json();

                        if (res.status === 422) {
                            this.errors = response.errors;
                            return;
                        }

                        if (!res.ok) {
                            msg.popup({ type: 'error', msg: response.message || 'An error occurred', duration: 3000 });
                            return;
                        }

                        this.saved = { ...response.profile, expertise: response.profile.expertise ?? [] };
                        this.form = copy(this.saved);
                        this.avatarUrl = response.avatar_url;
                        this.completion = response.completion;
                        this.photoFile = null;
                        this.photoPreview = null;
                        this.editing = false;
                        msg.popup({ type: 'success', msg: response.message, duration: 3000 });
                    } catch {
                        msg.popup({ type: 'error', msg: 'Something went wrong. Please try again.', duration: 3000 });
                    } finally {
                        this.saving = false;
                    }
                },

                // Runs only from the confirmation dialog, after the instructor has typed DELETE
                async deleteProfile() {
                    const msg = new Msg('.msg-alert');
                    this.deleting = true;

                    try {
                        // config.deleteUrl comes from Blade's route(); route() itself only exists in PHP
                        const res = await fetch(config.deleteUrl, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json',
                            },
                        });
                        const response = await res.json();

                        if (!res.ok) {
                            msg.popup({ type: 'error', msg: response.message || 'An error occurred', duration: 3000 });
                            return;
                        }

                        msg.popup({ type: 'success', msg: response.message, duration: 2000 });
                        // A moment to read the message, then leave: the account is gone
                        setTimeout(() => window.location.href = response.redirect_url, 1500);
                    } catch {
                        msg.popup({ type: 'error', msg: 'Something went wrong. Please try again.', duration: 3000 });
                    } finally {
                        this.deleting = false;
                    }
                },

                // Fetches the next page of reviews and appends them, the same way live search swaps results
                async loadMoreReviews() {
                    this.loadingReviews = true;

                    try {
                        const html = await fetch(this.nextReviews).then(res => res.text());
                        const fresh = new DOMParser().parseFromString(html, 'text/html').querySelector('[data-review-list]');

                        document.querySelector('[data-review-list]').append(...fresh.children);
                        this.nextReviews = fresh.dataset.next || null;
                    } finally {
                        this.loadingReviews = false;
                    }
                },
            }));
        });
    </script>
@endpush

<x-layout :footer="true">
    <div class="msg-alert"></div>

    <div
        class="py-10"
        :class="editing && 'pb-32'"
        x-data="profilePage(@js([
            'canEdit' => $canEdit,
            'profile' => [
                'headline' => $profile?->headline,
                'bio' => $profile?->bio,
                'expertise' => $profile?->expertise ?? [],
                'years_experience' => $profile?->years_experience,
                'website' => $profile?->website,
                'linkedin' => $profile?->linkedin,
            ],
            'avatarUrl' => $user->avatarUrl(),
            'completion' => $canEdit ? $user->profileCompletion() : 100,
            'nextReviews' => $reviews->nextPageUrl(),
            'updateUrl' => route('instructors.update', $user),
            'deleteUrl' => route('instructors.delete', $user),
        ]))"
        @keydown.escape.window="confirmingDelete ? (confirmingDelete = false) : (editing && cancel())"
    >
        <x-back-link>Back</x-back-link>

        {{-- Banner --}}
        <div
            class="relative h-48 overflow-hidden rounded-[2.5rem] bg-primary sm:h-56"
            style="background-image: repeating-linear-gradient(135deg, rgba(153, 101, 21, 0.22) 0 2px, transparent 2px 24px), radial-gradient(circle at 85% 15%, rgba(208, 240, 192, 0.25), transparent 45%);"
        >
            <div class="absolute -bottom-24 -right-10 h-72 w-72 rounded-full bg-golden/30 blur-3xl"></div>
            <span class="absolute right-6 top-6 rounded-full bg-white/10 px-4 py-1.5 text-xs font-bold uppercase tracking-[0.2em] text-tea-green backdrop-blur">
                <i class="fas fa-chalkboard-teacher mr-1"></i> Instructor
            </span>
        </div>

        <div class="grid gap-8 lg:grid-cols-[340px_minmax(0,1fr)]">
            {{-- Identity --}}
            <aside class="relative -mt-20 self-start lg:sticky lg:top-6">
                <div class="relative rounded-3xl bg-white px-6 pb-8 pt-20 shadow-xl sm:px-8">
                    <div class="absolute -top-16 left-6 sm:left-8">
                        <div class="relative">
                            <img
                                src="{{ $user->avatarUrl() }}"
                                :src="photoPreview || avatarUrl"
                                x-show="photoPreview || avatarUrl"
                                @unless($user->avatarUrl()) style="display: none" @endunless
                                alt="{{ $user->fullName() }}"
                                class="h-32 w-32 rounded-full object-cover shadow-lg ring-8 ring-white"
                            >
                            <div
                                x-show="!(photoPreview || avatarUrl)"
                                @if($user->avatarUrl()) style="display: none" @endif
                                class="flex h-32 w-32 items-center justify-center rounded-full bg-primary text-4xl font-black text-white shadow-lg ring-8 ring-white"
                            >{{ $user->initials() }}</div>

                            @if($canEdit)
                                <label x-show="editing" x-cloak class="absolute bottom-1 right-1 flex h-10 w-10 items-center justify-center rounded-full bg-golden text-white shadow-lg ring-4 ring-white hover:cursor-pointer hover:opacity-90" title="Change photo">
                                    <i class="fas fa-camera"></i>
                                    <input type="file" accept="image/*" class="hidden" @change="pickPhoto">
                                </label>
                            @endif
                        </div>
                    </div>

                    @if($canEdit)
                        <p x-show="error('avatar')" x-text="error('avatar')" x-cloak class="mb-3 text-sm font-semibold text-red-600"></p>
                    @endif

                    <h1 class="text-2xl font-extrabold text-gray-900">{{ $user->fullName() }}</h1>

                    {{-- Headline --}}
                    <p x-show="!editing && saved.headline" x-text="saved.headline" @unless($profile?->headline) x-cloak @endunless class="mt-1 font-semibold text-golden">{{ $profile?->headline }}</p>
                    @if($canEdit)
                        <button type="button" x-show="!editing && !saved.headline" @if($profile?->headline) x-cloak @endif @click="startEditing('headline')" class="mt-1 text-sm font-semibold text-gray-400 hover:cursor-pointer hover:text-golden">
                            <i class="fas fa-plus mr-1"></i> Add a headline
                        </button>
                        <div x-show="editing" x-cloak class="mt-3">
                            <input x-ref="headline" x-model="form.headline" maxlength="120" placeholder="e.g. Full stack developer and patient teacher" class="{{ $input }}">
                            <p x-show="error('headline')" x-text="error('headline')" class="mt-1 text-sm font-semibold text-red-600"></p>
                        </div>
                    @endif

                    {{-- Rating and reply time --}}
                    <div class="mt-4 flex flex-wrap items-center gap-2 text-sm">
                        @if($user->rating !== null)
                            <x-star-rating :value="$user->rating" />
                            <span class="font-extrabold text-gray-900">{{ number_format($user->rating, 1) }}</span>
                            <span class="text-gray-500">· {{ $stats['reviews'] }} {{ Str::plural('review', $stats['reviews']) }}</span>
                        @else
                            <span class="rounded-full bg-tea-green px-3 py-1 text-xs font-bold text-primary">New instructor</span>
                        @endif
                    </div>
                    @if($user->replyTimeLabel())
                        <p class="mt-2 text-sm text-gray-500"><i class="fas fa-clock mr-1 text-golden"></i> {{ $user->replyTimeLabel() }}</p>
                    @endif

                    {{-- Stats --}}
                    <div class="mt-6 grid grid-cols-3 divide-x divide-gray-200 rounded-2xl bg-gray-50 py-4 text-center">
                        <div>
                            <p class="text-xl font-extrabold text-gray-900">{{ $stats['courses'] }}</p>
                            <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">{{ Str::plural('Course', $stats['courses']) }}</p>
                        </div>
                        <div>
                            <p class="text-xl font-extrabold text-gray-900">{{ number_format($stats['students']) }}</p>
                            <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">{{ Str::plural('Student', $stats['students']) }}</p>
                        </div>
                        <div class="px-2">
                            <p x-show="!editing" x-text="saved.years_experience ?? '—'" class="text-xl font-extrabold text-gray-900">{{ $profile?->years_experience ?? '—' }}</p>
                            @if($canEdit)
                                <input x-show="editing" x-cloak x-ref="years_experience" x-model="form.years_experience" type="number" min="0" max="60" class="w-full rounded-xl border border-gray-200 bg-white px-2 py-1 text-center text-lg font-extrabold text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary/20">
                            @endif
                            <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Yrs exp.</p>
                        </div>
                    </div>
                    @if($canEdit)
                        <p x-show="error('years_experience')" x-text="error('years_experience')" x-cloak class="mt-1 text-sm font-semibold text-red-600"></p>
                    @endif

                    {{-- Links and details --}}
                    <ul class="mt-6 space-y-3 text-sm text-gray-700">
                        <li x-show="!editing && saved.website" @unless($profile?->website) x-cloak @endunless>
                            <a href="{{ $profile?->website }}" :href="saved.website" target="_blank" rel="noopener" class="flex items-center gap-3 hover:text-primary">
                                <i class="fas fa-link w-4 text-golden"></i>
                                <span x-text="host(saved.website)" class="truncate">{{ $host($profile?->website) }}</span>
                            </a>
                        </li>
                        <li x-show="!editing && saved.linkedin" @unless($profile?->linkedin) x-cloak @endunless>
                            <a href="{{ $profile?->linkedin }}" :href="saved.linkedin" target="_blank" rel="noopener" class="flex items-center gap-3 hover:text-primary">
                                <i class="fab fa-linkedin w-4 text-golden"></i>
                                <span class="truncate">LinkedIn</span>
                            </a>
                        </li>
                        @if($canEdit)
                            <li x-show="editing" x-cloak class="space-y-2">
                                <input x-model="form.website" type="url" placeholder="https://your-website.com" class="{{ $input }}">
                                <p x-show="error('website')" x-text="error('website')" class="text-sm font-semibold text-red-600"></p>
                                <input x-model="form.linkedin" type="url" placeholder="https://www.linkedin.com/in/you" class="{{ $input }}">
                                <p x-show="error('linkedin')" x-text="error('linkedin')" class="text-sm font-semibold text-red-600"></p>
                            </li>
                        @endif
                        <li class="flex items-center gap-3"><i class="fas fa-location-dot w-4 text-golden"></i> {{ $user->province }}, Canada</li>
                        <li class="flex items-center gap-3"><i class="fas fa-calendar w-4 text-golden"></i> Member since {{ $user->created_at->format('M Y') }}</li>
                    </ul>

                    @if($canEdit)
                        <button type="button" x-show="!editing" @click="startEditing()" class="mt-8 w-full rounded-2xl border-2 border-primary py-3 font-bold text-primary transition hover:cursor-pointer hover:bg-primary hover:text-white">
                            <i class="fas fa-pen mr-2"></i> Edit profile
                        </button>

                        <div x-show="completion < 100" @if($user->profileCompletion() === 100) x-cloak @endif class="mt-6 rounded-2xl bg-tea-green/50 p-4">
                            <div class="flex items-center justify-between text-sm">
                                <span class="font-bold text-primary">Profile completion</span>
                                <span class="font-extrabold text-golden" x-text="completion + '%'">{{ $user->profileCompletion() }}%</span>
                            </div>
                            <div class="mt-2 h-2 overflow-hidden rounded-full bg-white">
                                <div class="h-full rounded-full bg-golden transition-all duration-500" :style="`width: ${completion}%`" style="width: {{ $user->profileCompletion() }}%"></div>
                            </div>
                            <p class="mt-2 text-xs text-gray-600">Students are more likely to enroll when they know who's teaching.</p>
                        </div>

                        {{-- Only in edit mode, at the very bottom, so it's never clicked by accident --}}
                        <div x-show="editing" x-cloak class="mt-8 border-t border-dashed border-red-200 pt-6">
                            <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-red-600">Danger zone</p>
                            <p class="mt-1 text-sm text-gray-500">Deletes your account and takes your profile off {{config('app.name')}}.</p>
                            <button type="button" @click="confirmingDelete = true; deleteConfirmation = ''; $nextTick(() => $refs.deleteConfirmation.focus())" class="mt-3 w-full rounded-2xl border-2 border-red-200 py-2.5 text-sm font-bold text-red-600 transition hover:cursor-pointer hover:border-red-600 hover:bg-red-600 hover:text-white">
                                <i class="fas fa-trash mr-2"></i> Delete account
                            </button>
                        </div>
                    @endif
                </div>
            </aside>

            {{-- Content --}}
            <div class="space-y-6 lg:pt-8">
                {{-- About --}}
                <section x-show="saved.bio || canEdit" @unless($profile?->bio || $canEdit) x-cloak @endunless class="{{ $card }}">
                    <h2 class="{{ $label }}">About {{ $user->first_name }}</h2>
                    <p x-show="!editing && saved.bio" x-text="saved.bio" @unless($profile?->bio) x-cloak @endunless class="mt-4 whitespace-pre-line leading-relaxed text-gray-700">{{ $profile?->bio }}</p>
                    @if($canEdit)
                        <button type="button" x-show="!editing && !saved.bio" @if($profile?->bio) x-cloak @endif @click="startEditing('bio')" class="{{ $placeholder }}">
                            <i class="fas fa-feather mr-2"></i> Add a bio: who you are, what you've built and how you teach
                        </button>
                        <div x-show="editing" x-cloak class="mt-4">
                            <textarea x-ref="bio" x-model="form.bio" rows="7" maxlength="2000" placeholder="Tell students who you are, what you've built and how you teach..." class="{{ $input }}"></textarea>
                            <div class="mt-1 flex justify-between gap-4 text-xs">
                                <p x-text="error('bio')" class="font-semibold text-red-600"></p>
                                <span class="shrink-0 text-gray-400" x-text="(form.bio?.length ?? 0) + ' / 2000'"></span>
                            </div>
                        </div>
                    @endif
                </section>

                {{-- Expertise --}}
                <section x-show="saved.expertise.length || canEdit" @unless($profile?->expertise || $canEdit) x-cloak @endunless class="{{ $card }}">
                    <h2 class="{{ $label }}">Expertise</h2>
                    <div x-show="!editing" class="mt-4 flex flex-wrap gap-2">
                        <template x-for="skill in saved.expertise" :key="skill">
                            <span x-text="skill" class="rounded-full bg-tea-green px-4 py-1.5 text-sm font-bold text-primary"></span>
                        </template>
                        @if($canEdit)
                            <button type="button" x-show="!saved.expertise.length" @if($profile?->expertise) x-cloak @endif @click="startEditing('expertise')" class="{{ $placeholder }} mt-0!">
                                <i class="fas fa-tags mr-2"></i> Add the topics you teach
                            </button>
                        @endif
                    </div>
                    @if($canEdit)
                        <div x-show="editing" x-cloak class="mt-4">
                            <div class="flex flex-wrap gap-2">
                                <template x-for="(skill, index) in form.expertise" :key="skill">
                                    <span class="inline-flex items-center gap-2 rounded-full bg-primary py-1.5 pl-4 pr-2 text-sm font-bold text-white">
                                        <span x-text="skill"></span>
                                        <button type="button" @click="form.expertise.splice(index, 1)" class="flex h-5 w-5 items-center justify-center rounded-full bg-white/20 text-[10px] hover:cursor-pointer hover:bg-white/40" :aria-label="'Remove ' + skill">
                                            <i class="fas fa-xmark"></i>
                                        </button>
                                    </span>
                                </template>
                            </div>
                            <div class="mt-3 flex gap-2">
                                <input x-ref="expertise" x-model="newSkill" @keydown.enter.prevent="addSkill()" :disabled="form.expertise.length >= 8" maxlength="30" placeholder="Add a topic and press Enter" class="{{ $input }} disabled:opacity-50">
                                <button type="button" @click="addSkill()" :disabled="form.expertise.length >= 8 || !newSkill.trim()" class="shrink-0 rounded-2xl bg-golden px-5 text-sm font-bold text-white hover:cursor-pointer hover:opacity-90 disabled:opacity-50">Add</button>
                            </div>
                            <div class="mt-1 flex justify-between gap-4 text-xs">
                                <p x-text="error('expertise')" class="font-semibold text-red-600"></p>
                                <span class="shrink-0 text-gray-400" x-text="form.expertise.length + ' / 8'"></span>
                            </div>
                        </div>
                    @endif
                </section>

                {{-- Courses --}}
                @if($courses->isNotEmpty() || $canEdit)
                    <section class="{{ $card }}">
                        <div class="flex items-baseline justify-between gap-4">
                            <h2 class="{{ $label }}">Courses</h2>
                            <div class="flex items-center gap-2">
                                <span class="text-sm text-gray-400">{{ $courses->count() }} published</span>
                                {{-- <a href="{{ route('dashboard') }}" class="mt-auto pt-4">
                                    <span class="flex items-center justify-center gap-2 rounded-xl bg-primary py-2.5 text-sm font-bold text-white transition group-hover:opacity-90">
                                        View courses <i class="fas fa-arrow-right text-xs transition-transform group-hover:translate-x-1"></i>
                                    </span>
                                </a> --}}
                            </div>
                        </div>

                        @if($courses->isNotEmpty())
                            <div class="mt-4 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                                @foreach($courses as $course)
                                    <a href="{{ route('courses.show', $course->slug) }}" class="group flex flex-col overflow-hidden rounded-2xl border border-gray-100 transition hover:shadow-md">
                                        <div class="aspect-video overflow-hidden bg-gray-50">
                                            <img
                                                src="{{ $course->thumbnail ? asset('storage/' . $course->thumbnail) : asset('images/logo.png') }}"
                                                alt="{{ $course->name }}"
                                                @class(['h-full w-full transition duration-500 group-hover:scale-105', 'object-cover' => $course->thumbnail, 'object-contain p-8' => ! $course->thumbnail])
                                            >
                                        </div>
                                        <div class="flex grow flex-col p-4">
                                            <h3 class="line-clamp-2 font-bold text-gray-900 group-hover:text-primary">{{ $course->name }}</h3>
                                            <div class="mt-2 flex items-center justify-between text-sm">
                                                <span class="text-gray-500"><i class="fas fa-users mr-1 text-gray-400"></i> {{ $course->students_count }}</span>
                                                <span class="font-extrabold text-primary">${{ number_format($course->price, 2) }}</span>
                                            </div>
                                            {{-- mt-auto keeps the button on the bottom edge when titles wrap to different lengths --}}
                                            
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <a href="{{ route('courses.create') }}" class="{{ $placeholder }} block">
                                <i class="fas fa-book mr-2"></i> Your published courses show up here. Courses publish once they have {{ \App\Models\Course::SECTIONS_TO_PUBLISH }} sections.
                            </a>
                        @endif
                    </section>
                @endif

                {{-- Reviews --}}
                <section class="{{ $card }}">
                    <h2 class="{{ $label }}">Reviews</h2>

                    @if($stats['reviews'])
                        <div class="mt-4 grid items-center gap-6 rounded-2xl bg-gray-50 p-6 md:grid-cols-[180px_minmax(0,1fr)]">
                            <div class="text-center">
                                <p class="text-5xl font-black text-gray-900">{{ number_format($stats['average_stars'], 1) }}</p>
                                <x-star-rating :value="$stats['average_stars']" class="mt-2" />
                                <p class="mt-1 text-sm text-gray-500">{{ $stats['reviews'] }} {{ Str::plural('review', $stats['reviews']) }}</p>
                            </div>
                            <x-rating-breakdown :counts="$starCounts" />
                        </div>

                        <div data-review-list data-next="{{ $reviews->nextPageUrl() }}" class="mt-2 divide-y divide-gray-100">
                            @foreach($reviews as $review)
                                <article class="flex items-start gap-4 py-5">
                                    <x-avatar :user="$review->student" size="sm" />
                                    <div class="min-w-0 grow">
                                        <div class="flex flex-wrap items-center justify-between gap-x-4 gap-y-1">
                                            <p class="font-bold text-gray-900">
                                                {{ $review->student->shortName() }}
                                                @if($review->course)
                                                    <span class="font-normal text-gray-500">on {{ $review->course->name }}</span>
                                                @endif
                                            </p>
                                            <time datetime="{{ $review->created_at->toIso8601String() }}" class="text-xs text-gray-400">{{ $review->created_at->diffForHumans() }}</time>
                                        </div>
                                        <x-star-rating :value="$review->stars" class="mt-1 text-xs" />
                                        @if($review->comment)
                                            <p class="mt-2 text-sm leading-relaxed text-gray-700">{{ $review->comment }}</p>
                                        @endif
                                    </div>
                                </article>
                            @endforeach
                        </div>

                        <button type="button" x-show="nextReviews" @unless($reviews->hasMorePages()) x-cloak @endunless @click="loadMoreReviews()" :disabled="loadingReviews" class="mt-2 w-full rounded-2xl bg-gray-50 py-3 text-sm font-bold text-primary transition hover:cursor-pointer hover:bg-gray-100 disabled:opacity-50">
                            <span x-text="loadingReviews ? 'Loading...' : 'Show more reviews'">Show more reviews</span>
                        </button>
                    @else
                        <div class="mt-4 rounded-2xl bg-gray-50 px-6 py-10 text-center">
                            <i class="far fa-star text-3xl text-golden/50"></i>
                            <p class="mt-3 font-bold text-gray-700">No reviews yet</p>
                            <p class="text-sm text-gray-500">Reviews from students across all of {{ $user->first_name }}'s courses show up here.</p>
                        </div>
                    @endif
                </section>
            </div>
        </div>

        {{-- Delete confirmation --}}
        @if($canEdit)
            <div x-show="confirmingDelete" x-cloak x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-primary/60 p-4 backdrop-blur-sm" @click.self="!deleting && (confirmingDelete = false)">
                <div role="dialog" aria-modal="true" aria-labelledby="delete-title" class="w-full max-w-md rounded-3xl bg-white p-8 shadow-2xl">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-red-50 text-red-600">
                        <i class="fas fa-triangle-exclamation text-2xl"></i>
                    </div>
                    <h2 id="delete-title" class="mt-5 text-xl font-extrabold text-gray-900">Delete your account?</h2>
                    <p class="mt-2 text-sm leading-relaxed text-gray-600">
                        Your profile will disappear and you'll be signed out. Students won't be able to find your page any more.
                    </p>

                    <label class="mt-6 block text-sm font-semibold text-gray-700">
                        Type <span class="font-mono font-bold text-red-600">DELETE</span> to confirm
                        <input x-ref="deleteConfirmation" x-model="deleteConfirmation" @keydown.enter="deleteConfirmation === 'DELETE' && !deleting && deleteProfile()" autocomplete="off" class="{{ $input }} mt-2 font-mono">
                    </label>

                    <div class="mt-6 flex gap-3">
                        <button type="button" @click="confirmingDelete = false" :disabled="deleting" class="grow rounded-2xl bg-gray-100 py-3 text-sm font-bold text-gray-700 hover:cursor-pointer hover:bg-gray-200">Keep my account</button>
                        <button type="button" @click="deleteProfile()" :disabled="deleteConfirmation !== 'DELETE' || deleting" class="grow rounded-2xl bg-red-600 py-3 text-sm font-bold text-white hover:cursor-pointer hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-40">
                            <span x-show="!deleting">Delete account</span>
                            <span x-show="deleting" x-cloak><i class="fas fa-spinner fa-spin mr-2"></i> Deleting...</span>
                        </button>
                    </div>
                </div>
            </div>
        @endif

        {{-- Save bar --}}
        @if($canEdit)
            <div
                x-show="editing"
                x-cloak
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="translate-y-full"
                x-transition:enter-end="translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="translate-y-0"
                x-transition:leave-end="translate-y-full"
                class="fixed inset-x-0 bottom-0 z-40 bg-primary px-4 py-4 shadow-2xl sm:px-16"
            >
                <div class="mx-auto flex max-w-7xl items-center justify-between gap-4">
                    <p class="hidden text-sm text-white/70 sm:block"><i class="fas fa-pen-to-square mr-2 text-golden"></i> Editing your profile. Press Esc to cancel.</p>
                    <div class="flex w-full gap-3 sm:w-auto">
                        <button type="button" @click="cancel()" :disabled="saving" class="grow rounded-2xl bg-white/10 px-6 py-3 text-sm font-bold text-white hover:cursor-pointer hover:bg-white/20 sm:grow-0">Cancel</button>
                        <button type="button" @click="save()" :disabled="saving" class="grow rounded-2xl bg-golden px-8 py-3 text-sm font-bold text-white hover:cursor-pointer hover:opacity-90 disabled:opacity-50 sm:grow-0">
                            <span x-show="!saving"><i class="fas fa-check mr-2"></i> Save profile</span>
                            <span x-show="saving" x-cloak><i class="fas fa-spinner fa-spin mr-2"></i> Saving...</span>
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-layout>
