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
        </Head>

        <component
            :is="navbarLayoutName"
            :available-languages="availableLanguages"
            :current-language="currentLanguage"
            :logo-url="getLogoUrl"
            :menus="menus[currentLanguage]"
        />

        <slot />
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
                headerLayout: usePage().props.value.headerLayout,
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
                const layout = this.headerLayout;
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
