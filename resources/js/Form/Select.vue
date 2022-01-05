<template>
    <sdb-form-select
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
    </sdb-form-select>
</template>

<script>
    import SdbFormSelect from '@/Sdb/Form/Select';
    import { isBlank, useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'FormSelect',

        components: {
            SdbFormSelect,
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
                if (! isBlank(this.schema.default_value)) {
                    this.computedValue = this.schema.default_value;
                }
            }
        },
    };
</script>
