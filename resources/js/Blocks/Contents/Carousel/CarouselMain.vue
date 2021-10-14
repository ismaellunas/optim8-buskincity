<template>
    <figure class="carousel image" :class="config.carousel?.ratio">
        <slot></slot>

        <span
            class="icon carousel-button has-text-info is-size-3 ml-5 prev"
            @click="$emit('prev')"
        >
            <i class="fa fa-chevron-left"></i>
        </span>
        <span
            class="icon carousel-button has-text-info is-size-3 mr-5 next"
            @click="$emit('next')"
        >
            <i class="fa fa-chevron-right"></i>
        </span>

        <div class="carousel-indicator">
            <template
                v-for="(total, index) in totalImage"
                :key="index"
            >
                <div
                    class="carousel-indicator-item"
                    :class="{ active: visibleSlide === index }"
                >
                </div>
            </template>
        </div>
    </figure>
</template>

<script>
    export default {
        name: 'CarouselMain',

        props: {
            config: { type: Object, required: true },
            totalImage: { type: Number, default: 0 },
            visibleSlide: { type: Number, default: 0 },
        },

        emits: ['prev', 'next'],
    }
</script>