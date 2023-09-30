<template>
    <div>
        <form-file-upload
            ref="file_upload"
            v-model="computedValue"
            :accepted-types="schema.accept"
            :allow-multiple="true"
            :disabled="schema.is_disabled"
            :label="schema.label"
            :max-files="schema.max_file_number"
            :max-file-size="schema.max_file_size"
            :media="schema.media"
            :is-filename-shown="false"
            :message="error(schema.name + '.files', bagName, errors)"
            :placeholder="schema.placeholder"
            :readonly="schema.is_readonly"
            :required="schema.is_required"
            :dimension="schema.dimension"
            @add-files="onAddFiles"
        >
            <template #default>
                <biz-button-icon
                    v-if="isImageEditorEnabled"
                    type="button"
                    class="is-small"
                    :icon="editIcon"
                    @click="openModal()"
                >
                    <span>
                        Open Image Editor
                    </span>
                </biz-button-icon>
            </template>

            <template #note>
                <biz-field-notes
                    type="info"
                    :notes="notes"
                />
            </template>
        </form-file-upload>

        <template v-if="isMultipleUpload">
            <biz-modal-card
                v-if="isModalOpen && isImageEditorEnabled"
                content-class="is-huge"
                :is-close-hidden="true"
                @close="cancelEditedFiles()"
            >
                <template #header>
                    <p class="modal-card-title">
                        Image Editor
                    </p>
                    <biz-button
                        aria-label="close"
                        class="delete is-primary"
                        type="button"
                        @click="cancelEditedFiles()"
                    />
                </template>

                <div
                    class="columns is-multiline is-mobile"
                    :class="{ 'is-centered': ! isMultipleUpload }"
                >
                    <template
                        v-for="(file, index) in computedValue.files"
                        :key="index"
                    >
                        <div
                            v-if="isImage(file)"
                            class="column"
                            :class="cardImageClasses"
                        >
                            <biz-file-drag-drop-detail
                                v-model:medium="computedValue.files[index]"
                                :dimension="schema.dimension"
                                :is-multiple-upload="isMultipleUpload"
                            />
                        </div>
                    </template>
                </div>

                <template #footer>
                    <div
                        class="columns"
                        style="width: 100%"
                    >
                        <div class="column">
                            <div class="buttons is-pulled-right">
                                <biz-button
                                    type="button"
                                    class="is-primary"
                                    @click="saveEditedFiles()"
                                >
                                    Save
                                </biz-button>

                                <biz-button
                                    class="ml-2"
                                    type="button"
                                    @click="cancelEditedFiles()"
                                >
                                    Cancel
                                </biz-button>
                            </div>
                        </div>
                    </div>
                </template>
            </biz-modal-card>
        </template>

        <template v-else>
            <biz-file-drag-drop-modal-image-editor
                v-if="isModalOpen && isImageEditorEnabled && isImage(computedValue.files[0])"
                v-model:medium="computedValue.files[0]"
                :dimension="schema.dimension"
                @on-update="saveEditedFiles()"
                @on-close="cancelEditedFiles()"
            />
        </template>
    </div>
</template>

<script>
    import MixinHasModal from '@/Mixins/HasModal';
    import MixinHasPageErrors from '@/Mixins/HasPageErrors';
    import BizButton from '@/Biz/Button.vue';
    import BizButtonIcon from '@/Biz/ButtonIcon.vue';
    import BizFieldNotes from '@/Biz/FieldNotes.vue';
    import BizFileDragDropDetail from '@/Biz/FileDragDropDetail.vue';
    import BizFileDragDropModalImageEditor from '@/Biz/FileDragDropModalImageEditor.vue';
    import BizModalCard from '@/Biz/ModalCard.vue';
    import FormFileUpload from '@/Biz/Form/FileUpload.vue';
    import { useModelWrapper } from '@/Libs/utils';
    import { cloneDeep, map } from 'lodash';
    import { edit as editIcon } from '@/Libs/icon-class';

    export default {
        name: 'FormFileDragDrop',

        components: {
            BizButton,
            BizButtonIcon,
            BizFieldNotes,
            BizFileDragDropDetail,
            BizFileDragDropModalImageEditor,
            BizModalCard,
            FormFileUpload,
        },

        mixins: [
            MixinHasModal,
            MixinHasPageErrors,
        ],

        inject: [
            'bagName',
        ],

        props: {
            errors: {
                type: Object,
                default: () => {}
            },
            modelValue: {
                type: [Object, null],
                default: null
            },
            schema: {
                type: Object,
                required: true
            },
        },

        setup(props, { emit }) {
            return {
                computedValue: useModelWrapper(props, emit),
            };
        },

        data() {
            return {
                editIcon,
                isModalPreviewOpen: false,
                previewImageSrc: null,
            };
        },

        computed: {
            notes() {
                return this.schema.instructions
                    .concat(this.schema.notes);
            },

            hasImage() {
                let hasImage = false;

                this.computedValue.files.forEach((file) => {
                    if (this.isImage(file)) {
                        hasImage = true;
                    }
                });

                return hasImage;
            },

            isMultipleUpload() {
                return (
                    this.schema.max_file_number > 1
                    && this.computedValue.files.length > 1
                );
            },

            isImageEditorEnabled() {
                return this.schema.is_image_editor_enabled
                    && this.hasImage;
            },

            cardImageClasses() {
                if (! this.isMultipleUpload) {
                    return "is-8";
                }

                return "is-3-desktop is-6-tablet is-12-mobile"
            },
        },

        methods: {
            reset() {
                this.$refs.file_upload
                    .$refs.file_upload.reset();
            },

            isImage(file) {
                return file.type.startsWith("image") ?? false;
            },

            saveEditedFiles() {
                const newFiles = cloneDeep(this.computedValue.files);

                this.reset();

                this.$refs.file_upload
                    .$refs.file_upload.addFiles(newFiles);

                setTimeout(() => {
                    this.closeModal();
                }, 200);
            },

            cancelEditedFiles() {
                let files = this.$refs.file_upload
                    .$refs.file_upload.getFiles();

                this.computedValue.files = map(files, 'file');

                this.closeModal();
            },

            onAddFiles(addedFiles) {
                if (addedFiles.length > 0) {
                    addedFiles.forEach((file) => {
                        if (this.isImage(file)) {
                            this.openModal();
                        }
                    });
                }
            },
        },
    };
</script>
