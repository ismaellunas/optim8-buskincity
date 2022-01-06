<template>
    <form-input
        v-model="computedValue"
        :label="schema.label"
        :maxlength="schema.maxlength"
        :placeholder="schema.placeholder"
        :disabled="schema.is_disabled"
        :readonly="schema.is_readonly"
        :required="schema.is_required"
        :message="message"
    />
</template>

<script>
    import FormInput from '@/Sdb/Form/Input';
    import { isBlank, useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'FormText',

        components: {
            FormInput,
        },

        props: {
            schema: {
                type: Object,
                required: true
            },
            modelValue: {
                type: [String, Number, null],
                default: null
            },
            message: {
                type: [Object, String, Array],
                default: undefined
            },
        },

        setup(props, { emit }) {
            return {
                computedValue: useModelWrapper(props, emit),
            };
        },

        mounted() {
            if (
                isBlank(this.computedValue)
                && !isBlank(this.schema.default_value)
            ) {
                this.computedValue = this.schema.default_value;
            }
        },
    };
</script>
