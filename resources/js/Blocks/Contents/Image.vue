<template>
    <div>
        <sdb-toolbar-content
            v-if="isEditMode"
            @delete-content="deleteContent"
        />

        <figure
            v-if="hasImage"
            :class="figureClass"
            class="image"
        >
            <sdb-button
                v-if="isEditMode"
                class="is-small is-overlay"
                type="button"
                style="z-index: 1"
                @click="toggleEdit"
            >
                <span class="icon" v-if="isFormDisplayed"><i class="fas fa-times-circle"></i></span>
                <span class="icon" v-else><i class="fas fa-pen"></i></span>
            </sdb-button>

            <img :src="imageSrc" :alt="altText" :class="imgClass">
        </figure>

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
    import DeletableContentMixin from '@/Mixins/DeletableContent';
    import EditModeContentMixin from '@/Mixins/EditModeContent';
    import HasModalMixin from '@/Mixins/HasModal';
    import MixinContainImageContent from '@/Mixins/ContainImageContent';
    import SdbButton from '@/Sdb/Button';
    import SdbModalImageBrowser from '@/Sdb/Modal/ImageBrowser';
    import SdbToolbarContent from '@/Blocks/Contents/ToolbarContent';
    import { useModelWrapper, isBlank } from '@/Libs/utils';
    import { detachImageFromMedia } from '@/Libs/page-builder';

    export default {
        components: {
            SdbButton,
            SdbModalImageBrowser,
            SdbToolbarContent,
        },
        mixins: [
            DeletableContentMixin,
            EditModeContentMixin,
            HasModalMixin,
            MixinContainImageContent,
        ],
        props: {
            id: {},
            entityId: {},
            modelValue: {},
            dataMedia: {},
            images: {},
        },
        data() {
            return {
                image: this.entity.content.figure.image,
                isFormOpen: false,
                modalImages: [],
            };
        },
        setup(props, { emit }) {
            return {
                config: props.modelValue?.config,
                entity: useModelWrapper(props, emit),
                pageMedia: useModelWrapper(props, emit, 'dataMedia'),
            };
        },
        methods: {
            toggleEdit() {
                this.isFormOpen = !this.isFormOpen;
            },
            onContentDeleted() { /* @override Mixins/DeletableContent */
                if (!isBlank(this.image.mediaId)) {
                    detachImageFromMedia(this.image.mediaId, this.pageMedia);
                }
            },
            onShownModal() { /* @override */
                this.getImagesList(route('admin.media.list.image'));
            },
            onImageListLoadedSuccess(data) { /* @override Mixins/ContainImageContent */
                this.modalImages = data;
            },
            onImageListLoadedFail(error) { /* @override Mixins/ContainImageContent */
                this.closeModal();
            },
            onImageSelected() { /* @override Mixins/ContainImageContent */
                this.closeModal();
                this.isFormOpen = false;
            },
            onImageUpdated() { /* @override Mixins/ContainImageContent */
                this.closeModal();
            },
        },
        computed: {
            hasImage() {
                return !isBlank(this.entity.content.figure.image.src);
            },
            imageSrc() {
                if (this.hasImage) {
                    return this.entity.content.figure.image.src;
                }
                return "";
            },
            /* @overide */
            canEdit() {
                return this.isEditMode && this.hasImage;
            },
            isFormDisplayed() {
                return this.isEditMode && (
                    !this.hasImage
                    || (this.isEditMode && this.isFormOpen)
                );
            },
            figureClass() {
                let classes = [];
                classes.push(this.config?.image?.fixedSquare ?? "");
                classes.push(this.config?.image?.ratio ?? "");
                return classes;
            },
            imgClass() {
                let classes = [];
                classes.push(this.config?.image?.rounded ?? "");
                return classes;
            },
        }
    }
</script>
