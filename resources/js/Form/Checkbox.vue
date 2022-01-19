<template>
    <biz-form-checbox-toggle
        v-model="computedValue"
        :false-value="schema.false_value"
        :true-value="schema.true_value"
        :text="schema.text"
        :disabled="schema.is_disabled"
        :is-raw="schema.is_raw"
        :message="error(schema.name, bagName, errors)"
        :readonly="schema.is_readonly"
        :required="schema.is_required"
        :value="schema.value"
    />
</template>

<script>
    import BizFormChecboxToggle from '@/Biz/Form/CheckboxToggle';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'FormCheckbox',

        components: {
            BizFormChecboxToggle,
        },

        mixins: [
            MixinHasPageErrors,
        ],

        inject: [
            'bagName',
        ],

        props: {
            schema: {
                type: Object,
                required: true
            },
            modelValue: {
                type: [String, Number, Boolean, null],
                required: true
            },
            errors: {
                type: Object,
                default: () => {}
            }
        },

        setup(props, { emit }) {
            return {
                computedValue: useModelWrapper(props, emit),
            };
        },
    };
</script>
