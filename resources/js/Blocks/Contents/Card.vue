<template>
    <div>
        <sdb-toolbar-content
            v-if="isEditMode"
            @delete-content="deleteContent"
        />

        <div class="card">
            <div class="card-image">
                <figure class="image" :class="figureClass" v-if="hasImage">
                    <img :src="image.src" :class="imgClass">
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
                    <upload-image-content
                        v-model="entity.content.cardImage.figure.image.src"
                        :upload-route="route('admin.media.upload-image')"
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
            <div class="card-content" v-if="isCardContentDisplayed">
                <div class="content">
                    <template v-if="isEditMode">
                        <sdb-ckeditor-inline v-model="entity.content.cardContent.content.html"/>
                    </template>
                    <template v-else>
                        <div v-html="entity.content.cardContent.content.html"></div>
                    </template>
                </div>
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
    import SdbCkeditorInline from '@/Sdb/CkeditorInline'
    import SdbImageBrowserModal from '@/Sdb/ImageBrowserModal';
    import SdbToolbarContent from '@/Blocks/Contents/ToolbarContent';
    import UploadImageContent from '@/Blocks/Contents/UploadImage';
    import { useModelWrapper, emitModelValue, isBlank } from '@/Libs/utils'
    import { isEmpty } from 'lodash';

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
                config: props.modelValue.config,
                entity: useModelWrapper(props, emit),
                figure: props.modelValue.content.cardImage.figure,
                image: props.modelValue.content.cardImage.figure.image,
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
                this.entity.content.cardImage.figure.image.src = imagePath;
                emitModelValue(this.$emit, this.entity.content);
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
                this.entity.content.cardImage.figure.image.src = image.file_url;
                this.closeModal();
            },
        },
        computed: {
            hasImage() {
                return !isBlank(this.entity.content.cardImage.figure.image.src);
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
            isCardContentDisplayed() {
                if (this.isEditMode) {
                    return true;
                }
                return !isEmpty(this.entity.content.cardContent.content.html);
            },
        }
    }
</script>
