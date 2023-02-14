<template>
    <div>
        <Head :title="title" />

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
    import LayoutBackendBlank from '@/Layouts/BackendBlank';
    import BizImage from '@/Biz/Image';
    import { computed } from 'vue';
    import { Head, usePage } from '@inertiajs/inertia-vue3';

    export default {
        name: 'LayoutAdmin',

        components: {
            BizImage,
            Head,
        },

        layout: LayoutBackendBlank,

        props: {
            title: { type: String, required: true },
        },

        setup() {
            const appLogoImageUrl = computed(() => usePage().props.value.appLogoUrl);

            return {
                appLogoImageUrl,
            };
        },
    }
</script>
