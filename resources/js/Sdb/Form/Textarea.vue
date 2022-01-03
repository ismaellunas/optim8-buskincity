<template>
    <sdb-form-field
        :is-required="required"
    >
        <template #label>
            {{ label }}
        </template>

        <div class="control">
            <sdb-textarea
                v-bind="$attrs"
                :class="{'is-danger': message}"
                :disabled="disabled"
                :value="modelValue"
                :required="required"
                @input="$emit('update:modelValue', $event.target.value)"
            />
        </div>

        <sdb-input-error :message="message" />
    </sdb-form-field>
</template>

<script>
    import SdbFormField from '@/Sdb/Form/Field';
    import SdbInputError from '@/Sdb/InputError';
    import SdbTextarea from '@/Sdb/Textarea';

    export default {
        name: 'SdbFormTextarea',

        components: {
            SdbFormField,
            SdbInputError,
            SdbTextarea,
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
