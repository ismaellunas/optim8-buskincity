<template>
    <biz-form-field
        :is-required="required"
    >
        <template #label>
            {{ label }}
        </template>

        <file-pond
            ref="pond"
            name="file_upload"
            class-name="my-pond"
            :accepted-file-types="acceptedFileTypes"
            :allow-multiple="allowMultiple"
            :label-idle="placeholder"
            :max-files="maxFiles"
            :required="required"
            @updatefiles="onUpdateFiles"
        />

        <slot name="note">
            <p
                v-if="notes"
                class="help is-info"
            >
                <ul>
                    <li
                        v-for="note, index in notes"
                        :key="index"
                    >
                        {{ note }}
                    </li>
                </ul>
            </p>
        </slot>

        <template #error>
            <biz-input-error :message="message" />
        </template>
    </biz-form-field>
</template>

<script>
    import BizFormField from '@/Biz/Form/Field';
    import BizInputError from '@/Biz/InputError';
    import vueFilePond from "vue-filepond";
    import FilePondPluginFileValidateType from "filepond-plugin-file-validate-type";
    import FilePondPluginImagePreview from "filepond-plugin-image-preview";
    import { useModelWrapper } from '@/Libs/utils';
    import { replace } from 'lodash';

    import "filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.css";
    import "filepond/dist/filepond.min.css";

    const FilePond = vueFilePond(
        FilePondPluginFileValidateType,
        FilePondPluginImagePreview
    );

    export default {
        name: 'FileUpload',

        components: {
            FilePond,
            BizFormField,
            BizInputError,
        },

        inheritAttrs: false,

        props: {
            acceptedTypes: {
                type: Array,
                default:() => [],
            },
            notes: {
                type: Array,
                default: () => [],
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
                type: [Object, null],
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
            placeholder: {
                type: String,
                default: 'Drop files here...'
            },
            allowMultiple: {
                type: Boolean,
                default: false,
            },
            maxFiles: {
                type: Number,
                default: 1
            },
        },

        emits: [
            'on-file-picked',
            'update:modelValue',
        ],

        setup(props, { emit }) {
            const acceptedTypes = [];

            props.acceptedTypes.forEach(function (type) {
                acceptedTypes.push(replace(type, '.', 'image/'));
            });

            return {
                files: useModelWrapper(props, emit),
                acceptedFileTypes: acceptedTypes.toString()
            };
        },

        methods: {
            onUpdateFiles(files) {
                let tmpFiles = [];

                files.forEach(function (file) {
                    tmpFiles.push(file.source);
                });

                this.$emit('update:modelValue', tmpFiles);
            },
        },
    }
</script>