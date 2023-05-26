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

                    <div class="content mt-5">

                        <h2 id="quos-sit-inventore">Quos Sit Inventore</h2>

<p>Error assumenda voluptates rerum quis. Velit aut qui veritatis ipsa impedit nihil rerum ut. Ipsa pariatur odio sed molestiae ullam. Veniam veritatis est ipsum eum sapiente consequatur. Libero aperiam et voluptatem sit ex et in. Enim sunt minus sequi cum aut distinctio. Autem asperiores possimus dolorem omnis et consectetur qui. Deserunt nulla rerum rerum odit debitis non molestiae. Est aut ipsam laborum praesentium velit fuga. Corporis nemo enim dignissimos est molestiae magni. Dolores eum soluta aliquid rerum a sed iste. Eveniet commodi sint et repudiandae nulla dolor.</p>

<h2 id="ad-impedit-est">Ad Impedit Est</h2>

<p>Maxime sequi qui voluptatem incidunt dolorem ea sed. Veritatis quo voluptas quas id architecto sed. Quos et quibusdam laborum unde. Non qui nostrum corporis labore molestiae nobis vel. Et dolore excepturi quis dignissimos enim nobis explicabo. Ea rerum quasi soluta ut provident voluptates deleniti. Ea est voluptas aut dolores exercitationem nihil hic. Similique ipsam quibusdam velit qui. Molestiae veritatis illum esse repellendus earum adipisci dolor. Harum magnam ratione ea sed.</p>

<h2 id="aspernatur-incidunt-autem">Aspernatur Incidunt Autem</h2>

<p>Distinctio doloribus ullam provident ut autem. Consequatur architecto repellat ea eos exercitationem. Delectus mollitia optio dignissimos omnis culpa cumque laboriosam. Ab non fuga et est autem odio voluptas. Temporibus ratione asperiores et et eligendi et ut. Qui earum quo sed repudiandae. Quis quas eius molestiae nihil ipsa. Ea odit dolor maiores tempora pariatur temporibus perferendis. Recusandae consequuntur est dolor ea nostrum.</p>

<h2 id="quia-doloribus-enim">Quia Doloribus Enim</h2>

<p>Quis rerum maxime quibusdam quia voluptas. Facere est et eum eos aut reiciendis sapiente recusandae. A vero occaecati voluptas enim fugit qui. Iure repellendus sed saepe a dolor quia. Perspiciatis laboriosam tempore sit. Dolorem debitis quasi ab ut maiores fugit non. Temporibus vero ex dolor natus quisquam. Omnis rerum voluptates animi. Voluptatem quam quis voluptas est quis enim corrupti. Aut dolorem et et cum. Autem rerum cupiditate voluptas mollitia provident harum est quia. Earum voluptate modi nostrum architecto. Numquam qui ad accusamus ut hic expedita.</p>

<h2 id="provident-provident-ut">Provident Provident Ut</h2>

<p>Minus quod temporibus blanditiis dolor magnam. Dolor quia vitae ut dicta voluptas. Ducimus hic perferendis aliquid quis. Iusto et corporis minima et dolorem quas magni quae. Quibusdam odio eaque veniam blanditiis consequatur ipsam. Nobis rerum sed amet quia facilis vel. Neque ab autem officiis numquam. Suscipit nam corporis et et. Quod aut voluptatibus doloremque nihil magni temporibus quidem. Sed fugit quos explicabo consequuntur iste.</p>
                    </div>

                    <div class="tags mt-6">
                                                    <span class="tag">Published on 05 May 2023</span>

                        <span class="tag">Last updated on 05 May 2023</span>
                    </div>

                                            <div class="box is-shadowless has-background-light mt-6">
                            <div class="is-flex is-align-items-center">
                                <div class="pr-2">
                                    <figure class="image is-96x96 mr-3">
                                        <img
                                            width="96"
                                            height="96"
                                            src="http://localhost:8001/images/profile-picture-default.png"
                                            class="is-rounded ls-is-cached lazyloaded"
                                            alt="Author: Super Administrator"
                                        />
                                    </figure>
                                </div>
                                <div>
                                    <div class="is-flex is-align-items-center mb-3">
                                        <h3 class="title is-5 m-0">
                                            Super Administrator
                                        </h3>
                                        <p class="is-size-7 ml-3">
                                            Author
                                        </p>
                                    </div>
                                    <p>

                                    </p>
                                </div>
                            </div>
                        </div>
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

            <div class="section is-medium has-background-light">
            <div class="container">
                <div class="columns is-multiline">
                    <div class="column is-12">
                        <h2 class="title is-2 mb-5">Related Articles</h2>
                    </div>

                                            <div class="column is-4">
                            <article class="b752-blog-item box is-shadowless is-clipped p-0">
                                <figure>
                                    <a href="http://localhost:8001/blog/et-est-error-sapiente-ea-est">
                                        <img src="/storage/images/default-post-thumbnail.png"/>
                                    </a>
                                </figure>
                                <div class="p-5">
                                    <h2 class="title is-5 mb-2">
                                        <a href="http://localhost:8001/blog/et-est-error-sapiente-ea-est">Et est error sapiente ea est.</a>
                                    </h2>
                                    <div class="content is-size-7">
                                        <p>News</p>
                                    </div>
                                </div>
                            </article>
                        </div>
                                            <div class="column is-4">
                            <article class="b752-blog-item box is-shadowless is-clipped p-0">
                                <figure>
                                    <a href="http://localhost:8001/blog/error-laborum-itaque-molestiae">
                                        <img src="/storage/images/default-post-thumbnail.png"/>
                                    </a>
                                </figure>
                                <div class="p-5">
                                    <h2 class="title is-5 mb-2">
                                        <a href="http://localhost:8001/blog/error-laborum-itaque-molestiae">Error laborum itaque molestiae.</a>
                                    </h2>
                                    <div class="content is-size-7">
                                        <p>News</p>
                                    </div>
                                </div>
                            </article>
                        </div>
                                            <div class="column is-4">
                            <article class="b752-blog-item box is-shadowless is-clipped p-0">
                                <figure>
                                    <a href="http://localhost:8001/blog/fuga-dolorum-cumque-corrupti">
                                        <img src="/storage/images/default-post-thumbnail.png"/>
                                    </a>
                                </figure>
                                <div class="p-5">
                                    <h2 class="title is-5 mb-2">
                                        <a href="http://localhost:8001/blog/fuga-dolorum-cumque-corrupti">Fuga dolorum cumque corrupti.</a>
                                    </h2>
                                    <div class="content is-size-7">
                                        <p>News</p>
                                    </div>
                                </div>
                            </article>
                        </div>
                                    </div>
            </div>
        </div>
        </div>

        @stack('bottom_scripts')

        @stack('bottom_styles')
    </body>
</html>
