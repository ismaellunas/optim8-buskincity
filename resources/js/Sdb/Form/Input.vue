<template>
    <sdb-form-field
        :is-required="required"
    >
        <template v-slot:label>{{ label }}</template>

        <sdb-input
            v-bind="$attrs"
            :disabled="disabled"
            :required="required ? true : false"
            :value="modelValue"
            :class="{'is-danger': message}"
            @input="$emit('update:modelValue', $event.target.value)"
            @keypress="$emit('on-keypress', $event)"
            @blur="$emit('on-blur', $event)"
        />

        <template v-slot:error>
            <sdb-input-error :message="message"/>
        </template>
    </sdb-form-field>
</template>

<script>
    import SdbFormField from '@/Sdb/Form/Field';
    import SdbInput from '@/Sdb/Input';
    import SdbInputError from '@/Sdb/InputError';

    export default {
        name: 'SdbFormInput',
        inheritAttrs: false,
        components: {
            SdbFormField,
            SdbInput,
            SdbInputError,
        },
        emits: [
            'update:modelValue',
            'on-keypress',
            'on-blur'
        ],
        props: {
            label: {
                type: String
            },
            message: {
                type: Object,
            },
            modelValue: {
                type: [String, Number]
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
    };
</script>
