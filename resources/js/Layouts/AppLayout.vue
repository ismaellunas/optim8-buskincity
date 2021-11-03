<template>
    <div id="main-container-wrapper">
        <Head>
            <link
                rel="stylesheet"
                :href="$page.props.css.backend.app"
            >
        </Head>

        <sdb-navbar-menu />

        <section class="hero is-small is-primary mb-4">
            <div class="hero-body">
                <div class="container">
                    <p class="title">
                        <slot name="header" />
                    </p>
                </div>
            </div>
        </section>

        <div
            id="main-container"
            class="container mb-2"
        >
            <slot />
        </div>
    </div>
</template>

<script>
    import SdbNavbarMenu from '@/Sdb/NavbarMenu';
    import { Head } from '@inertiajs/inertia-vue3';

    export default {
        name: 'LayoutApp',

        components: {
            Head,
            SdbNavbarMenu,
        },
    };
</script>
