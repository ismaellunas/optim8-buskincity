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

        @if (!empty($fontUrls['mainTextFont']))
            <link rel="stylesheet" href="{{ $fontUrls['mainTextFont'] }}">
        @endif

        @if (!empty($fontUrls['headingsFont']))
            <link rel="stylesheet" href="{{ $fontUrls['headingsFont'] }}">
        @endif

        @if (!empty($fontUrls['buttonsFont']))
            <link rel="stylesheet" href="{{ $fontUrls['buttonsFont'] }}">
        @endif

        @stack('styles')

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
        <section class="hero is-fullheight">
            <div class="hero-body">
                <div class="container has-text-centered">
                    <div class="columns">
                        <div class="column is-two-fifths has-text-left">
                            <div class="card">
                                <div class="card-image">
                                    <figure class="image is-3by4">
                                        <img src="https://dummyimage.com/550x715/e5e5e5/ffffff.jpg">
                                    </figure>
                                </div>
                            </div>
                        </div>
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </section>

        @env ('local')
            <script src="http://localhost:3000/browser-sync/browser-sync-client.js"></script>
        @endenv

        <script src="{{ mix('js/app.js', 'themes/biz') }}" defer></script>

        @if ($errors->any())
            <script>
                document.getElementById('formFields').classList.remove('is-hidden');
                document.getElementById('socialMediaForm').classList.add('is-hidden');
            </script>
        @endif

        <script>
            function showForm() {
                document.getElementById('formFields').classList.remove('is-hidden');
                document.getElementById('socialMediaForm').classList.add('is-hidden');
            }

            function backOrOpenSocialMediaForm() {
                let formFields = document.getElementById('formFields');
                if (formFields.classList.contains('is-hidden')) {
                    window.location.href = "{{ url('/') }}"
                }
                document.getElementById('formFields').classList.add('is-hidden');
                document.getElementById('socialMediaForm').classList.remove('is-hidden');
            }


            function showHidePassword() {
                document.querySelectorAll('.input-password').forEach(function(el) {
                    if (el.getAttribute('type') === 'password') {
                        el.setAttribute('type', 'text');
                    } else {
                        el.setAttribute('type', 'password');
                    }
                });
                document.querySelectorAll('.icon-password .button').forEach(function(e) {
                    if (e.classList.contains('is-hidden')) {
                        e.classList.remove('is-hidden');
                    } else {
                        e.classList.add('is-hidden');
                    }

                });
            }
        </script>

        @stack('bottom_scripts')


        @if ($additionalJavascript)
            <script>
                {!! $additionalJavascript !!}
            </script>
        @endif

        {!! $trackingCodeBeforeBody !!}
    </body>

</html>
