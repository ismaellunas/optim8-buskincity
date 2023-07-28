<template>
    <nav
        class="navbar has-shadow"
        role="navigation"
        aria-label="main navigation"
    >
        <div class="container">
            <div class="navbar-brand">
                <biz-link
                    :href="navLogo ? navLogo.link : null"
                    class="navbar-item"
                >
                    <img
                        alt=""
                        height="28"
                        :src="appLogoImageUrl"
                    >
                </biz-link>

                <a
                    role="button"
                    class="navbar-burger"
                    :class="{'is-active': isMenuDisplay}"
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
                                @mouseover="toggleDropdown(true, index)"
                                @mouseleave="toggleDropdown(false, index)"
                            >
                                <a
                                    class="navbar-link"
                                    :class="{'is-active': menu.isActive}"
                                >
                                    {{ menu.title }}
                                </a>
                                <div
                                    class="navbar-dropdown"
                                    :style="[isMenuDisplay ? '' : {display: activeDropdownIndex == index ? 'block' : 'none'}]"
                                >
                                    <template
                                        v-for="(childMenu, childIndex) in menu.children"
                                        :key="childIndex"
                                    >
                                        <biz-navbar-item
                                            v-if="childMenu.isEnabled"
                                            :is-internal-link="true"
                                            :class="{'has-text-primary': childMenu.isActive}"
                                            :url="childMenu.link"
                                            @after-click="closeMenu()"
                                        >
                                            {{ childMenu.title }}
                                        </biz-navbar-item>
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
                                @click="closeMenu()"
                            >
                                {{ menu.title }}
                            </biz-link>
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
                                <biz-navbar-item
                                    v-if="navProfile"
                                    is-internal-link
                                    :url="navProfile.link"
                                    @after-click="closeMenu()"
                                >
                                    {{ navProfile.title }}
                                </biz-navbar-item>

                                <biz-link
                                    v-if="$page.props.jetstream.hasApiFeatures"
                                    :href="route('api-tokens.index')"
                                    class="navbar-item"
                                >
                                    API Tokens
                                </biz-link>

                                <hr class="navbar-divider">

                                <biz-navbar-item
                                    ref="logout"
                                    url=""
                                    @click.prevent="logout"
                                >
                                    Logout
                                </biz-navbar-item>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>
</template>

<script>
    import BizLink from '@/Biz/Link.vue';
    import BizNavbarItem from '@/Biz/NavbarItem.vue';
    import { computed, onMounted, onUnmounted, ref } from 'vue';
    import { router, usePage } from '@inertiajs/vue3';

    export default {
        name: 'BizNavbarMenu',
        components: {
            BizLink,
            BizNavbarItem,
        },
        setup() {
            const navMenus = computed(() => usePage().props.menus.nav);
            const navLogo = computed(() => usePage().props.menus.navLogo);
            const navProfile = computed(() => usePage().props.menus.navProfile);
            const appLogoImageUrl = computed(() => usePage().props.appLogoUrl);

            const navbarDropdown = document.getElementsByClassName('navbar-item-dropdown');
            const activeDropdownIndex = ref(null);
            const isMenuDisplay = ref(false);

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

            const toggleDropdown = (payload, index) => {
                if (payload && !isMenuDisplay.value) {
                    activeDropdownIndex.value = index;
                } else {
                    activeDropdownIndex.value = null;
                }
            };

            router.on('before', (event) => {
                toggleDropdown(false, null);
            });

            return {
                appLogoImageUrl,
                csrfToken: usePage().props.csrfToken,
                isMenuDisplay,
                navLogo,
                navMenus,
                navProfile,
                activeDropdownIndex,
                toggleDropdown,
            };
        },
        methods: {
            switchToTeam(team) {
                router.put(route('current-team.update'), {
                    'team_id': team.id
                }, {
                    preserveState: false
                });
            },

            logout() {
                router.post(route('logout'));
            },

            showMenu() {
                this.isMenuDisplay = !this.isMenuDisplay;
            },

            closeMenu() {
                this.isMenuDisplay = false;
            },

        }
    };
</script>
