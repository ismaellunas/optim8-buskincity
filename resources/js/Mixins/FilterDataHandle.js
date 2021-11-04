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
        search(term) {
            this.queryParams['term'] = term;
            this.refreshWithQueryParams();
        },
        refreshWithQueryParams() {
            this.$inertia.get(
                route(this.baseRouteName+'.index'),
                this.queryParams,
                {
                    replace: true,
                    preserveState: true,
                    onStart: () => this.onStartLoadingOverlay(),
                    onFinish: () => this.onEndLoadingOverlay(),
                }
            );
        },
    },
};
