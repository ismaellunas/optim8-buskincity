<template>
    <transition :name="direction" mode="in-out">
        <div v-show="visibleSlide === index" class="carousel-slide">
            <!-- <img :src="entity.mediaId" alt=""> -->
            <sdb-image
                v-if="hasImage"
                :src="imageSrc"
                :alt="altText"
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
                class="card-content has-background-info-light is-flex is-justify-content-center is-align-items-center card-custom"
                style="height: 500px"
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
    </transition>
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
        name: 'CarouselSlide',

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
            index: { type: Number, default: 0 },
            dataMedia: {},
            selectedLocale: String,
            entityMedia: { type: Object },
            visibleSlide: { type: Number, default: 0 },
            direction: { type: String, required: true },
            isEditMode: { type: Boolean },
        },

        data() {
            return {
                entityImage: this.entityMedia,
                images: usePage().props.value.images ?? {},
                isFormOpen: false,
                modalImages: [],
            };
        },

        setup(props, { emit }) {
            return {
                entity: useModelWrapper(props, emit),
                pageMedia: useModelWrapper(props, emit, 'dataMedia'),
            };
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
    }
</script>

<style scoped>
    .carousel-slide {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
    }

    .card-custom {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 2;
    }

    .left-enter-active {
        animation: leftInAnimation 0.4s ease-in-out;
    }

    .left-leave-active {
        animation: leftOutAnimation 0.4s ease-in-out;
    }

    @keyframes leftInAnimation {
        from { transform: translateX(100%); }
        to { transform: translateX(0%); }
    }

    @keyframes leftOutAnimation {
        from { transform: translateX(0%); }
        to { transform: translateX(-100%); }
    }

    .right-enter-active {
        animation: rightInAnimation 0.4s ease-in-out;
    }

    .right-leave-active {
        animation: rightOutAnimation 0.4s ease-in-out;
    }

    @keyframes rightInAnimation {
        from { transform: translateX(-100%); }
        to { transform: translateX(0%); }
    }

    @keyframes rightOutAnimation {
        from { transform: translateX(0%); }
        to { transform: translateX(+100%); }
    }
</style>