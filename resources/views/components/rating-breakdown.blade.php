{{-- One bar per star level; $counts is [5 => 80, 4 => 30, ...] from countBy() --}}
@props(['counts'])

@php($total = max($counts->sum(), 1))

<div {{ $attributes->merge(['class' => 'space-y-2']) }}>
    @foreach(range(5, 1) as $star)
        @php($count = $counts[$star] ?? 0)
        <div class="flex items-center gap-3 text-sm">
            <span class="w-8 shrink-0 font-bold text-gray-700">{{ $star }} <i class="fas fa-star text-[10px] text-golden"></i></span>
            <div class="h-2.5 grow overflow-hidden rounded-full bg-gray-100">
                <div class="h-full rounded-full bg-golden" style="width: {{ round($count / $total * 100) }}%"></div>
            </div>
            <span class="w-8 shrink-0 text-right text-gray-500">{{ $count }}</span>
        </div>
    @endforeach
</div>
