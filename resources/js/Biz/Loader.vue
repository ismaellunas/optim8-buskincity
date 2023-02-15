<template>
    <loading
        v-model:active="computedValue"
        :is-full-page="isFullPage"
        :color="color"
        :loader="loader"
        :opacity="opacity"
    />
</template>

<script>
    import Loading from 'vue-loading-overlay';
    import { useModelWrapper } from '@/Libs/utils';

    export default {
        name: "BizLoader",

        components: {
            Loading,
        },

        props: {
            color: { type: String, default: '#3280bf' },
            isFullPage: { type: Boolean, default: true },
            loader: { type: String, default: 'dots' },
            modelValue: { type: Boolean, default: true },
            opacity: { type: Number, default: 0.3 },
        },

        setup(props, {emit}) {
            return {
                computedValue: useModelWrapper(props, emit),
            };
        },
    }
</script>