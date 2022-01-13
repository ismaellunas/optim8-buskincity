<template>
    <biz-field>
        <biz-label :is-required="required">
            {{ label }}
        </biz-label>

        <biz-field class="has-addons mb-0">
            <div class="control is-expanded">
                <biz-input
                    :disabled="disabled"
                    :placeholder="placeholder"
                    :required="required"
                    :value="modelValue"
                    @input="$emit('update:modelValue', $event.target.value)"
                    @keypress="$emit('on-keypress', $event)"
                    @blur="$emit('on-blur', $event)"
                />
            </div>

            <slot name="afterInput" />
        </biz-field>

        <biz-input-error :message="message" />
    </biz-field>
</template>

<script>
    import BizField from '@/Biz/Field';
    import BizInput from '@/Biz/Input';
    import BizInputError from '@/Biz/InputError';
    import BizLabel from '@/Biz/Label';

    export default {
        name: 'BizFormInputAddons',
        components: {
            BizField,
            BizInput,
            BizInputError,
            BizLabel,
        },
        emits: [
            'on-blur',
            'on-keypress',
            'update:modelValue',
        ],
        props: {
            disabled: {
                type: Boolean,
                default: false
            },
            label: String,
            message: Object|null,
            modelValue: {},
            placeholder: String,
            required: {
                type: Boolean,
                default: false
            },
        },
    };
</script>
