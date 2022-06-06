<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        @stack('metas')

        <title>{{ $title ?? config('app.name') }}</title>

        @if (!empty($faviconUrl))
            <link rel="icon" type="image/x-icon" href="{{ $faviconUrl }}">
        @endif

        <!-- Fonts -->
        <link rel="stylesheet" href="{{ $appCssUrl }}">

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

        @env ('production')
            <!-- Scripts -->
            <script src="https://kit.fontawesome.com/632bc9cc22.js" crossorigin="anonymous"></script>
        @endenv

        @env ('local')
            <!-- Styles -->
            <link rel="stylesheet" href="{{ mix('css/local.css') }}">
            <!-- Scripts -->
            <script src="{{ mix('js/local.js') }}" defer></script>

            @if (config('constants.fontawesome_local'))
                <script src="{{ mix('js/fontawesome.js') }}" defer></script>
            @else
                <script src="https://kit.fontawesome.com/632bc9cc22.js" crossorigin="anonymous"></script>
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

        <div id="app">
            <div class="content">
                {{ $slot }}
            </div>
        </div>

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
