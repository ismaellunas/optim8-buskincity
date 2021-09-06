<template>
    <figure
        class="image"
        :class="composedFigureClass"
    >
        <img
            :alt="alt"
            :description="description"
            :class="composedImgClass"
            :src="src"
            :style="imgStyle"
        >

        <slot></slot>
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
                return classes;
            },
        },
    };
</script>
