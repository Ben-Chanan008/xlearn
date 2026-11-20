@props(['rows'])
{{-- No blank lines inside, or markdown ends the HTML block there --}}
<table class="details" width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td class="details-cell">
<table width="100%" cellpadding="0" cellspacing="0" role="presentation">
@foreach ($rows as $label => $value)
<tr>
<td @class(['details-label', 'details-divider' => ! $loop->first])>{{ $label }}</td>
<td @class(['details-value', 'details-divider' => ! $loop->first]) align="right">{{ $value }}</td>
</tr>
@endforeach
</table>
</td>
</tr>
</table>
