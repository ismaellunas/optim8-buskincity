<template>
    <div>
        <sdb-toolbar-content
            v-if="isEditMode"
            @delete-content="deleteContent"
        />

        <sdb-image
            v-if="hasImage"
            :src="imageSrc"
            :alt="altText"
            :ratio="this.config?.image?.ratio"
            :rounded="this.config?.image?.rounded"
            :square="this.config?.image?.fixedSquare"
        >
            <sdb-button
                v-if="isEditMode"
                class="is-small is-overlay"
                style="z-index: 1"
                type="button"
                @click="toggleEdit"
            >
                <span class="icon" v-if="isFormDisplayed"><i class="fas fa-times-circle"></i></span>
                <span class="icon" v-else><i class="fas fa-pen"></i></span>
            </sdb-button>
        </sdb-image>

        <div
            v-if="isFormDisplayed"
            class="card-content has-background-info-light"
        >
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
    import MixinEditModeComponent from '@/Mixins/EditModeComponent';
    import MixinHasModal from '@/Mixins/HasModal';
    import SdbButton from '@/Sdb/Button';
    import SdbImage from '@/Sdb/Image';
    import SdbModalImageBrowser from '@/Sdb/Modal/ImageBrowser';
    import SdbToolbarContent from '@/Blocks/Contents/ToolbarContent';
    import { useModelWrapper, isBlank } from '@/Libs/utils';
    import { usePage } from '@inertiajs/inertia-vue3';

    export default {
        components: {
            SdbButton,
            SdbImage,
            SdbModalImageBrowser,
            SdbToolbarContent,
        },
        mixins: [
            MixinContainImageContent,
            MixinDeletableContent,
            MixinEditModeComponent,
            MixinHasModal,
        ],
        props: {
            id: {},
            entityId: {},
            modelValue: {},
            dataMedia: {},
            selectedLocale: String,
        },
        data() {
            return {
                entityImage: this.entity.content.figure.image,
                images: usePage().props.value.images ?? {},
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
                if (!isBlank(this.entityImage.mediaId)) {
                    this.detachImageFromMedia(this.entityImage.mediaId, this.pageMedia);
                }
            },
            onShownModal() { /* @override */
                this.setTerm('');
                this.getImagesList(route(this.imageListRouteName));
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
        }
    }
</script>
