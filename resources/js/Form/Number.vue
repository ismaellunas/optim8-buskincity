<template>
    <sdb-form-number
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
    import SdbFormNumber from '@/Sdb/Form/Number';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'FormNumber',

        components: {
            SdbFormNumber,
        },

        props: {
            schema: {
                type: Object,
                required: true
            },
            modelValue: {
                type: [String, Number, null],
                required: true
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
    };
</script>
