<template>
    <sdb-form-field
        :is-required="required"
    >
        <div class="control">
            <sdb-checkbox-toggle
                v-model="computedValue"
                v-bind="$attrs"
                :disabled="disabled"
                :value="value"
                :true-value="trueValue"
                :false-value="falseValue"
            >
                &nbsp;
                <span
                    v-if="isRaw"
                    v-html="text"
                />
                <span v-else>
                    {{ text }}
                </span>
            </sdb-checkbox-toggle>
        </div>

        <template #error>
            <sdb-input-error :message="message" />
        </template>
    </sdb-form-field>
</template>

<script>
    import SdbCheckboxToggle from '@/Sdb/CheckboxToggle';
    import SdbFormField from '@/Sdb/Form/Field';
    import SdbInputError from '@/Sdb/InputError';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'SdbFormChecboxToggle',

        components: {
            SdbCheckboxToggle,
            SdbFormField,
            SdbInputError,
        },

        inheritAttrs: false,

        props: {
            message: {
                type: [Array, Object, String],
                default: undefined
            },
            modelValue: {
                type: [String, Number, Boolean, null],
                required: true
            },
            disabled: {
                type: Boolean,
                default: false
            },
            text: {
                type: String,
                default: '',
            },
            required: {
                type: Boolean,
                default: false
            },
            trueValue: {
                type: [String, Number, Boolean],
                default: true,
            },
            falseValue: {
                type: [String, Number, Boolean],
                default: false,
            },
            value: {
                type: [String, Number, Boolean, Function, Object, Array],
                default: undefined
            },
            isRaw: {
                type: Boolean,
                default: false
            }
        },

        emits: [
            'update:modelValue',
        ],

        setup(props, { emit }) {
            return {
                computedValue: useModelWrapper(props, emit),
            };
        },
    };
</script>
