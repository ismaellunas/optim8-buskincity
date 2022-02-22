<template>
    <app-layout>
        <template #header>
            {{ title }}
        </template>

        <biz-error-notifications :errors="$page.props.errors" />

        <div class="box">
            <div class="control">
                <biz-button
                    v-if="!hasConnectedAccount"
                    class="is-link"
                    @click="createConnectedAccount"
                >
                    Create Connected Account
                </biz-button>

                <biz-button
                    v-else
                    class="is-link"
                    @click="redirectToStripe"
                >
                    Login To Stripe
                </biz-button>
            </div>
        </div>

        <section
            v-if="hasConnectedAccount"
            class="section box"
        >
            <h1 class="title">
                Balances
            </h1>

            <div
                v-if="balance.available"
                class="mb-4"
            >
                <h2 class="subtitle">
                    Available
                </h2>

                <biz-table class="is-bordered is-hoverable is-fullwidth">
                    <tr>
                        <th>Currency</th>
                        <th>Amount</th>
                    </tr>
                    <tr
                        v-for="available, index in balance.available"
                        :key="index"
                    >
                        <td>{{ available.currency.toUpperCase() }}</td>
                        <td>{{ available.amount }}</td>
                    </tr>
                </biz-table>
            </div>

            <div
                v-if="balance.pending"
                class="mb-4"
            >
                <h2 class="subtitle">
                    Pending
                </h2>

                <biz-table class="is-bordered is-hoverable is-fullwidth">
                    <tr>
                        <th>Currency</th>
                        <th>Amount</th>
                    </tr>
                    <tr
                        v-for="pending, index in balance.pending"
                        :key="index"
                    >
                        <td>{{ pending.currency.toUpperCase() }}</td>
                        <td>{{ pending.amount }}</td>
                    </tr>
                </biz-table>
            </div>
        </section>
    </app-layout>
</template>

<script>
    import AppLayout from './../Layouts/AppLayout';
    import BizButton from '@/Biz/Button';
    import BizTable from '@/Biz/Table';
    import MixinHasLoader from '@/Mixins/HasLoader';
    import BizErrorNotifications from '@/Biz/ErrorNotifications';
    import { oops as oopsAlert } from '@/Libs/alert';

    export default {
        components: {
            AppLayout,
            BizButton,
            BizTable,
            BizErrorNotifications,
        },

        mixins: [
            MixinHasLoader,
        ],

        props: {
            hasConnectedAccount: {
                type: Boolean,
                default: false,
            },
            errors: {
                type: Object,
                default: () => {},
            },
            title: {
                type: String,
                default: 'Stripe'
            },
            balance: {
                type: Object,
                default: () => {},
            },
        },

        methods: {
            createConnectedAccount() {
                const self = this;

                this.$inertia.post(
                    route('payment-management.stripe.create-connected-account'),
                    {},
                    {
                        replace: true,
                        preserveState: true,
                        onStart: () => self.onStartLoadingOverlay(),
                    }
                );
            },

            async redirectToStripe() {
                this.onStartLoadingOverlay();

                try {
                    const response = await axios.get(
                        route('payment-management.stripe.redirect-to-stripe')
                    );

                    window.open(response.data.url);

                } catch (error) {
                    oopsAlert();
                }

                this.onEndLoadingOverlay();
            },
        },
    };
</script>
