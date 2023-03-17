<template>
    <div>
        <inertia-head :title="title" />

        <section class="hero is-fullheight">
            <div class="hero-body">
                <div class="container">
                    <div class="has-text-centered mb-3">
                        <biz-image
                            :src="appLogoImageUrl"
                            :style="{ width: '200px' }"
                            :has-position="true"
                        />
                    </div>

                    <div class="columns is-centered">
                        <div class="column is-two-fifths">
                            <div class="box">
                                <slot />
                            </div>
                        </div>
                    </div>

                    <div class="columns is-centered mt-4">
                        <div class="column is-two-fifths">
                            <slot name="back" />
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>

<script>
    import LayoutBackendBlank from '@/Layouts/BackendBlank.vue';
    import BizImage from '@/Biz/Image.vue';
    import { Head as InertiaHead, usePage } from '@inertiajs/vue3';

    export default {
        name: 'LayoutAdmin',

        components: {
            BizImage,
            InertiaHead,
        },

        layout: LayoutBackendBlank,

        props: {
            title: { type: String, required: true },
        },

        setup() {
            return {
                appLogoImageUrl: usePage().props.appLogoUrl,
            };
        },
    }
</script>
