@inject('setting', 'App\Services\SettingService')
<!DOCTYPE html>
<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    class="has-navbar-fixed-top"
>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <link rel="stylesheet" href="{{ $setting->getFrontendCssUrl() }}" defer>

        @include('favicon')

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @routes

        @vite(['resources/js/app.js'])

        @include('fontawesome')
    </head>

    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
