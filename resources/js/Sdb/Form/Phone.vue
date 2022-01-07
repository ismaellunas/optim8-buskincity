<template>
    <sdb-form-field
        :is-required="required"
    >
        <template #label>
            {{ label }}
        </template>

        <div class="control">
            <sdb-phone
                v-bind="$attrs"
                :class="{'is-danger': message}"
                :disabled="disabled"
                :required="required"
                :value="modelValue"
                @input="$emit('update:modelValue', $event.target.value)"
            />
        </div>

        <template #error>
            <sdb-input-error :message="message" />
        </template>
    </sdb-form-field>
</template>

<script>
    import SdbFormField from '@/Sdb/Form/Field';
    import SdbPhone from '@/Sdb/Phone';
    import SdbInputError from '@/Sdb/InputError';

    export default {
        name: 'SdbFormPhone',

        components: {
            SdbFormField,
            SdbPhone,
            SdbInputError,
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
        },

        emits: [
            'update:modelValue',
        ],
    };
</script>
