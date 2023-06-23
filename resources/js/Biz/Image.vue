<template>
    <figure
        class="image is-clipped"
        :class="composedFigureClass"
    >
        <img
            :alt="alt"
            :description="description"
            :class="composedImgClass"
            :src="src"
            :data-src="dataSrc"
            :style="imgStyle"
        >

        <slot />
    </figure>
</template>

<script>
    export default {
        props: {
            alt: String,
            description: String,
            imgStyle: {},
            ratio: String,
            rounded: String,
            square: String,
            src: String,
            dataSrc: { type: String, default: null },
            hasPosition: { type: Boolean, default: false },
        },
        computed: {
            composedImgClass() {
                const classes = [];
                if (this.rounded) {
                    classes.push(this.rounded);
                }
                return classes;
            },
            composedFigureClass() {
                const classes = [];
                if (this.ratio) {
                    classes.push(this.ratio);
                }
                if (this.square) {
                    classes.push(this.square);
                }
                if (this.hasPosition && !this.ratio) {
                    classes.push('is-inline-block');
                }
                return classes;
            },
        },
    };
</script>
