@once
    @push('styles')
        <style>
            .carousel-slide {
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
            }

            .left-enter-active {
                animation: leftInAnimation 0.4s ease-in-out;
            }

            .left-leave-active {
                animation: leftOutAnimation 0.4s ease-in-out;
            }

            @keyframes leftInAnimation {
                from { transform: translateX(100%); }
                to { transform: translateX(0%); }
            }

            @keyframes leftOutAnimation {
                from { transform: translateX(0%); }
                to { transform: translateX(-100%); }
            }

            .right-enter-active {
                animation: rightInAnimation 0.4s ease-in-out;
            }

            .right-leave-active {
                animation: rightOutAnimation 0.4s ease-in-out;
            }

            @keyframes rightInAnimation {
                from { transform: translateX(-100%); }
                to { transform: translateX(0%); }
            }

            @keyframes rightOutAnimation {
                from { transform: translateX(0%); }
                to { transform: translateX(+100%); }
            }
        </style>
    @endpush
@endonce

<div id="{{ $uid }}" class="carousel-container container">
    <figure class="carousel is-relative is-clipped has-background-grey-lighter image {{ $ratio }}">
        <transition
            v-for="(entityImage, index) in entityImages"
            :name="direction"
            mode="in-out"
        >
            <div
                v-show="visibleSlide === index"
                class="carousel-slide"
            >
                <figure :class="figureClasses">
                    <img
                        :alt="entityImage.alt"
                        :src="entityImage.file_url"
                    />
                </figure>
            </div>
        </transition>

        <div class="level is-overlay">
            <div class="level-item is-justify-content-start">
                <span
                    class="icon carousel-button has-text-info is-size-3 ml-5"
                    @click="prevSlide"
                >
                    <i class="fa fa-chevron-left"></i>
                </span>
            </div>

            <div class="level-item is-align-items-end mb-4" style="height: 100%">
                <div class="carousel-indicator">
                    <div
                        v-for="(total, index) in entityImages.length"
                        :key="index"
                        :class="{ active: visibleSlide === index }"
                        class="carousel-indicator-item has-background-info"
                    >
                    </div>
                </div>
            </div>

            <div class="level-item is-justify-content-end">
                <span
                    class="icon carousel-button has-text-info is-size-3 mr-5"
                    @click="nextSlide"
                >
                    <i class="fa fa-chevron-right"></i>
                </span>
            </div>
        </div>
    </figure>
</div>

@once
    @push('bottom_scripts')
    <script>
        const Carousel = {
            data() {
                return {
                    direction: 'left',
                    entityImages: [],
                    config: {},
                    visibleSlide: 0,
                    index: 0,
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
            computed: {
                figureClasses() {
                    const classes = ['image'];
                    classes.push(this.config.ratio ?? null);
                    return classes.filter(Boolean);
                },
            },
            mounted() {
                setInterval(() => {
                    if (this.config.autoPlay) this.nextSlide();
                }, 6000);
            },
        };
    </script>
    @endpush
@endonce

@push('bottom_scripts')
<script>
    var Carousel_{{ $uid }} = Vue.createApp(Carousel).mount('#{{ $uid }}');
    Carousel_{{ $uid }}.$data.entityImages = {{ Illuminate\Support\Js::from($carouselImages) }};
    Carousel_{{ $uid }}.$data.config = {{ Illuminate\Support\Js::from($config) }};
</script>
@endpush
