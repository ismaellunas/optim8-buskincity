<template>
    <div
        id="main-container-wrapper"
        class="pb-4 mb-4"
    >
        <biz-navbar-menu class="is-info" />

        <biz-hero
            class="box py-0"
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
        name: 'LayoutUser',

        components: {
            BizHero,
            BizNavbarMenu,
        },
    };
</script>
