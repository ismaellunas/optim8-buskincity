<template>
    <div>
        <sdb-toolbar-content
            v-if="isEditMode"
            @delete-content="deleteContent"
        />

        <div class="container">
            <carousel-main
                :visible-slide="visibleSlide"
                :total-image="entityImages.length"
                @prev="prevSlide"
                @next="nextSlide"
            >
                <template
                    v-for="(entityImage, index) in entityImages"
                    :key="index"
                >
                    <carousel-slide
                        :data-media="dataMedia"
                        :selected-locale="selectedLocale"
                        :index="index"
                        :entity-media="entityImage"
                        :visible-slide="visibleSlide"
                        :direction="direction"
                        :is-edit-mode="isEditMode"
                    >
                    </carousel-slide>
                </template>
            </carousel-main>
        </div>
    </div>
</template>

<script>
    import MixinDeletableContent from '@/Mixins/DeletableContent';
    import MixinEditModeComponent from '@/Mixins/EditModeComponent';
    import SdbButton from '@/Sdb/Button';
    import SdbSelect from '@/Sdb/Select';
    import SdbToolbarContent from '@/Blocks/Contents/ToolbarContent';
    import { useModelWrapper } from '@/Libs/utils';
    import { cloneDeep } from 'lodash';
    import CarouselMain from './Carousel/CarouselMain.vue';
    import CarouselSlide from './Carousel/CarouselSlide.vue';

    export default {
        components: {
            SdbButton,
            SdbSelect,
            SdbToolbarContent,
            CarouselMain,
            CarouselSlide,
        },

        mixins: [
            MixinDeletableContent,
            MixinEditModeComponent,
        ],

        props: {
            modelValue: {},
            dataMedia: {},
            selectedLocale: String,
        },

        data() {
            return {
                entityImages: this.entity.content.carousel.image,
                entityTemplate: this.entity.content.template,
                direction: 'left',
                sliderOptions: [1,2,3,4,5,6],
                visibleSlide: 0,
            };
        },

        setup(props, { emit }) {
            return {
                config: props.modelValue?.config,
                entity: useModelWrapper(props, emit),
                pageMedia: useModelWrapper(props, emit, 'dataMedia'),
            };
        },

        mounted() {
            if (
                !this.isEditMode
                && (
                    this.config.carousel.autoPlay === "active"
                    && this.config.carousel.autoPlay !== ""
                )
            ) {
                setInterval(() => {
                    this.nextSlide();
                }, 6000);
            }
        },

        watch: {
            'config.carousel.numberOfSliders': function(to, from) {
                const numberOfSliders = parseInt(this.config.carousel.numberOfSliders);
                const originalNumberOfSliders = this.entityImages.length;

                if (numberOfSliders < originalNumberOfSliders) {
                    const confirmText = 'Are you sure you want to decrease the number of slider?';
                    if (confirm(confirmText) === false) {
                        this.config.carousel.numberOfSliders = originalNumberOfSliders;
                        return;
                    }

                    const decreaseNumber = originalNumberOfSliders - numberOfSliders;
                    for (let i = 0; i < decreaseNumber; i++) {
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
            }
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
        },
    }
</script>