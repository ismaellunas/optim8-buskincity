import MixinHasLoader from './HasLoader';

export default {
    mixins: [
        MixinHasLoader,
    ],
    methods: {
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
