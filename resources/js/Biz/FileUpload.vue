<template>
    <file-pond
        ref="pond"
        class-name="my-pond"
        :accepted-file-types="acceptedTypes"
        :allow-multiple="allowMultiple"
        :disabled="disabled"
        :label-idle="placeholder"
        :max-file-size="maxFileSize"
        :max-files="maxFiles"
        :max-total-file-size="maxTotalFileSize"
        :required="required"
        :drop-validation="true"
        @addfile="onAddFile"
        @updatefiles="onUpdateFiles"
        @removefile="onRemoveFile"
    />
</template>

<script>
    import FilePondPluginFileValidateSize from 'filepond-plugin-file-validate-size';
    import FilePondPluginFileValidateType from 'filepond-plugin-file-validate-type';
    import FilePondPluginImagePreview from 'filepond-plugin-image-preview';
    import vueFilePond from 'vue-filepond';
    import { filter } from 'lodash';

    import 'filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.css';
    import 'filepond/dist/filepond.min.css';

    const FilePond = vueFilePond(
        FilePondPluginFileValidateSize,
        FilePondPluginFileValidateType,
        FilePondPluginImagePreview
    );

    export default {
        name: 'FileUpload',

        components: {
            FilePond,
        },

        props: {
            acceptedTypes: { type: Array, default: null, },
            allowMultiple: { type: Boolean, default: false, },
            disabled: { type: Boolean, default: false, },
            maxFiles: { type: Number, default: 1, },
            maxFileSize: { type: [String, Number], default: '50MB', },
            maxTotalFileSize: { type: [String, Number], default: '50MB', },
            placeholder: { type: String, default: 'Drag & Drop your files or Browse', },
            required: { type: Boolean, default: false, },
        },

        emits: [
            'add-file',
            'add-files',
            'update-files',
        ],

        data() {
            return {
                addedFiles: [],
                errorFiles: [],
            };
        },

        methods: {
            addFiles(files) {
                this.$refs.pond.addFiles(files)
                    .catch(() => {
                        return ;
                    });
            },

            getFiles() {
                return this.$refs.pond.getFiles();
            },

            onUpdateFiles(files) {
                const self = this;
                let uploadFiles = [];

                files.forEach(function (file) {
                    uploadFiles.push(file.source);
                });

                self.$emit('update-files', uploadFiles);
                self.$emit('add-files', self.addedFiles);

                self.addedFiles = [];
            },

            reset() {
                this.$refs.pond.removeFiles();

                this.errorFiles = [];
            },

            onAddFile(error, file) {
                if (! error) {
                    this.addedFiles.push(file.file)
                } else {
                    this.errorFiles.push(file.file)
                }
            },

            onRemoveFile(error, file) {
                this.errorFiles = filter(
                    this.errorFiles,
                    function (errorFile) {
                        return errorFile.name !== file.source.name;
                    }
                );
            },
        },
    }
</script>

<style>
    .filepond--credits {
        display: none !important;
    }
</style>