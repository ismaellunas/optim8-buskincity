<template>
    <div class="column is-half">
        <h2 class="title is-4 mt-5">
            {{ title }}
        </h2>
        <div class="box is-shadowless">
            <template v-if="!data.hasConnectedAccount">
                <slot name="description">
                    <p>If you would like to receive donations and payments for private gigs through BuskinCity, please apply for payments with Stripe:</p>
                </slot>

                <label class="label mt-5">Country<sup class="has-text-danger">*</sup></label>
                <div class="field is-horizontal">
                    <div class="field-body">
                        <div class="field">
                            <div class="control">
                                <div class="select is-fullwidth">
                                    <select
                                        v-model="createStripeForm.country"
                                        required
                                    >
                                        <option value="">
                                            Select option
                                        </option>
                                        <option
                                            v-for="option in data.countryOptions"
                                            :key="option.id"
                                            :value="option.id"
                                        >
                                            {{ option.value }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <biz-input-error :message="error('country')" />
                        </div>

                        <div class="field">
                            <div class="control">
                                <button
                                    class="button is-primary"
                                    @click="createConnectedAccount"
                                >
                                    <span class="has-text-weight-bold">Create Connected Account</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <div
                v-else
                class="notification is-info is-light"
            >
                <p>You are already connected with Stripe. <strong>See more details?</strong></p>

                <div class="buttons are-small mt-5">
                    <biz-link
                        :href=" route('payments.stripe.show')"
                        class="button is-info"
                    >
                        <span class="icon is-small">
                            <i class="fa-solid fa-arrow-up-right-from-square" />
                        </span>
                        <span class="has-text-weight-bold">Stripe Connect</span>
                    </biz-link>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import MixinHasLoader from '@/Mixins/HasLoader';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import BizInputError from '@/Biz/InputError';
    import BizLink from '@/Biz/Link';
    import { confirm as confirmAlert, oops as oopsAlert } from '@/Libs/alert';
    import { useForm } from '@inertiajs/inertia-vue3';

    export default {
        name: 'StripeConnect',

        components: {
            BizInputError,
            BizLink,
        },

        mixins: [
            MixinHasLoader,
            MixinHasPageErrors,
        ],

        props: {
            data: {type: Object, required: true},
            title: {type: String, default: ""},
            order: {type: Number, required: true},
        },

        setup(props) {
            const createStripeForm = {
                country: props.data.defaultCountry,
            };

            return {
                createStripeForm: useForm(createStripeForm),
            };
        },

        methods: {
            createConnectedAccount() {
                const self = this;
                const url = route('payments.stripe.create-connected-account');

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
        },
    };
</script>
