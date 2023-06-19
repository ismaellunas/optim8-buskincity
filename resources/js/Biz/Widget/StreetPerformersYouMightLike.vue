<template>
    <div class="column is-12-desktop is-12-tablet is-12-mobile">
        <h2 class="title is-4">
            {{ title }}
        </h2>
        <div class="box is-shadowless">
            <p>Coming soon! We are brewing a new feature that will allow you to follow your favourite street performers. Have questions?</p>

            <div class="buttons are-small mt-5">
                <a
                    href="#"
                    class="button"
                    target="_blank"
                >
                    <biz-icon
                        class="is-small"
                        :icon="iconPreview"
                    />
                    <span class="has-text-weight-bold">Get in Touch</span>
                </a>
            </div>
        </div>
    </div>
</template>

<script>
    import BizIcon from '@/Biz/Icon.vue';
    import { preview as iconPreview } from '@/Libs/icon-class';

    export default {
        name: 'StreetPerformersYouMightLikeWidget',

        components: {
            BizIcon,
        },

        props: {
            columns: {type: [Number, String], default: 6},
            data: {type: Object, required: true},
            order: {type: Number, required: true},
            title: {type: String, default: ""},
        },

        setup() {
            return {
                iconPreview,
            };
        },
    };
</script>
