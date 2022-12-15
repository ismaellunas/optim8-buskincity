<template>
    <div class="box">
        <div class="columns">
            <div class="column">
                <div class="is-pulled-left">
                    <biz-filter-search
                        v-model="term"
                        @search="search"
                    />
                </div>

                <div class="is-clearfix" />
            </div>
        </div>

        <div class="table-container column is-12">
            <biz-table class="is-striped is-hoverable is-fullwidth">
                <thead>
                    <tr>
                        <biz-table-column-sort
                            :order="order"
                            :is-sorted="column == 'name'"
                            @click="orderColumn('name')"
                        >
                            Name
                        </biz-table-column-sort>
                        <th>
                            <div class="level-right">
                                Actions
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="product in products.data"
                        :key="product.id"
                    >
                        <td>{{ product.name }}</td>
                        <td>
                            <div class="level-right">
                                <biz-button-link
                                    class="is-ghost has-text-black"
                                    :href="route(baseRouteName+'.show', product.id)"
                                >
                                    <biz-icon
                                        class="is-small"
                                        :icon="icon.calendarCirclePlus"
                                    />
                                </biz-button-link>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </biz-table>
        </div>

        <biz-pagination
            :links="products.links"
            :query-params="queryParams"
        />
    </div>
</template>

<script>
    import MixinHasColumnSorted from '@/Mixins/HasColumnSorted';
    import BizButtonLink from '@/Biz/ButtonLink';
    import BizFilterSearch from '@/Biz/Filter/Search';
    import BizIcon from '@/Biz/Icon';
    import BizPagination from '@/Biz/Pagination';
    import BizTable from '@/Biz/Table';
    import BizTableColumnSort from '@/Biz/TableColumnSort';
    import icon from '@/Libs/icon-class';
    import Layout from '@/Layouts/User';
    import { merge } from 'lodash';
    import { ref } from "vue";

    export default {
        components: {
            BizButtonLink,
            BizFilterSearch,
            BizIcon,
            BizPagination,
            BizTable,
            BizTableColumnSort,
        },

        mixins: [
            MixinHasColumnSorted,
        ],

        layout: Layout,

        props: {
            baseRouteName: { type: String, required: true },
            pageQueryParams: { type: Object, default: () => {} },
            products: { type: Object, required: true },
        },

        setup(props) {
            const queryParams = merge(
                {},
                props.pageQueryParams
            );

            return {
                queryParams: ref(queryParams),
                term: ref(props.pageQueryParams?.term ?? ""),
            };
        },

        data() {
            return {
                icon
            };
        },
    };
</script>
