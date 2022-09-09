<template>
    <div class="box">
        <div class="columns">
            <!--
            <div class="column">
                <div class="is-pulled-left">
                    <biz-filter-search
                        v-model="term"
                        @search="search"
                    />
                </div>
            </div>
            <div class="column">
                <div
                    v-if="can.add"
                    class="is-pulled-right"
                >
                    <biz-button-link
                        :href="route(baseRouteName+'.create')"
                        class="is-primary"
                    >
                        <biz-icon
                            class="is-small"
                            :icon="icon.add"
                        />

                        <span>Create New</span>
                    </biz-button-link>
                </div>
            </div>
            -->
        </div>

        <div class="table-container">
            <biz-table class="is-striped is-hoverable is-fullwidth">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Status</th>
                        <th>
                            <div class="level-right">
                                Actions
                            </div>
                        </th>
                    </tr>
                </thead>
            </biz-table>
        </div>

        <!--
        <biz-pagination
            :links="products.links"
            :query-params="queryParams"
        />
        -->
    </div>
</template>

<script>
    import Layout from '@/Layouts/User';
    import BizButtonIcon from '@/Biz/ButtonIcon';
    import BizButtonLink from '@/Biz/ButtonLink';
    import BizIcon from '@/Biz/Icon';
    import BizPagination from '@/Biz/Pagination';
    import BizTable from '@/Biz/Table';
    import icon from '@/Libs/icon-class';
    import { confirmDelete, oops as oopsAlert, success as successAlert } from '@/Libs/alert';
    import { merge } from 'lodash';
    import { ref } from "vue";

    export default {
        components: {
            BizButtonLink,
            BizIcon,
            BizPagination,
            BizTable,
        },

        layout: Layout,

        props: {
            baseRouteName: { type: String, required: true },
            //can: { type: Object, required: true },
            //pageQueryParams: { type: Array, default: () => [] },
            //products: { type: Object, required: true },
        },

        setup(props) {
            //const queryParams = merge(
            //    {},
            //    props.pageQueryParams
            //);

            return {
                //icon,
                //queryParams: ref(queryParams),
                //term: ref(props.pageQueryParams?.term ?? null),
            };
        },

        methods: {
        },
    };
</script>
