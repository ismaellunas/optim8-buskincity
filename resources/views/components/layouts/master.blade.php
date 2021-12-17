<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <meta name="description" content="{{ $metaDescription ?? config('app.name') }}">
        <meta name="keywords" content="{{ $metaKeywords ?? config('app.name') }}">

        @stack('metas')

        <title>{{ $title ?? config('app.name') }}</title>

        <!-- Fonts -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">
        <link rel="stylesheet" href="{{ $appCssUrl }}">
        <link rel="stylesheet" href="{{ mix('css/sweetalert2.min.css') }}">

        @if ($additionalCssUrl)
            <link rel="stylesheet" href="{{ $additionalCssUrl }}">
        @endif

        @stack('styles')

        <!-- Scripts -->
        <script src="https://kit.fontawesome.com/632bc9cc22.js" crossorigin="anonymous"></script>

        @if ($additionalJavascriptUrl)
            <script src="{{ $additionalJavascriptUrl }}" crossorigin="anonymous"></script>
        @endif

        @env ('production')
            <!-- Styles -->
            <link href="https://cdn.jsdelivr.net/npm/vue-loading-overlay@4/dist/vue-loading.css" rel="stylesheet">
        @endenv

        @env ('local')
            <!-- Styles -->
            <link rel="stylesheet" href="{{ mix('css/vue-loading.css') }}">
            <!-- Scripts -->
            <script src="{{ mix('js/local.js') }}" defer></script>
        @endenv

        @stack('scripts')

        {!! $trackingCodeInsideHead !!}
    </head>

    <body class="font-sans antialiased">
        {!! $trackingCodeAfterBody !!}

        <x-headers.header :logoUrl="$logoUrl" />

        <div id="app">
            {{ $slot }}
        </div>

        <x-footer
            :logoUrl="$logoUrl"
        />

        @env ('local')
            <script src="http://localhost:3000/browser-sync/browser-sync-client.js"></script>
        @endenv

        @stack('bottom_scripts')

        <script src="{{ mix('js/frontend.js') }}"></script>

        {!! $trackingCodeBeforeBody !!}
    </body>

</html>