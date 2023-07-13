@inject('setting', 'App\Services\SettingService')

@php
    $ogImageUrl = $setting->getOpenGraphImageUrl(
        config('constants.dimensions.open_graph.width'),
        config('constants.dimensions.open_graph.height'),
    );
@endphp

<!-- Facebook Meta Tags -->
<meta property="og:url" content="{{ config('app.url') }}">
<meta property="og:type" content="website">
<meta property="og:title" content="{{ $title ?? config('app.name') }}">
@if (! empty($ogImageUrl))
    <meta property="og:image" content="{{ $ogImageUrl }}">
@endif
<meta property="og:image:width" content="{{ config('constants.dimensions.open_graph.width') }}"/>
<meta property="og:image:height" content="{{ config('constants.dimensions.open_graph.height') }}"/>

<!-- Twitter Meta Tags -->
<meta name="twitter:card" content="summary_large_image">
<meta property="twitter:domain" content="config('constants.domain')">
<meta property="twitter:url" content="{{ config('app.url') }}">
<meta name="twitter:title" content="{{ $title ?? config('app.name') }}">
@if (! empty($ogImageUrl))
    <meta name="twitter:image" content="{{ $ogImageUrl }}">
@endif
<meta property="twitter:image:width" content="{{ config('constants.dimensions.open_graph.width') }}"/>
<meta property="twitter:image:height" content="{{ config('constants.dimensions.open_graph.height') }}"/>

@if (!empty($metaDescription) && $metaDescription != "")
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta name="twitter:description" content="{{ $metaDescription }}">
@endif