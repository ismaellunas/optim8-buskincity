<template>
    <div
        id="main-container-wrapper"
        class="pb-4 mb-4"
    >
        <HeadTag :title="title ?? titleChild" />

        <frontend-navbar-menu />

        <div class="section is-small">
            <div class="container">
                <div class="columns">
                    <div class="column is-6">
                        <h1 class="title is-2">
                            {{ title ?? titleChild }}
                        </h1>

                        <p
                            v-if="description || descriptionChild"
                        >
                            {{ description ?? descriptionChild }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="section is-small has-background-light">
            <div class="container theme-font">
                <slot />
            </div>
        </div>

        <frontend-footer-menu />
    </div>
</template>

<script>
    import FrontendFooterMenu from '@/Frontend/FooterMenu';
    import FrontendNavbarMenu from '@/Frontend/NavbarMenu';
    import { Head as HeadTag } from '@inertiajs/inertia-vue3';
    import { head } from 'lodash';

    export default {
        name: 'LayoutUser',

        components: {
            FrontendFooterMenu,
            FrontendNavbarMenu,
            HeadTag,
        },

        props: {
            description: {type: String, default: null},
            title: { type: String, default: null },
        },

        computed: {
            titleChild() {
                return head(this.$slots.default())?.type.props.title?.default ?? '';
            },

            descriptionChild() {
                return head(this.$slots.default())?.type.props.description?.default ?? '';
            },
        },
    };
</script>
