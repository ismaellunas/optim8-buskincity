<template>
    <biz-form-field :is-required="required">
        <template #label>
            {{ label }}
        </template>

        <biz-date-time
            v-model="dateValue"
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
    import { ref } from 'vue';

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
                default: null,
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
        setup(props) {
            return {
                dateValue: ref(props.modelValue),
            };
        },
    };
</script>
