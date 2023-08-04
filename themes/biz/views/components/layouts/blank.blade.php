<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        @stack('metas')

        <title>{{ $title ?? config('app.name') }}</title>

        @stack('styles')

        @stack('scripts')
    </head>

    <body class="font-sans antialiased">
        <div id="app">
            <div class="content">
                {{ $slot }}
            </div>
        </div>

        @stack('bottom_scripts')
    </body>
</html>
