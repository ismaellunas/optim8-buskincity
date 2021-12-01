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

<div class="carousel-container container">
    <figure class="carousel is-relative is-clipped has-background-grey-lighter image {{ $ratio }}">
        @foreach($carouselImages as $key => $carousel)
            <div class="carousel-slide {{ $key !== 0 ? 'is-hidden' : '' }}" data-index="{{ $key }}">
                <x-image
                    :media="$carousel"
                    :locale="$locale"
                    :ratio="$ratio"
                    :rounded="null"
                    :square="null"
                />
            </div>
        @endforeach
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
                    @for ($i = 0; $i < $numberOfSliders; $i++)
                        <div class="carousel-indicator-item has-background-info"></div>
                    @endfor
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
                    entityImages: {!! json_encode($carouselImages) !!},
                    visibleSlide: 0,
                    index: 0,
                }
            },
            props: ['index'],
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
            created() {
                //alert('hello')
                console.log(this.entityImages);
            }
        }
        Vue.createApp(Carousel).mount('.carousel-container')
    </script>
    @endpush
@endonce
