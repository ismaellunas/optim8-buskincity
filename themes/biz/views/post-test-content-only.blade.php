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
                        </div>

                        <div class="column is-3 is-offset-1">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @stack('bottom_scripts')

        @stack('bottom_styles')
    </body>
</html>
