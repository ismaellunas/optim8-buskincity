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
    />
</template>

<script>
    import FilePondPluginFileValidateSize from 'filepond-plugin-file-validate-size';
    import FilePondPluginFileValidateType from 'filepond-plugin-file-validate-type';
    import FilePondPluginImagePreview from 'filepond-plugin-image-preview';
    import vueFilePond from 'vue-filepond';

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
                let uploadFiles = [];

                files.forEach(function (file) {
                    uploadFiles.push(file.source);
                });

                this.$emit('update-files', uploadFiles);
                this.$emit('add-files', this.addedFiles);

                this.addedFiles = [];
            },

            reset() {
                this.$refs.pond.removeFiles();
            },

            onAddFile(error, file) {
                this.addedFiles.push(file.file)
            },
        },
    }
</script>

<style>
    .filepond--credits {
        display: none !important;
    }
</style>