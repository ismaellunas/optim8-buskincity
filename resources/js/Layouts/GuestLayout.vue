<template>
    <div>
        <div id="main-container-wrapper">
            <sdb-navbar-menu />
        </div>

        <slot />
    </div>
</template>

<script>
    import SdbNavbarMenu from '@/Sdb/NavbarMenu';

    export default {
        name: 'LayoutGuest',

        components: {
            SdbNavbarMenu,
        },
    };
</script>
