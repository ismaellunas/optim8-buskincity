@inject('settingService', 'App\Services\SettingService')

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @include('favicon')

        <!-- Fonts -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;600;700&display=swap">

        <link rel="stylesheet" href="{{ $settingService->getBackendCssUrl() }}" defer>

        <!-- Scripts -->
        @routes

        @vite(['resources/js/app.js'])

        @include('fontawesome')
    </head>

    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
