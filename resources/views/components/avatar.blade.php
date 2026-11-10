{{-- A user's photo, or their initials on brown when they haven't added one --}}
@props(['user', 'size' => 'md'])

@php
    $sizes = [
        'xs' => 'h-7 w-7 text-[10px]',
        'sm' => 'h-10 w-10 text-sm',
        'md' => 'h-14 w-14 text-lg',
        'lg' => 'h-20 w-20 text-2xl',
    ];
@endphp

@if($user->avatarUrl())
    <img src="{{ $user->avatarUrl() }}" alt="{{ $user->fullName() }}" {{ $attributes->merge(['class' => "{$sizes[$size]} shrink-0 rounded-full object-cover"]) }}>
@else
    <div {{ $attributes->merge(['class' => "{$sizes[$size]} flex shrink-0 items-center justify-center rounded-full bg-primary font-extrabold text-white"]) }}>
        {{ $user->initials() }}
    </div>
@endif
