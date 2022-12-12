@inject('setting', 'App\Services\SettingService')

@if (!empty($setting->getFaviconUrl()))
<link rel="icon" sizes="32x32" href="{{ $setting->getFaviconUrl(32) }}">
<link rel="icon" sizes="192x192" href="{{ $setting->getFaviconUrl(192) }}">
<link rel="apple-touch-icon" href="{{ $setting->getFaviconUrl(180) }}">
<meta name="msapplication-TileImage" content="{{ $setting->getFaviconUrl(270) }}">
@endif
