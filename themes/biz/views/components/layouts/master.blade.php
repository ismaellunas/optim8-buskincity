<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <meta name="description" content="{{ $metaDescription ?? config('app.name') }}">
        <meta name="keywords" content="{{ $metaKeywords ?? config('app.name') }}">

        @stack('metas')

        <title>{{ $title ?? config('app.name') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&display=swap" rel="stylesheet">

        @if (!empty($fontUrls['mainTextFont']))
            <link rel="stylesheet" href="{{ $fontUrls['mainTextFont'] }}">
        @endif

        @if (!empty($fontUrls['headingsFont']))
            <link rel="stylesheet" href="{{ $fontUrls['headingsFont'] }}">
        @endif

        @if (!empty($fontUrls['buttonsFont']))
            <link rel="stylesheet" href="{{ $fontUrls['buttonsFont'] }}">
        @endif

        @stack('styles')

        {{-- <link rel="stylesheet" href="{{ $appCssUrl }}"> --}}
        <link rel="stylesheet" href="{{ mix('css/template.css') }}">

        @env ('production')
            <!-- Styles -->
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/vue-loading-overlay@4/dist/vue-loading.css">
            <!-- Scripts -->
            <script src="https://kit.fontawesome.com/32c120ba1c.js" crossorigin="anonymous"></script>
        @endenv

        @env ('local')
            <!-- Styles -->
            <link rel="stylesheet" href="{{ mix('css/local.css') }}">
            <!-- Scripts -->
            <script src="{{ mix('js/local.js') }}" defer></script>

            @if (config('constants.fontawesome_local'))
                <script src="{{ mix('js/fontawesome.js') }}" defer></script>
            @else
                <script src="https://kit.fontawesome.com/32c120ba1c.js" crossorigin="anonymous"></script>
            @endif
        @endenv

        @stack('scripts')

        @if ($additionalCss)
            <style type="text/css">
                {!! $additionalCss !!}
            </style>
        @endif

        {!! $trackingCodeInsideHead !!}
    </head>

    <body class="font-sans antialiased">
        {!! $trackingCodeAfterBody !!}

        <x-headers.header :logoUrl="$logoUrl" />

        <div id="app">
            {{ $slot }}
        </div>

        <x-footer
            :logoUrl="$logoUrl"
        />

        @env ('local')
            <script src="http://localhost:3000/browser-sync/browser-sync-client.js"></script>
        @endenv

        @stack('bottom_scripts')

        <script src="{{ mix('js/app.js', 'themes/' . config('theme.active')) }}" defer></script>

        @if ($additionalJavascript)
            <script>
                {!! $additionalJavascript !!}
            </script>
        @endif

        {!! $trackingCodeBeforeBody !!}
    </body>

</html>
