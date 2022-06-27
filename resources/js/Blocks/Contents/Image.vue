<template>
    <div :style="dimensionStyle">
        <biz-toolbar-content
            @delete-content="deleteContent"
            @duplicate-content="duplicateContent"
        />

        <biz-image
            v-if="hasImage"
            :src="imageSrc"
            :alt="altText"
            :ratio="config?.image?.ratio"
            :rounded="config?.image?.rounded"
            :square="config?.image?.fixedSquare"
        >
            <biz-button
                class="is-small is-overlay"
                style="z-index: 1"
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
        </biz-image>

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
        props: {
            can: Object,
            id: String,
            entityId: {},
            modelValue: Object,
            dataMedia: {type: Array, default: []},
            selectedLocale: String,
        },
        setup(props, { emit }) {
            return {
                config: props.modelValue?.config,
                dataImages: inject('dataImages'),
                entity: useModelWrapper(props, emit),
                pageMedia: useModelWrapper(props, emit, 'dataMedia'),
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
        },
        methods: {
            toggleEdit() {
                this.isFormOpen = !this.isFormOpen;
            },
            onContentDeleted() { /* @override Mixins/DeletableContent */
                if (!isBlank(this.entityImage.mediaId)) {
                    this.detachImageFromMedia(this.entityImage.mediaId, this.pageMedia);
                }
            },
            onShownModal() { /* @override */
                this.setTerm('');
                this.getImagesList(route(this.imageListRouteName));
            },
            onImageListLoadedSuccess(data) { /* @override Mixins/ContentHasMediaLibrary */
                this.modalImages = data;
            },
            onImageListLoadedFail(error) { /* @override Mixins/ContentHasMediaLibrary */
                this.closeModal();
            },
            onImageSelected() { /* @override Mixins/ContentHasMediaLibrary */
                this.closeModal();
                this.isFormOpen = false;
            },
            onImageUpdated() { /* @override Mixins/ContentHasMediaLibrary */
                this.closeModal();
            },
        },
    }
</script>
