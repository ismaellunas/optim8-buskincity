<template>
    <biz-form-field :is-required="required">
        <template #label>
            {{ label }}
        </template>

        <biz-date-time
            v-model="dateValue"
            v-bind="$attrs"
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
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'BizFormDateTime',

        components: {
            BizDateTime,
            BizFormField,
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
                type: [String, Date, Array, null],
                required: true,
                default: null,
            },
            required: {
                type: Boolean,
                default: false,
            }
        },

        emits: [
            'update:modelValue',
        ],

        setup(props, { emit }) {
            return {
                dateValue: useModelWrapper(props, emit),
            };
        },
    };
</script>
