<!DOCTYPE html>
<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    @class([
        'has-navbar-fixed-top' => $hasHeader
    ])
>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name') }}</title>

        @if (!empty($metaDescription) && $metaDescription != "")
            <meta name="description" content="{{ $metaDescription }}">
        @endif

        @stack('metas')

        @include('favicon')

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

        <link rel="stylesheet" href="{{ $appCssUrl }}">

        @env ('production')
            <!-- Scripts -->
            <script src="https://kit.fontawesome.com/32c120ba1c.js" crossorigin="anonymous"></script>
        @endenv

        @env ('local')
            @if (config('constants.fontawesome_local'))
                @vite(['resources/js/fontawesome.js'])
            @else
                <script src="https://kit.fontawesome.com/32c120ba1c.js" crossorigin="anonymous"></script>
            @endif
        @endenv

        @stack('scripts')

        @vite(['themes/'.config('theme.active').'/js/app.js', 'resources/js/bulma-misc.js'])

        @if ($additionalCss)
            <style type="text/css">
                {!! $additionalCss !!}
            </style>
        @endif

        {!! $trackingCodeInsideHead !!}
    </head>

    <body
        @class(array_merge(
            [
                'font-sans',
                'antialiased',
            ],
            $bodyClasses,
        ))
        style="{{ $bodyStyles }}"
    >
        {!! $trackingCodeAfterBody !!}

        @if ($hasHeader)
            <x-headers.header
                :logoUrl="$logoUrl"
            />
        @endif

        <div id="app">
            {{ $slot }}
        </div>

        @if ($hasFooter)
            <x-footer
                :logoUrl="$logoUrl"
            />
        @endif

        @stack('bottom_scripts')

        @stack('bottom_styles')

        @if ($additionalJavascript)
            <script>
                {!! $additionalJavascript !!}
            </script>
        @endif

        {!! $trackingCodeBeforeBody !!}

        @include('cookie-consent::index')
    </body>
</html>
