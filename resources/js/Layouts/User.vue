<template>
    <div
        id="main-container-wrapper"
        class="pb-4 mb-4"
    >
        <HeadTag :title="title ?? titleChild" />

        <frontend-navbar-menu />

        <div class="section is-small">
            <div class="container">
                <h1 class="title is-2">
                    {{ title ?? titleChild }}
                </h1>

                <div class="columns is-multiline is-mobile">
                    <div class="column is-6-desktop is-12-tablet is-12-mobile">
                        <p
                            v-if="description || descriptionChild"
                        >
                            {{ description ?? descriptionChild }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="section is-small has-background-light theme-font">
            <div class="container">
                <slot />
            </div>
        </div>

        <frontend-footer-menu />
    </div>
</template>

<script>
    import FrontendFooterMenu from '@/Frontend/FooterMenu.vue';
    import FrontendNavbarMenu from '@/Frontend/NavbarMenu.vue';
    import { Head as HeadTag } from '@inertiajs/vue3';
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
