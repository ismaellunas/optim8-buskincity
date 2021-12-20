<template>
    <sdb-text-editor
        v-model="editorValue"
        :config="config ?? editorConfig"
        class="p-1"
    />

    <sdb-modal-image-browser
        v-if="isModalOpen"
        title="Image Library"
        :data="media"
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
    import SdbModalImageBrowser from '@/Sdb/Modal/ImageBrowser';
    import SdbTextEditor from '@/Sdb/EditorTinymce';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'SdbFormTextEditorFullInline',
        components: {
            SdbModalImageBrowser,
            SdbTextEditor,
        },
        mixins: [
            MixinHasModal,
            MixinImageLibrary,
        ],
        props: {
            config: Object,
            modelValue: {},
        },
        emits: ['update:modelValue'],
        setup(props, { emit }) {
            return {
                editorValue: useModelWrapper(props, emit),
            };
        },
        data() {
            return {
                editorConfig: {
                    inline: true,
                    menubar: false,
                    branding: false,
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
                        'removeformat image'
                    ),
                    toolbar2: (
                        'alignleft aligncenter alignright alignjustify | ' +
                        'bullist numlist outdent indent hr | ' +
                        'anchor link table charmap code | '
                    ),
                    contextmenu: 'link image',
                    file_picker_types: 'image', //'file image media'
                    file_picker_callback: this.filePickerCallback,
                },
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
    };
</script>
