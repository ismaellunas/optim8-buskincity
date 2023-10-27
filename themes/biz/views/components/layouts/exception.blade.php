<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        @stack('metas')

        <title>{{ $title ?? config('app.name') }}</title>

        @include('favicon')

        <!-- Fonts -->
        <link rel="stylesheet" href="{{ $appCssUrl }}">

        @include('head-fonts', ['fontUrls' => $fontUrls])

        @stack('styles')

        @include('fontawesome')

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

        @if ($additionalJavascript)
            <script>
                {!! $additionalJavascript !!}
            </script>
        @endif

        {!! $trackingCodeBeforeBody !!}
    </body>
</html>
