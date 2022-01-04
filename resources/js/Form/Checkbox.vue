<template>
    <sdb-form-checbox-toggle
        v-model="computedValue"
        :false-value="schema.false_value"
        :true-value="schema.true_value"
        :text="schema.text"
        :disabled="schema.is_disabled"
        :is-raw="schema.is_raw"
        :message="message"
        :readonly="schema.is_readonly"
        :required="schema.is_required"
        :value="schema.value"
    />
</template>

<script>
    import SdbFormChecboxToggle from '@/Sdb/Form/CheckboxToggle';
    import { isBlank, useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'FormCheckbox',

        components: {
            SdbFormChecboxToggle,
        },

        props: {
            schema: {
                type: Object,
                required: true
            },
            modelValue: {
                type: [String, Number, Boolean, null],
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

        mounted() {
            if (this.modelValue == '') {
                if (! isBlank(this.schema.default_value)) {
                    this.computedValue = this.schema.default_value;
                }
            }
        },
    };
</script>
