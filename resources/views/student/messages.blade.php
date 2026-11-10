@push('scripts')
	<script>
		// Sends a message to the instructor, adds it to the thread, and resolves to true if the server saved it
		function sendMessage(content) {
			const msg = new Msg('.msg-alert');

			return fetch("{{ route('send-message') }}", {
				method: 'POST',
				body: JSON.stringify({
					receiver_id: {{ $course->instructor_id }},
					course_id: {{ $course->id }},
					content: content,
				}),
				headers: {
					'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
					'Content-Type': 'application/json',
					'Accept': 'application/json',
				},
			}).then(async res => {
				const response = await res.json();

				if (res.ok)
					addBubble(content);
				else
					msg.popup({ type: 'error', msg: response.message || 'An error occurred', duration: 3000 });

				return res.ok;
			}).catch(() => {
				msg.popup({ type: 'error', msg: 'Something went wrong. Please try again.', duration: 3000 });

				return false;
			});
		}

		// Appends the student's own message to the bottom of the thread
		function addBubble(content) {
			const thread = document.querySelector('[data-thread]');
			thread.querySelector('[data-thread-empty]')?.remove();

			const bubble = document.createElement('div');
			bubble.className = 'w-fit max-w-[80%] rounded-2xl px-4 py-2 text-sm ml-auto bg-primary text-white';

			// textContent, never innerHTML: the message is shown exactly as typed and can't inject HTML
			const text = document.createElement('p');
			text.className = 'whitespace-pre-line';
			text.textContent = content;

			const time = document.createElement('time');
			time.className = 'mt-1 block text-[11px] text-white/60';
			time.textContent = 'just now';

			bubble.append(text, time);
			thread.append(bubble);
			thread.scrollTop = thread.scrollHeight;
		}
	</script>
@endpush

