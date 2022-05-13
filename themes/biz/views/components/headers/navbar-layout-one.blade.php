<nav class="navbar has-shadow is-fixed-top" role="navigation" aria-label="main navigation">
    <div class="container">
        <div class="navbar-brand">
            <a class="navbar-item" href="{{ route('homepage') }}">
                <img src="{{ $logo }}">
            </a>

            <a role="button" class="navbar-burger burger" aria-label="menu" aria-expanded="false" data-target="navbarExampleTransparentExample">
                <span aria-hidden="true"></span>
                <span aria-hidden="true"></span>
                <span aria-hidden="true"></span>
            </a>
        </div>

        <div id="navbarExampleTransparentExample" class="navbar-menu">
            <div class="navbar-start">
                @foreach ($menus as $menu)
                    @if ($menu->children)
                        <div class="navbar-item has-dropdown is-hoverable navbar-item-dropdown">
                            <a class="navbar-link">
                                {{ $menu->title }}
                            </a>
                            <div class="navbar-dropdown">
                                @foreach ($menu->children as $childMenu)
                                    <a
                                        @class([
                                            'navbar-item',
                                            'has-text-primary' => $childMenu->isActive(request()->url()),
                                        ])
                                        href="{{ $childMenu->getUrl() }}"
                                        target="{{ $childMenu->getTarget() }}"
                                    >
                                        {{ $childMenu->title }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <a
                            @class([
                                'navbar-item',
                                'has-text-primary' => $menu->isActive(request()->url()),
                            ])
                            href="{{ $menu->getUrl() }}"
                            target="{{ $menu->getTarget() }}"
                        >
                            {{ $menu->title }}
                        </a>
                    @endif
                @endforeach
            </div>

            <div class="navbar-end">
                <div class="navbar-item has-dropdown is-hoverable">
                    <a href="#" class="navbar-link">{{ strtoupper($currentLanguage) }}</a>
                    <div class="navbar-dropdown">
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
                <div class="navbar-item">
                    <div class="buttons">
                        <a href="{{ route('register') }}" class="button is-primary">
                            <span class="has-text-weight-bold">{{ __("Sign up") }}</span>
                        </a>
                        <a href="{{ route('login') }}" class="button is-light">
                            <span class="has-text-weight-bold">{{ __("Log in") }}</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>