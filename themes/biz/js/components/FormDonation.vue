<template>
    <div>
        <form
            ref="form"
            :action="submitRoute"
            method="POST"
            @submit.prevent="submit"
        >
            <input
                type="hidden"
                name="_token"
                :value="csrf"
            >

            <div class="content">
                <div class="field is-grouped">
                    <p
                        v-for="amountOption, index in selectedAmountOptions"
                        :key="index"
                        class="control"
                    >
                        <button
                            class="button is-primary"
                            type="button"
                            @click.prevent="setAmount(amountOption)"
                        >
                            <span class="icon is-small">
                                <i class="fas fa-money-bill" />
                            </span>
                            <span>{{ amountOption }}</span>
                        </button>
                    </p>
                </div>

                <div class="field has-addons mb-0">
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

                    <div class="control is-expanded">
                        <input
                            type="number"
                            name="amount"
                            placeholder="Amount of money"
                            min="1"
                            required
                            :class="{input: true, 'is-danger': messages.length > 0}"
                            :value="filteredAmount"
                            @paste="onPaste"
                        >
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

            <div class="buttons has-addons is-centered">
                <button
                    class="button is-link"
                    :disabled="buttonDisabled"
                >
                    {{ buttonText }}
                </button>
            </div>
        </form>
    </div>
</template>

<script>
    import BizSelect from '@/Biz/Select';

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
                type: Array,
                default: () => [],
            },
        },

        data: () => ({
            csrf: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            selectedCurrency: 'SEK',
            amount: null,
            buttonDisabled: null,
        }),

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
