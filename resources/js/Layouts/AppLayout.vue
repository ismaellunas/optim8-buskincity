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

            <biz-flash-expired :flash="$page.props.flash" />

            <slot />
        </div>
    </div>
</template>

<script>
    import BizNavbarMenu from '@/Biz/NavbarMenu';
    import BizFlashExpired from '@/Biz/FlashExpired';

    export default {
        name: 'LayoutApp',

        components: {
            BizNavbarMenu,
            BizFlashExpired
        },
    };
</script>
