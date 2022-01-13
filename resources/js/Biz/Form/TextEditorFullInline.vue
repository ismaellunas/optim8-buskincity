<template>
    <div>
        <biz-text-editor
            v-model="editorValue"
            :config="config ?? editorConfig"
            class="p-1"
        />

        <biz-modal-media-browser
            v-if="isModalOpen"
            title="Media Library"
            :accepted-file-type="acceptedTypes"
            :data="media"
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
    import BizModalMediaBrowser from '@/Biz/Modal/MediaBrowser';
    import BizTextEditor from '@/Biz/EditorTinymce';
    import { acceptedImageTypes, acceptedVideoTypes } from '@/Libs/defaults';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'BizFormTextEditorFullInline',
        components: {
            BizModalMediaBrowser,
            BizTextEditor,
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
                        'removeformat image media'
                    ),
                    toolbar2: (
                        'alignleft aligncenter alignright alignjustify | ' +
                        'bullist numlist outdent indent hr | ' +
                        'anchor link table charmap code | '
                    ),
                    contextmenu: 'link image',
                    file_picker_types: 'image media', //'file image media'
                    file_picker_callback: this.filePickerCallback,
                    media_live_embeds: true,
                },
                tinyMceImage: {
                    file: null,
                    element: null,
                },
                tinyMceModal: null,
                tinyMceModalSelector: 'div.tox.tox-silver-sink.tox-tinymce-aux > div',
                fileType: null,
            };
        },
        computed: {
            acceptedTypes() {
                switch (this.fileType) {
                case "media":
                    return acceptedVideoTypes;
                    break;

                default:
                    return acceptedImageTypes;
                    break;
                }
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
                this.fileType = fileType;

                switch (this.fileType) {
                case "media":
                    this.setType(['video']);
                    break;

                default:
                    this.setType([fileType]);
                    break;
                }
            },
        },
    };
</script>
