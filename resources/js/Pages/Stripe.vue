<template>
    <div>
        <biz-error-notifications :errors="$page.props.errors" />

        <div class="box mb-6">
            <form @submit.prevent="submit">
                <div class="columns">
                    <div class="column">
                        <h3 class="title is-3">
                            {{ i18n.settings }}
                        </h3>
                    </div>
                    <div class="column">
                        <div class="field is-grouped is-grouped-right">
                            <div class="control">
                                <biz-button class="is-link">
                                    {{ i18n.save }}
                                </biz-button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="columns">
                    <div class="column">
                        <b>{{ i18n.is_enabled }}</b>
                    </div>

                    <div class="column">
                        <biz-select
                            v-model="form.is_enabled"
                        >
                            <option :value="true">
                                Enabled
                            </option>
                            <option :value="false">
                                Disabled
                            </option>
                        </biz-select>
                    </div>
                </div>

                <div class="columns">
                    <div class="column">
                        <b>{{ i18n.default_country }}</b>
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
                        <b>{{ i18n.application_fee_percentage }}</b>
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
                        <b>{{ i18n.payment_currencies }}</b>
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
                                    <th>{{ i18n.currency }}</th>
                                    <th>{{ i18n.minimal_payment }}</th>
                                    <th colspan="2">
                                        {{ i18n.amount_options }}
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
                                            :min="minimalCurrencyAmounts[currency]"
                                            style="width: 5rem;"
                                        >
                                            <template #note>
                                                <p class="help is-info">
                                                    {{ `${i18n.minimal}: ${minimalCurrencyAmounts[currency]} ${currency}` }}
                                                </p>
                                            </template>
                                        </biz-form-input>
                                    </td>
                                    <td>
                                        <div class="columns is-multiline">
                                            <div class="column is-6">
                                                <biz-form-input-addons
                                                    v-model="tempAmountOptions[ currency ]"
                                                    type="number"
                                                    :min="form.minimal_amounts[ currency ] >= minimalCurrencyAmounts[currency] ? form.minimal_amounts[ currency ] : minimalCurrencyAmounts[currency]"
                                                    @keydown.enter.prevent="addAmount(currency)"
                                                >
                                                    <template #afterInput>
                                                        <p class="control">
                                                            <button
                                                                class="button is-primary"
                                                                type="button"
                                                                :disabled="!canAddAmountOption(tempAmountOptions[ currency ], currency)"
                                                                @click.prevent="addAmount(currency)"
                                                            >
                                                                <i :class="icon.plusCircle" />
                                                            </button>
                                                        </p>
                                                    </template>

                                                    <template #note>
                                                        <p class="help is-info">
                                                            {{ `${i18n.minimal}: ${form.minimal_amounts[ currency ] >= minimalCurrencyAmounts[currency] ? form.minimal_amounts[ currency ] : minimalCurrencyAmounts[currency]} ${currency}` }}
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

                <div class="columns">
                    <div class="column">
                        <b>{{ i18n.primary_color }}</b>
                    </div>

                    <div class="column">
                        <biz-form-input-color
                            v-model="form.color_primary"
                            :message="error('color_primary')"
                        />
                    </div>
                </div>

                <div class="columns">
                    <div class="column">
                        <b>{{ i18n.secondary_color }}</b>
                    </div>

                    <div class="column">
                        <biz-form-input-color
                            v-model="form.color_secondary"
                            :message="error('color_secondary')"
                        />
                    </div>
                </div>

                <div class="columns">
                    <div class="column">
                        <b>{{ i18n.logo }}</b>
                    </div>

                    <div class="column">
                        <biz-form-media-library
                            v-model="form.logo"
                            image-preview-size="6"
                            :placeholder="i18n.open_media_library"
                            :is-browse-enabled="can?.media?.browse ?? false"
                            :is-download-enabled="can?.media?.read ?? false"
                            :is-edit-enabled="can?.media?.edit ?? false"
                            :is-upload-enabled="can?.media?.add ?? false"
                            :medium="logoMedia"
                            :dimension="dimensions.logo"
                            :instructions="instructions.mediaLibrary"
                            :message="error('logo')"
                        />
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
    import MixinHasLoader from '@/Mixins/HasLoader';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import AppLayout from '@/Layouts/AppLayout.vue';
    import BizButton from '@/Biz/Button.vue';
    import BizCheckbox from '@/Biz/Checkbox.vue';
    import BizDropdownItem from '@/Biz/DropdownItem.vue';
    import BizErrorNotifications from '@/Biz/ErrorNotifications.vue';
    import BizFormDropdownSearch from '@/Biz/Form/DropdownSearch.vue';
    import BizFormField from '@/Biz/Form/Field.vue';
    import BizFormInput from '@/Biz/Form/Input.vue';
    import BizFormInputAddons from '@/Biz/Form/InputAddons.vue';
    import BizFormInputColor from '@/Biz/Form/InputColor.vue';
    import BizInputError from '@/Biz/InputError.vue';
    import BizSelect from '@/Biz/Select.vue';
    import BizTable from '@/Biz/Table.vue';
    import BizFormMediaLibrary from '@/Biz/Form/MediaLibrary.vue';
    import icon from '@/Libs/icon-class';
    import { debounce, difference, isEmpty, filter, find, forEach, sortBy, mapValues } from 'lodash';
    import { debounceTime } from '@/Libs/defaults';
    import { success as successAlert, oops as oopsAlert } from '@/Libs/alert';
    import { useForm } from '@inertiajs/vue3';

    export default {
        name: 'StripeSettings',

        components: {
            BizButton,
            BizCheckbox,
            BizDropdownItem,
            BizErrorNotifications,
            BizFormDropdownSearch,
            BizFormField,
            BizFormInput,
            BizFormInputAddons,
            BizFormInputColor,
            BizInputError,
            BizSelect,
            BizTable,
            BizFormMediaLibrary,
        },

        mixins: [
            MixinHasLoader,
            MixinHasPageErrors,
        ],

        provide() {
            return {
                i18n: this.i18n,
            };
        },

        layout: AppLayout,

        props: {
            amountOptions: {
                type: Object,
                default: () => {}
            },
            applicationFeePercentage: {
                type: Number,
                default: null,
            },
            can: {
                type: Object,
                default: () => {}
            },
            colorPrimary: {
                type: String,
                default: null,
            },
            colorSecondary: {
                type: String,
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
            dimensions: { type: Object, default: () => {} },
            errors: {
                type: Object,
                default: () => {}
            },
            isEnabled: {
                type: Boolean,
                default: false,
            },
            logoMedia: {
                type: Object,
                default: () => {},
            },
            instructions: {
                type: Object,
                default: () => {},
            },
            minimalAmounts: {
                type: Object,
                default: () => {},
            },
            minimalCurrencyAmounts: {
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
            i18n: {
                type: Object,
                default: () => ({
                    amount_options : 'Amount options',
                    application_fee_percentage : 'Application fee percentage',
                    currency : 'Currency',
                    default_country : 'Default country',
                    is_enabled : 'Is enabled?',
                    logo : 'Logo',
                    minimal_payment : 'Minimal payment',
                    minimal: 'Minimal',
                    open_media_library : 'Open media library',
                    payment_currencies : 'Payment currencies',
                    primary_color : 'Primary color',
                    save : 'Save',
                    secondary_color : 'Secondary color',
                    settings : 'Settings',
                }),
            },
        },

        setup(props) {
            const amountOptions = mapValues(props.amountOptions ?? {}, function (options) {
                return sortBy(options);
            });

            const form = {
                amount_options: amountOptions,
                application_fee_percentage: props.applicationFeePercentage ?? null,
                default_country: props.defaultCountry ?? '',
                is_enabled: props.isEnabled ?? false,
                minimal_amounts: props.minimalAmounts ?? {},
                payment_currencies: props.paymentCurrencies ?? [],
                color_primary: props.colorPrimary ?? '',
                color_secondary: props.colorSecondary ?? '',
                logo: props.logoMedia?.id ?? null,
            };

            return {
                form: useForm(form),
            };
        },

        data() {
            return {
                filteredCountries: this.countryOptions.slice(0, 10),
                icon,
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

            'form.minimal_amounts': {
                deep: true,
                handler(value) {
                    const self = this;

                    forEach(self.form.amount_options, (options, currency) => {
                        self.form.amount_options[currency] = filter(
                            options,
                            function (amountOption) {
                                return amountOption >= value[currency];
                            }
                        );
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
            }, debounceTime),

            addAmount(currency) {
                if (this.canAddAmountOption(this.tempAmountOptions[ currency ], currency)) {
                    this.form.amount_options[ currency ].push(
                        parseInt(this.tempAmountOptions[ currency ])
                    );
                    this.tempAmountOptions[ currency ] = '';

                    this.form.amount_options[ currency ] = sortBy(this.form.amount_options[ currency ]);
                }
            },

            canAddAmountOption(amount, currency) {
                let minimalAmount = this.form.minimal_amounts[ currency ] >= this.minimalCurrencyAmounts[currency]
                    ? this.form.minimal_amounts[ currency ]
                    : this.minimalCurrencyAmounts[currency];

                return (
                    amount
                    && /^\+?\d+$/.test(amount.replace(/\s/g,''))
                    && parseInt(amount) > 0
                ) && parseInt(minimalAmount) <= parseInt(amount);
            },

            deleteAmount(currency, index) {
                this.form.amount_options[ currency ].splice(index, 1);
            },

            submit() {
                const self = this;
                this.form.post(this.route('admin.settings.stripe.update'), {
                    preserveScroll: false,
                    preserveState: false,
                    onStart: () => {
                        self.onStartLoadingOverlay();
                    },
                    onSuccess: (page) => {
                        successAlert(page.props.flash.message);
                    },
                    onError(errors) {
                        oopsAlert();
                    },
                    onFinish: () => {
                        self.onEndLoadingOverlay();
                    }
                });
            },
        },
    };
</script>
