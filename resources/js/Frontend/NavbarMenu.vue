<template>
    <nav
        class="navbar has-shadow is-fixed-top"
        role="navigation"
        aria-label="main navigation"
    >
        <div class="container">
            <div class="navbar-brand">
                <biz-link
                    class="navbar-item"
                    :href="navLogo ? navLogo.link : null"
                >
                    <img :src="logoImageUrl">
                </biz-link>

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
                                        <biz-link
                                            v-if="childMenu.isEnabled"
                                            class="navbar-item"
                                            :href="childMenu.link"
                                            :class="{'is-active': menu.isActive}"
                                        >
                                            {{ childMenu.title }}
                                        </biz-link>
                                    </template>
                                </div>
                            </div>
                        </template>
                        <template v-else>
                            <biz-link
                                v-if="menu.isEnabled"
                                class="navbar-item"
                                :active="menu.isActive"
                                :href="menu.link"
                            >
                                {{ menu.title }}
                            </biz-link>
                        </template>
                    </template>
                </div>

                <div class="navbar-end">
                    <div class="navbar-item has-dropdown is-hoverable">
                        <a class="navbar-link">
                            {{ $page.props.user.full_name }}
                        </a>

                        <div class="navbar-dropdown">
                            <template
                                v-for="(dropdownMenu, index) in $page.props.menus.dropdownRightMenus"
                                :key="index"
                            >
                                <biz-link
                                    v-if="dropdownMenu.isEnabled"
                                    :href="dropdownMenu.link"
                                    class="navbar-item"
                                >
                                    {{ dropdownMenu.title }}
                                </biz-link>
                            </template>

                            <biz-link
                                v-if="$page.props.jetstream.hasApiFeatures"
                                :href="route('api-tokens.index')"
                                class="navbar-item"
                            >
                                API Tokens
                            </biz-link>

                            <hr class="navbar-divider">

                            <form
                                ref="logout"
                                method="POST"
                                :action="route('logout')"
                                @submit.prevent="logout"
                            >
                                <input
                                    type="hidden"
                                    name="_token"
                                    :value="csrfToken"
                                >
                                <biz-link
                                    class="navbar-item"
                                    @click.prevent="logout"
                                >
                                    Logout
                                </biz-link>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>
</template>

<script>
    import BizLink from '@/Biz/Link';
    import { computed, onMounted, onUnmounted } from 'vue';
    import { usePage } from '@inertiajs/inertia-vue3';

    export default {
        components: {
            BizLink,
        },
        setup() {
            const navMenus = computed(() => usePage().props.value.menus.nav);
            const navLogo = computed(() => usePage().props.value.menus.navLogo);

            const logoImageUrl = computed(() => {
                return (
                    usePage().props.value.logoUrl
                    ?? "https://dummyimage.com/48x28/e5e5e5/000000.png&text=Logo"
                );
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
                logoImageUrl,
                navMenus,
                navLogo,
            };
        },
        data() {
            return {
                isMenuDisplay: false,
                csrfToken: this.$page.props.csrfToken,
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
                if (route().current('admin*')) {
                    this.$inertia.post(route('logout'));
                } else {
                    this.$refs.logout.submit();
                }
            },
            showMenu() {
                this.isMenuDisplay = !this.isMenuDisplay;
            },
        }
    }
</script>
