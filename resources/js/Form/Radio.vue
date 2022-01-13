<template>
    <biz-form-radio
        v-model="computedValue"
        :disabled="schema.is_disabled"
        :label="schema.label"
        :message="message"
        :required="schema.is_required"
        :options="schema.options"
    />
</template>

<script>
    import BizFormRadio from '@/Biz/Form/Radio';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'FormRadio',

        components: {
            BizFormRadio,
        },

        props: {
            schema: {
                type: Object,
                required: true
            },
            modelValue: {
                type: [String, Number, null],
                default: ''
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
