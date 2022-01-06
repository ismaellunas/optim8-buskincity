<template>
    <sdb-form-radio
        v-model="computedValue"
        :disabled="schema.is_disabled"
        :label="schema.label"
        :message="message"
        :required="schema.is_required"
        :options="schema.options"
    />
</template>

<script>
    import SdbFormRadio from '@/Sdb/Form/Radio';
    import { isBlank, useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'FormRadio',

        components: {
            SdbFormRadio,
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
