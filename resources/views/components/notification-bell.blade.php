@php
    $unreadCount = auth()->user()->unreadNotifications()->count();
    $notifications = auth()->user()->notifications()->take(10)->get();
@endphp

<div class="relative" x-data="{ open: false }" @click.away="open = false" data-notification-bell>
    <button type="button" @click="open = !open" class="relative flex h-9 w-9 cursor-pointer items-center justify-center rounded-full bg-primary text-white/90 shadow-sm ring-1 ring-gray-100 hover:text-golden" aria-label="Notifications" data-bell-button>
        <i class="fas fa-lightbulb mx-auto text-sm"></i>
        @if($unreadCount)
            <span data-unread-count class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-500 px-1 text-[11px] font-bold text-white">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        @endif
    </button>

    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="absolute right-0 top-full z-50 mt-2 w-80 rounded-2xl border border-gray-100 bg-white py-2 shadow-xl"
         style="display: none;">
        <div class="flex items-center justify-between px-4 py-2">
            <p class="font-bold text-gray-800">Notifications</p>
            <form method="POST" action="{{ route('notifications.read-all') }}" @class(['hidden' => ! $unreadCount]) data-read-all>
                @csrf
                <button type="submit" class="cursor-pointer text-xs font-semibold text-primary hover:underline">Mark all read</button>
            </form>
        </div>
        <hr class="my-1 border-gray-100">

        <div class="max-h-96 overflow-y-auto" data-notification-list>
            @forelse($notifications as $notification)
                <a href="{{ route('notifications.open', $notification->id) }}" @class(['flex gap-3 px-4 py-3 transition duration-200 hover:bg-gray-50', 'bg-primary/5' => $notification->unread()])>
                    <i class="fas {{ $notification->data['icon'] ?? 'fa-bell' }} mt-1 w-5 text-gray-400"></i>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold text-gray-800">{{ $notification->data['title'] ?? 'Notification' }}</p>
                        <p class="text-xs text-gray-500">{{ $notification->data['body'] ?? '' }}</p>
                        <time class="mt-1 block text-[11px] text-gray-400" datetime="{{ $notification->created_at->toIso8601String() }}">{{ $notification->created_at->diffForHumans() }}</time>
                    </div>
                    @if($notification->unread())
                        <span class="mt-2 h-2 w-2 shrink-0 rounded-full bg-primary" aria-label="Unread"></span>
                    @endif
                </a>
            @empty
                <p class="px-4 py-8 text-center text-sm text-gray-400" data-caught-up>You're all caught up.</p>
            @endforelse
        </div>
    </div>

    {{-- Copied by the script below for notifications that arrive while the page is open --}}
    <template data-badge-template>
        <span class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-500 px-1 text-[11px] font-bold text-white"></span>
    </template>
    <template data-row-template>
        <a class="flex gap-3 bg-primary/5 px-4 py-3 transition duration-200 hover:bg-gray-50">
            <i class="fas mt-1 w-5 text-gray-400" data-icon></i>
            <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold text-gray-800" data-title></p>
                <p class="text-xs text-gray-500" data-body></p>
                <time class="mt-1 block text-[11px] text-gray-400">just now</time>
            </div>
            <span class="mt-2 h-2 w-2 shrink-0 rounded-full bg-primary" aria-label="Unread"></span>
        </a>
    </template>
</div>

<script>
    // Adds new notifications to the bell as they arrive, without a reload
    window.addEventListener('load', () => {
        const bell = document.querySelector('[data-notification-bell]');
        const list = bell.querySelector('[data-notification-list]');
        const openUrl = @js(route('notifications.open', '__id__'));
        let unread = {{ $unreadCount }};

        Echo.private('App.Models.User.{{ auth()->id() }}').notification(notification => {
            unread++;

            let badge = bell.querySelector('[data-bell-button] > span');
            if (! badge) {
                badge = bell.querySelector('[data-badge-template]').content.firstElementChild.cloneNode(true);
                badge.dataset.unreadCount = '';
                bell.querySelector('[data-bell-button]').append(badge);
            }
            badge.textContent = unread > 9 ? '9+' : unread;

            const row = bell.querySelector('[data-row-template]').content.firstElementChild.cloneNode(true);
            row.href = openUrl.replace('__id__', notification.id);
            row.querySelector('[data-icon]').classList.add(notification.icon || 'fa-bell');
            row.querySelector('[data-title]').textContent = notification.title || 'Notification';
            row.querySelector('[data-body]').textContent = notification.body || '';
            list.prepend(row);

            // Same as the page load: the latest 10 only
            list.querySelectorAll('a')[10]?.remove();
            list.querySelector('[data-caught-up]')?.remove();
            bell.querySelector('[data-read-all]').classList.remove('hidden');
        });
    });
</script>
