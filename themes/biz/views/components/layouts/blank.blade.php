<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        @stack('metas')

        <title>{{ $title ?? config('app.name') }}</title>

        @include('favicon')

        @stack('styles')

        @stack('scripts')

        @if ($additionalCss)
            <style type="text/css">
                {!! $additionalCss !!}
            </style>
        @endif
    </head>

    <body class="font-sans antialiased">
        <div id="app">
            <div class="content">
                {{ $slot }}
            </div>
        </div>

        @stack('bottom_scripts')

        @vite(['themes/'.config('theme.active').'/js/app.js'])

        @if ($additionalJavascript)
            <script>
                {!! $additionalJavascript !!}
            </script>
        @endif
    </body>

</html>
