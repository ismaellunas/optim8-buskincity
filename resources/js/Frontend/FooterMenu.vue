<template>
    <footer>
        <section class="section">
            <div class="container">
                <div class="columns is-multiline is-mobile">
                    <div class="column is-3-desktop is-3-tablet is-12-mobile">
                        <img
                            :src="appLogoImageUrl"
                            class="mb-2"
                            style="max-width:160px"
                        >

                        <div class="buttons">
                            <a
                                v-for="(socialMedia, index) in socialMediaMenus"
                                :key="index"
                                class="button is-ghost has-text-black"
                                :target="socialMedia.target"
                                :href="socialMedia.url"
                            >
                                <biz-icon :icon="socialMedia.icon" />
                            </a>
                        </div>
                    </div>
                    <div class="column is-9-desktop is-9-tablet is-12-mobile">
                        <div class="columns is-multiline is-mobile">
                            <template
                                v-for="(menu, index) in navMenus"
                                :key="index"
                            >
                                <div class="column is-4-desktop is-6-tablet is-12-mobile">
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
                </div>
                <div class="columns is-multiline is-mobile">
                    <div class="column is-12-desktop is-12-tablet is-12-mobile">
                        <p class="is-size-7 has-text-centered mt-5">
                            This site is protected by reCAPTCHA and the Google
                            <a href="https://policies.google.com/privacy">Privacy Policy</a> and
                            <a href="https://policies.google.com/terms">Terms of Service</a> apply.<br>
                            © Copyright 2022, {{ appName }}
                        </p>
                    </div>
                </div>
            </div>
        </section>
    </footer>
</template>

<script>
    import BizIcon from '@/Biz/Icon.vue';
    import { computed } from 'vue';
    import { usePage } from '@inertiajs/vue3';
    import { appName } from '@/Libs/defaults';

    export default {
        name: 'FrontendFooterMenu',

        components: {
            BizIcon,
        },

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

            socialMediaMenus() {
                return usePage().props.socialMediaMenus;
            },
        },
    }
</script>