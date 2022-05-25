<template>
    <biz-form-field
        :is-required="required"
    >
        <template #label>
            {{ label }}
        </template>

        <div
            v-for="medium in listMedia"
            :key="medium.id"
            class="columns mb-0"
        >
            <div class="column is-full">
                <component
                    :is="mediaComponent"
                    :medium="medium"
                    @on-delete-clicked="deleteMedium($event)"
                />
            </div>
        </div>

        <div class="control">
            <file-pond
                v-if="maxFileNumber > 0"
                ref="pond"
                :key="filePondKey"
                name="file_upload"
                class-name="my-pond"
                :accepted-file-types="acceptedTypes"
                :allow-multiple="allowMultiple"
                :label-idle="placeholder"
                :max-files="maxFileNumber"
                :max-file-size="maxFileSizeUpload"
                :max-total-file-size="maxTotalFileSizeUpload"
                :required="isRequired"
                :disabled="disabled"
                @updatefiles="onUpdateFiles"
            />
        </div>


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
    import BizMediaTextItem from '@/Biz/Media/TextItem';
    import FilePondPluginFileValidateSize from 'filepond-plugin-file-validate-size';
    import FilePondPluginFileValidateType from "filepond-plugin-file-validate-type";
    import FilePondPluginImagePreview from "filepond-plugin-image-preview";
    import vueFilePond from "vue-filepond";
    import { confirmDelete } from '@/Libs/alert';
    import { replace } from 'lodash';
    import { useModelWrapper } from '@/Libs/utils';

    import "filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.css";
    import "filepond/dist/filepond.min.css";

    const FilePond = vueFilePond(
        FilePondPluginFileValidateSize,
        FilePondPluginFileValidateType,
        FilePondPluginImagePreview
    );

    export default {
        name: 'FileUpload',

        components: {
            BizFormField,
            BizInputError,
            BizMediaTextItem,
            FilePond,
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
            media: {
                type: Array,
                default:() => []
            },
            mediaComponent: {
                type: String,
                default: 'BizMediaTextItem',
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
            maxFileSize: {
                type: [Number, null],
                default: null
            },
            maxTotalFileSize: {
                type: [Number, null],
                default: null
            },
        },

        emits: [
            'on-file-picked',
        ],

        setup(props, { emit }) {
            return {
                fileUploadField: useModelWrapper(props, emit),
            };
        },

        data() {
            return {
                filePondKey: 0,
            };
        },

        computed: {
            hasMedia() {
                return this.media && this.media.length > 0;
            },

            maxFileNumber() {
                let mediaLength = 0;
                let deleteMedia = 0;

                if (this.hasMedia) {
                    mediaLength = this.media.length;
                }

                if (this.fileUploadField.deleted_media) {
                    deleteMedia = this.fileUploadField.deleted_media.length;
                }

                return this.maxFiles - mediaLength + deleteMedia;
            },

            maxFileSizeUpload() {
                return this.maxFileSize ? this.maxFileSize + `KB` : null;
            },

            maxTotalFileSizeUpload() {
                return this.maxTotalFileSize ? this.maxTotalFileSize + `KB` : null;
            },

            listMedia() {
                const self = this;

                return this.media.filter((medium) => {
                    return !self
                        .fileUploadField
                        .deleted_media
                        .includes(medium.id);
                });
            },

            isRequired() {
                return !this.hasMedia ? this.required : false;
            },
        },

        methods: {
            onUpdateFiles(files) {
                let tmpFiles = [];

                files.forEach(function (file) {
                    tmpFiles.push(file.source);
                });

                this.fileUploadField.files = tmpFiles;
            },

            deleteMedium(medium) {
                const self = this;

                confirmDelete().then((result) => {
                    if (result.isConfirmed) {
                        self.fileUploadField.deleted_media.push(medium.id);
                    }
                });
            },

            reset() {
                this.filePondKey += 1;
            },
        },
    }
</script>