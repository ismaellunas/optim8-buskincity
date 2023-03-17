<template>
    <footer class="pt-6 pb-6">
        <div class="container">
            <div class="columns is-multiline">
                <div class="column is-3">
                    <img
                        :src="appLogoImageUrl"
                        style="max-width:160px"
                    >
                </div>
                <div class="column is-9">
                    <div class="columns is-multiline">
                        <template
                            v-for="(menu, index) in navMenus"
                            :key="index"
                        >
                            <div class="column is-4">
                                <aside class="menu">
                                    <p class="menu-label">
                                        {{ menu.title }}
                                    </p>
                                    <ul class="menu-list">
                                        <template
                                            v-for="(childMenu, childIndex) in menu.children"
                                            :key="childIndex"
                                        >
                                            <li>
                                                <a
                                                    :href="childMenu.link"
                                                    :target="childMenu.target"
                                                >
                                                    {{ childMenu.title }}
                                                </a>
                                            </li>
                                        </template>
                                    </ul>
                                </aside>
                            </div>
                        </template>
                    </div>
                </div>
                <div class="column is-12">
                    <p class="is-size-7 has-text-centered mt-5">
                        This site is protected by reCAPTCHA and the Google
                        <a href="https://policies.google.com/privacy">Privacy Policy</a> and
                        <a href="https://policies.google.com/terms">Terms of Service</a> apply.<br>
                        © Copyright 2022, {{ appName }}
                    </p>
                </div>
            </div>
        </div>
    </footer>
</template>

<script>
    import { computed } from 'vue';
    import { usePage } from '@inertiajs/vue3';
    import { appName } from '@/Libs/defaults';

    export default {
        name: 'FrontendFooterMenu',

        setup() {
            const appLogoImageUrl = computed(() => usePage().props.appLogoUrl);

            return {
                appName: appName,
                appLogoImageUrl,
            };
        },

        computed: {
            navMenus() {
                return this.$page.props.footerMenus.nav;
            },
        },
    }
</script>