<x-layout :footer="false">
	<div class="msg-alert"></div>
	<x-student-course-tab :course="$course" />

	<div class="mb-12 px-4 sm:px-6 lg:px-8" x-data="{ query: '' }">
		<div class="mb-8 flex flex-col gap-5 md:flex-row md:items-end md:justify-between">
			<div>
				<p class="text-xs font-extrabold uppercase tracking-[0.2em] text-golden">Instructor communication</p>
				<h1 class="mt-2 text-3xl font-extrabold text-gray-900 sm:text-4xl">Messages</h1>
				<p class="mt-2 max-w-2xl text-gray-600">Ask your instructor anything and keep the whole conversation in one place.</p>
			</div>
			<div class="flex items-center gap-2 rounded-2xl bg-white px-4 py-3 shadow-sm ring-1 ring-gray-100">
				<i class="fas fa-envelope-open-text text-primary"></i>
				<span class="text-sm font-bold text-gray-700">{{ $messages->count() }} message{{ $messages->count() === 1 ? '' : 's' }}</span>
			</div>
		</div>

		<div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_320px]">
			<section class="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm sm:p-7">
				<div class="mb-6">
					<h2 class="text-xl font-extrabold text-gray-900">Conversation with {{ $course->owner->fullName() }}</h2>
					<p class="mt-1 text-sm text-gray-500">Messages related to {{ $course->name }}.</p>
				</div>

				<div class="mb-6">
					<x-search-bar x-model="query" placeholder="Search your messages..." />
				</div>

				<div class="max-h-[60vh] space-y-3 overflow-y-auto rounded-2xl bg-gray-50 p-4" data-thread x-init="$el.scrollTop = $el.scrollHeight">
					@forelse($messages as $message)
						@php($mine = $message->sender_id == auth()->id())
						<div
							x-show="@js(Str::lower($message->content)).includes(query.toLowerCase())"
							@class(['w-fit max-w-[80%] rounded-2xl px-4 py-2 text-sm', 'ml-auto bg-primary text-white' => $mine, 'bg-white text-gray-700 ring-1 ring-gray-100' => ! $mine])
						>
							<p class="whitespace-pre-line">{{ $message->content }}</p>
							<time @class(['mt-1 block text-[11px]', 'text-white/60' => $mine, 'text-gray-400' => ! $mine]) datetime="{{ $message->created_at->toIso8601String() }}">{{ $message->created_at->diffForHumans() }}</time>
						</div>
					@empty
						<p data-thread-empty class="py-10 text-center text-sm text-gray-400">No messages yet. Ask your instructor anything below.</p>
					@endforelse
				</div>

				<form
					x-data="{ content: '', sending: false }"
					@submit.prevent="sending = true; sendMessage(content).then(sent => { sending = false; if (sent) content = ''; })"
					class="mt-4 flex items-end gap-2"
				>
					<textarea x-model="content" rows="2" maxlength="2000" required class="w-full rounded-2xl border border-gray-200 bg-white px-3 py-3 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-primary/20" placeholder="Message {{ $course->owner->fullName() }}..."></textarea>
					<button type="submit" :disabled="sending || !content.trim()" class="rounded-full bg-primary px-5 py-3 text-sm font-semibold text-white hover:opacity-90 hover:cursor-pointer disabled:opacity-50" aria-label="Send message">
						<i class="fas fa-paper-plane"></i>
					</button>
				</form>
			</section>

			<aside class="space-y-6">
				<div class="rounded-3xl bg-primary p-6 text-white shadow-sm">
					<a href="{{ route('instructors.show', $course->owner) }}" class="group flex items-center gap-4">
						<x-avatar :user="$course->owner" class="ring-2 ring-white/20" />
						<div>
							<p class="text-xs font-bold uppercase tracking-wider text-golden">Your instructor</p>
							<h2 class="mt-1 text-lg font-extrabold group-hover:underline">{{ $course->owner->fullName() }}</h2>
							<p class="text-xs text-white/60">View profile <i class="fas fa-arrow-right ml-1 text-[10px]"></i></p>
						</div>
					</a>
					<div class="mt-6 border-t border-white/15 pt-5">
						<p class="text-sm leading-6 text-white/75">Questions about the material? Send your instructor a message here.</p>
						@if($course->owner->replyTimeLabel())
							<p class="mt-2 text-sm font-semibold text-golden"><i class="fas fa-clock mr-1"></i> {{ $course->owner->replyTimeLabel() }}</p>
						@endif
					</div>

					<form
						method="POST"
						action="{{ route('course.review', $course) }}"
						x-data="{ stars: {{ (int) old('stars', $review?->stars ?? 0) }}, hover: 0 }"
						class="mt-6 border-t border-white/15 pt-5"
					>
						@csrf
						<p class="text-xs font-bold uppercase tracking-wider text-golden">{{ $review ? 'Your review' : 'Rate your instructor' }}</p>

						<input type="hidden" name="stars" :value="stars">
						<div class="mt-2 flex gap-1" @mouseleave="hover = 0">
							@foreach(range(1, 5) as $star)
								<button
									type="button"
									@click="stars = {{ $star }}"
									@mouseenter="hover = {{ $star }}"
									:class="(hover || stars) >= {{ $star }} ? 'text-golden' : 'text-white/30'"
									class="text-2xl transition hover:cursor-pointer"
									aria-label="{{ $star }} star{{ $star === 1 ? '' : 's' }}"
								><i class="fas fa-star"></i></button>
							@endforeach
						</div>
						@error('stars')
							<p class="mt-1 text-xs text-red-200">{{ $message }}</p>
						@enderror

						<textarea name="comment" rows="2" maxlength="1000" class="mt-3 w-full rounded-2xl bg-white/10 px-3 py-2 text-sm text-white placeholder:text-white/50 focus:outline-none focus:ring-2 focus:ring-white/30" placeholder="Anything to add? (optional)"></textarea>
						@error('comment')
							<p class="mt-1 text-xs text-red-200">{{ $message }}</p>
						@enderror

						<button type="submit" :disabled="!stars" class="mt-3 w-full rounded-full bg-white px-5 py-2 text-sm font-bold text-primary hover:opacity-90 hover:cursor-pointer disabled:opacity-50">
							{{ $review ? 'Update review' : 'Submit review' }}
						</button>
					</form>
				</div>

				<div class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
					<div class="flex items-center gap-3">
						<div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-tea-green text-primary">
							<i class="fas fa-book-open"></i>
						</div>
						<div>
							<p class="text-xs font-bold uppercase tracking-wider text-gray-400">Current course</p>
							<h2 class="mt-1 font-extrabold text-gray-900">{{ $course->name }}</h2>
						</div>
					</div>
					<a href="{{ route('courses.learn', $course->slug) }}" class="mt-6 inline-flex items-center text-sm font-extrabold text-primary hover:opacity-75">
						Continue learning <i class="fas fa-arrow-right ml-2 text-xs"></i>
					</a>
				</div>
			</aside>
		</div>
	</div>
</x-layout>
