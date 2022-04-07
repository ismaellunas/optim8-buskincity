<nav class="navbar is-info is-justify-content-space-between">
    <div class="navbar-brand">
        <a
            class="navbar-item"
            href="{{ route('homepage') }}"
        >
            <img
                src="{{ $logo }}"
                alt=""
                height="28"
            >
        </a>

        <div
            class="navbar-burger burger"
            data-target="navbarExampleTransparentExample"
        >
            <span aria-hidden="true"></span>
            <span aria-hidden="true"></span>
            <span aria-hidden="true"></span>
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
                        <div class="navbar-item has-dropdown is-hoverable navbar-item-dropdown">
                            <a class="navbar-link">
                                {{ $menu->title }}
                            </a>
                            <div class="navbar-dropdown">
                                @foreach ($menu->children as $childMenu)
                                    <a
                                        class="navbar-item"
                                        href="{{ $childMenu->link }}"
                                        target="{{ $childMenu->target }}"
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
                            target="{{ $menu->target }}"
                        >
                            {{ $menu->title }}
                        </a>
                    @endif
                @endforeach
                <div class="navbar-item has-dropdown is-hoverable navbar-item-dropdown">
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
                @foreach ($socialMediaMenus as $socialMediaMenu)
                    <a
                        href="{{ $socialMediaMenu['url'] }}"
                        class="navbar-item"
                    >
                        <i class="{{ $socialMediaMenu['icon'] }}"></i>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</nav>