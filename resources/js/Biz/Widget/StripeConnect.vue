<template>
    <div
        class="column"
        :class="columnClasses"
    >
        <h2 class="title is-4">
            {{ startCase(title) }}
        </h2>
        <div class="box is-shadowless">
            <template v-if="!data.hasConnectedAccount">
                <slot name="description">
                    <p>
                        {{ i18n.inconnect }}
                    </p>
                </slot>

                <biz-label
                    class="mt-5"
                    is-required
                >
                    {{ i18n.country }}
                </biz-label>

                <div class="field is-horizontal">
                    <div class="field-body">
                        <biz-form-select
                            v-model="createStripeForm.country"
                            :message="error('country')"
                            is-fullwidth
                            required
                        >
                            <option value="">
                                {{ i18n.select_option }}
                            </option>
                            <option
                                v-for="option in data.countryOptions"
                                :key="option.id"
                                :value="option.id"
                            >
                                {{ option.value }}
                            </option>
                        </biz-form-select>

                        <div class="field">
                            <div class="control">
                                <button
                                    class="button is-primary"
                                    @click="createConnectedAccount"
                                >
                                    <span class="has-text-weight-bold">
                                        {{ i18n.create_connected_account }}
                                    </span>
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
                <p>
                    {{ i18n.connect }}
                </p>

                <div class="buttons are-small mt-5">
                    <biz-link
                        :href=" route('payments.stripe.show')"
                        class="button is-info"
                    >
                        <span class="icon is-small">
                            <i class="fa-solid fa-arrow-up-right-from-square" />
                        </span>
                        <span class="has-text-weight-bold">
                            {{ i18n.manage_payments }}
                        </span>
                    </biz-link>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import MixinHasLoader from '@/Mixins/HasLoader';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import MixinWidget from '@/Mixins/Widget';
    import BizLabel from '@/Biz/Label.vue';
    import BizFormSelect from '@/Biz/Form/Select.vue';
    import BizLink from '@/Biz/Link.vue';
    import { confirm as confirmAlert, oops as oopsAlert } from '@/Libs/alert';
    import { useForm } from '@inertiajs/vue3';
    import { startCase } from 'lodash';

    export default {
        name: 'StripeConnect',

        components: {
            BizLabel,
            BizFormSelect,
            BizLink,
        },

        mixins: [
            MixinHasLoader,
            MixinHasPageErrors,
            MixinWidget,
        ],

        props: {
            data: {type: Object, required: true},
            title: {type: String, default: ""},
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
                    this.i18n.create_alert_title,
                    this.i18n.create_alert_text,
                    this.i18n.create_alert_button,
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

            startCase,
        },
    };
</script>
