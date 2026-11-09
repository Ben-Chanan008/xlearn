{{-- Pages pass their own back destination; without one it returns to the previous page --}}
@props(['href' => url()->previous()])

<a href="{{ $href }}" {{ $attributes->merge(['class' => 'mb-6 inline-flex items-center text-sm font-semibold text-primary hover:underline']) }}>
    <i class="fas fa-arrow-left mr-2"></i>{{ $slot->isEmpty() ? 'Back' : $slot }}
</a>
