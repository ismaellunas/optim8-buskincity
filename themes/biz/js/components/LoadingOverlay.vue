<template>
    <loading
        v-model:active="isLoading"
        :is-full-page="true"
        :color="color"
        :loader="loader"
        :opacity="opacity"
        :z-index="zIndex"
    />
</template>

<script>
    import Loading from 'vue-loading-overlay';
    import { loadingOptions } from './../../../../resources/js/Libs/defaults';

    export default {
        name: 'LoadingOverlay',

        components: {
            Loading
        },

        props: {
            color: { type: [Number, String], default: lodadingOptions.color },
            loader: { type: String, default: lodadingOptions.loader },
            opacity: { type: [Number, String], default: lodadingOptions.opacity },
            zIndex: { type: [Number, String], default: lodadingOptions.zIndex },
        },

        data() {
            return {
                isLoading: true,
            };
        },
    }
</script>
