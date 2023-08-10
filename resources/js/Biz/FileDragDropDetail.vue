<template>
    <div>
        <biz-media-gallery-item
            :medium="computedMediumItem"
            :is-delete-enabled="false"
            :is-download-enabled="false"
            :is-edit-enabled="true"
            :is-preview-enabled="true"
            :is-image-preview-thumbnail="false"
            :ratio="isMultipleUpload ? `is-1by1` : null"
            :image-styles="isMultipleUpload ? `object-fit: contain;` : null"
            :figure-styles="isMultipleUpload ? `background-color: #000;` : null"
            @on-edit-clicked="onEditClicked()"
            @on-preview-clicked="onPreviewClicked()"
        />

        <biz-file-drag-drop-modal-image-editor
            v-if="isModalOpen"
            v-model:medium="computedMedium"
            v-model:medium-url="computedMediumUrl"
            :dimensions="dimensions"
            @on-close="closeModal()"
        />

        <biz-modal
            v-show="isModalPreviewOpen"
            @close="isModalPreviewOpen = false"
        >
            <p class="image">
                <img
                    :src="previewImageSrc"
                    alt="preview-image"
                >
            </p>
        </biz-modal>
    </div>
</template>

<script>
    import MixinHasModal from '@/Mixins/HasModal';
    import BizFileDragDropModalImageEditor from '@/Biz/FileDragDropModalImageEditor.vue';
    import BizMediaGalleryItem from '@/Biz/Media/GalleryItem.vue';
    import BizModal from '@/Biz/Modal.vue';
    import { useModelWrapper } from '@/Libs/utils';
    import { ref } from 'vue';

    export default {
        name: "BizFileDragDropDetail",

        components: {
            BizFileDragDropModalImageEditor,
            BizMediaGalleryItem,
            BizModal,
        },

        mixins: [
            MixinHasModal,
        ],

        props: {
            dimensions: { type: Object, default: () => {} },
            isMultipleUpload: { type: Boolean, required: true },
            medium: { type: Object, required: true },
        },

        setup(props, {emit}) {
            return {
                computedMedium: useModelWrapper(props, emit, 'medium'),
                computedMediumUrl: ref(URL.createObjectURL(props.medium))
            }
        },

        data() {
            return {
                isModalPreviewOpen: false,
                previewImageSrc: null,
            };
        },

        computed: {
            computedMediumItem() {
                return {
                    file: this.computedMedium,
                    file_url: this.computedMediumUrl,
                }
            },
        },

        methods: {
            onEditClicked() {
                this.openModal();
            },

            onPreviewClicked() {
                this.previewImageSrc = this.computedMediumUrl;

                this.isModalPreviewOpen = true;
            },
        },
    }
</script>