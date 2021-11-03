<template>
    <div>
        <Head>
            <link
                rel="stylesheet"
                :href="$page.props.css.backend.app"
            >
        </Head>

        <div id="main-container-wrapper">
            <sdb-navbar-menu />
        </div>

        <slot />
    </div>
</template>

<script>
    import SdbNavbarMenu from '@/Sdb/NavbarMenu';
    import { Head } from '@inertiajs/inertia-vue3';

    export default {
        name: 'LayoutGuest',

        components: {
            SdbNavbarMenu,
            Head,
        },
    };
</script>
