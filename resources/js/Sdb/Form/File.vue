<template>
    <sdb-form-field
        :is-required="required"
    >
        <template #label>
            {{ label }}
        </template>

        <sdb-input-file
            v-model="file"
            v-bind="$attrs"
            :accept="acceptedTypes"
            :disabled="disabled"
            :is-name-displayed="isNameDisplayed"
            @on-file-picked="$emit('on-file-picked', $event)"
        />

        <slot name="note" />

        <template #error>
            <sdb-input-error :message="message" />
        </template>
    </sdb-form-field>
</template>

<script>
    import SdbFormField from '@/Sdb/Form/Field';
    import SdbInputFile from '@/Sdb/InputFile';
    import SdbInputError from '@/Sdb/InputError';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'SdbFormFile',

        components: {
            SdbFormField,
            SdbInputFile,
            SdbInputError,
        },

        inheritAttrs: false,

        props: {
            acceptedTypes: {
                type: Array,
                default:() => [],
            },
            isNameDisplayed: {
                type: Boolean,
                default: true,
            },
            label: {
                type: String,
                default: null,
            },
            message: {
                type: Object,
                default: () => {},
            },
            modelValue: {
                type: [File, null],
                required: true,
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
            'on-file-picked',
            'update:modelValue',
        ],

        setup(props, { emit }) {
            return {
                file: useModelWrapper(props, emit),
            };
        },
    };
</script>
