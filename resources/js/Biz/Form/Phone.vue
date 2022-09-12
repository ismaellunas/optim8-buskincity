<template>
    <biz-form-field
        :class="fieldClass"
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
            :dropdown-max-height="dropdownMaxHeight"
            :dropdown-max-width="dropdownMaxWidth"
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
            fieldClass: {
                type: [Object, Array, String],
                default: undefined,
            },
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
                    {id: 'AU', value: "Australia", dial: '61'},
                    {id: 'ID', value: "Indonesia", dial: '62'},
                    {id: 'SG', value: "Singapore", dial: '65'},
                    {id: 'US', value: "United States", dial: '1'},
                ]
            },
            defaultCountry: {
                type: String,
                default: 'US',
            },
            dropdownMaxHeight: {
                type: Number,
                default: 350
            },
            dropdownMaxWidth: {
                type: Number,
                default: null
            },
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
                selectedCountryCode: this.defaultCountry,
            };
        },

        computed: {
            isError() {
                return !isEmpty(this.message);
            },
        }
    };
</script>
