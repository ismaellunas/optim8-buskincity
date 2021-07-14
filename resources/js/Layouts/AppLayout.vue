<template>
    <div id="main-container-wrapper">

        <sdb-navbar-menu />

        <!-- <jet-banner /> -->

        <section class="hero is-small is-primary mb-4">
            <div class="hero-body">
                <div class="container">
                    <p class="title">
                        <slot name="header"></slot>
                    </p>
                </div>
            </div>
        </section>

        <div id="main-container" class="container mb-2">
            <slot></slot>
        </div>
    </div>
</template>

<script>
    //import JetBanner from '@/Jetstream/Banner'
    import SdbNavbarMenu from '@/Sdb/NavbarMenu';

    export default {
        components: {
            //JetBanner,
            SdbNavbarMenu,
        },
    }
</script>
