<template>
    <div>
        <div class="columns is-multiline">
            <biz-widget-stripe-connect
                title="Stripe Connected"
                :data="stripeConnectData"
                :order="0"
            >
                <template #description>
                    <p>Create and connect an account with Stripe, this will allow you to receive payments through BuskinCity. If you already have a Stripe account you need to create a new one using the form below:</p>
                </template>
            </biz-widget-stripe-connect>
        </div>
    </div>
</template>

<script>
    import BizWidgetStripeConnect from '@/Biz/Widget/StripeConnect.vue';
    import Layout from '@/Layouts/User.vue';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';

    export default {
        name: 'WidgetPayments',

        components: {
            BizWidgetStripeConnect,
        },

        mixins: [
            MixinHasPageErrors,
        ],

        layout: Layout,

        props: {
            hasConnectedAccount: {
                type: Boolean,
                required: true,
            },
            errors: {
                type: Object,
                default: () => {},
            },
            countryOptions: {
                type: Object,
                default: () => {},
            },
            defaultCountry: {
                type: String,
                default: null,
            },
        },

        data() {
            return {
                stripeConnectData: {
                    countryOptions: this.countryOptions,
                    defaultCountry: this.defaultCountry,
                    hasConnectedAccount: this.hasConnectedAccount,
                },
            };
        },
    };
</script>
