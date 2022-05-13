<footer class="footer has-background-info-light">
    <div class="container">
        <div class="columns">
            <div class="column">
                <a href="{{ route('homepage') }}">
                    <img
                        src="{{ $logoUrl ?? 'https://dummyimage.com/150x100/e5e5e5/000000.png&text=B+752' }}"
                        alt=""
                        width="150"
                    >
                </a>
            </div>
            <div class="column">
                <div class="content has-text-right menu-footer">
                    <p>
                        @foreach ($menus as $menu)
                            <a
                                href="{{ $menu->getUrl() }}"
                                class="pl-5 has-text-black"
                                target="{{ $menu->getTarget() }}"
                            >
                                {{ $menu->title }}
                            </a>
                        @endforeach
                    </p>
                    <p class="mt-5">
                        @foreach ($socialMediaMenus as $socialMediaMenu)
                            <a
                                href="{{ $socialMediaMenu['url'] }}"
                                class="pl-3 has-text-black is-size-4"
                                target="{{ $socialMediaMenu['target'] }}"
                            >
                                <i class="{{ $socialMediaMenu['icon'] }}"></i>
                            </a>
                        @endforeach
                    </p>
                </div>
            </div>
        </div>
    </div>
</footer>