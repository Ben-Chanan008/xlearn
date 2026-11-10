{{-- Five golden stars for a 0–5 value, with half stars --}}
@props(['value'])

<span {{ $attributes->merge(['class' => 'inline-flex gap-0.5 text-golden']) }} aria-label="{{ number_format($value, 1) }} out of 5 stars">
    @foreach(range(1, 5) as $star)
        @if($value >= $star)
            <i class="fas fa-star"></i>
        @elseif($value >= $star - 0.5)
            <i class="fas fa-star-half-stroke"></i>
        @else
            <i class="far fa-star text-golden/40"></i>
        @endif
    @endforeach
</span>
