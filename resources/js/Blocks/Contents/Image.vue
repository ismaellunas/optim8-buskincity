<template>
    <div>
        <sdb-toolbar-content @delete-content="deleteContent" v-if="isEditMode"/>

        <figure class="image" v-if="hasImage">
            <sdb-button
                type="button"
                class="is-small is-overlay"
                @click="toggleEdit"
                v-if="isEditMode">
                <span class="icon" v-if="isFormDisplayed"><i class="fas fa-times-circle"></i></span>
                <span class="icon" v-else><i class="fas fa-pen"></i></span>
            </sdb-button>

            <img :src="imageSrc" :alt="content.figure.attrs.alt">
        </figure>

        <div class="card-content has-background-info-light" v-if="isFormDisplayed">
            <upload-image-content
                :entityId="entityId"
                :uploadRoute="route('admin.media.upload-image')"
                @close-form="closeForm"
                v-model="content.figure.image.src"
            />
            <div class="divider my-2">OR</div>
            <div class="block has-text-centered">
                <sdb-button @click="openModal()" type="button">
                    <span>Open Media</span>
                    <span class="icon is-small">
                        <i class="far fa-image"></i>
                    </span>
                </sdb-button>
            </div>
        </div>

        <sdb-modal-card
            :content-class="['is-huge']"
            :is-close-hidden="false"
            @close="closeModal()"
            v-show="isModalOpen"
            >
            <template v-slot:header>
                <p class="modal-card-title">Images</p>
                <sdb-button
                    @click="closeModal()"
                    class="delete is-primary"
                    type="button"
                    aria-label="close"
                />
            </template>

            <template v-slot:footer>
                <sdb-pagination
                    :links="modalImages.links ?? []"
                    :is-ajax="true"
                    @on-clicked-pagination="getImagesRequest"
                />
            </template>

            <div class="columns is-multiline">
                <div class="column is-3" v-for="image in modalImages.data">
                    <div class="card">
                        <div class="card-image">
                            <figure class="image is-4by3">
                            <img :src="image.file_url" :alt="image.file_name">
                            </figure>
                        </div>
                        <div class="card-content p-2">
                            <div class="content">
                                <p>{{ image.file_name }}</p>
                            </div>
                        </div>
                        <footer class="card-footer">
                            <sdb-button
                                @click.prevent="selectImage(image)"
                                class="card-footer-item">
                                Select
                            </sdb-button>
                        </footer>
                    </div>
                </div>
            </div>
        </sdb-modal-card>
    </div>
</template>

<script>
    import DeletableContentMixin from '@/Mixins/DeletableContent';
    import EditModeContentMixin from '@/Mixins/EditModeContent';
    import HasModalMixin from '@/Mixins/HasModal';
    import SdbButton from '@/Sdb/Button';
    import SdbLink from '@/Sdb/Link';
    import SdbModalCard from '@/Sdb/ModalCard';
    import SdbPagination from '@/Sdb/Pagination';
    import SdbToolbarContent from '@/Blocks/Contents/ToolbarContent';
    import UploadImageContent from '@/Blocks/Contents/UploadImage';
    import { useModelWrapper, isBlank } from '@/Libs/utils';

    export default {
        mixins: [
            DeletableContentMixin,
            EditModeContentMixin,
            HasModalMixin,
        ],
        components: {
            SdbButton,
            SdbLink,
            SdbModalCard,
            SdbPagination,
            SdbToolbarContent,
            UploadImageContent,
        },
        props: {
            class: {type: Array},
            id: {},
            entityId: {},
            modelValue: {},
        },
        data() {
            return {
                isFormOpen: false,
                modalImages: [],
            };
        },
        setup(props, { emit }) {
            return {
                content: useModelWrapper(props, emit),
                contentClass: props.modelValue.figure.attrs.class ?? [],
            };
        },
        methods: {
            toggleEdit() {
                this.isFormOpen = !this.isFormOpen;
            },
            closeForm() {
                this.isFormOpen = false;
            },
            onShownModal() { /* @overide */
                this.getImagesRequest(route('admin.media.list.image'));
            },
            getImagesRequest(url) {
                const self = this;
                axios.get(url)
                    .then(function (response) {
                        console.log(response.data);
                        self.modalImages = response.data;
                    })
                    .catch(function (error) {
                        //console.log(error);
                    })
                    .then(function () {
                        // always executed
                    });
            },
            selectImage(image) {
                this.content.figure.image.src = image.file_url;
                this.closeModal();
            },
        },
        computed: {
            hasImage() {
                return !isBlank(this.content.figure.image.src);
            },
            imageSrc() {
                if (this.hasImage) {
                    return this.content.figure.image.src;
                }
                return 'https://bulma.io/images/placeholders/640x480.png';
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
        }
    }
</script>
