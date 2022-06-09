<template>
    <layout>
        <Head :title="title" />

        <template #header>
            <h1 class="title is-2">
                {{ title }}
            </h1>
        </template>

        <template #headerDescription>
            <p>BuskinCity allows you to receive payments/donations from your audience, to activate payments you need to apply with our partners.</p>
        </template>

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
    </layout>
</template>

<script>
    import BizWidgetStripeConnect from '@/Biz/Widget/StripeConnect';
    import Layout from '@/Layouts/User';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import { Head } from '@inertiajs/inertia-vue3';

    export default {
        components: {
            BizWidgetStripeConnect,
            Head,
            Layout,
        },

        mixins: [
            MixinHasPageErrors,
        ],

        props: {
            hasConnectedAccount: {
                type: Boolean,
                required: true,
            },
            errors: {
                type: Object,
                default: () => {},
            },
            title: {
                type: String,
                default: 'Payments'
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
