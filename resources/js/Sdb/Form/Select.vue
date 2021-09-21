<template>
    <sdb-form-field
        :is-required="required"
    >
        <template v-slot:label>{{ label }}</template>

        <sdb-select
            v-model="selected"
            :disabled="disabled"
            :placeholder="placeholder"
            :class="class"
        >
            <slot></slot>
        </sdb-select>

        <template v-slot:error>
            <sdb-input-error :message="message"/>
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
        props: {
            class: {},
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
        setup(props, { emit }) {
            return {
                selected: useModelWrapper(props, emit),
            };
        },
    };
</script>
