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
                        :direction="direction"
                        :entity-image="entityImage"
                        :index="index"
                        :visible-slide="visibleSlide"
                    />
                </template>
            </carousel-main>
        </div>
    </div>
</template>

<script>
    import CarouselMain from '@/Components/Builder/Content/Carousel/CarouselMain.vue';
    import CarouselSlide from '@/Components/Builder/Content/Carousel/CarouselSlide.vue';

    export default {
        components: {
            CarouselMain,
            CarouselSlide,
        },

        props: {
            config: {
                type: Object,
                required: true,
            },
            entityImages: {
                type: Array,
                default:() => [],
            },
            slideSpeed: {
                type: Number,
                required: true,
            },
        },

        data() {
            return {
                direction: 'left',
                visibleSlide: 0,
            };
        },

        computed: {
            isAutoPlay() {
                return this.config.autoPlay;
            },
        },

        mounted() {
            if (this.isAutoPlay) {
                setInterval(() => {
                    this.nextSlide();
                }, this.slideSpeed);
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