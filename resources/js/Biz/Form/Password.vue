<template>
    <biz-field>
        <biz-label v-if="hasLabel">
            {{ label }}
        </biz-label>

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

        <biz-input-error :message="message" />
    </biz-field>
</template>

<script>
    import BizField from '@/Biz/Field';
    import BizInputPassword from '@/Biz/InputPassword';
    import BizInputError from '@/Biz/InputError';
    import BizLabel from '@/Biz/Label';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'BizFormPassword',

        components: {
            BizField,
            BizInputPassword,
            BizInputError,
            BizLabel,
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
