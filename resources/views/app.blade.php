<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        {{-- <title>{{ config('app.name', 'Laravel') }}</title> --}}

        <!-- Fonts -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">

        <!-- Styles -->
        <link rel="stylesheet" href="{{ mix('css/additional.css') }}">

        <!-- Scripts -->
        @routes
        <script src="{{ mix('js/app.js') }}" defer></script>

        @env ('production')
            <!-- Scripts -->
            <script defer src="https://use.fontawesome.com/releases/v5.15.3/js/solid.js" integrity="sha384-9xA4r2/2cctd+IZZKLvI1hmeHZ5Yp8xXkS6J8inDtdyZCqhEHVcTGmSUCbNED5Ae" crossorigin="anonymous"></script>
            <script defer src="https://use.fontawesome.com/releases/v5.15.3/js/regular.js" integrity="sha384-bPKzNk+f6IzEi89cU+jf3bwWzJQqo+U1/QYUijuD7XD9WO3MSrrAVVEglIOCo6VD" crossorigin="anonymous"></script>
            <script defer src="https://use.fontawesome.com/releases/v5.15.3/js/brands.js" integrity="sha384-oEE/PrsvhwsuT1MjC4sgnz39CQ84HoPt8jwH0RLyJDdDOKulN+UEbm9IgJW0aTu5" crossorigin="anonymous"></script>
            <script defer src="https://use.fontawesome.com/releases/v5.15.3/js/fontawesome.js" integrity="sha384-hD97VKS04Rv8VYShf782apVZOVP6bVH/ubzrWXIIbKOwnD6gsDIcB29K03FL1A9J" crossorigin="anonymous"></script>
        @endenv

        @env ('local')
            <!-- Scripts -->
            <script src="{{ mix('js/local.js') }}" defer></script>
        @endenv

    </head>
    <body class="font-sans antialiased">
        @inertia

        @env ('local')
            <script src="http://localhost:3000/browser-sync/browser-sync-client.js"></script>
        @endenv
    </body>
</html>
