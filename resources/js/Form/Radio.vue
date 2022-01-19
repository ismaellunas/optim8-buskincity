<template>
    <biz-form-radio
        v-model="computedValue"
        :disabled="schema.is_disabled"
        :label="schema.label"
        :message="error(schema.name, bagName, errors)"
        :required="schema.is_required"
        :options="schema.options"
    />
</template>

<script>
    import BizFormRadio from '@/Biz/Form/Radio';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'FormRadio',

        components: {
            BizFormRadio,
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
                type: [String, Number, null],
                default: ''
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
