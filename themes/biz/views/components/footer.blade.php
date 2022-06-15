<footer class="has-background-light pt-6 pb-6">
    <div class="container">
        <div class="columns is-multiline">
            <div class="column is-3">
                <img
                    src="{{ $logoUrl ?? 'https://dummyimage.com/150x100/e5e5e5/000000.png&text=B+752' }}"
                    style="max-width:160px"
                >
            </div>
            <div class="column is-9">
                <div class="columns is-multiline">
                    @foreach($menus['nav'] as $menu)
                        <div class="column is-4">
                            <aside class="menu">
                                <p class="menu-label">{{ $menu->title }}</p>
                                <ul class="menu-list">
                                    @foreach ($menu->children as $childMenu)
                                        <li>
                                            <a
                                                href="{{ $childMenu->url }}"
                                                target="{{ $childMenu->target }}"
                                            >
                                                {{ $childMenu->title }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </aside>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="column is-12">
                <p class="is-size-7 has-text-centered mt-5">
                    © Copyright 2022, {{ config('app.name') }}
                </p>
            </div>
        </div>
    </div>
</footer>