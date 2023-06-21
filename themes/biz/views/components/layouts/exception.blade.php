<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        @stack('metas')

        <title>{{ $title ?? config('app.name') }}</title>

        @include('favicon')

        <!-- Fonts -->
        @include('head-fonts', ['fontUrls' => $fontUrls])

        <link rel="stylesheet" href="{{ $appCssUrl }}">

        @stack('styles')

        @env ('production')
            <!-- Scripts -->
            <script src="https://kit.fontawesome.com/632bc9cc22.js" crossorigin="anonymous"></script>
        @endenv

        @env ('local')
            @vite(['resources/sass/local.sass'])

            @if (config('constants.fontawesome_local'))
                @vite(['resources/js/fontawesome.js'])
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

        <div id="app" class="has-background-grey-darker">
            <div
                class="is-flex is-justify-content-center is-align-items-center"
                style="height: 100vh"
            >
                <div class="container has-text-white has-text-centered">
                    {{ $slot }}
                </div>
            </div>
        </div>

        @stack('bottom_scripts')

        @vite(['themes/'.config('theme.active').'/js/app.js'])

        @if ($additionalJavascript)
            <script>
                {!! $additionalJavascript !!}
            </script>
        @endif

        {!! $trackingCodeBeforeBody !!}
    </body>
</html>
