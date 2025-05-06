<template>
    <div
        class="column is-6-desktop is-6-tablet is-12-mobile"
    >
        <h2 class="title is-4">
            {{ title }}
        </h2>

        <div class="box is-shadowless">
            <p>Coming soon! We are brewing a new feature that will allow you to see nearby city events and performances. Have questions?</p>

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
        name: 'UpcomingEventsWidget',

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
    };
</script>
