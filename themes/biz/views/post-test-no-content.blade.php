<!DOCTYPE html>
<html class='has-navbar-fixed-top'>
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width" />
        <title></title>


        <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="{{ $appCssUrl }}">

        @stack('metas')
        @stack('styles')
        @stack('scripts')

        @env ('production')
            <!-- Styles -->
            <link href="https://cdn.jsdelivr.net/npm/vue-loading-overlay@6/dist/css/index.css" rel="stylesheet">
            <!-- Scripts -->
            <script src="https://kit.fontawesome.com/32c120ba1c.js" crossorigin="anonymous"></script>
        @endenv
        @vite(['themes/'.config('theme.active').'/js/app.js', 'resources/js/bulma-misc.js'])
    </head>
    <body>
        <div id="app">
            <div class="b752-blog-post section is-medium">
                <div class="container">
                    <div class="columns is-centered">
                        <div class="column is-7">
                            <header>
                                <h1 class="title is-1">Esse magni temporibus quidem rem ea.</h1>

                                <div class="is-flex">
                                    <nav class="breadcrumb">
                                        <ul>
                                            <li>
                                                <a href="http://localhost:8001">
                                                    Home
                                                </a>
                                            </li>
                                            <li>
                                                <a href="http://localhost:8001/blog">
                                                    Blog
                                                </a>
                                            </li>
                                            <li class="is-active">
                                                <a href="#">
                                                    News
                                                </a>
                                            </li>
                                        </ul>
                                    </nav>
                                    <div>
                                        <span class="mr-1">•</span> 2 Minute Read
                                    </div>
                                </div>
                            </header>
                        </div>

                        <div class="column is-3 is-offset-1">
                            <div class="b752-blog-sidebar">
                                <aside class="menu">
                                    <p class="menu-label">Table of Contents</p>
                                    <ul class="menu-list">
                                        <li>
                                            <a href="#quos-sit-inventore">
                                                Quos Sit Inventore
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#ad-impedit-est">
                                                Ad Impedit Est
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#aspernatur-incidunt-autem">
                                                Aspernatur Incidunt Autem
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#quia-doloribus-enim">
                                                Quia Doloribus Enim
                                            </a>
                                        </li>
                                        <li>
                                            <a href="#provident-provident-ut">
                                                Provident Provident Ut
                                            </a>
                                        </li>
                                    </ul>
                                </aside>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @stack('bottom_scripts')

        @stack('bottom_styles')
    </body>
</html>
