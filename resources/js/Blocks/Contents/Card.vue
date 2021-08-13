<template>
    <div :class="wrapperClass">
        <sdb-toolbar-content
            v-if="isEditMode"
            @delete-content="deleteContent"
        />

        <div class="card">
            <div class="card-image" :class="cardImageClass">
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
    import SdbEditor from '@/Sdb/EditorTinymce';
    import SdbImageBrowserModal from '@/Sdb/ImageBrowserModal';
    import SdbToolbarContent from '@/Blocks/Contents/ToolbarContent';
    import { concat, isEmpty } from 'lodash';
    import { createMarginClasses, createPaddingClasses } from '@/Libs/page-builder';
    import { emitModelValue, useModelWrapper } from '@/Libs/utils';

    export default {
        mixins: [
            EditModeContentMixin,
            DeletableContentMixin,
            HasModalMixin,
        ],
        components: {
            SdbButton,
            SdbEditor,
            SdbImageBrowserModal,
            SdbToolbarContent,
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
        }
    }
</script>
