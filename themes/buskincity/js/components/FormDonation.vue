<template>
    <form
        ref="form"
        v-if="hasRequirements"
        :action="submitRoute"
        method="POST"
        @submit.prevent="submit"
    >
        <input
            type="hidden"
            name="_token"
            :value="csrf"
        >

        <div class="buttons mt-5">
            <template
                v-for="amountOption, index in selectedAmountOptions"
                :key="index"
            >
                <button
                    class="button is-link is-outlined is-flex-grow-1"
                    type="button"
                    @click.prevent="setAmount(amountOption)"
                >
                    <span class="has-text-weight-bold">
                        {{ currencyValue + ' ' + amountOption }}
                    </span>
                </button>
            </template>
        </div>
        <div class="content">
            <div class="field has-addons mb-0">
                <div class="control is-expanded">
                    <input
                        v-model="filteredAmount"
                        type="number"
                        name="amount"
                        placeholder="Enter the amount"
                        min="1"
                        required
                        :class="{input: true, 'is-danger': messages.length > 0}"
                        @paste="onPaste"
                    >
                </div>

                <div class="control">
                    <span
                        id="currency"
                        class="select"
                    >
                        <biz-select
                            id="currency"
                            v-model="selectedCurrency"
                            name="currency"
                        >
                            <option
                                v-for="currency in currencies"
                                :key="currency['id']"
                                :value="currency['id']"
                            >
                                {{ currency['value'] }}
                            </option>
                        </biz-select>
                    </span>
                </div>
            </div>

            <div
                v-for="message, index in messages"
                :key="index"
                class="help is-danger"
            >
                {{ message }}
            </div>
        </div>
        <div class="buttons">
            <button
                class="button is-primary is-medium is-flex-grow-1"
                :disabled="buttonDisabled"
            >
                <span class="has-text-weight-bold">{{ buttonText + ' ' + donationAmount }}</span>
            </button>
        </div>
    </form>
</template>

<script>
    import BizSelect from '@/Biz/Select';
    import { find } from 'lodash';

    export default {
        name: 'FormDonation',

        components: {
            BizSelect,
        },

        props: {
            amountOptions: {
                type: Object,
                default: () => {},
            },
            currencies: {
                type: Array,
                required: true,
            },
            submitRoute: {
                type: String,
                required: true,
            },
            buttonText: {
                type: String,
                default: 'Donate',
            },
            messages: {
                type: Array,
                default: () => [],
            },
            listMinimalPayments: {
                type: Object,
                default: () => {},
            },
        },

        data() {
            return {
                csrf: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                selectedCurrency: this.currencies[0] ? this.currencies[0].id : '',
                amount: null,
                buttonDisabled: null,
            };
        },

        computed: {
            selectedAmountOptions() {
                return this.amountOptions[this.selectedCurrency];
            },

            filteredAmount: {
                get() {
                    return this.amount;
                },
                set(value) {
                    this.amount = parseInt(value);
                },
            },

            minimalPayment() {
                return this.listMinimalPayments[this.selectedCurrency] ?? 0;
            },

            hasRequirements() {
                return this.currencies.length > 0;
            },

            currencyValue() {
                return find(this.currencies, { 'id': this.selectedCurrency }).value;
            },

            donationAmount() {
                return this.amount ? this.currencyValue + ' ' + this.amount : '';
            }
        },

        methods: {
            setAmount(amount) {
                this.amount = amount;
            },

            onPaste(event) {
                let paste = (event.clipboardData || window.clipboardData).getData('text');

                this.filteredAmount = paste;

                event.preventDefault();
            },

            submit() {
                this.$refs.form.submit();
                this.buttonDisabled = true;
            },
        }
    };
</script>
