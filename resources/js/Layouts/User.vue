<template>
    <div
        id="main-container-wrapper"
        class="pb-4 mb-4"
    >
        <biz-navbar-menu class="is-info" />

        <div class="section is-small">
            <div class="container">
                <div class="columns">
                    <div class="column is-6">
                        <slot name="header" />

                        <slot name="subheader" />
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
    import BizNavbarMenu from '@/Biz/NavbarMenu';

    export default {
        name: 'LayoutUser',

        components: {
            BizNavbarMenu,
        },
    };
</script>
