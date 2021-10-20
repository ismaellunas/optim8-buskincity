<template>
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

    export default {
        components: {
            SdbNavbarMenu,
        },
    }
</script>
