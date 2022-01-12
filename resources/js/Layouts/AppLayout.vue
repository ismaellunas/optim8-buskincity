<template>
    <div id="main-container-wrapper">
        <biz-navbar-menu />

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
    import BizNavbarMenu from '@/Biz/NavbarMenu';

    export default {
        name: 'LayoutApp',

        components: {
            BizNavbarMenu,
        },
    };
</script>
