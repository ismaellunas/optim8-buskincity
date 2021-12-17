<template>
    <sdb-form-field
        :is-required="required"
    >
        <template #label>
            {{ label }}
        </template>

        <sdb-select
            v-model="selected"
            v-bind="$attrs"
            :disabled="disabled"
            :placeholder="placeholder"
        >
            <slot />
        </sdb-select>

        <template #error>
            <sdb-input-error :message="message" />
        </template>
    </sdb-form-field>
</template>

<script>
    import SdbFormField from '@/Sdb/Form/Field';
    import SdbInputError from '@/Sdb/InputError';
    import SdbSelect from '@/Sdb/Select';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'SdbFormSelect',

        components: {
            SdbFormField,
            SdbInputError,
            SdbSelect,
        },

        inheritAttrs: false,

        props: {
            disabled: {
                type: Boolean,
                default: false
            },
            label: {
                type: String,
                default: ""
            },
            message: {
                type: Object,
                default: () => {}
            },
            modelValue: {
                type: [Object, Array, String, Number, Boolean, null],
                required: true
            },
            placeholder: {
                type: String,
                default: ""
            },
            required: {
                type: Boolean,
                default: false
            },
        },
        setup(props, { emit }) {
            return {
                selected: useModelWrapper(props, emit),
            };
        },
    };
</script>
