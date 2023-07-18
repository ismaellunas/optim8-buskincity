<template>
    <biz-form-field
        :class="fieldClass"
    >
        <template
            v-if="label"
            #label
        >
            {{ label }}
        </template>

        <div class="control">
            <biz-text-editor
                v-model="editorValue"
                :disabled="disabled"
                :placeholder="placeholder"
                :config="editorConfig"
            />
        </div>

        <slot name="note" />

        <template #error>
            <biz-input-error :message="message" />
        </template>

        <biz-modal-media-browser
            v-if="isModalOpen"
            title="Media Library"
            :accepted-file-type="acceptedTypes"
            :data="media"
            :instructions="mediaLibraryInstructions"
            :is-download-enabled="isDownloadEnabled"
            :is-upload-enabled="isUploadEnabled"
            :query-params="mediaListQueryParams"
            :search="search"
            :style="{zIndex: 1300}"
            @close="closeModal"
            @on-clicked-pagination="getMediaList"
            @on-media-selected="selectFile"
            @on-media-submitted="onMediaSubmitted"
            @on-view-changed="setView"
        />
    </biz-form-field>
</template>

<script>
    import MixinHasModal from '@/Mixins/HasModal';
    import MixinMediaLibrary from '@/Mixins/MediaLibrary';
    import MixinMediaTextEditor from '@/Mixins/MediaTextEditor';
    import BizFormField from '@/Biz/Form/Field.vue';
    import BizInputError from '@/Biz/InputError.vue';
    import BizModalMediaBrowser from '@/Biz/Modal/MediaBrowser.vue';
    import BizTextEditor from '@/Biz/EditorTinymce.vue';
    import { fullConfig } from '@/Libs/tinymce-configs';
    import { useModelWrapper, isEmpty } from '@/Libs/utils';

    export default {
        name: 'BizFormTextEditorFull',

        components: {
            BizFormField,
            BizInputError,
            BizModalMediaBrowser,
            BizTextEditor,
        },

        mixins: [
            MixinHasModal,
            MixinMediaLibrary,
            MixinMediaTextEditor,
        ],

        props: {
            config: {type: Object, default: () => {}},
            disabled: {type: Boolean, default: false},
            fieldClass: { type: [Object, Array, String], default: undefined },
            height: {type: Number, default: 500},
            isConfigCombined: {type: Boolean, default: false},
            isDownloadEnabled: {type: Boolean, default: true},
            isMediaEnabled: {type: Boolean, default: true},
            isUploadEnabled: {type: Boolean, default: true},
            label: { type: String, default: null },
            message: { type: [String, Array], default: undefined },
            modelValue: { type: [String, null], required: true },
            placeholder: { type: String, default: null },
        },

        emits: ['update:modelValue'],

        setup(props, { emit }) {
            return {
                editorValue: useModelWrapper(props, emit),
            };
        },

        data() {
            return {
                tinyMceImage: {
                    file: null,
                    element: null,
                },
                tinyMceModal: null,
                tinyMceModalSelector: 'div.tox.tox-silver-sink.tox-tinymce-aux > div',
            };
        },

        computed: {
            editorConfig() {
                const editorConfig = {
                    ...fullConfig,
                    ...{
                        height: this.height,
                        contextmenu: 'link image',
                        file_picker_types: 'image media', //'file image media'
                        file_picker_callback: (
                            this.isMediaEnabled
                                ? this.filePickerCallback
                                : false
                        ),
                        media_live_embeds: true,
                    }
                };

                if (this.isConfigCombined) {
                    return Object.assign(editorConfig, this.config)
                }

                if (!isEmpty(this.config)) {
                    return this.config;
                }

                return editorConfig;
            },
        },

        methods: {
            onShownModal() {/* @override Mixins/HasModal */
                this.tinyMceModal = document.querySelector(this.tinyMceModalSelector);
                this.tinyMceModal.style.display = 'none';

                this.setTerm('');
                this.getMediaList(route(this.mediaListRouteName));
            },
            onCloseModal() { /* @override Mixins/HasModal */
                if (this.tinyMceModal) {
                    this.tinyMceModal.style.display = '';
                }
            },
            selectFile(file) {
                this.tinyMceImage.file = file;

                if (this.tinyMceImage.element) {
                    this.tinyMceImage.element.click();
                }
                this.closeModal();
            },
            onMediaSubmitted(response) {
                this.selectFile(response.data[0]);
            },
            filePickerCallback(callback, value, meta) {
                const self = this;

                self.setTypeToMedia(meta.filetype);

                self.setInstructions(meta.filetype);

                self.openModal();

                self.tinyMceImage.element = document.createElement('input');

                self.tinyMceImage.element.onclick = function () {
                    callback(
                        self.tinyMceImage.file?.optimize_file_url ?? self.tinyMceImage.file?.file_url,
                        { alt: self.tinyMceImage.file?.alt ?? '' }
                    );

                    self.tinyMceImage.file = null;
                    self.tinyMceImage.element.remove();
                    self.tinyMceImage.element = null;
                };
            },
        },
    };
</script>