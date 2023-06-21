<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <meta name="description" content="{{ $metaDescription ?? config('app.name') }}">

        @stack('metas')

        <title>{{ $title ?? config('app.name') }}</title>

        @include('favicon')

        <!-- Fonts -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&display=swap">
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
            <!-- Scripts -->
            <script src="https://kit.fontawesome.com/632bc9cc22.js" crossorigin="anonymous"></script>
        @endenv

        @env ('local')
            @if (config('constants.fontawesome_local'))
                @vite(['resources/js/fontawesome.js'])
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

    <body>
        {!! $trackingCodeAfterBody !!}

        <div id="app">
            <section class="section">
                <div class="container">
                    {{ $slot }}
                </div>
            </section>
        </div>

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


            function showHidePassword(identifier) {
                let target = identifier.getAttribute('data-target');
                let inputPassword = document.getElementById(target);

                if (inputPassword.getAttribute('type') === 'password') {
                    inputPassword.setAttribute('type', 'text');
                } else {
                    inputPassword.setAttribute('type', 'password');
                }

                identifier.querySelectorAll('.button').forEach(function(el) {
                    if (el.classList.contains('is-hidden')) {
                        el.classList.remove('is-hidden');
                    } else {
                        el.classList.add('is-hidden');
                    }

                });
            }

            function disableFieldset() {
                const fieldset = document.getElementById('fieldset');

                if (fieldset) {
                    fieldset.setAttribute('disabled','disabled');
                }

                return true;
            };
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
