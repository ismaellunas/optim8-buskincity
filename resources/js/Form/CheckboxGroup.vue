<template>
    <biz-form-checkbox-group
        v-model="computedValue"
        :disabled="schema.is_disabled"
        :is-raw="schema.is_raw"
        :label="schema.label"
        :layout="schema.layout"
        :message="message"
        :options="schema.options"
        :readonly="schema.is_readonly"
        :required="schema.is_required"
    />
</template>

<script>
    import BizFormCheckboxGroup from '@/Biz/Form/CheckboxGroup';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'FormCheckboxGroup',

        components: {
            BizFormCheckboxGroup,
        },

        props: {
            schema: {
                type: Object,
                required: true
            },
            modelValue: {
                type: Array,
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
