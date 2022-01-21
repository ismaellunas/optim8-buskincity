<template>
    <biz-form-field
        :is-required="required"
    >
        <template #label>
            {{ label }}
        </template>

        <div class="control">
            <biz-textarea
                v-bind="$attrs"
                :class="{'is-danger': message}"
                :disabled="disabled"
                :value="modelValue"
                :required="required"
                @input="$emit('update:modelValue', $event.target.value)"
            />
        </div>

        <biz-input-error :message="message" />
    </biz-form-field>
</template>

<script>
    import BizFormField from '@/Biz/Form/Field';
    import BizInputError from '@/Biz/InputError';
    import BizTextarea from '@/Biz/Textarea';

    export default {
        name: 'BizFormTextarea',

        components: {
            BizFormField,
            BizInputError,
            BizTextarea,
        },

        inheritAttrs: false,

        props: {
            disabled: {
                type: Boolean,
                default: false
            },
            label: {
                type: String,
                default: ''
            },
            message: {
                type: [Array, Object, String],
                default: undefined,
            },
            modelValue: {
                type: [String, Number, null, undefined],
                required: true
            },
            required: {
                type: Boolean,
                default: false
            }
        },

        emits: ['update:modelValue'],
    };
</script>
