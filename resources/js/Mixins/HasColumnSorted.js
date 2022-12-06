import MixinFilterDataHandle from './FilterDataHandle';

export default {
    mixins: [
        MixinFilterDataHandle,
    ],

    computed: {
        order() {
            return this.pageQueryParams?.order;
        },
        column() {
            return this.pageQueryParams?.column;
        },
    },

    methods: {
        orderColumn(column) {
            const order = this.order == 'desc' || typeof this.order === 'undefined' ? 'asc' : 'desc';

            this.queryParams['column'] = column;
            this.queryParams['order'] = order;
            this.refreshWithQueryParams();
        },
    },
}