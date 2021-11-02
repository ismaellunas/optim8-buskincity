<template>
    <div>
        <Head>
            <link
                rel="stylesheet"
                :href="$page.props.css.frontend.app"
            >
            <link
                v-if="$page.props.css.frontend.additional_css"
                rel="stylesheet"
                :href="$page.props.css.frontend.additional_css"
            >
            <link
                v-if="$page.props.css.frontend.tracking_code_inside_head"
                rel="stylesheet"
                :href="$page.props.css.frontend.tracking_code_inside_head"
            >
        </Head>

        <link
            v-if="$page.props.css.frontend.tracking_code_after_body"
            rel="stylesheet"
            :href="$page.props.css.frontend.tracking_code_after_body"
        >

        <nav class="navbar is-primary">
            <div class="navbar-brand">
                <sdb-link
                    class="navbar-item"
                    href="/"
                >
                    <img
                        src="https://dummyimage.com/48x28/e5e5e5/000000.png&text=B+752"
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
                    <sdb-link
                        href="/"
                        class="navbar-item"
                    >
                        Home
                    </sdb-link>

                    <sdb-link
                        v-for="menuItem in menus.navbar"
                        :key="menuItem.title"
                        :href="menuItem.link"
                        class="navbar-item"
                    >
                        {{ menuItem.title }}
                    </sdb-link>
                </div>

                <div class="navbar-end">
                    <div class="navbar-item">
                        <div class="field">
                            <div class="control">
                                <input
                                    class="input"
                                    type="text"
                                    placeholder="Search . . ."
                                >
                            </div>
                        </div>
                    </div>
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

        <slot />

        <link
            v-if="$page.props.css.frontend.tracking_code_before_body"
            rel="stylesheet"
            :href="$page.props.css.frontend.tracking_code_before_body"
        >
    </div>
</template>

<script>
    import SdbLink from '@/Sdb/Link';
    import { Head, usePage } from '@inertiajs/inertia-vue3';

    export default {
        name: 'LayoutPage',

        components: {
            Head,
            SdbLink,
        },

        props: [
            'currentLanguage',
            'languageOptions',
            'menus',
            'user',
        ],

        data() {
            return {
                'tracking_code_inside_head': ".button {color: red}",
                'tracking_code_after_body': "",
                'tracking_code_before_body': "",
            };
        },

        computed: {
            availableLanguages() {
                return this
                    .languageOptions
                    .filter(option => option.id !== this.currentLanguage);
            },
        },

        mounted() {
            let recaptchaScript = document.createElement('script');
            recaptchaScript.setAttribute('src', usePage().props.value.js.frontend.additional_javascript);
            document.body.appendChild(recaptchaScript);
        },
    };
</script>
