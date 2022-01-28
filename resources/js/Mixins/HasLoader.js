export default {
    data() {
        return {
            loader: null,
        };
    },

    methods: {
        onStartLoadingOverlay() {
            this.loader = this.$loading.show();
        },
        onEndLoadingOverlay() {
            this.loader.hide();
        },
    },
}