<template>
    <div
        id="main-container-wrapper"
        class="mb-6 pb-4"
    >
        <HeadTag :title="title ?? titleChild" />

        <biz-navbar-menu />

        <section class="hero is-small is-primary mb-4">
            <div class="hero-body">
                <div class="container">
                    <h3 class="title is-size-3">
                        {{ capitalCase(title ?? titleChild) }}
                    </h3>
                </div>
            </div>
        </section>

        <div
            id="main-container"
            class="container mb-2"
        >
            <div
                v-if="!isBreadcrumbsEmpty"
                class="columns mb-0"
            >
                <div class="column">
                    <biz-breadcrumbs :breadcrumbs="breadcrumbs" />
                </div>

                <div
                    v-if="hasSideBreadcrumbsSlot"
                    class="column"
                >
                    <slot name="sideBreadcrumbs" />
                </div>
            </div>

            <biz-flash-expired :flash="$page.props.flash" />

            <slot />
        </div>
    </div>
</template>

<script>
    import BizBreadcrumbs from '@/Biz/Breadcrumbs.vue';
    import BizFlashExpired from '@/Biz/FlashExpired.vue';
    import BizNavbarMenu from '@/Biz/NavbarMenu.vue';
    import { Head as HeadTag } from '@inertiajs/vue3';
    import { head, isEmpty } from 'lodash';
    import { capitalCase } from 'change-case';

    export default {
        name: 'AppLayout',

        components: {
            BizBreadcrumbs,
            BizNavbarMenu,
            BizFlashExpired,
            HeadTag,
        },

        props: {
            breadcrumbs: { type: Array, default: () => [] },
            title: { type: String, default: null },
        },

        computed: {
            titleChild() {
                return head(this.$slots.default())?.type.props.title?.default ?? '';
            },

            isBreadcrumbsEmpty() {
                return isEmpty(this.breadcrumbs)
            },

            hasSideBreadcrumbsSlot() {
                return !!this.$slots.sideBreadcrumbs;
            },
        },

        methods: {
            capitalCase,
        },
    };
</script>
