<template>
    <biz-form-field
        :class="wrapperClass"
        :is-required="required"
    >
        <template
            v-if="hasLabel"
            #label
        >
            {{ label }}
        </template>

        <biz-input-password
            ref="input"
            v-bind="$attrs"
            v-model="computedValue"
            :disabled="disabled"
            :has-error="hasError"
            :placeholder="placeholder"
            :required="required"
            @keypress="$emit('on-keypress', $event)"
        />

        <template #error>
            <biz-input-error :message="message" />
        </template>
    </biz-form-field>
</template>

<script>
    import BizFormField from '@/Biz/Form/Field';
    import BizInputPassword from '@/Biz/InputPassword';
    import BizInputError from '@/Biz/InputError';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'BizFormPassword',

        components: {
            BizFormField,
            BizInputPassword,
            BizInputError,
        },

        inheritAttrs: false,

        props: {
            disabled: {
                type: Boolean,
                default: false
            },
            label: {
                type: [String, null],
                default: null,
            },
            message: {
                type: [Array, Object, String],
                default: undefined
            },
            modelValue: {
                type: [String, Number, null],
                required: true
            },
            placeholder: {
                type: [String, null],
                default: null,
            },
            required: {
                type: Boolean,
                default: false
            },
            wrapperClass: {
                type: [Array, Object, String],
                default: '',
            },
        },

        emits: [
            'on-keypress',
            'update:modelValue',
        ],

        setup(props, { emit }) {
            return {
                computedValue: useModelWrapper(props, emit),
            };
        },

        computed: {
            hasError() {
                return this.message ? true : false;
            },

            hasLabel() {
                return this.label ? true : false;
            },
        },
    };
</script>
