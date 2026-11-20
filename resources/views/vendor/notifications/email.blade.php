{{-- Optional extras a notification passes through ->markdown('notifications::email', [...]): eyebrow, accent, details, callout --}}
<x-mail::message :accent="$accent ?? 'green'">
{{-- Label above the greeting --}}
@isset($eyebrow)
<x-mail::eyebrow>{{ $eyebrow }}</x-mail::eyebrow>
@endisset

{{-- Greeting --}}
@if (! empty($greeting))
# {{ $greeting }}
@else
# @lang('Hello!')
@endif

{{-- Intro Lines --}}
@foreach ($introLines as $line)
{{ $line }}

@endforeach

{{-- Details box --}}
@isset($details)
<x-mail::details :rows="$details" />

@endisset
{{-- Callout --}}
@isset($callout)
<x-mail::callout>{{ $callout }}</x-mail::callout>

@endisset
{{-- Action Button --}}
@isset($actionText)
<x-mail::button :url="$actionUrl">
{{ $actionText }}
</x-mail::button>
@endisset

{{-- Outro Lines --}}
@foreach ($outroLines as $line)
{{ $line }}

@endforeach

{{-- Salutation --}}
@if (! empty($salutation))
{{ $salutation }}
@else
See you in class,<br>
**The xlearn team**
@endif

{{-- Subcopy --}}
@isset($actionText)
<x-slot:subcopy>
Button not working? Paste this link into your browser:<br>
<span class="break-all">[{{ $displayableActionUrl }}]({{ $actionUrl }})</span>
</x-slot:subcopy>
@endisset
</x-mail::message>
