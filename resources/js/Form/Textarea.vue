<template>
    <biz-form-textarea
        v-model="computedValue"
        :disabled="schema.is_disabled"
        :label="schema.label"
        :maxlength="schema.maxlength"
        :message="error(schema.name, bagName, errors)"
        :placeholder="schema.placeholder"
        :readonly="schema.is_readonly"
        :required="schema.is_required"
        :rows="schema.rows"
    />
</template>

<script>
    import BizFormTextarea from '@/Biz/Form/Textarea';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'FormTextarea',

        components: {
            BizFormTextarea,
        },

        mixins: [
            MixinHasPageErrors,
        ],

        inject: [
            'bagName',
        ],

        props: {
            errors: {
                type: Object,
                default: () => {}
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
