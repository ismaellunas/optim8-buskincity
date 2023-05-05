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
    import MixinMediaLibrary from '@/Mixins/MediaLibrary';
    import MixinMediaTextEditor from '@/Mixins/MediaTextEditor';
    import BizModalMediaBrowser from '@/Biz/Modal/MediaBrowser.vue';
    import BizTextEditor from '@/Biz/EditorTinymce.vue';
    import { fullConfig } from '@/Libs/tinymce-configs';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'BizFormTextEditorFullInline',
        components: {
            BizModalMediaBrowser,
            BizTextEditor,
        },
        mixins: [
            MixinHasModal,
            MixinMediaLibrary,
            MixinMediaTextEditor,
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
                    ...fullConfig,
                    ...{
                        inline: true,
                        branding: false,
                        contextmenu: 'link image',
                        file_picker_types: 'image media', //'file image media'
                        file_picker_callback: this.filePickerCallback,
                        media_live_embeds: true,
                    }
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
