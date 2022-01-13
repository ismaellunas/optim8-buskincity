<template>
    <div :class="wrapperClass">
        <biz-toolbar-content
            @delete-content="deleteContent"
        />

        <div class="card">
            <div class="card-image" :class="cardImageClass">
                <biz-image
                    v-if="hasImage"
                    :src="imageSrc"
                    :alt="altText"
                    :ratio="this.config?.image?.ratio"
                    :rounded="this.config?.image?.rounded"
                    :square="this.config?.image?.fixedSquare"
                />

                <biz-button
                    v-if="hasImage"
                    type="button"
                    class="is-overlay is-small"
                    @click="toggleEdit"
                >
                    <span class="icon" v-if="isFormDisplayed"><i class="fas fa-times-circle"></i></span>
                    <span class="icon" v-else><i class="fas fa-pen"></i></span>
                </biz-button>

                <div class="card-content has-background-info-light" v-if="isFormDisplayed">
                    <div class="block has-text-centered">
                        <biz-button
                            type="button"
                            :disabled="!can.media.browse"
                            @click="openModal()"
                        >
                            <span>Open Media</span>
                            <span class="icon is-small">
                                <i class="far fa-image"></i>
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
                    <biz-editor v-model="entity.content.cardContent.content.html"/>
                </div>
            </div>
        </div>

        <biz-modal-image-browser
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
    import MixinContentHasMediaLibrary from '@/Mixins/ContentHasMediaLibrary';
    import MixinDeletableContent from '@/Mixins/DeletableContent';
    import MixinHasModal from '@/Mixins/HasModal';
    import BizButton from '@/Biz/Button';
    import BizEditor from '@/Biz/EditorTinymce';
    import BizImage from '@/Biz/Image';
    import BizModalImageBrowser from '@/Biz/Modal/ImageBrowser';
    import BizToolbarContent from '@/Blocks/Contents/ToolbarContent';
    import { concat } from 'lodash';
    import { createMarginClasses, createPaddingClasses } from '@/Libs/page-builder';
    import { isBlank, useModelWrapper } from '@/Libs/utils';
    import { inject } from "vue";

    export default {
        name: 'Card',
        components: {
            BizButton,
            BizEditor,
            BizImage,
            BizModalImageBrowser,
            BizToolbarContent,
        },
        mixins: [
            MixinContentHasMediaLibrary,
            MixinDeletableContent,
            MixinHasModal,
        ],
        props: {
            can: Object,
            dataMedia: {},
            id: {},
            modelValue: {},
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
                entityImage: this.entity.content.cardImage.figure.image,
                images: this.dataImages,
                isFormOpen: false,
                modalImages: [],
            };
        },
        methods: {
            onContentDeleted() { /* @override Mixins/DeletableContent */
                if (!isBlank(this.entityImage.mediaId)) {
                    this.detachImageFromMedia(this.entityImage.mediaId, this.pageMedia);
                }
            },
            onImageSelected() { /* @override Mixins/ContentHasMediaLibrary */
                this.closeModal();
                this.isFormOpen = false;
            },
            onImageUpdated() { /* @override Mixins/ContentHasMediaLibrary */
                this.closeModal();
            },
            toggleEdit() {
                this.isFormOpen = !this.isFormOpen;
            },
            onShownModal() { /* @overide */
                this.setTerm('');
                this.getImagesList(route(this.imageListRouteName));
            },
            onImageListLoadedSuccess(data) { /* @override Mixins/ContentHasMediaLibrary */
                this.modalImages = data;
            },
            onImageListLoadedFail(error) { /* @override Mixins/ContentHasMediaLibrary */
                this.closeModal();
            },
        },
        computed: {
            isFormDisplayed() {
                return !this.hasImage || (this.hasImage && this.isFormOpen);
            },
            cardImageClass() {
                let classes = [];
                const suffix = {top: 't', right: 'r', bottom: 'b', left: 'l'};
                if (this.config?.image?.padding) {
                    for (const [key, value] of Object.entries(this.config.image.padding)) {
                        classes.push( 'p'+suffix[key]+'-'+value );
                    }
                }
                return classes;
            },
            cardContentClass() {
                return concat(
                    this.config.content?.size,
                    this.config.content?.alignment
                ).filter(Boolean);
            },
            wrapperClass() {
                return concat(
                    createPaddingClasses(this.config.wrapper?.padding),
                    createMarginClasses(this.config.wrapper?.margin)
                ).filter(Boolean);
            }
        },
    }
</script>
