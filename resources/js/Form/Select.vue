<template>
    <biz-form-select
        v-model="computedValue"
        :disabled="schema.is_disabled"
        :label="schema.label"
        :message="message"
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
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'FormSelect',

        components: {
            BizFormSelect,
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

        mounted() {
            if (this.modelValue == '') {
                if (this.schema.default_value == this.modelValue) {
                    this.computedValue = null;
                }
            }
        },
    };
</script>
