<template>
    <sdb-form-field
        :is-required="required"
    >
        <template #label>
            {{ label }}
        </template>

        <sdb-input-file
            v-model="file"
            :accept="acceptedTypes"
            :disabled="disabled"
            :is-name-displayed="isNameDisplayed"
            @on-file-picked="$emit('onFilePicked')"
        />

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
                default: false,
            },
            label: {
                type: String,
                default: null,
            },
            message: {
                type: Object,
                default: () => {},
            },
            modelValue: {},
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
            'onFilePicked',
            'update:modelValue',
        ],
        setup(props, { emit }) {
            return {
                file: useModelWrapper(props, emit),
            };
        },
    };
</script>
