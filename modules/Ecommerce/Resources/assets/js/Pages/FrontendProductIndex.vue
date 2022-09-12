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
            </div>

            <div class="column" />
        </div>

        <div class="table-container">
            <biz-table class="is-striped is-hoverable is-fullwidth">
                <thead>
                    <tr>
                        <th>Name</th>
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
                                        :icon="iconFormatter('fa-calendar-circle-plus')"
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
    import BizButtonIcon from '@/Biz/ButtonIcon';
    import BizButtonLink from '@/Biz/ButtonLink';
    import BizFilterSearch from '@/Biz/Filter/Search';
    import BizIcon from '@/Biz/Icon';
    import BizPagination from '@/Biz/Pagination';
    import BizTable from '@/Biz/Table';
    import Layout from '@/Layouts/User';
    import MixinFilterDataHandle from '@/Mixins/FilterDataHandle';
    import { iconFormatter } from '@/Libs/icon-class';
    import { confirmDelete, oops as oopsAlert, success as successAlert } from '@/Libs/alert';
    import { merge } from 'lodash';
    import { ref } from "vue";

    export default {
        components: {
            BizButtonLink,
            BizFilterSearch,
            BizIcon,
            BizPagination,
            BizTable,
        },

        mixins: [
            MixinFilterDataHandle,
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

        methods: {
            iconFormatter: iconFormatter,
        },
    };
</script>
