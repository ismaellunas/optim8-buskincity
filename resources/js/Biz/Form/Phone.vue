<template>
    <biz-form-field
        :is-required="required"
    >
        <template #label>
            {{ label }}
        </template>

        <biz-phone
            v-model="computedValue"
            v-bind="$attrs"
            :is-error="isError"
            :country-options="countryOptions"
            :default-country="defaultCountry"
            :disabled="disabled"
            :required="required"
        />

        <slot name="note" />

        <template #error>
            <biz-input-error :message="message" />
        </template>
    </biz-form-field>
</template>

<script>
    import BizFormField from '@/Biz/Form/Field';
    import BizPhone from '@/Biz/Phone';
    import BizInputError from '@/Biz/InputError';
    import { isEmpty } from 'lodash';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'BizFormPhone',

        components: {
            BizFormField,
            BizInputError,
            BizPhone,
        },

        inheritAttrs: false,

        props: {
            label: {
                type: String,
                default: ''
            },
            message: {
                type: [Array, Object, String],
                default: undefined
            },
            modelValue: {
                type: Object,
                required: true
            },
            disabled: {
                type: Boolean,
                default: false
            },
            required: {
                type: Boolean,
                default: false
            },
            countryOptions: {
                type: [Array, Object],
                default: () => [
                    {code: 'AU', countryName: "Australia", dial: '+61'},
                    {code: 'ID', countryName: "Indonesia", dial: '+62'},
                    {code: 'SG', countryName: "Singapore", dial: '+65'},
                    {code: 'US', countryName: "United States", dial: '+1'},
                ]
            },
            defaultCountry: {
                type: String,
                default: 'US',
            }
        },

        emits: [
            'update:modelValue',
        ],

        setup(props, {emit}) {
            return {
                computedValue: useModelWrapper(props, emit),
            };
        },

        data() {
            return {
                selectedCountryCode: 'ID',
            };
        },

        computed: {
            isError() {
                return !isEmpty(this.message);
            },
        }
    };
</script>
