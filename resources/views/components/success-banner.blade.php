@if (session('error') || session('success'))
    <div
        x-data="{ show: true }"
        x-init="setTimeout(() => show = false, 50000000)"
        x-show="show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 -translate-y-4"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-4"
        class="fixed top-24 right-8 z-50 max-w-sm w-full bg-white rounded-3xl shadow-2xl border border-tea-green/50 overflow-hidden flex items-stretch"
        
    >
        @if(session()->has('success'))
            <div class="w-2 bg-tea-green"></div>
            <div class="p-6 flex items-center gap-4 grow">
                <div class="w-12 h-12 bg-tea-green/20 rounded-2xl flex items-center justify-center text-primary shrink-0">
                    <i class="fas fa-check-circle text-xl"></i>
                </div>
                <div class="grow">
                    <h4 class="font-black text-primary text-sm uppercase tracking-widest leading-none mb-1">Success!</h4>
                    <p class="text-gray-600 text-sm font-medium">{{ session('success') }}</p>
                </div>
                <button @click="show = false" class="text-gray-400 hover:text-primary transition-colors cursor-pointer">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        @elseif(session()->has('error'))
            <div class="w-2 bg-tea-green"></div>
            <div class="p-6 flex items-center gap-4 grow">
                <div class="w-12 h-12 bg-tea-green/20 rounded-2xl flex items-center justify-center text-primary shrink-0">
                    <i class="fas fa-circle-xmark text-xl"></i>
                </div>
                <div class="grow">
                    <h4 class="font-black text-primary text-sm uppercase tracking-widest leading-none mb-1">Error!</h4>
                    <p class="text-gray-600 text-sm font-medium">{{ session('error') }}</p>
                </div>
                <button @click="show = false" class="text-gray-400 hover:text-primary transition-colors cursor-pointer">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        @endif
    </div>
    {{-- {{ dd(session()->all()) }} --}}
@endif
