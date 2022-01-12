<template>
    <nav
        class="navbar has-shadow"
        role="navigation"
        aria-label="main navigation"
    >
        <div class="container">
            <div class="navbar-brand">
                <sdb-link
                    :href="navLogo.link"
                    class="navbar-item"
                >
                    <img
                        src="https://dummyimage.com/48x28/e5e5e5/000000.png&text=B+752"
                        alt=""
                        height="28"
                    >
                </sdb-link>

                <a
                    role="button"
                    class="navbar-burger"
                    aria-label="menu"
                    aria-expanded="false"
                    data-target="navbarBasicExample"
                    @click="showMenu()"
                >
                    <span aria-hidden="true" />
                    <span aria-hidden="true" />
                    <span aria-hidden="true" />
                </a>
            </div>

            <div
                id="navbarBasicExample"
                class="navbar-menu"
                :class="{ 'is-active': isMenuDisplay }"
            >
                <div class="navbar-start">
                    <template
                        v-for="(menu, index) in navMenus"
                        :key="index"
                    >
                        <template v-if="menu.children && menu.isEnabled">
                            <div
                                class="navbar-item has-dropdown is-hoverable navbar-item-dropdown"
                            >
                                <a
                                    class="navbar-link"
                                    :class="{'is-active': menu.isActive}"
                                >
                                    {{ menu.title }}
                                </a>
                                <div class="navbar-dropdown">
                                    <template
                                        v-for="(childMenu, childIndex) in menu.children"
                                        :key="childIndex"
                                    >
                                        <sdb-link
                                            v-if="childMenu.isEnabled"
                                            class="navbar-item"
                                            :href="childMenu.link"
                                            :class="{'is-active': menu.isActive}"
                                        >
                                            {{ childMenu.title }}
                                        </sdb-link>
                                    </template>
                                </div>
                            </div>
                        </template>
                        <template v-else>
                            <sdb-link
                                v-if="menu.isEnabled"
                                class="navbar-item"
                                :active="menu.isActive"
                                :href="menu.link"
                            >
                                {{ menu.title }}
                            </sdb-link>
                        </template>
                    </template>
                </div>

                <div class="navbar-end">
                    <div class="navbar-item">
                        <div class="navbar-item has-dropdown is-hoverable navbar-item-dropdown">
                            <a class="navbar-link">
                                {{ $page.props.user.full_name }}
                            </a>

                            <div class="navbar-dropdown is-boxed">
                                <sdb-link
                                    :href="navProfile.link"
                                    class="navbar-item"
                                >
                                    {{ navProfile.title }}
                                </sdb-link>

                                <sdb-link
                                    v-if="$page.props.jetstream.hasApiFeatures"
                                    :href="route('api-tokens.index')"
                                    class="navbar-item"
                                >
                                    API Tokens
                                </sdb-link>

                                <hr class="navbar-divider">

                                <form
                                    method="POST"
                                    @submit.prevent="logout"
                                >
                                    <sdb-button
                                        as="button"
                                        class="navbar-item ml-2 is-ghost"
                                    >
                                        Log Out
                                    </sdb-button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>
</template>

<script>
    import SdbButton from '@/Sdb/Button';
    import SdbLink from '@/Sdb/Link';
    import { computed, onMounted, onUnmounted } from 'vue';
    import { usePage } from '@inertiajs/inertia-vue3';

    export default {
        components: {
            SdbButton,
            SdbLink,
        },
        setup() {
            const navMenus = computed(() => usePage().props.value.menus.nav);
            const navLogo = computed(() => usePage().props.value.menus.navLogo);
            const navProfile = computed(() => usePage().props.value.menus.navProfile);

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

            onMounted(() => {
                eventDropdownMenu();

                window.addEventListener('resize', function () {
                    eventDropdownMenu();
                });
            });

            onUnmounted(() => {
                for (let i = 0; i < navbarDropdown.length; i++) {
                    navbarDropdown[i].removeEventListener('click', setActiveMenu);
                }
            });

            return {
                navMenus,
                navLogo,
                navProfile,
            };
        },
        data() {
            return {
                isMenuDisplay: false,
            };
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
                this.$inertia.post(route('logout'));
            },
            showMenu() {
                this.isMenuDisplay = !this.isMenuDisplay;
            },
        }
    }
</script>
