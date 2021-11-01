<template>
    <Head>
        <link
            v-for="css in $page.props.css.frontend"
            rel="stylesheet"
            :href="css"
        >
    </Head>

    <nav class="navbar is-primary">
        <div class="navbar-brand">
            <sdb-link class="navbar-item" href="/">
                <img :src="logoUrl" alt="" height="28">
            </sdb-link>
            <div class="navbar-burger burger" data-target="navbarExampleTransparentExample">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>

        <div id="navbarExampleTransparentExample" class="navbar-menu">
            <div class="navbar-start">
                <sdb-link href="/" class="navbar-item">Home</sdb-link>
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
                                >
                                    <sdb-link
                                        class="navbar-item"
                                        :href="childMenu.link"
                                    >
                                        {{ childMenu.title }}
                                    </sdb-link>
                                </template>
                            </div>
                        </div>
                    </template>
                    <template v-else>
                        <sdb-link
                            class="navbar-item"
                            :href="menu.link"
                        >
                            {{ menu.title }}
                        </sdb-link>
                    </template>
                </template>
            </div>

            <div class="navbar-end">
                <div class="navbar-item">
                    <div class="field">
                        <div class="control">
                            <input class="input" type="text" placeholder="Search . . .">
                        </div>
                    </div>
                </div>
                <div class="navbar-item has-dropdown is-hoverable">
                    <span class="navbar-link">{{ currentLanguage.toUpperCase() }}</span>
                    <div class="navbar-dropdown is-boxed">
                        <sdb-link
                            v-for="language in availableLanguages"
                            :href="route('language.change', [language.id])" class="navbar-item">
                            {{ language.id.toUpperCase() }}
                        </sdb-link>
                    </div>
                </div>
                <sdb-link :href="route('login')" class="navbar-item pr-5">Login</sdb-link>
            </div>
        </div>
    </nav>

    <slot></slot>

</template>

<script>
    import SdbLink from '@/Sdb/Link';
    import { Head, usePage } from '@inertiajs/inertia-vue3';

    export default {
        name: 'LayoutPage',

        props: [
            'currentLanguage',
            'languageOptions',
            'menus',
            'user',
            //'errorBags',
            //'flash',
            //'jetstream',
            //'socialstream',
        ],

        components: {
            Head,
            SdbLink,
        },

        setup() {
            return {
                menuSettings: usePage().props.value.menuSettings,
            };
        },

        computed: {
            availableLanguages() {
                return this
                    .languageOptions
                    .filter(option => option.id !== this.currentLanguage);
            },

            logoUrl() {
                return this.menuSettings.header_logo_url.value ?? "https://dummyimage.com/48x28/e5e5e5/000000.png&text=B+752";
            },
        }
    }
</script>
