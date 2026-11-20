@props(['accent' => 'green'])
<x-mail::layout :accent="$accent">
{{-- Header --}}
<x-slot:header>
<x-mail::header :url="route('home')">
{{ config('app.name') }}
</x-mail::header>
</x-slot:header>

{{-- Body --}}
{!! $slot !!}

{{-- Subcopy --}}
@isset($subcopy)
<x-slot:subcopy>
<x-mail::subcopy>
{!! $subcopy !!}
</x-mail::subcopy>
</x-slot:subcopy>
@endisset

{{-- Footer --}}
<x-slot:footer>
<x-mail::footer>
[Visit xlearn]({{ route('home') }})

You're getting this email because you have an xlearn account.<br>
© {{ date('Y') }} xlearn. {{ __('All rights reserved.') }}
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>
