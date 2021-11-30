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

<div class="counter container">
    <figure class="carousel is-relative is-clipped has-background-grey-lighter image {{ $ratio }}">
        @foreach($carouselImages as $carousel)
            <div class="carousel-slide">
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
                <span class="icon carousel-button has-text-info is-size-3 ml-5">
                    <i class="fa fa-chevron-left"></i>
                </span>
            </div>
            <div class="level-item is-align-items-end mb-4" style="height: 100%">
                <div class="carousel-indicator">
                    @for ($i = 0; $i < $numberOfSliders; $i++)
                        <div class="carousel-indicator-item has-background-info"> </div>
                    @endfor
                </div>
            </div>
            <div class="level-item is-justify-content-end">
                <span class="icon carousel-button has-text-info is-size-3 mr-5">
                    <i class="fa fa-chevron-right"></i>
                </span>
            </div>
        </div>
    </figure>
</div>

@once
    @push('bottom_scripts')
    <script>
        const Counter = {
        data() {
            return {
                    counter: "test",
                    counter2: "test2",
                }
            }
        }
        console.log(document.getElementsByClassName('counter').length);
        Vue.createApp(Counter).mount('.counter')
    </script>
    @endpush
@endonce
