<template>
    <biz-form-field
        :is-required="required"
    >
        <div class="control">
            <biz-checkbox-toggle
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
            </biz-checkbox-toggle>
        </div>

        <slot name="note" />

        <template #error>
            <biz-input-error :message="message" />
        </template>
    </biz-form-field>
</template>

<script>
    import BizCheckboxToggle from '@/Biz/CheckboxToggle';
    import BizFormField from '@/Biz/Form/Field';
    import BizInputError from '@/Biz/InputError';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'BizFormChecboxToggle',

        components: {
            BizCheckboxToggle,
            BizFormField,
            BizInputError,
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
