<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        {{-- <title>{{ config('app.name', 'Laravel') }}</title> --}}

        <!-- Fonts -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;600;700&display=swap">
        <link rel="stylesheet" href="{{ mix('css/app.css') }}" defer>

        <!-- Scripts -->
        @routes
        <script src="{{ mix('js/app.js') }}" defer></script>

        @env ('production')
            <!-- Styles -->
            <link href="https://cdn.jsdelivr.net/npm/vue-loading-overlay@4/dist/vue-loading.css" rel="stylesheet">
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
    </head>
    <body class="font-sans antialiased">
        @inertia

        @env ('local')
            <script src="http://localhost:3000/browser-sync/browser-sync-client.js"></script>
        @endenv
    </body>
</html>
