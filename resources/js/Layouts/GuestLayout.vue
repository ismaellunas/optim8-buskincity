<template>
    <div>
        <div id="main-container-wrapper">
            <biz-navbar-menu />
        </div>

        <slot />
    </div>
</template>

<script>
    import BizNavbarMenu from '@/Biz/NavbarMenu';

    export default {
        name: 'LayoutGuest',

        components: {
            BizNavbarMenu,
        },
    };
</script>
