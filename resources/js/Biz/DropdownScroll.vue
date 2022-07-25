<template>
    <div :style="styles">
        <slot />
    </div>
</template>

<script>
    export default {
        name: "BizDropdownScroll",

        props: {
            maxHeight: { type: Number, default: 200 },
            maxWidth: { type: Number, default: null }
        },

        computed: {
            styles() {
                const styles = {};

                if (this.maxHeight) {
                    styles['maxHeight'] = this.maxHeight + 'px';
                    styles['overflowX'] = 'auto';
                }

                if (this.maxWidth) {
                    styles['maxWidth'] = this.maxWidth + 'px';
                    styles['overflowY'] = 'auto';
                }

                return styles;
            },
        },
    };
</script>