@inject('storageService', 'App\Services\StorageService')

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
        <link rel="stylesheet" href="{{ $appCssUrl }}">

        @include('head-fonts', ['fontUrls' => $fontUrls])

        @stack('styles')

        @include('fontawesome')

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

        <div id="app">
            <loading-overlay id="loader" class="is-hidden"></loading-overlay>

            <section class="section">
                <div class="container">
                    <div class="columns is-multiline">
                        <div class="column is-5-desktop is-6-tablet is-hidden-mobile">
                            <div class="card">
                                <div class="card-image">
                                    <figure class="image is-3by4">
                                        <x-image
                                            src="{{ $storageService::getImageUrl(config('constants.default_images.user_auth_card')) }}"
                                            class="is-radius"
                                            width="470"
                                            height="600"
                                            is-lazyload
                                        />
                                    </figure>
                                </div>
                            </div>
                        </div>
                        {{ $slot }}
                    </div>
                </div>
            </section>
        </div>

        @vite(['themes/'.config('theme.active').'/js/app.js'])

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

            function setLoader() {
                document.getElementById('loader').classList.remove('is-hidden');
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
