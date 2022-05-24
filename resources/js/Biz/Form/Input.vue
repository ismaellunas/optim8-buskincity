<template>
    <biz-form-field
        :class="fieldClass"
        :has-left-icon="hasLeftIcon"
        :is-required="required"
    >
        <template
            v-if="label"
            #label
        >
            {{ label }}
        </template>

        <slot name="leftIcons" />

        <biz-input
            ref="input"
            v-bind="$attrs"
            :class="{'is-danger': message}"
            :disabled="disabled"
            :required="required"
            :value="modelValue"
            @input="$emit('update:modelValue', $event.target.value)"
            @keypress="$emit('on-keypress', $event)"
            @blur="$emit('on-blur', $event)"
        />

        <slot name="note" />

        <template #error>
            <biz-input-error :message="message" />
        </template>
    </biz-form-field>
</template>

<script>
    import BizFormField from '@/Biz/Form/Field';
    import BizInput from '@/Biz/Input';
    import BizInputError from '@/Biz/InputError';

    export default {
        name: 'BizFormInput',

        components: {
            BizFormField,
            BizInput,
            BizInputError,
        },

        inheritAttrs: false,

        props: {
            fieldClass: {
                type: [Object, Array, String],
                default: undefined,
            },
            label: {
                type: String,
                default: ''
            },
            message: {
                type: [Array, Object, String],
                default: undefined
            },
            modelValue: {
                type: [String, Number, null],
                required: true
            },
            disabled: {
                type: Boolean,
                default: false
            },
            required: {
                type: Boolean,
                default: false
            },
        },

        emits: [
            'update:modelValue',
            'on-keypress',
            'on-blur'
        ],

        computed: {
            hasLeftIcon() {
                return !!this.$slots.leftIcons;
            },
        },
    };
</script>
