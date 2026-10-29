<x-layout :footer="false">
	<x-student-course-tab :course="$course" />

	<div class="mb-12 px-4 sm:px-6 lg:px-8" x-data="{ query: '', filter: 'all' }">
		<div class="mb-8 flex flex-col gap-5 md:flex-row md:items-end md:justify-between">
			<div>
				<p class="text-xs font-extrabold uppercase tracking-[0.2em] text-golden">Instructor communication</p>
				<h1 class="mt-2 text-3xl font-extrabold text-gray-900 sm:text-4xl">Messages</h1>
				<p class="mt-2 max-w-2xl text-gray-600">Keep up with announcements, feedback, and guidance from your instructor.</p>
			</div>
			<div class="flex items-center gap-2 rounded-2xl bg-white px-4 py-3 shadow-sm ring-1 ring-gray-100">
				<i class="fas fa-envelope-open-text text-primary"></i>
				<span class="text-sm font-bold text-gray-700">{{ $messages->count() }} message{{ $messages->count() === 1 ? '' : 's' }}</span>
			</div>
		</div>

		<div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_320px]">
			<section class="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm sm:p-7">
				<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
					<div>
						<h2 class="text-xl font-extrabold text-gray-900">Your inbox</h2>
						<p class="mt-1 text-sm text-gray-500">Messages related to {{ $course->name }}.</p>
					</div>
					<div class="flex rounded-xl bg-gray-50 p-1 text-sm font-bold text-gray-500">
						<button type="button" @click="filter = 'all'" class="rounded-lg px-3 py-2 transition" :class="filter === 'all' ? 'bg-white text-primary shadow-sm' : ''">All</button>
						<button type="button" @click="filter = 'recent'" class="rounded-lg px-3 py-2 transition" :class="filter === 'recent' ? 'bg-white text-primary shadow-sm' : ''">Recent</button>
					</div>
				</div>

				<label class="mb-6 flex items-center gap-3 rounded-2xl bg-gray-50 px-4 py-3 text-gray-400 ring-1 ring-transparent focus-within:ring-primary/20">
					<i class="fas fa-search"></i>
					<input x-model="query" type="search" placeholder="Search your messages..." class="w-full bg-transparent text-sm text-gray-700 outline-none placeholder:text-gray-400">
				</label>

				<div class="space-y-4">
					@forelse($messages as $message)
						@php($isRecent = $message->created_at->gte(now()->subDays(7)))
						<article
							x-data="{ content: @js(Str::lower($message->content)), sender: @js(Str::lower($message->sender->fullName())), isRecent: @js($isRecent) }"
							x-show="(content.includes(query.toLowerCase()) || sender.includes(query.toLowerCase())) && (filter === 'all' || isRecent)"
							x-transition
							class="rounded-2xl border border-gray-100 bg-gray-50/70 p-5 transition hover:border-primary/20 hover:bg-white hover:shadow-sm"
						>
							<div class="flex items-start gap-4">
								<div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-primary text-sm font-extrabold text-white">
									{{ $message->sender->initials() }}
								</div>
								<div class="min-w-0 flex-1">
									<div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
										<div>
											<h3 class="font-extrabold text-gray-900">{{ $message->sender->fullName() }}</h3>
											<p class="text-xs font-semibold uppercase tracking-wider text-golden">Course instructor</p>
										</div>
					
										<time class="text-xs font-semibold text-gray-400" datetime="{{ $message->created_at->toIso8601String() }}">{{ $message->created_at->diffForHumans() }}</time>
									</div>
									<p class="mt-4 whitespace-pre-line text-sm leading-7 text-gray-600">{{ $message->content }}</p>
								</div>
							</div>
							<button class="flex px-5 py-2 mt-6 items-center justify-center bg-gray-300 shadow-md text-gray-700 rounded-full hover:bg-gray-200">
											<i class="fas fa-comment"></i> Reply 	
										</button>
						</article>
					@empty
						<div class="rounded-2xl border border-dashed border-gray-200 bg-gray-50 px-6 py-14 text-center">
							<div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-tea-green text-primary">
								<i class="fas fa-inbox text-xl"></i>
							</div>
							<h3 class="mt-5 text-lg font-extrabold text-gray-900">Your inbox is clear</h3>
							<p class="mx-auto mt-2 max-w-sm text-sm leading-6 text-gray-500">When your instructor sends an announcement or personal note, it will appear here.</p>
						</div>
					@endforelse
				</div>
			</section>

			<aside class="space-y-6">
				<div class="rounded-3xl bg-primary p-6 text-white shadow-sm">
					<div class="flex items-center gap-4">
						<div class="flex h-14 w-14 items-center justify-center rounded-full bg-white/15 text-xl font-extrabold">
							{{ $course->owner->initials() }}
						</div>
						<div>
							<p class="text-xs font-bold uppercase tracking-wider text-golden">Your instructor</p>
							<h2 class="mt-1 text-lg font-extrabold">{{ $course->owner->fullName() }}</h2>
						</div>
					</div>
					<div class="mt-6 border-t border-white/15 pt-5">
						<p class="text-sm leading-6 text-white/75">Questions about the material? Review your messages for the latest course guidance.</p>
					</div>
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
