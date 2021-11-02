<template>
    <sdb-field>
        <sdb-label>{{ label }}</sdb-label>

        <sdb-input-password
            ref="input"
            v-bind="$attrs"
            v-model="modelValue"
            :disabled="disabled"
            :has-error="hasError"
            :placeholder="placeholder"
            :required="required"
            @input="$emit('update:modelValue', $event.target.value)"
            @keypress="$emit('on-keypress', $event)"
        />

        <sdb-input-error :message="message"/>
    </sdb-field>
</template>

<script>
    import SdbField from '@/Sdb/Field';
    import SdbInputPassword from '@/Sdb/InputPassword';
    import SdbInputError from '@/Sdb/InputError';
    import SdbLabel from '@/Sdb/Label';

    export default {
        name: 'SdbFormPassword',
        components: {
            SdbField,
            SdbInputPassword,
            SdbInputError,
            SdbLabel,
        },
        inheritAttrs: false,
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
        emits: [
            'on-keypress',
            'update:modelValue',
        ],
        computed: {
            hasError() {
                return this.message ? true : false;
            },
        },
    };
</script>
