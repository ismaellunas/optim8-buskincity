<template>
    <loading
        v-model:active="isLoading"
        :is-full-page="true"
        :opacity="0.3"
        :z-index="8000"
        color="#3280bf"
        loader="dots"
    />
</template>

<script>
    import Loading from 'vue-loading-overlay';
    import 'vue-loading-overlay/dist/vue-loading.css';

    export default {
        name: 'LoadingOverlay',

        components: {
            Loading
        },

        data() {
            return {
                isLoading: true,
            }
        },
    }
</script>