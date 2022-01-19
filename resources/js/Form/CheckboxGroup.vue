<template>
    <biz-form-checkbox-group
        v-model="computedValue"
        :disabled="schema.is_disabled"
        :is-raw="schema.is_raw"
        :label="schema.label"
        :layout="schema.layout"
        :message="error(schema.name, bagName, errors)"
        :options="schema.options"
        :readonly="schema.is_readonly"
        :required="schema.is_required"
    />
</template>

<script>
    import BizFormCheckboxGroup from '@/Biz/Form/CheckboxGroup';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'FormCheckboxGroup',

        components: {
            BizFormCheckboxGroup,
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
                type: Array,
                required: true
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
