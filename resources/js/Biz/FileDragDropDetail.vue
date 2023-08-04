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
            @on-edit-clicked="onEditClicked()"
            @on-preview-clicked="onPreviewClicked()"
        />

        <biz-modal-image-editor
            v-if="isModalOpen"
            v-model="computedMediumFileUrl"
            v-model:cropper="cropper"
            :cropped-image-type="croppedImageType"
            :file-name="computedMedium.name"
            :dimensions="dimensions"
            :is-resize-enabled="false"
            @close="closeModal"
        >
            <template #actions>
                <biz-button
                    type="button"
                    class="is-link"
                    @click="updateFile"
                >
                    Done
                </biz-button>
            </template>
        </biz-modal-image-editor>

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
    import BizButton from '@/Biz/Button.vue';
    import BizMediaGalleryItem from '@/Biz/Media/GalleryItem.vue';
    import BizModal from '@/Biz/Modal.vue';
    import BizModalImageEditor from '@/Biz/Modal/ImageEditor.vue';
    import { getCanvasBlob, useModelWrapper } from '@/Libs/utils';
    import { ref } from 'vue';

    export default {
        name: "BizFileDragDropDetail",

        components: {
            BizButton,
            BizMediaGalleryItem,
            BizModal,
            BizModalImageEditor,
        },

        mixins: [
            MixinHasModal,
        ],

        props: {
            medium: { type: Object, required: true },
            dimensions: { type: Object, default: () => {} },
            isMultipleUpload: { type: Boolean, required: true },
        },

        setup(props, {emit}) {
            return {
                computedMedium: useModelWrapper(props, emit, 'medium'),
                computedMediumFileUrl: ref(URL.createObjectURL(props.medium))
            }
        },

        data() {
            return {
                croppedImageType: "image/png",
                cropper: null,
                isModalPreviewOpen: false,
                previewImageSrc: null,
            };
        },

        computed: {
            computedMediumItem() {
                return {
                    file: this.computedMedium,
                    file_url: this.computedMediumFileUrl,
                }
            },
        },

        methods: {
            onEditClicked() {
                this.openModal();
            },

            onPreviewClicked() {
                this.previewImageSrc = this.computedMediumItem.file_url;

                this.isModalPreviewOpen = true;
            },

            updateFile() {
                const self = this;

                self.getCropperBlob()
                    .then((blob) => {
                        self.computedMediumFileUrl = URL.createObjectURL(blob);
                        self.computedMedium = blob;

                        self.closeModal();
                    });
            },

            getCropperBlob() {
                return getCanvasBlob(
                    this.cropper.getCroppedCanvas(),
                    this.croppedImageType
                );
            },
        },
    }
</script>