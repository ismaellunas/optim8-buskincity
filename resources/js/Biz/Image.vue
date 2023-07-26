<template>
    <figure
        class="image is-clipped"
        :class="composedFigureClass"
    >
        <img
            v-bind="$attrs"
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
        name: 'BizImage',

        props: {
            alt: { type: [String, null], default: null },
            description: { type: [String, null], default: null },
            imgStyle: { type: [String, Object], default: "" },
            ratio: { type: [String, null], default: null },
            rounded: { type: [String, null], default: null },
            square: { type: [String, null], default: null },
            src: { type: [String, null], default: null },
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
