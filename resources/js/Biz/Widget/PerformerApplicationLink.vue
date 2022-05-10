<template>
    <div
        class="column"
        :class="wrapperClass"
    >
        <h2 class="title is-4">
            {{ title }}
        </h2>

        <div class="box is-shadowless">
            <template v-if="data">
                <p>Apply to become a performer on BuskinCity. It will allow you to have your page, receive donations, private bookings, and build a bigger audience.</p>
                <div class="buttons are-small mt-5">
                    <biz-button-link
                        class="is-primary"
                        :href="route(data.baseRouteName+'.create')"
                    >
                        <span class="icon is-small">
                            <i class="fa-solid fa-arrow-up-right-from-square" />
                        </span>
                        <span class="has-text-weight-bold">Apply Now</span>
                    </biz-button-link>
                </div>
            </template>

            <div
                v-else
                class="notification is-info is-light"
            >
                <p>Our team is reviewing your application. Usually takes from 3-5 days to get approved. We will contact you by email with our decision. <strong>Have questions or comments?</strong></p>

                <div class="buttons are-small mt-5">
                    <biz-button-link class="is-info">
                        <span class="icon is-small">
                            <i class="fa-solid fa-arrow-up-right-from-square" />
                        </span>
                        <span class="has-text-weight-bold">Get in Touch</span>
                    </biz-button-link>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import BizButtonLink from '@/Biz/ButtonLink';

    export default {
        name: 'PerformerApplicationLink',

        components: {
            BizButtonLink,
        },

        props: {
            columns: { type: [Number, String], default: 6 },
            data: { type: Object, default: null },
            order: {type: Number, required: true},
            title: { type: String, default: "" },
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