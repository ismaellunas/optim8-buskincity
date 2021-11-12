<template>
    <sdb-form-field
        :is-required="required"
    >
        <template #label>
            {{ label }}
        </template>

        <sdb-dropdown-search
            v-bind="$attrs"
            :disabled="disabled"
            :placeholder="placeholder"
        >
            <template #trigger>
                <slot name="trigger" />
            </template>

            <slot />
        </sdb-dropdown-search>

        <template #error>
            <sdb-input-error :message="message" />
        </template>
    </sdb-form-field>
</template>

<script>
    import SdbFormField from '@/Sdb/Form/Field';
    import SdbInputError from '@/Sdb/InputError';
    import SdbDropdownSearch from '@/Sdb/DropdownSearch';

    export default {
        name: 'SdbFormDropdownSearch',

        components: {
            SdbFormField,
            SdbInputError,
            SdbDropdownSearch,
        },

        inheritAttrs: false,

        props: {
            disabled: {
                type: Boolean,
                default: false
            },
            label: {
                type: String,
                required: true,
            },
            message: {
                type: Object,
                default: () => {},
            },
            placeholder: {
                type: String,
                default: "Search ..."
            },
            required: {
                type: Boolean,
                default: false
            },
        },
    };
</script>
