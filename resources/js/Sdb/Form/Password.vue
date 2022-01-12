<template>
    <sdb-field>
        <sdb-label v-if="hasLabel">
            {{ label }}
        </sdb-label>

        <sdb-input-password
            ref="input"
            v-bind="$attrs"
            v-model="computedValue"
            :disabled="disabled"
            :has-error="hasError"
            :placeholder="placeholder"
            :required="required"
            @keypress="$emit('on-keypress', $event)"
        />

        <sdb-input-error :message="message" />
    </sdb-field>
</template>

<script>
    import SdbField from '@/Sdb/Field';
    import SdbInputPassword from '@/Sdb/InputPassword';
    import SdbInputError from '@/Sdb/InputError';
    import SdbLabel from '@/Sdb/Label';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'SdbFormPassword',

        components: {
            SdbField,
            SdbInputPassword,
            SdbInputError,
            SdbLabel,
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
