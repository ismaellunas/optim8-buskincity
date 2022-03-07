<template>
    <app-layout>
        <template #header>
            {{ title }}
        </template>

        <biz-error-notifications :errors="$page.props.errors" />

        <div class="box mb-6">
            <form @submit.prevent="submit">
                <div class="columns">
                    <div class="column">
                        <h3 class="title is-3">
                            Settings
                        </h3>
                    </div>
                    <div class="column">
                        <div class="field is-grouped is-grouped-right">
                            <div class="control">
                                <biz-button class="is-link">
                                    Save
                                </biz-button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="columns">
                    <div class="column">
                        <b>Default Country</b>
                    </div>

                    <div class="column">
                        <biz-form-dropdown-search
                            :close-on-click="true"
                            :message="error('default_country')"
                            @search="searchCountry($event)"
                        >
                            <template #trigger>
                                <span :style="{'min-width': '4rem'}">
                                    {{ selectedCountry }}
                                </span>
                            </template>

                            <biz-dropdown-item
                                v-for="option in filteredCountries"
                                :key="option.id"
                                @click="selectedCountry = option"
                            >
                                {{ option.value }}
                            </biz-dropdown-item>
                        </biz-form-dropdown-search>
                    </div>
                </div>

                <div class="columns">
                    <div class="column">
                        <b>Application Fee Percentage</b>
                    </div>

                    <div class="column">
                        <biz-form-input-addons
                            v-model="form.application_fee_percentage"
                            min="0"
                            type="number"
                            wrapper-class="column is-half p-0"
                            :message="error('application_fee_percentage')"
                        >
                            <template #afterInput>
                                <p class="control">
                                    <a class="button is-static">%</a>
                                </p>
                            </template>
                        </biz-form-input-addons>
                    </div>
                </div>

                <div class="columns">
                    <div class="column">
                        <b>Payment Currencies</b>
                    </div>

                    <div class="column">
                        <biz-form-field>
                            <div class="control">
                                <biz-checkbox
                                    v-for="(currency, index) in currencyOptions"
                                    :key="index"
                                    v-model:checked="form.payment_currencies"
                                    label-class="mr-4"
                                    :value="currency"
                                >
                                    &nbsp;{{ currency }}
                                </biz-checkbox>
                            </div>

                            <template #error>
                                <biz-input-error
                                    :message="error('payment_currencies')"
                                />
                            </template>
                        </biz-form-field>

                        <biz-table
                            v-if="form.payment_currencies.length"
                            class="is-fullwidth"
                        >
                            <thead>
                                <tr>
                                    <th>Currency</th>
                                    <th>Minimal Payment</th>
                                    <th colspan="2">
                                        Amount Options
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="(currency, index) in form.payment_currencies"
                                    :key="index"
                                >
                                    <td>{{ currency }}</td>
                                    <td>
                                        <biz-form-input
                                            v-model="form.minimal_amounts[ currency ]"
                                            type="number"
                                            min="1"
                                            style="width: 5rem;"
                                        />
                                    </td>
                                    <td>
                                        <div class="columns is-multiline">
                                            <div class="column is-6">
                                                <biz-form-input-addons
                                                    v-model="tempAmountOptions[ currency ]"
                                                    type="number"
                                                    min="1"
                                                >
                                                    <template #afterInput>
                                                        <p class="control">
                                                            <button
                                                                class="button is-primary"
                                                                type="button"
                                                                @click.prevent="addAmount(currency)"
                                                            >
                                                                <i class="fas fa-plus-circle" />
                                                            </button>
                                                        </p>
                                                    </template>
                                                </biz-form-input-addons>
                                            </div>
                                            <div class="column is-12 pt-0">
                                                <div class="tags are-medium">
                                                    <span
                                                        v-for="(amount, amountIndex) in form.amount_options[ currency ]"
                                                        :key="amountIndex"
                                                        class="tag is-success"
                                                    >
                                                        {{ amount }}
                                                        <button
                                                            class="delete is-small"
                                                            @click.prevent="deleteAmount(currency, amountIndex)"
                                                        />
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </biz-table>
                    </div>
                </div>
            </form>
        </div>
    </app-layout>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout';
    import BizButton from '@/Biz/Button';
    import BizCheckbox from '@/Biz/Checkbox';
    import BizDropdownItem from '@/Biz/DropdownItem';
    import BizErrorNotifications from '@/Biz/ErrorNotifications';
    import BizFormDropdownSearch from '@/Biz/Form/DropdownSearch';
    import BizFormField from '@/Biz/Form/Field';
    import BizFormInput from '@/Biz/Form/Input';
    import BizFormInputAddons from '@/Biz/Form/InputAddons';
    import BizInputError from '@/Biz/InputError';
    import BizTable from '@/Biz/Table';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import { debounce, difference, isEmpty, filter, find, forEach } from 'lodash';
    import { success as successAlert, oops as oopsAlert } from '@/Libs/alert';
    import { useForm, usePage } from '@inertiajs/inertia-vue3';

    export default {
        name: 'StripeSettings',

        components: {
            AppLayout,
            BizButton,
            BizCheckbox,
            BizDropdownItem,
            BizErrorNotifications,
            BizFormDropdownSearch,
            BizFormField,
            BizFormInput,
            BizFormInputAddons,
            BizInputError,
            BizTable,
        },

        mixins: [
            MixinHasPageErrors,
        ],

        props: {
            amountOptions: {
                type: Object,
                default: () => {}
            },
            applicationFeePercentage: {
                type: Number,
                default: null,
            },
            countryOptions: {
                type: Array,
                required: true
            },
            currencyOptions: {
                type: Array,
                required: true,
            },
            defaultCountry: {
                type: String,
                default: "",
            },
            errors: {
                type: Object,
                default: () => {}
            },
            minimalAmounts: {
                type: Object,
                default: () => {},
            },
            paymentCurrencies: {
                type: Array,
                default: () => [],
            },
            title: {
                type: String,
                default: "Stripe"
            },
        },

        setup(props) {
            const form = {
                amount_options: props.amountOptions ?? {},
                application_fee_percentage: props.applicationFeePercentage ?? null,
                default_country: props.defaultCountry ?? '',
                minimal_amounts: props.minimalAmounts ?? {},
                payment_currencies: props.paymentCurrencies ?? [],
            };

            return {
                form: useForm(form),
            };
        },

        data() {
            return {
                filteredCountries: this.countryOptions.slice(0, 10),
                loader: null,
                tempAmountOptions: {},
            }
        },

        computed: {
            selectedCountry: {
                get() {
                    if (this.form.default_country) {
                        const country = find(
                            this.countryOptions,
                            ['id', this.form.default_country]
                        );
                        return country.value;
                    }

                    return '';
                },

                set(country) {
                    this.form.default_country = country.id;
                }
            },
        },

        watch: {
            'form.payment_currencies': {
                deep: true,
                handler(value, oldValue) {
                    const self = this;
                    const addedValues = difference(value, oldValue);
                    const reducedValues = difference(oldValue, value);

                    forEach(addedValues, (addedValue) => {
                        self.form.amount_options[ addedValue ] = [];
                        self.form.minimal_amounts[ addedValue ] = "";
                        self.tempAmountOptions[ addedValue ] = "";
                    });

                    forEach(reducedValues, (reducedValue) => {
                        delete self.form.amount_options[ reducedValue ];
                        delete self.form.minimal_amounts[ reducedValue ];
                        delete self.tempAmountOptions[ reducedValue ];
                    });
                },
            },
        },

        methods: {
            searchCountry: debounce(function(term) {
                if (!isEmpty(term) && term.length > 1) {
                    this.filteredCountries = filter(this.countryOptions, function (country) {
                        return new RegExp(term, 'i').test(country.value);
                    }).slice(0, 10);
                } else {
                    this.filteredCountries = this.countryOptions.slice(0, 10);
                }
            }, 750),

            addAmount(currency) {
                if (this.tempAmountOptions[ currency ]) {
                    this.form.amount_options[ currency ].push(
                        this.tempAmountOptions[ currency ]
                    );
                    this.tempAmountOptions[ currency ] = '';
                }
            },

            deleteAmount(currency, index) {
                this.form.amount_options[ currency ].splice(index, 1);
            },

            submit() {
                const self = this;
                this.form.post(this.route('admin.settings.stripe.update'), {
                    preserveScroll: false,
                    onStart: () => {
                        self.loader = self.$loading.show();
                    },
                    onSuccess: (page) => {
                        successAlert(page.props.flash.message);
                    },
                    onFinish: () => {
                        self.loader.hide();
                    }
                });
            },
        },
    };
</script>
