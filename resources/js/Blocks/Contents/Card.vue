<template>
    <div :class="wrapperClass">
        <sdb-toolbar-content
            v-if="isEditMode"
            @delete-content="deleteContent"
        />

        <div class="card">
            <div class="card-image" :class="cardImageClass">
                <figure class="image" :class="figureClass" v-if="hasImage">
                    <img :src="image.src" :class="imgClass" :alt="altText">
                </figure>

                <sdb-button
                    v-if="isEditMode && hasImage"
                    type="button"
                    class="is-overlay is-small"
                    @click="toggleEdit"
                >
                    <span class="icon" v-if="isFormDisplayed"><i class="fas fa-times-circle"></i></span>
                    <span class="icon" v-else><i class="fas fa-pen"></i></span>
                </sdb-button>

                <div class="card-content has-background-info-light" v-if="isFormDisplayed">
                    <div class="block has-text-centered">
                        <sdb-button @click="openModal()" type="button">
                            <span>Open Media</span>
                            <span class="icon is-small">
                                <i class="far fa-image"></i>
                            </span>
                        </sdb-button>
                    </div>
                </div>
            </div>
            <div
                v-if="isCardContentDisplayed"
                class="card-content"
            >
                <div
                    class="content"
                    :class="cardContentClass"
                >
                    <template v-if="isEditMode">
                        <sdb-editor v-model="entity.content.cardContent.content.html"/>
                    </template>
                    <template v-else>
                        <div v-html="entity.content.cardContent.content.html"></div>
                    </template>
                </div>
            </div>
        </div>

        <sdb-modal-image-browser
            v-if="isModalOpen"
            :data="modalImages"
            @close="closeModal"
            @on-clicked-pagination="getImagesList"
            @on-media-selected="selectImage"
            @on-media-submitted="updateImage"
        />
    </div>
</template>

<script>
    import MixinContainImageContent from '@/Mixins/ContainImageContent';
    import MixinDeletableContent from '@/Mixins/DeletableContent';
    import MixinEditModeContent from '@/Mixins/EditModeContent';
    import MixinHasModal from '@/Mixins/HasModal';
    import SdbButton from '@/Sdb/Button';
    import SdbEditor from '@/Sdb/EditorTinymce';
    import SdbModalImageBrowser from '@/Sdb/Modal/ImageBrowser';
    import SdbToolbarContent from '@/Blocks/Contents/ToolbarContent';
    import { concat, isEmpty } from 'lodash';
    import { createMarginClasses, createPaddingClasses } from '@/Libs/page-builder';
    import { emitModelValue, isBlank, useModelWrapper } from '@/Libs/utils';

    export default {
        components: {
            SdbButton,
            SdbEditor,
            SdbModalImageBrowser,
            SdbToolbarContent,
        },
        mixins: [
            MixinContainImageContent,
            MixinDeletableContent,
            MixinEditModeContent,
            MixinHasModal,
        ],
        props: {
            id: {},
            isEditMode: {type: Boolean, default: false},
            modelValue: {},
            dataMedia: {},
            images: {},
        },
        setup(props, { emit }) {
            return {
                //figure: props.modelValue.content.cardImage.figure,
                config: props.modelValue?.config,
                entity: useModelWrapper(props, emit),
                pageMedia: useModelWrapper(props, emit, 'dataMedia'),
            };
        },
        data() {
            return {
                image: this.entity.content.cardImage.figure.image,
                isFormOpen: false,
                modalImages: [],
            };
        },
        methods: {
            onContentDeleted() { /* @override Mixins/DeletableContent */
                if (!isBlank(this.image.mediaId)) {
                    this.detachImageFromMedia(this.image.mediaId, this.pageMedia);
                }
            },
            onImageSelected() { /* @override Mixins/ContainImageContent */
                this.closeModal();
                this.isFormOpen = false;
            },
            onImageUpdated() { /* @override Mixins/ContainImageContent */
                this.closeModal();
            },
            toggleEdit() {
                this.isFormOpen = !this.isFormOpen;
            },
            onShownModal() { /* @overide */
                this.getImagesList(route('admin.media.list.image'));
            },
            onImageListLoadedSuccess(data) { /* @override Mixins/ContainImageContent */
                this.modalImages = data;
            },
            onImageListLoadedFail(error) { /* @override Mixins/ContainImageContent */
                this.closeModal();
            },
        },
        computed: {
            hasImage() {
                return !isEmpty(this.entity.content.cardImage.figure.image.src);
            },
            isFormDisplayed() {
                return !this.hasImage || (this.hasImage && this.isFormOpen);
            },
            figureClass() {
                let classes = [];
                classes.push(this.config?.image?.fixedSquare ?? '');
                classes.push(this.config?.image?.ratio ?? '');
                return classes;
            },
            imgClass() {
                let classes = [];
                classes.push(this.config?.image?.rounded ?? '');
                return classes;
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
            },
            isCardContentDisplayed() {
                if (this.isEditMode) {
                    return true;
                }
                return !isEmpty(this.entity.content.cardContent.content.html);
            },
            altText() {
                if (this.images) {
                    const image = this
                        .images
                        .find(image => image.id === this.entity.content.cardImage.figure.image.mediaId);
                    if (image) {
                        return image.alt;
                    }
                }
                return "";
            },
        }
    }
</script>
