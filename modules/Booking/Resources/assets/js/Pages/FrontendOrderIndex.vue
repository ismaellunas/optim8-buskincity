<template>
    <div class="box">
        <div class="columns is-multiline">
            <div class="column is-4-desktop">
                <biz-filter-search
                    v-model="term"
                    @search="search"
                />
            </div>

            <div class="column is-2-desktop">
                <biz-dropdown
                    class="is-fullwidth"
                    :close-on-click="false"
                >
                    <template #trigger>
                        <span>Status ({{ statuses.length }})</span>

                        <biz-icon
                            class="is-small"
                            :icon="icon.angleDown"
                        />
                    </template>

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

            <div class="column is-4 is-3-widescreen">
                <biz-filter-date-range
                    v-model="dates"
                    max-range="31"
                    @update:model-value="onDateRangeChanged"
                />
            </div>

            <div class="column is-narrow">
                <biz-dropdown-search
                    class="is-fullwidth"
                    :close-on-click="true"
                    @search="searchCity($event)"
                >
                    <template #trigger>
                        <span>
                            {{ queryParams.city ?? 'Any' }}
                        </span>
                    </template>

                    <biz-dropdown-item
                        @click="onCityChange()"
                    >
                        Any
                    </biz-dropdown-item>

                    <biz-dropdown-item
                        v-for="(option, index) in filteredCities"
                        :key="index"
                        @click="onCityChange(option)"
                    >
                        {{ option }}
                    </biz-dropdown-item>
                </biz-dropdown-search>
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
                            :is-sorted="column == 'city'"
                            @click="orderColumn('city')"
                        >
                            City
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
                        <td>{{ order.city }}</td>
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
    import BizDropdownSearch from '@/Biz/DropdownSearch';
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
    import { debounce, isEmpty, isArray, filter, merge } from 'lodash';
    import { debounceTime } from '@/Libs/defaults';
    import { ref } from "vue";

    export default {
        components: {
            BizButtonLink,
            BizCheckbox,
            BizDropdown,
            BizDropdownItem,
            BizDropdownSearch,
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
            cityOptions: { type: Object, required: true },
            statusOptions: { type: Object, required: true },
        },

        setup(props) {
            return {
                icon,
                dates: ref(isArray(props.pageQueryParams?.dates)
                    ? props.pageQueryParams?.dates.filter(Boolean)
                    : []
                ),
                filteredCities: ref(props.cityOptions.slice(0, 10)),
                queryParams: ref({ ...{}, ...props.pageQueryParams }),
                statuses: ref(props.pageQueryParams?.status ?? []),
                term: ref(props.pageQueryParams?.term ?? null),
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

            onCityChange(city = null) {
                this.queryParams['city'] = city;
                this.refreshWithQueryParams(); // on mixin MixinFilterDataHandle
            },

            searchCity: debounce(function(term) {
                if (!isEmpty(term) && term.length > 1) {
                    this.filteredCities = filter(this.cityOptions, function (city) {
                        return new RegExp(term, 'i').test(city);
                    }).slice(0, 10);
                } else {
                    this.filteredCities = this.cityOptions.slice(0, 10);
                }
            }, debounceTime),

        },
    };
</script>
