<template>
    <biz-form-field :is-required="required">
        <template #label>
            {{ label }}
        </template>

        <biz-date-time
            v-model="modelValue"
            v-bind="$attrs"
            :disabled="disabled"
            @input="$emit('update:modelValue', $event)"
        />

        <template #error>
            <biz-input-error :message="message" />
        </template>
    </biz-form-field>
</template>

<script>
    import BizDateTime from '@/Biz/DateTime';
    import BizFormField from '@/Biz/Form/Field';
    import BizInputError from '@/Biz/InputError';

    export default {
        name: 'BizFormDateTime',
        components: {
            BizFormField,
            BizDateTime,
            BizInputError,
        },
        inheritAttrs: false,
        props: {
            label: {
                type: String,
                default: ''
            },
            message: {
                type: [Array, Object, String],
                default: undefined
            },
            modelValue: {
                type: [Date, Array, null],
                required: true,
            },
            disabled: {
                type: Boolean,
                default: false
            },
            required: {
                type: Boolean,
                default: false,
            }
        },
        emits: [
            'update:modelValue',
        ],
    };
</script>
