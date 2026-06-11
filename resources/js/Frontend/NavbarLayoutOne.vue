<template>
    <nav
        class="navbar has-shadow is-fixed-top"
        role="navigation"
        aria-label="main navigation"
    >
        <div class="container">
            <div class="navbar-brand">
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
                <div class="navbar-start">
                    <navbar-dropdown-item
                        v-for="(menu, index) in navMenus"
                        :key="index"
                        :menu="menu"
                    />
                </div>

                <div class="navbar-end">
                    <div class="navbar-item has-dropdown is-hoverable navbar-item-dropdown">
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

    export default {
        name: "FrontendNavbarLayoutOne",

        components: {
            BizLink,
            BizNavbarItem,
            NavbarDropdownItem,
        },

        mixins: [
            MixinNavbarMenu,
        ],
    }
</script>
