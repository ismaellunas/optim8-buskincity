<template>
    <nav
        class="navbar has-shadow is-fixed-top"
        role="navigation"
        aria-label="main navigation"
    >
        <div class="container">
            <div class="navbar-brand is-hidden-desktop">
                <a
                    class="navbar-item"
                    :href="navLogo.link"
                >
                    <img :src="logoUrl">
                </a>

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
                <div class="navbar-center is-hidden-touch">
                    <div class="level mb-0">
                        <div class="level-item">
                            <navbar-dropdown-item
                                v-for="(menu, index) in splitedNavMenus[0]"
                                :key="index"
                                :menu="menu"
                                top-dropdown-class="p-2"
                                top-link-class="p-5"
                            />
                        </div>

                        <div class="level-item">
                            <a
                                class="navbar-item force-center is-hidden-touch"
                                :href="navLogo.link"
                            >
                                <img :src="logoUrl">
                            </a>
                        </div>

                        <div class="level-item">
                            <navbar-dropdown-item
                                v-for="(menu, index) in splitedNavMenus[1]"
                                :key="index"
                                :menu="menu"
                                top-dropdown-class="p-2"
                                top-link-class="p-5"
                            />
                        </div>
                    </div>
                </div>

                <div class="navbar-start is-hidden-desktop">
                    <navbar-dropdown-item
                        v-for="(menu, index) in navMenus"
                        :key="index"
                        :menu="menu"
                    />
                </div>

                <div class="navbar-end navbar-end-layout-2">
                    <div class="navbar-item has-dropdown is-hoverable navbar-item-dropdown p-2">
                        <a class="navbar-link">
                            {{ $page.props.user.full_name }}
                        </a>

                        <div class="navbar-dropdown is-right">
                            <template
                                v-for="(dropdownMenu, index) in dropdownRightMenus"
                                :key="index"
                            >
                                <biz-navbar-item
                                    v-if="dropdownMenu.isEnabled"
                                    :class="{'has-text-primary': dropdownMenu.isActive}"
                                    :is-internal-link="dropdownMenu.isInternalLink"
                                    :url="dropdownMenu.link"
                                    @after-click="closeMenu()"
                                >
                                    {{ dropdownMenu.title }}
                                </biz-navbar-item>
                            </template>

                            <biz-link
                                v-if="$page.props.jetstream.hasApiFeatures"
                                class="navbar-item"
                                :href="route('api-tokens.index')"
                            >
                                API Tokens
                            </biz-link>

                            <hr
                                v-if="hasDropdownRightMenus"
                                class="navbar-divider"
                            >

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
                                    href="#"
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
    import MixinNavbarMenu from '@/Mixins/NavbarMenu';
    import BizLink from '@/Biz/Link.vue';
    import BizNavbarItem from '@/Biz/NavbarItem.vue';
    import NavbarDropdownItem from './NavbarDropdownItem.vue';
    import { chunk } from 'lodash';

    export default {
        name: "FrontendNavbarLayoutTwo",

        components: {
            BizLink,
            BizNavbarItem,
            NavbarDropdownItem,
        },

        mixins: [
            MixinNavbarMenu,
        ],

        computed: {
            splitedNavMenus() {
                return chunk(
                    this.navMenus,
                    Math.ceil(this.navMenus.length / 2)
                );
            },
        },
    }
</script>
