<template>
    <div
        class="page-component"
        @click="$emit('setting-content', id)"
    >
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
                @click="toggleEdit"
            >
                <span class="icon" v-if="isFormDisplayed"><i class="fas fa-times-circle"></i></span>
                <span class="icon" v-else><i class="fas fa-pen"></i></span>
            </sdb-button>

            <img :src="imageSrc" :alt="entity.content.figure.image?.alt ?? ''" :class="imgClass">
        </figure>

        <div class="card-content has-background-info-light" v-if="isFormDisplayed">
            <upload-image-content
                v-model="entity.content.figure.image.src"
                :entity-id="entityId"
                :upload-route="route('admin.media.upload-image')"
                @close-form="closeForm"
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
            v-if="isModalOpen"
            :data="modalImages"
            @close="closeModal"
            @on-clicked-pagination="getImagesRequest"
            @on-selected-image="selectImage"
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
                config: props.modelValue.config,
                entity: useModelWrapper(props, emit),
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
                this.entity.content.figure.image.src = image.file_url;
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
