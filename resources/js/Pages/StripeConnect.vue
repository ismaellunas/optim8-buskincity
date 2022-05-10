<template>
    <layout>
        <template #header>
            <h1 class="title is-2">
                {{ title }}
            </h1>
        </template>

        <div class="columns is-multiline">
            <div
                v-if="!hasConnectedAccount"
                class="column is-12"
            >
                <div class="box is-shadowless">
                    <biz-form-select
                        v-model="createStripeForm.country"
                        label="Country"
                        :message="error('country')"
                    >
                        <option
                            v-for="option in countryOptions"
                            :key="option.id"
                            :value="option.id"
                        >
                            {{ option.value }}
                        </option>
                    </biz-form-select>

                    <div class="control">
                        <biz-button
                            class="is-medium is-primary"
                            @click="createConnectedAccount"
                        >
                            <span class="has-text-weight-bold">Create Connected Account</span>
                        </biz-button>
                    </div>
                </div>
            </div>

            <div
                v-else
                class="column is-12"
            >
                <div class="box is-shadowless">
                    <div class="control">
                        <biz-button
                            v-if="hasPassedOnboarding"
                            class="is-medium is-primary"
                            type="button"
                            @click="redirectToStripe"
                        >
                            <span class="has-text-weight-bold">Login To Stripe</span>
                        </biz-button>

                        <biz-button
                            v-else
                            class="is-medium is-primary"
                            type="button"
                            @click="redirectToOnboardingAccount"
                        >
                            <span class="has-text-weight-bold">Continue Onboarding Process</span>
                        </biz-button>
                    </div>
                </div>
            </div>

            <div
                v-if="hasConnectedAccount"
                class="column is-12"
            >
                <div class="box is-shadowless">
                    <h2 class="title is-3">
                        Setting
                    </h2>

                    <form @submit.prevent="submit">
                        <biz-form-select
                            v-model="settingForm.is_enabled"
                            label="Is Enabled ?"
                            :message="error('is_enabled')"
                        >
                            <option :value="true">
                                Enabled
                            </option>
                            <option :value="false">
                                Disabled
                            </option>
                        </biz-form-select>

                        <div class="field is-grouped is-grouped-left">
                            <div class="control">
                                <biz-button class="is-medium is-primary">
                                    <span class="has-text-weight-bold">Update</span>
                                </biz-button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div
                v-if="hasConnectedAccount"
                class="column is-12"
            >
                <div class="box is-shadowless">
                    <h2 class="title is-3">
                        Balances
                    </h2>

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
                                <td>{{ available.amount / 100 }}</td>
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
                                <td>{{ pending.amount / 100 }}</td>
                            </tr>
                        </biz-table>
                    </div>
                </div>
            </div>

            <div
                v-if="hasConnectedAccount"
                class="column is-12"
            >
                <div class="box is-shadowless">
                    <h1 class="title">
                        Transactions
                    </h1>

                    <div
                        v-if="balanceTransactions.data.length > 0"
                        class="mb-4"
                    >
                        <biz-table class="is-bordered is-hoverable is-fullwidth">
                            <tr>
                                <th>Currency</th>
                                <th>Amount</th>
                                <th>Date</th>
                            </tr>
                            <tr
                                v-for="transaction, index in balanceTransactions.data"
                                :key="index"
                            >
                                <td>{{ transaction.currency }}</td>
                                <td>{{ transaction.amount }}</td>
                                <td>{{ transaction.created }}</td>
                            </tr>
                        </biz-table>

                        <nav
                            class="pagination"
                            role="navigation"
                            aria-label="pagination"
                        >
                            <button
                                class="pagination-previous button"
                                :disabled="isPreviousDisabled"
                                @click="paginationVisit(balanceTransactions.previous_url)"
                            >
                                Previous
                            </button>
                            <button
                                class="pagination-next button"
                                :disabled="isNextDisabled"
                                @click="paginationVisit(balanceTransactions.next_url)"
                            >
                                Next page
                            </button>
                        </nav>
                    </div>

                    <div
                        v-else
                        class="mb-4"
                    >
                        <p>No transactions</p>
                    </div>
                </div>
            </div>
        </div>
    </layout>
