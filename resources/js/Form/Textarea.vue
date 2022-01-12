<template>
    <biz-form-textarea
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
    import BizFormTextarea from '@/Biz/Form/Textarea';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'FormTextarea',

        components: {
            BizFormTextarea,
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
