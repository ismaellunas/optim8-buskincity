<template>
    <div>
        <div class="columns is-multiline">
            <biz-widget-stripe-connect
                :title="widgetTitle"
                :data="stripeConnectData"
                :columns="stripeConnectGrid"
            >
                <template #description>
                    <p>
                        {{ i18n.inconnect }}
                    </p>
                </template>
            </biz-widget-stripe-connect>
        </div>
    </div>
</template>

<script>
    import BizWidgetStripeConnect from '@/Biz/Widget/StripeConnect.vue';
    import Layout from '@/Layouts/User.vue';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import { startCase } from 'lodash';

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
            i18n: {
                type: Object,
                default: () => {},
            },
        },

        data() {
            return {
                stripeConnectData: {
                    countryOptions: this.countryOptions,
                    defaultCountry: this.defaultCountry,
                    hasConnectedAccount: this.hasConnectedAccount,
                    i18n: this.i18n,
                },
                stripeConnectGrid: {
                    desktop: 6,
                    tablet: 8,
                    mobile: 12,
                },
            };
        },

        computed: {
            widgetTitle() {
                if (this.hasConnectedAccount) {
                    return startCase(this.i18n.manage_payments);
                }

                return this.i18n.connect_payment;
            },
        },
    };
</script>
