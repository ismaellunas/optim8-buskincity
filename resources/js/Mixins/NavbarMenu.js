import { usePage } from '@inertiajs/vue3';
import { isEmpty } from 'lodash';

function setActiveMenu(e) {
    if (e.target.classList.contains('navbar-link')) {
        this.classList.toggle('is-active');
    }
}

export default {
    data() {
        return {
            isMenuDisplay: false,
            csrfToken: usePage().props.csrfToken,
        };
    },

    computed: {
        menus() {
            return usePage().props.menus;
        },

        navLogo() {
            return this.menus.navLogo ?? {};
        },

        navMenus() {
            return this.menus.nav ?? {};
        },

        dropdownRightMenus() {
            return this.menus.dropdownRightMenus ?? {};
        },

        hasDropdownRightMenus() {
            return ! isEmpty(this.dropdownRightMenus);
        },

        logoUrl() {
            return usePage().props.appLogoUrl;
        },
    },

    mounted() {
        const self = this;

        self.eventDropdownMenu();

        window.addEventListener('resize', function () {
            self.eventDropdownMenu();
        });
    },

    unmounted() {
        const self = this;
        const navbarDropdown = document.getElementsByClassName('navbar-item-dropdown');

        for (let i = 0; i < navbarDropdown.length; i++) {
            navbarDropdown[i].removeEventListener('click', setActiveMenu);
        }
    },

    methods: {
        switchToTeam(team) {
            this.$inertia.put(route('current-team.update'), {
                'team_id': team.id
            }, {
                preserveState: false
            });
        },

        logout() {
            if (route().current('admin*')) {
                this.$inertia.post(route('logout'));
            } else {
                this.$refs.logout.submit();
            }
        },

        showMenu() {
            this.isMenuDisplay = ! this.isMenuDisplay;
        },

        closeMenu() {
            this.isMenuDisplay = false;
        },

        eventDropdownMenu() {
            const self = this;
            const navbarDropdown = document.getElementsByClassName('navbar-item-dropdown');

            for (let i = 0; i < navbarDropdown.length; i++) {
                if (window.innerWidth < 1024) {
                    navbarDropdown[i].addEventListener('click', setActiveMenu);
                } else {
                    navbarDropdown[i].classList.remove('is-active');
                    navbarDropdown[i].removeEventListener('click', setActiveMenu);
                }
            }
        },
    },
}