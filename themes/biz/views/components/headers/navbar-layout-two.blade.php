@aware(['logo'])

<nav class="navbar has-shadow is-fixed-top" role="navigation" aria-label="main navigation">
    <div class="container">
        <div class="navbar-brand is-hidden-desktop">
            <a class="navbar-item" href="{{ $menus['navLogo']['link'] }}">
                <figure class="image">
                    <x-image
                        src="{{ $logo['url'] }}"
                        width="{{ $logo['width'] }}"
                        height="80"
                        is-lazyload
                    />
                </figure>
            </a>

            <a role="button" class="navbar-burger burger" aria-label="menu" aria-expanded="false" data-target="navbarExampleTransparentExample">
                <span aria-hidden="true"></span>
                <span aria-hidden="true"></span>
                <span aria-hidden="true"></span>
            </a>
        </div>

        <div id="navbarExampleTransparentExample" class="navbar-menu">
            <div class="navbar-start is-hidden-touch">
                @guest
                <div class="navbar-item has-dropdown is-hoverable navbar-item-dropdown">
                    <a href="#" class="navbar-link">{{ strtoupper($currentLanguage) }}</a>
                    <div class="navbar-dropdown">
                        @foreach ($languageOptions as $language)
                            <a
                                class="navbar-item"
                                href="{{ LaravelLocalization::localizeURL(route('language.change', $language['id'])) }}"
                            >
                                {{ strtoupper($language['id']) }}
                            </a>
                        @endforeach
                    </div>
                </div>
                @endguest
            </div>

            <div class="navbar-center is-hidden-touch">
                <div class="level mb-0">
                    <div class="level-item">
                        @if (isset($menus['splited'][0]))
                            @foreach ($menus['splited'][0] as $menu)
                                @if ($menu['children'])
                                    <div class="navbar-item has-dropdown is-hoverable navbar-item-dropdown p-2">
                                        <a class="navbar-link">
                                            {{ $menu['title'] }}
                                        </a>
                                        <div class="navbar-dropdown">
                                            @foreach ($menu['children'] as $childMenu)
                                                <a
                                                    @class([
                                                        'navbar-item',
                                                        'has-text-primary' => $isActive($childMenu['link']),
                                                    ])
                                                    href="{{ $childMenu['link'] }}"
                                                    target="{{ $childMenu['target'] }}"
                                                >
                                                    {{ $childMenu['title'] }}
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                @else
                                    <a
                                        @class([
                                            'navbar-item',
                                            'has-text-primary' => $isActive($menu['link']),
                                            'p-5',
                                        ])
                                        href="{{ $menu['link'] }}"
                                        target="{{ $menu['target'] }}"
                                    >
                                        {{ $menu['title'] }}
                                    </a>
                                @endif
                            @endforeach
                        @endif
                    </div>

                    <div class="level-item">
                        <a
                            class="navbar-item force-center is-hidden-touch"
                            href="{{ $menus['navLogo']['link'] }}"
                        >
                            <figure class="image">
                                <x-image
                                    src="{{ $logo['url'] }}"
                                    width="{{ $logo['width'] }}"
                                    height="80"
                                    is-lazyload
                                />
                            </figure>
                        </a>
                    </div>

                    <div class="level-item">
                        @if (isset($menus['splited'][1]))
                            @foreach ($menus['splited'][1] as $menu)
                                @if ($menu['children'])
                                    <div class="navbar-item has-dropdown is-hoverable navbar-item-dropdown p-2">
                                        <a class="navbar-link">
                                            {{ $menu['title'] }}
                                        </a>
                                        <div class="navbar-dropdown">
                                            @foreach ($menu['children'] as $childMenu)
                                                <a
                                                    @class([
                                                        'navbar-item',
                                                        'has-text-primary' => $isActive($childMenu['link']),
                                                    ])
                                                    href="{{ $childMenu['link'] }}"
                                                    target="{{ $childMenu['target'] }}"
                                                >
                                                    {{ $childMenu['title'] }}
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                @else
                                    <a
                                        @class([
                                            'navbar-item',
                                            'has-text-primary' => $isActive($menu['link']),
                                            'p-5',
                                        ])
                                        href="{{ $menu['link'] }}"
                                        target="{{ $menu['target'] }}"
                                    >
                                        {{ $menu['title'] }}
                                    </a>
                                @endif
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>

            <div class="navbar-end navbar-end-layout-2 is-hidden-touch">
                @guest
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
                @endguest

                @auth
                    <div class="navbar-item has-dropdown is-hoverable navbar-item-dropdown p-2">
                        <a class="navbar-link">
                            {{ auth()->user()->full_name }}
                        </a>

                        <div class="navbar-dropdown is-right">
                            @foreach ($menus['dropdownRightMenus'] as $menu)
                                @if ($menu['isEnabled'])
                                    <a
                                        class="navbar-item"
                                        href="{{ $menu['link'] }}"
                                    >
                                        {{ $menu['title'] }}
                                    </a>
                                @endif
                            @endforeach

                            @if (!empty($menus['dropdownRightMenus']))
                                <hr class="navbar-divider">
                            @endif

                            <form
                                id="form-logout"
                                method="POST"
                                action="{{ route('logout') }}"
                            >
                                @csrf
                                <a class="navbar-item" onclick="onLogout(event)">
                                    Logout
                                </a>
                            </form>
                        </div>
                    </div>
                @endauth
            </div>

            <div class="navbar-start is-hidden-desktop">
                @foreach ($menus['nav'] as $menu)
                    @if ($menu['children'])
                        <div class="navbar-item has-dropdown is-hoverable navbar-item-dropdown">
                            <a class="navbar-link">
                                {{ $menu['title'] }}
                            </a>
                            <div class="navbar-dropdown">
                                @foreach ($menu['children'] as $childMenu)
                                    <a
                                        @class([
                                            'navbar-item',
                                            'has-text-primary' => $isActive($childMenu['link']),
                                        ])
                                        href="{{ $childMenu['link'] }}"
                                        target="{{ $childMenu['target'] }}"
                                    >
                                        {{ $childMenu['title'] }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <a
                            @class([
                                'navbar-item',
                                'has-text-primary' => $isActive($menu['link']),
                            ])
                            href="{{ $menu['link'] }}"
                            target="{{ $menu['target'] }}"
                        >
                            {{ $menu['title'] }}
                        </a>
                    @endif
                @endforeach
            </div>

            <div class="navbar-end navbar-end-layout-2 is-hidden-desktop">
                @guest
                    <div class="navbar-item has-dropdown is-hoverable navbar-item-dropdown">
                        <a href="#" class="navbar-link">{{ strtoupper($currentLanguage) }}</a>
                        <div class="navbar-dropdown">
                            @foreach ($languageOptions as $language)
                                <a
                                    class="navbar-item"
                                    href="{{ LaravelLocalization::localizeURL(route('language.change', $language['id'])) }}"
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
                @endguest

                @auth
                    <div class="navbar-item has-dropdown is-hoverable navbar-item-dropdown">
                        <a class="navbar-link">
                            {{ auth()->user()->full_name }}
                        </a>

                        <div class="navbar-dropdown is-right">
                            @foreach ($menus['dropdownRightMenus'] as $menu)
                                @if ($menu['isEnabled'])
                                    <a
                                        class="navbar-item"
                                        href="{{ $menu['link'] }}"
                                    >
                                        {{ $menu['title'] }}
                                    </a>
                                @endif
                            @endforeach

                            @if (!empty($menus['dropdownRightMenus']))
                                <hr class="navbar-divider">
                            @endif

                            <form
                                id="form-logout"
                                method="POST"
                                action="{{ route('logout') }}"
                            >
                                @csrf
                                <a class="navbar-item" onclick="onLogout(event)">
                                    Logout
                                </a>
                            </form>
                        </div>
                    </div>
                @endauth
            </div>
        </div>
    </div>
</nav>

@push('bottom_scripts')
<script>
    function onLogout(e) {
        e.preventDefault();
        document.getElementById('form-logout').submit();
    }
</script>
@endpush