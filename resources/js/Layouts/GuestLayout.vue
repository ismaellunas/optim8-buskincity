<template>
    <Head>
        <link
            v-for="css in $page.props.css.backend"
            rel="stylesheet"
            :href="css"
        >
    </Head>

    <div id="main-container-wrapper">
        <sdb-navbar-menu />
    </div>

        <!--
        <section class="hero is-small is-primary mb-4">
            <div class="hero-body">
                <div class="container">
                    <p class="title">
                        <slot name="header"></slot>
                    </p>
                </div>
            </div>
        </section>
        -->

    <slot></slot>
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
