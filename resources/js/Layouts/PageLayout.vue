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

        <component
            :is="navbarLayoutName"
            :available-languages="availableLanguages"
            :current-language="currentLanguage"
            :logo-url="getLogoUrl"
            :menus="menus[currentLanguage]"
        />

        <slot />

        <link
            v-if="$page.props.css.frontend.tracking_code_before_body"
            rel="stylesheet"
            :href="$page.props.css.frontend.tracking_code_before_body"
        >
    </div>
</template>

<script>
    import SdbNavbarLayoutOne from '@/Sdb/Navbar/NavbarLayoutOne';
    import SdbNavbarLayoutTwo from '@/Sdb/Navbar/NavbarLayoutTwo';
    import SdbNavbarLayoutThree from '@/Sdb/Navbar/NavbarLayoutThree';
    import { Head, usePage } from '@inertiajs/inertia-vue3';
    import { isBlank } from '@/Libs/utils';

    export default {
        name: 'LayoutPage',

        components: {
            Head,
            SdbNavbarLayoutOne,
            SdbNavbarLayoutTwo,
            SdbNavbarLayoutThree,
        },

        props: [
            'currentLanguage',
            'languageOptions',
            'menus',
            'user',
        ],

        setup() {
            return {
                logoUrl: usePage().props.value.logoUrl,
                menuSettings: usePage().props.value.menuSettings,
            };
        },

        data() {
            return {
                'tracking_code_inside_head': ".button {color: red}",
                'tracking_code_after_body': "",
                'tracking_code_before_body': "",
            };
        },

        computed: {
            getLogoUrl() {
                return !isBlank(this.logoUrl) ? this.logoUrl : "https://dummyimage.com/48x28/e5e5e5/000000.png&text=B+752";
            },

            availableLanguages() {
                return this
                    .languageOptions
                    .filter(option => option.id !== this.currentLanguage);
            },

            navbarLayoutName() {
                const layout = parseInt(this.menuSettings.header_layout.value);
                switch (layout) {
                case 1:
                    return "SdbNavbarLayoutOne";
                    break;

                case 2:
                    return "SdbNavbarLayoutTwo";
                    break;

                case 3:
                    return "SdbNavbarLayoutThree";
                    break;

                default:
                    return "SdbNavbarLayoutOne";
                    break;
                }
            },
        },

        mounted() {
            if (usePage().props.value.js.frontend.additional_javascript) {
                let script = document.createElement('script');
                script.setAttribute('src', usePage().props.value.js.frontend.additional_javascript);
                document.body.appendChild(script);
            }
        },
    };
</script>
