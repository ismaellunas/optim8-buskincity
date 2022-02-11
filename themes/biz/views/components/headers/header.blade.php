<x-dynamic-component
    :component="$headerLayoutName"
    :menus="$menus"
    :currentLanguage="$currentLanguage"
    :logoUrl="$logoUrl"
    :languageOptions="$languageOptions"
/>

@push('bottom_scripts')
<script>
    const burgerMenu = document.querySelector('.burger');
    const navbarMenu = document.querySelector('.navbar-menu');

    burgerMenu.addEventListener('click', function () {
        navbarMenu.classList.toggle('is-active');
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