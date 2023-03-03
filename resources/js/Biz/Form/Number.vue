<template>
    <biz-form-field
        :is-required="required"
        :label-class="{'is-size-7': isSmall}"
    >
        <template #label>
            {{ label }}
        </template>

        <div class="control">
            <biz-number
                v-bind="$attrs"
                :class="{'is-danger': message, 'is-small': isSmall}"
                :disabled="disabled"
                :required="required"
                :value="modelValue"
                @input="$emit('update:modelValue', $event.target.value)"
            />
        </div>

        <slot name="note" />

        <template #error>
            <biz-input-error :message="message" />
        </template>
    </biz-form-field>
</template>

<script>
    import BizFormField from '@/Biz/Form/Field.vue';
    import BizNumber from '@/Biz/Number.vue';
    import BizInputError from '@/Biz/InputError.vue';

    export default {
        name: 'BizFormNumber',

        components: {
            BizFormField,
            BizNumber,
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
            isSmall: {
                type: Boolean,
                default: false
            },
        },

        emits: [
            'update:modelValue',
        ],
    };
</script>
