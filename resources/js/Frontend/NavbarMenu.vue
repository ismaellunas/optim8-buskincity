<template>
    <component :is="navbarComponentName" />
</template>

<script>
    import { usePage } from '@inertiajs/vue3';
    import { defineAsyncComponent, computed } from 'vue';

    export default {
        name: "FrontendNavbarMenu",

        components: {
            FrontendNavbarLayoutOne: defineAsyncComponent(() =>
                import('./NavbarLayoutOne.vue')
            ),
            FrontendNavbarLayoutTwo: defineAsyncComponent(() =>
                import('./NavbarLayoutTwo.vue')
            ),
            FrontendNavbarLayoutThree: defineAsyncComponent(() =>
                import('./NavbarLayoutThree.vue')
            ),
        },

        setup() {
            return {
                headerLayout: computed(() => usePage().props.headerLayout),
            };
        },

        computed: {
            navbarComponentName() {
                switch (this.headerLayout) {
                case 1:
                    return 'frontend-navbar-layout-one';
                    break;

                case 2:
                    return 'frontend-navbar-layout-two';
                    break;

                case 3:
                    return 'frontend-navbar-layout-three';
                    break;

                default:
                    return 'frontend-navbar-layout-one';
                    break;
                }
            },
        },
    }
</script>