<template>
    <div class="column is-half">
        <h2 class="title is-4 mt-5">
            {{ title }}
        </h2>
        <div class="box is-shadowless">
            <p>If you would like to receive donations and payments for private gigs through BuskinCity, please apply for payments with Stripe:</p>

            <label class="label mt-5">Country<sup class="has-text-danger">*</sup></label>
            <form action="#">
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
                        </div>

                        <div class="field">
                            <div class="control">
                                <button class="button is-primary">
                                    <span class="has-text-weight-bold">Create Connected Account</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import BizFormSelect from '@/Biz/Form/Select';
    import { useForm } from '@inertiajs/inertia-vue3';

    export default {
        name: 'StripeConnect',

        components: {
            BizFormSelect
        },

        mixins: [
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
    };
</script>
