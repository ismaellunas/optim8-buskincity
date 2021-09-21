<template>
    <sdb-form-field :is-required="required">
        <template v-slot:label>{{ label }}</template>

        <sdb-date-time
            v-model="modelValue"
            :disabled="disabled"
            @input="$emit('update:modelValue', $event)"
        />

        <template v-slot:error>
            <sdb-input-error :message="message"/>
        </template>
    </sdb-form-field>
</template>

<script>
    import SdbDateTime from '@/Sdb/DateTime';
    import SdbFormField from '@/Sdb/Form/Field';
    import SdbInputError from '@/Sdb/InputError';

    export default {
        name: 'SdbFormDateTime',
        components: {
            SdbFormField,
            SdbDateTime,
            SdbInputError,
        },
        emits: [
            'update:modelValue',
        ],
        props: {
            label: {
                type: String
            },
            message: {
                type: Object,
            },
            modelValue: Date|Array|null,
            disabled: {
                type: Boolean,
                default: false
            },
            required: {
                type: Boolean,
                default: false,
            }
        },
    };
</script>
