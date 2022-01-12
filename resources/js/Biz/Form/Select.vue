<template>
    <biz-form-field
        :is-required="required"
        :class="fieldClass"
    >
        <template #label>
            {{ label }}
        </template>

        <biz-select
            v-model="selected"
            v-bind="$attrs"
            :disabled="disabled"
            :placeholder="placeholder"
        >
            <slot />
        </biz-select>

        <template #error>
            <biz-input-error :message="message" />
        </template>
    </biz-form-field>
</template>

<script>
    import BizFormField from '@/Biz/Form/Field';
    import BizInputError from '@/Biz/InputError';
    import BizSelect from '@/Biz/Select';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'BizFormSelect',

        components: {
            BizFormField,
            BizInputError,
            BizSelect,
        },

        inheritAttrs: false,

        props: {
            disabled: {
                type: Boolean,
                default: false
            },
            label: {
                type: String,
                default: ""
            },
            message: {
                type: Object,
                default: () => {}
            },
            modelValue: {
                type: [Object, Array, String, Number, Boolean, null],
                required: true
            },
            placeholder: {
                type: String,
                default: ""
            },
            required: {
                type: Boolean,
                default: false
            },
            fieldClass: {
                type: [Object, Array, String],
                default: undefined,
            }
        },
        setup(props, { emit }) {
            return {
                selected: useModelWrapper(props, emit),
            };
        },
    };
</script>
