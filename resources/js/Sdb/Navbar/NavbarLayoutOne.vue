<template>
    <nav class="navbar is-primary">
        <div class="navbar-brand">
            <sdb-link
                class="navbar-item"
                href="/"
            >
                <img
                    :src="logoUrl"
                    alt=""
                    height="28"
                >
            </sdb-link>

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
                                    <sdb-navbar-item
                                        :is-internal-link="childMenu.isInternalLink"
                                        :url="childMenu.link"
                                    >
                                        {{ childMenu.title }}
                                    </sdb-navbar-item>
                                </template>
                            </div>
                        </div>
                    </template>
                    <template v-else>
                        <sdb-navbar-item
                            :is-internal-link="menu.isInternalLink"
                            :url="menu.link"
                        >
                            {{ menu.title }}
                        </sdb-navbar-item>
                    </template>
                </template>
            </div>

            <div class="navbar-end">
                <div class="navbar-item has-dropdown is-hoverable">
                    <span class="navbar-link">{{ currentLanguage.toUpperCase() }}</span>
                    <div class="navbar-dropdown is-boxed">
                        <sdb-link
                            v-for="language in availableLanguages"
                            :key="language.id"
                            class="navbar-item"
                            :href="route('language.change', [language.id])"
                        >
                            {{ language.id.toUpperCase() }}
                        </sdb-link>
                    </div>
                </div>
                <sdb-link
                    :href="route('login')"
                    class="navbar-item pr-5"
                >
                    Login
                </sdb-link>
            </div>
        </div>
    </nav>
</template>

<script>
    import SdbLink from '@/Sdb/Link';
    import SdbNavbarItem from '@/Sdb/NavbarItem';

    export default {
        name: 'NavbarLayoutOne',

        components: {
            SdbLink,
            SdbNavbarItem,
        },

        props: {
            availableLanguages: {type: Array, default: []},
            currentLanguage: {type: String, default: "en"},
            logoUrl: {type: String, required: true},
            menus: {type: Array, default: []},
        },
    }
</script>