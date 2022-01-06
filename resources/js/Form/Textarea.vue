<template>
    <sdb-form-textarea
        v-model="computedValue"
        :disabled="schema.is_disabled"
        :label="schema.label"
        :maxlength="schema.maxlength"
        :message="message"
        :placeholder="schema.placeholder"
        :readonly="schema.is_readonly"
        :required="schema.is_required"
        :rows="schema.rows"
    />
</template>

<script>
    import SdbFormTextarea from '@/Sdb/Form/Textarea';
    import { isBlank, useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'FormTextarea',

        components: {
            SdbFormTextarea,
        },

        props: {
            message: {
                type: [Object, String, Array],
                default: undefined,
            },
            modelValue: {
                type: [String, Number, null],
                default: null
            },
            schema: {
                type: Object,
                required: true
            },
        },

        setup(props, { emit }) {
            return {
                computedValue: useModelWrapper(props, emit),
            };
        },
    };
</script>
