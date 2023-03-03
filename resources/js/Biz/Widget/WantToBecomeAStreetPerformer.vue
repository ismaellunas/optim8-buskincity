<template>
    <div
        class="column"
        :class="wrapperClass"
    >
        <h2 class="title is-4">
            {{ title }}
        </h2>

        <div class="box is-shadowless">
            <p>Coming soon! We are brewing a new feature that will allow you to have access to educational materials to help you become a successful screet performer. Have questions?</p>

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
        name: 'WantToBecomeAStreetPerformerWidget',

        components: {
            BizIcon,
        },

        props: {
            data: {type: Object, required: true},
            title: {type: String, default: ""},
            order: {type: Number, required: true},
            columns: {type: [Number, String], default: 6},
        },

        setup() {
            return {
                iconPreview,
            };
        },

        computed: {
            wrapperClass() {
                return [
                    'is-'+this.columns,
                ];
            },
        },
    };
</script>
