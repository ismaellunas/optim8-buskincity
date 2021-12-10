<nav class="navbar is-primary is-justify-content-space-between">
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
    <div>
        <div
            id="navbarExampleTransparentExample"
            class="navbar-menu"
        >
            <div class="navbar-end">
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
        <div class="navbar-menu">
            <div class="navbar-end pr-3">
                <a href="" class="navbar-item">
                    <i class="fab fa-facebook-square fa-2x"></i>
                </a>
                <a href="" class="navbar-item">
                    <i class="fab fa-twitter-square fa-2x"></i>
                </a>
                <a href="" class="navbar-item">
                    <i class="fab fa-instagram fa-2x"></i>
                </a>
                <a href="" class="navbar-item">
                    <i class="fab fa-snapchat fa-2x"></i>
                </a>
            </div>
        </div>
    </div>
</nav>