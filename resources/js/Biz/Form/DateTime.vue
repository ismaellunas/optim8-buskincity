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
                type: String
            },
            message: {
                type: Object,
            },
            modelValue: Date|Array|null,
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
