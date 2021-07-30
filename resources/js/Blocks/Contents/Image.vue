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

        <sdb-image-browser-modal
            :data="modalImages"
            @close="closeModal"
            @on-clicked-pagination="getImagesRequest"
            @on-selected-image="selectImage"
            v-show="isModalOpen"
        />
    </div>
</template>

<script>
    import DeletableContentMixin from '@/Mixins/DeletableContent';
    import EditModeContentMixin from '@/Mixins/EditModeContent';
    import HasModalMixin from '@/Mixins/HasModal';
    import SdbButton from '@/Sdb/Button';
    import SdbImageBrowserModal from '@/Sdb/ImageBrowserModal';
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
            SdbImageBrowserModal,
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
                        self.setModalImages(response.data);
                    })
                    .catch(function (error) {
                        self.$swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Something went wrong!',
                        })
                        self.closeModal();
                    });
            },
            setModalImages(data) {
                this.modalImages = data;
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
