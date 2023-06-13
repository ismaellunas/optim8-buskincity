<template>
    <div class="box">
        <div class="columns is-multiline is-mobile">
            <div class="column is-4-desktop is-6-tablet is-12-mobile">
                <biz-filter-search
                    v-model="term"
                    @search="search"
                />
            </div>
        </div>

        <biz-table-index
            :records="products"
            :query-params="queryParams"
        >
            <template #thead>
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
            </template>

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
        </biz-table-index>
    </div>
</template>

<script>
    import MixinHasColumnSorted from '@/Mixins/HasColumnSorted';
    import Layout from '@/Layouts/User.vue';
    import BizButtonLink from '@/Biz/ButtonLink.vue';
    import BizFilterSearch from '@/Biz/Filter/Search.vue';
    import BizIcon from '@/Biz/Icon.vue';
    import BizTableColumnSort from '@/Biz/TableColumnSort.vue';
    import BizTableIndex from '@/Biz/TableIndex.vue';
    import icon from '@/Libs/icon-class';
    import { merge } from 'lodash';
    import { ref } from "vue";

    export default {
        components: {
            BizButtonLink,
            BizFilterSearch,
            BizIcon,
            BizTableColumnSort,
            BizTableIndex,
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
