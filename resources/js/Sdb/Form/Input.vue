<template>
    <sdb-form-field>
        <template v-slot:label>{{ label }}</template>

        <sdb-input
            :disabled="disabled"
            :placeholder="placeholder"
            :value="modelValue"
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
        components: {
            SdbFormField,
            SdbInput,
            SdbInputError,
        },
        emits: ['update:modelValue', 'on-keypress', 'on-blur'],
        props: ['label', 'message', 'modelValue', 'placeholder', 'disabled', 'required'],
    }
</script>
