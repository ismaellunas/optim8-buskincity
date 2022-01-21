<template>
    <biz-form-select
        v-model="computedValue"
        :disabled="schema.is_disabled"
        :label="schema.label"
        :message="error(schema.name, bagName, errors)"
        :placeholder="schema.placeholder"
        :readonly="schema.is_readonly"
        :required="schema.is_required"
    >
        <option
            v-for="option, index in schema.options"
            :key="index"
            :value="index"
        >
            {{ option }}
        </option>
    </biz-form-select>
</template>

<script>
    import BizFormSelect from '@/Biz/Form/Select';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'FormSelect',

        components: {
            BizFormSelect,
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
                default: ''
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

        mounted() {
            if (this.modelValue == '') {
                if (this.schema.default_value == this.modelValue) {
                    this.computedValue = null;
                }
            }
        },
    };
</script>
