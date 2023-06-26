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

        @if (!empty($metaDescription) && $metaDescription != "")
            <meta name="description" content="{{ $metaDescription }}">
        @endif

        @stack('metas')

        <title>{{ $title ?? config('app.name') }}</title>

        @include('favicon')

        <!-- Fonts -->
        @include('head-fonts', ['fontUrls' => $fontUrls])

        <link rel="stylesheet" href="{{ $appCssUrl }}">

        @stack('styles')

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
                :logo="$logo"
            />
        @endif

        <div id="app">
            {{ $slot }}
        </div>

        @if ($hasFooter)
            <x-footer
                :logo="$logo"
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

        @stack('bottom_inject')
    </body>
</html>
