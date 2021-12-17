<template>
    <div>
        <div class="container">
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
                        :direction="direction"
                        :entity-media="entityImage"
                        :index="index"
                        :selected-locale="selectedLocale"
                        :visible-slide="visibleSlide"
                    />
                </template>
            </carousel-main>
        </div>
    </div>
</template>

<script>
    import CarouselMain from '@/Blocks/Frontend/Contents/Carousel/CarouselMain.vue';
    import CarouselSlide from '@/Blocks/Frontend/Contents/Carousel/CarouselSlide.vue';
    import { usePage } from '@inertiajs/inertia-vue3';

    export default {
        components: {
            CarouselMain,
            CarouselSlide,
        },

        props: {
            entity: {
                type: Object,
                default:() => {}
            },
            selectedLocale: {
                type: String,
                required: true,
            },
        },

        setup(props, { emit }) {
            return {
                config: props.entity?.config,
                images: usePage().props.value.images ?? {},
            };
        },

        data() {
            return {
                direction: 'left',
                entityImages: this.entity.content.carousel.images,
                visibleSlide: 0,
            };
        },

        computed: {
            isAutoPlay() {
                return this.config.carousel.autoPlay;
            },
        },

        mounted() {
            if (this.isAutoPlay) {
                setInterval(() => {
                    this.nextSlide();
                }, 6000);
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