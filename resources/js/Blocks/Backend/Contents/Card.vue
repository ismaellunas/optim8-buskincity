<template>
    <div :class="wrapperClass">
        <sdb-toolbar-content
            @delete-content="deleteContent"
        />

        <div class="card">
            <div class="card-image" :class="cardImageClass">
                <sdb-image
                    v-if="hasImage"
                    :src="imageSrc"
                    :alt="altText"
                    :ratio="this.config?.image?.ratio"
                    :rounded="this.config?.image?.rounded"
                    :square="this.config?.image?.fixedSquare"
                />

                <sdb-button
                    v-if="hasImage"
                    type="button"
                    class="is-overlay is-small"
                    @click="toggleEdit"
                >
                    <span class="icon" v-if="isFormDisplayed"><i class="fas fa-times-circle"></i></span>
                    <span class="icon" v-else><i class="fas fa-pen"></i></span>
                </sdb-button>

                <div class="card-content has-background-info-light" v-if="isFormDisplayed">
                    <div class="block has-text-centered">
                        <sdb-button
                            type="button"
                            :disabled="!can.media.browse"
                            @click="openModal()"
                        >
                            <span>Open Media</span>
                            <span class="icon is-small">
                                <i class="far fa-image"></i>
                            </span>
                        </sdb-button>
                    </div>
                </div>
            </div>
            <div class="card-content">
                <div
                    class="content"
                    :class="cardContentClass"
                >
                    <sdb-editor v-model="entity.content.cardContent.content.html"/>
                </div>
            </div>
        </div>

        <sdb-modal-image-browser
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
    import MixinContainImageContent from '@/Mixins/ContainImageContent';
    import MixinDeletableContent from '@/Mixins/DeletableContent';
    import MixinHasModal from '@/Mixins/HasModal';
    import SdbButton from '@/Sdb/Button';
    import SdbEditor from '@/Sdb/EditorTinymce';
    import SdbImage from '@/Sdb/Image';
    import SdbModalImageBrowser from '@/Sdb/Modal/ImageBrowser';
    import SdbToolbarContent from '@/Blocks/Backend/Contents/ToolbarContent';
    import { concat } from 'lodash';
    import { createMarginClasses, createPaddingClasses } from '@/Libs/page-builder';
    import { isBlank, useModelWrapper } from '@/Libs/utils';
    import { usePage } from '@inertiajs/inertia-vue3';

    export default {
        name: 'Card',
        components: {
            SdbButton,
            SdbEditor,
            SdbImage,
            SdbModalImageBrowser,
            SdbToolbarContent,
        },
        mixins: [
            MixinContainImageContent,
            MixinDeletableContent,
            MixinHasModal,
        ],
        props: {
            can: Object,
            id: {},
            modelValue: {},
            dataMedia: {},
            selectedLocale: String,
        },
        setup(props, { emit }) {
            return {
                config: props.modelValue?.config,
                entity: useModelWrapper(props, emit),
                pageMedia: useModelWrapper(props, emit, 'dataMedia'),
            };
        },
        data() {
            return {
                entityImage: this.entity.content.cardImage.figure.image,
                images: usePage().props.value.images ?? {},
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
                this.setTerm('');
                this.getImagesList(route(this.imageListRouteName));
            },
            onImageListLoadedSuccess(data) { /* @override Mixins/ContainImageContent */
                this.modalImages = data;
            },
            onImageListLoadedFail(error) { /* @override Mixins/ContainImageContent */
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
