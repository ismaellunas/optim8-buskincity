<template>
    <div :style="dimensionStyle">
        <biz-toolbar-content
            @delete-content="deleteContent"
            @duplicate-content="duplicateContent"
        />

        <biz-button
            class="is-small is-fullwidth"
            type="button"
            @click="toggleEdit"
        >
            <span
                v-if="isFormDisplayed"
                class="icon"
            >
                <i class="fas fa-times-circle" />
            </span>
            <span
                v-else
                class="icon"
            >
                <i class="fas fa-pen" />
            </span>
        </biz-button>

        <biz-image
            v-if="hasImage"
            :src="imageSrc"
            :alt="altText"
            :ratio="config?.image?.ratio"
            :rounded="config?.image?.rounded"
            :square="config?.image?.fixedSquare"
            :position="config?.image?.position"
            :img-style="imageStyles"
        />

        <div
            v-if="isFormDisplayed"
            class="card-content has-background-info-light"
        >
            <div class="block has-text-centered">
                <biz-button
                    type="button"
                    :disabled="!can.media.browse"
                    @click="openModal()"
                >
                    <span>Open Media</span>
                    <span class="icon is-small">
                        <i class="far fa-image" />
                    </span>
                </biz-button>
            </div>
        </div>

        <biz-modal-media-browser
            v-if="isModalOpen"
            :data="modalImages"
            :is-download-enabled="can.media.read"
            :is-upload-enabled="can.media.add"
            :query-params="imageListQueryParams"
            :search="search"
            @close="closeModal"
            @on-clicked-pagination="getImagesList"
            @on-media-selected="selectImage"
            @on-media-submitted="updateImage"
            @on-view-changed="setView"
        />
    </div>
</template>

<script>
    import MixinContentHasDimension from '@/Mixins/ContentHasDimension';
    import MixinContentHasMediaLibrary from '@/Mixins/ContentHasMediaLibrary';
    import MixinDeletableContent from '@/Mixins/DeletableContent';
    import MixinDuplicableContent from '@/Mixins/DuplicableContent';
    import MixinHasModal from '@/Mixins/HasModal';
    import BizButton from '@/Biz/Button';
    import BizImage from '@/Biz/Image';
    import BizModalMediaBrowser from '@/Biz/Modal/MediaBrowser';
    import BizToolbarContent from '@/Blocks/Contents/ToolbarContent';
    import { useModelWrapper, isBlank } from '@/Libs/utils';
    import { inject } from "vue";

    export default {
        name: 'Image',
        components: {
            BizButton,
            BizImage,
            BizModalMediaBrowser,
            BizToolbarContent,
        },
        mixins: [
            MixinContentHasDimension,
            MixinContentHasMediaLibrary,
            MixinDeletableContent,
            MixinDuplicableContent,
            MixinHasModal,
        ],
        inject: ['can'],
        props: {
            id: { type: String, required: true },
            modelValue: { type: Object, required: true },
            selectedLocale: { type: String, required: true },
        },
        setup(props, { emit }) {
            return {
                config: props.modelValue?.config,
                dataImages: inject('dataImages'),
                entity: useModelWrapper(props, emit),
                pageMedia: inject('dataMedia'),
            };
        },
        data() {
            return {
                entityImage: this.entity.content.figure.image,
                images: this.dataImages,
                isFormOpen: false,
                modalImages: [],
            };
        },
        computed: {
            /* @overide */
            canEdit() {
                return this.hasImage;
            },
            isFormDisplayed() {
                return !this.hasImage || this.isFormOpen;
            },
            imageWidth() {
                return this.config.image.width;
            },
            imageHeight() {
                return this.config.image.height;
            },
            imageStyles() {
                let styles = {
                    'width': this.imageWidth && this.imageWidth != ""
                        ? this.imageWidth + 'px'
                        : null,
                    'height': this.imageHeight && this.imageHeight != ""
                        ? this.imageHeight + 'px'
                        : null,
                };

                Object.keys(styles).forEach(key => {
                    if (
                        !styles[key]
                    ) {
                        delete styles[key];
                    }
                });

                return styles;
            },
        },
        methods: {
            toggleEdit() {
                this.isFormOpen = !this.isFormOpen;
            },
            onContentDeleted() { /* @override Mixins/MixinDeletableContent */
                if (!isBlank(this.entityImage.mediaId)) {
                    this.detachImageFromMedia(this.entityImage.mediaId, this.pageMedia);
                }
            },
            onShownModal() { /* @override */
                this.setTerm('');
                this.getImagesList(route(this.imageListRouteName));
            },
            onImageListLoadedSuccess(data) { /* @override Mixins/MixinContentHasMediaLibrary */
                this.modalImages = data;
            },
            onImageListLoadedFail(error) { /* @override Mixins/MixinContentHasMediaLibrary */
                this.closeModal();
            },
            onImageSelected() { /* @override Mixins/MixinContentHasMediaLibrary */
                this.closeModal();
                this.isFormOpen = false;
            },
            onImageUpdated() { /* @override Mixins/MixinContentHasMediaLibrary */
                this.closeModal();
            },
            onContentDuplicated() { /* @override Mixins/MixinDuplicableContent */
                this.attachImageToMedia(this.entityImage.mediaId, this.pageMedia);
            },
        },
    }
</script>
