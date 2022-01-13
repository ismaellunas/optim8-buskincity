<template>
    <nav class="navbar is-primary">
        <div class="navbar-brand">
            <biz-link
                class="navbar-item"
                :href="route('homepage')"
            >
                <img
                    :src="logoUrl"
                    alt=""
                    height="28"
                >
            </biz-link>

            <div
                class="navbar-burger burger"
                data-target="navbarExampleTransparentExample"
            >
                <span />
                <span />
                <span />
            </div>
        </div>

        <div
            id="navbarExampleTransparentExample"
            class="navbar-menu"
        >
            <div class="navbar-start">
                <template
                    v-for="(menu, index) in menus"
                    :key="index"
                >
                    <template v-if="menu.children.length > 0">
                        <div class="navbar-item has-dropdown is-hoverable">
                            <a
                                class="navbar-link"
                            >
                                {{ menu.title }}
                            </a>
                            <div class="navbar-dropdown">
                                <template
                                    v-for="childMenu in menu.children"
                                    :key="childMenu.id"
                                >
                                    <biz-navbar-item
                                        :is-internal-link="childMenu.isInternalLink"
                                        :url="childMenu.link"
                                    >
                                        {{ childMenu.title }}
                                    </biz-navbar-item>
                                </template>
                            </div>
                        </div>
                    </template>
                    <template v-else>
                        <biz-navbar-item
                            :is-internal-link="menu.isInternalLink"
                            :url="menu.link"
                        >
                            {{ menu.title }}
                        </biz-navbar-item>
                    </template>
                </template>
            </div>

            <div class="navbar-end">
                <div class="navbar-item has-dropdown is-hoverable">
                    <span class="navbar-link">{{ currentLanguage.toUpperCase() }}</span>
                    <div class="navbar-dropdown is-boxed">
                        <a
                            v-for="language in availableLanguages"
                            :key="language.id"
                            class="navbar-item"
                            :href="route('language.change', [language.id])"
                        >
                            {{ language.id.toUpperCase() }}
                        </a>
                    </div>
                </div>
                <biz-link
                    :href="route('login')"
                    class="navbar-item pr-5"
                >
                    Login
                </biz-link>
            </div>
        </div>
    </nav>
</template>

<script>
    import BizLink from '@/Biz/Link';
    import BizNavbarItem from '@/Biz/NavbarItem';

    export default {
        name: 'NavbarLayoutOne',

        components: {
            BizLink,
            BizNavbarItem,
        },

        props: {
            availableLanguages: {type: Array, default: []},
            currentLanguage: {type: String, default: "en"},
            logoUrl: {type: String, required: true},
            menus: {type: Array, default: []},
        },
    }
</script>