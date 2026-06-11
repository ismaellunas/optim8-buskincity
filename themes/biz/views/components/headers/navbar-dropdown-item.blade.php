@php
    /**
     * Renders a navbar menu item, recursing for nested children to produce a
     * Bulma flyout dropdown (e.g. City & Pitches → Country → City).
     *
     * Variables (passed via @include):
     *  - $menu: array{title,link,target,children,...}
     *  - $isActive: closure(?string $url): bool from BaseNavbarLayout
     *  - $depth: int (default 0)
     *  - $topDropdownClass: extra classes for the top-level dropdown wrapper
     *  - $topLinkClass: extra classes for top-level flat links
     */
    $depth = $depth ?? 0;
    $topDropdownClass = $topDropdownClass ?? '';
    $topLinkClass = $topLinkClass ?? '';
    $isTop = $depth === 0;
    $children = $menu['children'] ?? [];
@endphp

@if (! empty($children))
    <div @class([
        'navbar-item has-dropdown is-hoverable navbar-item-dropdown',
        $topDropdownClass => $isTop && $topDropdownClass !== '',
    ])>
        <a class="navbar-link">{{ $menu['title'] }}</a>

        <div class="navbar-dropdown">
            @foreach ($children as $childMenu)
                @include('components.headers.navbar-dropdown-item', [
                    'menu' => $childMenu,
                    'isActive' => $isActive,
                    'depth' => $depth + 1,
                    'topDropdownClass' => '',
                    'topLinkClass' => '',
                ])
            @endforeach
        </div>
    </div>
@else
    <a
        @class([
            'navbar-item',
            'has-text-primary' => $isActive($menu['link']),
            $topLinkClass => $isTop && $topLinkClass !== '',
        ])
        href="{{ $menu['link'] }}"
        target="{{ $menu['target'] }}"
    >
        {{ $menu['title'] }}
    </a>
@endif
