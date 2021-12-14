<template>
    <div id="main-container-wrapper">
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

    export default {
        name: 'LayoutApp',

        components: {
            SdbNavbarMenu,
        },
    };
</script>
