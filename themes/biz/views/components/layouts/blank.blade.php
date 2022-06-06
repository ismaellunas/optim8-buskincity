<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        @stack('metas')

        <title>{{ $title ?? config('app.name') }}</title>

        <!-- Fonts -->
        <link rel="stylesheet" href="{{ $appCssUrl }}">

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

        <script src="{{ mix('js/app.js', 'themes/' . config('theme.active')) }}" defer></script>

        @if ($additionalJavascript)
            <script>
                {!! $additionalJavascript !!}
            </script>
        @endif
    </body>

</html>
