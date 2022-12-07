<template>
    <div :style="dimensionStyle">
        <biz-toolbar-content
            @delete-content="deleteContent"
            @duplicate-content="duplicateContent"
        />

        <div
            class="card"
            :class="cardClasses"
        >
            <div
                class="card-image"
                :class="cardImageClass"
            >
                <biz-image
                    v-if="hasImage"
                    :src="imageSrc"
                    :alt="altText"
                    :ratio="this.config?.image?.ratio"
                    :rounded="this.config?.image?.rounded"
                    :square="this.config?.image?.fixedSquare"
                    :position="this.config?.image?.position"
                    :img-style="imageStyles"
                />

                <biz-button
                    v-if="hasImage"
                    type="button"
                    class="is-overlay is-small"
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
            </div>
            <div class="card-content">
                <div
                    class="content"
                    :class="cardContentClass"
                >
                    <biz-editor v-model="entity.content.cardContent.content.html" />
                </div>
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
    import BizEditor from '@/Biz/EditorTinymce';
    import BizImage from '@/Biz/Image';
    import BizModalMediaBrowser from '@/Biz/Modal/MediaBrowser';
    import BizToolbarContent from '@/Blocks/Contents/ToolbarContent';
    import { concat } from 'lodash';
    import { isBlank, useModelWrapper } from '@/Libs/utils';
    import { inject } from "vue";

    export default {
        name: 'Card',
        components: {
            BizButton,
            BizEditor,
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
                entityImage: this.entity.content.cardImage.figure.image,
                images: this.dataImages,
                isFormOpen: false,
                modalImages: [],
            };
        },
        computed: {
            isFormDisplayed() {
                return !this.hasImage || (this.hasImage && this.isFormOpen);
            },
            configCard() {
                return this.config.card;
            },
            configImage() {
                return this.config.image;
            },
            configContent() {
                return this.config.content;
            },
            cardImageClass() {
                let classes = [];

                const suffix = {top: 't', right: 'r', bottom: 'b', left: 'l'};

                if (this.configImage.padding) {
                    for (const [key, value] of Object.entries(this.configImage.padding)) {
                        classes.push( 'p'+suffix[key]+'-'+value );
                    }
                }
                return classes;
            },
            cardContentClass() {
                return concat(
                    this.configContent?.size,
                    this.configContent?.alignment
                ).filter(Boolean);
            },
            cardClasses() {
                return concat(
                    this.configCard.rounded,
                    (this.configCard.isShadowless ? 'is-shadowless' : ''),
                ).filter(Boolean);
            },
            imageWidth() {
                return this.configImage.width;
            },
            imageHeight() {
                return this.configImage.height;
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
            onContentDeleted() { /* @override MixinDeletableContent */
                if (!isBlank(this.entityImage.mediaId)) {
                    this.detachImageFromMedia(this.entityImage.mediaId, this.pageMedia);
                }
            },
            onImageSelected() { /* @override MixinContentHasMediaLibrary */
                this.closeModal();
                this.isFormOpen = false;
            },
            onImageUpdated() { /* @override MixinContentHasMediaLibrary */
                this.closeModal();
            },
            toggleEdit() {
                this.isFormOpen = !this.isFormOpen;
            },
            onShownModal() { /* @overide */
                this.setTerm('');
                this.getImagesList(route(this.imageListRouteName));
            },
            onImageListLoadedSuccess(data) { /* @override MixinContentHasMediaLibrary */
                this.modalImages = data;
            },
            onImageListLoadedFail(error) { /* @override MixinContentHasMediaLibrary */
                this.closeModal();
            },
            onContentDuplicated() { /* @override MixinDuplicableContent */
                this.attachImageToMedia(this.entityImage.mediaId, this.pageMedia);
            },
        },
    }
</script>
