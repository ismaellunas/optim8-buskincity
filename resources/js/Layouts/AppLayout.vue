<template>
    <div id="main-container-wrapper">
        <HeadTag :title="title ?? titleChild" />

        <biz-navbar-menu />

        <section class="hero is-small is-primary mb-4">
            <div class="hero-body">
                <div class="container">
                    <h3 class="title is-size-3">
                        {{ title ?? titleChild }}
                    </h3>
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
    import { Head as HeadTag } from '@inertiajs/inertia-vue3';
    import { head } from 'lodash';

    export default {
        name: 'AppLayout',

        components: {
            BizNavbarMenu,
            BizFlashExpired,
            HeadTag,
        },

        props: {
            title: { type: String, default: null },
        },

        computed: {
            titleChild() {
                return head(this.$slots.default())?.type.props.title?.default ?? '';
            },
        },
    };
</script>
