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

        @stack('styles')

        <!-- Scripts -->
        <script src="https://kit.fontawesome.com/632bc9cc22.js" crossorigin="anonymous"></script>

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

        <script src="https://unpkg.com/vue@next"></script>

        @stack('scripts')

        {!! $trackingCodeInsideHead !!}
    </head>

    <body class="font-sans antialiased">
        {!! $trackingCodeAfterBody !!}

        <nav class="navbar is-primary">
            <div class="navbar-brand">
                <a
                    class="navbar-item"
                    href="{{ route('homepage') }}"
                >
                    <img
                        src="{{ $logoUrl ?? 'https://dummyimage.com/48x28/e5e5e5/000000.png&text=B+752' }}"
                        alt=""
                        height="28"
                    >
                </a>

                <div
                    class="navbar-burger burger"
                    data-target="navbarExampleTransparentExample"
                >
                    <span />
                    <span />
                    <span />
                </div>
            </div>

            <div
                id="navbarExampleTransparentExample"
                class="navbar-menu"
            >
                <div class="navbar-start">
                    @foreach ($menus as $menu)
                        @if ($menu->children)
                            <div class="navbar-item has-dropdown is-hoverable">
                                <a class="navbar-link">
                                    {{ $menu->title }}
                                </a>
                                <div class="navbar-dropdown">
                                    @foreach ($menu->children as $childMenu)
                                        <a
                                            class="navbar-item"
                                            href="{{ $childMenu->link }}"
                                        >
                                            {{ $childMenu->title }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <a
                                class="navbar-item"
                                href="{{ $menu->link }}"
                            >
                                {{ $menu->title }}
                            </a>
                        @endif
                    @endforeach
                </div>

                <div class="navbar-end">
                    <div class="navbar-item has-dropdown is-hoverable">
                        <span class="navbar-link">{{ strtoupper($currentLanguage) }}</span>
                        <div class="navbar-dropdown is-boxed">
                            @foreach ($languageOptions as $language)
                                <a
                                    class="navbar-item"
                                    href="{{ route('language.change', $language['id']) }}"
                                >
                                    {{ strtoupper($language['id']) }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                    <a
                        href="{{ route('login') }}"
                        class="navbar-item pr-5"
                    >
                        Login
                    </a>
                </div>
            </div>
        </nav>

        {{ $slot }}

        <footer class="footer">
            <div class="container">
                <div class="content has-text-centered">
                    <p>
                        <a href="">
                            <i class="fab fa-facebook-square fa-2x"></i>
                        </a>
                        <a href="">
                            <i class="fab fa-twitter-square fa-2x"></i>
                        </a>
                        <a href="">
                            <i class="fab fa-instagram fa-2x"></i>
                        </a>
                        <a href="">
                            <i class="fab fa-snapchat fa-2x"></i>
                        </a>
                    </p>
                </div>
            </div>
        </footer>

        @env ('local')
            <script src="http://localhost:3000/browser-sync/browser-sync-client.js"></script>
        @endenv

        @stack('bottom_scripts')

        {!! $trackingCodeBeforeBody !!}
    </body>

</html>