</template>

<script>
    import Layout from '@/Layouts/User';
    import BizButton from '@/Biz/Button';
    import BizFormSelect from '@/Biz/Form/Select';
    import BizTable from '@/Biz/Table';
    import MixinHasLoader from '@/Mixins/HasLoader';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import { confirm as confirmAlert, oops as oopsAlert, success as successAlert } from '@/Libs/alert';
    import { ref } from 'vue';
    import { merge } from 'lodash';
    import { useForm } from '@inertiajs/inertia-vue3';
    import { isBlank } from '@/Libs/utils';

    export default {
        components: {
            Layout,
            BizButton,
            BizFormSelect,
            BizTable,
        },

        mixins: [
            MixinHasLoader,
            MixinHasPageErrors,
        ],

        props: {
            hasConnectedAccount: {
                type: Boolean,
                required: true,
            },
            hasPassedOnboarding: {
                type: Boolean,
                required: true,
            },
            errors: {
                type: Object,
                default: () => {},
            },
            title: {
                type: String,
                default: 'Stripe Connect'
            },
            balance: {
                type: Object,
                default: () => {},
            },
            balanceTransactions: {
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
            isEnabled: {
                type: Boolean,
                default: false,
            },
            pageQueryParams: {
                type: Object,
                default: null,
            },
        },

        setup(props) {
            const createStripeForm = {
                country: props.defaultCountry,
            };

            const settingForm = {
                is_enabled: props.isEnabled,
            };

            return {
                createStripeForm: useForm(createStripeForm),
                loader: ref(null),
                settingForm: useForm(settingForm),
                queryParams: ref(merge({}, props.pageQueryParams)),
            }
        },

        data() {
            return {
                baseUrl: route('payment-management.stripe.show'),
            };
        },

        computed: {
            isPreviousDisabled() {
                if (this.queryParams['startingAfter']) {
                    return false;
                }

                if (
                    this.queryParams['endingBefore']
                    && this.balanceTransactions.has_more == true
                ) {
                    return false;
                }

                return true;
            },

            isNextDisabled() {
                if (this.queryParams['endingBefore']) {
                    return false;
                }

                if (
                    this.queryParams['startingAfter']
                    && this.balanceTransactions.has_more == true
                ) {
                    return false;
                }

                if (
                    isBlank(this.queryParams)
                    && this.balanceTransactions.has_more == true
                ) {
                    return false;
                }

                return true;
            },
        },

        methods: {
            createConnectedAccount() {
                const self = this;
                const url = route('payment-management.stripe.create-connected-account');

                confirmAlert(
                    "Please double-check your country!",
                    "You will not be able to change your country in the future.",
                    "Continue"
                )
                    .then((result) => {
                        if (result.isConfirmed) {
                            self.createStripeForm.post(url, {
                                replace: true,
                                preserveState: true,
                                onStart: () => self.onStartLoadingOverlay(),
                                onFinish: () => self.onEndLoadingOverlay(),
                                onError: errors => {
                                    oopsAlert();
                                },
                            });
                        }
                    });
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


            async redirectToOnboardingAccount() {
                this.onStartLoadingOverlay();

                try {
                    const response = await axios.get(
                        route('payment-management.stripe.account-link')
                    );

                    window.open(response.data.url);

                } catch (error) {
                    console.error(error);

                    oopsAlert();
                }

                this.onEndLoadingOverlay();
            },

            submit() {
                this.settingForm.post(route('payment-management.stripe.update-setting'), {
                    onStart: () => {
                        this.loader = this.$loading.show();
                    },
                    onSuccess: (page) => {
                        successAlert(page.props.flash.message);
                    },
                    onError(errors) {
                        oopsAlert();
                    },
                    onFinish: () => {
                        this.loader.hide();
                    }
                });
            },

            paginationVisit(url) {
                this.$inertia.get(url, {}, {
                    preserveScroll: true,
                    onStart: () => {
                        this.loader = this.$loading.show();
                    },
                    onFinish: () => {
                        this.loader.hide();
                    }
                });
            }
        },
    };
</script>
