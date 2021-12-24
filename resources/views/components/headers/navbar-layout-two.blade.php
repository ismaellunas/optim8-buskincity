<nav class="navbar level is-primary mb-0">

    <div class="level-item">
        <div
            id="navbarExampleTransparentExample"
            class="navbar-menu"
        >
            <div class="navbar-end">
                @if (count($menuChunks) > 0)
                    @foreach ($menuChunks[0] as $key => $menu)
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
                                            target="{{ $target($childMenu->is_blank) }}"
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
                                target="{{ $target($menu->is_blank) }}"
                            >
                                {{ $menu->title }}
                            </a>
                        @endif
                    @endforeach
                @endif
            </div>
        </div>
    </div>
    <div class="level-item">
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
                <span />
                <span />
                <span />
            </div>
        </div>
    </div>
    <div class="level-item">
        <div
            id="navbarExampleTransparentExample"
            class="navbar-menu"
        >
            <div class="navbar-start">
                @if (count($menuChunks) > 1)
                    @foreach ($menuChunks[1] as $key => $menu)
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
                                            target="{{ $target($childMenu->is_blank) }}"
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
                                target="{{ $target($menu->is_blank) }}"
                            >
                                {{ $menu->title }}
                            </a>
                        @endif
                    @endforeach
                @endif
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
    </div>
</nav>