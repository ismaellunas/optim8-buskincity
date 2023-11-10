<footer class="has-background-light">
    <section class="section">
        <div class="container theme-font">
            <div class="columns is-multiline is-mobile">
                <div class="column is-3-desktop is-3-tablet is-12-mobile">
                    <figure
                        class="image mb-2"
                        style="max-width:160px"
                    >
                        <x-image
                            src="{{ $logo['url'] }}"
                            width="{{ $logo['width'] }}"
                            height="{{ $logo['height'] }}"
                            is-lazyload
                        />
                    </figure>

                    <div class="buttons">
                        @foreach ($socialMediaMenus as $socialMedia)
                            <a
                                class="button is-ghost has-text-black"
                                target="{{ $socialMedia['target'] }}"
                                href="{{ $socialMedia['url'] }}"
                            >
                                <x-icon :icon="$socialMedia['icon']" />
                            </a>
                        @endforeach
                    </div>
                </div>
                <div class="column is-9-desktop is-9-tablet is-12-mobile">
                    <div class="columns is-multiline is-mobile">
                        @foreach($menus['nav'] as $menu)
                            <div class="column is-4-desktop is-6-tablet is-12-mobile">
                                <aside class="menu">
                                    <p class="menu-label">{{ $menu['title'] }}</p>
                                    <ul class="menu-list">
                                        @foreach ($menu['children'] as $childMenu)
                                            <li>
                                                <a
                                                    href="{{ $childMenu['link'] }}"
                                                    target="{{ $childMenu['target'] }}"
                                                >
                                                    {{ $childMenu['title'] }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </aside>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="columns is-multiline is-mobile">
                <div class="column is-12-desktop is-12-tablet is-12-mobile">
                    <p class="is-size-7 has-text-centered mt-5">
                        This site is protected by reCAPTCHA and the Google
                        <a href="https://policies.google.com/privacy">Privacy Policy</a> and
                        <a href="https://policies.google.com/terms">Terms of Service</a> apply.<br>
                        © Copyright 2022, {{ config('app.name') }}
                    </p>
                </div>
            </div>
        </div>
    </section>
</footer>
