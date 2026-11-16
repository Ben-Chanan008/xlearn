{{-- The search input's look; extra attributes (x-model, name, placeholder...) go on the input --}}
<label class="flex items-center gap-3 rounded-2xl bg-gray-50 px-4 py-3 text-gray-400 ring-1 ring-transparent focus-within:ring-primary/20">
    <i class="fas fa-search"></i>
    <input type="search" {{ $attributes->merge(['class' => 'w-full bg-transparent text-sm text-gray-700 outline-none placeholder:text-gray-400']) }}>
</label>
