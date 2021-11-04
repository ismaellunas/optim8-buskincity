<template>
    <div>
        <Head>
            <link
                rel="stylesheet"
                :href="$page.props.css.frontend.app"
            >
        </Head>

        <component
            :is="navbarLayoutName"
            :available-languages="availableLanguages"
            :current-language="currentLanguage"
            :logo-url="logoUrl"
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
        }
    };
</script>
