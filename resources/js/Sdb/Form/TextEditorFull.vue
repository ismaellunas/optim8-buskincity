<template>
    <div class="field">
        <sdb-label>{{ label }}</sdb-label>

        <div class="control">
            <sdb-text-editor
                v-model="editorValue"
                :disabled="disabled"
                :placeholder="placeholder"
                :config="config ?? editorConfig"
            />
        </div>

        <sdb-input-error :message="message"/>
    </div>

    <sdb-modal-image-browser
        v-if="isModalOpen"
        title="Image Library"
        :data="media"
        :is-download-enabled="isDownloadEnabled"
        :is-upload-enabled="isUploadEnabled"
        :query-params="imageListQueryParams"
        :search="search"
        :style="{zIndex: 1200}"
        @close="closeModal"
        @on-clicked-pagination="getImagesList"
        @on-media-selected="selectFile"
        @on-media-submitted="onMediaSubmitted"
        @on-view-changed="setView"
    />
</template>

<script>
    import MixinHasModal from '@/Mixins/HasModal';
    import MixinImageLibrary from '@/Mixins/MediaLibrary';
    import SdbInputError from '@/Sdb/InputError';
    import SdbLabel from '@/Sdb/Label';
    import SdbModalImageBrowser from '@/Sdb/Modal/ImageBrowser';
    import SdbTextEditor from '@/Sdb/EditorTinymce';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'SdbFormTextEditorFull',
        components: {
            SdbInputError,
            SdbLabel,
            SdbModalImageBrowser,
            SdbTextEditor,
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
        methods: {
            onShownModal() {/* @override Mixins/HasModal */
                this.tinyMceModal = document.querySelector(this.tinyMceModalSelector);
                this.tinyMceModal.style.display = 'none';

                this.setTerm('');
                this.getImagesList(route(this.imageListRouteName));
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
        },
        computed: {
            editorConfig() {
                const editorConfig = {
                    height: 500,
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
                    toolbar2: (
                        'alignleft aligncenter alignright alignjustify | ' +
                        'bullist numlist outdent indent hr | ' +
                        'anchor link table charmap code | '
                    ),
                    contextmenu: 'link image',
                    file_picker_types: 'image', //'file image media'
                    file_picker_callback: this.filePickerCallback,
                };

                editorConfig.toolbar1 = (
                    'fullscreen | formatselect | ' +
                    'bold italic underline strikethrough blockquote | ' +
                    'forecolor backcolor | ' +
                    'removeformat'
                );

                if (this.isMediaEnabled) {
                    editorConfig.toolbar1 += ' image';
                }

                return editorConfig;
            },
        }
    };
</script>
