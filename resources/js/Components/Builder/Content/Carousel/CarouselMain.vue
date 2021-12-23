<template>
    <figure
        class="carousel is-relative is-clipped has-background-grey-lighter image"
        :class="config?.ratio"
    >
        <slot />

        <div class="level is-overlay">
            <div class="level-item is-justify-content-start">
                <span
                    class="icon carousel-button has-text-info is-size-3 ml-5"
                    @click="$emit('prev')"
                >
                    <i class="fa fa-chevron-left" />
                </span>
            </div>
            <div
                class="level-item is-align-items-end mb-4"
                style="height: 100%"
            >
                <div class="carousel-indicator">
                    <template
                        v-for="(total, index) in totalImage"
                        :key="index"
                    >
                        <div
                            class="carousel-indicator-item has-background-info"
                            :class="{ active: visibleSlide === index }"
                        />
                    </template>
                </div>
            </div>
            <div class="level-item is-justify-content-end">
                <span
                    class="icon carousel-button has-text-info is-size-3 mr-5"
                    @click="$emit('next')"
                >
                    <i class="fa fa-chevron-right" />
                </span>
            </div>
        </div>
    </figure>
</template>

<script>
    export default {
        name: 'CarouselMain',

        props: {
            config: {
                type: Object,
                required: true
            },
            totalImage: {
                type: Number,
                default: 0
            },
            visibleSlide: {
                type: Number,
                default: 0
            },
        },

        emits: ['prev', 'next'],
    }
</script>