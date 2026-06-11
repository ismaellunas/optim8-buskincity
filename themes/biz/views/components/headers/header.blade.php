{{-- Inline so the rules ship with the header component itself. The master layout
     emits `@stack('styles')` in <head> BEFORE this component renders, so a
     `@push('styles')` from here would silently drop on the floor. --}}
<style>
    /* Nested navbar dropdowns (e.g. City & Pitches -> Country -> City).
       Desktop: flyout to the right; only direct-child opens on hover.
       Mobile: render inline (flat list) inside the parent dropdown. */
    @media screen and (min-width: 1024px) {
        .navbar-dropdown .navbar-item.has-dropdown {
            position: relative;
        }

        .navbar-dropdown .navbar-item.has-dropdown > .navbar-dropdown {
            position: absolute;
            top: 0;
            left: 100%;
            margin-top: 0;
            min-width: 100%;
            white-space: nowrap;
        }

        /* Bulma's `.navbar-item.has-dropdown.is-hoverable:hover .navbar-dropdown`
           uses a descendant combinator and reveals EVERY nested dropdown when the
           top-level item is hovered. Hide nested panels by default, then show
           only the direct child of the currently-hovered nested item. */
        .navbar-dropdown .navbar-dropdown {
            display: none !important;
        }

        .navbar-dropdown .navbar-item.has-dropdown:hover > .navbar-dropdown,
        .navbar-dropdown .navbar-item.has-dropdown:focus-within > .navbar-dropdown,
        .navbar-dropdown .navbar-item.has-dropdown.is-active > .navbar-dropdown {
            display: block !important;
        }

        /* Make nested dropdown header rows (Country) visually identical to the
           flat city rows below them: full-width row, transparent default,
           light-gray on direct hover only, no primary-color "open" highlight. */
        .navbar-dropdown .navbar-item.has-dropdown {
            display: block;
            padding: 0;
        }

        .navbar-dropdown .navbar-item.has-dropdown > .navbar-link {
            background-color: transparent;
            color: inherit;
            padding-right: 0.75rem;
        }

        .navbar-dropdown .navbar-item.has-dropdown:hover > .navbar-link {
            background-color: #f5f5f5;
            color: inherit;
        }
    }

    @media screen and (max-width: 1023px) {
        .navbar-dropdown .navbar-item.has-dropdown > .navbar-dropdown {
            display: block;
            position: static;
            box-shadow: none;
            padding-left: 1.25rem;
        }
    }
</style>

<x-dynamic-component
    :component="$headerLayoutName"
    :menus="$menus"
    :currentLanguage="$currentLanguage"
    :languageOptions="$languageOptions"
/>

@push('bottom_scripts')
<script>
    const burgerMenu = document.querySelector('.navbar-burger');
    const navbarMenu = document.querySelector('.navbar-menu');

    burgerMenu.addEventListener('click', function () {
        navbarMenu.classList.toggle('is-active');
        burgerMenu.classList.toggle('is-active');
    });

    const navbarDropdown = document.getElementsByClassName('navbar-item-dropdown');

    function setActiveMenu(e) {
        if (e.target.classList.contains('navbar-link')) {
            this.classList.toggle('is-active');
        }
    }

    function eventDropdownMenu() {
        for (let i = 0; i < navbarDropdown.length; i++) {
            if (window.innerWidth < 1024) {
                navbarDropdown[i].addEventListener('click', setActiveMenu);
            } else {
                navbarDropdown[i].classList.remove('is-active');
                navbarDropdown[i].removeEventListener('click', setActiveMenu);
            }
        }
    }

    eventDropdownMenu();

    window.addEventListener('resize', function () {
        eventDropdownMenu();
    });
</script>
@endpush