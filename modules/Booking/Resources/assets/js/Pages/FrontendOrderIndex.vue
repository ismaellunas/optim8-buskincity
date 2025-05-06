<template>
    <div class="box">
        <div class="columns is-multiline is-mobile">
            <div class="column is-4-desktop is-4-tablet is-12-mobile">
                <biz-filter-search
                    v-model="term"
                    @search="search"
                />
            </div>

            <div class="column is-2-desktop is-2-tablet is-12-mobile">
                <biz-select
                    v-model="status"
                    class="is-fullwidth"
                    @change="onStatusChanged()"
                >
                    <option
                        v-for="statusOption in statusOptions"
                        :key="statusOption.id"
                        :value="statusOption.id"
                    >
                        <span>{{ statusOption.value }}</span>
                    </option>
                </biz-select>
            </div>

            <div class="column is-4-desktop is-3-tablet is-12-mobile">
                <biz-filter-date-range
                    v-model="dates"
                    max-range="31"
                    @update:model-value="onDateRangeChanged"
                />
            </div>

            <div class="column is-2-desktop is-3-tablet is-12-mobile">
                <biz-select
                    v-model="location"
                    class="is-fullwidth"
                    placeholder="Any"
                    @change="onLocationChanged()"
                >
                    <option
                        v-for="locationOption in computedLocationOptions"
                        :key="locationOption.id"
                        :value="locationOption.id"
                    >
                        {{ locationOption.value }}
                    </option>
                </biz-select>
            </div>
        </div>

        <biz-table-index
            :records="orders"
            :query-params="queryParams"
        >
            <template #thead>
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
                        :is-sorted="column == 'location'"
                        @click="orderColumn('location')"
                    >
                        Location
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
                        Check-in
                    </biz-table-column-sort>
                    <th>
                        <div class="level-right">
                            Actions
                        </div>
                    </th>
                </tr>
            </template>

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
                <td>{{ order.location }}</td>
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
                                :icon="iconShow"
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
    import BizFilterDateRange from '@/Biz/Filter/DateRange.vue';
    import BizFilterSearch from '@/Biz/Filter/Search.vue';
    import BizIcon from '@/Biz/Icon.vue';
    import BizSelect from '@/Biz/Select.vue';
    import BizTableColumnSort from '@/Biz/TableColumnSort.vue';
    import BizTableIndex from '@/Biz/TableIndex.vue';
    import BizTag from '@/Biz/Tag.vue';
    import { angleDown as iconAngleDown, show as iconShow } from '@/Libs/icon-class';
    import { isArray, each } from 'lodash';
    import { ref } from "vue";

    export default {
        components: {
            BizButtonLink,
            BizFilterDateRange,
            BizFilterSearch,
            BizIcon,
            BizSelect,
            BizTableColumnSort,
            BizTableIndex,
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
            locationOptions: { type: Object, required: true },
            statusOptions: { type: Object, required: true },
        },

        setup(props) {
            const country = props.pageQueryParams?.country;
            const city = props.pageQueryParams?.city;
            const location = country
                ? country + (city ? '-' + city : '')
                : null;

            return {
                iconAngleDown,
                iconShow,
                dates: ref(isArray(props.pageQueryParams?.dates)
                    ? props.pageQueryParams?.dates.filter(Boolean)
                    : []
                ),
                queryParams: ref({ ...{}, ...props.pageQueryParams }),
                status: ref(props.pageQueryParams?.status ?? null),
                term: ref(props.pageQueryParams?.term ?? null),
                location: ref(location),
            };
        },

        computed: {
            computedLocationOptions() {
                const options = [];

                each(this.locationOptions, (location, key) => {
                    options.push({
                        id: key,
                        value: location.country,
                    });

                    each(location.cities, (city) => {
                        options.push({
                            id: key +'-'+ city,
                            value: ' - '+ city,
                        });
                    });
                });

                return options;
            },

            locationParts() {
                const countryCity = {
                    country: null,
                    city: null,
                };

                if (!this.location) {
                    return countryCity;
                }

                const locationParts = this.location.split('-');

                return {
                    country: locationParts[0],
                    city: locationParts[1],
                };
            },
        },

        methods: {
            onStatusChanged() {
                this.queryParams['status'] = this.status;
                this.refreshWithQueryParams(); // on mixin MixinFilterDataHandle
            },

            onDateRangeChanged() {
                this.queryParams['dates'] = this.dates;
                this.refreshWithQueryParams(); // on mixin MixinFilterDataHandle
            },

            onLocationChanged() {
                this.queryParams['city'] = this.locationParts.city;
                this.queryParams['country'] = this.locationParts.country;
                this.refreshWithQueryParams(); // on mixin MixinFilterDataHandle
            },
        },
    };
</script>
