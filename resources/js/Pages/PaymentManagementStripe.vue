<template>
    <app-layout>
        <template #header>
            {{ title }}
        </template>

        <div
            v-if="!hasConnectedAccount"
            class="box"
        >
            <biz-form-select
                v-model="form.country"
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
                    class="is-link"
                    @click="createConnectedAccount"
                >
                    Create Connected Account
                </biz-button>
            </div>
        </div>

        <div
            v-else
            class="box"
        >
            <div class="control">
                <biz-button
                    v-if="hasPassedOnboarding"
                    class="is-link"
                    @click="redirectToStripe"
                >
                    Login To Stripe
                </biz-button>

                <biz-button
                    v-else
                    class="is-link"
                    @click="redirectToOnboardingAccount"
                >
                    Continue Onboarding Process
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
        </section>
    </app-layout>
</template>

<script>
    import AppLayout from './../Layouts/AppLayout';
    import BizButton from '@/Biz/Button';
    import BizTable from '@/Biz/Table';
    import MixinHasLoader from '@/Mixins/HasLoader';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import BizFormSelect from '@/Biz/Form/Select';
    import { confirm as confirmAlert, oops as oopsAlert } from '@/Libs/alert';
    import { useForm } from '@inertiajs/inertia-vue3';

    export default {
        components: {
            AppLayout,
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
                default: 'Stripe'
            },
            balance: {
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

        setup(props) {
            const form = {
                country: props.defaultCountry,
            };

            return {
                form: useForm(form),
            }
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
                            self.form.post(url, {
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
        },
    };
</script>
