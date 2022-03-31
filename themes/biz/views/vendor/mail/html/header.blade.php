@inject('settingService', 'App\Services\SettingService')

<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (trim($slot) === config('app.name') && !empty($settingService->getLogoUrl()))
<img src="{{ $settingService->getLogoUrl() }}" class="logo" alt="{{ config('app.name') }}">
@else
{{ $slot }}
@endif
</a>
</td>
</tr>
