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
