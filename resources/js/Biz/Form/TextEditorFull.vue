<template>
    <div>
        <div class="field">
            <biz-label>{{ label }}</biz-label>

            <div class="control">
                <biz-text-editor
                    v-model="editorValue"
                    :disabled="disabled"
                    :placeholder="placeholder"
                    :config="config ?? editorConfig"
                />
            </div>

            <biz-input-error :message="message" />
        </div>

        <biz-modal-image-browser
            v-if="isModalOpen"
            title="Media Library"
            :data="media"
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
    </div>
</template>

<script>
    import MixinHasModal from '@/Mixins/HasModal';
    import MixinImageLibrary from '@/Mixins/MediaLibrary';
    import BizInputError from '@/Biz/InputError';
    import BizLabel from '@/Biz/Label';
    import BizModalImageBrowser from '@/Biz/Modal/ImageBrowser';
    import BizTextEditor from '@/Biz/EditorTinymce';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'BizFormTextEditorFull',

        components: {
            BizInputError,
            BizLabel,
            BizModalImageBrowser,
            BizTextEditor,
        },

        mixins: [
            MixinHasModal,
            MixinImageLibrary,
        ],

        props: {
            config: Object,
            disabled: {type: Boolean, default: false},
            isDownloadEnabled: {type: Boolean, default: true},
            isMediaEnabled: {type: Boolean, default: true},
            isUploadEnabled: {type: Boolean, default: true},
            label: String,
            message: {},
            modelValue: {},
            placeholder: String,
            height: {type: Number, default: 500},
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
                    height: this.height,
                    menubar: false,
                    plugins: [
                        'advlist autolink lists link image charmap print preview ' +
                        'anchor searchreplace visualblocks code fullscreen',
                        'insertdatetime media table paste code wordcount hr code'
                    ],
                    block_formats: (
                        'Paragraph=p; '+
                        'Header 1=h1; '+
                        'Header 2=h2; '+
                        'Header 3=h3'
                    ),
                    toolbar1: (
                        'fullscreen | formatselect | ' +
                        'bold italic underline strikethrough blockquote | ' +
                        'forecolor backcolor | ' +
                        'removeformat image media'
                    ),
                    toolbar2: (
                        'alignleft aligncenter alignright alignjustify | ' +
                        'bullist numlist outdent indent hr | ' +
                        'anchor link table charmap code | '
                    ),
                    contextmenu: 'link image',
                    file_picker_types: 'image media', //'file image media'
                    file_picker_callback: (
                        this.isMediaEnabled
                            ? this.filePickerCallback
                            : false
                    ),
                    media_live_embeds: true,
                };

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
                this.selectFile(response.data);
            },
            filePickerCallback(callback, value, meta) {
                const self = this;

                self.addTypeOnQueryParams(meta.filetype);

                self.openModal();

                self.tinyMceImage.element = document.createElement('input');

                self.tinyMceImage.element.onclick = function () {
                    callback(
                        self.tinyMceImage.file.file_url,
                        { alt: self.tinyMceImage.file?.alt ?? '' }
                    );

                    self.tinyMceImage.file = null;
                    self.tinyMceImage.element.remove();
                    self.tinyMceImage.element = null;
                };
            },
            addTypeOnQueryParams(fileType) {
                if (fileType === "media") {
                    this.setType(['video']);
                } else {
                    this.setType([fileType]);
                };
            },
        },
    };
</script>