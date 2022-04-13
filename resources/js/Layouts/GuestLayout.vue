<template>
    <div id="main-container-wrapper">
        <biz-navbar-menu />

        <biz-hero
            class="is-primary mb-4"
            size="small"
        >
            <div class="container">
                <p class="title">
                    <slot name="header" />
                </p>
            </div>
        </biz-hero>

        <div
            id="main-container"
            class="container mb-2"
        >
            <slot />
        </div>
    </div>
</template>

<script>
    import BizHero from '@/Biz/Hero';
    import BizNavbarMenu from '@/Biz/NavbarMenu';

    export default {
        name: 'LayoutGuest',

        components: {
            BizHero,
            BizNavbarMenu,
        },
    };
</script>
