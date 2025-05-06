<template>
    <div :style="dimensionStyle">
        <biz-toolbar-content
            @delete-content="deleteContent"
            @duplicate-content="duplicateContent"
        />

        <biz-button-icon
            v-if="hasImage"
            class="is-fullwidth is-small"
            type="button"
            :icon="isFormDisplayed ? iconClear : iconEdit"
            @click="toggleEdit"
        />

        <div
            :class="config?.image?.position"
        >
            <biz-image
                v-if="hasImage"
                :src="imageSrc"
                :alt="altText"
                :ratio="config?.image?.ratio"
                :rounded="config?.image?.rounded"
                :square="config?.image?.fixedSquare"
                :img-style="imageStyles"
                :has-position="hasPosition"
            />
        </div>

        <div
            v-if="isFormDisplayed"
            class="card-content has-background-info-light"
        >
            <div class="block has-text-centered">
                <biz-button-icon
                    icon-class="is-small"
                    type="button"
                    :disabled="!can.media.browse"
                    :icon="iconImage"
                    @click="openModal()"
                >
                    <span>Open Media</span>
                </biz-button-icon>
            </div>
        </div>

        <biz-modal-media-browser
            v-if="isModalOpen"
            :data="modalImages"
            :instructions="instructions?.mediaLibrary"
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
    import BizButtonIcon from '@/Biz/ButtonIcon.vue';
    import BizImage from '@/Biz/Image.vue';
    import BizModalMediaBrowser from '@/Biz/Modal/MediaBrowser.vue';
    import BizToolbarContent from '@/Blocks/Contents/ToolbarContent.vue';
    import { useModelWrapper, isBlank } from '@/Libs/utils';
    import { inject } from "vue";
    import { clear as iconClear, edit as iconEdit, image as iconImage } from '@/Libs/icon-class';

    export default {
        name: 'ContentImage',
        components: {
            BizButtonIcon,
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
                iconClear,
                iconEdit,
                iconImage,
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
            hasPosition() {
                return !!this.config?.image?.position;
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
