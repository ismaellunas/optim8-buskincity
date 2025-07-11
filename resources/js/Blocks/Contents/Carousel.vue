<template>
    <div :style="dimensionStyle">
        <biz-toolbar-content
            style="z-index: 3"
            @delete-content="deleteContent"
            @duplicate-content="duplicateContent"
        />

        <carousel-main
            :config="config"
            :total-image="entityImages.length"
            :visible-slide="visibleSlide"
            @next="nextSlide"
            @prev="prevSlide"
        >
            <template
                v-for="(entityImage, index) in entityImages"
                :key="index"
            >
                <carousel-slide
                    :config="config"
                    :data-images="images"
                    :data-media="pageMedia"
                    :direction="direction"
                    :entity-media="entityImage"
                    :index="index"
                    :selected-locale="selectedLocale"
                    :visible-slide="visibleSlide"
                    @open-modal="openModalMedia(index)"
                />
            </template>
        </carousel-main>

        <biz-modal-media-browser
            v-if="isModalOpen"
            :data="modalImages"
            :instructions="instructions?.mediaLibrary"
            :is-download-enabled="can.media.read"
            :is-edit-enabled="can.media.edit"
            :is-upload-enabled="can.media.add"
            :query-params="imageListQueryParams"
            :search="search"
            @close="closeModal"
            @on-clicked-pagination="getImagesList"
            @on-close-edit-modal="refreshImageListByPageActive()"
            @on-media-selected="selectImage"
            @on-media-submitted="updateImage"
            @on-view-changed="setView"
        />
    </div>
</template>

<script>
    import CarouselMain from '@/Blocks/Contents/Carousel/CarouselMain.vue';
    import CarouselSlide from '@/Blocks/Contents/Carousel/CarouselSlide.vue';
    import MixinContentHasDimension from '@/Mixins/ContentHasDimension';
    import MixinContentHasMediaLibrary from '@/Mixins/ContentHasMediaLibrary';
    import MixinDeletableContent from '@/Mixins/DeletableContent';
    import MixinDuplicableContent from '@/Mixins/DuplicableContent';
    import MixinHasModal from '@/Mixins/HasModal';
    import BizModalMediaBrowser from '@/Biz/Modal/MediaBrowser.vue';
    import BizToolbarContent from '@/Blocks/Contents/ToolbarContent.vue';
    import { cloneDeep } from 'lodash';
    import { useModelWrapper, isBlank } from '@/Libs/utils';
    import { inject } from "vue";

    export default {
        name: 'ContentCarousel',
        components: {
            CarouselMain,
            CarouselSlide,
            BizModalMediaBrowser,
            BizToolbarContent,
        },

        mixins: [
            MixinContentHasDimension,
            MixinContentHasMediaLibrary,
            MixinDeletableContent,
            MixinDuplicableContent,
            MixinHasModal,
        ],

        inject: ['can'],

        props: {
            modelValue: {
                type: Object,
                required: true,
            },
            selectedLocale: {
                type: String,
                required: true,
            },
        },

        setup(props, { emit }) {
            return {
                config: props.modelValue?.config,
                dataImages: inject('dataImages'),
                entity: useModelWrapper(props, emit),
                pageMedia: inject('dataMedia'),
            };
        },

        data() {
            return {
                direction: 'left',
                entityImages: this.entity.content.carousel.images,
                entityTemplate: this.entity.content.template,
                images: this.dataImages,
                indexModify: null,
                sliderOptions: [1,2,3,4,5,6],
                visibleSlide: 0,
            };
        },

        watch: {
            'config.carousel.numberOfSliders': function() {
                const numberOfSliders = parseInt(this.config.carousel.numberOfSliders);
                const originalNumberOfSliders = this.entityImages.length;

                if (numberOfSliders < originalNumberOfSliders) {
                    const confirmText = 'Are you sure you want to decrease the number of slider?';
                    if (confirm(confirmText) === false) {
                        this.config.carousel.numberOfSliders = originalNumberOfSliders;
                        return;
                    }

                    let index = this.entityImages.length;
                    const decreaseNumber = originalNumberOfSliders - numberOfSliders;
                    for (let i = 0; i < decreaseNumber; i++) {
                        index--;
                        this.detachImageFromMedia(this.entityImages[index].mediaId, this.pageMedia);
                        this.entityImages.pop();
                    }
                    this.visibleSlide = 0;
                    this.direction = 'right';
                } else {
                    const increaseNumber = numberOfSliders - originalNumberOfSliders;
                    for (let i = 0; i < increaseNumber; i++) {
                        this.entityImages.push(cloneDeep(this.entityTemplate));
                    }
                }
                this.config.carousel.numberOfSliders = numberOfSliders;
            },
        },

        methods: {
            prevSlide() {
                if (this.visibleSlide <= 0) {
                    this.visibleSlide = this.entityImages.length - 1;
                } else {
                    this.visibleSlide--;
                }
                this.direction = 'right';
            },

            nextSlide() {
                if (this.visibleSlide >= this.entityImages.length - 1) {
                    this.visibleSlide = 0;
                } else {
                    this.visibleSlide++;
                }
                this.direction = 'left';
            },

            openModalMedia(index) {
                this.indexModify = index;
                this.openModal();
            },

            onShownModal() { /* @override */
                this.setTerm('');
                this.getImagesList(route(this.imageListRouteName));
            },

            selectImage(image) { /* @override Mixins/ContainImageContent */
                const locale = this.selectedLocale;
                if (!isBlank(this.entityImages[this.indexModify].mediaId)) {
                    this.detachImageFromMedia(this.entityImages[this.indexModify].mediaId, this.pageMedia);
                }

                if (!this.images[ locale ]) {
                    this.images[ locale ] = [];
                }
                this.images[locale].push(image);

                this.setImageNull();
                this.entityImages[this.indexModify].mediaId = image.id;

                this.attachImageToMedia(image.id, this.pageMedia);

                this.onImageSelected();
            },

            updateImage(response) {
                this.selectImage(response.data[0]);
                this.onImageUpdated();
            },

            onImageListLoadedFail(error) { /* @override Mixins/ContainImageContent */
                this.closeModal();
            },

            onImageSelected() { /* @override Mixins/ContainImageContent */
                this.closeModal();
            },

            onImageUpdated() { /* @override Mixins/ContainImageContent */
                this.closeModal();
            },

            onContentDeleted() { /* @override Mixins/DeletableContent */
                const countImage = this.entityImages.length;
                if (countImage > 0) {
                    for (let i = 0; i < countImage; i++) {
                        if (!isBlank(this.entityImages[i].mediaId)) {
                            this.detachImageFromMedia(this.entityImages[i].mediaId, this.pageMedia);
                        }
                    }
                }
            },

            onContentDuplicated() { /* @override MixinDuplicableContent */
                const countImage = this.entityImages.length;

                for (let i = 0; i < countImage; i++) {
                    this.attachImageToMedia(this.entityImages[i].mediaId, this.pageMedia);
                }
            },

            setImageNull() {
                this.entityImages[this.indexModify].mediaId = null;
            },
        },
    }
</script>