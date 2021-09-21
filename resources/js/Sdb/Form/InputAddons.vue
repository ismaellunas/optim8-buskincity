<template>
    <sdb-field>
        <sdb-label :is-required="required">{{ label }}</sdb-label>

        <sdb-field class="has-addons mb-0">
            <div class="control is-expanded">
                <sdb-input
                    :disabled="disabled"
                    :placeholder="placeholder"
                    :required="required"
                    :value="modelValue"
                    @input="$emit('update:modelValue', $event.target.value)"
                    @keypress="$emit('on-keypress', $event)"
                    @blur="$emit('on-blur', $event)"
                />
            </div>

            <slot name="afterInput"></slot>
        </sdb-field>

        <sdb-input-error :message="message"/>
    </sdb-field>
</template>

<script>
    import SdbField from '@/Sdb/Field';
    import SdbInput from '@/Sdb/Input';
    import SdbInputError from '@/Sdb/InputError';
    import SdbLabel from '@/Sdb/Label';

    export default {
        name: 'SdbFormInputAddons',
        components: {
            SdbField,
            SdbInput,
            SdbInputError,
            SdbLabel,
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
