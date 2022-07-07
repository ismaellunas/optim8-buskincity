<template>
    <div id="main-container-wrapper">
        <Head :title="title" />

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
    import { Head } from '@inertiajs/inertia-vue3';

    export default {
        name: 'LayoutApp',

        components: {
            BizNavbarMenu,
            BizFlashExpired,
            Head,
        },

        props: {
            title: { type: String, required: true },
        },
    };
</script>
