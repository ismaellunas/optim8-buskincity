<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (trim($slot) === config('app.name') && !empty(ThemeHelper::getLogoUrl()))
<img src="{{ ThemeHelper::getLogoUrl() }}" class="logo" alt="{{ config('app.name') }}">
@else
{{ $slot }}
@endif
</a>
</td>
</tr>
