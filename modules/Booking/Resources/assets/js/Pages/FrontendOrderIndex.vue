<template>
    <div class="box">
        <div class="columns">
            <div class="column is-4">
                <div class="is-pulled-left">
                    <biz-filter-search
                        v-model="term"
                        @search="search"
                    />
                </div>

                <div class="is-clearfix" />
            </div>

            <div class="column is-2">
                <biz-dropdown :close-on-click="false">
                    <template #trigger>
                        <span>Filter</span>

                        <span class="icon is-small">
                            <i
                                :class="icon.angleDown"
                                aria-hidden="true"
                            />
                        </span>
                    </template>

                    <biz-dropdown-item>
                        Status
                    </biz-dropdown-item>

                    <biz-dropdown-item
                        v-for="status in statusOptions"
                        :key="status.id"
                    >
                        <biz-checkbox
                            v-model:checked="statuses"
                            :value="status.id"
                            @change="onStatusChanged"
                        >
                            &nbsp; {{ status.value }}
                        </biz-checkbox>
                    </biz-dropdown-item>
                </biz-dropdown>
            </div>

            <div class="column is-4">
                <biz-filter-date-range
                    v-model="dates"
                    max-range="31"
                    @update:model-value="onDateRangeChanged"
                />
            </div>
        </div>

        <div class="table-container">
            <biz-table-info :records="orders" />

            <biz-table class="is-striped is-hoverable is-fullwidth">
                <thead>
                    <tr>
                        <biz-table-column-sort
                            :order="order"
                            :is-sorted="column == 'status'"
                            @click="orderColumn('status')"
                        >
                            Status
                        </biz-table-column-sort>
                        <biz-table-column-sort
                            :order="order"
                            :is-sorted="column == 'name'"
                            @click="orderColumn('name')"
                        >
                            Name
                        </biz-table-column-sort>
                        <biz-table-column-sort
                            :order="order"
                            :is-sorted="column == 'date'"
                            @click="orderColumn('date')"
                        >
                            Date
                        </biz-table-column-sort>
                        <th>Timezone</th>
                        <biz-table-column-sort
                            :order="order"
                            :is-sorted="column == 'time'"
                            @click="orderColumn('time')"
                        >
                            Time
                        </biz-table-column-sort>
                        <biz-table-column-sort
                            :order="order"
                            :is-sorted="column == 'checkin'"
                            @click="orderColumn('checkin')"
                        >
                            Check-In
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
                        v-for="order in orders.data"
                        :key="order.id"
                    >
                        <td>
                            <biz-tag class="is-medium">
                                {{ order.status }}
                            </biz-tag>
                        </td>
                        <td>{{ order.product_name }}</td>
                        <td>{{ order.date }}</td>
                        <td>{{ order.timezone }}</td>
                        <td>{{ order.start_end_time }}</td>
                        <td>{{ order.check_in_time }}</td>
                        <td>
                            <div class="level-right">
                                <biz-button-link
                                    class="is-ghost has-text-black"
                                    :href="route(baseRouteName+'.show', order.id)"
                                >
                                    <biz-icon
                                        class="is-small"
                                        :icon="icon.show"
                                    />
                                </biz-button-link>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </biz-table>
        </div>

        <biz-pagination
            :links="orders.links"
            :query-params="queryParams"
        />
    </div>
</template>

<script>
    import MixinHasColumnSorted from '@/Mixins/HasColumnSorted';
    import Layout from '@/Layouts/User';
    import BizButtonLink from '@/Biz/ButtonLink';
    import BizCheckbox from '@/Biz/Checkbox';
    import BizDropdown from '@/Biz/Dropdown';
    import BizDropdownItem from '@/Biz/DropdownItem';
    import BizFilterDateRange from '@/Biz/Filter/DateRange';
    import BizFilterSearch from '@/Biz/Filter/Search';
    import BizIcon from '@/Biz/Icon';
    import BizPagination from '@/Biz/Pagination';
    import BizTable from '@/Biz/Table';
    import BizTableInfo from '@/Biz/TableInfo';
    import BizTableColumnSort from '@/Biz/TableColumnSort';
    import BizTag from '@/Biz/Tag';
    import icon from '@/Libs/icon-class';
    import { confirmDelete, oops as oopsAlert, success as successAlert } from '@/Libs/alert';
    import { isArray, merge } from 'lodash';
    import { ref } from "vue";

    export default {
        components: {
            BizButtonLink,
            BizCheckbox,
            BizDropdown,
            BizDropdownItem,
            BizFilterDateRange,
            BizFilterSearch,
            BizIcon,
            BizPagination,
            BizTable,
            BizTableInfo,
            BizTableColumnSort,
            BizTag,
        },

        mixins: [
            MixinHasColumnSorted,
        ],

        layout: Layout,

        props: {
            baseRouteName: { type: String, required: true },
            pageQueryParams: { type: Object, default: () => {} },
            orders: { type: Object, required: true },
            statusOptions: { type: Object, required: true },
        },

        setup(props) {
            const queryParams = merge(
                {},
                props.pageQueryParams
            );

            return {
                icon,
                dates: ref(isArray(props.pageQueryParams?.dates)
                    ? props.pageQueryParams?.dates.filter(Boolean)
                    : []
                ),
                statuses: ref(props.pageQueryParams?.status ?? []),
                term: ref(props.pageQueryParams?.term ?? null),
                queryParams: ref(queryParams),
            };
        },

        methods: {
            onStatusChanged() {
                this.queryParams['status'] = this.statuses;
                this.refreshWithQueryParams(); // on mixin MixinFilterDataHandle
            },

            onDateRangeChanged() {
                this.queryParams['dates'] = this.dates;
                this.refreshWithQueryParams(); // on mixin MixinFilterDataHandle
            },
        },
    };
</script>
