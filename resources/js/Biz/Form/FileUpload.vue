<template>
    <biz-form-field
        :is-required="required"
    >
        <template #label>
            {{ label }}
        </template>

        <component
            :is="mediaComponent"
            :media="computedListMedia"
            :is-edit-enabled="true"
            :is-scrolled="true"
            :is-filename-shown="isFilenameShown"
            @on-delete-clicked="onDeleteMedium"
            @on-edit-clicked="onEditMedium"
        />

        <div class="control">
            <biz-file-upload
                v-if="maxFileNumber > 0"
                ref="file_upload"
                :accepted-types="acceptedTypes"
                :allow-multiple="allowMultiple"
                :disabled="disabled"
                :max-file-size="maxFileSizeUpload"
                :max-files="maxFileNumber"
                :max-total-file-size="maxTotalFileSizeUpload"
                :placeholder="placeholder"
                :required="isRequired"
                @on-update-files="onUpdateFiles"
                @on-add-file="$emit('on-add-file')"
            />
        </div>

        <slot />

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

        <biz-file-drag-drop-modal-image-editor
            v-if="isModalOpen"
            v-model:medium="editedMedium.image"
            :medium-url="editedMedium.image.file_url"
            :dimension="dimension"
            @on-close="closeModal()"
            @on-update="saveEditedFile()"
        />
    </biz-form-field>
</template>

<script>
    import MixinHasLoader from '@/Mixins/HasLoader';
    import MixinHasModal from '@/Mixins/HasModal';
    import BizFileUpload from '@/Biz/FileUpload.vue';
    import BizFormField from '@/Biz/Form/Field.vue';
    import BizInputError from '@/Biz/InputError.vue';
    import BizMediaGallery from '@/Biz/Media/Gallery.vue';
    import BizMediaText from '@/Biz/Media/Text.vue';
    import BizFileDragDropModalImageEditor from '@/Biz/FileDragDropModalImageEditor.vue';
    import { confirmDelete, oops as oopsAlert, success as successAlert } from '@/Libs/alert';
    import { useModelWrapper } from '@/Libs/utils';
    import { cloneDeep } from 'lodash';
    import { serialize } from 'object-to-formdata';

    export default {
        name: 'FileUpload',

        components: {
            BizFileUpload,
            BizFormField,
            BizInputError,
            BizMediaGallery,
            BizMediaText,
            BizFileDragDropModalImageEditor,
        },

        mixins: [
            MixinHasLoader,
            MixinHasModal,
        ],

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
            isFilenameShown: {
                type: Boolean,
                default: true,
            },
            mediaComponent: {
                type: String,
                default: 'BizMediaGallery',
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
                type: [Number, String],
                default: 1
            },
            maxFileSize: {
                type: [Number, String, null],
                default: null
            },
            maxTotalFileSize: {
                type: [Number, String, null],
                default: null
            },
            dimension: { type: Object, default: () => {} },
        },

        emits: [
            'on-add-file',
            'on-file-picked',
            'on-update-files',
        ],

        setup(props, { emit }) {
            return {
                fileUploadField: useModelWrapper(props, emit),
            };
        },

        data() {
            return {
                editedMedium: {},
                listMedia: this.media,
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

            computedListMedia() {
                const self = this;

                return this.listMedia.filter((medium) => {
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

        watch: {
            media(newData) {
                this.listMedia = newData;
            }
        },

        methods: {
            async onUpdateFiles(files) {
                this.fileUploadField.files = await Promise.all(files);

                this.$emit('on-update-files');
            },

            onDeleteMedium(medium) {
                const self = this;

                confirmDelete().then((result) => {
                    if (result.isConfirmed) {
                        self.fileUploadField.deleted_media.push(medium.id);
                    }
                });
            },

            onEditMedium(medium) {
                this.openModal();

                this.editedMedium.image = medium;
                this.editedMedium.media_id = medium.id;
            },

            async saveEditedFile() {
                const self = this;
                let editedFile = cloneDeep(self.editedMedium);

                editedFile.image = await editedFile.image;

                self.closeModal();

                self.onStartLoadingOverlay();

                axios.post(
                    route('admin.api.media.replace'),
                    serialize(editedFile)
                )
                    .then((response) => {
                        let data = response.data;

                        successAlert(data.message);

                        self.listMedia = self.listMedia.map((medium) => {
                            if (medium.id === data.media.id) {
                                return data.media;
                            }

                            return medium;
                        });
                    })
                    .catch((error) => {
                        console.log(error);
                        oopsAlert();
                    })
                    .then(() => {
                        self.onEndLoadingOverlay();
                    });
            },

            onCloseModal() {
                this.editedMedium = {};
            },

            reset() {
                this.$refs.file_upload.reset();
            },
        },
    }
</script>