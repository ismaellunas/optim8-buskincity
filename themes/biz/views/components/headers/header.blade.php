<x-dynamic-component
    :component="$headerLayoutName"
    :menus="$menus"
    :currentLanguage="$currentLanguage"
    :languageOptions="$languageOptions"
/>

@push('styles')
<style>
    /* Nested navbar dropdowns (e.g. City & Pitches -> Country -> City).
       Desktop: flyout to the right of the parent dropdown.
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
@endpush

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