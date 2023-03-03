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

                <biz-button-icon
                    v-if="hasImage"
                    class="is-overlay is-small"
                    type="button"
                    :icon="isFormDisplayed ? iconClear : iconEdit"
                    @click="toggleEdit"
                />

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
    import BizButtonIcon from '@/Biz/ButtonIcon.vue';
    import BizEditor from '@/Biz/EditorTinymce.vue';
    import BizImage from '@/Biz/Image.vue';
    import BizModalMediaBrowser from '@/Biz/Modal/MediaBrowser.vue';
    import BizToolbarContent from '@/Blocks/Contents/ToolbarContent.vue';
    import icon from '@/Libs/icon-class';
    import { clear as iconClear, edit as iconEdit, image as iconImage } from '@/Libs/icon-class';
    import { concat } from 'lodash';
    import { inject } from "vue";
    import { isBlank, useModelWrapper } from '@/Libs/utils';

    export default {
        name: 'ContentCard',

        components: {
            BizButtonIcon,
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
                iconClear,
                iconEdit,
                iconImage,
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
            hasPosition() {
                return !!this.config?.image?.position;
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
