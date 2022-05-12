<template>
    <div
        id="main-container-wrapper"
        class="pb-4 mb-4"
    >
        <frontend-navbar-menu />

        <div class="section is-small">
            <div class="container">
                <div class="columns">
                    <div class="column is-6">
                        <slot name="header" />

                        <slot name="headerDescription" />
                    </div>
                </div>
            </div>
        </div>

        <div class="section is-small has-background-light">
            <div class="container">
                <slot />
            </div>
        </div>
    </div>
</template>

<script>
    import BizHero from '@/Biz/Hero';
    import FrontendNavbarMenu from '@/Frontend/NavbarMenu';

    export default {
        name: 'LayoutUser',

        components: {
            FrontendNavbarMenu,
        },
    };
</script>
