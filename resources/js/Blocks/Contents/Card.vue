<template>
    <div>
        <sdb-toolbar-content
            v-if="isEditMode"
            @delete-content="deleteContent"
        />

        <div class="card">
            <div class="card-image">
                <figure class="image" :class="figure.attrs.class" v-if="hasImage">
                    <img :src="image.src" alt="image.attrs.alt">
                </figure>

                <sdb-button
                    type="button"
                    class="is-overlay is-small"
                    @click.prevent="toggleEdit"
                    v-if="isEditMode && hasImage">
                    <span class="icon" v-if="isFormDisplayed"><i class="fas fa-times-circle"></i></span>
                    <span class="icon" v-else><i class="fas fa-pen"></i></span>
                </sdb-button>

                <div class="card-content has-background-info-light" v-if="isFormDisplayed">
                    <upload-image-content
                        :uploadRoute="route('admin.media.upload-image')"
                        v-model="content.cardImage.figure.image.src"
                        @uploaded-image="updateImageSource"
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
            </div>
            <div class="card-content">
                <div class="content">
                    <template v-if="isEditMode">
                        <sdb-ckeditor-inline
                            v-model="content.cardContent.content.html"
                            />
                    </template>
                    <template v-else>
                        <div v-html="content.cardContent.content.html"></div>
                    </template>
                </div>
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
    import SdbCkeditorInline from '@/Sdb/CkeditorInline'
    import SdbImageBrowserModal from '@/Sdb/ImageBrowserModal';
    import SdbToolbarContent from '@/Blocks/Contents/ToolbarContent';
    import UploadImageContent from '@/Blocks/Contents/UploadImage';
    import { useModelWrapper, emitModelValue, isBlank } from '@/Libs/utils'

    export default {
        mixins: [
            EditModeContentMixin,
            DeletableContentMixin,
            HasModalMixin,
        ],
        components: {
            SdbButton,
            SdbCkeditorInline,
            SdbImageBrowserModal,
            SdbToolbarContent,
            UploadImageContent,
        },
        props: {
            id: {},
            isEditMode: {type: Boolean, default: false},
            modelValue: {},
        },
        setup(props, { emit }) {
            return {
                content: useModelWrapper(props, emit),
                image: props.modelValue.cardImage.figure.image,
                figure: props.modelValue.cardImage.figure,
            };
        },
        data() {
            return {
                isFormOpen: false,
                modalImages: [],
            };
        },
        methods: {
            updateImageSource(imagePath) {
                this.content.cardImage.figure.image.src = imagePath;
                emitModelValue(this.$emit, this.content);
            },
            toggleEdit() {
                this.isFormOpen = !this.isFormOpen;
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
                this.content.cardImage.figure.image.src = image.file_url;
                this.closeModal();
            },
        },
        computed: {
            hasImage() {
                return !isBlank(this.content.cardImage.figure.image.src);
            },
            isFormDisplayed() {
                return !this.hasImage || (this.hasImage && this.isFormOpen);
            }
        }
    }
</script>
